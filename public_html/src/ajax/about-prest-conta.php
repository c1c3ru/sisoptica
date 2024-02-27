<?php

$config = Config::getInstance(); 

$prest  = $config->filter('prest');

if(empty($prest)) $config->throwAjaxError('Prestação de Conta não informada');

$prestacao = $config->currentController->getPrestacaoConta($prest);
    
$type = $config->filter('type'); 

switch ($type) {
    case 'html':
        if(empty($prestacao->id)) exit('<h3>Prestação de Conta inválida</h3>');
        toHtml($prestacao);
        break;
    default:
        
        if(empty($prestacao->id) || $prestacao->status || $prestacao->cancelada) 
            $config->throwAjaxError('Prestação de Conta inválida');
        
        $vars = get_object_vars($prestacao);

        $vars['itens'] = $config->currentController->getItensByPrestacao($prestacao->id);
        
        include_once CONTROLLERS.'tipoPagamento.php';
        include_once CONTROLLERS.'caixa.php';
        $caixaController    = new CaixaController();
        $tipoController     = new TipoPagamentoController(); 
        $tipos_dinheiro     = $tipoController->tiposDinheiro(true);
        $caixas             = array();
        foreach ($vars['itens']  as &$item){ 
            if(in_array($item->tipo, $tipos_dinheiro)){
                if(!isset($caixas[$item->data])){
                    $caixa = $caixaController->getCaixaByData($item->data);
                    $caixas[$item->data] = $caixa;
                } else {
                    $caixa = $caixas[$item->data];
                }
                if(!empty($caixa->id) && $caixa->status == CaixaModel::STATUS_FECHADO){
                    $item->nonCaixa = true;
                }
            }
            $item->prestacao = $prestacao->id; 
        }
        
        include_once CONTROLLERS.'funcionario.php';
        $func_controller    = new FuncionarioController();
        $cobrador           = $func_controller->getFuncionario($prestacao->cobrador); 
        $vars['loja'] = $cobrador->loja;

        include_once CONTROLLERS.'parcela.php';
        $parcela_controller = new ParcelaController();
        $pagamentos         = $parcela_controller->getPagamentosByPrestacao($prestacao->id);

        $vars['pagamentos'] = $pagamentos;

        $type = $config->filter("type");

        $config->throwAjaxSuccess($vars);
        break;
}

function toHtml(PrestacaoConta $prestacao){ 
    
    $config = Config::getInstance();
    $itens  = $config->currentController->getItensByPrestacao($prestacao->id, true);

    include_once CONTROLLERS.'funcionario.php';
    $func_controller    = new FuncionarioController();
    $cobrador           = $func_controller->getFuncionario($prestacao->cobrador);

    include_once CONTROLLERS.'loja.php';
    $loja_controller    = new LojaController();
    $loja               = $loja_controller->getLoja($cobrador->loja);
    
    include_once CONTROLLERS.'parcela.php';
    $parcela_controller = new ParcelaController();
    $pagamentos         = $parcela_controller->getPagamentosByPrestacao($prestacao->id);
?>
<h3>Informações sobre prestaçao de conta</h3>
<label> Loja: <span> <?php echo $loja->sigla; ?> </span> </label>
<label> Cobrador: <span> <?php echo $cobrador->nome; ?> </span> </label>
<br/>
<label> Data Inicial: <span> <?php echo $config->maskData( $prestacao->dtInicial ); ?> </span> </label>
<label> Data Final: <span> <?php echo $config->maskData( $prestacao->dtFinal ); ?> </span> </label>
<br/>
<label> Status: <span> <?php echo (!$prestacao->status ? 'Aberta' : 'Fechada'); ?> </span> </label>
<label> Cancelada: <span> <?php echo (!$prestacao->cancelada ? 'Não' : 'Sim'); ?> </span> </label>
<br/>
<fieldset>
    <legend>Itens</legend>
    <table class="pagamentos-table">
        <thead>
            <tr>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody id="tbody-pagamentos">
    <?php 
    $totalItens = 0;
    foreach($itens as $item){ 
        $totalItens += $item->valor;
    ?>
    <tr> 
        <td>R$ <?php echo $config->maskDinheiro($item->valor)?> </td>
        <td><?php echo $item->tipo; ?></td>
        <td><?php echo ( empty($item->data) ? 'S/D' : $config->maskData($item->data) );?></td>
    </tr>
    <?php } ?>
        </tbody>
    </table>
<p style="font-size: 10pt; padding: 5px; margin-top: 5px; border-top:lightgray solid 1px;"> 
    TOTAL ITENS: <b> R$ <?php echo $config->maskDinheiro($totalItens); ?> </b> 
</p>
</fieldset>
<br/>
<fieldset>
    <legend>Lançamentos vinculados</legend>
    <table class="pagamentos-table">
        <thead>
            <tr>
                <th>Venda</th>
                <th>Nº Parcela</th>
                <th>Valor</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody id="tbody-pagamentos">
            <?php 
            $totalPagamentos = 0;
            foreach($pagamentos as $pagamento){
                $totalPagamentos += $pagamento->valor;
            ?>
            <tr> 
                <td> <?php echo $pagamento->vendaParcela; ?> </td>
                <td> <?php echo $pagamento->numeroParcela; ?> </td>
                <td> <?php echo $config->maskDinheiro($pagamento->valor); ?> </td>
                <td> <?php echo $config->maskData($pagamento->data); ?> </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <p style="font-size: 10pt; padding: 5px; margin-top: 5px; border-top:lightgray solid 1px;"> 
        TOTAL LANÇAMENTOS: <b> R$ <?php echo $config->maskDinheiro($totalPagamentos); ?> </b> 
    </p>
</fieldset>
<style>
.pagamentos-table {text-align: center; font-size: 10pt; width: 100%; max-height: 251.5px;overflow-y: auto;}
.pagamentos-table thead th {background: lightgray;}
.pagamentos-table tr td{border-bottom:lightgray solid 1px;}
.pagamentos-table td, th{padding: 5px; padding-left: 10px; padding-right: 10px; border-radius: 3px;}    
</style>
<?php } ?>
