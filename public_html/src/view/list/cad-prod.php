<?php

$config = Config::getInstance();

$controller = $config->currentController;

$withOperations = false;

if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $withOperations = true;
}

$isWithFoerignValues = true;
$produtos = $controller->getAllProdutos($isWithFoerignValues);

if(!empty($produtos)){
    
    echo "<h3 class=\"title-form\"> Lista de Produtos </h3>";
    
    $headers = array( "codigo" => "Código" );
    
    if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { 
        $headers["precoCompra"] = "Preço de Compra";
    }
    $headers["precoVenda"] = "Preço de Venda";
    $headers["tipo"] = "Tipo de Produto";
    $headers["marca"] = "Marca";
    $headers["categoria"] = "Categoria";
    $headers["operations"] = "Operações";
    
    if($withOperations) {
        $img_del = "<img src='".GRID_ICONS."remover.png"."' title='Remover Produto'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png"."' title='Editar Produto'>";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Produto'>";
        for($i = 0, $l = count($produtos); $i < $l; $i++){
           
            $vars = get_object_vars($produtos[$i]);
             if($vars["categoria"] == "0")
               $vars["categoria"] = "Produto (Genérica)";
            else
                 $vars["categoria"] = "Lentes";
            $link_del = "<a href='?op=del_prod&prod={$vars["id"]}' class='ask' ask='deletar' >$img_del</a>";
            $link_edit = "<a href='javascript:openEditProdutoMode({$vars["id"]})'>$img_edit</a>";
            $service = "ajax.php?code=7781&type=html&prod={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_del $link_edit $link_view";
            $produtos[$i] = (object) $vars;
        }
    } else {
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Produto'>";
        for($i = 0, $l = count($produtos); $i < $l; $i++){
            $vars = get_object_vars($produtos[$i]);
            $service = "ajax.php?code=7781&type=html&prod={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_view";
            $produtos[$i] = (object) $vars;
        }
    }
    
    include_once LISTS."table-generic.php";
    
    $table = new GenericTable($headers, $produtos);
    $table->id = "lista-produtos";
    
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Produtos </h3>";
}
if(!empty($produtos)){
?>
<script>
dependencies.push(function(){
    
    var lTable = $("#lista-produtos").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
             { "asSorting": [ "desc", "asc" ]},
             <?php } ?>
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [], "sWidth": "15%" }
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