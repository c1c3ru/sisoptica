<?php

$config = Config::getInstance();

$cliente = $config->currentController->getCliente($config->filter("clie"));

$type = $config->filter("type");

include_once CONTROLLERS."regiao.php";
include_once CONTROLLERS."localidade.php";

switch ($type){
    case "html": 
        if(empty($cliente->id)) echo "<h3>Cliente inexistente</h3>";
        else {
            $cliente->telefones = $config->currentController->getAllTelefonesOfCliente($cliente->id);
            html($cliente);
        } 
        break;
    default: 
        if(empty($cliente->id)) $config->throwAjaxError("Cliente inexistente");
        
        $cliente->telefones = $config->currentController->getAllTelefonesOfCliente($cliente->id);

        $vars = get_object_vars($cliente);
        
        $controller = new LocalidadeController();
        $localidade = $controller->getLocalidade($cliente->localidade);
        $controller = new RegiaoController();
        $cidade = $controller->getCidade($localidade->cidade);
        
        $vars["cidade"] = $cidade->id;
        $vars["casaPropria"] = $cliente->casaPropria ? "1" : "";
        
        $telefones = array();
        foreach($cliente->telefones as $telefone){
            $telefones[] = $telefone->numero;
        }
        
        $vars["telefones"] = $telefones;
        
        $config->throwAjaxSuccess($vars);
}

function html(Cliente $cliente){
    $config = Config::getInstance();
?>
<h3> Informações sobre <?php echo $cliente->nome; ?> </h3>
<label> Nome: <span> <?php echo $cliente->nome; ?> </span> </label>
<label> Data de Nascimento: <span> <?php echo $config->maskData($cliente->nascimento); ?> </span> </label>
<br/>
<label> Id: <span> <?php echo $cliente->id; ?> </span> </label> 
<label> Apelido: <span> <?php echo $cliente->apelido; ?> </span> </label> 
<br/>
<label> RG: <span> <?php echo $cliente->rg; ?> </span> </label>
<label> Órgão Emissor: <span> <?php echo $cliente->orgaoEmissor; ?> </span> </label>
<br/>
<label> CPF: <span> <?php echo $config->maskCPF($cliente->cpf); ?> </span> </label>
<br/> 
<label> Telefones: 
<?php
    $telefones = array();
    foreach($cliente->telefones as $telefone){
        $telefones[] = $config->maskTelefone($telefone->numero);
    }
    echo "<span> ".  implode(", ", $telefones)." </span>";
?>
</label>
<br/><br/>
<h4> Parentesco </h4>
<label> Conjugue: <span> <?php echo $cliente->conjugue; ?> </span> </label>
<br/>
<label> Nome da Pai: <span> <?php echo $cliente->nomePai; ?> </span> </label>
<label> Nome da Mãe: <span> <?php echo $cliente->nomeMae; ?> </span> </label>
<br/><br/>
<h4> Sobre localização e moradia </h4>
<label> Endereco: <span> <?php echo $cliente->endereco; ?> </span> </label>
<label> Numero: <span> <?php echo $cliente->numero; ?> </span> </label>
<br/>
<label> Bairro: <span> <?php echo $cliente->bairro; ?> </span> </label>
<label> Referência: <span> <?php echo $cliente->referencia; ?> </span> </label>
<br/>
<label> Casa Própria: <span> <?php echo $cliente->casaPropria ? "Sim" : "Não"; ?> </span> </label>
<label> Tempo de Casa Própria: <span> <?php echo $cliente->tempoCasaPropria; ?> </span> </label>
<br/>
<label> Observação: <span> <?php echo $cliente->observacao?> </span> </label>
<h4> Outras </h4>
<label> Renda Mensal: <span> R$ <?php echo $cliente->rendaMensal?> </span> </label>
<br/><br/>
<h4> Localidade </h4>
<?php    

$controller = new LocalidadeController();
$localidade = $controller->getLocalidade($cliente->localidade);
$controller = new RegiaoController();
$cidade = $controller->getCidade($localidade->cidade);
$estado = $controller->getEstado($cidade->estado);
?>
<label> Nome: <span> <?php echo $localidade->nome; ?> </span> </label>
<label> Cidade: <span> <?php echo $cidade->nome; ?> </span> </label>
<label> Estado: <span> <?php echo $estado->sigla; ?> </span> </label>
<?php
}
?>
