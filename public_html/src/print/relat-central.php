<?php
$config = Config::getInstance();

$origem     = $config->filter('origem');
$destino    = $config->filter('destino');
$limite_inf = $config->filter('data-limite-inferior');
$limite_sup = $config->filter('data-limite-superior');
$resumido   = $config->filter('resumida') != null;
$status     = $config->filter('status');

$movimentacoes = $config->currentController->getMovimentacoes($origem, $destino, $limite_inf, $limite_sup, true);

echo_header($limite_inf, $limite_sup, $resumido, $origem, $destino, $status);

if($resumido){
    html_resumido($movimentacoes);
} else {
    html($movimentacoes);
}

function html($movimentacoes) {
    
    include_once CONTROLLERS.'produto.php';
    $produtoBuff = \buffer\CtrlBuffer::newInstance(new ProdutoController(), 'getProduto');

    $config = Config::getInstance();
    
    echo '<div class=\'content\'>';
    
    foreach($movimentacoes as $movimentacao){
        
        if(empty($movimentacao->lojaOrigem)) { $movimentacao->lojaOrigem = 'FORNECEDOR'; }
        $movimentacao->status   = $config->currentController->getTextualStatus($movimentacao->status);
        echo '<p class=\'name\'>';
        echo $movimentacao->lojaOrigem . ' >> ' . $movimentacao->lojaDestino;
        echo ' ('.$movimentacao->status.') ';
        echo ' no dia <b>'.$config->maskData($movimentacao->data).'</b>';
        echo '</p>';
        
        echo '<div class=\'content\'>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Cod. Produto</th>';
        echo '<th>Desc. Produto</th>';
        echo '<th>Qtd. Saída</th>';
        echo '<th>Qtd. Entrada</th>';
        echo '<th>Valor Uni.</th>';
        echo '<th>Valor Total.</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $produtos = $config->currentController->getProdutosOfCentralEstoque($movimentacao->id);
        $val = $qtd = 0;
        $count_par = 0;
        foreach($produtos as $p) { 
            
            $val += ( $p->quantidadeEntrada * $p->valor ); 
            $qtd += $p->quantidadeEntrada;
            
            $produto = $produtoBuff->getEntry($p->produto);
            
            $class = !($count_par%2)?'class=\'par\'':'';  
            echo '<tr '.$class.'>';
            echo '<td>'.$produto->codigo.'</td>';
            echo '<td>'.$produto->descricao.'</td>';
            echo '<td>'.$p->quantidadeEntrada.'</td>';
            echo '<td>'.$p->quantidadeSaida.'</td>';
            echo '<td>R$ '.$config->maskDinheiro($p->valor).'</td>';
            echo '<td>R$ '.$config->maskDinheiro($p->valor * $p->quantidadeEntrada).'</td>';
            echo '</tr>';
            
            $count_par ++;
        }
        echo '</tbody>';
        echo '</table>';
        echo '<p class=\'digest\'>Qtd. Produtos: <b>'.$qtd.'</b> Valor: <b>R$ '.$config->maskDinheiro($val).'</b></p>';
        echo '</div>';
    }
    
    echo '</div>';
}

function html_resumido($movimentacoes){
    
    $config = Config::getInstance();
    
    echo '<div class=\'content\'>';
    
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Origem</th>';
    echo '<th>Destino</th>';
    echo '<th>Data</th>';
    echo '<th>Qtd. Produtos</th>';
    echo '<th>Valor</th>';
    echo '<th>Observação</th>';
    echo '<th>Status</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    $count_par = 0;
    foreach($movimentacoes as $movimentacao){
        
        if(empty($movimentacao->lojaOrigem)) { $movimentacao->lojaOrigem = 'FORNECEDOR'; }
        
        $produtos = $config->currentController->getProdutosOfCentralEstoque($movimentacao->id);
        $val = $qtd = 0;
        foreach($produtos as $p) { 
            $val += ( $p->quantidadeEntrada * $p->valor ); 
            $qtd += $p->quantidadeEntrada; 
        }
        $status_class           = 'status status_'.$movimentacao->status;
        $movimentacao->status   = $config->currentController->getTextualStatus($movimentacao->status); 
        
        $class = !($count_par%2)?'class=\'par\'':'';  
        echo '<tr '.$class.'>';
        echo '<tr>';
        echo '<td>'.$movimentacao->lojaOrigem.'</td>';
        echo '<td>'.$movimentacao->lojaDestino.'</td>';
        echo '<td>'.$config->maskData($movimentacao->data).'</td>';
        echo '<td>'.$qtd.'</td>';
        echo '<td>R$ '.$config->maskDinheiro($val).'</td>';
        echo '<td>'.$movimentacao->observacao.'</td>';
        echo '<td class=\''.$status_class.'\'>'.$movimentacao->status.'</td>';
        echo '</tr>';
        
        $count_par++;
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '</div>';
}

function echo_header($limite_inf, $limite_sup, $resumido, $origem, $destino, $status){
    include_once CONTROLLERS.'loja.php';
    $loja_controller    = new LojaController();
    $config             = Config::getInstance(); 
    
    echo '<p class=\'name\'>';
    
    echo 'Relatório ' . ( $resumido ? 'resumido' : '' );
    
    
    if(empty($status) && $status != '0'){ 
        echo ' de todas as movimentações '; 
    } else { 
        $status = $config->currentController->getTextualStatus($status);
        echo ' das movimentações <b>' . $status . '</b>'; 
    }
    
    if($origem == 'null') { $origem = ' FORNECEDOR '; }
    else if(empty($origem)) { $origem = 'TODAS AS LOJAS';}
    else { $origem = $loja_controller->getLoja($origem)->sigla; }
    if(empty($destino)){ $destino = 'TODAS AS LOJAS'; }
    else { $destino = $loja_controller->getLoja($destino)->sigla; }
    
    echo ' entre <b>' . $origem . '</b> e <b>' . $destino . '</b>';
    if(!empty($limite_inf)) { echo ' de <b>' . $config->maskData($limite_inf) . '</b>'; }
    if(!empty($limite_sup)) { echo ' até <b>' . $config->maskData($limite_sup) . '</b>'; }
    echo ' gerado em <b>' . date('d/m/y') . '</b>';
    
    echo '</p>';
}
?>
<style>
.status {font-weight: bolder; padding: 5px}
.status_0{background: lightgoldenrodyellow; color:goldenrod;}
.status_1{background: green; color:white;}
.status_2{background: salmon; color: brown;}
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
.digest{text-align: right; background: green; color: white; padding: 5px;margin: 0px;border:gray solid 1px;}
</style>