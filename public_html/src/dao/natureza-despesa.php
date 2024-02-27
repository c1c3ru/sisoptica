<?php
class NaturezaDespesaModel extends Database {

    const TABLE = 'natureza_despesa';

    const ID    = 'id';

    const NOME  = 'nome';

    const TIPO  = 'tipo';

    const ENTRADA = 'entrada';

    public function insert(NaturezaDespesa $natureza) {
        $fields = array(self::NOME, self::TIPO, self::ENTRADA);
        $vars   = get_object_vars($natureza);
        unset($vars['id']);
        return parent::insert(self::TABLE, $fields, self::turnInValues($vars));
    }

    public function select($fields = " * ", $condition = null) {
        $res    = parent::select(self::TABLE, $fields, $condition);
        $anna   = $this->getAnnalisses();
        $naturezas = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $naturezas[] = new NaturezaDespesa(
                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                isset($row->{self::NOME}) ? $row->{self::NOME} : '',
                isset($row->{self::TIPO}) ? $row->{self::TIPO} : '',
                isset($row->{self::ENTRADA}) ? $row->{self::ENTRADA} : false
            );
        }
        return $naturezas;
    }

    public function delete($natureza) {
        $condition = self::ID.'='.$natureza;
        return parent::delete(self::TABLE, $condition);
    }

    public function update(NaturezaDespesa $natureza) {
        $dic = array(
            self::NOME  => $natureza->nome,
            self::TIPO  => $natureza->tipo,
            self::ENTRADA => $natureza->entrada
        );
        $condition = self::ID.' = '.$natureza->id;
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }

}
?>
