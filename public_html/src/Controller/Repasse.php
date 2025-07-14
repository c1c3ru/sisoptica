<?php
namespace Sisoptica\Controller;

include_once ENTITIES.'repasse.php';
include_once MODELS.'repasse.php';

class RepasseController {
    
    private $modelRepasse ;
    private static $fieldDatas = array(
        'dt-chegada'                => RepasseModel::DT_CHEGADA,
        'dt-envio-conserto'         => RepasseModel::DT_ENVIO_CONSERTO,
        'dt-recebimento-conserto'   => RepasseModel::DT_RECEBIMENTO_CONSERTO,
        'dt-envio-cliente'          => RepasseModel::DT_ENVIO_CLIENTE,
    );  
    
    public function __construct() {
        $this->modelRepasse = new RepasseModel();
    }
    
    public function addRepasse(Repasse $repasse = null) {
        if($repasse == null){
            $repasse = new Repasse();
            $this->linkRepasse($repasse);
        }
        $config = Config::getInstance();
        if(empty($repasse->cobrador) || empty($repasse->venda) || empty($repasse->dtChegada)){
            $config->failInFunction("Cobrador, Venda e Data de Chegada são obrigatórios");
            $config->redirect('index.php?op=cad-repasse');
        }
        $hasId  = $config->filter('for-update-id');
        $res    = false;
        if(empty($hasId)){
            $res = $this->modelRepasse->insert($repasse);
        } else {
            $repasse->id = $hasId;
            $res = $this->modelRepasse->update($repasse);
        }
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelRepasse->handleError());
        }
        $config->redirect('index.php?op=cad-repasse');
    }
    
    public function linkRepasse(Repasse &$repasse){
        $config = Config::getInstance();
        $repasse->dtChegada                 = $config->filter('dt-chegada');
        $repasse->dtEnvioConserto           = $config->filter('dt-envio-conserto');
        if(empty($repasse->dtEnvioConserto)){
            $repasse->dtEnvioConserto       = null;
        }
        $repasse->dtRecebimentoConserto     = $config->filter('dt-recebimento-conserto');
        if(empty($repasse->dtRecebimentoConserto)){
            $repasse->dtRecebimentoConserto = null;
        }
        $repasse->dtEnvioCliente            = $config->filter('dt-envio-cliente');
        if(empty($repasse->dtEnvioCliente)){
            $repasse->dtEnvioCliente        = null;
        }
        $repasse->observacao                = mb_strtoupper($config->filter('observacao'), 'UTF-8');
        $repasse->cobrador                  = $config->filter('cobrador');
        $repasse->venda                     = $config->filter('venda');
    }
    
    public function removeRepasse(){
        $config = Config::getInstance();
        $id_repasse = $config->filter("repasse");
        if(isset($id_repasse)){
            if($this->modelRepasse->delete($id_repasse)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelLaboratorio->handleError());
            }    
        } else {
            $config->failInFunction("Repasse não informado");
        }
        $config->redirect("?op=cad-repasse");
    }
    
    public function getAllRepasses($foreign_values) {
        if($foreign_values) return $this->modelRepasse->superSelect();
        return $this->modelRepasse->select();
    }
    
    public function getRepasse($id_repasse){
        $condition  = RepasseModel::ID.' = '.$id_repasse;
        $res        = $this->modelRepasse->select('*', $condition);
        if(empty($res)) return new Repasse();
        return $res[0];
    }
    
    public function getRepassesRange($data_ini, $data_fim, $datas, $cobrador = null, $loja = null){

        $condition  = '1=1';
        if($cobrador != null) {
            $condition  .= ' AND ' . RepasseModel::COBRADOR.' = '.$cobrador;
        }

        $fields_dt_between = array();
        foreach($datas as $data){
            if(array_key_exists($data, self::$fieldDatas)) {
                $fields_dt_between[] = self::$fieldDatas[$data].' BETWEEN \''.$data_ini.'\' AND \''.$data_fim.'\'';
            }
        }

        if($loja != null) {
            $sub_select = ' SELECT '.VendaModel::ID.' FROM '.VendaModel::TABLE.' WHERE '.VendaModel::LOJA.'='.$loja;
            $condition .= ' AND '.RepasseModel::VENDA. ' IN ( '.$sub_select.' )';
        }

        if (!empty($fields_dt_between)) {
            $condition .= ' AND (' . implode($fields_dt_between, ' AND ') . ' )';
        }

        return $this->modelRepasse->select('*', $condition);
    }
    
}
?>
