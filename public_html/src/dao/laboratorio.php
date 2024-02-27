<?php
/**
 * Essa classe implementa o modelo da entidade Laboratorio.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class LaboratorioModel extends Database {
    
    /**
     * Nome da tabela de laboratórios no banco de dados 
     */
    const TABLE = "laboratorio";
    
    /**
     * Nome da coluna do identificador do laboratório no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome do laboratório no banco de dados.
     */
    const NOME = "nome";
    
    /**
     * Nome da coluna do telefone do laboratório no banco de dados.
     */
    const TELEFONE = "telefone";
    
    /**
     * Nome da coluna do CPNJ do laboratório no banco de dados.
     */
    const CNPJ = "cnpj";
    
    /**
     * Nome da coluna que indica se o laboratório em questão é o principal do banco de dados.
     */
    const PRINCIPAL = "principal";
    
    /**
     * Insere um laboratório na base de dados.
     * @param Laboratorio $laboratorio Laboratorio que vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Laboratorio $laboratorio) {
        
        if($laboratorio->principal){
            parent::update(self::TABLE, self::PRINCIPAL, 0, "1 = 1" );
        }
        
        $values = get_object_vars($laboratorio);
        unset($values["id"]);
        $fields = implode(",", array(self::NOME, self::TELEFONE, self::CNPJ, self::PRINCIPAL));
        return parent::insert(self::TABLE, $fields, Database::turnInValues($values));
    }
    
    /**
     * Seleciona uma lista de laboratórios com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de laboratórios do tipo Laboratorio.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $laboratorios = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $laboratorios[] = new Laboratorio(isset($row->{ self::ID })? $row->{ self::ID } : 0,
                                isset($row->{ self::NOME })? ($row->{ self::NOME }) : "",
                                isset($row->{ self::TELEFONE })? $row->{ self::TELEFONE } : "",
                                isset($row->{ self::CNPJ })? $row->{ self::CNPJ } : "" ,
                                isset($row->{ self::PRINCIPAL})? $row->{ self::PRINCIPAL }: false        
                                );
        }
        return $laboratorios;
    }
    
    /**
     * Atualiza <i>$laboratorio</i> de acordo com o seu identificador.
     * @param Laboratorio $laboratorio laboratório que vai ser aualizado
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function update(Laboratorio $laboratorio) {
        
        if($laboratorio->principal){
            parent::update(self::TABLE, self::PRINCIPAL, '0'); 
        }
        
        $dic = array(
            self::NOME => $laboratorio->nome,
            self::TELEFONE => $laboratorio->telefone,
            self::CNPJ => $laboratorio->cnpj,
            self::PRINCIPAL => $laboratorio->principal
        );
        $condition = self::ID." = ".$laboratorio->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Realiza a remoção de um laboratório.
     * @param int $id_venda identificador do laboratório (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_laboratorio){
        $condition = self::ID." = $id_laboratorio";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já existe um laboratório com este nome ou CNPJ";
        return parent::handleError();
    }
}

?>
