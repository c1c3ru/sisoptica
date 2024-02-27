<?php

/**
 * Essa classe implementa o modelo da entidade Marca.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class MarcaModel extends Database {
    
    /**
     * Nome da tabela de marcas de produtos no banco de dados 
     */
    const TABLE = "marca_produto";
    
    /**
     * Nome da coluna do identificador da marca no banco de dados.
     */
    const ID = "id_marca_produto";
    
    /**
     * Nome da coluna do nome da marca no banco de dados.
     */
    const NOME = "nome_marca_produto";
    
    /**
     * Seleciona uma lista de marcas de produtos com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de marcas de produtos do tipo Marca.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);		
        $ana = $this->getAnnalisses();		
        $marcas = array();
        while(($row = $ana->fetchObject($res)) !== false) {
           $marcas[] = new Marca(isset($row->{self::ID})? $row->{self::ID} : 0, 
                                 isset($row->{self::NOME})? ($row->{self::NOME}): "");	
        }
        return $marcas;
    }
    
    /**
     * Insere uma marca de produto na base de dados.
     * @param Marca $marca marca que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Marca $marca) {
        $values = get_object_vars($marca);
        unset($values["id"]);
        $fields = implode(",", array(self::NOME));
        return parent::insert(self::TABLE, $fields, Database::turnInValues($values));
    }

    /**
     * Atualiza <i>marca</i> de acordo com o seu identificador.
     * @param Marca $marca marca que vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Marca $marca){
        return parent::update(self::TABLE, self::NOME, "'{$marca->nome}'", self::ID." = ".$marca->id);
    }
    
    /**
     * Realiza a remoção de uma marca.
     * @param int $id_marca identificador da marca (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_marca){
        return parent::delete(self::TABLE, self::ID." = $id_marca");
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já existe uma marca com este nome";
        return parent::handleError();
    }
}
?>
