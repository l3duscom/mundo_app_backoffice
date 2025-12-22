<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CodigoBonusModel;
use App\Models\BonusModel;
use App\Models\IngressoModel;
use App\Models\PedidoModel;
use App\Traits\ValidacoesTrait;

class CodigoBonus extends BaseController
{
    use ValidacoesTrait;

    private $codigoBonusModel;
    private $bonusModel;
    private $ingressoModel;
    private $pedidoModel;

    public function __construct()
    {
        $this->codigoBonusModel = new CodigoBonusModel();
        $this->bonusModel = new BonusModel();
        $this->ingressoModel = new IngressoModel();
        $this->pedidoModel = new PedidoModel();
    }

    /**
     * Lista códigos de bonus
     */
    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('visualizar-home')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        // Contar expirados
        $hoje = date('Y-m-d');
        $total_expirados = $this->codigoBonusModel
            ->where('usado', 1)
            ->where('validade <', $hoje)
            ->where('validade IS NOT NULL')
            ->countAllResults();

        $data = [
            'titulo' => 'Códigos de Bonus',
            'total_disponiveis' => $this->codigoBonusModel->contaDisponiveis(),
            'total_usados' => $this->codigoBonusModel->contaUsados(),
            'total_expirados' => $total_expirados,
        ];

