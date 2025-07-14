<?php
namespace Sisoptica\Repository;
require_once 'database.php';
/**
 * Essa classe implementa o modelo da entidade Cargo.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class CargoModel extends Database {
    
    /**
     * Nome da tabela de cargos no banco de dados 
     */
    const TABLE = "funcionario_cargo";
    
    /**
     * Nome da coluna do identificador da cargo no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome do cargo no banco de dados.
     */
    const NOME = "nome_cargo";
    
    /**
     * Constante que representa o id do cargo de cobrador
     */
    const COD_COBRADOR = 2;
    
    /**
     * Constante que representa o id do cargo de gerente
     */
    const COD_GERENTE = 4;
    
    /**
     * Constante que representa o id do cargo de oculista
     */
    const COD_OCULISTA = 8;
    
    /**
     * Constante que representa o id do cargo de vendedor
     */
    const COD_VENDEDOR = 1;
    
    /**
     * Constante que representa o id do cargo de diretor
     */
    const COD_DIRETOR = 5;
    
    /**
     * Constante que representa o id do cargo de agente de vendas
     */
    const COD_AGENTE = 6;

    /**
     * Constante que representa o id do cargo de líder de equipe
     */
    const COD_LIDER_EQUIPE = 7;

    /**
     * Constante que representa o id do cargo de optometrista
     */
    const COD_OPTOMETRISTA = 8;
    
    /**
     * Seleciona uma lista de cargos com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de cargos do tipo Cargo.
     */
    public function select($table, $fields = " * ", $condition = null, $debug = false)
    {
        if(is_array($fields)) $fields = implode (",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $cargos = array();
        while(($row = $ana->fetchObject($res)) != false) {
            $cargo = new Cargo( isset($row->{self::ID}) ? $row->{self::ID} : 0,
                                isset($row->{self::NOME})? ($row->{self::NOME}): "" );
            $cargos[] = $cargo;		
        }
        return $cargos;
    }
    
}
