<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro que restringe usuários do grupo Vendas (ID 10)
 * a acessar apenas as rotas do pipeline
 */
class VendasFilter implements FilterInterface
{
    /**
     * Rotas permitidas para usuários de vendas
     */
    private array $rotasPermitidas = [
        'pipeline',                       // Rota index
        'pipeline/kanban',                // View principal do kanban
        'pipeline/criar',                 // Formulário de criação de lead
        'pipeline/exibir',                // Exibir detalhes do lead
        'pipeline/editar',                // Formulário de edição do lead
        'pipeline/cadastrar',             // Salvar novos leads (POST)
        'pipeline/atualizar',             // Atualizar leads (POST)
        'pipeline/alteraretapa',          // Mover cards no kanban (drag & drop)
        'pipeline/recuperaleadskanban',   // Carregar dados do kanban (AJAX)
        'pipeline/registraratividade',    // Registrar atividades no lead
        'login/logout',                   // Permitir logout
        'usuarios/imagem',                // Para carregar imagens de usuários
    ];

    /**
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $autenticacao = service('autenticacao');

        // Se não estiver logado, deixa o LoginFilter cuidar
        if ($autenticacao->estaLogado() === false) {
            return null;
        }

        $usuarioLogado = $autenticacao->pegaUsuarioLogado();

        // Se não for usuário de vendas, permite acesso normal
        if (!$usuarioLogado->is_vendas) {
            return null;
        }

        // É usuário de vendas - verificar se a rota é permitida
        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $uri = $request->getUri()->getPath();
        
        // Remove barra inicial se houver
        $uri = ltrim($uri, '/');

        // Verifica se a rota atual está na lista de permitidas
        foreach ($this->rotasPermitidas as $rotaPermitida) {
            // Verifica se a URI começa com a rota permitida
            if (strpos($uri, $rotaPermitida) === 0) {
                return null; // Permite acesso
            }
        }

        // Rota não permitida - redireciona para o kanban
        return redirect()->to(site_url('pipeline/kanban'))
            ->with('atencao', 'Você não tem permissão para acessar essa área.');
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
