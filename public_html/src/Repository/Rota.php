<?php
namespace Sisoptica\Repository;
//Incluindo classes associadas a esse modelo
include_once MODELS."regiao.php";

/**
 * Essa classe implementa o modelo da entidade Rota.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class RotaModel extends Database{
    
    /**
     * Nome da tabela de rotas no banco de dados 
     */
    const TABLE = "rota";
    
    /**
     * Nome da coluna do identificador da rota no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome da rota no banco de dados.
     */
    const NOME = "nome_rota";
    
    /**
     * Nome da coluna do identificador da região da rota no banco de dados.
     */
    const REGIAO = "id_regiao";
    
    /**
     * Seleciona uma lista de rotas com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de rotas do tipo Rota.
     */
    public function select($fields = " * ", $condition = null) {
        if(strpos($fields, "*") !== FALSE){
            $fields = array(self::TABLE.".".self::ID, self::NOME, self::REGIAO);
        }
        if(is_array($fields)) $fields = implode(",", $fields);
        $condition = is_null($condition) ? $this->conditionsSelect() : $condition . ' AND ' . $this->conditionsSelect();
        $res = parent::select($this->tablesSelect(), $fields, $condition);
        $anna = $this->getAnnalisses();
        $rotas = array();
        while(($row = $anna->fetchObject($res)) !== false){
            $rotas[] = new Rota(  isset($row->{ self::ID })? $row->{ self::ID } : 0,
                                  isset($row->{ self::NOME })? $row->{ self::NOME } : "",
                                  isset($row->{ self::REGIAO })? $row->{ self::REGIAO } : 0 );
                                           
        }
        return $rotas;
    }
    
     /**
     * Seleciona uma lista de rotas com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (região).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de rotas do tipo Rota.
     */
    public function superSelect($condition = null, $limit = null){
        $fields = array(self::TABLE.".".self::ID, self::NOME, RegiaoModel::NOME);
        $fields_joined = implode(",", $fields);
        $condition = is_null($condition) ? $this->conditionsSelect() : $condition . " AND " . $this->conditionsSelect();
        $res = parent::select($this->tablesSelect(), $fields_joined, $condition, $limit);
        $anna = $this->getAnnalisses();
        $rotas = array();
        while(($row = $anna->fetchObject($res)) !== false){
            $rotas[] = new Rota(  $row->{ self::ID },
                                  $row->{ self::NOME },
                                  $row->{ RegiaoModel::NOME } );
                                           
        }
        return $rotas;
    }
    
    /**
     * Insere uma rota na base de dados.
     * @param Rota $rota rota que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Rota $rota) {
        $fields = implode(",", array(self::NOME, self::REGIAO));
        $rota = get_object_vars($rota);
        unset($rota["id"]);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($rota));
        return $res;
    }
    
    /**
     * Realiza a remoção de uma rota.
     * @param int $id_rota identificador da rota (pode string também)..
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_rota) {
        $condition = self::ID." = $id_rota";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Atualiza <i>rota</i> de acordo com o seu identificador.
     * @param Rota $rota rota que vai ser atualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Rota $rota) {
        $dic = array (
            self::NOME => $rota->nome,
            self::REGIAO => $rota->regiao
        );
        return $this->formattedUpdates( self::TABLE, 
                                        $this->turnInUpdateValues($dic), 
                                        self::ID." = ".$rota->id);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $code = $this->errorCode();
        if($code == Annalisses::ERR_DUPLICATE) 
            return "Já existe uma rota com este nome";
        return parent::handleError();
    }

    private function tablesSelect() {
       return self::TABLE . "," . RegiaoModel::TABLE;
    }

    private function conditionsSelect() {
        return RegiaoModel::TABLE.".".RegiaoModel::ID." = ".self::TABLE.".".self::REGIAO .
            " ORDER BY ".RotaModel::TABLE.".".RotaModel::NOME;
    }

}
?>
