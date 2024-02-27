<?php

/**
 * Essa classe implementa o modelo da entidade Estado.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class EstadoModel extends Database {	

    /**
     * Nome da tabela de vendas no banco de dados 
     */
    const TABLE = "uf";

    /**
     * Nome da coluna do nome do estado no banco de dados.
     */
    const NOME = "nome_uf";
    
    /**
     * Nome da coluna da sigla do estado no banco de dados.
     */
    const SIGLA = "sigla_uf";
    
    /**
     * Nome da coluna do identificador do estado no banco de dados.
     */
    const ID =  "id_uf";

    /**
     * Seleciona uma lista de estados com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de esatdos do tipo Estado.
     */
    public function select($fields = "*", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);

        $this_condition = " 1 = 1 ORDER BY ".self::ID;
        if(is_null($condition)) $condition = $this_condition;
        else $condition .= " AND $this_condition";

        $res = parent::select(self::TABLE, $fields, $condition);		
        $ana = $this->getAnnalisses();		
        while(($row = $ana->fetchObject($res)) !== false) {
            $estado = new Estado( isset($row->{self::ID})? $row->{self::ID} : 0, 
                                  isset($row->{self::NOME})? ($row->{self::NOME}): "",
                                  isset($row->{self::SIGLA})? $row->{self::SIGLA}: "");
            $estados[] = $estado;		
        }
        return $estados;
    } 	
}
?>
