<?php
$config = Config::getInstance();

if(!$config->checkGerentConfirm()){
    $config->throwAjaxError('Sem autorização');
}

$venda = $config->filter('venda');

if(empty($venda)) {
    $config->throwAjaxError('Venda inválida');
}

if($config->currentController->turnInCarnes($venda)){
    $config->throwAjaxSuccess(null);
} else {
    $config->throwAjaxError('Falha ao atualizar as parcelas');
}

?>
