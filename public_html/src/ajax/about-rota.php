<?php

$config = Config::getInstance();

$controller = $config->currentController;

$rota = $controller->getRota($config->filter("rota"));

$type = $config->filter("type");

switch ($type){
    case "html":
        if(!empty($rota->id)) html($rota);
        else echo "<h3>Rota Inválida</h3>";
        break;
    default:
        if(!empty($rota->id)) {
            $vars = get_object_vars($rota);
            include_once CONTROLLERS."regiao.php";
            $controller_regiao = new RegiaoController();
            $regiao = $controller_regiao->getRegiao($rota->regiao);
            $vars["loja"] = $regiao->loja;
            $vars["cobrador"] = $regiao->cobrador;
            $vars["regiao"] = $regiao->id;
            $config->throwAjaxSuccess($vars);    
        }
        else $config->throwAjaxError("Rota inválida");
        
}

function html(Rota $rota){
?>
<h3> Informações sobre o produto </h3>
<label> Nome: <span> <?php echo $rota->nome; ?> </span> </label>
<br/>
<?php
include_once CONTROLLERS."regiao.php";
$regiao_controller = new RegiaoController();
$isWithForeignValues = true;
$regiao = $regiao_controller->getRegiao($rota->regiao, $isWithForeignValues);
?>
<label> Região: <span> <?php echo $regiao->nome; ?> </span> </label>
<br/>
<label> Cobrador: <span> <?php echo $regiao->cobrador; ?> </span> </label>
<br/>
<?php    
}

?>
