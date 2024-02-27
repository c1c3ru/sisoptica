<?php

$config = Config::getInstance();

$controller = $config->currentController;

$withOperations = true;

if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_VENDEDOR) $withOperations = false;

if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
    $rotas =  $controller->getAllRotas();
else
    $rotas =  $controller->getAllRotas(false, $_SESSION[SESSION_LOJA_FUNC]);

if(!empty($rotas)) {

    echo "<h3 class=\"title-form\"> Lista de Rotas </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array (  "nome" => "Nome Rota",  
                        "loja" => "Loja",
                        "cobrador" => "Cobrador",
                        "regiao" => "Regiao" );
    
    
    include_once CONTROLLERS."regiao.php";
    $controller_regiao = new RegiaoController();
    for($i = 0, $len = count($rotas); $i < $len; $i++){
        $vars = get_object_vars($rotas[$i]);
        $regiao = $controller_regiao->getRegiao($vars["regiao"], true);
        $vars["loja"] = $regiao->loja;
        $vars["cobrador"] = $regiao->cobrador;
        $vars["regiao"] = $regiao->nome;
        $rotas[$i] = (object) $vars;
    }
    
    if($withOperations) {
        
        $headers["operations"] = "Operações";
        
        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Rota'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Rota'>";
        $img_view = "<img src='".GRID_ICONS."visualizar.png' title='Visualizar Rota'>";
        
        for($i = 0, $len = count($rotas); $i < $len; $i++){
            $vars = get_object_vars($rotas[$i]);

            $link_del = "<a href=\"?op=del_rota&rota={$vars["id"]}&loja={$vars["loja"]}\" class=\"ask\" ask=\"deletar\" >$img_del</a>";
            $link_edit = "<a href=\"javascript:openEditRotaMode({$vars["id"]})\">$img_edit</a>";
            $service = "'ajax.php?code=5582&rota={$vars["id"]}&type=html'";
            $link_view = "<a href=\"javascript:openViewDataMode($service)\"> $img_view </a>";
            
            $vars["operations"] = "$link_del $link_edit $link_view";

            $rotas[$i] = (object) $vars;
        }
    } else {
        $headers["operations"] = "Operações";
        $img_view = "<img src='".GRID_ICONS."visualizar.png' title='Visualizar Rota'>";
        for($i = 0, $len = count($rotas); $i < $len; $i++){
            $vars = get_object_vars($rotas[$i]);
            $service = "'ajax.php?code=5582&rota={$vars["id"]}&type=html'";
            $link_view = "<a href=\"javascript:openViewDataMode($service)\"> $img_view </a>";
            $vars["operations"] = "$link_view";
            $rotas[$i] = (object) $vars;
        }
    }
    
    
    $table = new GenericTable($headers, $rotas);
    $table->id = "lista-rotas";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Rotas </h3>";
}
if(!empty($rotas)) {
?> 
<script>
dependencies.push(function(){
    var oTable = $("#lista-rotas").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": []  }               
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
