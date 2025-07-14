<?php
namespace Sisoptica\Repository;

include_once MODELS . 'loja.php';
include_once MODELS . 'funcionario.php';

class EquipeModel extends Database {
    
    const TABLE = "equipe_venda";
    
    const ID    = "id";
    const NOME  = "nome";
    const LOJA  = "id_loja";
    const LIDER = "id_funcionario_lider";
    
    public function insert(Equipe &$equipe) {
        $fields = array(self::NOME, self::LOJA, self::LIDER);
        $values = get_object_vars($equipe);
        unset($values['id']);
        unset($values['integrantes']);
        if(parent::insert(self::TABLE, $fields, self::turnInValues($values))){
            $equipe->id = $this->getAnnalisses()->lastInsertedtId();
            return true;
        }
        return false;
    }
    
    public function select($fields = " * ", $condition = null) {
        $res        = parent::select(self::TABLE, $fields, $condition);
        $equipes    = array();
        $anna       = $this->getAnnalisses();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $equipes[] = new Equipe (
                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                isset($row->{self::NOME}) ? $row->{self::NOME} : "",
                isset($row->{self::LOJA}) ? $row->{self::LOJA} : null,
                isset($row->{self::LIDER}) ? $row->{self::LIDER} : null 
            );
        }
        return $equipes;
    }
    
    public function superSelect($condition = null) {
        $fields     = array(self::TABLE.'.'.self::ID, self::TABLE.'.'.self::NOME, 
                            LojaModel::SIGLA, FuncionarioModel::NOME);
        $joins[]    = self::innerJoin('LojaModel', self::LOJA);
        $joins[]    = self::innerJoin('FuncionarioModel', self::LIDER);
        $res        = parent::select(self::TABLE . ' ' . implode(' ', $joins), $fields, $condition);
        $equipes    = array();
        $anna       = $this->getAnnalisses();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $equipes[] = new Equipe (
                $row->{self::ID},
                $row->{self::NOME},
                $row->{LojaModel::SIGLA},
                $row->{FuncionarioModel::NOME} 
            );
        }
        return $equipes;
    }
    
    public function update(Equipe $equipe) {
        $vars = array(
            self::NOME  => $equipe->nome,
            self::LOJA  => $equipe->loja,
            self::LIDER => $equipe->lider
        );
        $condition = self::ID . " = " . $equipe->id;
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($vars), $condition);
    }
    
    public function delete(Equipe $equipe) {
        $condition = self::ID . " = " . $equipe->id;
        return parent::delete(self::TABLE, $condition);
    }

    public function handleError(){
        $errorCode = $this->errorCode();
        if ($errorCode == Annalisses::ERR_DUPLICATE) {
            return "Integrantes e líderes só podem pertecer a uma equipe";
        } else return parent::handleError();
    }
}

?>
