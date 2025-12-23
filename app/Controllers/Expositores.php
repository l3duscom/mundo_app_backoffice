<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\ValidacoesTrait;
use App\Entities\Expositor;
use App\Services\ResendService;

class Expositores extends BaseController
{

    use ValidacoesTrait;

    private $expositorModel;
    private $usuarioModel;
    private $grupoUsuarioModel;
    private $resendService;

    public function __construct()
    {
        $this->expositorModel = new \App\Models\ExpositorModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
        $this->resendService = new ResendService();
    }

    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando os expositores',
        ];

        return view('Expositores/index', $data);
    }

    public function recuperaExpositores()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $atributos = [
            'id',
            'tipo_pessoa',
            'nome',
            'nome_fantasia',
            'documento',
            'email',
            'telefone',
            'tipo_expositor',
            'segmento',
            'ativo',
            'deleted_at'
        ];

        $expositores = $this->expositorModel->select($atributos)
            ->withDeleted(true)
            ->orderBy('id', 'DESC')
            ->findAll();

        $data = [];

        foreach ($expositores as $expositor) {
            $nomeExibicao = $expositor->tipo_pessoa === 'pj' && !empty($expositor->nome_fantasia) 
                ? $expositor->nome_fantasia 
                : $expositor->nome;

            $tipoPessoaBadge = $expositor->tipo_pessoa === 'pj' 
                ? '<span class="badge bg-info">PJ</span>' 
                : '<span class="badge bg-secondary">PF</span>';

            // Badge para Tipo de Expositor
            $tipoExpositorBadge = $this->getBadgeTipoExpositor($expositor->tipo_expositor);

            // Badge para Segmento
            $segmentoBadge = $this->getBadgeSegmento($expositor->segmento);

            $data[] = [
                'nome' => anchor("expositores/exibir/$expositor->id", esc($nomeExibicao), 'title="Exibir expositor ' . esc($nomeExibicao) . '"') . ' ' . $tipoPessoaBadge,
                'documento' => esc($expositor->getDocumentoFormatado()),
                'email' => esc($expositor->email),
                'telefone' => esc($expositor->telefone),
                'tipo_expositor' => $tipoExpositorBadge . '<span class="d-none">' . esc($expositor->tipo_expositor ?? '') . '</span>',
                'segmento' => $segmentoBadge . '<span class="d-none">' . esc($expositor->segmento ?? '') . '</span>',
                'situacao' => $expositor->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('criar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = new Expositor();

        $this->removeBlockCepSessao();

        $data = [
            'titulo' => "Cadastrar novo expositor",
            'expositor' => $expositor,
        ];

        return view('Expositores/criar', $data);
    }

    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        if (session()->get('blockCep') === true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];

            return $this->response->setJSON($retorno);
        }

        // Recupero o post da requisição
        $post = $this->request->getPost();

        // Valida documento de acordo com o tipo de pessoa
        $validacaoDocumento = $this->validaDocumento($post['documento'], $post['tipo_pessoa'] ?? 'pf');
        if ($validacaoDocumento !== true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['documento' => $validacaoDocumento];
            return $this->response->setJSON($retorno);
        }

        // Limpa formatação do documento antes de salvar
        $post['documento'] = preg_replace('/[^0-9]/', '', $post['documento']);

        $expositor = new Expositor($post);

        if ($this->expositorModel->save($expositor)) {

            try {
                // Cria usuário do expositor
                $this->criaUsuarioParaExpositor($expositor);

                // Envia dados de acesso ao expositor
                $this->enviaEmailCriacaoExpositorAcesso($expositor);

                $btnCriar = anchor("expositores/criar", 'Cadastrar novo expositor', ['class' => 'btn btn-danger mt-2']);

                session()->setFlashdata('sucesso', "Dados salvos com sucesso!<br><br>Importante: informe ao expositor os dados de acesso ao sistema: <p>E-mail: $expositor->email <p><p>Senha inicial: 123456</p> Esses mesmos dados foram enviados para o e-mail do expositor.<br> $btnCriar");

                $retorno['id'] = $this->expositorModel->getInsertID();

                return $this->response->setJSON($retorno);
            } catch (\Exception $e) {
                // Em caso de erro ao criar usuário ou enviar email, retornamos erro
                $retorno['erro'] = 'Expositor cadastrado, mas houve um erro ao criar acesso: ' . $e->getMessage();
                $retorno['id'] = $this->expositorModel->getInsertID();
                return $this->response->setJSON($retorno);
            }
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->expositorModel->errors();

        return $this->response->setJSON($retorno);
    }

    public function exibir(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        // Verifica se o expositor está no grupo PDV (grupo_id = 9)
        $isPdv = false;
        if (!empty($expositor->usuario_id)) {
            $isPdv = $this->grupoUsuarioModel->usuarioEstaNoGrupo(9, $expositor->usuario_id) !== null;
        }

        $data = [
            'titulo' => "Detalhando o expositor " . esc($expositor->getNomeExibicao()),
            'expositor' => $expositor,
            'is_pdv' => $isPdv,
        ];

        return view('Expositores/exibir', $data);
    }

    public function editar(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        $this->removeBlockCepSessao();

        $data = [
            'titulo' => "Editando o expositor " . esc($expositor->getNomeExibicao()),
            'expositor' => $expositor,
        ];

        return view('Expositores/editar', $data);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        if (session()->get('blockCep') === true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];

            return $this->response->setJSON($retorno);
        }

        // Recupero o post da requisição
        $post = $this->request->getPost();

        $expositor = $this->buscaExpositorOu404($post['id']);

        // Valida documento de acordo com o tipo de pessoa
        $validacaoDocumento = $this->validaDocumento($post['documento'], $post['tipo_pessoa'] ?? 'pf');
        if ($validacaoDocumento !== true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['documento' => $validacaoDocumento];
            return $this->response->setJSON($retorno);
        }

        // Limpa formatação do documento antes de salvar
        $post['documento'] = preg_replace('/[^0-9]/', '', $post['documento']);

        $expositor->fill($post);

        if ($expositor->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }

        if ($this->expositorModel->save($expositor)) {
            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');
            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->expositorModel->errors();

        return $this->response->setJSON($retorno);
    }

    public function excluir(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('excluir-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        if ($expositor->deleted_at != null) {
            return redirect()->back()->with('info', "Expositor " . $expositor->getNomeExibicao() . " já encontra-se excluído");
        }

        if ($this->request->getMethod() === 'post') {
            $this->expositorModel->delete($id);

            return redirect()->to(site_url("expositores"))->with('sucesso', "Expositor " . $expositor->getNomeExibicao() . " excluído com sucesso!");
        }

        $data = [
            'titulo' => "Excluindo o expositor " . esc($expositor->getNomeExibicao()),
            'expositor' => $expositor,
        ];

        return view('Expositores/excluir', $data);
    }

    public function desfazerExclusao(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        if ($expositor->deleted_at === null) {
            return redirect()->back()->with('info', "Apenas expositores excluídos podem ser recuperados");
        }

        $expositor->deleted_at = null;
        $this->expositorModel->protect(false)->save($expositor);

        return redirect()->back()->with('sucesso', "Expositor " . $expositor->getNomeExibicao() . " recuperado com sucesso!");
    }

    public function reenviarEmail(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        try {
            $this->enviaEmailCriacaoExpositorAcesso($expositor);

            return redirect()->back()->with('sucesso', "Email de boas-vindas reenviado com sucesso para " . $expositor->email);
        } catch (\Exception $e) {
            return redirect()->back()->with('erro', "Erro ao enviar email: " . $e->getMessage());
        }
    }

    public function criarUsuario(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        // Verifica se já possui usuário vinculado
        if (!empty($expositor->usuario_id)) {
            return redirect()->back()->with('info', "Este expositor já possui um usuário de acesso vinculado.");
        }

        try {
            // Cria usuário do expositor
            $this->criaUsuarioParaExpositorExistente($expositor);

            // Envia dados de acesso ao expositor
            $this->enviaEmailCriacaoExpositorAcesso($expositor);

            return redirect()->back()->with('sucesso', "Usuário de acesso criado com sucesso!<br><br>Dados enviados para: " . $expositor->email . "<br>Senha inicial: 123456");
        } catch (\Exception $e) {
            return redirect()->back()->with('erro', "Erro ao criar usuário: " . $e->getMessage());
        }
    }


    public function consultaCep()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $cep = $this->request->getGet('cep');

        return $this->response->setJSON($this->consultaViaCep($cep));
    }

    /**
     * Adiciona ou remove o expositor do grupo PDV (grupo_id = 9)
     *
     * @param int $id ID do expositor
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function togglePdv(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-expositores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $expositor = $this->buscaExpositorOu404($id);

        // Verifica se o expositor tem usuário vinculado
        if (empty($expositor->usuario_id)) {
            return redirect()->back()->with('atencao', 'Este expositor não possui usuário de acesso vinculado. Crie o usuário primeiro.');
        }

        $grupoPdv = 9;
        $usuarioId = (int) $expositor->usuario_id;

        // Verifica se já está no grupo PDV
        $jaEhPdv = $this->grupoUsuarioModel
            ->where('usuario_id', $usuarioId)
            ->where('grupo_id', $grupoPdv)
            ->first();

        if ($jaEhPdv) {
            // Remove do grupo PDV
            $this->grupoUsuarioModel
                ->where('usuario_id', $usuarioId)
                ->where('grupo_id', $grupoPdv)
                ->delete();

            return redirect()->back()->with('sucesso', 'Expositor removido do grupo PDV com sucesso!');
        } else {
            // Adiciona ao grupo PDV
            $db = \Config\Database::connect();
            $builder = $db->table('grupos_usuarios');
            $builder->insert([
                'grupo_id'   => $grupoPdv,
                'usuario_id' => $usuarioId,
            ]);

            if ($db->affectedRows() > 0) {
                return redirect()->back()->with('sucesso', 'Expositor adicionado ao grupo PDV com sucesso!');
            } else {
                return redirect()->back()->with('erro', 'Erro ao adicionar expositor ao grupo PDV.');
            }
        }
    }

    /**
     * Remove da sessão os dados de bloqueio de CEP
     *
     * @return void
     */
    private function removeBlockCepSessao(): void
    {
        session()->remove('blockCep');
    }

    /**
     * Valida o documento (CPF ou CNPJ)
     *
     * @param string $documento
     * @param string $tipoPessoa
     * @return bool|string
     */
    private function validaDocumento(string $documento, string $tipoPessoa)
    {
        $validacoes = new \App\Validacoes\MinhasValidacoes();
        $documento = preg_replace('/[^0-9]/', '', $documento);
        $erro = null;

        if ($tipoPessoa === 'pj') {
            if (!$validacoes->validaCNPJ($documento, $erro)) {
                return $erro ?? 'Por favor digite um CNPJ válido';
            }
        } else {
            if (!$validacoes->validaCPF($documento, $erro)) {
                return $erro ?? 'Por favor digite um CPF válido';
            }
        }

        return true;
    }

    /**
     * Método que recupera o expositor
     *
     * @param integer $id
     * @return \App\Entities\Expositor
     */
    private function buscaExpositorOu404(int $id = null)
    {
        if (!$id || !$expositor = $this->expositorModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o expositor $id");
        }

        return $expositor;
    }

    /**
     * Retorna o badge colorido para o tipo de expositor
     *
     * @param string|null $tipo
     * @return string
     */
    private function getBadgeTipoExpositor(?string $tipo): string
    {
        if (empty($tipo)) {
            return '<span class="badge bg-light text-dark">N/A</span>';
        }

        $cores = [
            'Stand Comercial'   => 'bg-primary',
            'Artist Alley'      => 'bg-purple',
            'Vila dos Artesãos' => 'bg-warning text-dark',
            'Espaço Medieval'   => 'bg-dark',
            'Indie'             => 'bg-info',
            'Games'             => 'bg-danger',
            'Espaço Temático'   => 'bg-success',
            'Parceiros'         => 'bg-secondary',
            'Food Park'         => 'bg-orange',
            'Patrocinadores'    => 'bg-gold',
            'Outros'            => 'bg-light text-dark',
        ];

        $classe = $cores[$tipo] ?? 'bg-secondary';

        // Para cores customizadas que não existem no Bootstrap
        $style = '';
        if ($classe === 'bg-purple') {
            $classe = '';
            $style = 'style="background-color: #6f42c1; color: white;"';
        } elseif ($classe === 'bg-orange') {
            $classe = '';
            $style = 'style="background-color: #fd7e14; color: white;"';
        } elseif ($classe === 'bg-gold') {
            $classe = '';
            $style = 'style="background-color: #ffc107; color: #000;"';
        }

        return '<span class="badge ' . $classe . '" ' . $style . '>' . esc($tipo) . '</span>';
    }

    /**
     * Retorna o badge colorido para o segmento
     *
     * @param string|null $segmento
     * @return string
     */
    private function getBadgeSegmento(?string $segmento): string
    {
        if (empty($segmento)) {
            return '<span class="badge bg-light text-dark">N/A</span>';
        }

        $cores = [
            'Alimentação'       => 'bg-danger',
            'Artesanato'        => 'bg-warning text-dark',
            'Brinquedos'        => 'bg-info',
            'Colecionáveis'     => 'bg-primary',
            'Cosplay'           => 'bg-purple',
            'Decoração'         => 'bg-success',
            'Eletrônicos'       => 'bg-dark',
            'Games'             => 'bg-danger',
            'K-Pop'             => 'bg-pink',
            'Livros e HQs'      => 'bg-secondary',
            'Mangás e Animes'   => 'bg-purple',
            'Moda e Acessórios' => 'bg-info',
            'Papelaria'         => 'bg-warning text-dark',
            'Pelúcias'          => 'bg-pink',
            'Serviços'          => 'bg-primary',
            'Vestuário'         => 'bg-success',
            'Outro'             => 'bg-light text-dark',
        ];

        $classe = $cores[$segmento] ?? 'bg-secondary';

        // Para cores customizadas que não existem no Bootstrap
        $style = '';
        if ($classe === 'bg-purple') {
            $classe = '';
            $style = 'style="background-color: #6f42c1; color: white;"';
        } elseif ($classe === 'bg-pink') {
            $classe = '';
            $style = 'style="background-color: #e83e8c; color: white;"';
        }

        return '<span class="badge ' . $classe . '" ' . $style . '>' . esc($segmento) . '</span>';
    }

    /**
     * Método que cria o usuário para o expositor recém cadastrado
     * Se já existir usuário com o mesmo email, reutiliza e adiciona ao grupo Parceiro
     *
     * @param object $expositor
     * @return void
     */
    private function criaUsuarioParaExpositor(object $expositor): void
    {
        // Verifica se já existe usuário com o mesmo email
        $usuarioExistente = $this->usuarioModel->buscaUsuarioPorEmail($expositor->email);

        if ($usuarioExistente) {
            // Usuário já existe - reutiliza e adiciona ao grupo Parceiro se necessário
            $usuarioId = $usuarioExistente->id;

            // Verifica se já está no grupo Parceiro (grupo 4)
            $jaEhParceiro = $this->grupoUsuarioModel
                ->where('usuario_id', $usuarioId)
                ->where('grupo_id', 4)
                ->first();

            if (!$jaEhParceiro) {
                // Adiciona ao grupo Parceiro
                $grupoParceiro = [
                    'grupo_id'   => 4, // Grupo de Parceiros
                    'usuario_id' => $usuarioId,
                ];
                $this->grupoUsuarioModel->protect(false)->insert($grupoParceiro);
            }

            // Vincula o usuário existente ao expositor
            $this->expositorModel
                ->protect(false)
                ->where('id', $this->expositorModel->getInsertID())
                ->set('usuario_id', $usuarioId)
                ->update();

            return;
        }

        // Usuário não existe - cria novo
        $usuario = [
            'nome'     => $expositor->getNomeExibicao(),
            'email'    => $expositor->email,
            'password' => '123456',
            'ativo'    => true,
        ];

        // Criamos o usuário do expositor
        $this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

        $usuarioId = $this->usuarioModel->getInsertID();

        // Montamos os dados do grupo que o usuário fará parte
        $grupoCliente = [
            'grupo_id'   => 2, // Grupo de clientes - base
            'usuario_id' => $usuarioId,
        ];

        $grupoParceiro = [
            'grupo_id'   => 4, // Grupo de Parceiros
            'usuario_id' => $usuarioId,
        ];

        // Inserimos o usuário nos grupos
        $this->grupoUsuarioModel->protect(false)->insert($grupoCliente);
        $this->grupoUsuarioModel->protect(false)->insert($grupoParceiro);

        // Atualizamos a tabela de expositores com o ID do usuário criado
        $this->expositorModel
            ->protect(false)
            ->where('id', $this->expositorModel->getInsertID())
            ->set('usuario_id', $usuarioId)
            ->update();
    }

    /**
     * Método que cria o usuário para um expositor já existente no banco
     * Se já existir usuário com o mesmo email, reutiliza e adiciona ao grupo Parceiro
     *
     * @param object $expositor
     * @return void
     */
    private function criaUsuarioParaExpositorExistente(object $expositor): void
    {
        // Verifica se já existe usuário com o mesmo email
        $usuarioExistente = $this->usuarioModel->buscaUsuarioPorEmail($expositor->email);

        if ($usuarioExistente) {
            // Usuário já existe - reutiliza e adiciona ao grupo Parceiro se necessário
            $usuarioId = $usuarioExistente->id;

            // Verifica se já está no grupo Parceiro (grupo 4)
            $jaEhParceiro = $this->grupoUsuarioModel
                ->where('usuario_id', $usuarioId)
                ->where('grupo_id', 4)
                ->first();

            if (!$jaEhParceiro) {
                // Adiciona ao grupo Parceiro
                $grupoParceiro = [
                    'grupo_id'   => 4, // Grupo de Parceiros
                    'usuario_id' => $usuarioId,
                ];
                $this->grupoUsuarioModel->protect(false)->insert($grupoParceiro);
            }

            // Vincula o usuário existente ao expositor
            $this->expositorModel
                ->protect(false)
                ->where('id', $expositor->id)
                ->set('usuario_id', $usuarioId)
                ->update();

            return;
        }

        // Usuário não existe - cria novo
        $usuario = [
            'nome'     => $expositor->getNomeExibicao(),
            'email'    => $expositor->email,
            'password' => '123456',
            'ativo'    => true,
        ];

        // Criamos o usuário do expositor
        $this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

        $usuarioId = $this->usuarioModel->getInsertID();

        // Montamos os dados do grupo que o usuário fará parte
        $grupoCliente = [
            'grupo_id'   => 2, // Grupo de clientes - base
            'usuario_id' => $usuarioId,
        ];

        $grupoParceiro = [
            'grupo_id'   => 4, // Grupo de Parceiros
            'usuario_id' => $usuarioId,
        ];

        // Inserimos o usuário nos grupos
        $this->grupoUsuarioModel->protect(false)->insert($grupoCliente);
        $this->grupoUsuarioModel->protect(false)->insert($grupoParceiro);

        // Atualizamos a tabela de expositores com o ID do usuário criado
        $this->expositorModel
            ->protect(false)
            ->where('id', $expositor->id)
            ->set('usuario_id', $usuarioId)
            ->update();
    }

    /**
     * Método que envia o e-mail para o expositor com os dados de acesso.
     *
     * @param object $expositor
     * @return void
     */
    private function enviaEmailCriacaoExpositorAcesso(object $expositor): void
    {
        $data = [
            'expositor' => $expositor,
        ];

        $mensagem = view('Expositores/email_dados_acesso', $data);

        // Enviar via Resend
        $this->resendService->enviarEmail(
            $expositor->email,
            'Dados de acesso ao Mundo Dream para Expositores',
            $mensagem
        );
    }
}

