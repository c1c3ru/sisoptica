<?php
$config = Config::getInstance();

$nome   = $config->filter('nome');

$fields = $config->currentController->fieldsToSearch();

$fields[ClienteModel::TABLE.".".ClienteModel::NOME][0] = $nome;

$vendas = $config->currentController->searchVendas($fields, VendaModel::STATUS_TODOS);

$length = count($vendas);

$res = array();

include_once CONTROLLERS.'cliente.php';
$cliente_controller = new ClienteController();
$clientes           = array(); 
for($i = 0; $i < $length && $i < 5; $i++){
    if(!array_key_exists($vendas[$i]->cliente, $clientes)){
        $cliente = $cliente_controller->getCliente($vendas[$i]->cliente);
        $clientes[$vendas[$i]->cliente] = $cliente->nome;
    }
    $nome_cliente = $clientes[$vendas[$i]->cliente];
    $res[] = array("id" => $vendas[$i]->id, "cliente" => $nome_cliente);
}

$config->throwAjaxSuccess($res);

?>
