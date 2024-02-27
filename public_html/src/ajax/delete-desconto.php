<?php
$config = Config::getInstance();

if ($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR &&
    $_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR) {
    $config->throwAjaxError("Você não tem permissões suficientes.");
}

$venda = $config->filter("vend");
if(empty($venda)) $config->throwAjaxError("Venda Inválida");

include_once CONTROLLERS.'venda.php';
$venda_controller = new VendaController();
if($venda_controller->hasRenegociada($venda)) $config->throwAjaxError("Venda Renegociada!");


$desconto = $config->currentController->getDescontoOfVenda($venda);
if(!$desconto) $config->throwAjaxError("Desconto Inválido");

if($config->currentController->removePagamentoComODesconto($desconto->venda) &&
   $config->currentController->removerParcela($desconto)){
    
    include_once CONTROLLERS."venda.php";
    $venda_controller = new VendaController();
    $venda_controller->checkAndTurnStatus($venda);
    
    $config->throwAjaxSuccess(null, "Sucesso na Operação");
    
}
$config->throwAjaxError("Falha ao eliminar desconto");
    
?>
