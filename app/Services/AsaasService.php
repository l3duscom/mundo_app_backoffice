<?php

namespace App\Services;

use Exception;


class AsaasService
{


    private $access_token;
    private $customers;
    private $payments;



    public function __construct()
    {
        if (env('CI_ENVIRONMENT') == 'development') {
            $this->access_token = env('ASAAS_ACCESS_TOKEN_SANDBOX');
            $this->customers = 'https://sandbox.asaas.com/api/v3/customers';
            $this->payments = 'https://sandbox.asaas.com/api/v3/payments/';
        } else {
            $this->access_token = env('ASAAS_ACCESS_TOKEN');
            $this->customers = 'https://www.asaas.com/api/v3/customers';
            $this->payments = 'https://www.asaas.com/api/v3/payments/';
        }
    }

    public function customers($post)
    {

        $vars = array(
            'name' => $post['nome'],
            'email' => $post['email'],
            'phone' =>  $post['telefone'],
            'mobilePhone' =>  $post['telefone'],
            'cpfCnpj' => $post['cpf'],
            'postalCode' => $post['cep'],
            'addressNumber' => $post['numero'],
            "observations" => "Expositor - Backoffice",
            "notificationDisabled" => true,
        );

        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->customers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vars));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para ambiente Windows/localhost
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $apiResponse = curl_exec($ch);
            
            // Verifica erros do curl
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                log_message('error', 'Asaas CURL Error (customers): ' . $error);
                return ['errors' => [['description' => 'Erro de conexão: ' . $error]]];
            }

            curl_close($ch);
            
            $dadosCustomer = json_decode($apiResponse, true);
            
            // Log para debug
            log_message('info', 'Asaas customers response: ' . $apiResponse);

            return $dadosCustomer;
        } catch (Exception $e) {
            log_message('error', 'Asaas Exception: ' . $e->getMessage());
            return ['errors' => [['description' => $e->getMessage()]]];
        }
    }

    public function payments($post)
    {


        $dadosCustomer = $post['customer_id'];


        $credit_card = array(
            'customer' => $dadosCustomer,
            'billingType' => 'CREDIT_CARD',
            'dueDate' =>  date('Y-m-d', strtotime('+1 days')),
            'installmentCount' => $post['installmentCount'],
            'installmentValue' => number_format($post['installmentValue'], 2, '.', ''),
            'description' => $post['description'],
            'postalCode' => $post['postalCode'],
            'observations' => $post['observations'],
            'creditCard' => [
                'holderName' => $post['holderName'],
                'number' => $post['number'],
                'expiryMonth' => $post['expiryMonth'],
                'expiryYear' => $post['expiryYear'],
                'ccv' => $post['ccv']
            ],
            'creditCardHolderInfo' => [
                'name' => $post['nome'],
                'email' => $post['email'],
                'cpfCnpj' => $post['cpf'],
                'postalCode' => $post['cep'],
                'addressNumber' => $post['numero'],
                'mobilePhone' => $post['telefone']
            ],

            'remoteIp' => $_SERVER['REMOTE_ADDR']
        );




        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];



        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($credit_card));  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $apiResponse = curl_exec($ch);
            $dadosCreditCard = json_decode($apiResponse, true);


            curl_close($ch);

            return $dadosCreditCard;
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function paymentPix($post)
    {


        $dadosCustomer = $post['customer_id'];


        $pay = array(
            'customer' => $dadosCustomer,
            'billingType' => 'PIX',
            //'dueDate' =>  date('Y-m-d', strtotime('+1 days')),
            'dueDate' =>  date('Y-m-d'),
            'value' => $post['value'] / 100,
            'description' => $post['description'],
            'externalReference' => $post['externalReference'],
        );




        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];



        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pay));  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $apiResponse = curl_exec($ch);
            $retorno = json_decode($apiResponse, true);


            curl_close($ch);

            return $retorno;
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function listaCobranca($payment_id)
    {
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];



        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments . $payment_id);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $apiResponse = curl_exec($ch);
            $retorno = json_decode($apiResponse, true);


            curl_close($ch);


            return $retorno;
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function obtemQrCode(string $payment_id)
    {
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];



        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments . $payment_id . '/pixQrCode');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $apiResponse = curl_exec($ch);

            $retorno = json_decode($apiResponse, true);

            curl_close($ch);


            return $retorno;
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * Cria cobrança genérica (BOLETO ou PIX)
     * 
     * @param array $post Dados da cobrança
     *   - customer_id: ID do cliente no Asaas
     *   - billing_type: BOLETO ou PIX
     *   - value: Valor em reais (float)
     *   - due_date: Data de vencimento (Y-m-d)
     *   - description: Descrição da cobrança
     *   - external_reference: Referência externa (código do contrato)
     *   - installment_count: Quantidade de parcelas (opcional, só para boleto)
     *   - installment_value: Valor da parcela (opcional, só para boleto)
     * @return array
     */
    public function criarCobranca($post)
    {
        $pay = [
            'customer' => $post['customer_id'],
            'billingType' => $post['billing_type'] ?? 'BOLETO',
            'dueDate' => $post['due_date'] ?? date('Y-m-d', strtotime('+7 days')),
            'value' => (float) $post['value'],
            'description' => $post['description'] ?? 'Cobrança',
            'externalReference' => $post['external_reference'] ?? '',
        ];

        // Se for parcelado (boleto ou PIX)
        if (isset($post['installment_count']) && $post['installment_count'] > 1) {
            $pay['installmentCount'] = (int) $post['installment_count'];
            $pay['installmentValue'] = (float) $post['installment_value'];
            unset($pay['value']);
        }

        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pay));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $apiResponse = curl_exec($ch);
            
            // Verifica erros do curl
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                log_message('error', 'Asaas CURL Error (criarCobranca): ' . $error);
                return ['errors' => [['description' => 'Erro de conexão: ' . $error]]];
            }

            curl_close($ch);
            
            $retorno = json_decode($apiResponse, true);
            
            // Log para debug
            log_message('info', 'Asaas criarCobranca response: ' . $apiResponse);

            return $retorno;
        } catch (Exception $e) {
            log_message('error', 'Asaas Exception: ' . $e->getMessage());
            return ['errors' => [['description' => $e->getMessage()]]];
        }
    }

    /**
     * Confirma recebimento em dinheiro de uma cobrança existente
     * 
     * @param string $paymentId ID do pagamento no Asaas
     * @param float $value Valor recebido
     * @param string $paymentDate Data do pagamento (Y-m-d)
     * @return array
     */
    public function receberEmDinheiro(string $paymentId, float $value, string $paymentDate = null)
    {
        $paymentDate = $paymentDate ?? date('Y-m-d');
        
        $data = [
            'paymentDate' => $paymentDate,
            'value' => $value,
            'notifyCustomer' => false
        ];

        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments . $paymentId . '/receiveInCash');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $apiResponse = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                log_message('error', 'Asaas CURL Error (receberEmDinheiro): ' . $error);
                return ['errors' => [['description' => 'Erro de conexão: ' . $error]]];
            }

            curl_close($ch);
            
            $retorno = json_decode($apiResponse, true);
            
            log_message('info', 'Asaas receberEmDinheiro response: ' . $apiResponse);

            return $retorno;
        } catch (Exception $e) {
            log_message('error', 'Asaas Exception: ' . $e->getMessage());
            return ['errors' => [['description' => $e->getMessage()]]];
        }
    }

    /**
     * Busca detalhes de uma cobrança (incluindo parcelas)
     * 
     * @param string $paymentId ID do pagamento no Asaas
     * @return array
     */
    public function buscarCobranca(string $paymentId)
    {
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments . $paymentId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $apiResponse = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return ['errors' => [['description' => 'Erro de conexão: ' . $error]]];
            }

            curl_close($ch);
            
            return json_decode($apiResponse, true);
        } catch (Exception $e) {
            return ['errors' => [['description' => $e->getMessage()]]];
        }
    }

    /**
     * Busca parcelas de um parcelamento
     * 
     * @param string $installmentId ID do parcelamento no Asaas
     * @return array
     */
    public function buscarParcelas(string $installmentId)
    {
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];

        $url = str_replace('/payments/', '/installments/', $this->payments) . $installmentId . '/payments';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $apiResponse = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return ['errors' => [['description' => 'Erro de conexão: ' . $error]]];
            }

            curl_close($ch);
            
            return json_decode($apiResponse, true);
        } catch (Exception $e) {
            return ['errors' => [['description' => $e->getMessage()]]];
        }
    }

    /**
     * Cancela/Estorna uma cobrança
     * 
     * @param string $paymentId ID do pagamento no Asaas
     * @return array
     */
    public function cancelarCobranca(string $paymentId)
    {
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->access_token
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->payments . $paymentId);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $apiResponse = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                log_message('error', 'Asaas CURL Error (cancelarCobranca): ' . $error);
                return ['errors' => [['description' => 'Erro de conexão: ' . $error]]];
            }

            curl_close($ch);
            
            $retorno = json_decode($apiResponse, true);
            
            log_message('info', 'Asaas cancelarCobranca response: ' . $apiResponse);

            return $retorno;
        } catch (Exception $e) {
            log_message('error', 'Asaas Exception: ' . $e->getMessage());
            return ['errors' => [['description' => $e->getMessage()]]];
        }
    }
}
