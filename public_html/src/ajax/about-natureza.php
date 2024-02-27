<?php
$config     = Config::getInstance(); 
$id_natureza = $config->filter('natureza');
if(empty($id_natureza)){
    $config->throwAjaxError('Natureza não informado');
}
$natureza = $config->currentController->getNatureza($id_natureza);
$config->throwAjaxSuccess($natureza);
?>
