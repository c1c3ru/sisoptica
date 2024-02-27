<?php
$config = Config::getInstance();

$loja = $config->filter("loja");

if(empty($loja)) $config->throwAjaxError("Loja inválida");

$agentes = array_merge( 
	$config->currentController->getAllByCargo(CargoModel::COD_AGENTE, $loja),
	$config->currentController->getAllByCargo(CargoModel::COD_VENDEDOR, $loja),
	$config->currentController->getAllByCargo(CargoModel::COD_LIDER_EQUIPE, $loja));

$arr = array();

foreach ($agentes as $agente){
    $arr[] = array("nome" => $agente->nome, "id" => $agente->id);
}

$config->throwAjaxSuccess($arr);

?>
