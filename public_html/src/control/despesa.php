<?php

include_once MODELS.'despesa.php';
include_once ENTITIES.'despesa.php';

class DespesaController {

    private $modelDespesa;

    public function __construct() {
        $this->modelDespesa = new DespesaModel();
    }

    public function addDespesa(Despesa $despesa = null, $return = false, $ajaxMode = false){
        if($despesa == null){
            $despesa = new Despesa();
            $this->linkDespesa($despesa);
        }

        $funcRes = $ajaxMode ? 'throwAjaxError': 'failInFunction' ;

        $config = Config::getInstance();
        if(empty($despesa->natureza) || empty($despesa->entidade) ||
           empty($despesa->caixa) || empty($despesa->valor)) {
            $config->$funcRes('Os campos Valor, Natureza, Entidade são necessário e é preciso ter um caixa aberto');
            $config->redirect('index.php?op=cad-caix');
        }

        include_once CONTROLLERS.'naturezaDespesa.php';
        $naturezaController = new NaturezaDespesaController();
        $natureza = $naturezaController->getNatureza($despesa->natureza);
        $nome_natureza = strtoupper($natureza->nome);
        $isCombustivel = false;
        if( strpos($nome_natureza, 'COMBUSTÍVEL') !== FALSE ||
            strpos($nome_natureza, 'COMBUSTIVEL') !== FALSE ) {
            $isCombustivel = true;
        }

        if ($natureza->tipo != NaturezaDespesaController::SEM_TIPO) {
    			$entidade = $this->getEntidade($natureza, $despesa->entidade);
    			if ($entidade == null || $entidade->inativoParaCaixa) {
    				$config->$funcRes('Entidade Inválida');
    				$config->redirect('index.php?op=cad-caix');
    			}
    		}

        $hasId  = $config->filter('for-update-id');
        $res    = false;
        $resCombustivel = true;
        if($isCombustivel){
            $this->modelDespesa->begin();
        }
        if(empty($hasId)){
            $res = $this->modelDespesa->insert($despesa);
            if($isCombustivel){
                include_once CONTROLLERS.'combustivel.php';
                $combustivelController  = new CombustivelController();
                $_REQUEST['despesa']    = $despesa->id;
                $resCombustivel         = $combustivelController->addCombustivel();
            }
        } else {
            include_once CONTROLLERS.'naturezaDespesa.php';
            $despesa_cmp = $this->getDespesa($hasId);
            $despesa->id = $hasId;
            if($naturezaController->userCanRemoveOrEdit($natureza)){
                $res = $this->modelDespesa->update($despesa);
                if($isCombustivel){
                    include_once CONTROLLERS.'combustivel.php';
                    $combustivelController  = new CombustivelController();
                    $_REQUEST['despesa']    = $despesa->id;
                    $resCombustivel         = (
                        $combustivelController->removeCombustivelFromDespesa($despesa->id) &&
                        $combustivelController->addCombustivel()
                    );
                }
            } else {
                $config->$funcRes('Essa despesa não pode ser atualizada');
            }
        }
        if($return){
            return $res;
        }
        if($res && $resCombustivel){
            if($isCombustivel){ $this->modelDespesa->commit(); }
            if($ajaxMode) $config->throwAjaxSuccess(null);
            $config->successInFunction();
        } else {
            if($isCombustivel){ $this->modelDespesa->rollBack(); }
            if($isCombustivel && !$resCombustivel){
                $config->$funcRes('Falha ao persistir dados do combustível');
            } else {
                $config->$funcRes($this->modelDespesa->handleError());
            }
        }
        $config->redirect('index.php?op=cad-caix');
    }

    private function linkDespesa(Despesa &$despesa){
        $config = Config::getInstance();
        $despesa->valor         = str_replace(',', '.', $config->filter('valor'));
        $despesa->natureza      = $config->filter('natureza');
        $despesa->entidade      = $config->filter('entidade');
        $despesa->observacao    = mb_strtoupper($config->filter('observacao'),'UTF-8');
        $caixa                  = $config->filter('caixa');
        if(empty($caixa)) {
            include_once CONTROLLERS.'caixa.php';
            $caixaController        = new CaixaController();
            $caixa_hoje             = $caixaController->getCaixaAberto();
            $despesa->caixa         = $caixa_hoje->id;
        } else {
            $despesa->caixa = $caixa;
        }
    }

