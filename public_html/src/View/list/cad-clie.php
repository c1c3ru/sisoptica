<?php
$config = Config::getInstance();

$pesquisar = $config->filter("pesquisar-clientes");

if(!is_null($pesquisar)){
    
    $controller = $config->currentController;

    $nome   = $config->filter("nome");
    $cpf    = $config->filter("cpf");
    
    $clientes = $controller->getClientesForListView($nome, $cpf);

    $withOperations = true;

    if(!empty($clientes)){

        echo "<h3 class=\"title-form\"> Lista de Clientes </h3>";

        $headers = array( "nome"    => "Nome",
                          "cpf"     => "CPF",
                          "localidade"  => "Localidade",
                          "bloqueado"   => "Bloq.");

        if ($withOperations) {

            $headers["operations"] = "Operações";
            $img_del = "<img src='".GRID_ICONS."remover.png"."' title='Remover Cliente'>";
            $img_edit = "<img src='".GRID_ICONS."editar.png"."' title='Editar Cliente'>";
            $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Editar Cliente'>";

            for($i = 0, $l = count($clientes); $i < $l; $i++){
                $vars = get_object_vars($clientes[$i]);
                $link_del = "<a href='?op=del_clie&clie={$vars["id"]}' class='ask' ask='deletar' >$img_del</a>";
                $link_edit = "<a href='javascript:openEditClienteMode({$vars["id"]})'>$img_edit</a>";
                $service = "'ajax.php?code=4770&clie={$vars["id"]}&type=html'";
                $link_view = "<a href=\"javascript:openViewDataMode($service)\"> $img_view </a>";
                $vars["operations"] = "$link_del $link_edit $link_view";
                $vars["cpf"] = $config->maskCPF($vars["cpf"]);
                $vars["bloqueado"] = $vars["bloqueado"] ? "S" : "N";
                $clientes[$i] = (object) $vars;
            }
        } else {
            for($i = 0, $l = count($clientes); $i < $l; $i++){
                $vars = get_object_vars($clientes[$i]);
                $vars["cpf"] = $config->maskCPF($vars["cpf"]);
                $vars["bloqueado"] = $vars["bloqueado"] ? "S" : "N";
                $clientes[$i] = (object) $vars;
            }
        }


        include_once LISTS."table-generic.php";

        $table = new GenericTable($headers, $clientes);
        $table->id = "lista-clientes";

        $table->draw();

    } else {
        echo "<h3 class=\"title-form\"> Sem Clientes </h3>";
    }

}

if(!is_null($pesquisar) && !empty($clientes)){
?>
<script>
dependencies.push(function(){
    
    var lTable = $("#lista-clientes").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ], "sWidth": "50%" },
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             <?php if($withOperations){ ?> { "asSorting": []} <?php } ?>
        ],
        "aLengthMenu": [[5, 10, 15, 25, 50, 100, 300, 500, -1], [5, 10, 15, 25, 50, 100, 300, 500, "All"]],
        "sScrollY": "250px",
        "bScrollCollapse": false,
        "iDisplayLength" : 300
    });       

    lTable.$('tr').hover( function() {
        $(this).addClass('highlighted');
    }, function() {
       lTable.$('tr.highlighted').removeClass('highlighted');
    });
});
</script>
<?php } ?>