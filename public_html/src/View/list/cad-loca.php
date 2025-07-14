<?php

$config = Config::getInstance();

$controller = $config->currentController;

$withOperations = true;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_VENDEDOR) $withOperations = false;

$isWithForeignValues = true;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $localidades =  $controller->getAllLocalidades($isWithForeignValues);
} else 
    $localidades = $controller->getAllLocalidades(
            $isWithForeignValues, 
            $_SESSION[SESSION_LOJA_FUNC]
    );


if(!empty($localidades)){

    echo "<h3 class=\"title-form\"> Lista de Localidades </h3>";

    include_once LISTS."table-generic.php";   

    $headers = array( "nome"    => "Nome", 
                      "cidade"  => "Cidade",
                      "regiao"  => "Região",
                      "rota"    => "Rota",
                      "ordem"   => "Ordem na rota"
                );
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Localidade'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Localidade'>";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Localidade'>";
        
        for($i = 0, $len = count($localidades); $i < $len; $i++){
            $vars = get_object_vars($localidades[$i]);
            $link_del = "<a href=\"?op=del_loca&loca={$vars["id"]}&loja={$vars["loja"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href='javascript:openEditLocalidadeMode({$vars["id"]});'>$img_edit</a>";
            $service = "ajax.php?code=7571&type=html&loca={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_del $link_edit $link_view";
            $localidades[$i] = (object) $vars;
        }
    } else {
        $headers["operations"] = "Operações";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Localidade'>";
        for($i = 0, $len = count($localidades); $i < $len; $i++){
            $vars = get_object_vars($localidades[$i]);
            $service = "ajax.php?code=7571&type=html&loca={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_view";
            $localidades[$i] = (object) $vars;
        }
    }

    
    $table = new GenericTable($headers, $localidades);
    $table->id = "lista-localidades";
    $table->draw();

} else {
    echo "<h3 class=\"title-form\"> Sem Localidades </h3>";
}
if(!empty($localidades)){
?>    
<script>
dependencies.push(function(){
    var oTable = $("#lista-localidades").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [ "desc", "asc" ] },
             { "asSorting": [] }              
        ],
        "aLengthMenu": [[5, 10, 15, 25, 50, 100, 250, 500, -1], [5, 10, 15, 25, 50, 100, 250, 500, "All"]],
        "sScrollY": "250px",
        "bScrollCollapse": false,
        "iDisplayLength" : 500
    });       

    oTable.$('tr').hover( function() {
        $(this).addClass('highlighted');
    }, function() {
       oTable.$('tr.highlighted').removeClass('highlighted');
    });
}); 
</script>
<?php } ?>
    
