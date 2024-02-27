<?php

$config = Config::getInstance();

$verValor   = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR || $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_GERENTE ;

$loja       = $config->filter('loja');

include_once CONTROLLERS.'loja.php';
$loja_controller    = new LojaController();

if(empty($loja)){
    $lojas              = $loja_controller->getAllLojas();
    $loja               = array();
    foreach($lojas as $l){
        $loja[] = $l->id;
    }
} else {
    $loja = array($loja);
}
$labs       = $config->filter('lab');
$mode       = $config->filter('mode');
$ini_date   = $config->filter('data-inicial');
$end_date   = $config->filter('data-final');

$recebidas = ((int) $mode) == 1;

$labOS = $config->currentController->getTuplasByLab($loja, $ini_date, $end_date, $labs, $recebidas);

if(empty($labOS)) {
    exit('<script> alert( \'Nenhuma ordem de serviço '.
         ($recebidas?'enviada e recebida':'somente enviada').
         ' no perído de '.$config->maskData( $ini_date).' a '.$config->maskData( $end_date ).'\'); window.close();</script>');
}
$fieldsToSearch = $config->currentController->fieldsToSearch();
$keys = array_keys($fieldsToSearch);
//Definindo filtros para datas limites
$fieldsToSearch[$keys[3]][0] = " '$ini_date'  AND '$end_date'";
$fieldsToSearch[$keys[3]][1] = SQL_CMP_BETWEEN_CLAUSE; 
//Definindo filtros para datas de recebimento
if($recebidas){
    $fieldsToSearch[$keys[4]][1] = SQL_IS_NOT_NULL_CLAUSE;
} else {
    $fieldsToSearch[$keys[4]][1] = SQL_IS_NULL_CLAUSE;
}
//Defindindo filtro para loja
$fieldsToSearch[$keys[1]][1] = SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES;

$totalMaster = 0;

foreach($loja as $l){

    $l_obj = $loja_controller->getLoja($l);
    
    $fieldsToSearch[$keys[1]][0] = $l_obj->id;
    
    $total = 0;
    
    foreach ($labOS as $lab) {
    
        echo '<p class=\'name\'> Loja: <b>'.$l_obj->sigla.'</b> ( Lab: '.$lab->nome.' ('.$lab->quantidade.') - R$ '.$config->maskDinheiro($lab->total).' )';
        echo '<span style=\'float:right; \'> Relatório de Ordens de Serviço entre <b>'.$config->maskData($ini_date).'</b> e <b>'.$config->maskData($end_date).'</b></span>';
        echo '</p>';

        echo '<div class=\'content\'>';

        $fieldsToSearch[$keys[0]][0] = $lab->id;
        
        $ordens = $config->currentController->searchOrdens( $fieldsToSearch );

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Nº Ordem</th>';
        echo '<th>Nº Venda</th>';
        echo '<th>Data Envio</th>';
        if($recebidas) echo '<th>Data Recebimento</th>';
        if($verValor) echo '<th>Valor</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $count = 0;

        foreach ($ordens as $ordem) {
            $class = $count % 2 ? 'par' : ''; 
            echo '<tr class=\''.$class.'\'>';
            echo '<td>'.$ordem->numero.'</td>';
            $venda = empty($ordem->venda)? 'S/V' : $ordem->venda;
            echo '<td>'.$venda.'</td>';
            echo '<td>'.$config->maskData($ordem->dataEnvioLab).'</td>';
            if($recebidas) echo '<td>'.$config->maskData($ordem->dataRecebimentoLab).'</td>';
            if($verValor) echo '<td>'.$config->maskDinheiro($ordem->valor).'</td>';
            $total += $ordem->valor;
            echo '</tr>';
            $count++;
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

    }

    if($verValor) {
        echo '<p class=\'name borded-bottom\'>Total da loja '.$l_obj->sigla.': <b>R$ '.$config->maskDinheiro($total).' </b></p>';
    }
    
    $totalMaster += $total;
}
if($verValor) {
    echo '<p class=\'name borded-bottom\'>Total de todas as lojas : <b>R$ '.$config->maskDinheiro($totalMaster).' </b></p>';
}
?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
.borded-bottom{background: #EEE; border-bottom: green solid 2px; }
</style>