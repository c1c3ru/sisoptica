<?php

include_once MODELS.'natureza-despesa.php';
include_once ENTITIES.'natureza-despesa.php';

class NaturezaDespesaController {

    private $modelNatureza;

    public $tiposNatureza = array(
        self::SEM_TIPO          => 'SEM_TIPO',
        self::TIPO_FUNCIONARIO  => 'FUNCIONARIO',
        self::TIPO_VEICULO      => 'VEICULO',
        self::TIPO_LOJA         => 'LOJA'
    );

    const SEM_TIPO          = 0x000;
    const TIPO_FUNCIONARIO  = 0x001;
    const TIPO_VEICULO      = 0x002;
    const TIPO_LOJA         = 0x003;

    const ID_DESPESA_CAIXA      = 23;
    const NOME_APLICACAO_ESPECIE  = 'APLICACAO EM ESPECIE';

    public function __construct() {
        $this->modelNatureza = new NaturezaDespesaModel();
    }

    public function addNatureza(NaturezaDespesa $natureza = null){
        if($natureza == null){
            $natureza = new NaturezaDespesa();
            $this->linkNatureza($natureza);
        }
        $config = Config::getInstance();
        $hasId  = $config->filter('for-update-id');
        $res    = false;

        if(empty($hasId)){
            $res = $this->modelNatureza->insert($natureza);
        } else {
            $natureza->id = $hasId;
            $res = $this->modelNatureza->update($natureza);
        }

        if ($res) {
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelNatureza->handleError());
        }

        $config->redirect('index.php?op=cad-natu');
    }

    private function linkNatureza(NaturezaDespesa &$natureza){
        $config = Config::getInstance();
        $natureza->nome = mb_strtoupper($config->filter('nome'), 'UTF-8');
        $natureza->tipo = $config->filter('tipo');
        if(!array_key_exists($natureza->tipo, $this->tiposNatureza)){
            $natureza->tipo = self::SEM_TIPO;
        }
        $natureza->entrada = $config->filter('movimentacao') == 'e';
    }

    public function removeNatureza(){
        $config         = Config::getInstance();
        $id_natureza    = $config->filter('natureza');
        if (empty($id_natureza) || $id_natureza == self::ID_DESPESA_CAIXA){
            $config->failInFunction('Natureza de Despesa não informada');
        } else {
            if ($this->modelNatureza->delete($id_natureza)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelNatureza->handleError());
            }
        }
        $config->redirect('index.php?op=cad-natu');
    }

    public function getNatureza($id_natureza){
        $condition  = NaturezaDespesaModel::ID.'='.$id_natureza;
        $res        = $this->modelNatureza->select('*', $condition);
        if(empty($res)) return new NaturezaDespesa();
        return $res[0];
    }

    public function getAllNaturezas($ids = null){
        $condition = NaturezaDespesaModel::ID .' <> '.self::ID_DESPESA_CAIXA;
        if($ids != null){
            if(is_array($ids)) $ids = implode(',',$ids);
            $condition .= ' AND '.NaturezaDespesaModel::ID.' IN (' . $ids . ')';
        }
        $condition .= " ORDER BY ".NaturezaDespesaModel::NOME;
        return $this->modelNatureza->select('*', $condition);
    }

    public function getAllNaturezasSaidas($exceptNames=array()){
        return $this->getAllNaturezasByTypeAndExcepts(false, $exceptNames);
    }

    public function getAllNaturezasEntradas($exceptNames=array()){
        return $this->getAllNaturezasByTypeAndExcepts(true, $exceptNames);
    }

    private function getAllNaturezasByTypeAndExcepts($entrada, $exceptNames) {
        $condition  = NaturezaDespesaModel::ENTRADA.' = '.($entrada ? 'TRUE' : 'FALSE');

        $exceptNamesArr = array();
        foreach($exceptNames as $exceptName) {
            $exceptNamesArr[] = NaturezaDespesaModel::NOME . " NOT LIKE '$exceptName' ";
        }
        if (!empty($exceptNamesArr)) $condition .= " AND " . implode(" AND ", $exceptNamesArr);

        $condition .= " ORDER BY ".NaturezaDespesaModel::NOME;
        return $this->modelNatureza->select('*', $condition);
    }

    public function getNaturezaByNome($nome) {
        $condition  = NaturezaDespesaModel::NOME." = '$nome'";
        $res        = $this->modelNatureza->select('*', $condition);
        if(empty($res)) return new NaturezaDespesa();
        return $res[0];
    }

    public function userCanRemoveOrEdit($natureza) {
        $natureza = is_object($natureza) ? $natureza : $this->getNatureza($natureza);

        if ($natureza->id == NaturezaDespesaController::ID_DESPESA_CAIXA) {
          return false;
        } else {
          $isAplicacaoEspecie = strcasecmp(
            $natureza->nome, NaturezaDespesaController::NOME_APLICACAO_ESPECIE) == 0;
          if ($isAplicacaoEspecie) {
            return $_SESSION[SESSION_CARGO_FUNC] == CargoModel::COD_DIRETOR ||
                   $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR;
          }
        }

        return true;
    }

}
?>
