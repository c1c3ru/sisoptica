<?php
$config = Config::getInstance();

$controller = $config->currentController;

$pesquisar = $config->filter("pesquisar_lancamento");

if(!is_null($pesquisar)){
    
    $fields     = $controller->fieldsToSearch();
    
    foreach ($fields as $f => $v) {
        $r_f        = str_replace(".", "_", $f);
        $param      = $config->filter($r_f);
        $fields[$f][0] = is_null($param) ? $v[0] : $param ;
    }
    
    $vendas = $controller->searchVendas($fields, $config->filter("status"), false);
    
    $withOperations = true;

    if(!empty($vendas)) {

        echo "<h3 class=\"title-form\"> Lista de Vendas </h3>";

        include_once LISTS."table-generic.php";   

        $headers = array ( 
            "id"            => "Nº",
            "valor"         => "Valor",
            "cliente"       => "Cliente",
            "cpf"           => "CPF",
            "localidade"    => "Localidade",
            "loja"          => "Loja",
            "status"        => "Status",
            "operations"    => "Operações"
        );

        include_once CONTROLLERS."loja.php";
        include_once CONTROLLERS."cliente.php";
        include_once CONTROLLERS."parcela.php";

        $loja_controller = new LojaController();
        $clie_controller = new ClienteController();
        $parc_controller = new ParcelaController();
        
        if($withOperations) {

            $img_del    = "<img src='".GRID_ICONS."remover.png' title='Remover Venda'>";
            $img_edit   = "<img src='".GRID_ICONS."editar.png' title='Editar Venda'>";
            $img_view   = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Venda'>";
            $img_pdf    = "<img src='".GRID_ICONS."pdf.png"."' title='Gerar Carnê'>";
            $img_cal    = "<img src='".GRID_ICONS."calendar.png"."' title='Informar data da entrega'>";

            for($i = 0, $len = count($vendas); $i < $len; $i++){
                $vars = get_object_vars($vendas[$i]);
                
                $dataEntrega            = $vars["dataEntrega"];
                $vars["valor"]          = 'R$ '.$config->maskDinheiro($vars["valor"]);

                $cliente                = $clie_controller->getCliente($vars["cliente"], true);
                $loja                   = $loja_controller->getLoja($vars["loja"]);
                
                $vars["localidade"]     = substr($cliente->localidade, 0, 20);
                $vars["cliente"]        = substr($cliente->nome, 0 , 20);
                $vars["cpf"]            = $config->maskCPF($cliente->cpf);
                $vars["loja"]           = $loja->sigla;

                $isEdiatble             = $parc_controller->isEditableVenda($vars['id']) && 
                                          $config->currentController->isEditableVenda($vendas[$i]);
                
                $link_del = "";
                if($isEdiatble)
                    $link_del   = "<a href=\"javascript:;\" onclick=\"ACTION_AFTER = function(){delVenda({$vars['id']});}\" class=\"ask pgn\" ask=\"cancelar\" >$img_del</a>";
                $link_edit          = $isEdiatble ? "<a href=\"javascript:;\" onclick=\"openVendaTipoMode({$vars['id']})\">$img_edit</a>" : "";
                $service            = "ajax.php?code=5577&type=html&vend={$vars['id']}";
                $link_view          = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
                $link_carne         = "<a href='print.php?code=0001&venda={$vars['id']}&js=sim&ns=1' target='_blank'>$img_pdf</a>";
                $link_data_entrega  = empty($dataEntrega) && $parc_controller->isEditableVenda($vars['id'], true) ? "<a href='javascript:;' onclick='changeDataEntrega({$vars["id"]})'>$img_cal</a>" : "";
                $vars["operations"] = "$link_view $link_edit $link_carne $link_data_entrega $link_del";
                $vars["status"]     = $config->currentController->getStatusName("{$vars["status"]}");
                $vendas[$i] = (object) $vars;
            }
        } else {
            $img_view = "<img src='".GRID_ICONS."visualizar.png"."'>";
            $img_pdf    = "<img src='".GRID_ICONS."pdf.png"."' title='Gerar Carnê'>";
            
            for($i = 0, $len = count($vendas); $i < $len; $i++){
                $vars = get_object_vars($vendas[$i]);

                $vars["valor"]          = $config->currentController->getValorOfVenda($vars["id"]);
                $vars["valor"]          = $config->maskDinheiro($vars["valor"]);
                $vars["status"]         = $config->currentController->getStatusName("{$vars["status"]}");
                $service                = "ajax.php?code=5577&type=html&vend={$vars["id"]}";
                $link_view              = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
                $link_carne             = "<a href='print.php?code=0001&venda={{$vars["id"]}}&js=sim' target='_blank'> $img_pdf </a>";
                $vars["operations"]     = "$link_view $link_carne";

                $vendas[$i] = (object) $vars;
            }
        }

        $table = new GenericTable($headers, $vendas);
        $table->id = "lista-vendas";
        $table->draw();

    } else {
        echo "<h3 class=\"title-form\"> Sem Vendas </h3>";
    }

} 

if(!is_null($pesquisar) && !empty($vendas)){ ?>    
<script>
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
             { "asSorting": [ "desc", "asc" ]},
             { "asSorting": [ "desc", "asc" ]},
             <?php if ($withOperations) { ?> { "asSorting": [ "desc", "asc" ], "sWidth":"20%"} <?php } ?>                            
        ],
        "aLengthMenu": [[5, 10, 15, 25, 50, 100, 300, 500, -1], [5, 10, 15, 25, 50, 100, 300, 500, "All"]],
        "sScrollY": "200px",
        "bScrollCollapse": false,
        "iDisplayLength" : 100
    });    
    oTable.$('tr').hover( function() {
        $(this).addClass('highlighted');
    }, function() {
       oTable.$('tr.highlighted').removeClass('highlighted');
    });
}); 
</script>
<?php } ?>