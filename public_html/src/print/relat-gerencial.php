<?php

include_once CONTROLLERS.'parcela.php';
include_once CONTROLLERS.'venda.php';
include_once CONTROLLERS.'repasse.php';
include_once CONTROLLERS.'despesa.php';
include_once CONTROLLERS.'naturezaDespesa.php';
include_once CONTROLLERS.'caixa.php';
include_once CONTROLLERS.'veiculo.php';
include_once CONTROLLERS.'prestacaoConta.php';
include_once CONTROLLERS.'tipoPagamento.php';

$config = Config::getInstance();

$dt_ini = $config->filter('dt-inicial');
$dt_fim = $config->filter('dt-final');

$loja   = $config->filter('loja');
if(empty($loja)){
    $lojas = $config->currentController->getAllLojas();
} else {
    foreach($loja as $lid){ $lojas[] = $config->currentController->getLoja($lid); }
}

echo '  <p class=\'name\'> Relatório Gerencial gerado em <b>'.date('d/m/Y').'</b> de
        <b>'.$config->maskData($dt_ini).'</b> à <b>'.$config->maskData($dt_fim).'</b></p> ';

echo '<div class=\'content\'>';

echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th>DADOS</th>';
$lojas_ids = array();
foreach($lojas as $l){
    $lojas_ids[] = $l->id;
    echo '<th>'.$l->sigla.'</th>';
}
echo '<th>TOTAL</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

row_lancamentos($lojas_ids, $dt_ini, $dt_fim);
row_aplicacao_especie($lojas_ids, $dt_ini, $dt_fim);
row_parcelas_atrasadas($lojas_ids, $dt_ini, $dt_fim);
rows_cancelamentos($lojas_ids, $dt_ini, $dt_fim);
row_respasses($lojas_ids, $dt_ini, $dt_fim);
row_oculos_entregues($lojas_ids, $dt_ini, $dt_fim);
row_vendas_quitadas($lojas_ids, $dt_ini, $dt_fim);
row_total_vendas($lojas_ids, $dt_ini, $dt_fim);

$despesas = row_despesas_entradas($lojas_ids, $dt_ini, $dt_fim, 0);
$receitas = row_despesas_entradas($lojas_ids, $dt_ini, $dt_fim, 1);
row_saldos($lojas_ids, $despesas, $receitas, $dt_ini, $dt_fim);

echo '</tbody>';
echo '</table>';

echo '</div>';

echo '<p class=\'map\'><b>CD</b>: Caixa Diário; <b>PC</b>: Prestação de Conta</p>';

function row_lancamentos($lojas_ids, $dt_ini, $dt_fim){

    $parcela_controller = new ParcelaController();

    $config = Config::getInstance();

    echo '<tr>';
    echo '<td>LANÇAMENTOS</td>';
    $total = 0;
    foreach ($lojas_ids as $lojaid){
        $resumo = $parcela_controller->getResumoPagamentosInLoja($lojaid, $dt_ini, $dt_fim);
        $total += $resumo->total;
        echo '<td>R$ '.$config->maskDinheiro($resumo->total).'</td>';
    }
    echo '<td>R$ '.$config->maskDinheiro($total).'</td>';
    echo '</tr>';
}

function row_aplicacao_especie($lojas_ids, $dt_ini, $dt_fim){

    $config = Config::getInstance();
    $despesas_controller = new DespesaController();
    $natureza_controller = new NaturezaDespesaController();

    $natureza = $natureza_controller->getNaturezaByNome(NaturezaDespesaController::NOME_APLICACAO_ESPECIE);

    echo '<tr>';
    echo '<td>APLICAÇÕES EM ESPÉCIE</td>';
    $total = 0;
    foreach ($lojas_ids as $lojaid) {
        $despesas = $despesas_controller->getDespesaByNaturezaInLoja($natureza, $lojaid, $dt_ini, $dt_fim);
        $sum = summation_value_array($despesas);
        echo '<td>R$ '.$config->maskDinheiro($sum).'</td>';
        $total += $sum;
    }
    echo '<td>R$ '.$config->maskDinheiro($total).'</td>';
    echo '</tr>';
}

