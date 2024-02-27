<?php

$config = Config::getInstance();

$controller = $config->currentController;

$localidade = $controller->getLocalidade($config->filter("loca"));

$type = $config->filter("type");

switch($type){
    case "html":
        if(empty($localidade->id))  echo "<h3>Localidade inexistente</h3>";
        else {
            html($localidade);
        }
        break;
    default:
        if(!empty($localidade->id)){
            $vars = get_object_vars($localidade);

            include_once CONTROLLERS."rota.php";
            include_once CONTROLLERS."regiao.php";

            $controller_rota = new RotaController();
            $controller_regiao = new RegiaoController();

            $rota = $controller_rota->getRota($localidade->rota);
            $regiao = $controller_regiao->getRegiao($rota->regiao);

            $vars["loja"] = $regiao->loja;
            $vars["cobrador"] = $regiao->cobrador;
            $vars["regiao"] = $regiao->id;
            $vars["rota"] = $rota->id;

            $config->throwAjaxSuccess($vars);
        }
        $config->throwAjaxError("Falha na solicitação. Localidade Inexistente.");    
        break;
}

function html(Localidade $localidade){
    
    include_once CONTROLLERS."rota.php";
    include_once CONTROLLERS."regiao.php";
    include_once CONTROLLERS."loja.php";
    
    $controller_rota = new RotaController();
    $controller_regiao = new RegiaoController();
    $controller_loja = new LojaController();
    
    $rota = $controller_rota->getRota($localidade->rota);
    $regiao = $controller_regiao->getRegiao($rota->regiao);
    $loja = $controller_loja->getLoja($regiao->loja);
    $cidade = $controller_regiao->getCidade($localidade->cidade);
?>
<h3> Informações sobre a localidade </h3>
<label> Id: <span> <?php echo $localidade->id; ?> </span> </label>
<label> Nome: <span> <?php echo $localidade->nome; ?> </span> </label>
<br/>
<label> Cidade: <span> <?php echo $cidade->nome; ?> </span> </label>
<label> Loja: <span> <?php echo $loja->sigla; ?> </span> </label>
<br/>
<label> Região: <span> <?php echo $regiao->nome; ?> </span> </label>
<label> Rota: <span> <?php echo $rota->nome; ?> </span> </label>
<label> Posição na Rota: <span> <?php echo $localidade->ordem; ?> </span> </label>
<?php } ?>
