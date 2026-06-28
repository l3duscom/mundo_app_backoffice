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
}
