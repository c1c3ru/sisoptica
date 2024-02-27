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
    
    $vendas = $controller->searchVendas($fields, VendaModel::STATUS_ATIVA, false);
    
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
            "operations"    => "Operações"
        );

        include_once CONTROLLERS."loja.php";
        include_once CONTROLLERS."cliente.php";
        include_once CONTROLLERS."parcela.php";

        $loja_controller = new LojaController();
        $clie_controller = new ClienteController();
        $parc_controller = new ParcelaController();
        
        if($withOperations) {

            $img_view   = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Venda'>";
            $img_rene   = "<img src='".GRID_ICONS."renegociar.png"."' title='Renegociar Venda'>";
            
            for($i = 0, $len = count($vendas); $i < $len; $i++){
                $vars = get_object_vars($vendas[$i]);
                
                $vars["valor"]          = $config->currentController->getValorOfVenda($vars["id"]);
                $vars["valor"]          = 'R$ '.$config->maskDinheiro($vars["valor"]);

                $cliente                = $clie_controller->getCliente($vars["cliente"], true);
                $loja                   = $loja_controller->getLoja($vars["loja"]);
                
                $vars["localidade"]     = substr($cliente->localidade, 0, 20);
                $vars["cliente"]        = substr($cliente->nome, 0 , 20);
                $vars["cpf"]            = $config->maskCPF($cliente->cpf);
                $vars["loja"]           = $loja->sigla;

                $service            = "ajax.php?code=5577&type=html&vend={$vars["id"]}";
                $link_view          = "<a href='javascript:;' onclick=\"openViewDataMode('$service')\">$img_view</a>";
                
                $service            = "ajax.php?code=9650&venda={$vars["id"]}";
                $link_rene          = "<a href='javascript:;' onclick=\"openViewDataMode('$service')\">$img_rene</a>";
                
                $vars["operations"] = "$link_view $link_rene";
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