function row_parcelas_atrasadas($lojas_ids, $dt_ini, $dt_fim){

    $parcela_controller = new ParcelaController();

    $config = Config::getInstance();

    echo '<tr>';
    echo '<td>PARCELA ATRASADA</td>';
    $total = 0;
    foreach ($lojas_ids as $lojaid){
        $parcelas   = $parcela_controller->getParcelasAtrasdasInLoja($lojaid, $dt_ini, $dt_fim);
        $sum        = 0;
        foreach($parcelas as $p) {
            $japago = $parcela_controller->getValorPagoOfParcela($p);
            $dif    = $p->valor - $japago;
            $sum   += $dif;
        }
        $total += $sum;
        echo '<td>R$ '.$config->maskDinheiro($sum).'</td>';
    }
    echo '<td>R$ '.$config->maskDinheiro($total).'</td>';
    echo '</tr>';
}


function rows_cancelamentos($lojas_ids, $dt_ini, $dt_fim){


    $venda_controller = new VendaController();

    $totals = array('qtd' => 0/*, 'val' => 0*/);
    foreach ($lojas_ids as $lojaid){
        $cancelamentos   = $venda_controller->getCancelamentosByLoja($lojaid, $dt_ini, $dt_fim);
        $total_qtd       = count($cancelamentos);
        $totals[$lojaid]['qtd'] = $total_qtd;
        $totals['qtd'] += $total_qtd;
    }

    echo '<tr>';
    echo '<td>Nº CANCELAMENTOS</td>';
    foreach ($lojas_ids as $lojaid){
        echo '<td>'.$totals[$lojaid]['qtd'].'</td>';
    }
    echo '<td>'.$totals['qtd'].'</td>';
    echo '</tr>';
}


function row_respasses($lojas_ids, $dt_ini, $dt_fim){

    $repasse_controller = new RepasseController();

    echo '<tr>';
    echo '<td>Nº REPASSES </td>';
    $total  = 0;
    $datas  = array( 'dt-chegada', 'dt-recebimento-conserto', 'dt-envio-conserto', 'dt-envio-cliente');
    foreach ($lojas_ids as $lojaid){
        $qtd = count($repasse_controller->getRepassesRange(
            $dt_ini, $dt_fim, $datas, null, $lojaid
        ));
        $total += $qtd;
        echo '<td>'.$qtd.'</td>';
    }
    echo '<td>'.$total.'</td>';
    echo '</tr>';
}

function row_oculos_entregues($lojas_ids, $dt_ini, $dt_fim) {

    $vendaController = new VendaController();

    echo '<tr>';
    echo '<td>VENDAS ENTREGUES</td>';
    $qtdTotalVendasEntregues = 0;
    foreach ($lojas_ids as $loja_id) {
        $qtdVendasEntregues = count(
            $vendaController->getVendasEntregues($loja_id, $dt_ini, $dt_fim)
        );
        $qtdTotalVendasEntregues += $qtdVendasEntregues;
        echo '<td>' . $qtdVendasEntregues . '</td>';
    }
    echo '<td>' . $qtdTotalVendasEntregues . '</td>';
    echo '</tr>';
}

function row_vendas_quitadas($lojas_ids, $dt_ini, $dt_fim) {

    $vendaController = new VendaController();

    echo '<tr>';
    echo '<td>VENDAS QUITADAS</td>';
    $acc = 0;
    foreach ($lojas_ids as $loja_id) {
        $vendasQuitadas = $vendaController->getAllVendasQuitadas($loja_id, $dt_ini, $dt_fim);
        $qtdVendasQuitadas = count($vendasQuitadas);
        $acc += $qtdVendasQuitadas;
        echo '<td>' . $qtdVendasQuitadas . '</td>';
    }
    echo '<td>' . $acc . '</td>';
    echo '</tr>';
}

function row_total_vendas($lojas_ids, $dt_ini, $dt_fim) {
    $vendaController = new VendaController();
    echo '<tr>';
    echo '<td>TOTAL DE VENDAS</td>';
    $acc = 0;
    foreach ($lojas_ids as $loja_id) {
        $vendas = $vendaController->getVendasRangeDate($loja_id, $dt_ini, $dt_fim);
        $qtdVendas = count($vendas);
        $acc += $qtdVendas;
        echo '<td>' . $qtdVendas . '</td>';
    }
    echo '<td>' . $acc . '</td>';
    echo '</tr>';
}

