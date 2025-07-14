<?php
namespace Sisoptica\Repository;
//Incluindo classes associadas a esse modelo
include_once MODELS."loja.php";
include_once MODELS."funcionario.php";

/**
 * Essa classe implementa o modelo da entidade Regiao.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class RegiaoModel extends Database {
    
    /**
     * Nome da tabela de regiões no banco de dados 
     */
    const TABLE = "regiao";
    
    /**
     * Nome da coluna do identificador da região no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do nome da região no banco de dados.
     */
    const NOME = "nome_regiao";
    
    /**
     * Nome da coluna do identificador do cobrador da região no banco de dados.
     */
    const COBRADOR = "id_funcionario_cobrador";
    
    /**
     * Nome da coluna do identificador da loja da região no banco de dados.
     */
    const LOJA = "id_loja";
    
    /**
     * Seleciona uma lista de regiões com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de regiões do tipo Região.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $regioes = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $regioes[] = new Regiao(isset($row->{ self::ID })? $row->{ self::ID } : 0,
                                    isset($row->{ self::NOME })? ($row->{ self::NOME }) : "",
                                    isset($row->{ self::COBRADOR })? (int) $row->{ self::COBRADOR } : 0,
                                    isset($row->{ self::LOJA })? (int) $row->{ self::LOJA } : 0
                                    );
        }
        return $regioes;
    }
    
    /**
     * Seleciona uma lista de regiões com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (cobrador, loja).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de regiões do tipo Região.
     */
    public function superSelect($condition = null, $limit = null){
        $fields = array( self::TABLE.".".self::ID, self::NOME, 
                         FuncionarioModel::NOME, LojaModel::SIGLA );
        
        $fields_joined = implode(",", $fields);
        
        $this_condition = self::COBRADOR." = ".FuncionarioModel::TABLE.".".  FuncionarioModel::ID;
        $this_condition .= " AND ".self::TABLE.".".self::LOJA." = ".LojaModel::TABLE.".".LojaModel::ID;
        
        if(is_null($condition)) $condition = $this_condition;
        else $condition .= " AND ".$this_condition;
        
        $tables = implode(",", array(self::TABLE, FuncionarioModel::TABLE, LojaModel::TABLE));
        
        $res = parent::select($tables, $fields_joined, $condition, $limit);
        $ana = $this->getAnnalisses();
        $regioes = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $regioes[] = new Regiao($row->{ self::ID },
                                    ($row->{ self::NOME }),
                                    ($row->{ FuncionarioModel::NOME }),
                                    $row->{ LojaModel::SIGLA } );
        }
        return $regioes;
    }
    
    /**
     * Insere uma região na base de dados.
     * @param Regiao $regiao região que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Regiao $regiao) {
        $fields = implode(",", array(self::NOME, self::COBRADOR, self::LOJA));
        $values = Database::turnInValues(array($regiao->nome, $regiao->cobrador, $regiao->loja));
        return parent::insert( self::TABLE, $fields, $values);
    }
    
    /**
     * Realiza a remoção de regiões.
     * @param string $condition condição para remoção de regiões.
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($condition) {
        return parent::delete(self::TABLE, $condition);
    }

    /**
     * Atualiza <i>regiao</i> de acordo com o seu identificador.
     * @param Regiao $regiao regiao que vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Regiao $regiao) {
        $dic = array (
            self::NOME => $regiao->nome,
            self::COBRADOR => $regiao->cobrador,
            self::LOJA => $regiao->loja
        );
        $condition = self::ID." = ".$regiao->id;
        return $this->formattedUpdates(self::TABLE, $this->turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já existe uma região com este nome";
        return parent::handleError();
    }
}
?>
