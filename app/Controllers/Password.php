<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Token;
use App\Services\ResendService;

class Password extends BaseController
{
    private $usuarioModel;
    private $notifyService;
    private $resendService;


    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->notifyService = new \App\Services\NotifyService();
        $this->resendService = new ResendService();
    }

    public function esqueci()
    {
        $data = [
            'titulo' => 'Esqueci a minha senha',
        ];

        return view('Password/esqueci', $data);
    }

    public function start()
    {
        $data = [
            'titulo' => 'Primeiro Acesso',
        ];

        return view('Password/start', $data);
    }

    public function precessaEsqueci()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        // Recupero o e-mail da requisição
        $email = $this->request->getPost('email');

        $usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);

        if ($usuario === null || $usuario->ativo === false) {
            $retorno['erro'] = 'Não encontramos uma conta válida com esse e-mail';
            return $this->response->setJSON($retorno);
        }

        $usuario->iniciaPasswordReset();

        $this->usuarioModel->save($usuario);

        //  $mensagem = "Inciando a redefinição de senha \n\n*Por favor, clique no link abaixo para iniciar a redefinição da sua senha de acesso.* \n" . site_url("password/reset/$usuario->reset_token") . "\n\nCaso o link não esteja clicável, basta copiar e colar em seu navegador!";
        //if ($usuario->telefone) {
        // if (strlen($usuario->telefone) == 10 || strlen($usuario->telefone) == 11) {
        //if (strlen($usuario->telefone) == 11 && substr($usuario->telefone, 2, 1) == '9') {
        // $this->notifyService->notificawpppwd($usuario, $mensagem);
        //  }
        // }
        //  }

        $this->enviaEmailRedefinicaoSenha($usuario);

        return $this->response->setJSON([]);
    }

    public function resetEnviado()
    {
        $data = [
            'titulo' => 'E-mail de recuperação enviado para a sua caixa de entrada.',
        ];

        return view('Password/reset_enviado', $data);
    }

    public function reset($token = null)
    {

        if ($token === null) {

            return redirect()->to(site_url("password/esqueci"))->with("atencao", "Link inválido ou expirado");
        }

        // Buscamos o usuário na base de dados de acordo com hash do token que veio como parâmetro
        $usuario = $this->usuarioModel->buscaUsuarioParaRedefinirSenha($token);

        if ($usuario === null) {

            return redirect()->to(site_url("password/esqueci"))->with("atencao", "Link inválido ou expirado");
        }

        $data = [
            'titulo' => 'Crie a sua nova senha de acesso',
            'token' => $token,
        ];

        return view('Password/reset', $data);
    }

    public function processaReset()
    {

        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        // Recupero todos os dados do POST
        $post = $this->request->getPost();

        // Buscamos o usuário na base de dados de acordo com hash do token que veio como parâmetro
        $usuario = $this->usuarioModel->buscaUsuarioParaRedefinirSenha($post['token']);

        if ($usuario === null) {

            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['link_invalido' => 'Link inválido ou expirado'];
            return $this->response->setJSON($retorno);
        }

        $usuario->fill($post);

        $usuario->finalizaPasswordReset();

        if ($this->usuarioModel->save($usuario)) {

            session()->setFlashdata("sucesso", "Nova senha criada com sucesso!");

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->usuarioModel->errors();

        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }

    /**
     * Método que envia o e-mail para o usuário.
     *
     * @param object $usuario
     * @return void
     */
    private function enviaEmailRedefinicaoSenha(object $usuario): void
    {
        $data = [
            'token' => $usuario->reset_token,
        ];

        $mensagem = view('Password/reset_email', $data);

        // Enviar via Resend
        $this->resendService->enviarEmail(
            $usuario->email,
            'Redefinição da senha de acesso',
            $mensagem
        );
    }
}
