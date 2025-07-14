<?php
namespace Sisoptica\Repository;

include_once MODELS.'funcionario.php';

class RepasseModel extends Database {
    
    const TABLE                     = 'ordem_servico_repasse';
    
    const ID                        = 'id';
    
    const DT_CHEGADA                = 'dt_chegada';
    
    const DT_ENVIO_CONSERTO         = 'dt_envio_conserto';
    
    const DT_RECEBIMENTO_CONSERTO   = 'dt_recebimento_conserto';
    
    const DT_ENVIO_CLIENTE          = 'dt_envio_cliente';
    
    const OBSERVACAO                = 'observacao';
    
    const COBRADOR                  = 'id_funcionario_cobrador';
    
    const VENDA                     = 'id_venda';
    
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $repasses = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $repasses[] = new Repasse(
                isset($row->{ self::ID })? $row->{ self::ID } : 0,
                isset($row->{ self::DT_CHEGADA })? ($row->{ self::DT_CHEGADA }) : '',
                isset($row->{ self::DT_ENVIO_CONSERTO })? $row->{ self::DT_ENVIO_CONSERTO } : '',
                isset($row->{ self::DT_RECEBIMENTO_CONSERTO })? $row->{ self::DT_RECEBIMENTO_CONSERTO } : '',
                isset($row->{ self::DT_ENVIO_CLIENTE })? $row->{ self::DT_ENVIO_CLIENTE } : '',
                isset($row->{ self::OBSERVACAO })? $row->{ self::OBSERVACAO } : '',
                isset($row->{ self::COBRADOR })? $row->{ self::COBRADOR } : 0,
                isset($row->{ self::VENDA })? $row->{ self::VENDA } : 0
            );
        }
        return $repasses;
    }
    
    public function superSelect($condition = null, $limit = null){
        $fields = array(self::TABLE.".".self::ID, self::DT_CHEGADA, self::DT_ENVIO_CONSERTO,
                        self::DT_RECEBIMENTO_CONSERTO, self::DT_ENVIO_CLIENTE, 
                        self::TABLE.'.'.self::OBSERVACAO, FuncionarioModel::NOME,
                        self::VENDA);
        
        $fields_joined = implode(",", $fields);
        
        $this_condition = self::COBRADOR." = ".FuncionarioModel::TABLE.".".  FuncionarioModel::ID;
        
        if(is_null($condition)) $condition = $this_condition;
        else $condition .= " AND ".$this_condition;
        
        $tables = implode(",", array(self::TABLE, FuncionarioModel::TABLE));
        
        $res = parent::select($tables, $fields_joined, $condition, $limit);
        $ana = $this->getAnnalisses();
        $repasses = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $repasses[] = new Repasse(
                $row->{ self::ID },
                $row->{ self::DT_CHEGADA },
                $row->{ self::DT_ENVIO_CONSERTO },
                $row->{ self::DT_RECEBIMENTO_CONSERTO },
                $row->{ self::DT_ENVIO_CLIENTE },
                $row->{ self::OBSERVACAO },
                $row->{ FuncionarioModel::NOME },
                $row->{ self::VENDA }
            );
        }
        return $repasses;
    }
    
    public function insert(Repasse $repasse) {
        $fields = implode(',', array(self::DT_CHEGADA, self::DT_ENVIO_CONSERTO, 
            self::DT_RECEBIMENTO_CONSERTO, self::DT_ENVIO_CLIENTE, 
            self::OBSERVACAO, self::COBRADOR, self::VENDA)
        );
        $vars = get_object_vars($repasse);
        unset($vars['id']);        
        return parent::insert( self::TABLE, $fields, Database::turnInValues($vars));
    }
    
    public function delete($id_repasse) {
        $condition = self::ID.' = '.$id_repasse;
        return parent::delete(self::TABLE, $condition);
    }
    
    public function update(Repasse $repasse) {
        $dic = array(
            self::DT_CHEGADA                => $repasse->dtChegada,
            self::DT_ENVIO_CONSERTO         => $repasse->dtEnvioConserto,
            self::DT_RECEBIMENTO_CONSERTO   => $repasse->dtRecebimentoConserto,
            self::DT_ENVIO_CLIENTE          => $repasse->dtEnvioCliente, 
            self::OBSERVACAO                => $repasse->observacao, 
            self::COBRADOR                  => $repasse->cobrador, 
            self::VENDA                     => $repasse->venda
        );
        $condition = self::ID.'='.$repasse->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
}
?>
