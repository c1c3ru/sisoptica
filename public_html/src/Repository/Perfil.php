<?php
namespace Sisoptica\Repository;
require_once 'database.php';
/**
 * Essa classe implementa o modelo da entidade Perfil.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class PerfilModel extends Database {
    
    /**
     * Nome da tabela de perfis no banco de dados 
     */
    const TABLE = "perfil_sistema";
    
    /**
     * Nome da coluna do identificador do perfil no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome do perfil no banco de dados.
     */
    const NOME = "nome_perfil";
    
    /**
     * Seleciona uma lista de perfis com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de perfis do tipo Perfil.
     */
    public function select($table,$fields = " * ", $condition = null, $debug = false) {
        if(is_array($fields)) $fields = implode (",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition, $debug);
        $ana = $this->getAnnalisses();
        $perfis = array();
        while(($row = $ana->fetchObject($res)) != false) {
            $perfil = new Perfil( isset($row->{self::ID})? $row->{self::ID} : 0,
                isset($row->{self::NOME})? ($row->{self::NOME}): "" );
            $perfis[] = $perfil;
        }
        return $perfis;
    }
}

