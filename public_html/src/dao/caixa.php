<?php
class CaixaModel extends Database {
    
    const TABLE             = 'caixa_diario';
    
    const ID                = 'id';
    
    const DATA              = 'data';
    
    const SALDO             = 'saldo';
    
    const STATUS            = 'status';
    
    const LOJA              = 'loja';
    
    const STATUS_ABERTO     = 0;
    
    const STATUS_FECHADO    = 1;
    
    public function insert(Caixa &$caixa) {
        $fields = array(self::DATA, self::SALDO, self::STATUS, self::LOJA);
        $vars   = get_object_vars($caixa);
        unset($vars['id']);
        if(parent::insert(self::TABLE, $fields, self::turnInValues($vars))){
            $caixa->id = $this->getAnnalisses()->lastInsertedtId();
            return true;
        }
        return false;
    }
    
    public function select($fields = " * ", $condition = null) {
        $res    = parent::select(self::TABLE, $fields, $condition);
        $anna   = $this->getAnnalisses();
        $caixas = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $caixas[] = new Caixa(
                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                isset($row->{self::DATA}) ? $row->{self::DATA} : '',
                isset($row->{self::SALDO}) ? $row->{self::SALDO} : 0,
                isset($row->{self::STATUS}) ? $row->{self::STATUS} : self::STATUS_ABERTO,
                isset($row->{self::LOJA}) ? $row->{self::LOJA} : 0
            );
        }
        return $caixas;
    }
    
    public function delete($caixa) {
        $condition = self::ID.' = '.$caixa;
        return parent::delete(self::TABLE, $condition);
    }
    
    public function update(Caixa $caixa) {
        $dic = array(
            self::DATA      => $caixa->data,
            self::SALDO     => $caixa->saldo,
            self::STATUS    => $caixa->status,
            self::LOJA      => $caixa->loja
        );
        $condition = self::ID.' = '.$caixa->id;
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
    
    public function handleError() {
        if($this->errorCode() == Annalisses::ERR_DUPLICATE){
            return "Já existe um caixa desse dia";
        }
        return parent::handleError();
    }
    
}
?>
