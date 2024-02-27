<?php
$config = Config::getInstance();

$venda = $config->currentController->getVenda($config->filter("vend"));

$type = $config->filter("type");

$resumida = $config->filter('resum') != null;

switch ($type){
    case "html":
        if(empty($venda->id)) 
            echo "<h4> Venda Inexistente </h4>";
        else 
            html($venda);
        break;
    default:
        if(empty($venda->id)) $config->throwAjaxError("Venda Inexistente");
        
        if(!$resumida) {
            //Checkinf if venda is editable
            include_once CONTROLLERS."parcela.php";
            $parc_controller    = new ParcelaController();
            if(!$parc_controller->isEditableVenda($venda->id))
            $config->throwAjaxError("Venda não pode ser editada");
        }
        
        $vars = get_object_vars($venda);

        unset($vars["valor"]);
        
        if(!$resumida) {
            $venda_pai = $config->currentController->getPrimaryVenda($venda->id);
            //Captando informações sobre a consulta dessa venda
            include_once CONTROLLERS."consulta.php";
            $consulta_controller = new ConsultaController();
            $consulta = $consulta_controller->getConsultaByVenda($venda_pai->id);
            $vars["consulta"] = get_object_vars($consulta);
        }
        
        //Captando informações sobre o cliente dessa venda
        include_once CONTROLLERS."cliente.php";
        $cliente_controller = new ClienteController();
        $cliente = $cliente_controller->getCliente($venda->cliente, true);
        if(empty($cliente->id)) 
            $vars["cliente"] = array();
        else 
            $vars["cliente"] = array("nome" => $cliente->nome, "id" => $cliente->id,
                                     "localidade" => $cliente->localidade, 
                                     "endereco" => $cliente->endereco.", nº: ".$cliente->numero);
        
        if(!$resumida) {
            //Captando informações sobre a ordem de serviço dessa venda
            include_once CONTROLLERS."ordemServico.php";
            $os_controller = new OrdemServicoController();
            $os = $os_controller->getOrdemServico($venda_pai->os);
            $vars["os"] = array("numero" => $os->numero, "id" => $os->id );

            //Captando informações sobre os produtos dessa venda
            $produtos           = $config->currentController->getProdutosVendaOfVenda($venda_pai->id);
            foreach ($produtos as $produtoVenda) {
                $vars["produtos"][] = array("id" => $produtoVenda->produto, 
                                            "valor_vendido" => $produtoVenda->valor);  
            }

            //Captando informações sobre o parcelamento dessa venda
            $entrada            = $parc_controller->getParcela("0", $venda->id);
            $parcelas           = $parc_controller->getParcleasByVenda($venda->id);
            $qtd_parcelas       = count($parcelas) - 1;

            if(empty($entrada->venda)){
                $vars["parcelamento"]["com_entrada"]        = false;
                $vars["parcelamento"]["p_parcela"]["data"]  = $parcelas[0]->validade;
                $vars["parcelamento"]["p_parcela"]["valor"] = $parcelas[0]->valor;
            } else {
                $vars["parcelamento"]["com_entrada"]    = true;
                $vars["parcelamento"]["valor_entrada"]  = $parcelas[0]->valor;
            }
            $vars["parcelamento"]["data_demais"]  = $parcelas[1]->validade;
            $vars["parcelamento"]["q_parcelas"]   = $qtd_parcelas;
        }
        
        $config->throwAjaxSuccess($vars);
}

