<?php
$config = Config::getInstance();

$id_venda   = $config->filter("venda");
if(empty($id_venda)) $config->throwAjaxError("Venda Inválida");

$venda      = $config->currentController->getVenda($id_venda, true);
if(empty($venda->id)) $config->throwAjaxError("Venda Inválida");

include_once CONTROLLERS."parcela.php";
$parcela_controller = new ParcelaController();
$valor_atual        = $config->currentController->getValorOfVenda($venda->id);
$restante           = $parcela_controller->getRestanteOfVenda($venda->id);
$venda_fechada      = $venda->status == VendaModel::STATUS_QUITADA || $restante == 0;
?>
<label> Cliente: <span><?php echo $venda->cliente;?></span></label>
<br/>
<label> Valor Venda: <span> R$ <?php echo $config->maskDinheiro($valor_atual);?></span></label>
<label> Pago: <span> R$ <?php echo $config->maskDinheiro($valor_atual - $restante);?></span></label>
<fieldset> 
    <legend>&nbsp;Renegociação&nbsp;</legend>
    <?php if(!$venda_fechada){ ?>
    <form action="?op=rene_venda" method="post" id="form-rene" 
          <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_OPERADOR){ ?>
          onsubmit="return checkRequests() && submitRene();"
          <?php } else { ?>
          onsubmit="return checkRequests() && confirm('Deseja realmente renegociar venda?');"
          <?php } ?>
    >
        <input type="hidden" value="<?php echo $venda->id;?>" name="venda"/>
        <table class="center"> 
            <tr> 
                <td> <label> Saldo: </label> </td>
                <td> <label><span> R$ <?php echo $config->maskDinheiro($restante);?></span></label></td>
            </tr>
            <tr> 
                <td> 
                    <span>(</span>
                    <label><input type="radio" name="tipo" value="m" checked> Multa </label>
                    <label style="margin-right: 0;"><input type="radio" name="tipo" value="d"> Desconto </label>
                    <span>)</span>
                    <label> : </label>
                </td>
                <td> 
                    <input type="text" id="multaoudesconto" name="multaoudesconto" 
                    title="Ao ser registrada uma Multa, o valor aqui inserido, será positivo. Se for um Desconto, será negativo. O sistema controlá isso." 
                    onblur="calculeValorParcelas()" class="input text-input medium-input" />
                </td>
            </tr>
            <tr> 
                <td> <label> Entrada: </label></td>
                <td> 
                    <input type="text" id="entrada-renegociacao" name="entrada" onblur="calculeValorParcelas()" class="input text-input medium-input" />
                </td>
            </tr>
            <tr>  
                <td> <label> Quantidade de Parcelas: </label></td>
                <td> 
                    <input type="number" class="input text-input medium-input" onchange="calculeValorParcelas()" 
                    name="qtd-parcelas" id="qtd-parcelas" required min="1" value="1"/>
                </td>
            </tr>
            <tr>  
                <td> <label> Valor das Parcelas:</label> </td>
                <td> <label><span id="view-valor-parcelas" title="(Restante + Multa) / Quantidade de Parcelas"> R$ 0,00 </span> </label> </td>
            </tr>
            <tr> 
                <td> <label> Data Próxima Parcelas: </label> </td>
                <td> <input type="date" required name="data-parcela" id="data-parcela" class="input text-input" onblur="checkDataParcelas()"/> </td>
            </tr>
            <tr id='tr-prestacao-conta'>
                <td> <label>Prest.Conta:</label> </td>
                <td>
                    <select name="prestacao-conta-entrada" id="prestacao-conta-entrada-venda"
                        class="input select-input gray-grad-back big-input">	
                        <option value="">Selecione Prest. Conta</option>
                                    
                        <?php 
                        include_once CONTROLLERS.'prestacaoConta.php';
                        $prestacaoController    = new PrestacaoContaController();
                        $t_venda                = strtotime(date("Y-m-d")); 
                        $venda_origin           = $config->currentController->getVenda($id_venda);
                        $prestacoes             = $prestacaoController->getAllPrestacaoConta(
                            true, $venda_origin->loja, PrestacaoContaModel::STATUS_ABERTA
                        );
                        
                        foreach($prestacoes as $p){
                            $t_prest_i = strtotime($p->dtInicial);
                            $t_prest_f = strtotime($p->dtFinal);
                            if($t_venda < $t_prest_i && $t_venda > $t_prest_f){
                                continue;
                            }
                            echo '<option value=\''.$p->id.'\'>'.($p->cobrador . ' ('. $p->seq.')').'</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <input type="submit" class="btn submit green-back" name="submit" value="Renegociar" style="float: right"/>
    </form>
    <?php } else { ?>
    <label> Essa venda já foi quitada </label>
    <?php } ?>
</fieldset>
<style> 
form table tr td:first-child { text-align: right; }
</style>
<script src="script/jquery.maskMoney.js"></script>
<script>

var RESTANTE = <?php echo $restante; ?> ;

$(function() {
    $('#multaoudesconto').maskMoney(MONEY_SETUP);
    $('#entrada-renegociacao').maskMoney(MONEY_SETUP);
});

function checkRequests() {
    return checkDataParcelas();
}

function calculeValorParcelas(){

    var qtd_parcelas = parseInt(document.getElementById('qtd-parcelas').value);
    var multaoudesconto = parseFloat(floorMoney(document.getElementById('multaoudesconto').value));
    var entrada = parseFloat(floorMoney(document.getElementById('entrada-renegociacao').value));

    if(document.getElementsByName('tipo')[1].checked) multaoudesconto *= -1;

    var valor = (RESTANTE + multaoudesconto - entrada) / qtd_parcelas;

    $('#prestacao-conta-entrada-venda').attr('required', entrada > 0);
    $('#tr-prestacao-conta').css('display',entrada>0?'table-row':'none');

    document.getElementById('view-valor-parcelas').innerHTML = toMoney(valor);
}

calculeValorParcelas();
document.getElementsByName('tipo')[1].onchange = calculeValorParcelas;
document.getElementsByName('tipo')[0].onchange = calculeValorParcelas;

<?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_OPERADOR){ ?>

function submitRene() {

    if(!confirm('Deseja realmente renegociar venda?')){ return false; }

    POST_PARAM_AUX = document.getElementById('form-rene');

    ACTION_AFTER = function () {
        var strs = new Array();
        for(var i = 0; i < POST_PARAM_AUX.elements.length; i++){
            if(POST_PARAM_AUX.elements[i].type == "radio" && 
               !POST_PARAM_AUX.elements[i].checked ){
               continue;
            } 
            strs.push(POST_PARAM_AUX.elements[i].name+'='+POST_PARAM_AUX.elements[i].value);
        }
        window.location = POST_PARAM_AUX.action+'&'+strs.join('&');
    };

    ACTION_AFTER_NOT_CONFIRM = function(){
        openViewDataMode('ajax.php?code=9650&venda=<?php echo $id_venda;?>');
    };

    openPasswordGerenteNeed();

    return false;
}

<?php } ?>

function checkDataParcelas() {
    var inputDtParcela = $("#data-parcela");
    var dtParcelaTime = new Date(inputDtParcela.val()).getTime();
    var nowTime = new Date().getTime();
    var diff = dtParcelaTime - nowTime;
    if (diff > MAIN_LIMIT_DATE) {
        var invalid_message = "Data das próximas parcelas não pode ser mais que 60 dias";
        inputDtParcela.addClass("invalid-input").attr("title", invalid_message);
        return false;
    } else {
        inputDtParcela.removeClass("invalid-input").attr("title", "");
        return true;
    }
}

</script>