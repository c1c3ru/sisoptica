<?php
namespace Sisoptica\Controller;

include_once MODELS.'combustivel.php';
include_once ENTITIES.'combustivel.php';

class CombustivelController {
    
    private $modelCombustivel;
    
    public function __construct() {
        $this->modelCombustivel = new CombustivelModel();
    }
    
    public function addCombustivel(Combustivel $combustivel = null){
        if($combustivel == null){
            $combustivel = new Combustivel();
            $this->linkCombustivel($combustivel);
        }
        $vars = get_object_vars($combustivel);
        unset($vars['id']);
        foreach ($vars as $v) { 
            if(empty($v) && $v != '0') return false; 
        }
        return $this->modelCombustivel->insert($combustivel);
    }
    
    public function linkCombustivel(Combustivel &$combustivel){
        $config = Config::getInstance();
        $combustivel->litros    = (float) str_replace(',', '.', $config->filter('litros'));
        $combustivel->preco     = (float) str_replace(',', '.', $config->filter('preco'));
        $combustivel->kmInicial = (float) str_replace(',', '.', $config->filter('km-inicial'));
        $combustivel->kmFinal   = (float) str_replace(',', '.', $config->filter('km-final'));
        $combustivel->despesa   = $config->filter('despesa');
    }
    
    public function removeCombustivel($combustivel){
        return $this->modelCombustivel->delete($combustivel);
    }
    
    public function removeCombustivelFromDespesa($despesa){
        $combustivel = $this->getCombustivelByDespesa($despesa);
        return $this->modelCombustivel->delete($combustivel->id);
    }
    
    public function getCombustivel($id_combustivel){
        $condition  = CombustivelModel::ID.'='.$id_combustivel;
        $res        = $this->modelCombustivel->select('*', $condition);
        if(empty($res)) return new Combustivel();
        return $res[0];
    }
    
    public function getCombustivelByDespesa($despesa){
        $condition  = CombustivelModel::DESPESA.'='.$despesa;
        $res        = $this->modelCombustivel->select('*', $condition);
        if(empty($res)) return new Combustivel();
        return $res[0];
    }
    
    public function getAllCombustivels(){
        return $this->modelCombustivel->select();
    }
    
    public function getCombustiveisByVeiculo($veiculo_id, $data_ini, $data_fim){
        $cond_natu = "SELECT N.".NaturezaDespesaModel::ID." FROM ".NaturezaDespesaModel::TABLE." N";
        $cond_natu.= " WHERE N.".NaturezaDespesaModel::NOME." LIKE '%COMBUSTIVEL%' ";
        $cond_desp = "SELECT D.".DespesaModel::ID." FROM ".DespesaModel::TABLE." D,".CaixaModel::TABLE." C";
        $cond_desp.= " WHERE D.".DespesaModel::ENTIDADE." = ".$veiculo_id;
        $cond_desp.= " AND D.".DespesaModel::CAIXA." = C.".CaixaModel::ID;
        $cond_desp.= " AND C.".CaixaModel::DATA." BETWEEN '$data_ini' AND '$data_fim' ";
        $cond_desp.= " AND D.".DespesaModel::NATUREZA." IN ($cond_natu) ";
        $cond_desp.= " ORDER BY ".DespesaModel::ENTIDADE;
        $condition = CombustivelModel::DESPESA." IN ($cond_desp )";
        return $this->modelCombustivel->select("*", $condition);
    }
    
}
?>