function html(Venda $venda){
$config = Config::getInstance();
include_once CONTROLLERS."funcionario.php";
include_once CONTROLLERS."loja.php";
include_once CONTROLLERS."cliente.php";
include_once CONTROLLERS."ordemServico.php";
include_once CONTROLLERS."consulta.php";
include_once CONTROLLERS."localidade.php";
include_once CONTROLLERS."produto.php";
include_once CONTROLLERS."parcela.php";
include_once CONTROLLERS."equipe.php";

$func_control       = new FuncionarioController();
$loj_control        = new LojaController();
$clie_control       = new ClienteController();
$ordem_control      = new OrdemServicoController();
$consul_control     = new ConsultaController();
$localidade_control = new LocalidadeController();
$produto_control    = new ProdutoController();
$parcela_control    = new ParcelaController();
$equipe_control     = new EquipeController();

$venda_pai          = $config->currentController->getPrimaryVenda($venda->id);

$vendedor           = $func_control->getFuncionario($venda->vendedor);
$agente             = $func_control->getFuncionario($venda->agenteVendas);
$loja               = $loj_control->getLoja($venda->loja);
$cliente            = $clie_control->getCliente($venda->cliente);
$localidade_clie    = $localidade_control->getLocalidade($cliente->localidade, true);
$ordem              = $ordem_control->getOrdemServico($venda_pai->os, true);
$consulta           = $consul_control->getConsultaByVenda($venda_pai->id, true);

if (!empty($venda->equipe) && !empty($venda->liderEquipe)) {
    $equipe = $equipe_control->getEquipe($venda->equipe);
    $lider_equipe = $func_control->getFuncionario($venda->liderEquipe);
}

$venda->valor       = $config->currentController->getValorOfVenda($venda->id); 

$venda->produtos    = $config->currentController->getProdutosVendaOfVenda($venda->id);

$parcelas           = $parcela_control->getParcleasByVenda($venda->id, ParcelaController::TODAS_AS_PARCELAS, '', '', true);

$restante           = $parcela_control->getRestanteOfVenda($venda->id);

$com_entrada        = $parcelas[0]->numero == "0";

?>
<h4> Informações sobre a venda </h4>
<h5> Dados da Venda Nº <?php echo $venda->id;?> 
    (<?php echo $config->currentController->getStatusName("{$venda->status}");?>) 
</h5>
<div class="separted-info">
    <label> Cliente: <span> <?php echo $cliente->nome; ?></span></label>
    <br/>
    <label> Cidade: <span> <?php echo $localidade_clie->cidade; ?></span></label>
    <br/>
    <label> Localidade: <span> <?php echo $localidade_clie->nome; ?></span></label>
    <br/>
    <label> Endereço: <span> <?php echo $cliente->endereco.", nº ".$cliente->numero; ?></span></label>
</div>
<label> Data da Venda: <span> <?php echo $config->maskData($venda->dataVenda); ?> </span></label>
<label> Previsão de Entrega: <span> <?php echo $config->maskData($venda->previsaoEntrega); ?> </span></label>
<label> Data da Entrega: <span> <?php echo $config->maskData($venda->dataEntrega); ?> </span></label>
<br/>
<div class="separted-info">
    <label> Loja: <span> <?php echo $loja->sigla; ?></span></label>
    <label> Cidade: <span> <?php echo $loja->cidade; ?></span></label>
    <br/>
    <label> Equipe: <span> <?php echo (!empty($venda->equipe))? $equipe->nome : "Equipe não associada"; ?> </span></label>
    <label> Líder: <span> <?php echo (!empty($venda->liderEquipe))? $lider_equipe->nome : "Líder de equipe não associado"; ?> </span></label>
    <br/>
    <label> Vendedor: <span> <?php echo $vendedor->nome; ?></span></label>
    <label> Agente de Vendas: <span> <?php echo $agente->nome; ?></span></label>
</div> 
<br/>
<div class="separted-info">
    <label> Ordem de Servico: <span> <?php echo $ordem->numero ; ?></span></label>
    <br/>
    <label> Envio: <span> <?php echo $config->maskData($ordem->dataEnvioLab); ?></span></label>
    <label> Recebimento: <span> <?php echo $config->maskData($ordem->dataRecebimentoLab); ?></span></label>
    <br/>
    <label> Laboratório: <span> <?php echo $ordem->laboratorio; ?></span></label>
</div>
<br/>
<div class="separted-info">
    <label> Produtos: </label>
    <table style="text-align: center; width: 100%;">
        <thead> 
            <th class="same-info"> <label> Código </label> </th>
            <th class="same-info"> <label> Descrição </label> </th>
            <th class="same-info"> <label> Preço Venda </label> </th>
            <th class="same-info"> <label> Vendido Por </label> </th>
        </thead>
        <tbody>
        <?php
        foreach ($venda->produtos as $produtoVenda){
            $produtoObj = $produto_control->getProduto($produtoVenda->produto);
        ?>
            <tr> 
                <td class="any-info"><span><?php echo $produtoObj->codigo;    ?> </span></td>
                <td class="any-info"><span><?php echo $produtoObj->descricao; ?> </span></td>
                <td class="any-info"><span><?php echo $config->maskDinheiro($produtoObj->precoVenda); ?></span></td>
                <td class="any-info"><span><?php echo $config->maskDinheiro($produtoVenda->valor);    ?></span></td>
            </tr>
        <?php } ?>
        <?php 
        if($venda->vendaAntiga){
            echo "<tr><td colspan='4' class='any-info old-products'> <span><b>Produtos da Venda Original</b></span> </td></tr>";
            $produtos_antigos = $config->currentController->getProdutosVendaOfVenda($venda_pai->id); 
            foreach ($produtos_antigos as $produtoVenda){
                $produtoObj = $produto_control->getProduto($produtoVenda->produto);
        ?>
            <tr> 
                <td class="any-info"><span><?php echo $produtoObj->codigo;    ?> </span></td>
                <td class="any-info"><span><?php echo $produtoObj->descricao; ?> </span></td>
                <td class="any-info"><span><?php echo $config->maskDinheiro($produtoObj->precoVenda); ?></span></td>
                <td class="any-info"><span><?php echo $config->maskDinheiro($produtoVenda->valor);    ?></span></td>
            </tr>
        <?php }
        } ?>
        </tbody>
    </table>
    <br/>
    <?php
    $pagas = 0;
    $withDesconto = false;
    foreach($parcelas as $p) { 
        if($p->status == true) $pagas++; 
        if($p->valor < 0) { $withDesconto = -1 * $p->valor; }
    }
    $qtdPareclas  = count($parcelas) - 1;
    ?>
    <label> <?php echo $com_entrada ? "Entrada":"Primeira Parcela";?>: <span> <?php echo "R$ ".$config->maskDinheiro($parcelas[0]->valor); ?> </span> </label>
    <?php if($qtdPareclas > 0){ ?>
    <label> Parcelas: <span> <?php echo $qtdPareclas." de R$ ".$config->maskDinheiro($parcelas[1]->valor); ?> </span> </label>
    <?php } ?>
    <label> Parcelas Pagas <?php echo $com_entrada ? "(c/ entrada)":"";?>: <span> <?php echo $pagas;  ?> </span></label>
    <br/>
    <label> Valor Total: <span>  R$ <?php echo $config->maskDinheiro($venda->valor); ?></span></label>
    <label> Restante à ser pago:   <span>  R$ <?php echo $config->maskDinheiro($restante); ?> </span> </label>
    <?php if($withDesconto){ ?>
    <label> Desconto:   <span>  R$ <?php echo $config->maskDinheiro($withDesconto); ?> </span> </label>
    <?php } ?>
</div> 
<br/>
<h4> Dados da Consulta </h4>
<label> Nome Paciente:<span> <?php echo $consulta->nomePaciente;?></span></label>
<br/>
<div class="separted-info">
    <table style="text-align: center;width: 100%;"> 
        <tr>
            <td></td>
            <td class="same-info"><label>Esférico</label></td>
            <td class="same-info"><label>Cilíndrico</label></td>
            <td class="same-info"><label>Eixo</label></td>
            <td class="same-info"><label>D.N.P</label></td>
            <td class="any-info same-info"><label>DP:</label><span><?php echo $consulta->dp; ?></span></td>
            <td class="any-info same-info"><label>Cor</label><span><?php echo $consulta->cor; ?></span></td>
        </tr>
        <tr>
            <td class="same-info"><label>O.D</label></td>
            <td class="any-info"><span><?php echo $consulta->esfericoOD;?></span></td>
            <td class="any-info"><span><?php echo $consulta->cilindricoOD;?></span></td>
            <td class="any-info"><span><?php echo $consulta->eixoOD;?></span></td>
            <td class="any-info"><span><?php echo $consulta->dnpOD;?></span></td>
            <td class="any-info same-info"><label>Adição:</label><span><?php echo $consulta->adicao; ?></span></td>
            <td class="any-info same-info"><label>Altura:</label><span><?php echo $consulta->altura; ?></span></td>
        </tr>
        <tr>
            <td class="same-info"><label>O.E</label></td>
            <td class="any-info"><span><?php echo $consulta->esfericoOE;?></span></td>
            <td class="any-info"><span><?php echo $consulta->cilindricoOE;?></span></td>
            <td class="any-info"><span><?php echo $consulta->eixoOE;?></span></td>
            <td class="any-info"><span><?php echo $consulta->dnpOE;?></span></td>
            <td class="any-info same-info"><label>Lente:</label><span><?php echo $consulta->lente; ?></span></td>
            <td class="any-info same-info"><label>C.O:</label><span><?php echo $consulta->co; ?></span></td>
        </tr>
    </table>
</div>
<label> Oculista: <span> <?php echo $consulta->oculista;?> </span></label>
<br/>
<label> Observação: <span> <?php echo $consulta->observacao;?> </span></label>
<br/>
<style>
.same-info{background: white;border-radius:3px;}
.same-info label{margin: 5px;}
.any-info{border-bottom: white solid 1px;border-radius:5px;}
.any-info span{font-size: 10pt; color: #555;}
.old-products{background: white;border-bottom: lightgray solid 1px;text-align: right;padding-right: 10px;}
td.any-info.same-info{text-align: left;}
td.any-info.same-info label{margin-right: 2px;}
</style>
<?php } ?>
