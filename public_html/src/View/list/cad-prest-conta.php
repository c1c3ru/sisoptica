<?php

$config = Config::getInstance();

$controller = $config->currentController;
$byLoja     = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ? false : $_SESSION[SESSION_LOJA_FUNC]; 
$prestacoes = $controller->getAllPrestacaoConta(true, $byLoja);
$withOperations = false;
if( in_array( $_SESSION[SESSION_PERFIL_FUNC], 
              array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE)) 
    ){
    $withOperations = true;
}

if(!empty($prestacoes)) {

    echo "<h3 class=\"title-form\"> Prestações de Conta </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array (  "cobrador"  => "Cobrador",
                        "seq"       => "Seq.",
                        "status"    => "Status",
                        "dtInicial" => "Dt. Inicial",
                        "dtFinal"   => "Dt. Final",
                        "valorTotal" => "Val. Total",
                        "valorLanc"  => "Val. Lanc.");
    
    include_once CONTROLLERS.'parcela.php';
    include_once CONTROLLERS.'prestacaoConta.php';
    $parcela_controller     = new ParcelaController();
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_del        = "<img src='".GRID_ICONS."remover.png' title='Cancelar Prestação de Conta'>";
        $img_edit       = "<img src='".GRID_ICONS."editar.png' title='Editar Prestação de Conta'>";
        $img_reabrir    = "<img src='".GRID_ICONS."reabrir.png' title='Reabrir Prestação de Conta'/>";
        $img_view       = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Prestação'>";
        for($i = 0, $len = count($prestacoes); $i < $len; $i++){
            $vars = get_object_vars($prestacoes[$i]);
            
            $status = $vars["status"];
            $vars["dtInicial"]  = $config->maskData($vars["dtInicial"]);
            $vars["dtFinal"]  = $config->maskData($vars["dtFinal"]);
            $vars["valorTotal"] = 'R$ '.$config->maskDinheiro( $config->currentController->getValorOfPrestacao($vars['id']) );
            $vars["valorLanc"]  = 'R$ '.$config->maskDinheiro( $parcela_controller->getValorOfPrestacao($vars['id']) );
            
            $link_del = "<a href=\"javascript:;\" onclick=\"delPrest(".$vars['id'].")\" class=\"ask\" ask=\"cancelar\" >$img_del</a>";
            $service = "ajax.php?code=8881&type=html&prest={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            
            if($status){
                $vars['status'] = 'Fechada';
                $link_reopen    = '<a href=\'javascript:;\' onclick=\'reabrirPrestacao('.$vars["id"].')\'>'.$img_reabrir.'</a>'; 
                $vars["operations"] = "$link_reopen $link_view";
            } else {
                $vars['status'] = 'Aberta';
                $link_edit = "<a href='javascript:;' onclick=\"openEditPrestContaMode({$vars["id"]})\">$img_edit</a>";
                $vars["operations"] = "$link_edit $link_del $link_view ";
            }
            
            $prestacoes[$i] = (object) $vars;
        }
    } else {
        $headers["operations"] = "Operações";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Prestação'>";
        for($i = 0, $l = count($prestacoes); $i < $l; $i++){
            $vars = get_object_vars($prestacoes[$i]);
            $vars["dtInicial"]  = $config->maskData($vars["dtInicial"]);
            $vars["dtFinal"]  = $config->maskData($vars["dtFinal"]);
            $vars["valorTotal"] = 'R$ '.$config->maskDinheiro( $config->currentController->getValorOfPrestacao($vars['id']) );
            $vars["valorLanc"]  = 'R$ '.$config->maskDinheiro( $parcela_controller->getValorOfPrestacao($vars['id']) );
            $service = "ajax.php?code=8881&type=html&prest={$vars["id"]}";
            $link_view = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $vars["operations"] = "$link_view";
            $prestacoes[$i] = (object) $vars;
        }
    }
    
    $table = new GenericTable($headers, $prestacoes);
    $table->id = "lista-prestacoes";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Prestações de Conta </h3>";
}
if(!empty($prestacoes)) {
?>
<script>
dependencies.push(function(){
    var oTable = $("#lista-prestacoes").dataTable({
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