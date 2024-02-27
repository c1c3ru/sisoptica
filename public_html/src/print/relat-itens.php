<?php
$config = Config::getInstance();

include_once CONTROLLERS.'tipoPagamento.php';
include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'funcionario.php';
$tipoController = new TipoPagamentoController();
$lojaController = new LojaController();
$funcionarioController = new FuncionarioController();

$prestacaoBuff = \buffer\CtrlBuffer::newInstance($config->currentController, 'getPrestacaoConta');
$funcionarioBuffer = \buffer\CtrlBuffer::newInstance($funcionarioController, 'getFuncionario');

$data_ini   = $config->filter('data-limite-inferior');
$data_fim   = $config->filter('data-limite-superior');
$loja_id    = $config->filter('loja');
$tipo_ids   = $config->filter('tipos');

if(empty($loja_id)){
    $lojas = $lojaController->getAllLojas();
} else {
    $lojas = array($lojaController->getLoja($loja_id)); 
}

$tipos = $tipoController->getAllTiposPagamento($tipo_ids);

$sumTotal = 0;

foreach($lojas as $loja) {
    
    echo    '<p class=\'name\'> Itens de prestação de conta de <b>'.
            $config->maskData($data_ini).'</b> a <b>'.$config->maskData($data_fim).
            '</b> da '.$loja->sigla.'</p>';
    
    echo '<div class=\'content\'>';
    
    $sumLoja = 0;
    
    foreach($tipos as $tipo) {

        $itens = $config->currentController->getItensRangeData($tipo->id, $loja->id, $data_ini, $data_fim);

        echo "<p class='name'>{$tipo->nome}</p>";
        
        if(empty($itens)){
            echo '<p class=\'name\'>Sem Itens de '.$tipo->nome.' para essa data na '.$loja->sigla.'</p>';
            continue;
        }

        echo '<table>';

        echo '<thead>';
        echo '<tr>';
        echo '<th>Data</th>';
        echo '<th>Funcionário</th>';
        echo '<th>Prest. Conta Seq.</th>';
        echo '<th>Valor</th>';
        echo '</tr>';
        echo '</thead>';

        $counter    = 0; 

        echo '<tbody>';
        
        $sumTipo = 0;
        
        foreach ($itens as $item) {

            $item->prestacao  = $prestacaoBuff->getEntry($item->prestacao);
            $funcionario = $funcionarioBuffer->getEntry($item->prestacao->cobrador);
            
            $class = !($counter%2) ? 'class=\'par\'': '';
            
            echo '<tr '.$class.'>';
            echo '<td>'.$config->maskData($item->data).'</td>';
            echo '<td>'.$funcionario->nome.'</td>';
            echo '<td>'.$item->prestacao->seq.'</td>';
            echo '<td>'.$config->maskDinheiro($item->valor).'</td>';
            echo '</tr>';
            
            $counter++;
            
            $sumTipo += $item->valor;
            
        }
        echo '</tbody>';

        echo '</table>';

        $sumLoja += $sumTipo;
        
        echo '<p class=\'sum\'>Total: <b>R$ '.$config->maskDinheiro($sumTipo).'</b></p>';
        
    }
    
    echo '</div>';
    
    $sumTotal += $sumLoja;
    
    echo '<p class=\'sum\'>Total <b>'.$loja->sigla.'</b>: <b>R$ '.$config->maskDinheiro($sumLoja).'</b></p>';
    
}

echo '<p class=\'sum\'>Total Geral: <b>R$ '.$config->maskDinheiro($sumTotal).'</b></p>';

?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
.sum { text-align: right; margin:0;padding:10px;padding-right:0;border-bottom: lightgray solid 1px;}
</style>
