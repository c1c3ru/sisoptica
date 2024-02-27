<?php
$config = Config::getInstance();

$id_repasse = $config->filter('repasse');

$repasse = $config->currentController->getRepasse($id_repasse);

if( empty($repasse->id) ){
    $config->throwAjaxError('Repasse inválido');
}

include_once CONTROLLERS.'funcionario.php';
$controller_func = new FuncionarioController();
$cobrador = $controller_func->getFuncionario($repasse->cobrador);

$vars = get_object_vars($repasse);
$vars["loja"] = $cobrador->loja;

$config->throwAjaxSuccess( $vars );

?>
