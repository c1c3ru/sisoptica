<?php

include_once ENTITIES . 'equipe.php';
include_once MODELS . 'equipe.php';
include_once ENTITIES . 'equipe-funcionario.php';
include_once MODELS . 'equipe-funcionario.php';
include_once CONTROLLERS . 'funcionario.php';

class EquipeController {
    
    private $modelEquipe;
    private $modelEquipeFuncionario;
    
    public function __construct() {
        $this->modelEquipe = new EquipeModel();
        $this->modelEquipeFuncionario = new EquipeFuncionarioModel();
    }
    
    public function addEquipe(Equipe $equipe = null){
        if($equipe == null){
            $equipe =  new Equipe();
            $this->linkEquipe($equipe);
        }
        $config = Config::getInstance();
        $hasId  = $config->filter('for-update-id');
        $res    = false;
        $this->modelEquipe->begin();
        if(empty($hasId)) {
            $res = $this->modelEquipe->insert($equipe);
        } else {
            $equipe->id = $hasId;
            $res = $this->modelEquipe->update($equipe);
        }
        if($res) {
            if(!$this->syncIntegrantes($equipe)){
                $this->modelEquipe->rollBack();
                $config->failInFunction('Falha ao ajustar funcionários da Equipe');
            } else {
                $this->modelEquipe->commit();
                $config->successInFunction();
            }
        } else {
            $this->modelEquipe->rollBack();
            $config->failInFunction($this->modelEquipe->handleError());
        }
        $config->redirect('index.php?op=cad-equipe');
    }
    
    private function linkEquipe(Equipe &$equipe){
        $config = Config::getInstance();
        $equipe->nome   = mb_strtoupper($config->filter('nome'), 'UTF-8');
        $equipe->loja   = $config->filter('loja');
        $equipe->lider  = $config->filter('lider');
        $integrantes    = $config->filter('integrantes');
        $equipe->integrantes = array();
        foreach($integrantes as $i){
            $equipe->integrantes[] = new EquipeFuncionario(null, $i);
        }
    }
    
    public function removeEquipe($id_equipe = null) {
        $config = Config::getInstance();
        if(is_null($id_equipe)){
            $id_equipe = $config->filter('equipe');
        }
        if($this->modelEquipe->delete(new Equipe($id_equipe))) {
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelEquipe->handleError());
        }
        $config->redirect('index.php?op=cad-equipe');
    }
    
    public function syncIntegrantes(Equipe $equipe){

        $funcionarioController = new FuncionarioController();

        $notIsVendedor = function($funcionarioId) use ($funcionarioController) {
            return $funcionarioController->getFuncionario($funcionarioId)->cargo != CargoModel::COD_VENDEDOR;
        };

        $that = $this;
        $hasMoreThanOneEquipe = function($funcionarioId) use ($that) {
            return count($that->equipesOf($funcionarioId)) > 1;
        };

        $currentFuncIds = array_map(function($fi) {
            return $fi->funcionario;
        }, $this->getIntegrantes($equipe));

        foreach ($equipe->integrantes as $k => &$integrante) {
            $funcId = $integrante->funcionario;
            $key = array_search($funcId, $currentFuncIds);
            if($key !== FALSE) {
                unset($currentFuncIds[$key]);
                unset($equipe->integrantes[$k]);
            } else if ( $notIsVendedor($funcId) && $hasMoreThanOneEquipe($funcId) ) {
                unset($equipe->integrantes[$k]);
            } else {
                $integrante->equipe = $equipe->id;
                $integrante->dataEntrada = date('Y-m-d');
            }
        }

        if(!empty($currentFuncIds)) {
            // removendo os atuais integrantes das equipes, caso existam
            $condition  = EquipeFuncionarioModel::FUNCIONARIO.' IN ('.implode(',', $currentFuncIds).')';
            $condition .= ' AND ' . EquipeFuncionarioModel::EQUIPE.'='.$equipe->id; 
            if(!$this->modelEquipeFuncionario->delete($condition)){
                return false;
            }
        }

        return $this->modelEquipeFuncionario->inserts($equipe->integrantes);
    }
    
    public function getAllEquipes($foreign_values = false) {
        if($foreign_values) return $this->modelEquipe->superSelect();
        return $this->modelEquipe->select();
    }
    
    public function getEquipesByLoja($loja_id, $foreign_values = false) {
        $condition = EquipeModel::TABLE.'.'.EquipeModel::LOJA.' = '.$loja_id;
        if($foreign_values) return $this->modelEquipe->superSelect($condition);
        return $this->modelEquipe->select('*', $condition);
    }
    
    public function getEquipe($id_equipe, $foreign_values = false) {
        $condition  = EquipeModel::TABLE . '.' . EquipeModel::ID . '=' . $id_equipe;
        if($foreign_values) $res = $this->modelEquipe->superSelect($condition);
        else $res =  $this->modelEquipe->select('*', $condition);
        if(empty($res)) return new Equipe();
        return $res[0];
    }
 
    public function getIntegrantes(Equipe $equipe, $foreign_values = false){
        $condition = EquipeFuncionarioModel::EQUIPE . '=' . $equipe->id;
        if($foreign_values) return $this->modelEquipeFuncionario->superSelect($condition);
        return $this->modelEquipeFuncionario->select("*", $condition);
    }

    public function getIntegrantesByCargo(Equipe $equipe, $cargo, $foreign_values = false){
        $condition  = EquipeFuncionarioModel::EQUIPE . '=' . $equipe->id;
        $condition .= " AND ". FuncionarioModel::TABLE . "." . FuncionarioModel::CARGO . " = " . $cargo;
        if($foreign_values) return $this->modelEquipeFuncionario->superSelect($condition);
        return $this->modelEquipeFuncionario->select("*", $condition);
    }
    
    public function getFuncionariosIntegrantes($id_equipe){
        $condition      = EquipeFuncionarioModel::EQUIPE . '=' . $id_equipe;
        $integrantes    = $this->modelEquipeFuncionario->select("*", $condition);
        $ids            = array();
        foreach ($integrantes as $i) {
            $ids[] = $i->funcionario;
        }
        $func_model     = new FuncionarioModel();
        $condition      = FuncionarioModel::ID . 'IN ( ' . implode(',', $ids) . ' )';
        return $func_model->select('*', $condition);
    }
    
    public function getEquipeLiderMap(){
        $equipes    = $this->getAllEquipes();
        $map        = array();
        foreach($equipes as $equipe){
            $map[$equipe->lider][] = $equipe->id;
        }
        return $map;
    }

    public function equipesOf($funcionarioId) {
        $condition = EquipeFuncionarioModel::FUNCIONARIO." = $funcionarioId ";
        return $this->modelEquipeFuncionario->select('*', $condition);
    }
    
}

?>
