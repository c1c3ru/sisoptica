<?php

$config = Config::getInstance();

$loja = $config->currentController->getLoja($config->filter("loja"));

$type = $config->filter("type");

include_once CONTROLLERS."regiao.php";

switch($type){
    case "html": 
        if(empty($loja->id)) echo "<h3>Loja inexistente</h3>";
        else{ 
            $loja->telefones = $config->currentController->getAllTelefonesOfLoja($loja->id);
            html($loja);
        } 
        break;
    default: 
        
        if(empty($loja->id)) $config->throwAjaxError("Loja inexistente");
        
        $loja->telefones = $config->currentController->getAllTelefonesOfLoja($loja->id);

        $vars = get_object_vars($loja);
        
        $telefones = array();
        foreach($loja->telefones as $telefone){
            $telefones[] = $telefone->numero;
        }
        $vars["telefones"] = $telefones;

        $config->throwAjaxSuccess($vars);
}

function html(Loja $loja){
    $config = Config::getInstance();
?>
<h3> Informações sobre a loja </h3>
<label> Id: <span> <?php echo $loja->id; ?> </span> </label>
<label> Sigla: <span> <?php echo $loja->sigla; ?> </span> </label>
<br/>
<label> Telefones: 
    <?php
    $telefones = array();
    foreach($loja->telefones as $telefone){
        $telefones[] = $config->maskTelefone($telefone->numero);
    }
    echo "<span> ".  implode(", ", $telefones)." </span>";
?>
</label>
<br/><br/>
<h4> Informações de localização </h4>
<?php
$controller_regiao = new RegiaoController();
$cidade = $controller_regiao->getCidade($loja->cidade);
?>
<label> Cidade: <span> <?php echo $cidade->nome; ?> </span> </label>
<label> Bairro: <span> <?php echo $loja->bairro; ?> </span> </label>
<br/>
<label> Endereço: <span> <?php echo $loja->rua.", ".$loja->numero; ?> </span> </label>
<label> CEP: <span> <?php echo $config->maskCEP($loja->cep); ?> </span> </label>
<br/><br/>
<h4> Informações sobre regularização e gerência </h4>
<label> CNPJ: <span> <?php echo $config->maskCNPJ($loja->cnpj); ?> </span> </label>
<label> CGC: <span> <?php echo $loja->cgc; ?> </span> </label>
<br/>
<?php
    if(!empty ($loja->gerente)){
        include_once CONTROLLERS."funcionario.php";
        $controller_func = new FuncionarioController();
        $gerente = $controller_func->getFuncionario($loja->gerente);
        ?>
        <label> Gerente: <span> <?php echo $gerente->nome; ?> </span> </label>
        <?php
    } else {
    ?>
        <label> Gerente: <span> Sem Gerente </label> </label>
    <?php }
}
?>