        return view('CodigoBonus/index', $data);
    }

    /**
     * Formulário de novo código
     */
    public function novo()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('visualizar-home')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Novo Código de Bonus',
        ];

        return view('CodigoBonus/novo', $data);
    }

    /**
     * Cadastrar código (AJAX)
     */
    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $dados = [
            'codigo' => $post['codigo'],
            'validade' => $post['validade'] ?: null,
            'validade_lote' => $post['validade_lote'] ?: null,
            'usado' => 0,
        ];

        if ($this->codigoBonusModel->insert($dados)) {
            $retorno['sucesso'] = 'Código cadastrado com sucesso!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Não foi possível cadastrar o código.';
        $retorno['erros_model'] = $this->codigoBonusModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Formulário de edição
     */
    public function editar($id)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('visualizar-home')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $codigo = $this->codigoBonusModel->find($id);

        if (!$codigo) {
            return redirect()->to(site_url('codigo-bonus'))->with('erro', 'Código não encontrado.');
        }

        $data = [
            'titulo' => 'Editar Código de Bonus',
            'codigo' => $codigo,
        ];

        return view('CodigoBonus/editar', $data);
    }

    /**
     * Atualizar código (AJAX)
     */
    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $dados = [
            'codigo' => $post['codigo'],
            'validade' => $post['validade'] ?: null,
            'validade_lote' => $post['validade_lote'] ?: null,
        ];

        if ($this->codigoBonusModel->update($post['id'], $dados)) {
            $retorno['sucesso'] = 'Código atualizado com sucesso!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Não foi possível atualizar o código.';
        $retorno['erros_model'] = $this->codigoBonusModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Atualizar códigos em massa (AJAX)
     */
    public function atualizarMassa()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $ids = $post['ids'] ?? '';
        $validade = $post['validade'] ?? '';
        $validade_lote = $post['validade_lote'] ?? '';

        if (empty($ids)) {
            $retorno['erro'] = 'Nenhum código selecionado.';
            return $this->response->setJSON($retorno);
        }

        // Verificar se pelo menos um campo foi preenchido
        if (empty($validade) && empty($validade_lote)) {
            $retorno['erro'] = 'Preencha pelo menos um campo para atualizar.';
            return $this->response->setJSON($retorno);
        }

        $idsArray = array_filter(array_map('intval', explode(',', $ids)));

        if (empty($idsArray)) {
            $retorno['erro'] = 'IDs inválidos.';
            return $this->response->setJSON($retorno);
        }

        $dados = [];
        if (!empty($validade)) {
            $dados['validade'] = $validade;
        }
        if (!empty($validade_lote)) {
            $dados['validade_lote'] = $validade_lote;
        }

        $atualizados = 0;
        foreach ($idsArray as $id) {
            if ($this->codigoBonusModel->update($id, $dados)) {
                $atualizados++;
            }
        }

        $retorno['sucesso'] = "$atualizados código(s) atualizado(s) com sucesso!";
        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir código
     */
    public function excluir($id)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('visualizar-home')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $codigo = $this->codigoBonusModel->find($id);

        if (!$codigo) {
            return redirect()->to(site_url('codigo-bonus'))->with('erro', 'Código não encontrado.');
        }

        if ($codigo->usado == 1) {
            return redirect()->to(site_url('codigo-bonus'))->with('atencao', 'Não é possível excluir um código já utilizado.');
        }

        if ($this->codigoBonusModel->delete($id)) {
            return redirect()->to(site_url('codigo-bonus'))->with('sucesso', 'Código excluído com sucesso!');
        }

        return redirect()->to(site_url('codigo-bonus'))->with('erro', 'Não foi possível excluir o código.');
    }

    /**
     * Recupera códigos para DataTables (AJAX)
     */
    public function recuperaCodigos()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $codigos = $this->codigoBonusModel->orderBy('created_at', 'DESC')->findAll();

        $data = [];
        $hoje = date('Y-m-d');

        foreach ($codigos as $codigo) {
            // Status badge
            if ($codigo->usado == 1) {
                // Verificar se está expirado
                if ($codigo->validade && $codigo->validade < $hoje) {
                    $status = '<span class="badge bg-danger">Expirado</span>';
                } else {
                    $status = '<span class="badge bg-secondary">Usado</span>';
                }
            } else {
                $status = '<span class="badge bg-success">Disponível</span>';
            }

            // Validade
            $validade = $codigo->validade ? date('d/m/Y', strtotime($codigo->validade)) : '-';
            $validade_lote = $codigo->validade_lote ? date('d/m/Y', strtotime($codigo->validade_lote)) : '-';

            // Ações
            $acoes = '';
            if ($codigo->usado == 0) {
                $acoes .= '<a href="' . site_url('codigo-bonus/editar/' . $codigo->id) . '" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a> ';
                $acoes .= '<a href="' . site_url('codigo-bonus/excluir/' . $codigo->id) . '" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm(\'Tem certeza que deseja excluir este código?\')"><i class="bi bi-trash"></i></a>';
            } else {
                $acoes = '<span class="text-muted">-</span>';
            }

            $data[] = [
                'id' => $codigo->id,
                'codigo' => esc($codigo->codigo),
                'status' => $status,
                'validade' => $validade,
                'validade_lote' => $validade_lote,
                'created_at' => date('d/m/Y H:i', strtotime($codigo->created_at)),
                'acoes' => $acoes,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Importar códigos em lote (AJAX)
     */
    public function importar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        // Códigos separados por linha
        $codigosTexto = $post['codigos'] ?? '';
        $validade = $post['validade'] ?: null;
        $validade_lote = $post['validade_lote'] ?: null;

        $linhas = array_filter(array_map('trim', explode("\n", $codigosTexto)));

        if (empty($linhas)) {
            $retorno['erro'] = 'Nenhum código informado.';
            return $this->response->setJSON($retorno);
        }

        $inseridos = 0;
        foreach ($linhas as $codigo) {
            if (!empty($codigo)) {
                $dados = [
                    'codigo' => $codigo,
                    'validade' => $validade,
                    'validade_lote' => $validade_lote,
                    'usado' => 0,
                ];
                if ($this->codigoBonusModel->insert($dados)) {
                    $inseridos++;
                }
            }
        }

        $retorno['sucesso'] = "$inseridos código(s) importado(s) com sucesso!";
        return $this->response->setJSON($retorno);
    }

    /**
     * Liberar todos os códigos com validade expirada
     * - Marca o código como EXPIRADO na tabela bonus
     * - Libera o código na tabela codigo_bonus (usado = 0)
     */
    public function liberarExpirados()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('visualizar-home')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $hoje = date('Y-m-d');

        // Buscar códigos usados com validade expirada
        $codigosExpirados = $this->codigoBonusModel
            ->where('usado', 1)
            ->where('validade <', $hoje)
            ->where('validade IS NOT NULL')
            ->findAll();

        $liberados = 0;

        foreach ($codigosExpirados as $codigoBonus) {
            // Atualizar na tabela bonus: mudar código para "EXPIRADO"
            if ($codigoBonus->bonus_id) {
                $this->bonusModel->update($codigoBonus->bonus_id, [
                    'codigo' => 'EXPIRADO'
                ]);
            }

            // Liberar o código na tabela codigo_bonus
            $this->codigoBonusModel->update($codigoBonus->id, [
                'usado' => 0,
                'bonus_id' => null
            ]);

            $liberados++;
        }

        if ($liberados > 0) {
            return redirect()->to(site_url('codigo-bonus'))->with('sucesso', "$liberados código(s) expirado(s) liberado(s) com sucesso!");
        }

        return redirect()->to(site_url('codigo-bonus'))->with('info', 'Nenhum código expirado encontrado para liberar.');
    }

    /**
     * Migrar códigos Cinemark do evento do contexto
     * - Busca ingressos com coluna cinemark preenchida
     * - Cria registro na tabela bonus
     * - Cria registro na tabela codigo_bonus (marcado como usado)
     */
    public function migrarCinemarkEvento()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('visualizar-home')) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        // Pegar evento do contexto
        $evento_id = evento_selecionado();
        
        if (!$evento_id) {
            return redirect()->to(site_url('codigo-bonus'))->with('erro', 'Nenhum evento selecionado no contexto.');
        }

        // Instruções padrão do Cinemark
        $instrucoes = "1 - Atualize ou baixe o APP Cinemark no Google Play ou APP Store.\n" .
            "2 - Faça seu login, selecione o cinema, filme de sua preferência.\n" .
            "3 - Selecione o horário da sessão e os assentos;\n" .
            "4 - Selecione o tipo de ingresso como Voucher e quantidade de ingressos que irá utilizar;\n" .
            "5 - Apresente seu voucher online no celular diretamente na entrada da sala do cinema.";

        // Buscar ingressos com cinemark preenchido do evento selecionado
        $db = \Config\Database::connect();
        $builder = $db->table('ingressos');
        $builder->select('ingressos.id, ingressos.cinemark, ingressos.user_id, pedidos.evento_id');
        $builder->join('pedidos', 'pedidos.id = ingressos.pedido_id');
        $builder->where('pedidos.evento_id', $evento_id);
        $builder->where('ingressos.cinemark IS NOT NULL');
        $builder->where('ingressos.cinemark !=', '');
        $builder->orderBy('ingressos.id', 'DESC'); // Mais novo primeiro
        
        $ingressos = $builder->get()->getResult();

        $migrados = 0;
        $jaExistem = 0;
        $codigosCriados = 0;

        foreach ($ingressos as $ingresso) {
            $codigoCinemark = trim($ingresso->cinemark);
            
            if (empty($codigoCinemark)) {
                continue;
            }

            // Verificar se já existe bonus para este ingresso
            $bonusExistente = $this->bonusModel
                ->where('ingresso_id', $ingresso->id)
                ->where('tipo_bonus', 'cinemark')
                ->first();

            if ($bonusExistente) {
                $jaExistem++;
                continue;
            }

            // Criar registro na tabela bonus
            $bonusData = [
                'ingresso_id' => $ingresso->id,
                'user_id' => $ingresso->user_id,
                'tipo_bonus' => 'cinemark',
                'codigo' => $codigoCinemark,
                'instrucoes' => $instrucoes,
            ];

            $this->bonusModel->insert($bonusData);
            $bonusId = $this->bonusModel->getInsertID();

            // Verificar se código já existe na tabela codigo_bonus
            $codigoExistente = $this->codigoBonusModel->where('codigo', $codigoCinemark)->first();

            if (!$codigoExistente) {
                // Criar registro na tabela codigo_bonus
                $codigoBonusData = [
                    'codigo' => $codigoCinemark,
                    'usado' => 1,
                    'bonus_id' => $bonusId,
                    'validade' => null,
                    'validade_lote' => null,
                ];
                $this->codigoBonusModel->insert($codigoBonusData);
                $codigosCriados++;
            } else {
                // Atualizar código existente como usado
                $this->codigoBonusModel->update($codigoExistente->id, [
                    'usado' => 1,
                    'bonus_id' => $bonusId
                ]);
            }

            $migrados++;
        }

        $mensagem = "Migração concluída! $migrados ingresso(s) migrado(s).";
        if ($jaExistem > 0) {
            $mensagem .= " $jaExistem já existiam na nova tabela.";
        }
        if ($codigosCriados > 0) {
            $mensagem .= " $codigosCriados código(s) criado(s) na tabela codigo_bonus.";
        }

        return redirect()->to(site_url('codigo-bonus'))->with('sucesso', $mensagem);
    }
}

