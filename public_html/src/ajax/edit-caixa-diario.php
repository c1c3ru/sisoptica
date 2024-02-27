<?php
$config = Config::getInstance();
$caixa_id = $config->filter('caixa');

include_once CONTROLLERS.'funcionario.php';

if($_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR &&
   $_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR ){ 

    include_once CONTROLLERS.'caixa.php';
    $caixaController    = new CaixaController();
    $caixa              = $caixaController->getCaixaAberto();

    if($caixa->id != $caixa_id){
        exit('Caixa Inválido');
    }

} else {
    define("NO_CAIXA", $caixa_id);
}

echo "<div class='mini-content'>";
    include_once FORMS.'caix-dia.php';
echo "</div><br/>";
echo "<div class='mini-content list-mini-content' >";
    include_once LISTS.'caix-dia.php';
echo "</div><br/>";
?>
<style> 
#view-data-back .content {
    line-height: normal;
}
#view-data-back .list-mini-content {
    line-height: 30px;
}
</style>
<script> 
    adjustsMoneyFields();
    expandViewDataMode();
</script>