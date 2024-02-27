<?php

$config = Config::getInstance();
if($config->filter('bc') == null) {
    $produto = $config->currentController->getProduto($config->filter("prod"));
} else {
    $produto = $config->currentController->getProdutoByCodigo($config->filter("prod"));
}

$type = $config->filter("type");

switch ($type){
    case "html":
        if(empty($produto->id)) echo "<h3>Produto Inexistente</h3>";
        else html($produto);
        break;
    default: 
        if(empty($produto->id)) $config->throwAjaxError("Produto Inexistente");
        else { 
            $loja_id = $config->filter('loja');
            if(!empty($loja_id)){
                include_once CONTROLLERS.'centralEstoque.php';
                $centralEstoque = new CentralEstoqueController();
                $estoque        = $centralEstoque->getEstoqueOfProduto($loja_id, $produto->id);
                $vars           = get_object_vars($estoque);
                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
                    $vars["valor"] = $config->maskDinheiro($produto->precoCompra);
                } else {
                    $vars["valor"] = "0,00";
                }
                $vars['descricao'] = $produto->descricao;
            } else {
                $vars = get_object_vars($produto);
                $vars["precoCompra"] = $config->maskDinheiro($vars["precoCompra"]);
                $vars["precoVenda"] = $config->maskDinheiro($vars["precoVenda"]);
                $vars["precoVendaMin"] = $config->maskDinheiro($vars["precoVendaMin"]);
                if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR) {
                    unset($vars["precoCompra"]);
                }
            }
			$vars['descricao'] = $vars['descricao'];
            $config->throwAjaxSuccess($vars);
        }
}

function html(Produto $produto){
    $config = Config::getInstance();
?>
<h3> Informações sobre o produto </h3>
<label> Código: <span> <?php echo $produto->codigo; ?> </span> </label>
<label> Categoria: <span> <?php echo ProdutoController::getTextualCategoria($produto->categoria); ?> </span> </label>
<br/>
<label> Descrição: <br/> <span class="h-separator"> &nbsp; </span> <span> <?php echo $produto->descricao; ?> </span> </label>
<br/>
<?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
<label> Preço de Compra: <span> R$ <?php echo $config->maskDinheiro($produto->precoCompra); ?> </span> </label>
<?php } ?>
<br/>
<label> Preço de Venda: <span> R$ <?php echo $config->maskDinheiro($produto->precoVenda); ?> </span> </label>
<br/>
<label> Preço Mínimo de Venda: <span> R$ <?php echo $config->maskDinheiro($produto->precoVendaMin); ?> </span> </label>
<br/>
<?php
$config = Config::getInstance();
$tipo = $config->currentController->getTipoProduto($produto->tipo);
$marca = $config->currentController->getMarca($produto->marca);
?>
<label> Tipo de Produto: <span> <?php echo $tipo->nome; ?> </span> </label>
<label> Marca: <span> <?php echo $marca->nome; ?> </span> </label>
<?php 
}
?>
