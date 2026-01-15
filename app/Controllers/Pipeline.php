<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Lead;
use App\Entities\Expositor;

class Pipeline extends BaseController
{
    protected $leadModel;
    protected $atividadeModel;
    protected $expositorModel;
    protected $contratoModel;
    protected $usuarioModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->leadModel = new \App\Models\LeadModel();
        $this->atividadeModel = new \App\Models\LeadAtividadeModel();
        $this->expositorModel = new \App\Models\ExpositorModel();
        $this->contratoModel = new \App\Models\ContratoModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    /**
     * Redireciona para o Kanban
     */
    public function index()
    {
        return redirect()->to('pipeline/kanban');
    }

    /**
     * Exibe a visão Kanban do Pipeline
     */
    public function kanban()
    {
        $db = \Config\Database::connect();
        
        $eventos = $db->table('eventos')
            ->select('id, nome')
            ->where('deleted_at IS NULL')
            ->get()
            ->getResultArray();
            
        $vendedores = $db->table('usuarios')
            ->select('id, nome')
            ->where('deleted_at IS NULL')
            ->where('ativo', 1)
            ->get()
            ->getResultArray();

        $data = [
            'titulo'     => 'Pipeline de Vendas',
            'eventos'    => $eventos,
            'vendedores' => $vendedores,
        ];

        return view('Pipeline/kanban', $data);
    }

    /**
     * Recupera leads agrupados por etapa para o Kanban (AJAX)
     */
    public function recuperaLeadsKanban()
    {
        $eventoId = $this->request->getGet('evento_id');
        $vendedorId = $this->request->getGet('vendedor_id');

        $eventoId = $eventoId ? (int) $eventoId : null;
        $vendedorId = $vendedorId ? (int) $vendedorId : null;

        $dados = $this->leadModel->buscaLeadsPorEtapa($eventoId, $vendedorId);
        $estatisticas = $this->leadModel->getEstatisticas($eventoId);

        return $this->response->setJSON([
            'etapas'       => $dados,
            'estatisticas' => $estatisticas,
        ]);
    }

    /**
     * Formulário de criação de lead
     */
    public function criar()
    {
        $db = \Config\Database::connect();
        
        $eventos = $db->table('eventos')
            ->select('id, nome')
            ->where('deleted_at IS NULL')
            ->get()
            ->getResultArray();
            
        $vendedores = $db->table('usuarios')
            ->select('id, nome')
            ->where('deleted_at IS NULL')
            ->where('ativo', 1)
            ->get()
            ->getResultArray();

        $data = [
            'titulo'     => 'Novo Lead',
            'eventos'    => $eventos,
            'vendedores' => $vendedores,
        ];

        return view('Pipeline/criar', $data);
    }

    /**
     * Cadastra novo lead
     */
    public function cadastrar()
    {
        $lead = new Lead($this->request->getPost());
        $lead->codigo = $this->leadModel->gerarCodigo();

        // Limpa documento
        if ($lead->documento) {
            $lead->documento = preg_replace('/[^0-9]/', '', $lead->documento);
        }

        // Limpa valor
        $valorEstimado = $this->request->getPost('valor_estimado');
        if ($valorEstimado) {
            $lead->valor_estimado = $this->limparValorMonetario($valorEstimado);
        }

        if (!$this->leadModel->save($lead)) {
            return redirect()->back()
                ->with('errors', $this->leadModel->errors())
                ->withInput();
        }

        $leadId = $this->leadModel->getInsertID();

        // Registra atividade de criação
        $this->atividadeModel->registrarAtividade(
            $leadId,
            'criacao',
            'Lead criado no pipeline'
        );

        return redirect()->to('pipeline/exibir/' . $leadId)
            ->with('sucesso', 'Lead criado com sucesso!');
    }

