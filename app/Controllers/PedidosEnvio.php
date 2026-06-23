<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\EnderecoModel;
use App\Models\PedidoModel;
use App\Services\MelhorEnvioService;

class PedidosEnvio extends BaseController
{
    private PedidoModel $pedidoModel;
    private EnderecoModel $enderecoModel;
    private ClienteModel $clienteModel;
    private MelhorEnvioService $meService;

    public function __construct()
    {
        $this->pedidoModel   = new PedidoModel();
        $this->enderecoModel = new EnderecoModel();
        $this->clienteModel  = new ClienteModel();
        $this->meService     = new MelhorEnvioService();
    }

    /**
     * Cotacao de frete para um pedido.
     * AJAX POST /pedidos-envio/cotar/(:num)
     */
    public function cotar(int $pedidoId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        if (! $this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Sem permissao.',
            ]);
        }

        $pedido = $this->pedidoModel->find($pedidoId);
        if (! $pedido) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pedido nao encontrado.',
            ]);
        }

        $destino = $this->resolveEnderecoDestino($pedido);
        $erro    = $this->validaDestino($destino);
        if ($erro) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $erro,
                'destino' => $destino,
            ]);
        }

        if (! $this->meService->temCredencial()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Melhor Envio nao conectado. Acesse /melhor-envio/conectar.',
            ]);
        }

        try {
            $resposta = $this->meService->cotar([
                'postal_code' => $this->soDigitos($destino['cep']),
            ], [], $pedidoId);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Falha ao cotar: ' . $e->getMessage(),
            ]);
        }

        $servicos = $this->normalizaCotacao($resposta);

        return $this->response->setJSON([
            'success'  => true,
            'destino'  => $destino,
            'servicos' => $servicos,
        ]);
    }

    /**
     * Resolve endereco a partir de enderecos.pedido_id; fallback clientes.usuario_id.
     */
    private function resolveEnderecoDestino(object $pedido): array
    {
        $endereco = $this->enderecoModel->where('pedido_id', $pedido->id)->first();

        $cliente = $this->clienteModel
            ->select('id, nome, email, telefone, cpf, endereco, numero, bairro, cidade, estado, cep')
            ->where('usuario_id', $pedido->user_id)
            ->first();

        if ($endereco) {
            return [
                'nome'      => $cliente->nome   ?? '',
                'email'     => $cliente->email  ?? '',
                'telefone'  => $cliente->telefone ?? '',
                'documento' => preg_replace('/\D/', '', (string) ($cliente->cpf ?? '')),
                'cep'       => $endereco->cep,
                'endereco'  => $endereco->endereco,
                'numero'    => $endereco->numero,
                'bairro'    => $endereco->bairro,
                'cidade'    => $endereco->cidade,
                'estado'    => $endereco->estado,
                'origem'    => 'enderecos.pedido_id',
            ];
        }

        if ($cliente) {
            return [
                'nome'      => $cliente->nome,
                'email'     => $cliente->email,
                'telefone'  => $cliente->telefone,
                'documento' => preg_replace('/\D/', '', (string) $cliente->cpf),
                'cep'       => $cliente->cep,
                'endereco'  => $cliente->endereco,
                'numero'    => $cliente->numero,
                'bairro'    => $cliente->bairro,
                'cidade'    => $cliente->cidade,
                'estado'    => $cliente->estado,
                'origem'    => 'clientes.usuario_id',
            ];
        }

        return [];
    }

    private function validaDestino(array $d): ?string
    {
        if (empty($d)) {
            return 'Endereco de entrega nao encontrado para esse pedido.';
        }
        $cepDigitos = $this->soDigitos((string) ($d['cep'] ?? ''));
        if (strlen($cepDigitos) !== 8) {
            return 'CEP de destino invalido ou ausente.';
        }
        if (empty($d['numero'])) {
            return 'Numero do endereco de destino ausente.';
        }
        if (empty($d['estado'])) {
            return 'Estado do endereco de destino ausente.';
        }
        return null;
    }

    private function soDigitos(string $v): string
    {
        return preg_replace('/\D/', '', $v);
    }

    /**
     * Normaliza resposta do /api/v2/me/shipment/calculate para o front.
     * Filtra servicos com erro, ordena por preco crescente.
     */
    private function normalizaCotacao(array $resposta): array
    {
        $itens = [];
        foreach ($resposta as $s) {
            if (! is_array($s)) {
                continue;
            }
            if (! empty($s['error'])) {
                continue;
            }
            if (! isset($s['id'], $s['price'])) {
                continue;
            }
            $itens[] = [
                'id'              => (int) $s['id'],
                'nome'            => $s['name'] ?? '',
                'transportadora'  => $s['company']['name'] ?? '',
                'transportadora_picture' => $s['company']['picture'] ?? '',
                'preco'           => (float) $s['price'],
                'preco_formatado' => 'R$ ' . number_format((float) $s['price'], 2, ',', '.'),
                'prazo_dias'      => (int) ($s['delivery_time'] ?? 0),
                'pacote'          => $s['packages'][0] ?? null,
            ];
        }
        usort($itens, fn($a, $b) => $a['preco'] <=> $b['preco']);
        return $itens;
    }
}
