<?php
$config     = Config::getInstance(); 
$id_veiculo = $config->filter('veiculo');
if(empty($id_veiculo)){
    $config->throwAjaxError('Veículo não informado');
}
$veiculo = $config->currentController->getVeiculo($id_veiculo);
$config->throwAjaxSuccess($veiculo);
?>
