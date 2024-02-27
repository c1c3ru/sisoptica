<?php

$config = Config::getInstance();

$id_loja = $config->filter('loja');

if(empty($id_loja)) $config->throwAjaxError ('Loja vazia');

$is_fornecedor = $id_loja  == "null";

include_once CONTROLLERS.'produto.php';
$produto_control = new ProdutoController();

$res = array();

if(!$is_fornecedor) {
    $estoque = $config->currentController->getEstoqueInLoja($id_loja);
    foreach($estoque as $produtoLoja){
        $vars = get_object_vars($produtoLoja);
        unset($vars['loja']);
        unset($vars['produto']);
        $produto                = $produto_control->getProduto($produtoLoja->produto);
        $vars['id']             = $produto->id; 
        $vars['codigo']         = $produto->codigo;
        $vars['valor']          = (float)$produto->precoVenda;
        $vars['minimo']         = (float)$produto->precoVendaMin;
        $vars['descricao']      = $produto->descricao;
        $res[] = $vars;
    }
    $lentes = $produto_control->getAllLLentes();
    foreach($lentes as $lente){
        $vars['id']             = $lente->id; 
        $vars['codigo']         = $lente->codigo;
        $vars['valor']          = (float)$lente->precoVenda;
        $vars['minimo']         = (float)$lente->precoVendaMin;        
        $vars['descricao'] 	= $lente->descricao;
        $vars['quantidade']     = 'infinity';
        $res[] = $vars;
    }
} else {
    $produtos = $produto_control->getAllProdutos();
    foreach($produtos as $produto){
        $vars['id']             = $produto->id; 
        $vars['codigo']         = $produto->codigo;
        $vars['valor']          = (float)$produto->precoVenda;
        $vars['minimo']         = (float)$produto->precoVendaMin;        
    	$vars['descricao'] 	= $produto->descricao;
        $vars['quantidade']     = 'infinity';
        $res[] = $vars;
    }
}

$config->throwAjaxSuccess($res);

?>
