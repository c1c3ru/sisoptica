<?php

$config = Config::getInstance();

$id_central = $config->filter('central');

$type = $config->filter('type');

switch ($type) {
    case "html":
        if(empty($id_central)){
            exit('<h4>Central não informada</h4>');
        }

        $central = $config->currentController->getCentralEstoque($id_central);

        if(empty($central->id)){
            exit('<h4>Central inválida</h4>');
        }
        
        html($central);
        
    break;
    default:
        if(empty($id_central)){
            $config->throwAjaxError('Central não informada');
        }

        $central = $config->currentController->getCentralEstoque($id_central);

        if(empty($central->id)){
            $config->throwAjaxError('Central inválida');
        }

        $vars = get_object_vars($central);
        $vars["valor"] = $config->maskDinheiro($vars["valor"]);

        include_once CONTROLLERS.'produto.php';
        $produtoController  = new ProdutoController();
        $vars["produtos"]   = array();
        $produtos           = $config->currentController->getProdutosOfCentralEstoque($central->id);
        foreach($produtos as $p){
            $produto        = $produtoController->getProduto($p->produto);
            $vars_produto   = array(
                "codigo" => $produto->codigo,
                "quantidadeEntrada" => $p->quantidadeEntrada,
                "quantidadeSaida" => $p->quantidadeSaida,
                "descricao" => $produto->descricao
            );
            if(mb_detect_encoding($vars_produto['descricao']) != 'utf-8'){
                $vars_produto['descricao'] = utf8_encode($vars_produto['descricao']);
            }
            if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ||
               $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_GERENTE ){
                $vars_produto["valor"]  = $config->maskDinheiro($p->valor);
            }
            $vars["produtos"][] = $vars_produto;
        }

        $config->throwAjaxSuccess($vars);

    break;
}

function html(CentralEstoque $central){
    global $config;
    include_once CONTROLLERS.'produto.php';
    include_once CONTROLLERS.'loja.php';
    $produto_control    = new ProdutoController();
    $loja_control       = new LojaController();
    if(empty($central->lojaOrigem)) $central->lojaOrigem = "FORNECEDOR";
    else $central->lojaOrigem = $loja_control->getLoja($central->lojaOrigem)->sigla;
    $central->lojaDestino = $loja_control->getLoja($central->lojaDestino)->sigla;
    ?>
<h3> Informações sobre operação na cental de estoque</h3>
<label> Origem: <span><?php echo $central->lojaOrigem; ?></span></label>
<label> Destino: <span><?php echo $central->lojaDestino; ?></span></label>
<label> Data: <span><?php echo $config->maskData($central->data); ?></span></label>
<label> Valor: <span><?php echo $config->maskDinheiro($central->valor); ?></span></label>
<br/>
<label> Status: <span id="status"><?php echo $config->currentController->getTextualStatus($central->status);?></span></label>
<br/>
<label> Observação: <span><?php echo $central->observacao;?></span></label>
<br/>
<h4> Lista de Produtos </h4>
<table style="text-align: center; width: 100%;">
    <thead> 
        <th class="same-info"> <label> Código </label> </th>
        <th class="same-info"> <label> Descrição </label> </th>
        <th class="same-info"> <label> Valor Uni. </label> </th>
        <th class="same-info"> <label> Qtd. na Saida </label> </th>
        <th class="same-info"> <label> Qtd. na Entrada </label> </th>
        <th class="same-info"> <label> Valor</label> </th>
    </thead>
    <tbody>
    <?php
    $produtos = $config->currentController->getProdutosOfCentralEstoque($central->id);
    $sum_qtd_entrada = $sum_qtd_saida = $sum_valor = 0;
    foreach ($produtos as $p){
        $produtoObj = $produto_control->getProduto($p->produto);
        $sum_qtd_entrada+= $p->quantidadeEntrada;
        $sum_qtd_saida  += $p->quantidadeSaida;
        $sum_valor      += $p->valor;
        $class_error    = "";
        if($p->quantidadeEntrada != $p->quantidadeSaida && $central->status == CentralEstoqueModel::STATUS_PROBLEMAS){
            $class_error = "error_row";
        }
    ?>
        <tr class="<?php echo $class_error;?>"> 
            <td class="any-info"><span><?php echo $produtoObj->codigo;    ?> </span></td>
            <td class="any-info"><span><?php echo $produtoObj->descricao; ?> </span></td>
            <td class="any-info"><span>R$ <?php echo $config->maskDinheiro($p->valor); ?></span></td>
            <td class="any-info enfase"><span><?php echo $p->quantidadeEntrada; ?></span></td>
            <td class="any-info enfase"><span><?php echo $p->quantidadeSaida; ?></td>
            <td class="any-info"><span>R$ <?php echo $config->maskDinheiro($p->quantidadeEntrada * $p->valor); ?></span></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<br/>
<label> Qtd. Total. Entrada: <span><?php echo $sum_qtd_entrada; ?></span></label>
<label> Qtd. Total. Saida: <span><?php echo $sum_qtd_saida; ?></span></label>
<label> Valor Total: <span><?php echo $config->maskDinheiro($sum_valor); ?></span></label>
<style>
.same-info{background: #EEE;border-radius:3px;}
.same-info label{margin: 5px;}
.any-info{border-bottom: white solid 1px;border-radius:5px;border-bottom: lightgray solid 1px;}
.any-info span{font-size: 10pt; color: #555;}
.error_row{background: salmon;}
.error_row .enfase {background: brown;}
.error_row .enfase span {color: white;}
    <?php 
    switch ($central->status) {
        case CentralEstoqueModel::STATUS_OK:
            echo "#status{padding: 10px;text-shadow: none;;background: green;color: white}";
            break;
        case CentralEstoqueModel::STATUS_PENDENTE:
            echo "#status{padding: 10px;text-shadow: none;background: lightyellow;color: goldenrod}";
            break;
        case CentralEstoqueModel::STATUS_PROBLEMAS:
            echo "#status{padding: 10px;text-shadow: none;background: brown;color: white}";
            break;
    }
    ?>
</style>
<?php    
}
?>
