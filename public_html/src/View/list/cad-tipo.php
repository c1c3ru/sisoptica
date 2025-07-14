<?php

$config = Config::getInstance();

$controller = $config->currentController;
$tipos = $controller->getAllTiposProduto();

$withOperations = false;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $withOperations = true;
}

if(!empty($tipos)) {

    echo "<h3 class=\"title-form\"> Lista de Tipos de Produto </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array ( "nome" => "Nome" );
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Tipo de Produto'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Tipo de Produto'>";
        for($i = 0, $len = count($tipos); $i < $len; $i++){
            $vars = get_object_vars($tipos[$i]);

            $link_del = "<a href=\"?op=del_tipo&tipo={$vars["id"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href=\"javascript:openEditTipoMode({$vars["id"]})\">$img_edit</a>";

            $vars["operations"] = "$link_del $link_edit";

            $tipos[$i] = (object) $vars;
        }
    }
    
    $table = new GenericTable($headers, $tipos);
    $table->id = "lista-tipos";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Tipos de Produto </h3>";
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