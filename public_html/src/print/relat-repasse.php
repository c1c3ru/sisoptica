<?php
$config = Config::getInstance();

include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'venda.php';
include_once CONTROLLERS.'funcionario.php';
$lojaController = new LojaController();
$funcController = new FuncionarioController();
$vendController = new VendaController();

$data_ini   = $config->filter('data-inicial');
$data_fim   = $config->filter('data-final');
$loja_id    = $config->filter('loja');
$datas      = $config->filter('data');
$cobrador   = $config->filter('cobrador');

if(empty($loja_id)){
    $lojas = $lojaController->getAllLojas();
    foreach($lojas as $loja){
        $cobradores[$loja->id] = $funcController->getAllCobradores($loja->id);
    }
} else {
    $lojas = array($lojaController->getLoja($loja_id)); 
    if(empty($cobrador)){
        $cobradores[$loja_id] = $funcController->getAllCobradores($loja_id);
    } else {
        $cobradores[$loja_id] = array($funcController->getFuncionario($cobrador));
    }
}

foreach($lojas as $loja) {
    
    echo '<p class=\'name name-loja\'>';
    echo '[<b>'.$loja->sigla.'</b>]';
    echo ' - Relatorios das repasses ';
    echo ' <b>' . $config->maskData($data_ini).'</b>'; 
    echo ' a'; 
    echo ' <b>' . $config->maskData($data_fim) . '</b>' ; 
    echo '</p>';

    echo '<div class=\'content\'>';
    
    foreach($cobradores[$loja->id] as $cobrador){
        echo '<p class=\'name\'><b>'.$cobrador->nome.'</b></p>';
        echo '<div class=\'content\'>';
        
        $repasses = $config->currentController->getRepassesRange($data_ini, $data_fim, $datas, $cobrador->id);
        
        if(empty($repasses)){
            echo '<p class=\'name\'>Sem repasses nesse período</p>';
            echo '</div>';
            continue;
        }
        
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Chegada</th>';
        echo '<th>Env. Consrt.</th>';
        echo '<th>Recb. Consrt.</th>';
        echo '<th>Env. Cliente</th>';
        echo '<th>Obs.</th>';
        echo '<th>Venda</th>';
        echo '<th>Cliente</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $count = 0;
        foreach ($repasses as $repasse) {
            $class = !($count%2) ? '': 'class=\'par\'';
            echo '<tr '.$class.'>';
            echo '<td>'.$config->maskData($repasse->dtChegada).'</td>';
            echo '<td>'.$config->maskData($repasse->dtEnvioConserto).'</td>';
            echo '<td>'.$config->maskData($repasse->dtRecebimentoConserto).'</td>';
            echo '<td>'.$config->maskData($repasse->dtEnvioCliente).'</td>';
            echo '<td>'.$repasse->observacao.'</td>';
            echo '<td>'.$repasse->venda.'</td>';
            $venda = $vendController->getVenda($repasse->venda, true);
            echo '<td>'.$venda->cliente.'</td>';
            echo '</tr>';
            $count++;
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    
    echo '</div>';
}
?>
<style>
.name-loja{background:green;color:white;}
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
</style>