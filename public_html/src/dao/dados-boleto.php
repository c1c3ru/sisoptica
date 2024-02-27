<?php
class DadosBoletoModel extends Database {
    
    const TABLE                 = 'dados_boleto_loja';
    
    const LOJA                  = 'id_loja';
    
    const AGENCIA               = 'agencia';
    
    const CONTA                 = 'conta';
    
    const CONTA_DIGITO          = 'conta_digito';
    
    const CONTA_CEDENTE         = 'conta_cedente';
    
    const CONTA_CEDENTE_DIGITO  = 'conta_cedente_digito';
    
    const CARTEIRA              = 'carteira';
    
    const PADRAO                = 'padrao';
    
    public function select($fields = " * ", $condition = null) {
        $res    = parent::select(self::TABLE, $fields, $condition);
        $anna   = $this->getAnnalisses();
        $dados  = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $dados[] = new DadosBoleto(
                isset($row->{self::LOJA}) ? $row->{self::LOJA} : 0,
                isset($row->{self::AGENCIA}) ? $row->{self::AGENCIA} : "",
                isset($row->{self::CONTA}) ? $row->{self::CONTA} : "",
                isset($row->{self::CONTA_DIGITO}) ? $row->{self::CONTA_DIGITO} : "",
                isset($row->{self::CONTA_CEDENTE}) ? $row->{self::CONTA_CEDENTE} : "",
                isset($row->{self::CONTA_CEDENTE_DIGITO}) ? $row->{self::CONTA_CEDENTE_DIGITO} : "",
                isset($row->{self::CARTEIRA}) ? $row->{self::CARTEIRA} : "",
                isset($row->{self::PADRAO}) ? $row->{self::PADRAO} : false
            );
        }
        return $dados;
    }
    
}
?>
