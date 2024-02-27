<?php
$config = Config::getInstance();

$rotas = $config->currentController->getRotasByRegiao($config->filter("regiao"));

$arr = array();

foreach ($rotas as $rota){
    $arr[] = get_object_vars($rota);
}

$config->throwAjaxSuccess($arr);

?>
