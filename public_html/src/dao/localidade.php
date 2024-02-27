<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."rota.php";
include_once MODELS."cidade.php";

/**
 * Essa classe implementa o modelo da entidade Localidade.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class LocalidadeModel extends Database {
    
    /**
     * Nome da tabela de loclaidades no banco de dados 
     */
    const TABLE = "localidade";
    
    /**
     * Nome da coluna do identificador da localidade no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome da localidade no banco de dados.
     */
    const NOME = "nome_localidade";
    
    /**
     * Nome da coluna do identificador da cidade da localidade no banco de dados.
     */
    const CIDADE = "id_cidade";
    
    /**
     * Nome da coluna do identificador da rota da localidade no banco de dados.
     */
    const ROTA = "id_rota";
    
    /**
     * Nome da coluna da sequência da localidade na rota no banco de dados.
     */
    const SEQ_ROTA = "seq_rota";
    
    /**
     * Insere uma localidade na base de dados.
     * @param Localidade $localidade localidade que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Localidade $localidade) {
        $fields = implode(",", array(self::NOME, self::CIDADE, self::ROTA, self::SEQ_ROTA));
        $localidade = get_object_vars($localidade);
        unset($localidade["id"]);
        unset($localidade["loja"]);
        unset($localidade["regiao"]);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($localidade));
        return $res;
    }
    
    /**
     * Seleciona uma lista de localidades com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de localidades do tipo Localidade.
     */
    public function select($fields = "*", $condition = null) {
        
        if(is_array($fields)) $fields = implode(",", $fields);
        
        $tables = array(self::TABLE);
        //Adaptação de selção para verificar de qual loja são as localidades.
        if(strpos($fields, RegiaoModel::LOJA)){

            //Ajustando a condição
            $this_condition = self::ROTA." = ".RotaModel::TABLE.".".RotaModel::ID;
            $this_condition .= " AND ".RotaModel::REGIAO." = ".RegiaoModel::TABLE.".".RegiaoModel::ID;
            if(is_null($condition)) $condition = $this_condition;
            else $condition .= " AND $this_condition";
            
            //Adicionando os NATURAL JOINs
            $tables[] = RotaModel::TABLE; 
            $tables[] = RegiaoModel::TABLE;
            
        }
        
        $res = parent::select(implode(",",$tables), $fields, $condition);
        $ana = $this->getAnnalisses();
        $localidades = array();
        
        while(($row = $ana->fetchObject($res)) !== false){
            $localidade = new Localidade( isset($row->{ self::ID })? $row->{ self::ID } : 0,
                                          isset($row->{ self::NOME })? ($row->{ self::NOME }) : "",
                                          isset($row->{ self::CIDADE })? $row->{ self::CIDADE } : 0,
                                          isset($row->{ self::ROTA })? $row->{ self::ROTA } : 0,
                                          isset($row->{ self::SEQ_ROTA })? $row->{ self::SEQ_ROTA } : 0 );
                                           
            if(isset($row->{RegiaoModel::LOJA})){
                $localidade->loja = $row->{RegiaoModel::LOJA};
            }
            if(isset($row->{RegiaoModel::ID})){
                $localidade->regiao = $row->{RegiaoModel::ID};
            }
            $localidades[] = $localidade;
        }
        return $localidades;
    }
    
    /**
     * Seleciona uma lista de localidades com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (vendedor, cliente, os, etc.).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de localidades do tipo Localidade.
     */
    public function superSelect($condition = null){
        
        $fields = array( self::TABLE.".".self::ID, self::NOME, 
                         CidadeModel::NOME, RotaModel::NOME, self::SEQ_ROTA,
                         RegiaoModel::LOJA, RegiaoModel::NOME );
        
        $fields_joined = implode(",", $fields);
        
        $this_condition = self::TABLE.".".self::CIDADE." = ". CidadeModel::TABLE.".".  CidadeModel::ID;
        $this_condition .= " AND ".self::TABLE.".".self::ROTA." = ".RotaModel::TABLE.".".RotaModel::ID;
        $this_condition .= " AND ".RotaModel::REGIAO." = ".RegiaoModel::TABLE.".".RegiaoModel::ID;
        
        if(empty($condition)) $condition = $this_condition;
        else $condition .= " AND ".$this_condition;
        
        $tables = implode(",", array(self::TABLE, CidadeModel::TABLE, 
                                     RotaModel::TABLE, RegiaoModel::TABLE));
        
        $res = parent::select($tables, $fields_joined, $condition);
        $ana = $this->getAnnalisses();
        
        $localidades = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $localidade = new Localidade( $row->{ self::ID },
                                          $row->{ self::NOME },
                                          $row->{ CidadeModel::NOME },
                                          $row->{ RotaModel::NOME },
                                          $row->{ self::SEQ_ROTA } );
            
            $localidade->loja   = $row->{RegiaoModel::LOJA};
            $localidade->regiao = $row->{RegiaoModel::NOME};
            $localidades[] = $localidade;
                                           
        }
        return $localidades;
    }

    /**
     * Obtém o valor máximo da sequência da rota.
     * @param int $rota_id identificador da rota.
     * @return int valor máximo de seq_rota
     */
    public function maxPositionInRota($rota_id){
        $res = parent::select(self::TABLE, "MAX(".self::SEQ_ROTA.") as seq", self::ROTA." = $rota_id ");
        $anna = $this->getAnnalisses();
        if(($row = $anna->fetchObject($res))!== FALSE){
            return (int)$row->seq;
        }
        return 0;
    }
    
    /**
     * Realiza a remoção de uma ou mais localidades localidade.
     * @param int $condition filtra a remoção de localidades (WHERE)
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($condition) {
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Atualiza <i>loclaidade</i> de acordo com o seu identificador.
     * @param Localidade $localidade localidade que vai ser aualizada
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function update(Localidade $localidade) {
        $values = array(self::CIDADE => (int) $localidade->cidade, 
                        self::NOME => $localidade->nome,
                        self::SEQ_ROTA => $localidade->ordem,
                        self::ROTA => $localidade->rota );
        $condition = self::ID." = ".$localidade->id;;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($values), $condition);
    }

    /**
     * Atualiza <i>field</i> com <i>value</i> de acordo com <i>condition</i>.
     * @param string $value campo da tabela
     * @param string $value valor a atribuido
     * @param string $condition cláusula WHERE
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function simpleUpdate($field, $value, $condition) {
        return parent::update(self::TABLE, $field, $value, $condition);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        switch ($err_code) {
            case Annalisses::ERR_DUPLICATE: return "Já existe uma localidade com este nome";
            case Annalisses::ERR_FK_VIOLATION: return "Possivelmente um cliente esteja associado à essa localidade";
            default: return parent::handleError();
        }
    }
}

?>