    /**
     * Exibe detalhes do lead
     */
    public function exibir(?int $id = null)
    {
        $lead = $this->buscaLeadOu404($id);

        // Busca vendedor
        $vendedor = null;
        if ($lead->vendedor_id) {
            $vendedor = $this->usuarioModel->find($lead->vendedor_id);
        }

        // Busca evento
        $evento = null;
        if ($lead->evento_id) {
            $evento = $this->eventoModel->find($lead->evento_id);
        }

        // Busca atividades
        $atividades = $this->atividadeModel->buscarPorLead($id);

        // Busca expositor se convertido
        $expositor = null;
        if ($lead->expositor_id) {
            $expositor = $this->expositorModel->find($lead->expositor_id);
        }

        // Busca contrato se convertido
        $contrato = null;
        if ($lead->contrato_id) {
            $contrato = $this->contratoModel->find($lead->contrato_id);
        }

        $data = [
            'titulo'     => 'Lead: ' . $lead->getNomeExibicao(),
            'lead'       => $lead,
            'vendedor'   => $vendedor,
            'evento'     => $evento,
            'atividades' => $atividades,
            'expositor'  => $expositor,
            'contrato'   => $contrato,
        ];

        return view('Pipeline/exibir', $data);
    }

    /**
     * Formulário de edição do lead
     */
    public function editar(?int $id = null)
    {
        $lead = $this->buscaLeadOu404($id);
        
        $db = \Config\Database::connect();
        
        $eventos = $db->table('eventos')
            ->select('id, nome')
            ->where('deleted_at IS NULL')
            ->get()
            ->getResultArray();
            
        $vendedores = $db->table('usuarios')
            ->select('id, nome')
            ->where('deleted_at IS NULL')
            ->where('ativo', 1)
            ->get()
            ->getResultArray();

        $data = [
            'titulo'     => 'Editar Lead',
            'lead'       => $lead,
            'eventos'    => $eventos,
            'vendedores' => $vendedores,
        ];

        return view('Pipeline/editar', $data);
    }

    /**
     * Atualiza lead
     */
    public function atualizar()
    {
        $id = $this->request->getPost('id');
        $lead = $this->buscaLeadOu404($id);

        $dados = $this->request->getPost();

        // Limpa documento
        if (isset($dados['documento'])) {
            $dados['documento'] = preg_replace('/[^0-9]/', '', $dados['documento']);
        }

        // Limpa valor
        if (isset($dados['valor_estimado'])) {
            $dados['valor_estimado'] = $this->limparValorMonetario($dados['valor_estimado']);
        }

        $lead->fill($dados);

        if (!$this->leadModel->save($lead)) {
            return redirect()->back()
                ->with('errors', $this->leadModel->errors())
                ->withInput();
        }

        return redirect()->to('pipeline/exibir/' . $id)
            ->with('sucesso', 'Lead atualizado com sucesso!');
    }

    /**
     * Altera etapa do lead (AJAX - drag/drop)
     */
    public function alterarEtapa()
    {
        $id = $this->request->getPost('id');
        $novaEtapa = $this->request->getPost('etapa');

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            return $this->response->setJSON(['erro' => 'Lead não encontrado.']);
        }

        $etapaAnterior = $lead->etapa;

        // Se está movendo para "perdido", exige motivo
        if ($novaEtapa === 'perdido' && $etapaAnterior !== 'perdido') {
            $motivoPerda = $this->request->getPost('motivo_perda');
            if (!$motivoPerda) {
                return $this->response->setJSON(['erro' => 'Informe o motivo da perda.', 'requer_motivo' => true]);
            }
            $this->leadModel->update($id, ['motivo_perda' => $motivoPerda]);
        }

        // Atualiza etapa
        $this->leadModel->update($id, ['etapa' => $novaEtapa]);

        // Registra atividade de mudança
        $this->atividadeModel->registrarAtividade(
            $id,
            'mudanca_etapa',
            'Movido de "' . \App\Models\LeadAtividadeModel::getNomeEtapa($etapaAnterior) . '" para "' . \App\Models\LeadAtividadeModel::getNomeEtapa($novaEtapa) . '"',
            $etapaAnterior,
            $novaEtapa
        );

