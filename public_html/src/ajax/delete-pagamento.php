<?php
$config = Config::getInstance();

if ($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR &&
    $_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR) {
    $config->throwAjaxError("Você não tem permissões suficientes.");
}

$pgto = $config->filter("pgto");
if(empty($pgto)){
    $config->throwAjaxError("Pagamento Inválido");
}

$pagamento  = $config->currentController->getPagamento($pgto);
if(empty($pagamento->id)){
    $config->throwAjaxError("Pagamento Inválido");
}

if(!empty($pagamento->prestacaoConta)){
    include_once CONTROLLERS.'prestacaoConta.php';
    $prestacao_controller   = new PrestacaoContaController();
    $prestacao              = $prestacao_controller->getPrestacaoConta($pagamento->prestacaoConta);
    if($prestacao->status){
        $config->throwAjaxError('Prestação de conta associada a esse pagamento já está fechada!');
    }
}
if($config->currentController->removePagamento($pagamento, true)){
    
    include_once CONTROLLERS."venda.php";
    $venda_controller = new VendaController();
    $venda_controller->checkAndTurnStatus($pagamento->vendaParcela);
    
    $config->throwAjaxSuccess(null, "Sucesso na Operação");
}
$config->throwAjaxError("Falha ao remover pagamento");

?>
