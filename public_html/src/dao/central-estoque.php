<?php
include_once MODELS."funcionario.php";
include_once MODELS."loja.php";

class CentralEstoqueModel extends Database {
    
    const TABLE = "central_estoque";
    
    const ID                    = "id";
    const DATA                  = "data";
    const VALOR                 = "valor_total";
    const USUARIO_ESTOQUE       = "id_usuario_estoque";
    const USUARIO_CONFIRMACAO_ENTRADA   = "id_usuario_confirmacao_entrada";
    const USUARIO_CONFIRMACAO_SAIDA     = "id_usuario_confirmacao_saida";
    const LOJA_ORIGEM           = "id_loja_origem";
    const LOJA_DESTINO          = "id_loja_destino";
    const STATUS                = "status";
    const OBSERVACAO            = "observacao";
    
    const STATUS_PENDENTE       = 0;
    const STATUS_OK             = 1;
    const STATUS_PROBLEMAS      = 2;
    
    public function insert(CentralEstoque &$centralEstoque) {
        $fields = implode(",", array(
            self::DATA, self::VALOR, self::USUARIO_ESTOQUE, 
            self::USUARIO_CONFIRMACAO_ENTRADA, self::USUARIO_CONFIRMACAO_SAIDA,
            self::LOJA_ORIGEM, self::LOJA_DESTINO, self::STATUS, self::OBSERVACAO
        ));
        $vars = get_object_vars($centralEstoque);
        unset($vars["id"]);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
        if($res){
            $centralEstoque->id = $this->getAnnalisses()->lastInsertedtId();
        }
        return $res;
    }
    
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $centraisEstoques = array();        
        while(($row = $ana->fetchObject($res)) !== false){
            $centraisEstoque = new CentralEstoque(
                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                isset($row->{self::DATA}) ? $row->{self::DATA} : "",
                isset($row->{self::VALOR}) ? $row->{self::VALOR} : 0,
                isset($row->{self::USUARIO_ESTOQUE}) ? $row->{self::USUARIO_ESTOQUE} : null,
                isset($row->{self::USUARIO_CONFIRMACAO_ENTRADA}) ? $row->{self::USUARIO_CONFIRMACAO_ENTRADA} : null,
                isset($row->{self::USUARIO_CONFIRMACAO_SAIDA}) ? $row->{self::USUARIO_CONFIRMACAO_SAIDA} : null,
                isset($row->{self::LOJA_ORIGEM}) ? $row->{self::LOJA_ORIGEM} : null,
                isset($row->{self::LOJA_DESTINO}) ? $row->{self::LOJA_DESTINO} : null,
                isset($row->{self::STATUS}) ? $row->{self::STATUS} : self::STATUS_PENDENTE,
                isset($row->{self::OBSERVACAO}) ? $row->{self::OBSERVACAO} : ""
            );
            $centraisEstoques[] = $centraisEstoque;
        }
        return $centraisEstoques;
    }
    
    public function superSulect($condition = null) {
        $joins[] = " INNER JOIN ".FuncionarioModel::TABLE." F1 ON ".self::USUARIO_ESTOQUE." = F1.".FuncionarioModel::ID;
        $joins[] = " INNER JOIN ".LojaModel::TABLE." L1 ON ".self::LOJA_DESTINO." = L1.".LojaModel::ID;
        $joins[] = " LEFT JOIN ".FuncionarioModel::TABLE." F2 ON ".self::USUARIO_CONFIRMACAO_ENTRADA." = F2.".FuncionarioModel::ID;
        $joins[] = " LEFT JOIN ".FuncionarioModel::TABLE." F3 ON ".self::USUARIO_CONFIRMACAO_SAIDA." = F3.".FuncionarioModel::ID;
        $joins[] = " LEFT JOIN ".LojaModel::TABLE." L2 ON ".self::LOJA_ORIGEM." = L2.".LojaModel::ID;
        $fields = implode(",", array(
            self::TABLE.'.'.self::ID, self::TABLE.".".self::DATA, 
            self::TABLE.".".self::VALOR,
            "F1.".FuncionarioModel::NOME." AS USR_ESTOQUE", 
            "F2.".FuncionarioModel::NOME." AS USR_CONFIRM_ENTRADA",
            "F3.".FuncionarioModel::NOME." AS USR_CONFIRM_SAIDA",
            "L1.".LojaModel::SIGLA." AS LOJA_DESTINO",
            "L2.".LojaModel::SIGLA." AS LOJA_ORIGEM",
            self::TABLE.".".self::STATUS, self::TABLE.'.'.self::OBSERVACAO));
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields, $condition);
        $ana = $this->getAnnalisses();
        $centraisEstoques = array();        
        while(($row = $ana->fetchObject($res)) !== false){
            $centraisEstoque = new CentralEstoque(
                $row->{self::ID},
                $row->{self::DATA},
                $row->{self::VALOR},
                $row->USR_ESTOQUE,
                $row->USR_CONFIRM_ENTRADA,
                $row->USR_CONFIRM_SAIDA,
                $row->LOJA_ORIGEM,
                $row->LOJA_DESTINO,
                $row->{self::STATUS},
                $row->{self::OBSERVACAO}
            );
            $centraisEstoques[] = $centraisEstoque;
        }
        return $centraisEstoques;
    }
 
    public function delete($id_central) {
        $condition = self::ID." = ".$id_central;
        return parent::delete(self::TABLE ,$condition);
    }
    
    public function update(CentralEstoque $centralEstoque) {
        $dic = array(
            self::DATA                  => $centralEstoque->data,
            self::VALOR                 => $centralEstoque->valor,
            self::USUARIO_ESTOQUE       => $centralEstoque->usuarioEstoque,
            self::USUARIO_CONFIRMACAO_ENTRADA   => $centralEstoque->usuarioConfirmacaoEntrada,
            self::USUARIO_CONFIRMACAO_SAIDA     => $centralEstoque->usuarioConfirmacaoSaida,
            self::LOJA_ORIGEM           => $centralEstoque->lojaOrigem,
            self::LOJA_DESTINO          => $centralEstoque->lojaDestino,
            self::STATUS                => $centralEstoque->status,
            self::OBSERVACAO            => $centralEstoque->observacao
        );
        $condition = self::ID." = ".$centralEstoque->id;
        return $this->formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
    
    public function simpleUpdate($fields, $values, $condition){
        return parent::update(self::TABLE, $fields, $values, $condition);
    }
    
}
?>
