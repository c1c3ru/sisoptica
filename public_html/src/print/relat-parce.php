<?php

$config = Config::getInstance();

$data = $config->filter("data");

include_once CONTROLLERS."parcela.php";
$parc_controller = new ParcelaController();

$controller_regiao = $config->currentController;

$loja = $config->filter("loja");

include_once CONTROLLERS."loja.php";
$loja_controller = new LojaController();    

if(empty($loja)){
    
    $lojas = $loja_controller->getAllLojas();
    
    foreach ($lojas as $loja) {
        
        echo "<p class='name'>Loja: <b>$loja->sigla</b> ate <b>".$config->maskData($data)."</b></p>";
        
        $regioes = $controller_regiao->getRegioesByLoja($loja->id); 
        
        echo "<div class='content'>";
        mountTable($regioes, $data, $parc_controller);
        echo "</div>";
        
    }
    
} else {
    
    $loja = $loja_controller->getLoja($loja);
    
    echo "<p class='name'>Loja: <b>$loja->sigla</b> ate <b>".$config->maskData($data)."</b></p>";
    
    $regioes_id = $config->filter("regiao");
    $regioes    = array();
    foreach ($regioes_id as $regiao_id) {
        $regiao     = $controller_regiao->getRegiao($regiao_id);
        $regioes[]  = $regiao; 
    }
    echo "<div class='content'>";
    mountTable($regioes, $data, $parc_controller);
    echo "</div>";
}

function mountTable($regioes, $data, ParcelaController $controller){
    $config = Config::getInstance(); 
?>
<table> 
    <thead> 
        <tr> 
            <th> Regiao </th>
            <th> Qtd. Parcelas </th>
            <th> Valor Atrasado </th>
        </tr>
    </thead>
    <tbody> 
    <?php
    foreach ($regioes as $r) {
        echo "<tr>";
        echo "<td> $r->nome </td>";
        $parcelas = $controller->getParcelasAtrasadasInRegiao($r->id, $data);
        echo "<td>".count($parcelas)."</td>";
        $total = 0;
        foreach ($parcelas as $p) {
            $japago = $controller->getValorPagoOfParcela($p);
            $dif    = $p->valor - $japago;
            $total += $dif;
        }
        $valor = $config->maskDinheiro($total);
        echo "<td> R$ $valor </td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table> 

<?php
}
?>
<style> 
.content table thead th:first-child { background: transparent; color: #666; border: none;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; }
.content tbody tr td:hover { background: #eee; }
.content tbody tr td:first-child {background: #EEE;}
.content tbody tr td:last-child {border-left: lightgray solid 1px;}
</style>