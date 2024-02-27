<?php

$config = Config::getInstance();

$controller = $config->currentController;

$rota = $config->filter("rota");

$numberOfPositions = $controller->getNumberOfPositionsByRota($rota);

$config->throwAjaxSuccess($numberOfPositions);

?>
