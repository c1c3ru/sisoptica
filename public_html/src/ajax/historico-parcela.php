<?php
$config = Config::getInstance();

$numero = $config->filter("numero");
$venda  = $config->filter("venda");

$parcela = $config->currentController->getParcela($numero, $venda);

if(empty($parcela->venda)) 
    $config->throwAjaxError("Parcela inválida");

$pagamentos = $config->currentController->getPagamentosOfParcela($parcela);

$arr = array();

include_once CONTROLLERS."funcionario.php";
$func_controller = new FuncionarioController();
foreach($pagamentos as $p){
    $cobrador   = $func_controller->getFuncionario($p->cobrador);
    $autor      = $func_controller->getFuncionario($p->autor);
    $arr[] = array( "valor" => $config->maskDinheiro($p->valor), 
                    "data" => $config->maskData($p->data), 
                    "cobrador" => utf8_encode($cobrador->nome),
                    "autor" => utf8_encode($autor->nome) );
}

$config->throwAjaxSuccess($arr);

?>
