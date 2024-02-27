<?php

include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'venda.php';
include_once CONTROLLERS.'funcionario.php';
include_once CONTROLLERS.'cliente.php';
include_once CONTROLLERS.'localidade.php';
include_once CONTROLLERS.'rota.php';
include_once CONTROLLERS.'regiao.php';
include_once CONTROLLERS.'parcela.php';


$config = Config::getInstance();

$lojaController = new LojaController();
$vendaController = new VendaController();
$funcController = new FuncionarioController();
$clientController = new ClienteController();
$localidadeController = new LocalidadeController();
$rotaController = new RotaController();
$regiaoController = new RegiaoController();
$parcelaController = new ParcelaController();

$bufferFunc = \buffer\CtrlBuffer::newInstance($funcController, 'getFuncionario');
$bufferCliente = \buffer\CtrlBuffer::newInstance($clientController, 'getCliente');
$bufferLocalidade = \buffer\CtrlBuffer::newInstance($localidadeController, 'getLocalidade');
$bufferRota = \buffer\CtrlBuffer::newInstance($rotaController, 'getRota');
$bufferRegiao = \buffer\CtrlBuffer::newInstance($regiaoController, 'getRegiao');

$dtIni = $config->filter('data-limite-inferior');
$dtFim = $config->filter('data-limite-superior');
$idLoja = $config->filter('loja');
$idAgente = $config->filter('agente');
$resumido = $config->filter('resumida') != null;

if (empty($idLoja)) {
    $lojas = $lojaController->getAllLojas();
} else {
    $lojas = array($lojaController->getLoja($idLoja));
}

function cmp_cobrador($a, $b) {
    return strcmp($a->nome, $b->nome);
}