function row_despesas_entradas($lojasIds, $dt_ini, $dt_fim, $tipo /*0 -> saida, 1-> entrada*/){

    $despesasController = new DespesaController();
    $naturezaController = new NaturezaDespesaController();
    $tipoPagamentoController = new TipoPagamentoController();
    $parcelaController = new ParcelaController();

    $rows = array();
    $despesasLojas = array();

    foreach($lojasIds as $lojaId){ $despesasLojas[$lojaId] = 0; }

    $config = Config::getInstance();

    echo '<tr class=\'enfases\' id=\'total-'.$tipo.'\' title=\'Clique para maximizar ou minimizar\'>';

    if ($tipo == 0) {
        $label = 'DESPESAS';
        $origem = "CD";
        $tiposDespesaEntrada = $naturezaController->getAllNaturezasSaidas();
        $getItens = function($natureza, $lojaid, $dt_ini, $dt_fim) use ($despesasController) {
            return $despesasController->getDespesaByNaturezaInLoja($natureza, $lojaid, $dt_ini, $dt_fim);
        };
    } else {
        $label = 'RECEITAS';
        $origem = "PC";
        $tiposDespesaEntrada = array(
            (object) array('nome' => 'BOLETO'),
            (object) array('nome' => 'POR COBRADOR')
        );
        $getItens = function($natureza, $lojaid, $dt_ini, $dt_fim) use ($parcelaController) {
            if ($natureza->nome == 'BOLETO')
                return $parcelaController->getPagamentosByBoletoInRangeByLoja($lojaid, $dt_ini, $dt_fim);
            else if ($natureza->nome == 'POR COBRADOR')
                return $parcelaController->getPagamentosByCobradorInRangeByLoja($lojaid, $dt_ini, $dt_fim);
            else
                return array();
        };
    }

    echo '<td>TOTAL DE ' . $label . '</td>';

    foreach ($tiposDespesaEntrada as $tipoDespesaEntrada) {

        $row = (object) array(
            'natureza' => $tipoDespesaEntrada->nome,
            'total' => 0,
            'lojas' => array(),
            'origem' => $origem
        );

        foreach ($lojasIds as $lojaId) {
            $itens  = $getItens($tipoDespesaEntrada, $lojaId, $dt_ini, $dt_fim);;
                $sum = summation_value_array($itens);
            $row->lojas[$lojaId] = $sum;
            $row->total += $sum;
            $despesasLojas[$lojaId] += $sum;
        }

        $rows[] = $row;
    }

    $total = 0;
    foreach ($lojasIds as $lojaId){
        $despesasVal = $despesasLojas[$lojaId];
        $total += $despesasVal;
        echo '<td>R$ '.$config->maskDinheiro($despesasVal).'</td>';
    }
    echo '<td>R$ '.$config->maskDinheiro($total).'</td>';
    echo '</tr>';

    foreach($rows as $row){
        echo '<tr class=\'enfases-child\' parent=\'total-' . $tipo . '\'>';
        echo '<td>' . $row->natureza . '</td>';
        foreach($row->lojas as $val){
            echo '<td>R$ '.$config->maskDinheiro($val).'</td>';
        }
        echo '<td>R$ '.$config->maskDinheiro($row->total).'</td>';
        echo '</tr>';
    }

    return $despesasLojas;

}

function row_saldos($lojas_ids, $despesas, $receitas, $dt_ini, $dt_fim) {

    $config = Config::getInstance();
    $total = 0;
    $saldos = array();

    echo '<tr class=\'saldo-row\'  id=\'saldo\'>';
    echo '<td>SALDO (Receitas - Despesas)</td>';
    foreach($lojas_ids as $lojaid){
        $res = $receitas[$lojaid] - $despesas[$lojaid];
        echo '<td>R$ '.$config->maskDinheiro($res).'</td>';
        $total += $res;
        $saldos[$lojaid] = $res;
    }
    echo '<td>R$ '.$config->maskDinheiro($total).'</td>';
    echo '</tr>';

}


/////////////////
//    Utils    //
/////////////////

function summation_value_array($arr, $valueAttr = "valor") {
    return array_reduce($arr, function($sum, $next) use ($valueAttr) {
        return $next->$valueAttr + $sum;
    }, 0.0);
}

?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr:nth-child(even) { background: #eee; }
.content tbody tr.enfases{background: green;color: white;}
p.map{font-size: 10pt; color: gray; margin: 5px 5px;}
@media screen {
    .enfases-child {display: none;}
}
.enfases{cursor: pointer; font-weight: bolder;}
.saldo-row{color:white; font-weight: bolder;}
#saldo { background-color: rgb(132, 144, 131) !important; }
#total-0 {background-color: rgb(197, 102, 91);}
#total-1 {background-color: rgb(76, 171, 74);}
</style>
<script src="script/jquery.js"></script>;
<script>
    jQuery(function(){
        $('.enfases').click(function(){
            var pid = jQuery(this).attr('id');
            $('.enfases-child[parent='+pid+']').slideToggle('fast');
        });
    })
</script>
