<?php

$config = Config::getInstance();

$controller = $config->currentController;

$isWithForeignValues = true;

$withOperations = false;
$lojas = $controller->getAllLojas($isWithForeignValues);

if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $withOperations = true;
} 

if(!empty($lojas)){
    
    echo "<h3 class=\"title-form\"> Lista de Lojas </h3>";
    
    $headers = array( "sigla" => "Sigla",
                      "cidade" => "Cidade", 
                      "rua" => "Endereço", 
                      "cnpj" => "CNPJ",
                      "gerente" => "Gerente",
                      "operations" => "Operações");
    
    if($withOperations) {
        
        $img_del = "<img src='".GRID_ICONS."remover.png"."' title='Remover Loja'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png"."' title='Editar Loja'>";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Loja'>";
        for($i = 0, $l = count($lojas); $i < $l; $i++){
            $vars = get_object_vars($lojas[$i]);
            $link_del = "<a href='?op=del_loja&loja={$vars["id"]}' class='ask' ask='deletar' >$img_del</a>";
            $link_edit = "<a href='javascript:openEditLojaMode({$vars["id"]})'>$img_edit</a>";
            $service = "ajax.php?code=9981&type=html&loja={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_del $link_edit $link_view";
            $lojas[$i] = (object) $vars;
        }
    } else {
        
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."'>";
        for($i = 0, $l = count($lojas); $i < $l; $i++){
            $vars = get_object_vars($lojas[$i]);
            $service = "ajax.php?code=9981&type=html&loja={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_view";
            $lojas[$i] = (object) $vars;
        }
    }
    for($i = 0, $l = count($lojas); $i < $l; $i++){
        $vars = get_object_vars($lojas[$i]);
        $vars["cnpj"] = $config->maskCNPJ($vars["cnpj"]);
        $vars["rua"] = $vars["rua"].", ".$vars["numero"];
        $lojas[$i] = (object) $vars;
    }
	
    include_once LISTS."table-generic.php";
    
    $table = new GenericTable($headers, $lojas);
    $table->id = "lista-lojas";
    
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Lojas </h3>";
}
if(!empty($lojas)){
?>
<script>
dependencies.push(function(){
    
    var lTable = $("#lista-lojas").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": []}
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