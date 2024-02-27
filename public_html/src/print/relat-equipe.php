<?php

$config = Config::getInstance();

$dt_ini = $config->filter('dt-inicial');
$dt_fim = $config->filter('dt-final');
$is_resumido= $config->filter('resumida') != null;

include_once CONTROLLERS.'loja.php';
$loja_controller = new LojaController();
$loja   = $config->filter('loja');
if(empty($loja)){
    $lojas = $config->currentController->getAllLojas();
} else {
    foreach($loja as $lid){ $lojas[] = $loja_controller->getLoja($lid); }
}


echo '  <p class=\'name\'> Releatório ' . ($is_resumido ? "resumido" : "detalhado") .
        ' de vendas das equipes de venda. Gerado em <b>'.date('d/m/Y') .
        '</b> de <b>'.$config->maskData($dt_ini).'</b> à <b>'.$config->maskData($dt_fim).'</b></p> ';


include_once CONTROLLERS.'venda.php';
$venda_controller = new VendaController();

echo '<div class=\'content\'>';

if ($is_resumido) {

    $valor_total =  $qtd_total = 0;

    foreach($lojas as $loja) {

        $equipes = $config->currentController->getEquipesByLoja($loja->id, true);

        if (empty($equipes)) {
            continue;
        }

        echo '<p class=\'name\'>' . $loja->sigla . '</p>';

        echo '<div class=\'content\'>';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Equipe</th>';
        echo '<th>Loja</th>';
        echo '<th>Valor Total Vendas</th>';
        echo '<th>Qtd. Total Vendas</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $valor_loja = $qtd_loja =  0;

        foreach ($equipes as $equipe) {
            
            $vendas = $venda_controller->getVendasByEquipe($equipe->id, $dt_ini, $dt_fim);
            
            printVendas($vendas, $equipe, $valor_loja, $qtd_loja);

        }

        //para vendas sem equipe
        $vendas_sem_equipe = $venda_controller->getVendasByNullEquipe($loja->id, $dt_ini, $dt_fim);
        $null_equipe = (object) array('nome' => 'SEM EQUIPE', 'loja' => $loja->sigla);
        printVendas($vendas_sem_equipe, $null_equipe, $valor_loja, $qtd_loja);

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        echo '<p class=\'name\' style=\'text-align:right; background:green;color:white;\'>';
        echo 'Total '.$loja->sigla.': <b>R$ ' . $config->maskDinheiro($valor_loja). '</b> (<b>' . $qtd_loja . '</b>vendas)';
        echo '</p>';

        $valor_total += $valor_loja;
        $qtd_total += $qtd_loja;

    }

    echo '<p class=\'name\' style=\'text-align:right; margin-top: 5px;background:green;color:white;\'>';
    echo 'Total: <b>R$ ' . $config->maskDinheiro($valor_total). '</b> (<b>' . $qtd_total . '</b>vendas)';
    echo '</p>';

} else {

    include_once CONTROLLERS.'funcionario.php';
    $funcController = new FuncionarioController();
    $funcBuff = \buffer\CtrlBuffer::newInstance($funcController, 'getFuncionario');

    $valor_total = $qtd_total = 0;

    foreach($lojas as $loja) {

        $equipes = $config->currentController->getEquipesByLoja($loja->id, true);

        if (empty($equipes)) {
            continue;
        }

        $valor_loja = $qtd_loja = 0;

        foreach ($equipes as $equipe) {

            echo '<p class=\'name\'> Loja: <b>' . $loja->sigla . '</b> Equipe: <b>'.$equipe->nome.'</b></p>';

            echo '<div class=\'content\'>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Integrante</th>';
            echo '<th>Valor Total Vendas</th>';
            echo '<th>Qtd. Total Vendas</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $integrantes = $config->currentController->getIntegrantesByCargo($equipe, CargoModel::COD_AGENTE);

            $accumulator_equipe = array();

            foreach ($integrantes as $integrante) {

                $func = $funcBuff->getEntry($integrante->funcionario);

                $vendas = $venda_controller->getVendasByIntegranteDeEquipe($func->id, $dt_ini, $dt_fim);
                $valor_local = 0;
                $qtd_local = count($vendas);
                foreach($vendas as $v){
                    if (!isset($accumulator_equipe[$v->id])) {
                        $accumulator_equipe[$v->id] = $venda_controller->getValorOfVenda($v->id);
                    }
                    $valor_local += $accumulator_equipe[$v->id];
                }

                echo '<tr>';
                echo '<td>'.$func->nome.'</td>';
                echo '<td>R$ '.$config->maskDinheiro($valor_local).'</td>';
                echo '<td>'.$qtd_local.'</td>';
                echo '</tr>';


            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            $valor_equipe = array_reduce($accumulator_equipe, function ($_1, $_2) { return $_1 + $_2; });
            $qtd_equipe = count($accumulator_equipe);

            echo '<p class=\'name\' style=\'text-align:right; background:green;color:white;\'>';
            echo 'Total '.$loja->sigla.' equipe '.$equipe->nome.': <b>R$ ' . $config->maskDinheiro($valor_equipe). '</b> (<b>' . $qtd_equipe . '</b>vendas)';
            echo '</p>';

            $valor_loja += $valor_equipe;
            $qtd_loja += $qtd_equipe;

        }

        echo '<p class=\'name\' style=\'text-align:right; margin-top: 5px; background:green;color:white;\'>';
        echo 'Total '.$loja->sigla.': <b>R$ ' . $config->maskDinheiro($valor_loja). '</b> (<b>' . $qtd_loja . '</b>vendas)';
        echo '</p>';

        $valor_total += $valor_loja;
        $qtd_total += $qtd_loja;

    }

    echo '<p class=\'name\' style=\'text-align:right; margin-top: 5px; background:green;color:white;\'>';
    echo 'Total: <b>R$ ' . $config->maskDinheiro($valor_total). '</b> (<b>' . $qtd_total . '</b>vendas)';
    echo '</p>';

}

echo '</div>';


function printVendas($vendas, $equipe, &$valAcc, &$qtdAcc) {
    $config = Config::getInstance();
    $venda_controller = new VendaController();

    $valor_local = 0;
    $qtd_local = count($vendas);
    foreach($vendas as $v){ $valor_local += $venda_controller->getValorOfVenda($v->id); }


    $valAcc += $valor_local;
    $qtdAcc += $qtd_local;

    echo '<tr>';
    echo '<td>'.$equipe->nome.'</td>';
    echo '<td>'.$equipe->loja.'</td>';
    echo '<td>R$ '.$config->maskDinheiro($valor_local).'</td>';
    echo '<td>'.$qtd_local.'</td>';
    echo '</tr>';
}

?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px;}
.content tbody tr:nth-child(even) { background: #eee; }
.content {border-top: 0;}
</style>
