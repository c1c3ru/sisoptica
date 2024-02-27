<?php

$config = Config::getInstance();

$controller = $config->currentController;

$regiao = $controller->getRegiao( $config->filter("regi") );

if(!empty($regiao->id)){
   $config->throwAjaxSuccess(get_object_vars($regiao));
}
$config->throwAjaxError("Região Inválida");
?>
