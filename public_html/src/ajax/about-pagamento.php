<?php

$config = Config::getInstance();

$pagamento = $config->currentController->getPagamento($config->filter("pgto"));

if(empty($pagamento->id)){
    $config->throwAjaxError("Pagamento inválido");
}

$config->throwAjaxSuccess(get_object_vars($pagamento));

?>
