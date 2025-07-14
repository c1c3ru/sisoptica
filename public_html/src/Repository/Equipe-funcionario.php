<?php
namespace Sisoptica\Repository;

include_once MODELS . 'equipe.php';
include_once MODELS . 'funcionario.php';

class EquipeFuncionarioModel extends Database {
    
    const TABLE = "equipe_venda_funcionario";
    
    const EQUIPE = 'id_equipe';
    const FUNCIONARIO = 'id_funcionario';
    const DATA_ENTRADA = 'data_entrada';
    
    public function insert(EquipeFuncionario &$equipeFunc) {
        $fields = array(self::EQUIPE, self::FUNCIONARIO, self::DATA_ENTRADA);
        $values = get_object_vars($equipeFunc);
        return parent::insert(self::TABLE, $fields, $values);
    }
    
    public function inserts($arrEquipeFunc){
        if(empty($arrEquipeFunc)) return true;
        if(!is_array($arrEquipeFunc)) return false;
        $values = array();
        foreach ($arrEquipeFunc as $equipeFunc){
            $vars = get_object_vars($equipeFunc);
            $values[] = "(".Database::turnInValues($vars).")";
        }
        $fields = array(self::EQUIPE, self::FUNCIONARIO, self::DATA_ENTRADA);
        return parent::insert(self::TABLE, $fields, $values);
    }
    
    public function select($fields = " * ", $condition = null) {
        $joins[]    = self::innerJoin('FuncionarioModel', self::FUNCIONARIO);
        $res        = parent::select(self::TABLE . ' ' . implode(' ', $joins), $fields, $condition);
        $equipeFuncs    = array();
        $anna       = $this->getAnnalisses();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $equipeFuncs[] = new EquipeFuncionario (
                isset($row->{self::EQUIPE}) ? $row->{self::EQUIPE} : null,
                isset($row->{self::FUNCIONARIO}) ? $row->{self::FUNCIONARIO} : null,
                isset($row->{self::DATA_ENTRADA}) ? $row->{self::DATA_ENTRADA} : '' 
            );
        }
        return $equipeFuncs;
    }
    
    public function superSelect($condition = null) {
        $fields     = array(self::DATA_ENTRADA, EquipeModel::NOME, FuncionarioModel::NOME);
        $joins[]    = self::innerJoin('EquipeModel', self::EQUIPE);
        $joins[]    = self::innerJoin('FuncionarioModel', self::FUNCIONARIO);
        $res        = parent::select(self::TABLE . ' ' . implode(' ', $joins), $fields, $condition);
        $equipeFuncs    = array();
        $anna       = $this->getAnnalisses();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $equipeFuncs[] = new EquipeFuncionario (
                $row->{EquipeModel::NOME},
                $row->{FuncionarioModel::NOME},
                $row->{self::DATA_ENTRADA} 
            );
        }
        return $equipeFuncs;
    }
    
    public function update(EquipeFuncionario $equipeFunc) {
        $vars = array(
            self::DATA_ENTRADA  => $equipeFunc->dataEntrada
        );
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($vars), self::idCondition($equipeFunc));
    }
    
    public function delete($condition){
        return parent::delete(self::TABLE, $condition);
    }
    
    public static function idCondition(EquipeFuncionario $equipeFunc) {
        return  self::TABLE . '.' . self::EQUIPE . '=' . $equipeFunc->equipe . 
                ' AND ' . self::TABLE . '.' . self::FUNCIONARIO . '=' . $equipeFunc->funcionario;
    }
    
}
?>
