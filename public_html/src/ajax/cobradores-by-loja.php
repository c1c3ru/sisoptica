<?php
$config = Config::getInstance();

$cobradores = $config->currentController->getAllCobradores($config->filter("loja"));

$arr = array();

foreach ($cobradores as $cobrador) {
    $arr[] = array("id" => $cobrador->id, "nome" => $cobrador->nome);
}

$config->throwAjaxSuccess($arr);

?>
