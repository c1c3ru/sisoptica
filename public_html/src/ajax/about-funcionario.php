<?php

$config = Config::getInstance();

$funcionario = $config->currentController->getFuncionario($config->filter("func"));

$type = $config->filter("type");

switch ($type){
    case "html": 
        if(empty($funcionario->id)) echo "<h3>Funcionário Inexistente</h3>";
        else{ 
            $funcionario->telefones = $config->currentController->getAllTelefonesOfFuncionario($funcionario->id);
            html($funcionario);    
        }
        break;
    default :
        if(empty($funcionario->id)) $config->throwAjaxError("Funcionário Inexistente");
        else{ 
            $funcionario->telefones = $config->currentController->getAllTelefonesOfFuncionario($funcionario->id);
            $vars = get_object_vars($funcionario);
            $telefones = array();
            foreach($funcionario->telefones as $telefone){
                $telefones[] = $telefone->numero;
            }
            $vars["telefones"] = $telefones;
            $config->throwAjaxSuccess($vars);            
        };
}

function html(Funcionario $funcionario){
    $config = Config::getInstance();
    include_once CONTROLLERS."loja.php";
    include_once CONTROLLERS."regiao.php";
    $controller_loja = new LojaController();
    $controller_regiao = new RegiaoController();
?>   
<h3> Informações sobre o <?php echo $funcionario->nome; ?> </h3>
<label> Id: <span> <?php echo $funcionario->id; ?> </span> </label>
<label> Login no Sistema: <span> <?php echo $funcionario->login; ?> </span> </label>
<label> Status: <span><?php echo $funcionario->status? "Ativado" : "Desativado" ;?></span></label>
<br/>
<label> Data de Nascimento: <span> <?php echo $config->maskData($funcionario->nascimento); ?> </span> </label>
<label> Email: <span> <?php echo $funcionario->email; ?> </span> </label>
<br/>
<label> Data de Admissao: <span> <?php echo $config->maskData($funcionario->admissao); ?> </span> </label>
<label> Data de Demissao: <span> <?php echo $config->maskData($funcionario->demissao); ?> </span> </label>
<br/>
<label> Telefones: 
<?php
    $telefones = array();
    foreach($funcionario->telefones as $telefone){
        $telefones[] = $config->maskTelefone($telefone->numero);
    }
    echo "<span> ".  implode(", ", $telefones)." </span>";
?>
</label>
<br/>
<?php $cidade_func = $controller_regiao->getCidade($funcionario->cidade); ?>
<label> Cidade: <span> <?php echo $cidade_func->nome;?> </span> </label>
<br/>
<label> Endereço: <span> <?php echo $funcionario->rua.", ".$funcionario->numero; ?> </span> </label>
<label> Bairro: <span> <?php echo $funcionario->bairro; ?> </span> </label>
<br/>
<label> CEP: <span> <?php echo $config->maskCEP($funcionario->cep); ?> </span> </label>
<label> Referência: <span> <?php echo $funcionario->referencia; ?> </span> </label>
<br/>
<label> CPF: <span> <?php echo $config->maskCPF($funcionario->cpf); ?> </span> </label>
<label> RG: <span> <?php echo $funcionario->rg; ?> </span> </label>
<label> CPT: <span> <?php echo $funcionario->cpt; ?> </span> </label>
<br/><br/>
<h4> Dados Bancários </h4>
<label> Banco: <span> <?php echo $funcionario->banco; ?> </span> </label>
<label> Agência: <span> <?php echo $funcionario->agencia; ?> </span> </label>
<label> Conta: <span> <?php echo $funcionario->conta; ?> </span> </label>
<br/><br/>
<h4> Dados da loja onde ele trabalha </h4>
<?php
$loja = $controller_loja->getLoja($funcionario->loja);
$cidade = $controller_regiao->getCidade($loja->cidade);
?>
<label> Loja: <span> <?php echo $loja->sigla; ?> </span> </label>
<label> Cidade: <span> <?php echo $cidade->nome; ?> </span> </label>
<br/>
<label> Rua: <span> <?php echo $loja->rua; ?> </span> </label>
<label> Bairro: <span> <?php echo $loja->bairro; ?> </span> </label>
<label> CEP: <span> <?php echo $loja->cep; ?> </span> </label>
<br/><br/>
<h4> Referências ao cargo</h4>
<?php
$config = Config::getInstance();
$cargo = $config->currentController->getCargo($funcionario->cargo);
$perfil = $config->currentController->getPerfil($funcionario->perfil);
?>
<label> Cargo: <span> <?php echo $cargo->nome; ?> </span> </label>
<label> Perfil: <span> <?php echo $perfil->nome; ?> </span> </label>
<?php    
}

?>
