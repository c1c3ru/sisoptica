<?php

$config     = Config::getInstance();
$ini_date   = $config->filter('data-inicial');
$end_date   = $config->filter('data-final');
$lojas_id   = $config->filter('loja');
$is_resumido= $config->filter('resumida') != null;

include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'venda.php';
include_once CONTROLLERS.'funcionario.php';
$loja_controller    = new LojaController();
$venda_controller   = new VendaController();
$func_controller    = new FuncionarioController();    

if($is_resumido) {
    
    echo    '<p class=\'name\'>Resumo dos cancelamentos de venda por loja entre <b>'.
            $config->maskData($ini_date).'</b> e <b>'.$config->maskData($end_date).'</b> </p>';
    
    echo '<div class=\'content\'>';
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Loja</th>';
    echo '<th>Qtd. Cancelamentos</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '</tbody>';
    $count = 0;
    foreach($lojas_id as $loja_id){
        $class = !($count%2)?'class=\'par\'':'';
        $count++;
        $loja = $loja_controller->getLoja($loja_id);
        $cancelamentos = $venda_controller->getCancelamentosByLoja($loja->id, $ini_date, $end_date);
        echo '<tr '.$class.'>';
        echo '<td>'.$loja->sigla.'</td>';
        echo '<td>'.count($cancelamentos).'</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
} else {

    $funcBuffer = \buffer\CtrlBuffer::newInstance($func_controller, 'getFuncionario');

    foreach($lojas_id as $loja_id){
        $loja = $loja_controller->getLoja($loja_id);
        $cancelamentos = $venda_controller->getCancelamentosByLoja($loja->id, $ini_date, $end_date);
        echo    '<p class=\'name\'>Detalhamento dos cancelamentos de venda da loja <b>'.$loja->sigla.'</b> entre <b>'.
                $config->maskData($ini_date).'</b> e <b>'.$config->maskData($end_date).'</b> </p>';
        if(empty($cancelamentos)){
            echo '<div class=\'content\'>Sem cancelamentos nesse período</div>';
            continue;
        }
        echo '<div class=\'content\'>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Venda</th>';
        echo '<th>Autor nome</th>';
        echo '<th>Autor ID</th>';
        echo '<th>Autorizador Por</th>';
        echo '<th>Data</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '</tbody>';
        $count = 0;
        foreach($cancelamentos as $cancelamento) {
            $class = !($count%2)?'class=\'par\'':'';
            $count++;
            $autor      = $funcBuffer->getEntry($cancelamento->autor);
            $autorizaor = $funcBuffer->getEntry($cancelamento->autorizador);
            echo '<tr '.$class.'>';
            echo '<td>'.$cancelamento->venda.'</td>';
            echo '<td>'.$autor->nome.'</td>';
            echo '<td>'.$autor->id.'</td>';
            echo '<td>'.$autorizaor->nome.'</td>';
            echo '<td>'.$config->maskData($cancelamento->data).'</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}
?>
<style>
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
</style>