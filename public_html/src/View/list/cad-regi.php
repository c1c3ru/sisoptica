<?php

$config = Config::getInstance();

$controller = $config->currentController;

$withOperations = true;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_VENDEDOR) $withOperations = false;

$isWithForeignValues = true;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
    $regioes =  $controller->getAllRegioes($isWithForeignValues);
else 
    $regioes =  $controller->getAllRegioes($isWithForeignValues, $_SESSION[SESSION_LOJA_FUNC]);

if(!empty($regioes)){
    
    echo "<h3 class=\"title-form\"> Lista de Regiões </h3>";
    
    $headers = array( "nome" => "Nome Região",
                      "cobrador" => "Cobrador",
                      "loja" => "Loja" );

    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del =  "<img src='".GRID_ICONS."remover.png"."' title='Remover Região'>";
        $img_edit =  "<img src='".GRID_ICONS."editar.png"."' title='Editar Região'>";
        for($i = 0, $l = count($regioes); $i < $l; $i++){
            $vars = get_object_vars($regioes[$i]);
            $link_del = "<a href='?op=del_regi&regi={$vars["id"]}&loja={$vars["loja"]}' class='ask' ask='deletar' >$img_del</a>";
            $link_edit = "<a href='javascript:openEditRegiaoMode({$vars["id"]})'>$img_edit</a>";
            $vars["operations"] = "$link_del $link_edit";
            $regioes[$i] = (object) $vars;
        }
    }
    //print_r ($regioes);exit;
    include_once LISTS."table-generic.php";
    
    $table = new GenericTable($headers, $regioes);
    $table->id = "lista-regioes";
    
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Regiões </h3>";
}
if(!empty($regioes)){
?>
<script>
dependencies.push(function(){
    
    var lTable = $("#lista-regioes").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ], "sWidth": "35%" },
             { "asSorting": [ "desc", "asc" ], "sWidth": "25%"},
             { "asSorting": [ "desc", "asc" ], "sWidth": "20%" },
             <?php if($withOperations) { ?> { "asSorting": []} <?php } ?>
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