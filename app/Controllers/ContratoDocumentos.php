<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\ContratoDocumento;
use App\Services\ResendService;

class ContratoDocumentos extends BaseController
{
    private $documentoModel;
    private $modeloModel;
    private $contratoModel;
    private $expositorModel;
    private $eventoModel;
    private $resendService;

    public function __construct()
    {
        $this->documentoModel = new \App\Models\ContratoDocumentoModel();
        $this->modeloModel = new \App\Models\ContratoDocumentoModeloModel();
        $this->contratoModel = new \App\Models\ContratoModel();
        $this->expositorModel = new \App\Models\ExpositorModel();
        $this->eventoModel = new \App\Models\EventoModel();
        $this->resendService = new ResendService();
    }

    /**
     * Exibe documentos de um contrato
     */
    public function gerenciar(int $contratoId = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $contrato = $this->buscaContratoOu404($contratoId);
        $expositor = $this->expositorModel->find($contrato->expositor_id);
        $evento = $this->eventoModel->find($contrato->event_id);
        
        $documentos = $this->documentoModel->buscaPorContrato($contratoId);
        $modelos = $this->modeloModel->listaAtivos();

        $data = [
            'titulo' => 'Gerenciar Documentos - ' . esc($contrato->codigo ?? '#' . $contrato->id),
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
            'documentos' => $documentos,
            'modelos' => $modelos,
        ];

        return view('ContratoDocumentos/gerenciar', $data);
    }

