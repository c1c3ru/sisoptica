<?php
namespace Sisoptica\Repository;

include_once MODELS.'venda.php';
include_once MODELS.'funcionario.php';

class CancelamentoModel extends Database {
    
    const TABLE = "venda_cancelamento";
    
    const VENDA = "venda_id";
    
    const AUTOR = "funcionario_id_autor";
    
    const AUTORIZADOR = "funcionario_id_autorizador";
    
    const DATA  = "data_cancelamento";
    
    public function insert(Cancelamento $cancelamento) {
        $vars = get_object_vars($cancelamento);
        $fields = array(self::VENDA, self::AUTOR, self::AUTORIZADOR, self::DATA);
        return parent::insert(self::TABLE, $fields, self::turnInValues($vars));
    }
    
    public function select($fields = " * ", $condition = null) {

        if(strpos($fields, '*') !== FALSE){
            $all = implode(',', array(
                self::VENDA, self::DATA,
                'F_AUTOR.id AS f_autor_id',
                'F_AUTORIZADOR.id AS f_autorizador_id'
            ));
            $fields = str_replace('*', $all, $fields);
        }

        $joins[]        = ' INNER JOIN '.  FuncionarioModel::TABLE . ' AS F_AUTOR ON '.
                            self::AUTOR .' = F_AUTOR.' . FuncionarioModel::ID;
        $joins[]        = ' INNER JOIN '.  FuncionarioModel::TABLE . ' AS F_AUTORIZADOR ON '.
                            self::AUTORIZADOR .' = F_AUTORIZADOR.' . FuncionarioModel::ID;
        $joins[]        = self::innerJoin('VendaModel', self::VENDA);
        $res            = parent::select(self::TABLE . implode(' ', $joins), $fields, $condition);
        $cancelamentos  = array();
        $anna           = $this->getAnnalisses();
        while(($row = $anna->fetchObject($res))!== FALSE){
            $cancelamentos[] = new Cancelamento(
                isset($row->{self::VENDA}) ? $row->{self::VENDA} : 0,
                isset($row->f_autor_id) ? $row->f_autor_id: 0,
                isset($row->f_autorizador_id) ? $row->f_autorizador_id: 0,
                isset($row->{self::DATA}) ? $row->{self::DATA} : null
            );
        }
        return $cancelamentos;
    }
    
}
?>
