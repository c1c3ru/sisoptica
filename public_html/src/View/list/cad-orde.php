<?php

$config = Config::getInstance();

$pesquisar = $config->filter("pesquisar-ordens");

if(!is_null($pesquisar)) {

    $controller = $config->currentController;

    $fields     = $controller->fieldsToSearch();
    
    foreach ($fields as $f => $v) {
        $r_f        = str_replace(".", "_", $f);
        $param      = $config->filter($r_f);
        $fields[$f][0] = is_null($param) ? $v[0] : $param ;
    }
    
    $isWithForeignValues = true;
    $withOperations = true;

    $ordens = $controller->searchOrdens($fields, $isWithForeignValues);

    if(!empty($ordens)){

        echo "<h3 class=\"title-form\"> Lista de Ordens de Serviço </h3>";

        $headers = array( "numero"              => "Número",
                          "loja"                => "Loja",
                          "venda"               => "Nº Venda",
                          "dataEnvioLab"        => "Data Envio",
                          "dataRecebimentoLab"  => "Recebimento"
                         );
        
//        include_once CONTROLLERS."venda.php";
//        $venda_controller = new VendaController();
        
        if($withOperations) {
            $headers["operations"] = "Operações";
            $img_del = "<img src='".GRID_ICONS."remover.png"."' title='Remover Ordem de Serviço'>";
            $img_edit = "<img src='".GRID_ICONS."editar.png"."' title='Editar Ordem de Serviço'>";
            $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Ordem de Serviço'>";

            for($i = 0, $l = count($ordens); $i < $l; $i++){
                $vars = get_object_vars($ordens[$i]);
//                $venda  = $venda_controller->getVendaByOrdemServico($vars["id"]);
                $vars["venda"] = empty($vars["venda"]) ? "S/V" : $vars["venda"] ;
                $vars["armacaoLoja"] = $vars["armacaoLoja"] ? "Sim" : "Não";
                $vars["dataEnvioLab"] = $config->maskData($vars["dataEnvioLab"]);
                if(!empty( $vars["dataRecebimentoLab"] ))
                    $vars["dataRecebimentoLab"] = $config->maskData($vars["dataRecebimentoLab"]);
                $link_del = "";
                if(!$vars["cancelada"])
                    $link_del = "<a href='javascript:;' onclick=\"ACTION_AFTER = function(){delOrdem({$vars["id"]});}\" class='ask pgn' ask='cancelar' >$img_del</a>";
                $link_edit = "<a href='javascript:;' onclick=\"openEditOrdemMode({$vars["id"]})\">$img_edit</a>";
                $service = "ajax.php?code=7723&type=html&orde={$vars["id"]}";
                $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
                $vars["operations"] = "$link_del $link_edit $link_view";

                $vars["cancelada"] = $vars["cancelada"] ? "Sim" : "Não";

                $ordens[$i] = (object) $vars;
            }
        } 

        include_once LISTS."table-generic.php";

        $table = new GenericTable($headers, $ordens);
        $table->id = "lista-ordens";

        $table->draw();

    } else {
        echo "<h3 class=\"title-form\"> Sem Ordens de Serviço </h3>";
    }
}
if(!is_null($pesquisar) && !empty($ordens)){
?>
<script>
dependencies.push(function(){
    
    var lTable = $("#lista-ordens").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             <?php if($withOperations){ ?>{ "asSorting": []}, <?php } ?>
        ],
        "sScrollY": "250px",
        "bScrollCollapse": false
    });       

    lTable.$('tr').hover( function() {
        $(this).addClass('highlighted');
    }, function() {
       lTable.$('tr.highlighted').removeClass('highlighted');
    });
});
</script>
<?php } ?>