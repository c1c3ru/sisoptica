<?php
class VeiculoModel extends Database {
    
    const TABLE     = "veiculo";
    
    const ID        = "id";
    
    const NOME      = "nome";
    
    const PLACA     = "placa";
    
    const MOTORISTA = "id_funcionario_motorista";
    
    const LOJA      = "id_loja";
    
	const INATIVO_PARA_CAIXA = "inativo_para_caixa";
	
    public function insert(Veiculo $veiculo) {
        $fields = implode(',', array(
            self::NOME, self::PLACA, self::MOTORISTA, self::LOJA, 
			self::INATIVO_PARA_CAIXA
        ));
        $vars = get_object_vars($veiculo);
        unset($vars['id']);
        return parent::insert(self::TABLE, $fields, self::turnInValues($vars));
    }
    
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res    = parent::select(self::TABLE, $fields, $condition);
        $anna   = $this->getAnnalisses();
        $veiculos = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $veiculos[] = new Veiculo(isset($row->{self::ID})?$row->{self::ID}:0, 
                                      isset($row->{self::NOME})?$row->{self::NOME}:'', 
                                      isset($row->{self::PLACA})?$row->{self::PLACA}:'', 
                                      isset($row->{self::MOTORISTA})?$row->{self::MOTORISTA}:null,
                                      isset($row->{self::LOJA})?$row->{self::LOJA}:null,
									  isset($row->{self::INATIVO_PARA_CAIXA})?$row->{self::INATIVO_PARA_CAIXA}:FALSE);
        }
        return $veiculos;
    }
    
    public function superSelect($condition = null){
        $fields = array(
            self::TABLE.'.'.self::ID, self::TABLE.'.'.self::NOME, 
            self::PLACA, FuncionarioModel::TABLE.'.'.FuncionarioModel::NOME, 
            LojaModel::SIGLA, self::TABLE.'.'.self::INATIVO_PARA_CAIXA
        );
        $joins[]= self::leftJoin('FuncionarioModel', self::TABLE.'.'.self::MOTORISTA);
        $joins[]= self::leftJoin('LojaModel', self::TABLE.'.'.self::LOJA);
        $res    = parent::select(self::TABLE.' '.implode(' ', $joins), implode(',',$fields), $condition);
        $anna   = $this->getAnnalisses();
        $veiculos = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $veiculos[] = new Veiculo($row->{self::ID}, 
                                      $row->{self::NOME}, 
                                      $row->{self::PLACA}, 
                                      $row->{FuncionarioModel::NOME},
                                      $row->{LojaModel::SIGLA},
									  $row->{self::INATIVO_PARA_CAIXA});
        }
        return $veiculos;
    }
    
    public function delete($veiculo) {
        $condition = self::ID.' = '.$veiculo;
        return parent::delete(self::TABLE, $condition);
    }
    
    public function update(Veiculo $veiculo) {
        $dic = array(
            self::NOME      => $veiculo->nome,
            self::PLACA     => $veiculo->placa,
            self::MOTORISTA => $veiculo->motorista,
            self::LOJA      => $veiculo->loja,
			self::INATIVO_PARA_CAIXA => $veiculo->inativoParaCaixa
        );
        $condition = self::ID.' = '.$veiculo->id;
        return parent::formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
}
?>
