<?php

namespace App\Models;

use CodeIgniter\Model;

class DadosEnvioModel extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    /**
     * Busca dados para exportação de envios
     * Prioriza endereço da tabela 'enderecos', mas usa 'clientes' como fallback
     * 
     * @param int $evento_id ID do evento
     * @return array
     */
    public function buscarDadosEnvio(int $evento_id): array
    {
        $sql = "
        SELECT 
            c.nome,
            '' as empresa,
            c.cpf,
            COALESCE(e.cep, c.cep) as cep,
            COALESCE(e.endereco, c.endereco) as endereco,
            
            -- Extrai o número (pega de enderecos ou clientes)
            SUBSTRING_INDEX(COALESCE(e.numero, c.numero), ' ', 1) as numero,
            
            -- Extrai o complemento
            CASE 
                WHEN SUBSTRING_INDEX(COALESCE(e.numero, c.numero), ' ', 1) <> 
                     SUBSTRING(COALESCE(e.numero, c.numero), LOCATE(' ', COALESCE(e.numero, c.numero)) + 1) 
                THEN SUBSTRING(COALESCE(e.numero, c.numero), LOCATE(' ', COALESCE(e.numero, c.numero)) + 1) 
                ELSE NULL 
            END AS complemento,
            
            COALESCE(e.bairro, c.bairro) as bairro,
            COALESCE(e.cidade, c.cidade) as cidade,
            COALESCE(e.estado, c.estado) as uf,
            
            '' as aos_cuidados,
            'N' as nota_fiscal,
            '' as servico,
            '' as serv_adicionais,
            p.total as valor_declarado,
            '' as observacoes,
            'Ingressos Dreamfest 25' as conteudo,
            '' as ddd,
            '' as telefone,
            c.email,
            '' as chave,
            '0,1' as peso,
            '1' as altura,
            '10' as largura,
            '15' as comprimento,
            '' as entrega_vizinho,
            '' as rfid,
            p.id as pedido_id,
            p.cod_pedido,
            p.rastreio

        FROM pedidos p

        INNER JOIN clientes c ON c.usuario_id = p.user_id

        -- LEFT JOIN: permite que pedidos sem endereço em 'enderecos' apareçam
        LEFT JOIN (
            SELECT e1.pedido_id, e1.cep, e1.endereco, e1.numero, e1.bairro, e1.cidade, e1.estado, e1.created_at
            FROM enderecos e1
            INNER JOIN (
                SELECT pedido_id, MAX(created_at) AS max_updated
                FROM enderecos
                GROUP BY pedido_id
            ) sub ON e1.pedido_id = sub.pedido_id AND e1.created_at = sub.max_updated
        ) e ON e.pedido_id = p.id

        WHERE 
            p.frete = 1 
            AND (p.rastreio IS NULL OR p.rastreio = '') 
            AND p.evento_id = ?
            AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
            
        ORDER BY c.nome ASC
        ";
        
        $query = $this->db->query($sql, [$evento_id]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Conta quantos pedidos precisam de envio
     * 
     * @param int $evento_id ID do evento
     * @return int
     */
    public function contarPedidosParaEnvio(int $evento_id): int
    {
        return $this->db->table('pedidos')
            ->where('frete', 1)
            ->where('evento_id', $evento_id)
            ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'])
            ->groupStart()
                ->where('rastreio IS NULL')
                ->orWhere('rastreio', '')
            ->groupEnd()
            ->countAllResults();
    }
}

