<?php

//Include for CargoModel
include_once CONTROLLERS.'funcionario.php';

//Obtendo controlador geral e auxiliar 
$config = Config::getInstance();

//Obtendo código do serviço
$service_code = $config->filter("code");

//Carregando o serviço
$service = $config->loadAjaxService($service_code);

//Reportando falha em caso de serviço inexistente
if( is_null($service) ) $config->throwAjaxError("Serviço inválido");

//Carregando controlador padrão 
$config->loadCurrentController($service);

//Requisitando o arquivo do serviço
$config->requestService($service);

?>
