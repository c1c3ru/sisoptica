<?php

/**
 * Essa classe implementa o modelo da entidade TipoProduto.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class TipoProdutoModel extends Database {
    
    /**
     * Nome da tabela de tipos de produtos no banco de dados 
     */
    const TABLE = "tipo_produto";
    
    /**
     * Nome da coluna do identificador do tipo de produto no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome do tipo de produto no banco de dados.
     */
    const NOME = "nome_tipo_produto";
    
    /**
     * Seleciona uma lista de tipos de produtos com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de tipos de produtos do tipo TipoProduto.
     */
    public function select($fields = " * ", $condition = null, $limit = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition, $limit);		
        $ana = $this->getAnnalisses();		
        $tipos = array();
        while(($row = $ana->fetchObject($res)) !== false) {
           $tipos[] = new TipoProduto(  isset($row->{self::ID})? $row->{self::ID} : 0, 
                                        isset($row->{self::NOME})? ($row->{self::NOME}): "");	
        }
        return $tipos;
    }
    
    /**
     * Insere um tipo de produto na base de dados.
     * @param TipoProduto $tipo tipo de produto que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(TipoProduto $tipo) {
        $values = get_object_vars($tipo);
        unset($values["id"]);
        $fields = implode(",", array(self::NOME));
        return parent::insert(self::TABLE, $fields, Database::turnInValues($values));
    }

    /**
     * Atualiza <i>tipo</i> de acordo com o seu identificador.
     * @param TipoProduto $tipo tipo de peodutoque vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(TipoProduto $tipo){
        return parent::update(self::TABLE, self::NOME, "'{$tipo->nome}'", self::ID." = ".$tipo->id);
    }
    
    /**
     * Realiza a remoção de uma tipo de produto.
     * @param int $id_tipo identificador do tipo de produto (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_tipo){
        return parent::delete(self::TABLE, self::ID." = $id_tipo");
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já existe um tipo de produto com este nome";
        return parent::handleError();
    }
}
?>
