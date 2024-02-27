<?php
$config = Config::getInstance();

$id_loja    = $config->filter('loja');
$data_ini   = $config->filter('data-limite-inferior');
$data_fim   = $config->filter('data-limite-superior');
$detalhado  = $config->filter('resumida') == null;

include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'despesa.php';
include_once CONTROLLERS.'combustivel.php';
include_once CONTROLLERS.'naturezaDespesa.php';
include_once CONTROLLERS.'caixa.php';
$lojaController         = new LojaController();
$despesaController      = new DespesaController();
$combustivelController  = new CombustivelController();


if(empty($id_loja)){
    $lojas = $lojaController->getAllLojas();
} else {
    $lojas = array($lojaController->getLoja($id_loja));   
}

$despesasBuffer = \buffer\CtrlBuffer::newInstance($despesaController, 'getDespesa');

foreach ($lojas as $loja){
    
    $veiculos = $config->currentController->getVeiculosByLoja($loja->id);
    
    echo  '<p class=\'name\'> Relatório do consumo de combustível dos veículos da <b>'.
            $loja->sigla.'</b> de <b>'.$config->maskData($data_ini).'</b> à <b>'.$config->maskData($data_fim).'</b></p>';
    
    echo '<div class=\'content\'>';
    
    echo '<table>';
    
    echo '<thead>';
    echo '<tr>';
    echo '<th>Veiculo</th>';
    if(!$detalhado){
        echo '<th>Qtd.</th>';
    }
    echo '<th>Km Inicial</th>';
    echo '<th>Km Final</th>';
    echo '<th>Litros</th>';
    echo '<th>Valor</th>';
    echo '<th>Média R$/L</th>';
    echo '<th>Média Km/L</th>';
    echo '</tr>';
    echo '</thead>';
    
    echo '<tbody>';
    $count = 0;
    foreach($veiculos as $veiculo){
        $combustiveis   = $combustivelController->getCombustiveisByVeiculo($veiculo->id, $data_ini, $data_fim);
        if(empty($combustiveis)) continue;
        
        if($detalhado) {
            foreach ($combustiveis as $combustivel) {
                $despesa = $despesasBuffer->getEntry($combustivel->despesa);
                $class = !($count % 2) ? 'class=\'par\'' : '' ;
                $mediaKmL = !$combustivel->litros ? 0 : 
                            ($combustivel->kmFinal - $combustivel->kmInicial) / $combustivel->litros;
                echo '<tr '.$class.'>';
                echo '<td>'.$veiculo->nome.'</td>';
                echo '<td>'.number_format($combustivel->kmInicial, 2, '.', ' ').'</td>';
                echo '<td>'.number_format($combustivel->kmFinal, 2, '.', ' ').'</td>';
                echo '<td>'.$combustivel->litros.'</td>';
                echo '<td>R$ '.$config->maskDinheiro($despesa->valor).'</td>';
                echo '<td>R$ '.$config->maskDinheiro($combustivel->preco).'</td>';
                echo '<td>'.number_format($mediaKmL, 2, '.', ' ').'</td>';
                echo '</tr>';
                $count++;
            }
            
        } else {
            $minKm = $combustiveis[0]->kmInicial; 
            $maxKm = $combustiveis[0]->kmFinal;
            $sumValor = $sumLitros = $sumPreco = 0;
            $qtdCombustiveis= count($combustiveis);
            for ($i = 1, $l = count($combustiveis); $i < $l; $i++) {
                $combustivel = $combustiveis[$i];
                $despesa = $despesasBuffer->getEntry($combustivel->despesa);
                $sumValor += $despesa->valor; 
                if(!isset($minKm)) $minKm = $combustivel->kmInicial;
                else if($combustivel->kmInicial < $minKm) $minKm = $combustivel->kmInicial;
                if(!isset($maxKm)) $maxKm = $combustivel->kmFinal;
                else if($combustivel->kmFinal > $maxKm) $maxKm = $combustivel->kmFinal;
                $sumLitros += $combustivel->litros;
                $sumPreco += $combustivel->preco;
            }
            $mediaPrecoL    = !$qtdCombustiveis ? 0 : $sumPreco / $qtdCombustiveis;
            $mediaKmL       = !$sumLitros ? 0 : ($maxKm - $minKm) / $sumLitros;
            $class = !($count % 2) ? 'class=\'par\'' : '' ; 
            echo '<tr '.$class.'>';
            echo '<td>'.$veiculo->nome.'</td>';
            echo '<td>'.$qtdCombustiveis.'</td>';
            echo '<td>'.number_format($minKm, 2, '.', ' ').'</td>';
            echo '<td>'.number_format($maxKm, 2, '.', ' ').'</td>';
            echo '<td>'.$sumLitros.'</td>';
            echo '<td>R$ '.$config->maskDinheiro($sumValor).'</td>';
            echo '<td>R$ '.$config->maskDinheiro($mediaPrecoL).'</td>';
            echo '<td>'.number_format($mediaKmL, 2, '.', ' ').'</td>';
            echo '</tr>';
            $count++;
            unset($minKm);
            unset($maxKm);
        }
    }
    
    echo '</tbody>';
    
    echo '</table>';
    
    echo '</div>';
    
}
?>
<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
</style>