<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use chillerlan\QRCode\QRCode;

class Meets extends BaseController
{
    private $queueModel;
    private $eventoModel;

    public function __construct()
    {
        $this->queueModel  = new \App\Models\QueueModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    /**
     * Lista as reservas de Meet & Greet do usuário autenticado.
     * GET /api/meets
     * GET /api/meets?event_id=17   (filtra por evento)
     */
    public function index()
    {
        $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;

        if (!$usuarioAutenticado) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Não autenticado.',
            ]);
        }

        $userId  = $usuarioAutenticado['user_id'];
        $eventId = $this->request->getGet('event_id');

        try {
            $reservas = $this->queueModel
                ->select([
                    'queue_meet.id',
                    'queue_meet.meet_id',
                    'queue_meet.ordem',
                    'queue_meet.code',
                    'queue_meet.status',
                    'queue_meet.ingresso_id',
                    'queue_meet.created_at',
                    'meet.artista',
                    'meet.dia',
                    'meet.data_meet',
                    'meet.hora_inicial',
                    'meet.hora_final',
                    'meet.tipo',
                    'meet.event_id',
                    'eventos.nome AS evento_nome',
                    'eventos.data_inicio AS evento_data_inicio',
                    'eventos.data_fim AS evento_data_fim',
                ])
                ->join('meet', 'meet.id = queue_meet.meet_id')
                ->join('eventos', 'eventos.id = meet.event_id', 'left')
                ->where('queue_meet.user_id', $userId);

            if ($eventId) {
                $reservas->where('meet.event_id', (int) $eventId);
            }

            $lista = $reservas->orderBy('meet.data_meet', 'DESC')->findAll();

            $data = [];
            foreach ($lista as $r) {
                $pendente = strtoupper((string) ($r->status ?? '')) === 'PENDENTE';

                $data[] = [
                    'id'           => $r->id,
                    'meet_id'      => $r->meet_id,
                    'code'         => $r->code,
                    'status'       => $r->status,
                    'ordem'        => $pendente ? null : (int) $r->ordem,
                    'pendente'     => $pendente,
                    'qr_code'      => (new QRCode)->render($r->code),
                    'artista'      => $r->artista,
                    'dia'          => $r->dia,
                    'data_meet'    => $r->data_meet,
                    'hora_inicial' => $r->hora_inicial,
                    'hora_final'   => $r->hora_final,
                    'tipo'         => $r->tipo,
                    'ingresso_id'  => $r->ingresso_id,
                    'evento'       => [
                        'id'          => $r->event_id,
                        'nome'        => $r->evento_nome,
                        'data_inicio' => $r->evento_data_inicio,
                        'data_fim'    => $r->evento_data_fim,
                    ],
                    'created_at'   => $r->created_at,
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data'    => $data,
                'total'   => count($data),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'API Meets: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar reservas.',
                'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno',
            ]);
        }
    }

    /**
     * Valida uma reserva de Meet & Greet pelo código lido (portaria/staff do M&G).
     *
     * POST /api/meets/validar
     * Body (JSON): { "code": "ABC123", "operador": 9 }
     *
     * Regras:
     * - Localiza a reserva por queue_meet.code.
     * - Se a reserva já estiver validada (status = VALIDADO ou ordem preenchida),
     *   retorna "ALREADY_VALIDATED" com os dados da reserva (mas não modifica nada).
     * - Se ainda não validada, calcula a próxima "ordem" (max(ordem) do meet + 1),
     *   marca status = VALIDADO e retorna "VALIDATED" com a ordem atribuída.
     */
    public function validar()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Método não permitido',
            ]);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getRawInput();

        $code     = strtoupper(trim((string) ($payload['code'] ?? '')));
        $operador = isset($payload['operador']) ? (int) $payload['operador'] : null;

        if ($code === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'code'    => 'INVALID_PAYLOAD',
                'message' => 'Código não informado.',
                'errors'  => ['code' => 'O campo code é obrigatório.'],
            ]);
        }

        try {
            $reserva = $this->queueModel->recuperaPorCodigo($code);

            if (!$reserva) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'code'    => 'NOT_FOUND',
                    'message' => 'Código não encontrado.',
                ]);
            }

            $jaValidada = !empty($reserva->ordem)
                || strtoupper((string) $reserva->status) === 'VALIDADO';

            if ($jaValidada) {
                return $this->response->setJSON([
                    'success'      => true,
                    'code'         => 'ALREADY_VALIDATED',
                    'ja_validado'  => true,
                    'message'      => 'Esta reserva já foi validada.',
                    'reserva'      => $this->payloadReserva($reserva),
                ]);
            }

            $ultima  = $this->queueModel->recuperaOrdem((int) $reserva->meet_id);
            $proxima = ($ultima && !empty($ultima->ordem)) ? (int) $ultima->ordem + 1 : 1;

            $ok = $this->queueModel
                ->protect(false)
                ->where('id', $reserva->id)
                ->set([
                    'ordem'  => $proxima,
                    'status' => 'VALIDADO',
                ])
                ->update();

            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível validar a reserva.',
                ]);
            }

            $reserva->ordem  = $proxima;
            $reserva->status = 'VALIDADO';

            return $this->response->setJSON([
                'success'   => true,
                'code'      => 'VALIDATED',
                'operador'  => $operador,
                'message'   => 'Reserva validada com sucesso.',
                'reserva'   => $this->payloadReserva($reserva),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'API Meets validar: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao validar reserva.',
                'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno',
            ]);
        }
    }

    private function payloadReserva($r): array
    {
        return [
            'id'              => isset($r->id) ? (int) $r->id : null,
            'code'            => $r->code ?? null,
            'ordem'           => isset($r->ordem) ? (int) $r->ordem : null,
            'status'          => $r->status ?? null,
            'artista'         => $r->artista ?? null,
            'dia'             => $r->dia ?? null,
            'tipo'            => $r->tipo ?? null,
            'data_meet'       => $r->data_meet ?? null,
            'hora_inicial'    => $r->hora_inicial ?? null,
            'usuario_id'      => isset($r->user_id) ? (int) $r->user_id : null,
            'usuario_nome'    => $r->usuario_nome ?? null,
            'usuario_email'   => $r->usuario_email ?? null,
            'ingresso_id'     => isset($r->ingresso_id) ? (int) $r->ingresso_id : null,
            'ingresso_nome'   => $r->ingresso_nome ?? null,
            'ingresso_codigo' => $r->ingresso_codigo ?? null,
            'event_id'        => isset($r->event_id) ? (int) $r->event_id : null,
        ];
    }
}
