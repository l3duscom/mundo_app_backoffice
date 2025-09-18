<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;
use App\Entities\Check;
use Config\Services;


class Acessos extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    protected $ingressoModel;
    protected $checkModel;

    public function __construct()
    {
        // Ajuste para seus namespaces reais dos models
        $this->ingressoModel = model('IngressoModel');
        $this->checkModel    = model('CheckModel');
    }

    /**
     * Verifica e registra o acesso de um ingresso.
     *
     * Rota: POST /api/access/check
     * Consome: application/json
     *
     * Corpo (JSON):
     * - evento_id (int, obrigatório): ID do evento.
     * - codigo    (string, obrigatório): Código do ingresso.
     * - operador  (int, obrigatório): ID do operador que está realizando a leitura/validação.
     * - tipo      (string, opcional): Tipo de registro. Padrão: "ACESSO".
     *
     * Regras de validação:
     * - evento_id: required|is_natural_no_zero
     * - codigo   : required|string
     * - operador : required|is_natural_no_zero
     * - tipo     : permit_empty|string
     *
     * Regras de negócio:
     * - O ingresso precisa existir para o evento informado e estar com o pedido em um dos
     *   status permitidos: CONFIRMED, RECEIVED, paid, RECEIVED_IN_CASH.
     * - Ingressos do tipo "combo" são sempre válidos; demais tipos precisam estar com data_inicio <= hoje
     *   e data_fim >= hoje (data no fuso America/Sao_Paulo).
     * - Sempre é registrado um novo lançamento em `checks` com os dados do acesso.
     * - Se já houver acessos anteriores (tipo_acesso = "ACESSO"), a resposta inclui um aviso (warnings)
     *   e o code retorna "ALREADY_USED".
     *
     * Respostas:
     * 200 OK
     * {
     *   "success": true,
     *   "code": "ALLOWED" | "ALREADY_USED",
     *   "access_count_before": 0,
     *   "ingresso": {
     *     "id": 123,
     *     "codigo": "ABC123",
     *     "nome": "VIP FULL - Sábado",
     *     "ticket": { "tipo":"vip","dia":"sab","data_inicio":"2025-09-18","data_fim":"2025-09-18" },
     *     "pedido": { "evento_id": 77, "status":"CONFIRMED","user_id": 9, "frete": null, "rastreio": "OS123..." }
     *   },
     *   "display": {
     *     "titulo": "Acesso VIP",
     *     "liberado_a_partir": "09:00",
     *     "material": ["Credencial + Cordão colecionável", "..."],
     *     "observacao": "MATERIAL ENTREGUE VIA SEDEX" | null
     *   },
     *   "warnings": []
     * }
     *
     * 400 Bad Request (erros de validação)
     * {
     *   "status": 400,
     *   "error": 400,
     *   "messages": { "evento_id": "The evento_id field is required." }
     * }
     *
     * 404 Not Found (ingresso não localizado/fora da janela)
     * {
     *   "success": false,
     *   "code": "NOT_FOUND",
     *   "message": "O ingresso não foi localizado ou não está válido para hoje."
     * }
     *
     * 500 Internal Server Error (falha ao salvar o acesso)
     * { "status": 500, "error": 500, "messages": { "error": "Não foi possível registrar o acesso." } }
     *
     * Autenticação:
     * - Recomendado: Bearer Token/JWT para identificar o operador no header.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON com o resultado da verificação/registro.
     */


    /**
     * POST /api/access/check
     * Body (JSON): { "evento_id":123, "codigo":"ABC123", "operador": 9, "tipo": "ACESSO" }
     */
    public function check()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->fail('Method not allowed', 405);
        }

        // Lê JSON (array). Se vier form-data, cai no getRawInput como fallback
        $payload = $this->request->getJSON(true) ?? $this->request->getRawInput();

        // Validação básica
        $rules = [
            'evento_id' => 'required|is_natural_no_zero',
            'codigo'    => 'required|string',
            'operador'  => 'required|is_natural_no_zero',
            'tipo'      => 'permit_empty|string', // ex: "ACESSO"
        ];
        $validation = Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($payload)) {
            // se sua versão tiver ResponseTrait::failValidationErrors
            return $this->failValidationErrors($validation->getErrors());

            // alternativa compatível caso não tenha:
            // return $this->respond(['errors' => $validation->getErrors()], 422);
        }

        $eventoId = (int) $payload['evento_id'];
        $codigo   = trim($payload['codigo']);
        $operador = (int) $payload['operador'];
        $tipo     = !empty($payload['tipo']) ? $payload['tipo'] : 'ACESSO';

        $today = Time::today('America/Sao_Paulo')->toDateString();

        $atributos = [
            'ingressos.id',
            'ingressos.codigo',
            'ingressos.nome',
            'tickets.tipo',
            'tickets.dia',
            'tickets.data_inicio',
            'tickets.data_fim',
            'pedidos.evento_id',
            'pedidos.status',
            'pedidos.user_id',
            'pedidos.frete',
            'pedidos.rastreio',
        ];

        $ingresso = $this->ingressoModel->select($atributos)
            ->withDeleted(true)
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('tickets', 'tickets.id = ingressos.ticket_id')
            ->where('pedidos.evento_id', $eventoId)
            ->where('ingressos.codigo', $codigo)
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->groupStart()
                ->where('tickets.tipo', 'combo')
                ->orGroupStart()
                    ->where('tickets.tipo !=', 'combo')
                    ->where('tickets.data_inicio <=', $today)
                    ->where('tickets.data_fim >=', $today)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('ingressos.id', 'DESC')
            ->first();

        if (!$ingresso) {
            return $this->respond([
                'success' => false,
                'code'    => 'NOT_FOUND',
                'message' => 'O ingresso não foi localizado ou não está válido para hoje.',
            ], 404);
        }

        // Total de acessos anteriores registrados
        $totalAcessos = $this->checkModel
            ->where('ingresso_id', $ingresso->id)
            ->where('tipo_acesso', 'ACESSO')
            ->countAllResults();

        // Registra o acesso (mesmo se já tiver acessos, segue seu padrão)
        $check = new Check($payload);
        $check->usuario_id  = (int) $ingresso->user_id;
        $check->ingresso_id = (int) $ingresso->id;
        $check->operador_id = $operador;
        $check->tipo_acesso = $tipo;

        if (!$this->checkModel->save($check)) {
            return $this->failServerError('Não foi possível registrar o acesso.');
        }

        // Monta o "display" equivalente às mensagens de sucesso/atenção
        $display = $this->buildDisplayFor($ingresso->nome, $ingresso->rastreio);

        // Se já tinha acesso anterior, informa em "warnings"
        $warnings = [];
        if ($totalAcessos > 0) {
            $warnings[] = sprintf(
                'Este ingresso já foi utilizado %d vez(es). Entrada permitida apenas com pulseira inviolada.',
                $totalAcessos
            );
        }

        return $this->respond([
            'success'               => true,
            'code'                  => $totalAcessos > 0 ? 'ALREADY_USED' : 'ALLOWED',
            'access_count_before'   => $totalAcessos,
            'ingresso' => [
                'id'          => (int) $ingresso->id,
                'codigo'      => $ingresso->codigo,
                'nome'        => $ingresso->nome,
                'ticket'      => [
                    'tipo'        => $ingresso->tipo,
                    'dia'         => $ingresso->dia,
                    'data_inicio' => $ingresso->data_inicio,
                    'data_fim'    => $ingresso->data_fim,
                ],
                'pedido'      => [
                    'evento_id' => (int) $ingresso->evento_id,
                    'status'    => $ingresso->status,
                    'user_id'   => (int) $ingresso->user_id,
                    'frete'     => $ingresso->frete,
                    'rastreio'  => $ingresso->rastreio,
                ],
            ],
            'display'  => $display,  // dados prontos para sua UI
            'warnings' => $warnings, // exibe alerta de "Atenção!" quando houver
        ], 200);
    }

    /**
     * Constrói o bloco "display" que substitui as mensagens de flash (para a UI consumir).
     */
    private function buildDisplayFor(string $nomeIngresso, ?string $rastreio): array
    {
        $nomeUpper    = mb_strtoupper($nomeIngresso, 'UTF-8');
        $temRastreio  = !empty($rastreio);
        $liberadoVIP  = '09:00';
        $liberadoBASE = '10:00';

        // Defaults
        $ret = [
            'titulo'              => 'Acesso BASIC',
            'liberado_a_partir'   => $liberadoBASE,
            'material'            => [],
            'observacao'          => $temRastreio ? 'MATERIAL ENTREGUE VIA SEDEX' : null,
        ];

        if (strpos($nomeUpper, 'VIP') !== false) {
            $ret['titulo'] = $temRastreio ? 'Acesso VIP FULL' : 'Acesso VIP';
            $ret['liberado_a_partir'] = $liberadoVIP;
            $ret['material'] = $temRastreio ? [] : [
                'Credencial + Cordão colecionável',
                'Pôster Colecionável',
                'Ingresso Holográfico',
                'Copo Colecionável',
                'Pulseira RFID (favor vincular)',
            ];
        } elseif (strpos($nomeUpper, 'PREMIUM') !== false) {
            $ret['titulo'] = 'Acesso PREMIUM';
            $ret['liberado_a_partir'] = $liberadoVIP;
            $ret['material'] = $temRastreio ? [] : [
                'Credencial + Cordão colecionável',
                'Pôster Colecionável',
                'Pulseira',
            ];
        } elseif (strpos($nomeUpper, 'EPIC') !== false) {
            $ret['titulo'] = 'Acesso EPIC PASS';
            $ret['liberado_a_partir'] = $liberadoVIP;
            $ret['material'] = $temRastreio ? [] : [
                'Credencial + Cordão colecionável',
                'Pôster Colecionável',
                'Pulseira de tecido',
            ];
        } elseif (strpos($nomeUpper, 'COSPLAY') !== false) {
            $ret['titulo'] = 'Acesso COSPLAY';
            $ret['liberado_a_partir'] = $liberadoBASE;
            $ret['material'] = $temRastreio ? [] : [
                'Credencial + Cordão colecionável',
                'Pulseira',
            ];
        } else {
            // BASIC (default)
            $ret['titulo'] = 'Acesso BASIC';
            $ret['liberado_a_partir'] = $liberadoBASE;
            $ret['material'] = $temRastreio ? [] : [
                'Credencial + Cordão colecionável',
                'Pulseira',
            ];
        }

        return $ret;
    }
}
