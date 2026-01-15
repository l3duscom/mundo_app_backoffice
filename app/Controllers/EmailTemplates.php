<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class EmailTemplates extends BaseController
{
    /**
     * Mapeamento de categorias e seus templates de email
     */
    protected $categorias = [
        'Clientes' => [
            'pasta' => 'Clientes',
            'icone' => 'bx-user',
            'cor' => 'primary',
            'templates' => [
                'email_dados_acesso' => 'Dados de Acesso',
                'email_acesso_alterado' => 'Acesso Alterado',
                'email_dados_acesso_influencer' => 'Acesso Influencer',
                'email_dados_acesso_membro' => 'Acesso Membro',
                'email_dados_acesso_parceiros' => 'Acesso Parceiros',
                'email_migracao' => 'Migração de Conta',
            ]
        ],
        'Pedidos' => [
            'pasta' => 'Pedidos',
            'icone' => 'bx-cart',
            'cor' => 'success',
            'templates' => [
                'email_paid' => 'Pagamento Confirmado',
                'email_pedido' => 'Novo Pedido',
                'email_pedido_cartao' => 'Pedido Cartão',
                'email_cortesia' => 'Cortesia',
                'email_envio_cartao' => 'Envio do Cartão',
                'email_rastreio' => 'Rastreio',
            ]
        ],
        'Concursos' => [
            'pasta' => 'Concursos',
            'icone' => 'bx-trophy',
            'cor' => 'warning',
            'templates' => [
                'email_aprovado' => 'Inscrição Aprovada',
                'email_rejeitado' => 'Inscrição Rejeitada',
                'email_cancelada' => 'Inscrição Cancelada',
                'email_paid' => 'Pagamento Confirmado',
                'email_pedido' => 'Pedido de Inscrição',
                'email_checkin' => 'Check-in',
                'email_checkin_online' => 'Check-in Online',
                'email_cortesia' => 'Cortesia',
                'email_envio_cartao' => 'Envio do Cartão',
                'email_pedido_cartao' => 'Pedido Cartão',
                'email_rastreio' => 'Rastreio',
            ]
        ],
        'Contratos' => [
            'pasta' => 'ContratoDocumentos',
            'icone' => 'bx-file',
            'cor' => 'info',
            'templates' => [
                'email_assinatura' => 'Solicitação de Assinatura',
                'email_contrato_assinado' => 'Contrato Assinado',
                'email_contrato_confirmado' => 'Contrato Confirmado',
            ]
        ],
        'Expositores' => [
            'pasta' => 'Expositores',
            'icone' => 'bx-store',
            'cor' => 'secondary',
            'templates' => [
                'email_dados_acesso' => 'Dados de Acesso',
            ]
        ],
        'Senha' => [
            'pasta' => 'Password',
            'icone' => 'bx-lock-alt',
            'cor' => 'danger',
            'templates' => [
                'reset_email' => 'Recuperação de Senha',
            ]
        ],
        'Notificações' => [
            'pasta' => 'Notifications',
            'icone' => 'bx-bell',
            'cor' => 'dark',
            'templates' => [
                'email_dados_acesso' => 'Dados de Acesso',
                'email_acesso_alterado' => 'Acesso Alterado',
            ]
        ],
        'Base de Conhecimento' => [
            'pasta' => 'Conhecimento',
            'icone' => 'bx-book',
            'cor' => 'primary',
            'templates' => [
                'email_dados_acesso' => 'Dados de Acesso',
                'email_acesso_alterado' => 'Acesso Alterado',
            ]
        ],
    ];

    /**
     * Listagem de todos os templates de email
     */
    public function index()
    {
        // Conta total de templates
        $totalTemplates = 0;
        foreach ($this->categorias as $cat) {
            $totalTemplates += count($cat['templates']);
        }

        $data = [
            'titulo' => 'Templates de Email',
            'categorias' => $this->categorias,
            'totalTemplates' => $totalTemplates,
        ];

        return view('EmailTemplates/index', $data);
    }

    /**
     * Exibir preview de um template específico
     */
    public function exibir(string $categoria = null, string $template = null)
    {
        if (!$categoria || !$template) {
            return redirect()->to('email-templates')->with('erro', 'Template não especificado');
        }

        // Busca a categoria
        $categoriaInfo = null;
        $categoriaNome = null;
        foreach ($this->categorias as $nome => $info) {
            if (strtolower($info['pasta']) === strtolower($categoria)) {
                $categoriaInfo = $info;
                $categoriaNome = $nome;
                break;
            }
        }

        if (!$categoriaInfo) {
            return redirect()->to('email-templates')->with('erro', 'Categoria não encontrada');
        }

        // Verifica se o template existe na categoria
        $templateNome = null;
        foreach ($categoriaInfo['templates'] as $key => $nome) {
            if ($key === $template) {
                $templateNome = $nome;
                break;
            }
        }

        if (!$templateNome) {
            return redirect()->to('email-templates')->with('erro', 'Template não encontrado');
        }

        // Caminho do arquivo
        $caminhoView = $categoriaInfo['pasta'] . '/' . $template;
        $caminhoArquivo = APPPATH . 'Views/' . $categoriaInfo['pasta'] . '/' . $template . '.php';

        // Verifica se o arquivo existe
        if (!file_exists($caminhoArquivo)) {
            return redirect()->to('email-templates')->with('erro', 'Arquivo do template não encontrado');
        }

        // Carrega o conteúdo do arquivo
        $conteudoRaw = file_get_contents($caminhoArquivo);

        // Tenta renderizar com dados de exemplo
        $conteudoHtml = $this->renderizarComDadosExemplo($caminhoView);

        $data = [
            'titulo' => 'Preview: ' . $templateNome,
            'template' => $template,
            'templateNome' => $templateNome,
            'categoria' => $categoriaNome,
            'categoriaInfo' => $categoriaInfo,
            'caminhoArquivo' => str_replace(APPPATH, 'app/', $caminhoArquivo),
            'conteudoRaw' => $conteudoRaw,
            'conteudoHtml' => $conteudoHtml,
        ];

        return view('EmailTemplates/exibir', $data);
    }

    /**
     * Renderiza o template com dados de exemplo para preview
     */
    protected function renderizarComDadosExemplo(string $view): string
    {
        // Dados de exemplo para preenchimento
        $dadosExemplo = [
            'cliente' => (object) [
                'id' => 1,
                'nome' => 'João da Silva',
                'email' => 'joao@exemplo.com',
                'cpf' => '123.456.789-00',
                'telefone' => '(51) 99999-9999',
            ],
            'evento' => (object) [
                'id' => 1,
                'nome' => 'Dreamfest 2025 - Mega Convenção Geek',
                'data_inicio' => '2025-12-06',
                'data_fim' => '2025-12-07',
                'hora_inicio' => '11:00:00',
                'hora_fim' => '19:00:00',
                'local' => 'Centro de Eventos FENAC - Novo Hamburgo/RS',
            ],
            'pedido' => (object) [
                'id' => 12345,
                'codigo' => 'PED-2025-12345',
                'valor_total' => 150.00,
                'status' => 'pago',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            'concurso' => (object) [
                'id' => 1,
                'nome_social' => 'João Cosplay',
                'personagem' => 'Naruto Uzumaki',
                'serie' => 'Naruto Shippuden',
                'categoria' => 'Cosplay Individual',
            ],
            'contrato' => (object) [
                'id' => 1,
                'numero' => 'CTR-2025-001',
                'razao_social' => 'Empresa Exemplo LTDA',
                'valor_total' => 5000.00,
            ],
            'senha' => 'SenhaExemplo123',
            'link' => site_url('exemplo/link'),
            'token' => 'abc123def456',
            'qrcode_link' => site_url('checkout/qrcode/exemplo'),
        ];

        try {
            return view($view, $dadosExemplo);
        } catch (\Throwable $e) {
            // Se houver erro, retorna mensagem informativa
            return '<div class="alert alert-warning">
                <i class="bx bx-info-circle me-2"></i>
                <strong>Não foi possível renderizar preview completo.</strong><br>
                Este template requer dados específicos que não estão disponíveis no preview.
                <br><small class="text-muted">Erro: ' . esc($e->getMessage()) . '</small>
            </div>';
        }
    }
}
