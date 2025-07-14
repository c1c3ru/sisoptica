<?php

$config = Config::getInstance();

$controller = $config->currentController;

$withOperations = false;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $withOperations = true;
}

$laboratorios = $controller->getAllLaboratorios();

if(!empty($laboratorios)){
    
    echo "<h3 class=\"title-form\"> Lista de Laboratórios </h3>";
    
    $headers = array( "nome" => "Nome", 
                      "telefone" => "Telefone",
                      "cnpj" => "CNPJ",
                      "principal" => "Principal"
                    );
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del = "<img src='".GRID_ICONS."remover.png"."' title='Remover Laboratório'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png"."' title='Editar Laboratório'>";
        for($i = 0, $l = count($laboratorios); $i < $l; $i++){
            $vars = get_object_vars($laboratorios[$i]);
            $link_del = "<a href='?op=del_labo&labo={$vars["id"]}' class='ask' ask='deletar' >$img_del</a>";
            $link_edit = "<a href='javascript:openEditLaboratorioMode({$vars["id"]})'>$img_edit</a>";
            $vars["operations"] = "$link_del $link_edit";
            
            $vars["principal"] = $vars["principal"] ? "Sim" : "Não";
            
            $laboratorios[$i] = (object) $vars;
        }
    }
    
    for($i = 0, $l = count($laboratorios); $i < $l; $i++){
        $vars = get_object_vars($laboratorios[$i]);
        $vars["cnpj"] = $config->maskCNPJ($vars["cnpj"]);
        $laboratorios[$i] = (object) $vars;
    }
    
    include_once LISTS."table-generic.php";
    
    $table = new GenericTable($headers, $laboratorios);
    $table->id = "lista-laboratorios";
    
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Laboratórios </h3>";
}
if(!empty($laboratorios)){
?>
<script>
dependencies.push(function(){
    
    var lTable = $("#lista-laboratorios").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ], "sWidth":"25%"},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             <?php if($withOperations){ ?>{ "asSorting": [] }, <?php } ?>
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