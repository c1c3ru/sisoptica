<?php

include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'venda.php';

$lojaController = new LojaController();
$vendaController = new VendaController();

$dt_ini = $config->filter('data-limite-inferior');
$dt_fim = $config->filter('data-limite-superior');
$is_resumida = !is_null($config->filter('resumida'));
$loja = $config->filter("loja");

if(empty($loja)){
    $lojas = $lojaController->getAllLojas();
} else {
    foreach($loja as $lid){ $lojas[] = $lojaController->getLoja($lid); }
}

echo '  <p class=\'name\'> Relatório ' . ($is_resumida ? 'resumido' : 'detalhado' ) .
        ' de vendas quitadas gerado em <b>'.date('d/m/Y').'</b> de
        <b>'.$config->maskData($dt_ini).'</b> à <b>'.$config->maskData($dt_fim).'</b></p> ';

echo '<div class=\'content\'>';
$acc_global = 0;

if ($is_resumida) {

    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>LOJA</th>';
    echo '<th>VENDAS QUITADAS</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($lojas as $loja) {
        $vendasQuitadas = $vendaController->getAllVendasQuitadas($loja->id, $dt_ini, $dt_fim);
        $qtdVendasQuitadas = count($vendasQuitadas);
        $acc_global += $qtdVendasQuitadas;
        echo '<tr>';
        echo '<td>' . $loja->sigla . '</td>';
        echo '<td>' . $qtdVendasQuitadas . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

} else { //detalhado

    include_once CONTROLLERS.'cliente.php';
    $bufferClientes = \buffer\CtrlBuffer::newInstance(new ClienteController(), 'getCliente');

    function get_as_link($venda_id) {
        return "<a href=\"index.php?op=cad-vend&v=$venda_id\" target='_blank'>$venda_id</a>";
    }

    foreach ($lojas as $loja) {
        echo '<p class="name">' . $loja->sigla;

        $vendasQuitadas = $vendaController->getAllVendasQuitadas($loja->id, $dt_ini, $dt_fim);
        $qtdVendasQuitadas = count($vendasQuitadas);
        if ($qtdVendasQuitadas == 0) {
            echo ': Sem vendas quitadas nesses período</p><br/>';
            continue;
        } else echo '</p>';

        $acc_global += $qtdVendasQuitadas;

        echo '<div class="content">';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>VENDA</th>';
        echo '<th>CLIENTE</th>';
        echo '<th>DATA QUITAÇÃO</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($vendasQuitadas as $vendaAndData) {

            $venda = $vendaAndData[0];
            $data = $vendaAndData[1];

            $cliente = $bufferClientes->getEntry($venda->cliente);

            echo '<tr>';
            echo '<td>' . get_as_link($venda->id) . '</td>';
            echo '<td>' . $cliente->nome . '</td>';
            echo '<td>' . $config->maskData($data) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '</div>';
    }
}

echo '<p class="total name">TOTAL DE VENDAS QUITADAS: ' . $acc_global . '</p>';
echo '</div>';

?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr:nth-child(even) { background: #eee; }
.total { font-weight: bolder; text-align: right; border-left: none; }
</style>