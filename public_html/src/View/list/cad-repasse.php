<?php

$config = Config::getInstance();

$controller = $config->currentController;

$withOperations = true;

$repasses =  $controller->getAllRepasses( true );

if(!empty($repasses)){

    echo "<h3 class=\"title-form\"> Lista de Repasses </h3>";

    include_once LISTS."table-generic.php";   

    $headers = array( 
        "dtChegada"             => "Chegada",
        "dtEnvioConserto"       => "Env. Consrt",
        "dtRecebimentoConserto" => "Rec. Consrt",
        "dtEnvioCliente"        => "Env. Clint",
        "observacao"            => "Obs.",
        "cobrador"              => "Cobrador",
        "cliente"               => "Cliente",
        "venda"                 => "Venda"
    );
    
    include_once CONTROLLERS.'venda.php';
    $venda_controller   = new VendaController();
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Repasse'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Repasse'>";
//        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Localidade'>";
        
        for($i = 0, $len = count($repasses); $i < $len; $i++){
            $vars = get_object_vars($repasses[$i]);
            $link_del = "<a href=\"?op=del_repasse&repasse={$vars["id"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href='javascript:openEditRepasseMode({$vars["id"]});'>$img_edit</a>";
//            $service = "ajax.php?code=XXXX&type=html&repasse={$vars["id"]}";
//            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"]             = "$link_del $link_edit";
            $vars["dtChegada"]              = $config->maskData($vars["dtChegada"]);
            $vars["dtEnvioConserto"]        = $vars["dtEnvioConserto"] ? $config->maskData($vars["dtEnvioConserto"]) : '-';
            $vars["dtRecebimentoConserto"]  = $vars["dtRecebimentoConserto"] ? $config->maskData($vars["dtRecebimentoConserto"]) : '-';
            $vars["dtEnvioCliente"]         = $vars["dtEnvioCliente"] ? $config->maskData($vars["dtEnvioCliente"]) : '-';
            $venda = $venda_controller->getVenda($vars['venda'], true);
            $vars["cliente"] = substr($venda->cliente,0,20);
            $repasses[$i] = (object) $vars;
        }
        
    } /*else {
        $headers["operations"] = "Operações";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Localidade'>";
        for($i = 0, $len = count($localidades); $i < $len; $i++){
            $vars = get_object_vars($localidades[$i]);
            $service = "ajax.php?code=7571&type=html&loca={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_view";
            $localidades[$i] = (object) $vars;
        }
    }*/

    
    $table = new GenericTable($headers, $repasses);
    $table->id = "lista-repasse";
    $table->draw();

} else {
    echo "<h3 class=\"title-form\"> Sem Repasses </h3>";
}
if(!empty($repasses)){
?>    
<script>
dependencies.push(function(){
    var oTable = $("#lista-repasse").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [], "sWidth" : "5%" }              
        ],
        "sScrollY": "250px",
        "bScrollCollapse": false
    });       

    oTable.$('tr').hover( function() {
        $(this).addClass('highlighted');
    }, function() {
       oTable.$('tr.highlighted').removeClass('highlighted');
    });
}); 
</script>
<?php } ?>
    
