<?php

$config = Config::getInstance();

$controller = $config->currentController;
$isAdmin = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR;
include_once CONTROLLERS.'funcionario.php';
$isDiretor = $_SESSION[SESSION_CARGO_FUNC] == CargoModel::COD_DIRETOR;
$caixas = $controller->getAllCaixas( !$isAdmin && !$isDiretor );

$withOperations = true;

if(!empty($caixas)) {

    $status = $controller->statusCaixa;
    
    echo "<h3 class=\"title-form\"> Lista de Caixas Diários </h3>";

    include_once LISTS."table-generic.php";   
    
    $headers = array ( "loja" => "Loja", "data" => "Caixa", "saldo" => "Saldo", "status" => "Status");
        
    include_once CONTROLLERS.'loja.php';
    $lojaController = new LojaController();
    $lojas          = array();
    
    if($withOperations) {
        $headers["operations"] = "Operações";
        $img_view = "<img src='".GRID_ICONS."visualizar.png' title='Ver Despesas'>";
        $img_fech = "<img src='".GRID_ICONS."registradora.png' title='Fechar Caixa'>";
//        $img_del = "<img src='".GRID_ICONS."remover.png' title='Remover Caixa'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png' title='Editar Caixa'>";
        
        for($i = 0, $len = count($caixas); $i < $len; $i++){
            $vars = get_object_vars($caixas[$i]);
            
            $service    = 'ajax.php?code=8109&caixa='.$vars["id"];
            $link_view  = "<a href=\"javascript:openViewDataMode('$service')\">$img_view</a>";
            $link_caixa = $vars['status'] ? '' : '<a href=\'javascript:;\' onclick=\'fecharCaixa('.$vars['loja'].')\'>'.$img_fech.'</a>';
            $service    = 'ajax.php?code=8181&caixa='.$vars["id"]; 
            $link_edit  = $vars['status'] ? '' : "<a href=\"javascript:openViewDataMode('$service')\">$img_edit</a>";
            
            $vars['operations'] = "$link_caixa $link_view $link_edit";
            
            if(!array_key_exists($vars['loja'], $lojas)){
                $lojas[$vars['loja']] = $lojaController->getLoja($vars['loja']);
            }
            $loja = $lojas[$vars['loja']];
            
            $saldo              = $vars['status'] == CaixaModel::STATUS_FECHADO ? 
                                  $vars["saldo"] : $config->currentController->getSaldoOfCaixa((object) $vars) ;
            $vars["saldo"]      = "R$ ".$config->maskDinheiro($saldo);
            $vars["data"]       = $config->maskData($vars["data"]);
            $vars["loja"]       = $loja->sigla;
            $vars["status"]     = $status[$vars["status"]];
            
            $caixas[$i] = (object) $vars;
        }
    }
    
    $table = new GenericTable($headers, $caixas);
    $table->id = "lista-caixas";
    $table->draw();
    
} else {
    echo "<h3 class=\"title-form\"> Sem Caixa Diário </h3>";
}
if(!empty($caixas)) {
?>  
<script>
dependencies.push(function(){
    var oTable = $("#lista-caixas").dataTable({
        "bPaginate" : true,
        "bJQueryUI" : true,
        "sPaginationType": "full_numbers",
        "aoColumns": [
             { "asSorting": [ "desc", "asc" ]},
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