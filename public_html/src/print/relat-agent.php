<?php
$config = Config::getInstance();

$id_loja    = $config->filter('loja');
//$id_agente  = $config->filter('agente');

include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'funcionario.php';
$lojaController = new LojaController();
$funcController = new FuncionarioController();

$resumed    = !($config->filter('resumida') == null);
$func_build = $resumed ? 'build_resumed_list_vendas' : 'build_detailed_list_vendas' ;

$accumulator = array();

if(empty($id_loja)){

    $lojas = $lojaController->getAllLojas();

    foreach($lojas as $loja) {
        $func_build($loja, $accumulator);
    }

} else {

    $loja = $lojaController->getLoja($id_loja);

    $func_build($loja, $accumulator);

}

$qtd = count($accumulator);
$sum = array_reduce($accumulator, function ($a, $b) { return $a + $b; });

echo '<p class=\'name total\' align=\'right\'>';
echo ' TOTAL GERAL: ('.$qtd.') R$ '.$config->maskDinheiro($sum);
echo '</p>';

function build_resumed_list_vendas($loja, &$accumulator) {

    $config = Config::getInstance();
    $data_ini   = $config->filter('data-inicial');
    $data_fim   = $config->filter('data-final');

    echo  '<p class=\'name\'> Relatorios das vendas dos agentes de vendas da '.$loja->sigla.'. Gerado em <b>'.date('d/m/Y') .
        '</b> de <b>'.$config->maskData($data_ini).'</b> à <b>'.$config->maskData($data_fim).'</b></p>';
    echo '<div class=\'content\'>';

    echo '<table>';

    echo '<thead>';
    echo '<tr>';
    echo '<th>Agente</th>';
    echo '<th>Qtd. Vendas</th>';
    echo '<th>Valor Total</th>';
    echo '</tr>';
    echo '</thead>';

    echo '<tbody>';

    $agentes    = getAgentes($loja->id);
    $l_acc      = array();

    foreach ($agentes as $agente) {

        $vendas = $config->currentController->getVendasByAgente($agente->id, $data_ini, $data_fim);

        $qtd = count($vendas);
        if ($qtd == 0) continue;

        $sum = 0;
        foreach ($vendas as $venda) {
            $val = $config->currentController->getValorOfVenda($venda->id);
            $sum += $val;

            if (!in_array($venda->id, array_keys($l_acc))) {
                $l_acc[$venda->id] = $val;
            }
        }

        echo '<tr>';
        echo '<td>'.$agente->nome.'</td>';
        echo '<td>'.$qtd.'</td>';
        echo '<td>R$ '.$config->maskDinheiro($sum).'</td>';
        echo '</tr>';

    }

    $id_agente = $config->filter('agente');
    if (empty($id_agente)) {
        $foreignVendas = $config->currentController->getVendasFromLojaByForeignAgente($loja->id, $data_ini, $data_fim);
        $qtd = $sum = 0;
        foreach ($foreignVendas as $fVenda) {
            if (!in_array($fVenda->id, array_keys($l_acc))) {
                $val = $config->currentController->getValorOfVenda($fVenda->id);
                $sum += $val;
                $qtd++;

                $l_acc[$fVenda->id] = $val;
            }
        }
        echo '<tr>';
        echo '<td> (AGENTE DE OUTRAS LOJAS) </td>';
        echo '<td>'.$qtd.'</td>';
        echo '<td>R$ '.$config->maskDinheiro($sum).'</td>';
        echo '</tr>';
    }

    $qtd = $sum = 0;
    $keys_accumulator = array_keys($accumulator);
    foreach ($l_acc as $venda_id => $val) {
        if (!in_array($venda_id, $keys_accumulator)) {
            $accumulator[$venda_id] = $val;
        }
        $qtd++;
        $sum += $val;
    }

    echo '<tr class=\'total\'>';
    echo '<td> TOTAL DA LOJA </td>';
    echo '<td>'.$qtd.'</td>';
    echo '<td>R$ '.$config->maskDinheiro($sum).'</td>';
    echo '</tr>';

    echo '</tbody>';
    echo '</table>';
    echo '</div>';


}

