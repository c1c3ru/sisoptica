<?php

$config = Config::getInstance();

$controller = $config->currentController;
$tipos = $controller->getAllTiposPagamento();
$withOperations = true;

if(!empty($tipos)) {

    echo "<h3 class=\"title-form\"> Tipos de Recebimento </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array ( "nome" => "Tipo", "observacao" => "Observação" );
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Tipo de Recebimento'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Tipo de Recebimento'>";
        for($i = 0, $len = count($tipos); $i < $len; $i++){
            $vars = get_object_vars($tipos[$i]);

            $link_del = "<a href=\"?op=del_tipo_pgmto&tipo={$vars["id"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href=\"javascript:openEditTipoPagamentoMode({$vars["id"]})\">$img_edit</a>";

            $vars["operations"] = "$link_del $link_edit";

            $tipos[$i] = (object) $vars;
        }
    }
    
    $table = new GenericTable($headers, $tipos);
    $table->id = "lista-tipos";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Tipos de Recebimento </h3>";
}
if(!empty($tipos)) {
?>  
<script>
dependencies.push(function(){
    var oTable = $("#lista-tipos").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ], "sWidth": "45%" },
             { "asSorting": [ "desc", "asc" ], "sWidth": "45%" },
             <?php if($withOperations) { ?> { "asSorting": []  } <?php } ?>               
        ],
        "sScrollY": "200px",
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