<?php

$config = Config::getInstance();

$controller = $config->currentController;
$veiculos = $controller->getAllVeiculos(true);

$withOperations = true;

if(!empty($veiculos)) {

    echo "<h3 class=\"title-form\"> Lista de Veículos </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array (  "nome" => "Nome", "placa" => "Placa", 
                        "loja" => "Loja", "motorista" => "Motorista" );
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Veículo'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Veículo'>";
        for($i = 0, $len = count($veiculos); $i < $len; $i++){
            $vars = get_object_vars($veiculos[$i]);

            $link_del = "<a href=\"?op=del_veic&veiculo={$vars["id"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href=\"javascript:openEditVeiculoMode({$vars["id"]})\">$img_edit</a>";

            $vars["operations"] = "$link_del $link_edit";

            $veiculos[$i] = (object) $vars;
        }
    }
    
    $table = new GenericTable($headers, $veiculos);
    $table->id = "lista-veiculos";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Veículos </h3>";
}
if(!empty($veiculos)) {
?>  
<script>
dependencies.push(function(){
    var oTable = $("#lista-veiculos").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
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