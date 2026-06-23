<?php

namespace App\Controllers;

use App\Models\MelhorEnvioLogModel;
use App\Models\PedidoModel;
use App\Services\MelhorEnvioService;

class MelhorEnvio extends BaseController
{
    private MelhorEnvioService $service;

    public function __construct()
    {
        $this->service = new MelhorEnvioService();
    }

    /**
     * Tela inicial - mostra status da conexao e botao para conectar.
     */
    public function conectar()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return redirect()->to(site_url('/'))->with('atencao', 'Apenas administradores podem conectar o Melhor Envio.');
        }

        $cred = $this->service->credencialAtual();
        $state = bin2hex(random_bytes(16));
        session()->set('me_oauth_state', $state);

        $data = [
            'titulo'  => 'Melhor Envio',
            'cred'    => $cred,
            'authUrl' => $this->service->authorizeUrl($state),
        ];

        return view('MelhorEnvio/conectar', $data);
    }

    /**
     * OAuth callback - recebe ?code e troca por tokens.
     */
    public function callback()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return redirect()->to(site_url('/'))->with('atencao', 'Sem permissao.');
        }

        $code     = (string) $this->request->getGet('code');
        $state    = (string) $this->request->getGet('state');
        $expected = (string) session()->get('me_oauth_state');

        if ($code === '' || $state === '' || $state !== $expected) {
            log_message('warning', '[ME-OAUTH] callback com state invalido');
            return redirect()->to(site_url('melhor-envio/conectar'))
                ->with('erro', 'Estado OAuth invalido. Tente conectar novamente.');
        }

        session()->remove('me_oauth_state');

        try {
            $this->service->exchangeCode($code);
        } catch (\Throwable $e) {
            log_message('error', '[ME-OAUTH] callback falhou: ' . $e->getMessage());
            return redirect()->to(site_url('melhor-envio/conectar'))
                ->with('erro', 'Falha ao concluir conexao: ' . $e->getMessage());
        }

        return redirect()->to(site_url('melhor-envio/status'))
            ->with('sucesso', 'Melhor Envio conectado com sucesso.');
    }

    /**
     * Tela de status - mostra credencial atual + ultimos logs.
     */
    public function status()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return redirect()->to(site_url('/'))->with('atencao', 'Sem permissao.');
        }

        $pedidoId = $this->request->getGet('pedido_id');
        $pedidoId = $pedidoId !== null && $pedidoId !== '' ? (int) $pedidoId : null;

        $logModel = new MelhorEnvioLogModel();

        $data = [
            'titulo' => 'Melhor Envio - Status',
            'cred'   => $this->service->credencialAtual(),
            'logs'   => $logModel->ultimos(100, $pedidoId),
            'pedidoIdFiltro' => $pedidoId,
        ];

        return view('MelhorEnvio/status', $data);
    }

    /**
     * Webhook ME - eventos order.*
     */
    public function webhook()
    {
        // GET = health-check para validacao da URL no painel ME / browser
        if ($this->request->getMethod(true) === 'GET') {
            return $this->response->setStatusCode(200)->setJSON([
                'ok'       => true,
                'endpoint' => 'melhor-envio/webhook',
                'aceita'   => 'POST',
                'ambiente' => env('CI_ENVIRONMENT'),
            ]);
        }

        $raw    = $this->request->getBody() ?? '';
        $sig    = $this->request->getHeaderLine('X-ME-Signature');
        $secret = (string) env('MELHOR_ENVIO_WEBHOOK_SECRET');
        $calc   = hash_hmac('sha256', $raw, $secret);

        if ($secret === '' || ! hash_equals($calc, $sig)) {
            log_message('warning', '[ME-WEBHOOK] assinatura invalida');
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }

        $payload = json_decode($raw, true);
        $event   = $payload['event'] ?? null;
        $meId    = $payload['data']['id'] ?? null;

        log_message('info', '[ME-WEBHOOK] event=' . ($event ?? 'unknown') . ' id=' . ($meId ?? '-'));

        $logModel = new MelhorEnvioLogModel();
        $logModel->registrar([
            'pedido_id'     => null,
            'endpoint'      => 'webhook:' . ($event ?? 'unknown'),
            'http_method'   => 'POST',
            'request_body'  => $raw,
            'response_body' => null,
            'http_status'   => 200,
            'duracao_ms'    => 0,
            'erro'          => null,
        ]);

        if (! $event || ! $meId) {
            return $this->response->setStatusCode(200)->setJSON(['ok' => true]);
        }

        $pedidoModel = new PedidoModel();
        $pedido = $pedidoModel->where('me_order_id', $meId)->first();
        if (! $pedido) {
            return $this->response->setStatusCode(200)->setJSON(['ok' => true]);
        }

        $update = [];
        switch ($event) {
            case 'order.generated':
                $update['me_status']       = 'generated';
                $update['me_etiqueta_url'] = $payload['data']['tag_url'] ?? $pedido->me_etiqueta_url;
                break;
            case 'order.posted':
                if ($pedido->me_status !== 'posted') {
                    $update['me_status']     = 'posted';
                    $update['rastreio']      = $payload['data']['tracking'] ?? $pedido->rastreio;
                    $update['me_postado_em'] = date('Y-m-d H:i:s');
                }
                break;
            case 'order.delivered':
                $update['me_status']      = 'delivered';
                $update['me_entregue_em'] = date('Y-m-d H:i:s');
                break;
            case 'order.undelivered':
                $update['me_status'] = 'undelivered';
                break;
            case 'order.cancelled':
                $update['me_status'] = 'cancelled';
                break;
        }

        if (! empty($update)) {
            $pedidoModel->update($pedido->id, $update);

            // Dispara e-mail apenas no momento da postagem
            if (isset($update['me_status']) && $update['me_status'] === 'posted') {
                try {
                    $pedidoFresh = $pedidoModel->find($pedido->id);
                    (new Pedidos())->enviaEmailRastreio($pedidoFresh);
                } catch (\Throwable $e) {
                    log_message('error', '[ME-WEBHOOK] falha ao enviar email: ' . $e->getMessage());
                }
            }
        }

        return $this->response->setStatusCode(200)->setJSON(['ok' => true]);
    }
}
