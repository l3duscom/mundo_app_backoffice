<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Agente;
use App\Models\AgenteModel;
use App\Models\AgenteAnexoModel;
use App\Models\ArtistaAgenteModel;

class Agentes extends BaseController
{
    protected $agenteModel;
    protected $anexoModel;

    public function __construct()
    {
        $this->agenteModel = new AgenteModel();
        $this->anexoModel = new AgenteAnexoModel();
    }

    /**
     * Listagem de agentes
     */
    public function index()
    {
        $data = [
            'titulo' => 'Agentes e Agências',
            'agentes' => $this->agenteModel->orderBy('nome', 'ASC')->findAll(),
            'tipos' => Agente::TIPOS,
        ];

        return view('Agentes/index', $data);
    }

    /**
     * Exibir agente
     */
    public function exibir(int $id = null)
    {
        $agente = $this->buscaAgenteOu404($id);

        $data = [
            'titulo' => $agente->getNomeExibicao(),
            'agente' => $agente,
            'anexos' => $this->anexoModel->buscarPorAgente($id),
            'artistas' => $this->agenteModel->buscaArtistasDoAgente($id),
            'tipos' => Agente::TIPOS,
        ];

        return view('Agentes/exibir', $data);
    }

    /**
     * Criar agente
     */
    public function criar()
    {
        $data = [
            'titulo' => 'Novo Agente',
            'agente' => new Agente(),
            'tipos' => Agente::TIPOS,
        ];

        return view('Agentes/criar', $data);
    }

    /**
     * Cadastrar agente
     */
    public function cadastrar()
    {
        $isAjax = $this->request->isAJAX();

        $agente = new Agente($this->request->getPost());

        if (!$this->agenteModel->insert($agente)) {
            $erro = 'Verifique os campos: ' . implode(', ', $this->agenteModel->errors());
            
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        $agenteId = $this->agenteModel->getInsertID();

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Agente cadastrado com sucesso!',
                'redirect' => site_url("agentes/exibir/{$agenteId}")
            ]);
        }
        
        return redirect()->to("agentes/exibir/{$agenteId}")->with('sucesso', 'Agente cadastrado com sucesso!');
    }

    /**
     * Editar agente
     */
    public function editar(int $id = null)
    {
        $agente = $this->buscaAgenteOu404($id);

        $data = [
            'titulo' => 'Editar ' . $agente->getNomeExibicao(),
            'agente' => $agente,
            'tipos' => Agente::TIPOS,
        ];

        return view('Agentes/editar', $data);
    }

    /**
     * Atualizar agente
     */
    public function atualizar()
    {
        $isAjax = $this->request->isAJAX();

        $id = $this->request->getPost('id');
        $agente = $this->buscaAgenteOu404($id);

        $agente->fill($this->request->getPost());

        if (!$this->agenteModel->save($agente)) {
            $erro = 'Verifique os campos: ' . implode(', ', $this->agenteModel->errors());
            
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Agente atualizado com sucesso!',
                'redirect' => site_url("agentes/exibir/{$id}")
            ]);
        }
        
        return redirect()->to("agentes/exibir/{$id}")->with('sucesso', 'Agente atualizado com sucesso!');
    }

    /**
     * Excluir agente (soft delete)
     */
    public function excluir(int $id = null)
    {
        $agente = $this->buscaAgenteOu404($id);
        
        $this->agenteModel->delete($id);

        return redirect()->to('agentes')->with('sucesso', 'Agente excluído com sucesso!');
    }

    /**
     * Upload de anexo
     */
    public function uploadAnexo()
    {
        $retorno['token'] = csrf_hash();

        $agenteId = $this->request->getPost('agente_id');
        $descricao = $this->request->getPost('descricao');
        $arquivo = $this->request->getFile('arquivo');

        if (!$arquivo || !$arquivo->isValid()) {
            $retorno['erro'] = 'Arquivo inválido';
            return $this->response->setJSON($retorno);
        }

        // Verificar tamanho (10MB)
        if ($arquivo->getSize() > 10 * 1024 * 1024) {
            $retorno['erro'] = 'Arquivo muito grande (máx: 10MB)';
            return $this->response->setJSON($retorno);
        }

        $anexoId = $this->anexoModel->salvarAnexo($agenteId, $arquivo, $descricao);

        if (!$anexoId) {
            $retorno['erro'] = 'Erro ao salvar anexo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Anexo enviado com sucesso!';
        $retorno['id'] = $anexoId;
        return $this->response->setJSON($retorno);
    }

    /**
     * Download de anexo
     */
    public function downloadAnexo(int $id = null)
    {
        $anexo = $this->anexoModel->find($id);
        if (!$anexo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anexo não encontrado');
        }

        $path = WRITEPATH . 'uploads/agentes/anexos/' . $anexo->arquivo;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arquivo não encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', $anexo->tipo)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $anexo->nome_arquivo . '"')
            ->setBody(file_get_contents($path));
    }

    /**
     * Visualizar anexo (inline)
     */
    public function visualizarAnexo(int $id = null)
    {
        $anexo = $this->anexoModel->find($id);
        if (!$anexo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anexo não encontrado');
        }

        $path = WRITEPATH . 'uploads/agentes/anexos/' . $anexo->arquivo;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arquivo não encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', $anexo->tipo)
            ->setHeader('Content-Disposition', 'inline; filename="' . $anexo->nome_arquivo . '"')
            ->setBody(file_get_contents($path));
    }

    /**
     * Remover anexo
     */
    public function removerAnexo()
    {
        $retorno['token'] = csrf_hash();

        $anexoId = $this->request->getPost('anexo_id');

        if (!$this->anexoModel->removerAnexo($anexoId)) {
            $retorno['erro'] = 'Erro ao remover anexo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Anexo removido!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Pesquisar agentes (para autocomplete/select2)
     */
    public function pesquisar()
    {
        $termo = $this->request->getGet('q') ?? '';
        
        $agentes = $this->agenteModel->pesquisar($termo);
        
        $results = [];
        foreach ($agentes as $a) {
            $results[] = [
                'id' => $a->id,
                'text' => $a->getNomeExibicao() . ' (' . $a->getTipoLabel() . ')',
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    /**
     * Busca agente ou 404
     */
    private function buscaAgenteOu404(int $id = null)
    {
        if (!$id || !$agente = $this->agenteModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Agente não encontrado');
        }
        return $agente;
    }
}
