<?php
$config     = Config::getInstance();

$nparcela   = $config->filter('nparc');
$vparcela   = $config->filter('vparc');
$data       = $config->filter('data');

if(empty($nparcela) || empty($vparcela)){
    $config->throwAjaxError('Campos inválidos');
}

if(!is_array($nparcela)) {
    $parcelas = array($nparcela);
} else {
    $parcelas = $nparcela;
}

if($config->currentController->remarcarParcela($parcelas, $vparcela, $data)){
    $config->throwAjaxSuccess(null, 'Sucesso na Operação!');
} else {
    $config->throwAjaxError('Falha ao remarcar a parcela');
}

?>
