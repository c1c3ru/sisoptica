<?php

include_once MODELS.'caixa.php';
include_once ENTITIES.'caixa.php';

class CaixaController {
    
    private $modelCaixa;
    
    public $statusCaixa = array(
        CaixaModel::STATUS_ABERTO => 'Aberto',
        CaixaModel::STATUS_FECHADO => 'Fechado'
    );
    
    public function __construct() {
        $this->modelCaixa = new CaixaModel();
    }
    
    public function addCaixa(Caixa $caixa = null){
        if($caixa == null){
            $caixa = new Caixa();
            $this->linkCaixa($caixa);
        }
        $config = Config::getInstance();
        if(empty($caixa->data)){
            $config->failInFunction('É necessário informar a data do caixa');
            $config->redirect('index.php?op=cad-caix');
        }
        if($this->existsAberto()){
            $config->failInFunction('Ainda existe um caixa aberto');
            $config->redirect('index.php?op=cad-caix');
        }
        $hasId  = $config->filter('for-update-id');
        $res    = false;
        $resDespCaixa = true;
        $this->modelCaixa->begin();
        if(empty($hasId)){
            $res = $this->modelCaixa->insert($caixa);
            if($res){
                $condition = CaixaModel::DATA.' = (SELECT MAX('.
                             CaixaModel::DATA.') FROM '.CaixaModel::TABLE.
                             ' WHERE '.CaixaModel::DATA.' < \''.$caixa->data.'\''.
                             ' AND '.CaixaModel::LOJA.' = '.$caixa->loja.' ) '.
                             ' AND '.CaixaModel::LOJA.' = '.$caixa->loja;
                $caixas    = $this->modelCaixa->select('*', $condition);
                if(!empty($caixas) && $caixas[0]->saldo > 0){
                    include_once CONTROLLERS.'despesa.php';
                    include_once CONTROLLERS.'naturezaDespesa.php';
                    $despesaController  = new DespesaController();
                    $despesa            = new Despesa(
                        0, $caixas[0]->saldo,
                        NaturezaDespesaController::ID_DESPESA_CAIXA, 
                        $caixas[0]->id, '', $caixa->id
                    ); 
                    $resDespCaixa = $despesaController->addDespesa($despesa, true);
                }
            }
        } else {
            $caixa->id = $hasId;
            $res = $this->modelCaixa->update($caixa); 
        }
        if($res && $resDespCaixa){
            $this->modelCaixa->commit();
            $config->successInFunction();
        } else {
            $this->modelCaixa->rollBack();
            if(!$resDespCaixa){
                $config->failInFunction('Falha ao registrar saldo do último caixa');
            } else {
                $config->failInFunction($this->modelCaixa->handleError());
            }
        }
        $config->redirect('index.php?op=cad-caix');
    }
    
    private function linkCaixa(Caixa &$caixa){
        $config = Config::getInstance();
        $caixa->data = $config->filter('data');
        $caixa->loja = $_SESSION[SESSION_LOJA_FUNC];
    }
    