    /**
     * Gera um novo documento a partir de um modelo
     */
    public function gerar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        try {
            $contratoId = $this->request->getPost('contrato_id');
            $modeloId = $this->request->getPost('modelo_id');

            if (empty($contratoId)) {
                $retorno['erro'] = 'ID do contrato não informado.';
                return $this->response->setJSON($retorno);
            }

            $contrato = $this->buscaContratoOu404($contratoId);

            // Verifica se modelo_id é vazio ou não numérico
            $modeloIdInt = !empty($modeloId) ? (int)$modeloId : null;

            // Gera o documento
            $documentoId = $this->documentoModel->gerarDocumento($contratoId, $modeloIdInt);

            if ($documentoId) {
                // Muda situação do contrato para aguardando_contrato
                if (in_array($contrato->situacao, ['proposta', 'proposta_aceita'])) {
                    $contrato->situacao = 'aguardando_contrato';
                    $this->contratoModel->save($contrato);
                }
                
                $retorno['sucesso'] = 'Documento gerado com sucesso!';
                $retorno['documento_id'] = $documentoId;
                return $this->response->setJSON($retorno);
            }

            // Se não gerou, verifica os erros de validação
            $erros = $this->documentoModel->errors();
            if (!empty($erros)) {
                $retorno['erro'] = 'Erro de validação: ' . implode(', ', $erros);
            } else {
                $retorno['erro'] = 'Erro ao gerar documento. Verifique se existe um modelo configurado para este tipo de contrato.';
            }
            return $this->response->setJSON($retorno);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao gerar documento: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            $retorno['erro'] = 'Erro interno: ' . $e->getMessage();
            return $this->response->setJSON($retorno);
        }
    }

    /**
     * Visualiza um documento
     */
    public function visualizar(int $id = null)
    {
        $documento = $this->documentoModel->find($id);
        
        if (!$documento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Documento não encontrado");
        }

        $contrato = $this->contratoModel->find($documento->contrato_id);

        $data = [
            'titulo' => 'Visualizar Documento',
            'documento' => $documento,
            'contrato' => $contrato,
        ];

        return view('ContratoDocumentos/visualizar', $data);
    }

    /**
     * Edita o conteúdo de um documento
     */
    public function editar(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-contratos')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para editar documentos.');
        }

        $documento = $this->documentoModel->find($id);
        
        if (!$documento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Documento não encontrado");
        }

        if (!$documento->podeEditar()) {
            return redirect()->back()->with('atencao', 'Este documento não pode mais ser editado.');
        }

        $contrato = $this->contratoModel->find($documento->contrato_id);

        $data = [
            'titulo' => 'Editar Documento',
            'documento' => $documento,
            'contrato' => $contrato,
        ];

        return view('ContratoDocumentos/editar', $data);
    }

    /**
     * Salva alterações do documento
     */
    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $conteudo = $this->request->getPost('conteudo_html');

        $documento = $this->documentoModel->find($id);

        if (!$documento || !$documento->podeEditar()) {
            $retorno['erro'] = 'Documento não pode ser editado.';
            return $this->response->setJSON($retorno);
        }

        $documento->conteudo_html = $conteudo;

        if ($this->documentoModel->save($documento)) {
            $retorno['sucesso'] = 'Documento salvo com sucesso!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao salvar documento.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Envia documento para assinatura
     */
    public function enviarParaAssinatura()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');

        $documento = $this->documentoModel->find($id);

        if (!$documento) {
            $retorno['erro'] = 'Documento não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($documento->status !== 'rascunho') {
            $retorno['erro'] = 'Apenas documentos em rascunho podem ser enviados para assinatura.';
            return $this->response->setJSON($retorno);
        }

        // Gera hash de assinatura
        $documento->gerarHashAssinatura();
        $documento->status = 'pendente_assinatura';
        $documento->data_envio = date('Y-m-d H:i:s');

        if ($this->documentoModel->save($documento)) {
            // Busca dados do contrato e expositor para enviar o email
            $contrato = $this->contratoModel->find($documento->contrato_id);
            $expositor = $this->expositorModel->find($contrato->expositor_id);
            $evento = $this->eventoModel->find($contrato->event_id);

            // Envia email para o expositor
            $this->enviaEmailAssinatura($documento, $contrato, $expositor, $evento);

            $retorno['sucesso'] = 'Documento enviado para assinatura! Um email foi enviado para o expositor.';
            $retorno['url_assinatura'] = $documento->getUrlAssinatura();
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao enviar documento para assinatura.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Envia email para o expositor com o link de assinatura
     */
    private function enviaEmailAssinatura($documento, $contrato, $expositor, $evento): void
    {
        $data = [
            'documento' => $documento,
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
            'url_assinatura' => $documento->getUrlAssinatura(),
        ];

        $mensagem = view('ContratoDocumentos/email_assinatura', $data);

        // Enviar via Resend
        $this->resendService->enviarEmail(
            $expositor->email,
            'Seu contrato está pronto para assinatura - ' . esc($evento->nome ?? 'Evento'),
            $mensagem
        );
    }

    /**
     * Página de assinatura (público para o expositor)
     */
    public function assinar(string $hash = null)
    {
        if (empty($hash)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Link inválido");
        }

        $documento = $this->documentoModel->buscaPorHash($hash);

        if (!$documento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Documento não encontrado ou link expirado");
        }

        $contrato = $this->contratoModel->find($documento->contrato_id);
        $expositor = $this->expositorModel->find($contrato->expositor_id);
        $evento = $this->eventoModel->find($contrato->event_id);

        $data = [
            'titulo' => 'Assinatura de Contrato',
            'documento' => $documento,
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
        ];

        return view('ContratoDocumentos/assinar', $data);
    }

    /**
     * Processa a assinatura do documento
     */
    public function processarAssinatura()
    {
        $retorno['token'] = csrf_hash();

        $hash = $this->request->getPost('hash');
        $nomeAssinante = $this->request->getPost('nome_assinante');
        $documentoAssinante = $this->request->getPost('documento_assinante');
        $concordo = $this->request->getPost('concordo');

        if (!$concordo) {
            $retorno['erro'] = 'Você deve concordar com os termos para assinar o documento.';
            return $this->response->setJSON($retorno);
        }

        if (empty($nomeAssinante) || empty($documentoAssinante)) {
            $retorno['erro'] = 'Preencha seu nome e documento para assinar.';
            return $this->response->setJSON($retorno);
        }

        $documento = $this->documentoModel->buscaPorHash($hash);

        if (!$documento || !$documento->podeAssinar()) {
            $retorno['erro'] = 'Documento não pode ser assinado. Verifique se o link está correto.';
            return $this->response->setJSON($retorno);
        }

        // Registra a assinatura
        $documento->status = 'assinado';
        $documento->assinado_por = $nomeAssinante;
        $documento->documento_assinante = preg_replace('/[^0-9]/', '', $documentoAssinante);
        $documento->data_assinatura = date('Y-m-d H:i:s');
        $documento->ip_assinatura = $this->request->getIPAddress();
        $documento->user_agent_assinatura = $this->request->getUserAgent()->getAgentString();

        if ($this->documentoModel->save($documento)) {
            // Busca dados do contrato e expositor para enviar o email de notificação
            $contrato = $this->contratoModel->find($documento->contrato_id);
            $expositor = $this->expositorModel->find($contrato->expositor_id);
            $evento = $this->eventoModel->find($contrato->event_id);

            // Muda situação do contrato para contrato_assinado
            if (in_array($contrato->situacao, ['aguardando_contrato', 'proposta', 'proposta_aceita'])) {
                $contrato->situacao = 'contrato_assinado';
                $contrato->data_assinatura = date('Y-m-d');
                $this->contratoModel->save($contrato);
            }

            // Envia email de notificação para a equipe de relacionamento
            $this->enviaEmailNotificacaoAssinatura($documento, $contrato, $expositor, $evento);

            $retorno['sucesso'] = 'Documento assinado com sucesso! Aguarde a confirmação pelo organizador.';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao processar assinatura.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Envia email de notificação para a equipe quando o contrato é assinado
     */
    private function enviaEmailNotificacaoAssinatura($documento, $contrato, $expositor, $evento): void
    {
        $data = [
            'documento' => $documento,
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
        ];

        $mensagem = view('ContratoDocumentos/email_contrato_assinado', $data);

        // Enviar via Resend para a equipe de relacionamento
        $this->resendService->enviarEmail(
            'relacionamento@mundodream.com.br',
            '✅ Contrato Assinado - ' . esc($expositor->nome_fantasia ?? $expositor->razao_social ?? 'Expositor') . ' - ' . esc($contrato->codigo ?? '#' . $contrato->id),
            $mensagem
        );
    }

    /**
     * Confirma documento assinado (admin)
     */
    public function confirmar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');

        $documento = $this->documentoModel->find($id);

        if (!$documento || !$documento->podeConfirmar()) {
            $retorno['erro'] = 'Documento não pode ser confirmado.';
            return $this->response->setJSON($retorno);
        }

        // Confirma o documento
        $documento->status = 'confirmado';
        $documento->data_confirmacao = date('Y-m-d H:i:s');
        $documento->confirmado_por = $this->usuarioLogado()->id;

        if ($this->documentoModel->save($documento)) {
            // Atualiza status do contrato para aguardando_credenciamento
            $contrato = $this->contratoModel->find($documento->contrato_id);
            if ($contrato && in_array($contrato->situacao, ['aguardando_contrato', 'contrato_assinado', 'proposta_aceita'])) {
                $contrato->situacao = 'aguardando_credenciamento';
                $contrato->data_assinatura = date('Y-m-d');
                $this->contratoModel->save($contrato);
            }

            // Busca dados do expositor e evento para enviar o email
            $expositor = $this->expositorModel->find($contrato->expositor_id);
            $evento = $this->eventoModel->find($contrato->event_id);

            // Envia email de confirmação para o expositor (com cópia para relacionamento)
            $this->enviaEmailConfirmacao($documento, $contrato, $expositor, $evento);

            $retorno['sucesso'] = 'Documento confirmado! O contrato foi finalizado e o expositor foi notificado.';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao confirmar documento.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Envia email de confirmação do contrato para o expositor e equipe
     */
    private function enviaEmailConfirmacao($documento, $contrato, $expositor, $evento): void
    {
        $data = [
            'documento' => $documento,
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
        ];

        $mensagem = view('ContratoDocumentos/email_contrato_confirmado', $data);

        // Enviar para o expositor e cópia para relacionamento
        $destinatarios = [
            $expositor->email,
            'relacionamento@mundodream.com.br'
        ];

        $this->resendService->enviarEmailMultiplos(
            $destinatarios,
            '🎉 Contrato Confirmado - ' . esc($evento->nome ?? 'Evento'),
            $mensagem
        );
    }

    /**
     * Cancela um documento
     */
    public function cancelar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');

        $documento = $this->documentoModel->find($id);

        if (!$documento) {
            $retorno['erro'] = 'Documento não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($documento->status === 'confirmado') {
            $retorno['erro'] = 'Documentos confirmados não podem ser cancelados.';
            return $this->response->setJSON($retorno);
        }

        $documento->status = 'cancelado';

        if ($this->documentoModel->save($documento)) {
            $retorno['sucesso'] = 'Documento cancelado!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao cancelar documento.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Busca contrato ou retorna 404
     */
    private function buscaContratoOu404(int $id = null)
    {
        if (!$id || !$contrato = $this->contratoModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Contrato não encontrado");
        }
        return $contrato;
    }

    // ========================================
    // CRUD DE MODELOS DE DOCUMENTO
    // ========================================

    /**
     * Lista modelos de documento
     */
    public function modelos()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-contratos')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para gerenciar modelos.');
        }

        $modelos = $this->modeloModel->withDeleted(true)->orderBy('nome', 'ASC')->findAll();

        $data = [
            'titulo' => 'Modelos de Documento de Contrato',
            'modelos' => $modelos,
        ];

        return view('ContratoDocumentos/modelos', $data);
    }

    /**
     * Formulário para criar modelo
     */
    public function criarModelo()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-contratos')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para criar modelos.');
        }

        $data = [
            'titulo' => 'Criar Modelo de Documento',
            'modelo' => new \App\Entities\ContratoDocumentoModelo(),
            'tiposItem' => $this->modeloModel::getTiposItem(),
            'variaveis' => \App\Entities\ContratoDocumentoModelo::getVariaveisPadrao(),
        ];

        return view('ContratoDocumentos/criar_modelo', $data);
    }

    /**
     * Salva novo modelo
     */
    public function salvarModelo()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $modelo = new \App\Entities\ContratoDocumentoModelo($post);

        if ($this->modeloModel->save($modelo)) {
            $retorno['sucesso'] = 'Modelo salvo com sucesso!';
            $retorno['id'] = $post['id'] ?? $this->modeloModel->getInsertID();
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao salvar modelo.';
        $retorno['erros_model'] = $this->modeloModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Edita modelo existente
     */
    public function editarModelo(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-contratos')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para editar modelos.');
        }

        $modelo = $this->modeloModel->find($id);

        if (!$modelo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Modelo não encontrado");
        }

        $data = [
            'titulo' => 'Editar Modelo de Documento',
            'modelo' => $modelo,
            'tiposItem' => $this->modeloModel::getTiposItem(),
            'variaveis' => \App\Entities\ContratoDocumentoModelo::getVariaveisPadrao(),
        ];

        return view('ContratoDocumentos/criar_modelo', $data);
    }

    /**
     * Exclui modelo
     */
    public function excluirModelo(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('excluir-contratos')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para excluir modelos.');
        }

        $modelo = $this->modeloModel->find($id);

        if (!$modelo) {
            return redirect()->back()->with('erro', 'Modelo não encontrado.');
        }

        $this->modeloModel->delete($id);

        return redirect()->to(site_url('contratodocumentos/modelos'))->with('sucesso', 'Modelo excluído com sucesso!');
    }
}