    public function removeDespesa($ajaxMode = false){
        $config         = Config::getInstance();
        $id_despesa     = $config->filter('despesa');
        $funcRes        = $ajaxMode ? 'throwAjaxError' : 'fainInFunction' ;
        if(empty($id_despesa)){
            $config->$funcRes('Despesa não informada');
        } else {
            include_once CONTROLLERS.'naturezaDespesa.php';
            $naturezaController = new NaturezaDespesaController();
            $despesa = $this->getDespesa($id_despesa);
            if($naturezaController->userCanRemoveOrEdit($despesa->natureza)) {
                if($this->modelDespesa->delete($id_despesa)){
                    if($ajaxMode){
                        $config->throwAjaxSuccess(null);
                    }
                    $config->successInFunction();
                } else {
                    $config->$funcRes($this->modelDespesa->handleError());
                }
            } else {
                $config->$funcRes('Essa despesa não pode ser removida');
            }
        }
        $config->redirect('index.php?op=cad-caix');
    }

    public function getDespesa($id_despesa){
        $condition  = DespesaModel::ID.'='.$id_despesa;
        $res        = $this->modelDespesa->select('*', $condition);
        if(empty($res)) return new Despesa();
        return $res[0];
    }

    public function getAllDespesas(){
        return $this->modelDespesa->select();
    }

    public function getDespesasByCaixa($caixa){
        $condition = DespesaModel::CAIXA.' = '.$caixa;
        return $this->modelDespesa->select('*', $condition);
    }

    public function getDespesaByNaturezaInLoja(NaturezaDespesa $natureza, $loja, $data_min, $data_max, $gropued = false) {
        $condition  = DespesaModel::NATUREZA.' = '.$natureza->id;
        $joins      = array();
        switch ($natureza->tipo) {
            case NaturezaDespesaController::TIPO_FUNCIONARIO:
                $joins[] = DespesaModel::leftJoin('FuncionarioModel', DespesaModel::ENTIDADE);
                $condition .= " AND ".FuncionarioModel::LOJA." = ".$loja;
                break;
            case NaturezaDespesaController::TIPO_LOJA:
                $condition .= " AND ".DespesaModel::ENTIDADE." = ".$loja;
                break;
            case NaturezaDespesaController::TIPO_VEICULO:
                $joins[] = DespesaModel::leftJoin('VeiculoModel', DespesaModel::ENTIDADE);
                $condition .= " AND ".VeiculoModel::LOJA." = ".$loja;
                break;
            case NaturezaDespesaController::SEM_TIPO:
                $condition .= " AND ".CaixaModel::LOJA." = ".$loja;
                break;
        }

        $joins[] = DespesaModel::innerJoin('CaixaModel', DespesaModel::CAIXA);
        $condition .= " AND ".CaixaModel::DATA." BETWEEN '" . $data_min . "' AND '" . $data_max . "'";
        if($gropued){
            $fields = array(DespesaModel::ENTIDADE, "SUM(".DespesaModel::VALOR.") AS ".DespesaModel::VALOR);
            $condition .= " GROUP BY ".DespesaModel::ENTIDADE;
        } else {
            $fields = array(
                DespesaModel::TABLE.'.'.DespesaModel::ID,
                DespesaModel::TABLE.'.'.DespesaModel::CAIXA,
                DespesaModel::TABLE.'.'.DespesaModel::ENTIDADE,
                DespesaModel::TABLE.'.'.DespesaModel::NATUREZA,
                DespesaModel::TABLE.'.'.DespesaModel::VALOR,
                DespesaModel::TABLE.'.'.DespesaModel::OBSERVACAO,
            );
        }
        $condition .= " ORDER BY ".DespesaModel::ENTIDADE;
        return $this->modelDespesa->select($fields, $condition, $joins);
    }

	public function getEntidade(NaturezaDespesa $natureza, $id) {
		switch ($natureza->tipo) {
            case NaturezaDespesaController::TIPO_FUNCIONARIO:
				include_once CONTROLLERS.'funcionario.php';
				$control = new FuncionarioController();
				return $control->getFuncionario($id);

            case NaturezaDespesaController::TIPO_LOJA:
				include_once CONTROLLERS.'loja.php';
				$control = new LojaController();
				return $control->getLoja($id);

            case NaturezaDespesaController::TIPO_VEICULO:
				include_once CONTROLLERS.'veiculo.php';
				$control = new VeiculoController();
				return $control->getVeiculo($id);
			default: return null;
        }
	}

}
?>
