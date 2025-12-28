<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Artista;
use App\Models\ArtistaModel;
use App\Models\ArtistaAgenteModel;
use App\Models\ArtistaContratacaoModel;

class Artistas extends BaseController
{
    protected $artistaModel;
    protected $agenteModel;
    protected $contratacaoModel;

    public function __construct()
    {
        $this->artistaModel = new ArtistaModel();
        $this->agenteModel = new ArtistaAgenteModel();
        $this->contratacaoModel = new ArtistaContratacaoModel();
    }

    /**
     * Listagem de artistas
     */
    public function index()
    {
        $data = [
            'titulo' => 'Artistas',
        ];

        return view('Artistas/index', $data);
    }

    /**
     * Recupera artistas para DataTable
     */
    public function recuperaArtistas()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $builder = $this->artistaModel
            ->select('artistas.*')
            ->withDeleted(true);

        $ativo = $this->request->getGet('ativo');
        if ($ativo !== null && $ativo !== '') {
            $builder->where('artistas.ativo', $ativo);
        }

        $artistas = $builder->orderBy('nome_artistico', 'ASC')->findAll();

        $data = [];
        foreach ($artistas as $artista) {
            $data[] = [
                'id' => $artista->id,
                'nome_artistico' => esc($artista->nome_artistico),
                'genero_musical' => esc($artista->genero_musical ?? '-'),
                'telefone' => esc($artista->telefone ?? '-'),
                'email' => esc($artista->email ?? '-'),
                'status' => $artista->exibeStatus(),
                'acoes' => $this->montaBotoes($artista),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        $artista = new Artista();

        $data = [
            'titulo' => 'Novo Artista',
            'artista' => $artista,
            'agentesVinculados' => [],
        ];

        return view('Artistas/criar', $data);
    }

    /**
     * Processa cadastro
     */
    public function cadastrar()
    {
        // Aceita tanto AJAX quanto POST normal
        $isAjax = $this->request->isAJAX();
        
        $artista = new Artista($this->request->getPost());

        if (!$this->artistaModel->insert($artista)) {
            $erro = 'Verifique os campos: ' . implode(', ', $this->artistaModel->errors());
            
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        $artistaId = $this->artistaModel->getInsertID();

        // Salvar agentes vinculados
        $this->salvarAgentes($artistaId, $this->request->getPost('agentes'));

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Artista cadastrado com sucesso!',
                'redirect' => site_url("artistas/exibir/{$artistaId}")
            ]);
        }
        
        return redirect()->to("artistas/exibir/{$artistaId}")->with('sucesso', 'Artista cadastrado com sucesso!');
    }

    /**
     * Exibir artista
     */
    public function exibir(int $id = null)
    {
        $artista = $this->buscaArtistaOu404($id);
        
        $data = [
            'titulo' => $artista->nome_artistico,
            'artista' => $artista,
            'agentesVinculados' => $this->agenteModel->buscarPorArtista($id),
            'contratacoes' => $this->contratacaoModel->buscaPorArtista($id),
        ];

        return view('Artistas/exibir', $data);
    }

    /**
     * Formulário de edição
     */
    public function editar(int $id = null)
    {
        $artista = $this->buscaArtistaOu404($id);

        $data = [
            'titulo' => 'Editar: ' . $artista->nome_artistico,
            'artista' => $artista,
            'agentesVinculados' => $this->agenteModel->buscarPorArtista($id),
        ];

        return view('Artistas/editar', $data);
    }

    /**
     * Processa atualização
     */
    public function atualizar()
    {
        $isAjax = $this->request->isAJAX();

        $id = $this->request->getPost('id');
        $artista = $this->buscaArtistaOu404($id);

        $artista->fill($this->request->getPost());

        if (!$this->artistaModel->save($artista)) {
            $erro = 'Verifique os campos: ' . implode(', ', $this->artistaModel->errors());
            
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        // Atualizar agentes vinculados
        $this->salvarAgentes($id, $this->request->getPost('agentes'));

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Artista atualizado com sucesso!',
                'redirect' => site_url("artistas/exibir/{$id}")
            ]);
        }
        
        return redirect()->to("artistas/exibir/{$id}")->with('sucesso', 'Artista atualizado com sucesso!');
    }

    /**
     * Excluir artista (soft delete)
     */
    public function excluir(int $id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $artista = $this->buscaArtistaOu404($id);

        if (!$this->artistaModel->delete($id)) {
            $retorno['erro'] = 'Erro ao excluir artista';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Artista excluído com sucesso!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Salva agentes vinculados ao artista
     */
    private function salvarAgentes(int $artistaId, ?array $agentes): void
    {
        // Remove vínculos antigos
        $db = \Config\Database::connect();
        $db->table('artista_agentes')->where('artista_id', $artistaId)->delete();

        if (empty($agentes)) return;

        foreach ($agentes as $ag) {
            if (empty($ag['agente_id'])) continue;

            $this->agenteModel->vincular(
                $artistaId,
                (int) $ag['agente_id'],
                $ag['funcao'] ?? 'agente',
                isset($ag['principal']) && $ag['principal']
            );
        }
    }

    /**
     * Monta botões de ação
     */
    private function montaBotoes($artista): string
    {
        $btns = '<div class="btn-group">';
        $btns .= '<a href="' . site_url("artistas/exibir/{$artista->id}") . '" class="btn btn-sm btn-outline-info" title="Ver"><i class="bx bx-show"></i></a>';
        $btns .= '<a href="' . site_url("artistas/editar/{$artista->id}") . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a>';
        $btns .= '</div>';
        return $btns;
    }

    /**
     * Busca artista ou 404
     */
    private function buscaArtistaOu404(int $id = null)
    {
        if (!$id || !$artista = $this->artistaModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Artista não encontrado');
        }
        return $artista;
    }
}
