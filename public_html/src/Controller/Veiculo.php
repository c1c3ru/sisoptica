<?php
namespace Sisoptica\Controller;

include_once MODELS.'veiculo.php';
include_once ENTITIES.'veiculo.php';

class VeiculoController {
    
    private $modelVeiculo;
    
    public function __construct() {
        $this->modelVeiculo = new VeiculoModel();
    }
    
    
    public function addVeiculo(Veiculo $veiculo = null){
        if($veiculo == null){
            $veiculo = new Veiculo();
            $this->linkVeiculo($veiculo);
        }
        $config = Config::getInstance();
        $hasId  = $config->filter('for-update-id');
        $res    = false;  
        if(empty($hasId)){
            $res = $this->modelVeiculo->insert($veiculo);
        } else {
            $veiculo->id = $hasId;
            $res = $this->modelVeiculo->update($veiculo);
        }
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelVeiculo->handleError());
        }
        $config->redirect('index.php?op=cad-veic');
    }
    
    private function linkVeiculo(Veiculo &$veiculo){
        $config = Config::getInstance();
        $veiculo->nome  = mb_strtoupper($config->filter('nome'), 'UTF-8');
        $veiculo->placa = mb_strtoupper($config->filter('placa'), 'UTF-8');
        $veiculo->motorista = $config->filter('motorista');
        $veiculo->loja      = $config->filter('loja');
		$veiculo->inativoParaCaixa = !is_null($config->filter("inativo-para-caixa"));
    }
    
    public function removeVeiculo(){
        $config     = Config::getInstance();
        $id_veiculo = $config->filter('veiculo');
        if(empty($id_veiculo)){
            $config->failInFunction('Veículo não informado');
        } else {
            if($this->modelVeiculo->delete($id_veiculo)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelVeiculo->handleError());
            }
        }
        $config->redirect('index.php?op=cad-veic');
    }
    
    public function getAllVeiculos($foreign_values = false, $by_loja = false){
        $condition = !$by_loja ? null : VeiculoModel::LOJA." = ".$by_loja ;
        if($foreign_values){
            return $this->modelVeiculo->superSelect($condition);
        }
        return $this->modelVeiculo->select('*', $condition);
    }
	
	public function getAllVeiculosNaoInativosParaCaixa($foreign_values = false, $by_loja = false){
        $condition = VeiculoModel::INATIVO_PARA_CAIXA. " = FALSE" ;
		if ($by_loja) $condition .= " AND " . VeiculoModel::LOJA." = ".$by_loja ;
		if ($foreign_values) {
            return $this->modelVeiculo->superSelect($condition);
        }
        return $this->modelVeiculo->select('*', $condition);
    }
    
    public function getVeiculo($veiculo_id){
        $condition  = VeiculoModel::ID.'='.$veiculo_id;
        $res        = $this->modelVeiculo->select('*', $condition);
        if(empty($res)){
            return new Veiculo();
        }
        return $res[0];
    }
    
    public function getVeiculosByLoja($loja_id){
        $condition = VeiculoModel::LOJA.'='.$loja_id;
        return $this->modelVeiculo->select('*', $condition);
    }
    
}
?>
