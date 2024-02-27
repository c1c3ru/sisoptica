<?php

$config = Config::getInstance();

$laboratorio = $config->currentController->getLaboratorio($config->filter("labo"));

if(empty($laboratorio->id)) $config->throwAjaxError("Labortório Inexistente");

$config->throwAjaxSuccess(get_object_vars($laboratorio));

?>
