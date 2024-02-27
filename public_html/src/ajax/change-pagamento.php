<?php
$config = Config::getInstance();

if(!$config->checkGerentConfirm()) 
    $config->throwAjaxError("Essa operação necessita da confirmação do gerente");

$pgto  = $config->filter("pgto");
if(empty($pgto)){
    $config->throwAjaxError("Pagamento Inválido");
}

$pagamento  = $config->currentController->getPagamento($pgto);
if(empty($pagamento->id)){
    $config->throwAjaxError("Pagamento Inválido");
}

include_once CONTROLLERS.'prestacaoConta.php';
$prestacao_controller   = new PrestacaoContaController();
$prestacao              = $prestacao_controller->getPrestacaoConta($pagamento->prestacaoConta);
if($prestacao->status || $prestacao->cancelada){
    $config->throwAjaxError('Prestação de conta associada a esse pagamento já está fechadaou cancelada!');
}

$valor                  = (float) $config->filter("valor");
$valorAtual             = (float) $pagamento->valor; 
$restante               = $config->currentController->getRestanteOfVenda($pagamento->vendaParcela);
if($valor > ($restante+$valorAtual)){
    $config->throwAjaxError("O valor não pode ser editado, pois ultrapassa o restante para quitar a divida");
}
$pagamento->data        = $config->filter("data");
$pagamento->cobrador    = $config->filter("cobrador");
$pagamento->prestacaoConta = $config->filter('prest');
$parcela                = $config->currentController->getParcela($pagamento->numeroParcela, $pagamento->vendaParcela);  
$japago                 = $config->currentController->getValorPagoOfParcela($parcela);
$valorProjeParcela      = ($japago - $valorAtual) + $valor;
$res                    = false;
$quitada = true; 
if($valorProjeParcela > $parcela->valor){
    
    $dif                = $valorProjeParcela - $parcela->valor;
    $pagamento->valor   = $valor - $dif;
    
    $res = $config->currentController->updatePagamento($pagamento);
    
    if(!$config->currentController->darBaixaEmParcela($parcela, $dif, $pagamento->cobrador)){
        $config->throwAjaxError("Falha ao redistribuir valor execido");
    }
    
} else {
    
    $pagamento->valor = $valor;
    
    $res = $config->currentController->updatePagamento($pagamento);
    
    if($parcela->status && $valorProjeParcela < $parcela->valor){
        $quitada = false;
        $config->currentController->turnNonQuitada($parcela);
    }
    if(!$parcela->status && ($valorProjeParcela == $parcela->valor)){
       $config->currentController->turnQuitada($parcela);
    }
}

if($res){
    
    include_once CONTROLLERS."venda.php";
    $venda_controller = new VendaController();
    $venda_controller->checkAndTurnStatus($parcela->venda);
    
    $config->throwAjaxSuccess(null, "Sucesso na Operação");
}

$config->throwAjaxError("Falha ao atualizar pagamento");

?>
