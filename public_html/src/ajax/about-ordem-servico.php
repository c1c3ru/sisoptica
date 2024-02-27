<?php

$config = Config::getInstance();

$controller = $config->currentController;

$ordem = $controller->getOrdemServico( $config->filter("orde") );

$type = $config->filter("type");

switch ($type){
    case "html" :
        if(empty($ordem->id)) echo "<h3> Ordem de Serviço Inexistente</h3>"; 
        else html($ordem);
        break;
    default:
        if(!empty($ordem->id)){
            $vars = get_object_vars($ordem);
            $vars["valor"] = $config->maskDinheiro($vars["valor"]);
            if( $_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR && 
                $_SESSION[SESSION_PERFIL_FUNC] != PERFIL_GERENTE ) {
                unset($vars['valor']);
            }
            $config->throwAjaxSuccess($vars);
        }
        $config->throwAjaxError("Ordem de Serviço Inválida");
        break;
}

function html(OrdemServico $ordem){
    $config = Config::getInstance();
    
    include_once CONTROLLERS."laboratorio.php";
    include_once CONTROLLERS."loja.php";
    include_once CONTROLLERS."funcionario.php";
    
    $labo_control = new LaboratorioController();
    $loja_control = new LojaController();
    $func_control = new FuncionarioController();
    
    $laboratorio    = $labo_control->getLaboratorio($ordem->laboratorio);
    $loja           = $loja_control->getLoja($ordem->loja);
    $autor          = $func_control->getFuncionario($ordem->autor);
?>
<h4> Informações sobre ordem de serviço 
    <?php if($ordem->cancelada){
        echo " (Cancelada)";
    }?>
</h4>
<label> Número OS: <span> <?php echo $ordem->numero; ?> </span> </label>
<label> Laboratório: <span> <?php echo $laboratorio->nome; ?> </span> </label>
<label> Venda: <span><?php echo empty($ordem->venda)? "Sem Venda" : $ordem->venda; ?></span></label>
<br/>
<label> Data Envio: <span><?php echo $config->maskData($ordem->dataEnvioLab); ?></label>
<label> Data Recebimento: <span><?php echo $config->maskData($ordem->dataRecebimentoLab); ?></label>
<br/>
<label> Loja: <span> <?php echo $loja->sigla ; ?> </span> </label>
<label> Armação da Loja: <span> <?php echo $ordem->armacaoLoja ? "Sim" : "Não" ; ?></span> </label>
<br/>
<?php if( $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR || $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_GERENTE ) { ?>
<label> Valor: <span> R$ <?php echo $config->maskDinheiro($ordem->valor);?> </span></label>
<?php } ?>
<label> Autor: <span> <?php echo $autor->nome;?> </span></label>
<?php } ?>
