<?php
$config = Config::getInstance();

$numero = $config->filter("numero");
$venda  = $config->filter("venda");

$parcela = $config->currentController->getParcela($numero, $venda);

if(empty($parcela->venda)) 
    $config->throwAjaxError("Parcela inválida");
if($parcela->status)
    $config->throwAjaxError("Parcela está quitada");
if($parcela->cancelda)
    $config->throwAjaxError("Parcela cancelada");

$valor          = str_replace(",", ".", $config->filter("valor"));

$restante       = $config->currentController->getRestanteOfVenda($parcela->venda);
if($valor > $restante){
    $config->throwAjaxError("O valor não pode ser maior do que o restante para quitar a dívida.");
}

$cobrador       = $config->filter("cobrador"); 
$data           = $config->filter("data");
$prest_id      = $config->filter('prest');

if(empty($prest_id)){
    $config->throwAjaxError("A prestação de conta precisa ser inforamada");
}
include_once CONTROLLERS.'prestacaoConta.php';
$prestacao_controller   = new PrestacaoContaController();
$prestacao              = $prestacao_controller->getPrestacaoConta($prest_id);
if(empty($prestacao->id) || $prestacao->status || $prestacao->cancelada){
    $config->throwAjaxError('Prestação de conta inválida');
}
$time_data  = strtotime($data);
$time_dti   = strtotime($prestacao->dtInicial);
$time_dtf   = strtotime($prestacao->dtFinal);
if($time_data < $time_dti ||  $time_data > $time_dtf ){
    $config->throwAjaxError('A data não está no perídodo de arrecadação da prestação de conta selecionada');
}

$total_prest    = $prestacao_controller->getValorOfPrestacao($prestacao->id);
$ja_pago        = $config->currentController->getValorOfPrestacao($prestacao->id);

if(($ja_pago + $valor) > $total_prest){
    $config->throwAjaxError('O lançamento ultrapassou a soma dos itens da prestação de conta selecionada');
}

if($config->currentController->darBaixaEmParcela($parcela, $valor, $cobrador, $data, $prestacao->id)){
    
    include_once CONTROLLERS."venda.php";
    $venda_controller = new VendaController();
    $venda_controller->checkAndTurnStatus($venda);
    
    $config->throwAjaxSuccess(null, "Parcelas alteradas");
} else {
    $config->throwAjaxError("Falha ao dar baixa nas parcelas");
}

?>
