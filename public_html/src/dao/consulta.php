<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."funcionario.php";

/**
 * Essa classe implementa o modelo da entidade Consulta.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ConsultaModel extends Database{
    
    /**
     * Nome da tabela de consultas no banco de dados 
     */
    const TABLE = "consulta";
    
    /**
     * Nome da coluna do identificador da consulta no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna do identificador da venda da consulta no banco de dados.
     */
    const VENDA = "id_venda";
    
    /**
     * Nome da coluna do nome do paciente da consulta no banco de dados.
     */
    const NOME_PACIENTE = "nome_paciente";
    
    /**
     * Nome da coluna do valor do esférico direito da consulta no banco de dados.
     */
    const ESFERICO_OD   = "esferico_od";
    
    /**
     * Nome da coluna do valor do esférico direito da consulta no banco de dados.
     */
    const ESFERICO_OE   = "esferico_oe";
    
    /**
     * Nome da coluna do valor do clindrico direito da consulta no banco de dados.
     */
    const CILINDRICO_OD = "cilindrico_od";
    
    /**
     * Nome da coluna do valor do cilindrico esquerdo da consulta no banco de dados.
     */
    const CILINDRICO_OE = "cilindrico_eq";
    
    /**
     * Nome da coluna do valor do eixo direito da consulta no banco de dados.
     */
    const EIXO_OD       = "eixo_od";
    
    /**
     * Nome da coluna do valor eixo esquerdo da consulta no banco de dados.
     */
    const EIXO_OE       = "eixo_oe";
    
    /**
     * Nome da coluna do valor DNP direito  da consulta no banco de dados.
     */
    const DNP_OD        = "dnp_od";
    
    /**
     * Nome da coluna do valor do DNP esquerdo da consulta no banco de dados.
     */
    const DNP_OE        = "dnp_oe";
    
    /**
     * Nome da coluna do valor do DP da consulta no banco de dados.
     */
    const DP            = "dp";
    
    /**
     * Nome da coluna da adicao da consulta no banco de dados.
     */
    const ADICAO        = "adicao";
    
    /**
     * Nome da coluna do valor da altura da consulta no banco de dados.
     */
    const ALTURA        = "altura";
    
    /**
     * Nome da coluna do valor do CO da consulta no banco de dados.
     */
    const CO            = "co";
    
    /**
     * Nome da coluna da cor da armação da consulta no banco de dados.
     */
    const COR           = "cor";
    
    /**
     * Nome da coluna da lente da armação da consulta no banco de dados.
     */
    const LENTE         = "lente";
    
    /**
     * Nome da coluna de observação sobre a consulta no banco de dados.
     */
    const OBSERVACAO    = "observacao";
    
    /**
     * Nome da coluna do identificador oculista da consulta no banco de dados.
     */
    const OCULISTA      = "id_funcionario_oculista";
    
    /**
     * Insere uma consulta na base de dados e, em caso de sucesso, atribui o id inserido.
     * @param Consulta $consulta consulta que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Consulta &$consulta) {
        $fields = implode(",", array(self::VENDA,
            self::NOME_PACIENTE, self::ESFERICO_OD, self::ESFERICO_OE,
            self::CILINDRICO_OD, self::CILINDRICO_OE, self::EIXO_OD, self::EIXO_OE,
            self::DNP_OD, self::DNP_OE,
            self::DP, self::ADICAO, self::ALTURA,
            self::CO, self::COR, self::LENTE, self::OBSERVACAO, self::OCULISTA
        ));
        $vars = get_object_vars($consulta);
        unset($vars["id"]);
        $values = Database::turnInValues($vars);
        if(parent::insert(self::TABLE, $fields, $values)){
            $consulta->id = $this->getAnnalisses()->lastInsertedtId();
            return true;
        }
        return false;
    }
    /**
     * Atualiza <i>consulta</i> de acordo com o seu identificador.
     * @param Consulta $consulta consulta que vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Consulta $consulta) {
        $dic = array(
            self::VENDA         => $consulta->venda,
            self::NOME_PACIENTE => $consulta->nomePaciente,
            self::ESFERICO_OD   => $consulta->esfericoOD,
            self::ESFERICO_OE   => $consulta->esfericoOE,
            self::CILINDRICO_OD => $consulta->cilindricoOD,
            self::CILINDRICO_OE => $consulta->cilindricoOE,
            self::EIXO_OD       => $consulta->eixoOD,
            self::EIXO_OE       => $consulta->eixoOE,
            self::DNP_OD        => $consulta->dnpOD,
            self::DNP_OE        => $consulta->dnpOE,
            self::DP            => $consulta->dp,
            self::ADICAO        => $consulta->adicao,
            self::ALTURA        => $consulta->altura,
            self::CO            => $consulta->co,
            self::COR           => $consulta->cor,
            self::LENTE         => $consulta->lente,
            self::OBSERVACAO    => $consulta->observacao,
            self::OCULISTA      => $consulta->oculista
        );
        $condition = self::ID." = {$consulta->id}";
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Seleciona uma lista de consultas com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (Oculista).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de consultas do tipo Consulta.
     */
    public function superSelect($condition = null){
        $fields =  array(
            self::TABLE.".".self::ID, self::VENDA,
            self::NOME_PACIENTE, self::ESFERICO_OD, self::ESFERICO_OE,
            self::DNP_OD, self::DNP_OE,
            self::CILINDRICO_OD, self::CILINDRICO_OE, self::EIXO_OD,
            self::EIXO_OE, self::DP, self::ADICAO, self::ALTURA,
            self::CO, self::COR, self::LENTE, self::OBSERVACAO, FuncionarioModel::NOME
        );
        
        $fields_joined = implode(",", $fields);
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE." ON ".self::OCULISTA." = ".FuncionarioModel::TABLE.".".FuncionarioModel::ID;
    
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields_joined, $condition);
        $ana = $this->getAnnalisses();
        $consultas = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $consultas[] = new Consulta(
                                $row->{self::ID},
                                $row->{self::VENDA},
                                $row->{self::NOME_PACIENTE},
                                $row->{self::ESFERICO_OD},
                                $row->{self::ESFERICO_OE},
                                $row->{self::CILINDRICO_OD},
                                $row->{self::CILINDRICO_OE},
                                $row->{self::EIXO_OD},
                                $row->{self::EIXO_OE},
                                $row->{self::DNP_OD},
                                $row->{self::DNP_OE},        
                                $row->{self::DP},
                                $row->{self::ADICAO},
                                $row->{self::ALTURA},
                                $row->{self::CO},
                                $row->{self::COR},
                                $row->{self::LENTE},
                                $row->{self::OBSERVACAO},
                                $row->{FuncionarioModel::NOME}        
                           );
        }
        return $consultas;
    }
    
    /**
     * Seleciona uma lista de consultas com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de consultas do tipo Consulta.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode (",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $consultas = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $consultas[] = new Consulta(
                                isset($row->{self::ID})?$row->{self::ID}:0,
                                isset($row->{self::VENDA})?$row->{self::VENDA}:0,
                                isset($row->{self::NOME_PACIENTE})?$row->{self::NOME_PACIENTE}:"",
                                isset($row->{self::ESFERICO_OD})?$row->{self::ESFERICO_OD}:0,
                                isset($row->{self::ESFERICO_OE})?$row->{self::ESFERICO_OE}:0,
                                isset($row->{self::CILINDRICO_OD})?$row->{self::CILINDRICO_OD}:0,
                                isset($row->{self::CILINDRICO_OE})?$row->{self::CILINDRICO_OE}:0,
                                isset($row->{self::EIXO_OD})?$row->{self::EIXO_OD}:0,
                                isset($row->{self::EIXO_OE})?$row->{self::EIXO_OE}:0,
                                isset($row->{self::DNP_OD})?$row->{self::DNP_OD}:0,
                                isset($row->{self::DNP_OE})?$row->{self::DNP_OE}:0,
                                isset($row->{self::DP})?$row->{self::DP}:0,
                                isset($row->{self::ADICAO})?$row->{self::ADICAO}:0,
                                isset($row->{self::ALTURA})?$row->{self::ALTURA}:0,
                                isset($row->{self::CO})?$row->{self::CO}:0,
                                isset($row->{self::COR})?$row->{self::COR}:"",
                                isset($row->{self::LENTE})?$row->{self::LENTE}:"",        
                                isset($row->{self::OBSERVACAO})?$row->{self::OBSERVACAO}:"",
                                isset($row->{self::OCULISTA})?$row->{self::OCULISTA}:0        
                           );
        }
        return $consultas;
    }
    
    /**
     * Realiza a remoção de uma consulta.
     * @param int $id_consulta identificador da consulta (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_consulta){
        $condition = self::ID." = $id_consulta";
        return parent::delete(self::TABLE, $condition);
    }
    
}
?>
