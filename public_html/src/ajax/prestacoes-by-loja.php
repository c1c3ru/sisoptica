<?php
$config     = Config::getInstance(); 

$loja       = $config->filter('loja');
$dt_venda   = $config->filter('dt-venda');
$t_venda    = strtotime($dt_venda);

if(empty($loja)) $config->throwAjaxError('Loja não informada');

$prestacoes = $config->currentController->getAllPrestacaoConta(true, $loja, 0);

$res = array();

foreach($prestacoes as $p){
    $t_prest_i = strtotime($p->dtInicial);
    $t_prest_f = strtotime($p->dtFinal);
    if($t_venda < $t_prest_i && $t_venda > $t_prest_f){
        return false;
    }
    $res[] = array('id' => $p->id, 'nome' => $p->cobrador . ' ('. $p->seq.')');
}

$config->throwAjaxSuccess($res);

?>