    public function removeCaixa(){
        $config      = Config::getInstance();
        $id_caixa    = $config->filter('caixa');
        if(empty($id_caixa)){
            $config->failInFunction('Caixa não informada');
        } else {
            $caixa = $this->getCaixa($id_caixa);
            if( $caixa->loja == $_SESSION[SESSION_LOJA_FUNC] && 
                $this->modelCaixa->delete($id_caixa))
            {
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelCaixa->handleError());
            }
        }
        $config->redirect('index.php?op=cad-caix');
    }
    
    public function getCaixa($id_caixa){
        $condition  = CaixaModel::ID.'='.$id_caixa;
        $res        = $this->modelCaixa->select('*', $condition);
        if(empty($res)) return new Caixa();
        return $res[0];
    }
    
    public function getCaixaHoje(){
        return $this->getCaixaByData(date("Y-m-d"));
    }
    
    public function getCaixaByData($data, $loja = null){
        $condition  = CaixaModel::DATA."= '$data' ";
        $condition .= ' AND '.CaixaModel::LOJA.' = '. ( $loja == null ? $_SESSION[SESSION_LOJA_FUNC] : $loja );
        $res        = $this->modelCaixa->select('*', $condition);
        if(empty($res)) return new Caixa();
        return $res[0];
    }
    
    public function getCaixaAberto($logged_loja = TRUE){
        $condition  = CaixaModel::STATUS.' = '.CaixaModel::STATUS_ABERTO;
        if($logged_loja === TRUE) {
            $condition .= ' AND '.CaixaModel::LOJA.' = '.$_SESSION[SESSION_LOJA_FUNC];
        } else if($logged_loja) {
            $condition .= ' AND '.CaixaModel::LOJA.' = '.$logged_loja;
        }
        $res        = $this->modelCaixa->select('*', $condition);
        if(empty($res)) return new Caixa();
        return $res[0];
    }
    
    public function existsAberto(){
        $aberto = $this->getCaixaAberto();
        return $aberto->id != 0;
    }
    
    public function getAllCaixas($logged_loja = true){
        $condition = '1=1';
        if($logged_loja){
            $condition = CaixaModel::LOJA.' = '.$_SESSION[SESSION_LOJA_FUNC];
        }
        $condition .= ' ORDER BY '.CaixaModel::STATUS.', '.CaixaModel::DATA.' DESC';
        return $this->modelCaixa->select('*', $condition);
    }
    
    public function fecharCaixa(){
        $config = Config::getInstance();
        
        $loja = true;
        include_once CONTROLLERS.'funcionario.php';
        if(( $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ||
           $_SESSION[SESSION_CARGO_FUNC] == CargoModel::COD_DIRETOR) &&
           ($loja_id = $config->filter('loja')) != null){
            $loja = $loja_id;
        }
        
        $caixa  = $this->getCaixaAberto($loja);
        if(empty($caixa->id)){
            $config->failInFunction('Nenhuma caixa aberto');
            $config->redirect('index.php?op=cad-caix');
        }
        
        include_once CONTROLLERS.'despesa.php';
        include_once CONTROLLERS.'naturezaDespesa.php';
        $naturezaController = new NaturezaDespesaController();
        $despesaController  = new DespesaController();
        $despesas           = $despesaController->getDespesasByCaixa($caixa->id);
        $naturezas          = array();
        $sum                = 0; 
        
        foreach($despesas as $despesa){ 
            if(!array_key_exists($despesa->natureza, $naturezas)){
                $naturezas[$despesa->natureza] = $naturezaController->getNatureza($despesa->natureza);
            }
            $natureza = $naturezas[$despesa->natureza];
            if($natureza->entrada) {
                $sum += $despesa->valor;
            } else {
                $sum -= $despesa->valor;
            }
        }
        
        include_once CONTROLLERS.'prestacaoConta.php';
        $prestacaoController    = new PrestacaoContaController();
        $itens                  = $prestacaoController->getItensDinheiroByData(
            $caixa->data, $caixa->loja
        );
        foreach($itens as $item){ $sum += $item->valor; }
        
        $caixa->status  = CaixaModel::STATUS_FECHADO;
        $caixa->saldo   = $sum;
        
        if($this->modelCaixa->update($caixa)){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelCaixa->handleError());
        }
        $config->redirect('index.php?op=cad-caix');
    }
    
    public function getSaldoOfCaixa($caixa) {
        
        include_once CONTROLLERS.'despesa.php';
        $despesaController  = new DespesaController();
        $despesas           = $despesaController->getDespesasByCaixa($caixa->id);
        
        include_once CONTROLLERS.'naturezaDespesa.php';
        $naturezaController     = new NaturezaDespesaController();
        $naturezas = array();

        $sumEntrada = 0;
        $sumSaida   = 0;

        foreach ($despesas as $despesa) {
            //Obtendo Natureza
            $natureza = new NaturezaDespesa();
            if(!array_key_exists($despesa->natureza, $naturezas)){
                $natureza = $naturezaController->getNatureza($despesa->natureza);
                $naturezas[$natureza->id] = $natureza;
            } else {
                $natureza = $naturezas[$despesa->natureza];
            }

            if($natureza->entrada){
                $sumEntrada += $despesa->valor;
            } else {
                $sumSaida   += $despesa->valor;
            }

        }

        include_once CONTROLLERS.'prestacaoConta.php';
        $prestacaoController    = new PrestacaoContaController();
        $itens                  = $prestacaoController->getItensDinheiroByData(
            $caixa->data, $caixa->loja
        );        
        foreach($itens as $item){ 
            $sumEntrada += $item->valor;
        }
        
        return $sumEntrada - $sumSaida;
    }

    
}
?>
