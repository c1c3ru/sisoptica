<?php

$config = Config::getInstance();

if(!$config->checkGerentConfirm()){
    $config->throwAjaxError("Essa operação necessita da autorização do gerente.");
}

$controller = $config->currentController;

include_once CONTROLLERS."venda.php";
$venda_controller = new VendaController();

$venda = $venda_controller->getVenda($config->filter("venda"));

if(empty($venda->id) || $venda->status != VendaModel::STATUS_ATIVA){
    $config->throwAjaxError("Venda Inválida/Não ativa");
}

$desconto       = str_replace(',', '.', $config->filter("desconto"));
$desconto       = empty($desconto) ? 0.00 : (float) $desconto;
$restante       = $controller->getRestanteOfVenda($venda->id);
$restanteCalc   = $restante; //$controller->getRestanteOfVenda($venda, true);

$isDiretor      = $_SESSION[SESSION_CARGO_FUNC] == CargoModel::COD_DIRETOR;
$max            = $config->maskDinheiro($isDiretor ? $restanteCalc : $restanteCalc * 0.2);
$max            = (float) str_replace(",", ".", $max);

if($desconto > $max){
    $max_value = $config->maskDinheiro($max);
    $config->throwAjaxError("O desconto não pode ser maior que 20% do restante (R$ $max_value)");
} 

$cobrador   = $config->filter("cobrador");
$data       = $config->filter("data");
$prest_id   = $config->filter('prest');

if(empty($prest_id)){
    $config->throwAjaxError("A prestação de conta precisa ser inforamada");
}
include_once CONTROLLERS.'prestacaoConta.php';
$prestacao_controller   = new PrestacaoContaController();
$prestacao              = $prestacao_controller->getPrestacaoConta($prest_id);
if(empty($prestacao->id) || $prestacao->status || $prestacao->cancelada){
    $config->throwAjaxError('Prestação de conta inválida');
}
$time_data  = strtotime($data);
$time_dti   = strtotime($prestacao->dtInicial);
$time_dtf   = strtotime($prestacao->dtFinal);
if($time_data < $time_dti ||  $time_data > $time_dtf ){
    $config->throwAjaxError('A data não está no perídodo de arrecadação da prestação de conta selecionada');
}
$total_prest    = $prestacao_controller->getValorOfPrestacao($prestacao->id);
$ja_pago_prest  = $controller->getValorOfPrestacao($prestacao->id);
if(($ja_pago_prest + ($restante - $desconto)) > $total_prest){
    $config->throwAjaxError('O lançamento ultrapassou a soma dos itens da prestação de conta selecionada');
}

$parcelas    = $controller->getParcleasByVenda($venda->id, ParcelaController::PARCELAS_NAO_PAGAS);

if(!empty($parcelas)){
    $parcela = $parcelas[0];
} else $config->throwAjaxError("Não há parcelas a pagar");

$page_all   = $controller->darBaixaEmParcela($parcela, $restante, $cobrador, $data, $prestacao->id, true);

if($page_all) { 
    
    if($desconto > 0.00) {
        //Adicionar desconto nas parcelas e pagamentos aqui
        $parcela_desconto = new Parcela( $parcelas[count($parcelas) -1]->numero + 1, 
                                         date("Y-m-d"), null, -$desconto, true, $venda->id, false );
        $controller->addParcela($parcela_desconto);

        
        //Adicionando  o lançamento com valor negativo, representando o desconto
        $pagamento = new Pagamento();
        $pagamento->cobrador        = $cobrador;
        $pagamento->data            = date("Y-m-d"); //Today
        $pagamento->dataBaixa       = date("Y-m-d"); //Today
        $pagamento->valor           = -1 * $desconto;
        $pagamento->numeroParcela   = $parcela_desconto->numero;
        $pagamento->vendaParcela    = $parcela_desconto->venda;
        $pagamento->prestacaoConta  = $prestacao->id;
        $pagamento->autor           = @(!empty($_SESSION[SESSION_ID_FUNC])? $_SESSION[SESSION_ID_FUNC] : null);
        $pagamento->comDesconto     = true;
        $controller->addPagamento($pagamento);
        //Finalizando adição
        
        $venda_controller->checkAndTurnStatus($venda->id);
    }
    
    $config->throwAjaxSuccess(null, "Todas as parcelas foram quitadas");    
} else $config->throwAjaxError("Falha ao quitar todas as parcelas restantes");

?>
