<?php

if(function_exists('usuario_logado') === false){

    function usuario_logado(){

        return service('autenticacao')->pegaUsuarioLogado();

    }


}

if(function_exists('evento_selecionado') === false){

    function evento_selecionado(){

        return session()->get('event_id');

    }


}

if(function_exists('evento_nome') === false){

    function evento_nome(){

        $event_id = session()->get('event_id');
        if($event_id) {
            $eventoModel = new \App\Models\EventoModel();
            $evento = $eventoModel->find($event_id);
            if($evento && isset($evento->nome)) {
                // Validar se o nome não é um texto de teste
                $nome = trim($evento->nome);
                
                // Lista de nomes inválidos ou de teste
                $nomes_invalidos = ['test', 'teste', 'asdasd', 'asda', 'asd', 'testando', 'demo'];
                
                if(strlen($nome) > 2 && !in_array(strtolower($nome), $nomes_invalidos)) {
                    return $nome;
                }
                
                // Log para debug (remover em produção)
                log_message('debug', "Nome de evento inválido rejeitado: '{$nome}'");
            }
        }
        return null;

    }


}

if(function_exists('evento_descricao_pagamento') === false){

    function evento_descricao_pagamento($tipo_pagamento = null){

        $event_id = session()->get('event_id');
        if($event_id) {
            $eventoModel = new \App\Models\EventoModel();
            $evento = $eventoModel->find($event_id);
            
            if($evento) {
                $descricao = 'Ingressos ' . $evento->nome;
                
                // Adiciona informação específica do tipo de pagamento
                if($tipo_pagamento === 'pix') {
                    $descricao .= ' (PIX com 10% desconto)';
                }
                
                return $descricao;
            }
        }
        
        // Fallback para caso não encontre o evento
        return 'Ingressos Dreamfest 25';

    }


}

if(function_exists('precisa_contexto_evento') === false){

    function precisa_contexto_evento($rota){

        $rotas_com_contexto = [
            'concursos',
            'pedidos/gerenciar_evento',
            'ingressos/add'
        ];
        
        foreach ($rotas_com_contexto as $rota_contexto) {
            if (strpos($rota, $rota_contexto) !== false) {
                return true;
            }
        }
        
        return false;

    }


}

if(function_exists('evento_selecionado_com_validacao') === false){

    function evento_selecionado_com_validacao(){

        $event_id = session()->get('event_id');
        
        if(!$event_id) {
            return null;
        }
        
        $eventoModel = new \App\Models\EventoModel();
        $evento = $eventoModel->find($event_id);
        
        // Se o evento não existe mais, limpar da sessão
        if(!$evento) {
            session()->remove('event_id');
            return null;
        }
        
        return $evento;

    }


}