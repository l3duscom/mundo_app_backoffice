<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Meets extends BaseController
{
    private $queueModel;
    private $meetModel;

    public function __construct()
    {
        $this->queueModel = new \App\Models\QueueModel();
        $this->meetModel = new \App\Models\MeetModel();
    }

    public function validar()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('access-controll')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Validar Meet & Greet',
        ];

        return view('Meets/validar', $data);
    }

    public function validarCode()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        if (!$this->usuarioLogado()->temPermissaoPara('access-controll')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Sem permissão.',
            ]);
        }

        $code = trim((string) ($this->request->getPost('code') ?? ''));
        $code = strtoupper($code);

        if ($code === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Código não informado.',
            ]);
        }

        $reserva = $this->queueModel->recuperaPorCodigo($code);

        if (!$reserva) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Código não encontrado.',
            ]);
        }

        if (!empty($reserva->ordem) || strtoupper((string)$reserva->status) === 'VALIDADO') {
            return $this->response->setJSON([
                'success' => false,
                'ja_validado' => true,
                'message' => 'Esta reserva já foi validada.',
                'data' => $this->payloadReserva($reserva),
            ]);
        }

        $ultima = $this->queueModel->recuperaOrdem((int) $reserva->meet_id);
        $proxima = ($ultima && !empty($ultima->ordem)) ? (int) $ultima->ordem + 1 : 1;

        $this->queueModel
            ->protect(false)
            ->where('id', $reserva->id)
            ->set([
                'ordem'  => $proxima,
                'status' => 'VALIDADO',
            ])
            ->update();

        $reserva->ordem = $proxima;
        $reserva->status = 'VALIDADO';

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Reserva validada com sucesso.',
            'data' => $this->payloadReserva($reserva),
        ]);
    }

    private function payloadReserva($r): array
    {
        return [
            'code'           => $r->code,
            'ordem'          => $r->ordem,
            'status'         => $r->status,
            'artista'        => $r->artista,
            'dia'            => $r->dia,
            'tipo'           => $r->tipo,
            'data_meet'      => $r->data_meet,
            'hora_inicial'   => $r->hora_inicial,
            'usuario_nome'   => $r->usuario_nome ?? null,
            'usuario_email'  => $r->usuario_email ?? null,
            'ingresso_nome'  => $r->ingresso_nome ?? null,
            'ingresso_codigo' => $r->ingresso_codigo ?? null,
        ];
    }
}
