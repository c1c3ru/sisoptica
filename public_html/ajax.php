<?php

include_once 'src/util/config.php';
//Obtendo controlador geral e auxiliar
$config = Config::getInstance();

//Verificando se o usuário está logado
if(!$config->isLoged()) 
    $config->throwAjaxError("Você não está logado no sistema");

//Iniciando o controlador de serviços AJAX.
$config->ajaxController();
