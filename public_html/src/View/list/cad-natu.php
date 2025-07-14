<?php

$config = Config::getInstance();

$controller = $config->currentController;
$naturezas = $controller->getAllNaturezas();

$withOperations = true;

if(!empty($naturezas)) {

    $tipos = $controller->tiposNatureza;
    
    echo "<h3 class=\"title-form\"> Lista de Natureza de Despesa </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array (  "nome" => "Nome", "tipo" => "Tipo de Entidade", "entrada" => "Fluxo");
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Natureza'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Natureza'>";
        for($i = 0, $len = count($naturezas); $i < $len; $i++){
            $vars = get_object_vars($naturezas[$i]);

            $link_del = "<a href=\"?op=del_natu&natureza={$vars["id"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href=\"javascript:openEditNaturezaMode({$vars["id"]})\">$img_edit</a>";

            $vars["operations"] = "$link_del $link_edit";
            $vars['tipo']       = $tipos[$vars['tipo']];
            $vars['entrada']    = $vars['entrada'] ? 'Entrada':'Saída';
            
            $naturezas[$i] = (object) $vars;
        }
        $naturezas[] = array('nome' => 'ITEM PRESTAÇÃO DE CONTA', 
                             'tipo' => 'LOJA', 'entrada' => 'Entrada', 
                             'operations' => '');
    }
    
    $table = new GenericTable($headers, $naturezas);
    $table->id = "lista-naturezas";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Natureza de Despesa </h3>";
}
if(!empty($naturezas)) {
?>  
<script>
dependencies.push(function(){
    var oTable = $("#lista-naturezas").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             <?php if($withOperations) { ?> { "asSorting": [],"sWidth":"10%"  } <?php } ?>               
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