<?php
$config = Config::getInstance();

$loja   = $config->filter('loja');
$so_ativos_req = $config->filter('sa');
$so_ativos = !empty($so_ativos_req); //Somente os ativos
$cargosid = $config->filter('cargo');
if (!empty($cargosid)) $cargosid = explode(',', $cargosid);

$funcs  = empty($cargosid) ? $config->currentController->getAllFuncionarios(false, $loja, $so_ativos) :
                              $config->currentController->getAllByCargo($cargosid, $loja, $so_ativos);
$res    = array();
foreach($funcs as $func){
    $res[] = array('id' => $func->id, 'nome' => $func->nome);
}

$config->throwAjaxSuccess($res);
?>
