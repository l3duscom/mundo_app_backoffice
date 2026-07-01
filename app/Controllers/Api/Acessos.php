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
    protected $clienteModel;
    protected $expositorModel;
    protected $pedidoOrderBumpModel;

    public function __construct()
    {
        // Ajuste para seus namespaces reais dos models
        $this->ingressoModel        = model('IngressoModel');
        $this->checkModel           = model('CheckModel');
        $this->clienteModel         = model('ClienteModel');
        $this->expositorModel       = model('ExpositorModel');
        $this->pedidoOrderBumpModel = model('PedidoOrderBumpModel');
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
            'ingressos.participante',
            'ingressos.pedido_id',
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
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('tickets', 'tickets.id = ingressos.ticket_id')
            ->where('pedidos.evento_id', $eventoId)
            ->where('ingressos.codigo', $codigo)
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->groupStart() // (
                // COMBO: válido no intervalo [data_inicio, data_fim]
                ->groupStart()
                    ->where('tickets.tipo', 'combo')
                    ->where('tickets.data_inicio <=', $today)
                    ->where('tickets.data_fim >=', $today)
                ->groupEnd()

                ->orGroupStart()
                    // NÃO-COMBO: válido apenas no dia exato
                    ->where('tickets.tipo !=', 'combo')
                    ->where('tickets.data_inicio', $today)
                ->groupEnd()
            ->groupEnd() // )
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

        // Participante: valor gravado no ingresso; se vazio, cai no nome do titular (cliente ou expositor)
        $participante = $this->resolveParticipante($ingresso->participante, (int) $ingresso->user_id);

        // Order bumps do pedido (produtos adicionais)
        $orderBumps = [];
        if (!empty($ingresso->pedido_id)) {
            try {
                $obs = $this->pedidoOrderBumpModel->getOrderBumpsPorPedido((int) $ingresso->pedido_id);
                foreach ($obs as $ob) {
                    $orderBumps[] = [
                        'id'              => (int) $ob->id,
                        'order_bump_id'   => (int) $ob->order_bump_id,
                        'nome'            => $ob->nome ?? null,
                        'descricao'       => $ob->descricao ?? null,
                        'imagem'          => $ob->imagem ?? null,
                        'tipo'            => $ob->tipo ?? null,
                        'quantidade'      => (int) ($ob->quantidade ?? 1),
                        'preco_unitario'  => isset($ob->preco_unitario) ? (float) $ob->preco_unitario : null,
                        'usado'           => (int) ($ob->usado ?? 0) === 1,
                        'usado_em'        => $ob->usado_em ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                log_message('warning', 'Falha ao carregar order bumps na leitura de acesso: ' . $e->getMessage());
            }
        }

        return $this->respond([
            'success'               => true,
            'code'                  => $totalAcessos > 0 ? 'ALREADY_USED' : 'ALLOWED',
            'access_count_before'   => $totalAcessos,
            'ingresso' => [
                'id'           => (int) $ingresso->id,
                'codigo'       => $ingresso->codigo,
                'nome'         => $ingresso->nome,
                'participante' => $participante,
                'ticket'       => [
                    'tipo'        => $ingresso->tipo,
                    'dia'         => $ingresso->dia,
                    'data_inicio' => $ingresso->data_inicio,
                    'data_fim'    => $ingresso->data_fim,
                ],
                'pedido'       => [
                    'id'        => (int) $ingresso->pedido_id,
                    'evento_id' => (int) $ingresso->evento_id,
                    'status'    => $ingresso->status,
                    'user_id'   => (int) $ingresso->user_id,
                    'frete'     => $ingresso->frete,
                    'rastreio'  => $ingresso->rastreio,
                ],
            ],
            'order_bumps' => $orderBumps,
            'display'     => $display,  // dados prontos para sua UI
            'warnings'    => $warnings, // exibe alerta de "Atenção!" quando houver
        ], 200);
    }

    /**
     * Valida acesso à sala VIP pela pulseira (credenciais.codigo).
     *
     * POST /api/acessos/salavip
     * Body (JSON): { "evento_id":123, "codigo":"RFID-ABC", "operador":9, "tipo":"ACESSO" }
     *
     * Diferenças em relação ao check() padrão:
     * - Busca por credenciais.codigo (pulseira RFID) em vez de ingressos.codigo.
     * - Só libera se o nome do ingresso contiver "VIP".
     * - Não valida janela de data do ticket (a sala VIP fica aberta durante todo o evento).
     */
    public function checkSalaVip()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->fail('Method not allowed', 405);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getRawInput();

        $rules = [
            'evento_id' => 'required|is_natural_no_zero',
            'codigo'    => 'required|string',
            'operador'  => 'required|is_natural_no_zero',
            'tipo'      => 'permit_empty|string',
        ];
        $validation = Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($payload)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $eventoId = (int) $payload['evento_id'];
        $codigo   = trim($payload['codigo']);
        $operador = (int) $payload['operador'];
        $tipo     = !empty($payload['tipo']) ? $payload['tipo'] : 'ACESSO';

        $atributos = [
            'ingressos.id',
            'ingressos.codigo',
            'ingressos.nome',
            'ingressos.participante',
            'ingressos.pedido_id',
            'pedidos.evento_id',
            'pedidos.status',
            'pedidos.user_id',
            'pedidos.frete',
            'pedidos.rastreio',
            'credenciais.id AS credencial_id',
            'credenciais.ticket_alimentacao',
            'credenciais.ticket_alimentacao_em',
        ];

        $ingresso = $this->ingressoModel->select($atributos)
            ->withDeleted(true)
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('credenciais', 'credenciais.ingresso_id = ingressos.id')
            ->where('pedidos.evento_id', $eventoId)
            ->where('credenciais.codigo', $codigo)
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->orderBy('ingressos.id', 'DESC')
            ->first();

        if (!$ingresso) {
            return $this->respond([
                'success' => false,
                'code'    => 'NOT_FOUND',
                'message' => 'Credencial não localizada ou pedido inválido.',
            ], 404);
        }

        // Regra da sala VIP: apenas ingressos com "VIP" no nome
        if (stripos((string) $ingresso->nome, 'VIP') === false) {
            return $this->respond([
                'success' => false,
                'code'    => 'NOT_VIP',
                'message' => 'O ingresso não possui privilégios VIP.',
                'ingresso' => [
                    'id'    => (int) $ingresso->id,
                    'nome'  => $ingresso->nome,
                ],
            ], 403);
        }

        // Total de acessos anteriores registrados
        $totalAcessos = $this->checkModel
            ->where('ingresso_id', $ingresso->id)
            ->where('tipo_acesso', 'ACESSO')
            ->countAllResults();

        // Registra o acesso
        $check = new Check($payload);
        $check->usuario_id  = (int) $ingresso->user_id;
        $check->ingresso_id = (int) $ingresso->id;
        $check->operador_id = $operador;
        $check->tipo_acesso = $tipo;

        if (!$this->checkModel->save($check)) {
            return $this->failServerError('Não foi possível registrar o acesso.');
        }

        $participante = $this->resolveParticipante($ingresso->participante, (int) $ingresso->user_id);

        $warnings = [];
        if ($totalAcessos > 0) {
            $warnings[] = sprintf(
                'Este ingresso já foi utilizado %d vez(es). Entrada permitida apenas com pulseira inviolada.',
                $totalAcessos
            );
        }

        $primeiraLeitura = $totalAcessos === 0;
        $ticketRetirado  = (int) ($ingresso->ticket_alimentacao ?? 0) === 1;

        return $this->respond([
            'success'             => true,
            'code'                => $totalAcessos > 0 ? 'ALREADY_USED' : 'ALLOWED',
            'access_count_before' => $totalAcessos,
            'ingresso' => [
                'id'             => (int) $ingresso->id,
                'codigo'         => $ingresso->codigo,
                'nome'           => $ingresso->nome,
                'participante'   => $participante,
                'credencial'     => $codigo,
                'credencial_id'  => (int) $ingresso->credencial_id,
                'pedido'         => [
                    'id'        => (int) $ingresso->pedido_id,
                    'evento_id' => (int) $ingresso->evento_id,
                    'status'    => $ingresso->status,
                    'user_id'   => (int) $ingresso->user_id,
                    'frete'     => $ingresso->frete,
                    'rastreio'  => $ingresso->rastreio,
                ],
            ],
            'ticket_alimentacao' => [
                'retirado'       => $ticketRetirado,
                'retirado_em'    => $ingresso->ticket_alimentacao_em ?? null,
                // Sugestão de UX: exibir o checkbox somente quando é a 1ª leitura
                // e ainda não foi retirado. Se já retirado, mostrar "já retirado em..."
                'mostrar_checkbox' => $primeiraLeitura && !$ticketRetirado,
                'credencial_id'  => (int) $ingresso->credencial_id,
            ],
            'display'  => [
                'titulo'    => 'Acesso Sala VIP',
                'atencao'   => 'Entrada permitida apenas com pulseira inviolada.',
            ],
            'warnings' => $warnings,
        ], 200);
    }

    /**
     * Marca (ou desmarca) a retirada do ticket de alimentação vinculado a uma credencial.
     *
     * POST /api/acessos/salavip/ticket-alimentacao/{credencial_id}
     * Body (JSON opcional): { "retirado": true|false }
     * - Se "retirado" não vier, alterna o estado atual (toggle).
     */
    public function marcarTicketAlimentacao($credencial_id = null)
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->fail('Method not allowed', 405);
        }

        $credencialId = (int) $credencial_id;
        if ($credencialId <= 0) {
            return $this->failValidationErrors(['credencial_id' => 'ID de credencial inválido.']);
        }

        $credencialModel = model('CredencialModel');

        $credencial = $credencialModel->find($credencialId);
        if (!$credencial) {
            return $this->respond([
                'success' => false,
                'code'    => 'NOT_FOUND',
                'message' => 'Credencial não encontrada.',
            ], 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getRawInput() ?? [];

        if (array_key_exists('retirado', $payload)) {
            $novoEstado = (bool) $payload['retirado'];
        } else {
            $novoEstado = ((int) ($credencial->ticket_alimentacao ?? 0)) !== 1;
        }

        $ok = $credencialModel->marcarTicketAlimentacao($credencialId, $novoEstado);

        if (!$ok) {
            return $this->failServerError('Não foi possível atualizar o ticket de alimentação.');
        }

        $credencial = $credencialModel->find($credencialId);

        return $this->respond([
            'success' => true,
            'code'    => $novoEstado ? 'MARKED_RETIRADO' : 'UNMARKED_RETIRADO',
            'ticket_alimentacao' => [
                'credencial_id' => (int) $credencial->id,
                'ingresso_id'   => (int) $credencial->ingresso_id,
                'retirado'      => (int) ($credencial->ticket_alimentacao ?? 0) === 1,
                'retirado_em'   => $credencial->ticket_alimentacao_em ?? null,
            ],
        ], 200);
    }

    /**
     * Retorna o nome do participante gravado no ingresso ou, se vazio,
     * cai no nome do cliente vinculado ao user_id; se não houver cliente,
     * usa o nome fantasia/nome do expositor.
     */
    private function resolveParticipante(?string $participanteIngresso, int $userId): ?string
    {
        if (!empty($participanteIngresso)) {
            return $participanteIngresso;
        }

        $cliente = $this->clienteModel->withDeleted(true)
            ->where('usuario_id', $userId)
            ->first();

        if ($cliente && !empty($cliente->nome)) {
            return $cliente->nome;
        }

        $expositor = $this->expositorModel->withDeleted(true)
            ->where('usuario_id', $userId)
            ->first();

        if ($expositor) {
            return $expositor->nome_fantasia ?? $expositor->nome ?? null;
        }

        return null;
    }

    /**
     * Marca um order bump do pedido como usado (ou desmarca).
     *
     * POST /api/access/orderbump/{id}/usar
     * Body (JSON opcional): { "usado": true|false, "pedido_id": 123 }
     *
     * - Se "usado" não vier, alterna o estado atual.
     * - Se "pedido_id" vier, valida que o order bump pertence a esse pedido
     *   (proteção contra usar o id de outro pedido no request).
     */
    public function marcarOrderBump($id = null)
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->fail('Method not allowed', 405);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->failValidationErrors(['id' => 'ID do order bump inválido.']);
        }

        $payload  = $this->request->getJSON(true) ?? $this->request->getRawInput();
        $pedidoId = isset($payload['pedido_id']) ? (int) $payload['pedido_id'] : null;

        $ob = $pedidoId
            ? $this->pedidoOrderBumpModel->getOrderBumpDoPedido($id, $pedidoId)
            : $this->pedidoOrderBumpModel->find($id);

        if (!$ob) {
            return $this->respond([
                'success' => false,
                'code'    => 'NOT_FOUND',
                'message' => 'Order bump não encontrado para o pedido informado.',
            ], 404);
        }

        // Se o cliente enviar "usado" explicitamente respeitamos; senão alternamos.
        if (array_key_exists('usado', $payload)) {
            $novoEstado = (bool) $payload['usado'];
        } else {
            $novoEstado = ((int) ($ob->usado ?? 0)) !== 1;
        }

        $ok = $novoEstado
            ? $this->pedidoOrderBumpModel->marcarComoUsado($id)
            : $this->pedidoOrderBumpModel->desmarcarUsado($id);

        if (!$ok) {
            return $this->failServerError('Não foi possível atualizar o order bump.');
        }

        $atualizado = $this->pedidoOrderBumpModel
            ->select('pedido_order_bumps.*, order_bumps.nome, order_bumps.descricao, order_bumps.imagem, order_bumps.tipo')
            ->join('order_bumps', 'order_bumps.id = pedido_order_bumps.order_bump_id', 'left')
            ->where('pedido_order_bumps.id', $id)
            ->first();

        return $this->respond([
            'success' => true,
            'code'    => $novoEstado ? 'MARKED_USED' : 'UNMARKED_USED',
            'order_bump' => [
                'id'             => (int) $atualizado->id,
                'pedido_id'      => (int) $atualizado->pedido_id,
                'order_bump_id'  => (int) $atualizado->order_bump_id,
                'nome'           => $atualizado->nome ?? null,
                'descricao'      => $atualizado->descricao ?? null,
                'imagem'         => $atualizado->imagem ?? null,
                'tipo'           => $atualizado->tipo ?? null,
                'quantidade'     => (int) ($atualizado->quantidade ?? 1),
                'preco_unitario' => isset($atualizado->preco_unitario) ? (float) $atualizado->preco_unitario : null,
                'usado'          => (int) ($atualizado->usado ?? 0) === 1,
                'usado_em'       => $atualizado->usado_em ?? null,
            ],
        ], 200);
    }

    /**
     * Constrói o bloco "display" que substitui as mensagens de flash (para a UI consumir).
     */
    private function buildDisplayFor(string $nomeIngresso, ?string $rastreio): array
    {
        $nomeUpper    = mb_strtoupper($nomeIngresso, 'UTF-8');
        $temRastreio  = !empty($rastreio);
        $liberadoVIP  = '10:00';
        $liberadoBASE = '11:00';

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
