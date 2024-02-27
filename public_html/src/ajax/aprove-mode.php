<?php
$config = Config::getInstance();

$id_central = $config->filter('central');

$central = $config->currentController->getCentralEstoque($id_central);

if(empty($central->id)) { 
    $config->throwAjaxError('Operação de Central de Estoque inválida');
}

echo '<h3>Cheque os produtos e confirme a operação</h3>';

$produtos = $config->currentController->getProdutosOfCentralEstoque($id_central);

include_once CONTROLLERS.'produto.php';
$produto_control = new ProdutoController();

$can_see_value = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR;

?>
<form action="index.php?op=valida_central" method="post">
    <input type="hidden" name="central" value="<?php echo $id_central;?>" />
    <table style="text-align: center; width: 100%;">
        <thead> 
            <th class="same-info"> <label> Código </label> </th>
            <th class="same-info"> <label> Descrição </label> </th>
            <?php if($can_see_value) { ?>
            <th class="same-info"> <label> Valor </label> </th>
            <?php } ?>
            <th class="same-info"> <label> Qtd. na Saida </label> </th>
            <th class="same-info" style="width: 22.5%;"> <label> Qtd. na Entrada </label> </th>
        </thead>
        <tbody>
        <?php
        foreach ($produtos as $p){
            $produtoObj = $produto_control->getProduto($p->produto);
        ?>
            <tr> 
                <td class="any-info"><span><?php echo $produtoObj->codigo;    ?> </span></td>
                <td class="any-info"><span><?php echo $produtoObj->descricao; ?> </span></td>
                <?php if($can_see_value) { ?>
                <td class="any-info"><span><?php echo $config->maskDinheiro($p->valor); ?></span></td>
                <?php } ?>
                <td class="any-info"><span><?php echo $p->quantidadeEntrada; ?></span></td>
                <td class="any-info">
                    <input type="number" min="0" name="qtds_chegada[]" 
                           value="<?php echo $p->quantidadeEntrada; ?>" 
                           class="input text-input medium-input"/>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <p style="text-align: right" class="v-separator">
        <input type="submit" class="btn submit green-back" name="submit" value="Validar"/>
    </p>
</form>
<style>
.same-info{background: #EEE;border-radius:3px;}
.same-info label{margin: 5px;}
.any-info{border-bottom: white solid 1px;border-radius:5px;border-bottom: lightgray solid 1px;}
.any-info span{font-size: 10pt; color: #555;}
</style>