<?php

$config = Config::getInstance();

$type = $config->filter('tipo-periodo');

$periodos = array();

switch ($type) {
//    case 'm':
//        $t_ini  = strtotime($config->filter("m-i"));
//        $t_fim  = strtotime($config->filter("m-f"));
//
//        $periodo_header  = " relatório mensal das vendas (valor em R$ e quantidade) de <b>".date('m/Y', $t_ini)."</b> à <b>".date('m/Y', $t_fim)."</b> ";
// 
//        $current    = $t_ini;       
//        while($current <= $t_fim){
//            $periodos[]     = (object) array("name" =>date('n/Y', $current), "date" => date("Y-m", $current));
//            $current        = strtotime("+1 month", $current);  
//        }
//        break;
//        
//    case 'w': 
//        
//        $t_ini      = strtotime($config->filter("w"));
//        $t_fim      = strtotime('+7 days', $t_ini);
//
//        $periodo_date = explode('-', $config->filter('w'));
//        $periodo_header  = " relatório das vendas (valor em R$ e quantidade) da ".  str_replace('W', '', $periodo_date[1]).
//                           "ª semana de ".$periodo_date[0];
//        
//        $current    = $t_ini;
//        while($current <= $t_fim){
//            $periodos[]     = (object) array("name" =>date('d/m/Y', $current), "date" => date("Y-m-d", $current));
//            $current        = strtotime("+1 day", $current);  
//        }
//        break;
//        
    case 'd': 
        $t_ini  = strtotime($config->filter("d-i"));
        $t_fim  = strtotime($config->filter("d-f"));

        $periodo_header  = " relatório diário das vendas (valor em R$ e quantidade) de <b>".date('d/m/Y', $t_ini)."</b> à <b>".date('d/m/Y', $t_fim)."</b> ";       
        
        $current    = $t_ini;       
        while($current <= $t_fim){
            $periodos[]     = (object) array("name" =>date('d/m/Y', $current), "date" => date("Y-m-d", $current));
            $current        = strtotime("+1 day", $current);  
        }
        
        break;
}

include_once CONTROLLERS."venda.php";
$venda_controller = new VendaController();

$controller_regiao = $config->currentController;

$loja = $config->filter("loja");

include_once CONTROLLERS."loja.php";
$loja_controller = new LojaController();    

if(empty($loja)){
    
    $lojas = $loja_controller->getAllLojas();                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
    
    foreach ($lojas as $loja) {
        
        echo "<p class='name'>Loja: $loja->sigla $periodo_header </p>";
        
        $regioes = $controller_regiao->getRegioesByLoja($loja->id); 
        
        echo "<div class='content'>";
        mountTable($loja, $regioes, $periodos, $venda_controller);
        echo "</div>";
        
    }
        
} else {
    
    $loja = $loja_controller->getLoja($loja);
    
    echo "<p class='name'>Loja: $loja->sigla $periodo_header </p>";
    
    $regioes_id = $config->filter("regiao");
    $regioes    = array();
    foreach ($regioes_id as $regiao_id) {
        $regiao     = $controller_regiao->getRegiao($regiao_id);
        $regioes[]  = $regiao; 
    }
    echo "<div class='content'>";
    mountTable($loja, $regioes, $periodos, $venda_controller);
    echo "</div>";
}

function mountTable($loja, $regioes, $periodos, VendaController $controller){
    $config = Config::getInstance();
?>
<table> 
    <thead> 
        <tr> 
            <th> Data </th>
            <?php 
            foreach($regioes as $r){
                echo "<th>".$r->nome."</th>";
            }
            ?>
            <th>OUTRAS REGIÕES</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody> 
    <?php
    $QTD = $VAL = 0; 
    foreach ($periodos as $p) {
        echo "<tr>";
        echo "<td> $p->name </td>";
        $total_qtd  = 0;
        $total_val  = 0; 
        foreach ($regioes as $regiao) {
            $res = $controller->getVendasByRegiaoInDate($regiao->id, $p->date);
            $val = $config->maskDinheiro($res["val"]);
            echo "<td> {$val} ({$res["qtd"]}) </td>";
            $total_qtd += $res["qtd"];
            $total_val += $res["val"];
        }

        //outras regiões
        $res = $controller->getVendasByOthersRegioesInDate($loja->id, $p->date);
        $val = $config->maskDinheiro($res["val"]);
        echo "<td> {$val} ({$res["qtd"]}) </td>";
        $total_qtd += $res["qtd"];
        $total_val += $res["val"];

        $val = $config->maskDinheiro($total_val);
        echo "<td> R$ $val ($total_qtd)  </td>";
        echo "</tr>";
        $QTD += $total_qtd;
        $VAL += $total_val;
    }
    ?>
    <tr>
        <td colspan="<?php echo count($regioes) + 1;?>">&nbsp;<td>
        <td><?php echo "R$ " . $config->maskDinheiro($VAL) . "($QTD)";?></td>
    </tr>
    </tbody>
    <tfoot>
        <tr> 
            <td> Data </td>
            <?php 
            foreach($regioes as $r){
                echo "<td>".$r->nome."</td>";
            }
            ?>
            <td>OUTRAS REGIÕES</td>
            <td> Total </td>
        </tr>
    </tfoot>
</table> 
<?php
}
?>
<style> 
.content {display:inline-block;}
.content table thead th:first-child,
.content table tfoot td:first-child { background: transparent; color: #666; border: none;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; }
.content tbody tr:hover { background: #eee; }
.content tbody tr td:first-child {background: #EEE;}
.content tbody tr td:last-child {border-left: lightgray solid 1px;}
.content tbody tr:last-child td:first-child {background: transparent;}
</style>