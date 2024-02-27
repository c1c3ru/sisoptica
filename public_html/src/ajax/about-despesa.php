<?php
$config     = Config::getInstance();   
$despesa_id = $config->filter('despesa');
$despesa    = $config->currentController->getDespesa($despesa_id);
if(empty($despesa->id)){
    $config->throwAjaxError('Despesa Inválida');
}
$vars = get_object_vars($despesa);

include_once CONTROLLERS.'naturezaDespesa.php';
$naturezaController = new NaturezaDespesaController();
$natureza = $naturezaController->getNatureza($despesa->natureza);
$nome_natureza = strtoupper($natureza->nome);
if( strpos($nome_natureza, 'COMBUSTÍVEL') !== FALSE || 
    strpos($nome_natureza, 'COMBUSTIVEL') !== FALSE ) {
    
    $vars['isCombustivel']  = true;
    
    include_once CONTROLLERS.'combustivel.php';
    $combustivelController  = new CombustivelController();
    $vars['combustivel']    = $combustivelController->getCombustivelByDespesa($despesa->id);
    
} else {
    $vars['isCombustivel'] = false;
}
$config->throwAjaxSuccess($vars);
?>
