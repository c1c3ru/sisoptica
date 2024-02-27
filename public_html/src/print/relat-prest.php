<?php
$config = Config::getInstance();

$loja_id    = $config->filter('loja');

include_once CONTROLLERS.'loja.php';
$loja_controller = new LojaController();    
if(empty($loja_id)){
    $lojas = $loja_controller->getAllLojas(); 
} else {
    $lojas = array($loja_controller->getLoja($loja_id)); 
}
$inicial        = $config->filter('inicial');
$final          = $config->filter('final');
$cobrador_id    = $config->filter('cobrador');

include_once CONTROLLERS.'funcionario.php';
$func_controller = new FuncionarioController();

$sumGeral = 0;

foreach ($lojas as $loja){
    
    if(empty($cobrador_id)){
        $cobradores = $func_controller->getAllCobradores($loja->id);
    } else {
        $cobradores[] = $func_controller->getFuncionario($cobrador_id);
    }

    echo    '<p class=\'name\'> Lista de prestações de conta da loja <b>' . $loja->sigla . '</b> entre <b>'.
            $config->maskData($inicial) . '</b> e <b>' . $config->maskData($final) .
            '</b>, separadas por cobrador. Gerado em <b>' . date('d/m/Y') . '</b> </p>';

    echo '<div class=\'content\'>';

    $sumLoja = 0;
    
    foreach($cobradores as $cobrador){

        $prestacoes = $config->currentController->getPrestacoesByCobrador(
            $cobrador->id, -1, $inicial, $final
        ); 

        if(empty($prestacoes)){
            continue;
        }
        
        echo '<p class=\'name\'>Cobrador: <b>' . $cobrador->nome . '</b> </p>';

        echo '<div class=\'content\'>';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Seq.</th>';
        echo '<th>Data Inicial</th>';
        echo '<th>Data Final</th>';
        echo '<th>Valor</th>';
        echo '<th>Status</th>';
        echo '</tr>'; 
        echo '</thead>';
        echo '<tbody>';
        $count = 0;
        foreach ($prestacoes as $prestacao) {
            $valor = $config->currentController->getValorOfPrestacao($prestacao->id);
            $sumLoja += $valor;
            echo '<tr '.($count % 2 ? '' : 'class=\'par\'' ).' >';
            echo '<td>'.$prestacao->seq.'</td>';
            echo '<td>'.$config->maskData($prestacao->dtInicial).'</td>';
            echo '<td>'.$config->maskData($prestacao->dtFinal).'</td>';
            echo '<td>'.$config->maskDinheiro($valor).'</td>';
            echo '<td>'.($prestacao->status ? 'Fechada' : 'Aberta' ).'</td>';
            echo '</tr>';
            $count++;
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    echo '<p class=\'total\'> Total da Loja <b>'.$loja->sigla.'</b>: <b>R$ '.$config->maskDinheiro($sumLoja).'</b></p>';
    
    echo '</div>';
    
    $sumGeral += $sumLoja;
}
echo '<p class=\'total\'> Total da Geral: <b>R$ '.$config->maskDinheiro($sumGeral).'</b></p>';
?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
.content .name{background: green; color: white;}
.total { text-align: right; color: #333; background: #eee; font-size: 10pt; border: green solid 2px; padding: 7.5px; margin: 0;}
</style>