<?php
class DespesaModel extends Database {
    
    const TABLE         = 'despesa_caixa';
    
    const ID            = 'id';
    
    const VALOR         = 'valor';
    
    const NATUREZA      = 'id_natureza';
    
    const ENTIDADE      = 'id_entidade';
    
    const OBSERVACAO    = 'observacao';
    
    const CAIXA         = 'id_caixa';
    
    public function insert(Despesa &$despesa) {
        $fields = array(self::VALOR, self::NATUREZA,self::ENTIDADE, 
                        self::OBSERVACAO, self::CAIXA);
        $vars   = get_object_vars($despesa);
        unset($vars['id']);
        if(parent::insert(self::TABLE, $fields, self::turnInValues($vars))){
            $despesa->id = $this->getAnnalisses()->lastInsertedtId();
            return true;
        } 
        return false;
    }
    
    public function select($fields = " * ", $condition = null, $joins = array()) {
        $res    = parent::select(self::TABLE.' '.implode(' ',$joins), $fields, $condition);
        $anna   = $this->getAnnalisses();
        $despesas = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $despesas[] = new Despesa(
                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                isset($row->{self::VALOR}) ? $row->{self::VALOR} : 0,
                isset($row->{self::NATUREZA}) ? $row->{self::NATUREZA} : 0,
                isset($row->{self::ENTIDADE}) ? $row->{self::ENTIDADE} : 0,
                isset($row->{self::OBSERVACAO}) ? $row->{self::OBSERVACAO} : '',
                isset($row->{self::CAIXA}) ? $row->{self::CAIXA} : 0
            );
        }
        return $despesas;
    }
    
    public function delete($despesa) {
        $condition = self::ID.' = '.$despesa;
        return parent::delete(self::TABLE, $condition);
    }
    
    public function update(Despesa $despesa) {
        $dic = array(
            self::VALOR         => $despesa->valor,
            self::NATUREZA      => $despesa->natureza,
            self::ENTIDADE      => $despesa->entidade,
            self::OBSERVACAO    => $despesa->observacao,
            self::CAIXA         => $despesa->caixa
        );
        $condition = self::ID.' = '.$despesa->id;
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
    
}
?>
