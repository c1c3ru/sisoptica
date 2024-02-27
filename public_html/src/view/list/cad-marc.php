<?php

$config = Config::getInstance();

$controller = $config->currentController;
$marcas = $controller->getAllMarcas();

$withOperations = false;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $withOperations = true;
}

if(!empty($marcas)) {

    echo "<h3 class=\"title-form\"> Lista de Marcas </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array ( "nome" => "Nome" );
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Marca de Produto'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Marca de Produto'>";
        for($i = 0, $len = count($marcas); $i < $len; $i++){
            $vars = get_object_vars($marcas[$i]);

            $link_del = "<a href=\"?op=del_marc&marc={$vars["id"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href=\"javascript:openEditMarcaMode({$vars["id"]})\">$img_edit</a>";

            $vars["operations"] = "$link_del $link_edit";

            $marcas[$i] = (object) $vars;
        }
    }
    
    $table = new GenericTable($headers, $marcas);
    $table->id = "lista-marcas";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Marcas </h3>";
}
if(!empty($marcas)) {
?>
<script>
dependencies.push(function(){
    var oTable = $("#lista-marcas").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ], "sWidth": "70%" },
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