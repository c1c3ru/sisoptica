<?php

$config = Config::getInstance();

$prestacao_id = $config->filter('prest');

if(empty($prestacao_id)){ exit('Prestação de conta inválida'); }

$prestacao = $config->currentController->getPrestacaoConta($prestacao_id);

if(empty($prestacao->id)){ exit('Prestação de conta inválida'); }

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
<link rel="stylesheet" href="css/index.css" type="text/css"/>
<div class="header">
<h3 style="margin-bottom: 10px;">RELATÓRIO DE INFORMAÇÕES SOBRE PRESTAÇAO DE CONTA</h3>
<p>GERADO EM: <?php echo date("d/m/y"); ?></p>
<img src="images/logo.jpg" width="350px"> 
</div>
<p class="v-separator">&nbsp;</p>
<fieldset>
    <label> Loja: <span> <?php echo $loja->sigla; ?> </span> </label>
    <label> Cobrador: <span> <?php echo $cobrador->nome; ?> </span> </label>
	<label> Seq.: <span> <?php echo $prestacao->seq; ?> </span> </label>
    <p class="v-separator">&nbsp;</p>
    <label> Data Inicial: <span> <?php echo $config->maskData( $prestacao->dtInicial ); ?> </span> </label>
    <label> Data Final: <span> <?php echo $config->maskData( $prestacao->dtFinal ); ?> </span> </label>
    <p class="v-separator">&nbsp;</p>
    <label> Status: <span> <?php echo (!$prestacao->status ? 'Aberta' : 'Fechada'); ?> </span> </label>
    <label> Cancelada: <span> <?php echo (!$prestacao->cancelada ? 'Não' : 'Sim'); ?> </span> </label>
</fieldset>
<br/>
<fieldset>
    <p class="title-form">Itens</p>
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
<?php 
$totalPagamentos 	= 0;
$total_pags			= count($pagamentos);
$limit_by_space		= ceil( $total_pags / 3 );
$counter 			= 0;
header_lanc();
$has_opened 		= true;
foreach($pagamentos as $pagamento){
		
	$totalPagamentos += $pagamento->valor;

	row_pag($pagamento);

	$counter++;
	$total_pags--;

	if($counter == $limit_by_space){
		$counter = 0;
		close_lanc();
		$has_opened = false;
		if($total_pags > 0){
			header_lanc();	
			$has_opened = true;
		}
	}

}
if($has_opened){
	close_lanc();
}
function header_lanc(){ 
?>
<div class='lanc-space'>
	<fieldset>
		<p class="title-form">Lançamentos vinculados</p>
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
}

function row_pag($pagamento){
	$config = Config::getInstance();
?>
				<tr> 
					<td> <?php echo $pagamento->vendaParcela; ?> </td>
					<td> <?php echo $pagamento->numeroParcela; ?> </td>
					<td> <?php echo $config->maskDinheiro($pagamento->valor); ?> </td>
					<td> <?php echo $config->maskData($pagamento->data); ?> </td>
				</tr>
<?php
}

function close_lanc(){
?>
   	 		</tbody>
		</table>
	</fieldset>
</div>
<?php
}
?>
<br style="clear:both;"/>
<p style="font-size: 10pt;padding: 5px;border-top: lightgray solid 1px;clear:both; margin-top: 10px;"> 
    TOTAL LANÇAMENTOS: <b> R$ <?php echo $config->maskDinheiro($totalPagamentos); ?> </b> 
</p>
<p class="v-separator">&nbsp;</p>
<div class="assinatura">
    <hr/><br/>
    Nome Completo do Gerente<br/>
    <b>Gerente</b>
</div>
<div class="assinatura" style="float: right;">
    <hr/><br/>
    Nome Completo do Cobrador<br/>
    <b>Cobrador</b>
</div>
<style>
.header{margin: 20px;}
.header h3, .header p {float: right; clear: both;}
.header img {width: 150px;box-shadow: 5px 5px 4px gray;}
.lanc-space {float: left; width: 33%;margin-right:0.33%;}
.pagamentos-table {text-align: center; font-size: 8pt; width: 100%;overflow-y: auto;}
.pagamentos-table thead th {background: lightgray;}
.pagamentos-table tr td{border-bottom:lightgray solid 1px;}
.pagamentos-table td, th{padding: 5px; border-radius: 3px;} 
br{margin-bottom: 10px; margin-top: 10px;}
.assinatura{display: inline-block;text-align: center; width: 25%;margin: 35px;}
</style>