function build_detailed_list_vendas($loja, &$accumulator) {
    $config = Config::getInstance();

    $data_ini   = $config->filter('data-inicial');
    $data_fim   = $config->filter('data-final');
    $agentes    = getAgentes($loja->id);

    foreach ($agentes as $agente) {

        $vendas = $config->currentController->getVendasByAgente($agente->id, $data_ini, $data_fim);

        if (empty($vendas)) continue;


        echo  '<p class=\'name\'> Relatorios das vendas do agente de vendas <b>'.$agente->nome.'</b> da '.$loja->sigla.'</p>';

        echo '<div class=\'content\'>';

        echo '<table>';

        echo '<thead>';
        echo '<tr>';
        echo '<th>Nº</th>';
        echo '<th>Data</th>';
        echo '<th>Cliente</th>';
        echo '<th>Valor</th>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';
        $sum = 0;
        $qtd = count($vendas);
        foreach($vendas as $venda){
            $valor = $config->currentController->getValorOfVenda($venda->id);
            echo '<tr>';
            echo '<td>'.$venda->id.'</td>';
            echo '<td>'.$config->maskData($venda->dataVenda).'</td>';
            echo '<td>'.$venda->cliente.'</td>';
            echo '<td>R$ '.$config->maskDinheiro($valor).'</td>';
            echo '</tr>';
            $sum += $valor;

            if (!in_array($venda->id, array_keys($accumulator))) {
                $accumulator[$venda->id] = $valor;
            }
        }

        echo '<tr class=\'total\'>';
        echo '<td colspan=\'3\'> TOTAL DO AGENTE </td>';
        echo '<td>('.$qtd.') R$ '.$config->maskDinheiro($sum).'</td>';
        echo '</tr>';

        echo '</tbody>';

        echo '</table>';

        echo '</div>';
    }


    $id_agente = $config->filter('agente');
    if (empty($id_agente)) {

        $foreignVendas = $config->currentController->getVendasFromLojaByForeignAgente($loja->id, $data_ini, $data_fim);

        if (!empty($foreignVendas)) {
            echo  '<p class=\'name\'> Relatorios das vendas da loja '.$loja->sigla.' com agentes de vendas de outras lojas </p>';

            echo '<div class=\'content\'>';

            echo '<table>';

            echo '<thead>';
            echo '<tr>';
            echo '<th>Nº</th>';
            echo '<th>Data</th>';
            echo '<th>Cliente</th>';
            echo '<th>Valor</th>';
            echo '</tr>';
            echo '</thead>';

            echo '<tbody>';

            $qtd = $sum = 0;
            foreach ($foreignVendas as $fVenda) {
                if (!in_array($fVenda->id, array_keys($accumulator))) {

                    $valor = $config->currentController->getValorOfVenda($fVenda->id);
                    echo '<tr>';
                    echo '<td>'.$fVenda->id.'</td>';
                    echo '<td>'.$config->maskData($fVenda->dataVenda).'</td>';
                    echo '<td>'.$fVenda->cliente.'</td>';
                    echo '<td>R$ '.$config->maskDinheiro($valor).'</td>';
                    echo '</tr>';

                    $sum += $valor;
                    $qtd++;


                    $accumulator[$fVenda->id] = $valor;
                }
            }

            echo '<tr class=\'total\'>';
            echo '<td colspan=\'3\'> TOTAL DO AGENTE </td>';
            echo '<td>('.$qtd.') R$ '.$config->maskDinheiro($sum).'</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';


}

function getAgentes($loja) {
    $config = Config::getInstance();
    $funcController = new FuncionarioController();
    $id_agente = $config->filter("agente");
    if (empty($id_agente)) {
        $funcController = new FuncionarioController();
        $agentes = array_merge(
                $funcController->getAllByCargo(CargoModel::COD_AGENTE, $loja),
                $funcController->getAllByCargo(CargoModel::COD_VENDEDOR, $loja),
                $funcController->getAllByCargo(CargoModel::COD_LIDER_EQUIPE, $loja));
        return $agentes;
    } else {
        return array($funcController->getFuncionario($id_agente));
    }
}

?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr:nth-child(odd) { background: #eee; }
.content tbody tr.total { background: green; color: white; }
.name.total { font-weight: bold; background: green; color: white;}
</style>
