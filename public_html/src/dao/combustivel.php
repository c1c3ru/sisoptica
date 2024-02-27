<?php
class CombustivelModel extends Database {
    
    const TABLE     = 'despesa_combustivel';
    
    const ID        = 'id';
    
    const LITROS    = 'litros';
    
    const PRECO     = 'preco';
    
    const KM_INICIAL    = 'km_inicial';
    
    const KM_FINAL      = 'km_final';
    
    const DESPESA   = 'id_despesa';
    
    public function insert(Combustivel $combustivel) {
        $fields = array(self::LITROS, self::PRECO,self::KM_INICIAL, 
                        self::KM_FINAL, self::DESPESA);
        $vars   = get_object_vars($combustivel);
        unset($vars['id']);
        return parent::insert(self::TABLE, $fields, self::turnInValues($vars));
    }
    
    public function select($fields = " * ", $condition = null) {
        $res    = parent::select(self::TABLE, $fields, $condition);
        $anna   = $this->getAnnalisses();
        $combustiveis = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $combustiveis[] = new Combustivel(
                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                isset($row->{self::LITROS}) ? $row->{self::LITROS} : 0,
                isset($row->{self::PRECO}) ? $row->{self::PRECO} : 0,
                isset($row->{self::KM_INICIAL}) ? $row->{self::KM_INICIAL} : 0,
                isset($row->{self::KM_FINAL}) ? $row->{self::KM_FINAL} : 0,
                isset($row->{self::DESPESA}) ? $row->{self::DESPESA} : 0
            );
        }
        return $combustiveis;
    }
    
    public function delete($combustivel) {
        $condition = self::ID.' = '.$combustivel;
        return parent::delete(self::TABLE, $condition);
    }
    
    public function update(Combustivel $combustivel) {
        $dic = array(
            self::LITROS        => $combustivel->litros,
            self::PRECO         => $combustivel->preco,
            self::KM_INICIAL    => $combustivel->kmInicial,
            self::KM_FINAL      => $combustivel->kmFinal,
            self::DESPESA       => $combustivel->despesa
        );
        $condition = self::ID.' = '.$combustivel->id;
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
    
}
?>