        return $this->response->setJSON(['sucesso' => true, 'mensagem' => 'Etapa atualizada com sucesso!']);
    }

    /**
     * Registra atividade no lead (AJAX)
     */
    public function registrarAtividade()
    {
        $leadId = $this->request->getPost('lead_id');
        $tipo = $this->request->getPost('tipo');
        $descricao = $this->request->getPost('descricao');

        $lead = $this->leadModel->find($leadId);
        if (!$lead) {
            return $this->response->setJSON(['erro' => 'Lead não encontrado.']);
        }

        $this->atividadeModel->registrarAtividade($leadId, $tipo, $descricao);

        // Atualiza updated_at do lead
        $this->leadModel->update($leadId, ['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON(['sucesso' => true, 'mensagem' => 'Atividade registrada!']);
    }

    /**
     * Converte lead em expositor e cria contrato inicial
     */
    public function converterEmExpositor()
    {
        $leadId = $this->request->getPost('lead_id');
        $lead = $this->buscaLeadOu404($leadId);

        // Verifica se já foi convertido
        if ($lead->isConvertido()) {
            return redirect()->back()->with('erro', 'Este lead já foi convertido em expositor.');
        }

        // Verifica se está na etapa correta
        if ($lead->etapa !== 'ganho') {
            return redirect()->back()->with('erro', 'O lead precisa estar na etapa "Ganho" para ser convertido.');
        }

        // Verifica se já existe expositor com mesmo documento
        $expositorExistente = null;
        if ($lead->documento) {
            $expositorExistente = $this->expositorModel->buscaPorDocumento($lead->documento);
        }

        // Se não existe, verifica por email
        if (!$expositorExistente && $lead->email) {
            $expositorExistente = $this->expositorModel->buscaPorEmail($lead->email);
        }

        $expositorId = null;

        if ($expositorExistente) {
            // Usa expositor existente
            $expositorId = $expositorExistente->id;
        } else {
            // Cria novo expositor
            $expositor = new Expositor([
                'tipo_pessoa'   => $lead->tipo_pessoa,
                'nome'          => $lead->nome,
                'nome_fantasia' => $lead->nome_fantasia,
                'documento'     => $lead->documento,
                'email'         => $lead->email,
                'telefone'      => $lead->telefone,
                'celular'       => $lead->celular,
                'segmento'      => $lead->segmento,
                'instagram'     => $lead->instagram,
                'observacoes'   => $lead->observacoes,
                'ativo'         => 1,
            ]);

            // Desativa validação de campos obrigatórios que podem estar vazios
            $this->expositorModel->skipValidation(true);
            
            if (!$this->expositorModel->save($expositor)) {
                return redirect()->back()
                    ->with('erro', 'Erro ao criar expositor: ' . implode(', ', $this->expositorModel->errors()));
            }

            $expositorId = $this->expositorModel->getInsertID();
        }

        // Cria contrato inicial
        $contratoData = [
            'codigo'        => $this->contratoModel->gerarCodigo(),
            'expositor_id'  => $expositorId,
            'event_id'      => $lead->evento_id,
            'situacao'      => 'proposta',
            'valor_final'   => $lead->valor_estimado,
            'observacoes'   => 'Contrato gerado a partir do lead ' . $lead->codigo,
        ];

        if (!$this->contratoModel->insert($contratoData)) {
            return redirect()->back()
                ->with('erro', 'Erro ao criar contrato.');
        }

        $contratoId = $this->contratoModel->getInsertID();

        // Atualiza lead com IDs
        $this->leadModel->update($leadId, [
            'expositor_id' => $expositorId,
            'contrato_id'  => $contratoId,
        ]);

        // Registra atividade
        $this->atividadeModel->registrarAtividade(
            $leadId,
            'conversao',
            'Lead convertido em Expositor (ID: ' . $expositorId . ') e Contrato (ID: ' . $contratoId . ')'
        );

        return redirect()->to('contratos/exibir/' . $contratoId)
            ->with('sucesso', 'Lead convertido com sucesso! Expositor e contrato criados.');
    }

    /**
     * Exclui lead (soft delete)
     */
    public function excluir(?int $id = null)
    {
        $lead = $this->buscaLeadOu404($id);

        if ($this->request->getMethod() === 'post') {
            $this->leadModel->delete($id);
            return redirect()->to('pipeline/kanban')
                ->with('sucesso', 'Lead excluído com sucesso!');
        }

        // GET - mostra confirmação
        return view('Pipeline/excluir', [
            'titulo' => 'Excluir Lead',
            'lead'   => $lead,
        ]);
    }

    /**
     * Busca lead ou retorna 404
     */
    private function buscaLeadOu404(?int $id)
    {
        if (!$id || !$lead = $this->leadModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Lead não encontrado.');
        }

        return $lead;
    }

    /**
     * Limpa valor monetário
     */
    private function limparValorMonetario(string $valor): float
    {
        $valor = preg_replace('/[^0-9,.]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }
}
