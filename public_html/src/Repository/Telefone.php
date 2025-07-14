<?php
namespace Sisoptica\Repository;

/**
 * Essa classe implementa o modelo da entidade Telefone.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class TelefoneModel extends Database {
    
    /**
     * Nome da tabela de telefones dos clientes  vendas no banco de dados 
     */
    const TABLE_CLIE = "cliente_telefone";
    
    /**
     * Nome da tabela de telefones de lojas no banco de dados 
     */
    const TABLE_LOJA = "loja_telefone";
    
    /**
     * Nome da tabela de telefones de funcinários no banco de dados 
     */
    const TABLE_FUNC = "funcionario_telefone";
    
    /**
     * Nome da coluna do numero do telefone no banco de dados.
     */
    const NUMERO = "telefone";
    
    /**
     * Nome da coluna do identificador do cliente do telefone no banco de dados.
     */
    const CLIENTE = "id_cliente";
    
    /**
     * Nome da coluna do identificador da loja do telefone no banco de dados.
     */
    const LOJA = "id_loja";
    
    /**
     * Nome da coluna do identificador do funcionário do telefone no banco de dados.
     */
    const FUNCIONARIO = "id_funcionario";
    
    /**
     * Seleciona uma lista de telefones com os <i>fields</i> preenchidos com os valores do banco.
     * @param string $table tabela de telefones (a de loja, de cliente ou de funcionário).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de telefones do tipo Telefone.
     */
    public function select($table, $condition = null) {
        $res = parent::select($table, " * ", $condition);
        $ana = $this->getAnnalisses();
        $telefones = array();
        switch($table){
            case self::TABLE_CLIE: $entityType = self::CLIENTE; break;
            case self::TABLE_LOJA: $entityType = self::LOJA; break;
            case self::TABLE_FUNC: $entityType = self::FUNCIONARIO; break;
            default: return null;
        }
        while(($row = $ana->fetchObject($res)) !== false){
            $telefones[] = new Telefone($row->{self::NUMERO}, $row->{$entityType});
        }
        return $telefones;
    }
    
    /**
     * Insere uma lista de telefones na base de dados.
     * @param string $table tabela de telefones (a de loja, de cliente ou de funcionário).
     * @param srray $telefones lista de telefones do tipo Telefone que vai ser inserida.
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert($table, $telefones) {
        switch($table){
            case self::TABLE_CLIE: $entityType = self::CLIENTE; break;
            case self::TABLE_LOJA: $entityType = self::LOJA; break;
            case self::TABLE_FUNC: $entityType = self::FUNCIONARIO; break;
            default: return null;
        }
        $fields = array(self::NUMERO, $entityType);
        if(is_array($telefones)){
            $values = array();
            foreach ($telefones as $telefone){
                $values[] = "(".Database::turnInValues($telefone).")";
            }
        } else {
            $values = Database::turnInValues($telefones);
        }
        return parent::insert($table, implode(",", $fields), $values);
    }
    
    /**
     * Realiza a remoção de um telefone.
     * @param string $table tabela de telefones (a de loja, de cliente ou de funcionário).
     * @param int $value_id identificador da entidade que está associado ao telefone (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($table, $value_id){
        switch($table){
            case self::TABLE_CLIE: $entityType = self::CLIENTE; break;
            case self::TABLE_LOJA: $entityType = self::LOJA; break;
            case self::TABLE_FUNC: $entityType = self::FUNCIONARIO; break;
            default: return null;
        }
        $condition = "$entityType = $value_id";
        return parent::delete($table, $condition);
    }
    
}
?>
