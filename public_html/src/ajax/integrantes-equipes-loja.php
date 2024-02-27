<?php
$config = Config::getInstance();

$lojaid = $config->filter("loja");
$equipeid = $config->filter("equipe");
$cargosid = explode(',', $config->filter("cargo"));

include_once CONTROLLERS.'funcionario.php';
$funcController = new FuncionarioController();
$funcBuffer = \buffer\CtrlBuffer::newInstance($funcController, 'getFuncionario');

$arr = array();

$equipes = (empty($equipeid)) ? $config->currentController->getEquipesByLoja($lojaid) :
                                 array($config->currentController->getEquipe($equipeid));

foreach ($equipes as $equipe){

    $integrantes = array();
    foreach ($cargosid as $cargoid) {
        $integrantes = array_merge($integrantes, $config->currentController->getIntegrantesByCargo($equipe, $cargoid));
    }

    foreach ($integrantes as $integrante) {
        $func = $funcBuffer->getEntry($integrante->funcionario);
        $arr[] = array("nome" => $func->nome, "id" => $func->id);
    }
}

$config->throwAjaxSuccess($arr);

?>