if ($resumido) {

    foreach ($lojas as $loja) {

        $vendasEntregues = $vendaController->getVendasEntregues($loja->id, $dtIni, $dtFim, $idAgente);

        if (empty($vendasEntregues)) {
            echo '<p class="name">' . $loja->sigla . '</p>';
            echo '<div class="content">';
            echo '<p>Nenhuma venda entregue</p>';
            echo '</div>';
            continue;
        }

        $cobradores = array();

        foreach ($vendasEntregues as $venda) {

            $cliente = $bufferCliente->getEntry($venda->cliente);
            $localidade = $bufferLocalidade->getEntry($cliente->localidade);
            $rota = $bufferRota->getEntry($localidade->rota);
            $regiao = $bufferRegiao->getEntry($rota->regiao);
            $cobrador = $bufferFunc->getEntry($regiao->cobrador);

            if (isset($cobradores[$cobrador->id])) {
                $cobradores[$cobrador->id]->qtd += 1;
            } else {
                $cobradores[$cobrador->id] = (object) array(
                  'nome' => $cobrador->nome,
                  'qtd' => 1
                );
            }
        }

        usort($cobradores, "cmp_cobrador");

        echo '<p class="name"> Relatório de vendas entregues pela loja ' . $loja->sigla ;
        echo ', entre '.$config->maskData($dtIni).' e '.$config->maskData($dtFim).'</p>';
        echo '<div class="content">';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Cobrador</th>';
        echo '<th>Vendas Entergues</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tboody>';
        $lojaCount = 0;
        foreach ($cobradores as $cobrador) {
            $lojaCount += $cobrador->qtd;
            echo '<tr>';
            echo '<td>' . $cobrador->nome . '</td>';
            echo '<td>' . $cobrador->qtd . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '<p class="total name">TOTAL: ' . $lojaCount . ' entregas</p>';

        echo '</div>';

    }

} else { //detalhado

    include_once CONTROLLERS.'cliente.php';
    $bufferClientes = \buffer\CtrlBuffer::newInstance(new ClienteController(), 'getCliente');

    function get_as_link($venda_id) {
        return "<a href=\"index.php?op=cad-vend&v=$venda_id\" target='_blank'>$venda_id</a>";
    }

    $geralValorSum =
    $geralVendasCount =
    $geralOculosCount = 0;

    foreach ($lojas as $loja) {

        $vendasEntregues = $vendaController->getVendasEntregues($loja->id, $dtIni, $dtFim, $idAgente);

        if (empty($vendasEntregues)) {
            echo '<p class="name">' . $loja->sigla . '</p>';
            echo '<div class="content">';
            echo '<p>Nenhuma venda entregue</p>';
            echo '</div>';
            continue;
        }

        $cobradores = array();

        foreach ($vendasEntregues as $venda) {

            $cliente = $bufferCliente->getEntry($venda->cliente);
            $localidade = $bufferLocalidade->getEntry($cliente->localidade);
            $rota = $bufferRota->getEntry($localidade->rota);
            $regiao = $bufferRegiao->getEntry($rota->regiao);
            $cobrador = $bufferFunc->getEntry($regiao->cobrador);
            $agente = $bufferFunc->getEntry($venda->agenteVendas);
            $produtos = $vendaController->getProdutosVendaOfVenda($venda->id);

            $vendaCobrador = (object) array(
                'id' => $venda->id,
                'cliente' => $bufferClientes->getEntry($venda->cliente)->nome,
                'dtEntrega' => $venda->dataEntrega,
                'valEntrega'=> $parcelaController->getValorEntrega($venda),
                'cntEntrega' =>count($produtos),
                'agente' => $agente->nome,
                'dtVenda' => $venda->dataVenda
            );

            if (isset($cobradores[$cobrador->id])) {
                $cobradores[$cobrador->id]->vendas[] = $vendaCobrador;
            } else {
                $cobradores[$cobrador->id] = (object) array('nome' => $cobrador->nome, 'vendas' => array($vendaCobrador));
            }
        }

        usort($cobradores, "cmp_cobrador");

        echo '<p class="name"> Relatório de vendas entregues pela loja ' . $loja->sigla ;
        echo ', entre '.$config->maskData($dtIni).' e '.$config->maskData($dtFim).'</p>';
        echo '<div class="content">';

        $lojaValorSum =
        $lojaVendasCount =
        $lojaOculosCount = 0;

        foreach ($cobradores as $cobrador) {

            echo '<p class="name"> Cobrador: ' . $cobrador->nome . '</p>';
            echo '<div class="content">';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Venda</th>';
            echo '<th>Data da Venda</th>';
            echo '<th>Agente de Venda</th>';
            echo '<th>Cliente</th>';
            echo '<th>Data Entrega</th>';
            echo '<th>Valor Entrega</th>';
            echo '<th>Qtd. Produtos</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tboody>';

            uasort($cobrador->vendas, function ($venda, $outraVenda) {
                return strtotime($venda->dtEntrega) -  strtotime($outraVenda->dtEntrega);
            });

            $cobradorValorSum =
            $cobradorOculosCount = 0;
            $cobradorVendasCount = count($cobrador->vendas);

            foreach ($cobrador->vendas as $venda) {
                $cobradorValorSum += $venda->valEntrega;
                $cobradorOculosCount += $venda->cntEntrega;

                echo '<tr>';
                echo '<td>' . get_as_link($venda->id) . '</td>';
                echo '<td>' . $config->maskData($venda->dtVenda) . '</td>';
                echo '<td>' . $venda->agente . '</td>';
                echo '<td>' . $venda->cliente . '</td>';
                echo '<td>' . $config->maskData($venda->dtEntrega) . '</td>';
                echo '<td>R$ ' . $config->maskDinheiro($venda->valEntrega) . '</td>';
                echo '<td>' . $venda->cntEntrega . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';

            echo '<p class=\'name\' style=\'text-align:right; margin-top: 5px; background:green;color:white;\'>';
            echo 'Total '.$cobrador->nome .': ';
            echo '<b>R$ ' . $config->maskDinheiro($cobradorValorSum).'</b>';
            echo '(<b>' . $cobradorVendasCount . '</b>' . vendas . ', <b>' . $cobradorOculosCount . '</b> produtos)';
            echo '</p>';

            echo '</div>';

            $lojaValorSum += $cobradorValorSum;
            $lojaVendasCount += $cobradorVendasCount;
            $lojaOculosCount += $cobradorOculosCount;

        }

        echo '<p class=\'name\' style=\'text-align:right; margin-top: 5px; background:green;color:white;\'>';
        echo 'Total '.$loja->sigla .': <b>R$ ' . $config->maskDinheiro($lojaValorSum) . '</b>';
        echo '(<b>' . $lojaVendasCount . '</b> vendas, <b>' . $lojaOculosCount . '</b> produtos)';
        echo '</p>';

        echo '</div>';



        $geralVendasCount += $lojaVendasCount;
        $geralValorSum += $lojaValorSum;
        $geralOculosCount += $lojaOculosCount;
    }

    echo '<p class=\'name\' style=\'text-align:right; margin-top: 5px; background:green;color:white;\'>';
    echo 'Total : <b>R$ ' . $config->maskDinheiro($geralValorSum). '</b>';
    echo '(<b>' . $geralVendasCount . '</b> vendas, <b>' . $geralOculosCount . '</b> produtos)';
    echo '</p>';

}

?>
<style>
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr:nth-child(even) { background: #eee; }
.content { margin-bottom: 20px; }
.total { font-weight: bolder; text-align: right; border-left: none; }
</style>
