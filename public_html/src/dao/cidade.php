<?php

/**
 * Essa classe implementa o modelo da entidade Cidade.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class CidadeModel extends Database {
	
    /**
     * Nome da tabela de cidades no banco de dados 
     */
    const TABLE = "cidade";
    
    /**
     * Nome da coluna do identificador da cidade no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome da cidade no banco de dados.
     */
    const NOME = "nome_cidade";
    
    /**
     * Nome da coluna do identificador do estado da cidade no banco de dados.
     */
    const ESTADO = "id_uf";
	
    /**
     * Seleciona uma lista de cidades ordernas pelo nome com os <i>fields</i> preenchidos 
     * com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return PDOStatment lista de cidade do tipo Cidade.
     */
    public function select($table, $fields = "*", $condition = null, $debug = false)
    {
        if (is_array($fields)) {
            $fields = implode(",", $fields);
        }

        $this_condition = " 1 = 1 ORDER BY ".self::NOME;
        if (is_null($condition)) {
            $condition = $this_condition;
        } else {
            $condition .= " AND $this_condition";
        }

        return parent::select($table, $fields, $condition, $debug);
    }

}
?>
