<?php

//Importando os modelos relacionados
include_once MODELS.'funcionario.php';

/**
 * Essa classe implementa o modelo da entidade PrestacaoConta.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class PrestacaoContaModel extends Database {
    
    /**
     * Nome da tabela de prestação de conta no banco de dados 
     */
    const TABLE = 'prestacao_conta';
    
    /**
     * Nome da coluna do identificador da prestação de conta no banco de dados.
     */
    const ID        = 'id';
    
    /**
     * Nome da coluna do cobrador da prestação de conta no banco de dados.
     */
    const COBRADOR  = 'id_cobrador';
    
    /**
     * Nome da coluna do status (fechada ou aberta) da prestação de conta no banco de dados.
     */
    const STATUS    = 'status';
    
    /**
     * Nome da coluna da data inicial de arrecadação da prestação de conta no banco de dados.
     */
    const DT_INICIAL= 'dt_inicial_arrecadacao';
    
    /**
     * Nome da coluna da data final de arrecadação da prestação de conta no banco de dados.
     */
    const DT_FINAL  = 'dt_final_arrecadacao';
    
    /**
     * Nome da coluna do valor sequêncial do cobrador da prestação de conta no banco de dados.
     */
    const SEQ       = 'seq';
    
    /**
     * Nome da coluna que iindica o cancelamento da prestação de conta no banco de dados.
     */
    const CANCELADA = 'cancelada';
    
    /**
     * Nome da coluna que iindica a prestação de conta foi auditada.
     */
    const AUDITADA = 'auditada';


    /**
     * Valor do status para quando a prestação de contas está aberta
     */
    const STATUS_ABERTA = 0;
    
    /**
     * Valor do status para quando a prestação de contas está fechada
     */
    const STATUS_FECHADA = 1;
    
    /**
     * Seleciona uma lista de prestações de conta com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de prestações de conta do tipo PrestacaoConta.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_string($fields) && strpos($fields, "*") !== FALSE){
            $fields = array(
                self::TABLE.'.'.self::ID, self::TABLE.'.'.self::STATUS, self::SEQ,
                self::COBRADOR, self::DT_INICIAL, self::DT_FINAL, self::CANCELADA,
                self::AUDITADA
            );
        }
        if(is_array($fields)) $fields = implode(",", $fields);
        $joins[]    = self::leftJoin('FuncionarioModel', self::TABLE.'.'.self::COBRADOR);
        $res        = parent::select(self::TABLE.' '.implode(' ', $joins), $fields, $condition);
        $ana        = $this->getAnnalisses();		
        $prestacoes = array();
        while(($row = $ana->fetchObject($res)) !== false) {
            $prestacoes[] = new PrestacaoConta(
                isset($row->{self::ID})         ? $row->{self::ID}:0 , 
                isset($row->{self::COBRADOR})   ? $row->{self::COBRADOR}:0 ,
                isset($row->{self::STATUS})     ? $row->{self::STATUS}:0,
                isset($row->{self::DT_INICIAL}) ? $row->{self::DT_INICIAL}:0, 
                isset($row->{self::DT_FINAL})   ? $row->{self::DT_FINAL}: 0,
                isset($row->{self::SEQ})        ? $row->{self::SEQ}: 0,
                isset($row->{self::CANCELADA})  ? $row->{self::CANCELADA} : false,
                isset($row->{self::AUDITADA})   ? $row->{self::AUDITADA} : false
            );	
        }
        return $prestacoes;
    }
    
     /**
     * Seleciona uma lista de prestações de conta com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (cobrador, etc).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de prestações de conta do tipo PrestacaoConta.
     */
    public function superSelect($condition = null){
        $fields = implode(',', array(
            self::TABLE.'.'.self::ID, self::TABLE.'.'.self::STATUS, self::SEQ, self::CANCELADA,
            FuncionarioModel::TABLE.'.'.FuncionarioModel::NOME, self::DT_INICIAL, self::DT_FINAL,
            self::AUDITADA
        ));
        $joins[]    = self::leftJoin('FuncionarioModel', self::TABLE.'.'.self::COBRADOR);
        $res        = parent::select(self::TABLE.' '.implode(' ', $joins), $fields, $condition);		
        $ana        = $this->getAnnalisses();		
        $prestacoes = array();
        while(($row = $ana->fetchObject($res)) !== false) {
            $prestacoes[] = new PrestacaoConta(
                $row->{self::ID},        
                $row->{FuncionarioModel::NOME},
                $row->{self::STATUS},
                $row->{self::DT_INICIAL}, 
                $row->{self::DT_FINAL},
                $row->{self::SEQ},
                $row->{self::CANCELADA},
                $row->{self::AUDITADA}
            );	
        }
        return $prestacoes;
    }
    
    /**
     * Insere uma prestação de conta na base de dados.
     * @param PrestacaoConta $prestacao referência da prestação de conta que vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(PrestacaoConta &$prestacao) {
        $fields = implode(',', array(
            self::COBRADOR, self::STATUS, self::DT_INICIAL, self::DT_FINAL, 
            self::SEQ, self::CANCELADA, self::AUDITADA
        ));
        $vars = get_object_vars($prestacao);
        unset($vars['id']);
        unset($vars['itens']);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
        if($res){
            $prestacao->id = $this->getAnnalisses()->lastInsertedtId();
        }
        return $res;
    }
    
    /**
     * Atualiza <i>$prestacao</i> de acordo com o seu identificador.
     * @param PrestacaoConta $prestacao prestação de conta que vai ser atualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(PrestacaoConta $prestacao) {
        $dic = array(
            self::STATUS    => $prestacao->status,
            self::DT_INICIAL=> $prestacao->dtInicial,
            self::DT_FINAL  => $prestacao->dtFinal,
            self::CANCELADA => $prestacao->cancelada,
            self::AUDITADA  => $prestacao->auditada
        );
        $condition = self::ID.' = '.$prestacao->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Atualiza <i>field</i> com <i>value</i> de acordo com <i>condition</i>.
     * @param string $value campo da tabela
     * @param string $value valor a atribuido
     * @param string $condition cláusula WHERE
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function simpleUpdate($field, $value, $condition){
        return parent::update(self::TABLE, $field, $value, $condition);
    }
    
    /**
     * Realiza a remoção de um aprestação de conta.
     * @param int $id_prestacao identificador da prestação de conta (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_prestacao) {
        $condition = self::ID.' = '.$id_prestacao;
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Obtém o valor máximo da coluna de sequência atribuido a um cobrador.
     * @param int $id_prestacao identificador do cobrador de conta (pode string também).
     * @return int valor máximo da coluna de sequência
     */
    public function maxSeq($id_cobrador){
        $field      = 'MAX('.self::SEQ.') AS MAX';
        $condition  = self::COBRADOR.'='.$id_cobrador;
        $res        = parent::select(self::TABLE, $field, $condition);
        $anna       = $this->getAnnalisses();
        if(($row = $anna->fetchObject($res))!== FALSE){
            return $row->MAX;
        }
        return 0;
    }
    
}
?>
