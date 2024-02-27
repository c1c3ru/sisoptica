<?php

$config = Config::getInstance();

include_once CONTROLLERS.'loja.php';

$loja_controller    = new LojaController();

$id_loja = $config->filter('loja');

if(empty($id_loja)){
    $lojas      = $loja_controller->getAllLojas();
    $lb_lojas   = " em <b>todas as lojas</b>";
} else {
    $lojas      = array( $loja_controller->getLoja($id_loja) ); 
    $lb_lojas   = " na loja <b>{$lojas[0]->sigla}</b>";
    $id_loja    = array();
}

echo '<p class=\'name\'> Níveis de estoque dos produtos '.$lb_lojas.'. Gerado em '.date('d/m/y').' </p>';

echo '<div class=\'content\'>';

echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th>Cod. Produto</th>';
echo '<th>Desc. Produto</th>';
foreach($lojas as $l){ 
    $id_loja[] = $l->id;
    echo '<th>'.$l->sigla.'</th>';
}
echo '<th>Total</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$produtos = $config->currentController->getEachProdutoInEstoque($id_loja);

$count = 0;

foreach($produtos as $p){

    $class = !($count % 2) ? 'class=\'par\'' : '';

    echo '<tr '.$class.'>';
    echo '<td>'.$p->codigo.'</td>';
    echo '<td>'.$p->descricao.'</td>';
    $sum = 0;
    foreach($id_loja as $l){ 
        echo '<td>'.$p->{'_'.$l}.'</td>';
        $sum += $p->{'_'.$l};
    }
    echo '<td>'.$sum.'</td>';
    echo '</tr>';

    $count++;
}

echo '</tbody>';
echo '</table>';

echo '</div>';

?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
</style>