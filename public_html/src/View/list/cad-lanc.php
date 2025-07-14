<?php
$config = Config::getInstance();
$pesquisar_act = $config->filter("pesquisar_lancamento");
if(!is_null($pesquisar_act)) {
    
    $controller = $config->currentController;
    
    $fields     = $controller->fieldsToSearch();
    
    foreach ($fields as $f => $v) {
        $r_f        = str_replace(".", "_", $f);
        $param      = $config->filter($r_f);
        $fields[$f][0] = is_null($param) ? $v[0] : $param ;
    }
    
    $vendas = $controller->searchVendas($fields, "0");
    
    if(empty($vendas)){
        echo "<h3 class=\"title-form\"> Nenhum Resultado </h3>";
    } else {
        echo "<h3 class=\"title-form\"> Lista de Vendas </h3>";
        
        include_once CONTROLLERS."ordemServico.php";
        include_once CONTROLLERS."cliente.php";
        
        $controller_os      = new OrdemServicoController();
        $controller_cliente = new ClienteController();
        
        $img_view   = "<img src='".GRID_ICONS."visualizar.png' title='Visualizar Venda'>";
        $img_edit   = "<img src='".GRID_ICONS."editar.png' title='Editar Lançamentos da Venda'>";
        $img_baixa  = "<img src='".GRID_ICONS."baixa.png' title='Dar baixa na Venda'>";

        for($i = 0, $l = count($vendas); $i < $l; $i++){
            $vars = get_object_vars($vendas[$i]);
            
            $cliente = $controller_cliente->getCliente($vars["cliente"]);
            $vars["nome"] = $cliente->nome;
            $vars["cpf"]  = $config->maskCPF($cliente->cpf);
            
            $os = $controller_os->getOrdemServico($vars["os"]);
            $vars["os"] = $os->numero;
            $service_view = "ajax.php?code=5577&type=html&vend={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service_view')\">$img_view</a>";
            $service_edit  = "ajax.php?code=8004&vend={$vars['id']}";
            $link_edit     = "<a href=\"javascript:openViewDataMode('$service_edit')\">$img_edit</a>";
            $service_baixa  = "ajax.php?code=9923&vend={$vars['id']}";
            $link_baixa     = "<a href=\"javascript:openViewDataMode('$service_baixa')\">$img_baixa</a>";
            
            $vars["operacoes"] = "$link_baixa $link_view $link_edit";
            
            $vendas[$i] = (object) $vars;
        }
        
        include_once LISTS."table-generic.php"; 
        
        $headers = array( "id"        => "Num. Venda",
                          "os"        => "Num. Os",
                          "nome"      => "Nome Cliente",
                          "cpf"       => "CPF",
                          "operacoes" => "Operações"
                        );
        $table = new GenericTable($headers, $vendas);
        $table->id = "lista-vendas";
        $table->draw();
    }
    
}
?>
<script>
<?php if(!is_null($pesquisar_act) && !empty($vendas)) { ?>
dependencies.push(function(){
    var oTable = $("#lista-vendas").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
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
<?php } ?>
</script>