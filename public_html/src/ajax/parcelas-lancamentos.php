<?php
$config = Config::getInstance();

$venda = $config->filter("vend");

if(is_null($venda)){
    echo "<h3>Venda Inválida</h3>";
    exit(0);
}

$isDiretor = $_SESSION[SESSION_CARGO_FUNC] == CargoModel::COD_DIRETOR;

$parcelas = $config->currentController->getParcleasByVenda($venda, ParcelaController::TODAS_AS_PARCELAS, '', '', true);

$restanteTotal  = $config->currentController->getRestanteOfVenda($venda);

$restanteCalc   = $restanteTotal;

?>
<table id="parcelas-table" class="center" 
restante="<?php echo $restanteTotal?>" restante-calc="<?php echo $restanteCalc?>"> 
    <thead> 
        <th> <a id="btn-todas-carne" href="javascript:;" onclick="turnInCarne()"> Todas em carnê</a> </th>
        <th>Valor</th>
        <th>Valor Pago</th>
        <th>Vencimento</th>
        <th style="line-height:initial;"> 
            Remarcação 
            <br/>
            <a href="javascript:;" onclick="remarcar_selecionadas()">Remarcar Selecionadas</a>
        </th>
        <th>Lançar Pagamentos</th>
        <th> </th>
    </thead>
    <tbody> 
<?php
$flagLancamento = false;
$flagDarBaixaOpcao = false;

include_once CONTROLLERS."funcionario.php";
include_once CONTROLLERS."venda.php";

$func_controller    = new FuncionarioController();
$venda_controller   = new VendaController();
$byLoja = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ? false : $_SESSION[SESSION_LOJA_FUNC];
$cobradores = $func_controller->getAllCobradores($byLoja);
$id_Cobrador = $venda_controller->getCobradorByVenda($venda);

$select_cobrador    = "<select class='input select-input gray-grad-back medium-input' id='cobrador-pagamento' ";
$select_cobrador   .= "onchange=\"loadPrests(this, document.getElementById('prest-pagamento'))\">";
foreach($cobradores  as $cobrador){
    $select_cobrador .= "<option value='{$cobrador->id}' ";
    if($cobrador->id == $id_Cobrador){
        $select_cobrador .= " selected ";
    }
    $select_cobrador .= "> ".utf8_encode($cobrador->nome)."</option>";
}
$select_cobrador .= "</select>"; 

$select_prest = "<select class='input select-input gray-grad-back medium-input' id='prest-pagamento'></select>";

$desconto_class = "";

$hasBoleto = false;

foreach($parcelas as $parcela){
    if ($parcela->numero == 0) $parcelaNome = "Entrada"; 
    else if($parcela->valor < 0) { 
        $parcelaNome = "Desconto";
        $parcela->valor = -1 * $parcela->valor;
        $desconto_class = "desconto-class";
    } else { 
        $parcelaNome    = "Parcela {$parcela->numero}";
        if($parcela->porBoleto) {
            $hasBoleto = true;
            $parcelaNome   .= "<p class='por-boleto'>(Boleto)</p>";
        } else {
            $parcelaNome   .= "<p class='por-boleto'>(Carnê)</p>";
        }
    }
    $valor      = $config->maskDinheiro($parcela->valor);
    $japago     = $config->currentController->getValorPagoOfParcela($parcela);
    $valor_pago = $config->maskDinheiro($japago);
    $validade   = $config->maskData($parcela->validade);
    if(!$parcela->status && !$flagLancamento && !$parcela->cancelada){

        $flagLancamento     = true;
        $flagDarBaixaOpcao  = true;
        $restante = $config->maskDinheiro($parcela->valor - $japago);

        $lancamento = "<div class='lancamento-space'>".
        $lancamento .= 'Cobrador: ' . $select_cobrador . "<br/>";
        $lancamento .= 'Prest. Conta: '.$select_prest . '<br/>';
        $lancamento .= "Recebido: <input type='text' class=\"input text-input small-input\" ";
        $lancamento .= " id=\"preco-lancamento\" restante='$restante' ><br/>";
        $lancamento .= "Data do Pagamento:";
        $lancamento .= "<input type=\"date\" class=\"input text-input\" id=\"data-pagamento-input\" />";
        $lancamento .= '</div>';

    } else {
        $flagDarBaixaOpcao  = false;
        if(!$parcela->cancelada) {
            $lancamento = $parcela->status ? "Quitada" : "À Quitar" ;
        } else {
            $lancamento = '<b style=\'color:brown\'>Cancelada</b>';
        }
    }

    $img_view       = "<img src='".GRID_ICONS."visualizar.png'>";
    $img_baixa      = "<img src='".GRID_ICONS."baixa.png'>";
    $img_remarcar   = "<img src='".GRID_ICONS."calendar.png'>";
    
    $ver_historico  = "<a href='javascript:;' onclick='viewHistory({$parcela->numero}, {$parcela->venda})' title='Visualizar Histórico'>$img_view</a>";
    $dar_baixa      = "<a href='javascript:;' onclick='darBaixa({$parcela->numero}, {$parcela->venda})' title='Dar Baixa'>$img_baixa</a>";
    $remarcar       = "<a href='javascript:;' onclick=\"remarcar({$parcela->numero}, '{$parcela->remarcacao}')\" title='Remarcar data para cobrança'>$img_remarcar</a>";
?>
        <tr class="<?php if($parcela->status){ echo "has-quitada"; } echo " ".$desconto_class;?>">    
            <td class="parcela-nome"> <?php echo $parcelaNome; ?> </td>
            <td> <?php echo "R$ $valor"; ?> </td>
            <td> <?php echo "R$ $valor_pago"; ?> </td>
            <td> <?php echo $validade; ?> </td>
            <td class="remarcar-colum">
            <?php if(!$parcela->status && !$parcela->cancelada) { ?>
                <input type="checkbox" class="check-remarcar" pnum="<?php echo $parcela->numero;?>"/>
            <?php } ?>
            <?php
            if (!empty($parcela->remarcacao) && !$parcela->cancelada)
                echo '<span class="span-label">' . $config->maskData($parcela->remarcacao) . '</span>';
            ?>
            <?php if(!$parcela->status  && !$parcela->cancelada) echo $remarcar; ?>
            </td>
            <td class="lancar-pagamentos-colum"> <?php echo $lancamento; ?> </td>
            <td <?php if($flagDarBaixaOpcao){ echo "id='dar-baixa-td'";} ?> > 
                    <?php
                    if(!$parcela->cancelada) { 
                        if($flagDarBaixaOpcao) {
                            echo $dar_baixa;
                        } 
                        if($flagDarBaixaOpcao || $parcela->status) {
                            echo $ver_historico;
                        }
                    }
                    ?> 
            </td>
        </tr>    
<?php  } ?>        
    </tbody>
</table>
<?php if($flagLancamento){ ?>
<div style="float: left;width: 49%;"> 
<fieldset>
        <legend>
            <a href="javascript:;;" onclick="$('#lancamento-total').slideToggle('fast')"> Quitar todas as parcelas restantes </a>
        </legend>
        <div class="hidden" id="lancamento-total">
            <?php ?>
            <label>
                Cobrador:
                <?php
                $n_select = str_replace( "cobrador-pagamento", "cobrador-pagamento-total", $select_cobrador);
                $n_select = str_replace( "prest-pagamento", "prest-pagamento-total", $n_select);
                echo $n_select;
                ?>
            </label>
            <label>
                Prest. Conta:
                <?php
                $m_select = str_replace( "prest-pagamento", "prest-pagamento-total", $select_prest);
                echo $m_select;
                ?>
            </label>
            <br/>
            <label> 
                Data: <input type="date" id="data-pagamento-todas" class="input text-input" 
                        value="<?php echo date("Y-m-d");?>" />
            </label>
            <span class="h-separator"> &nbsp; </span>
            <label> Dívida:
                <span> 
                    R$ <?php echo $config->maskDinheiro($restanteTotal); ?> 
                </span>
            </label>
            <span class="h-separator"> &nbsp; </span>
            <label title="Soma das parcelas válidas para o desconto"> Soma das Parcelas : 
                <span> 
                    R$ <?php echo $config->maskDinheiro($restanteCalc); ?> 
                </span>
            </label>
            <br/>
            <label> Desconto Máx.:
                <span> R$
                    <?php 
                        $max = $isDiretor ? $restanteCalc : $restanteCalc * 0.2;
                        echo $config->maskDinheiro($max);
                        $max_value = number_format($max, 2, '.', '');
                    ?>
                </span> 
            </label>
            <span class="h-separator"> &nbsp; </span>
            <label> Desconto: 
                <input class="input text-input small-input" type="text" id="desconto-lancamento-total" 
                       max="<?php echo $config->maskDinheiro($max_value);?>"
                       value="0,00"
                       onblur="recalcPercent()" />
                <span id="desc-percent" title="Por centagem das somas da parcela"></span>
            </label>
            <br/>
            <label>
                Valor à pagar: <span id="valor-a-pagar-label"> </span>
            </label>
            <br/>
            <button onclick="darBaixaParcelasRestantes(<?php echo $venda; ?>);" style="float: right;"
                    title="Dar baixa em todas as restantes" class="btn submit green-back">
                Quitar &nbsp;
                <img src="<?php echo GRID_ICONS."baixa.png";?>"/>
            </button>
        </div>
</fieldset>
</div>
<?php } ?>
<div style="float: right;width: 49%;"> 
<fieldset>
    <legend>&nbsp;histórico de pagamento&nbsp;</legend>
    <table id="history-parcela" class="hidden"  style="text-align: center; font-size: 10pt;"> 
        <thead> 
            <th> Pagamento </th>
            <th> Data </th>
            <th> Cobrador </th>
            <th> Usuário </th>
        </thead>
        <tbody></tbody>
    </table>
</fieldset>
</div>
<style>
#history-parcela {width: 100%;}
#history-parcela thead th{background: #EEE;}
#history-parcela tbody td{border-bottom:lightgray solid 1px;}
#parcelas-table {text-align: center; font-size: 10pt; width: 85%;}
#parcelas-table thead th {background: lightgray;}
#parcelas-table thead th:first-child{background: transparent;}
#parcelas-table thead th:last-child{background: transparent;}
#parcelas-table tr td{border-bottom:lightgray solid 1px;}
#parcelas-table tbody tr td:first-child {background: #eee;line-height: 15px;}
#parcelas-table td, th{padding: 5px; padding-left: 10px; padding-right: 10px; border-radius: 3px;}
#parcelas-table .has-quitada td { background: lightgreen; }
#parcelas-table .desconto-class td {background: rgba(100, 0,0,0.5);}
#parcelas-table tr td:last-child {
    background: #eee; 
    border-bottom: lightgreen solid 2px;
    border-top: lightgray solid 2px;
    width: 15%;
}
.lancar-pagamentos-colum { max-width: 40%; }
.lancar-pagamentos-colum input{ margin: 5pt 0 5pt 0; }
.lancamento-space, .remarcar-colum { text-align: right; }
.por-boleto{margin: 0; margin-top:2.5px; color: gray; font-size: 8pt;}
.medium-input { width: 200px !important; }
.small-input { width: 100px !important; }
#dar-baixa-td a { margin-right: 20px }
</style>
<script src="script/jquery.maskMoney.js"> </script>
<script src="script/mask.js"> </script>
<script> 
expandViewDataMode();
$("#preco-lancamento").ready(function(){
    $("#preco-lancamento").val( $("#preco-lancamento").attr("restante") ); 
});
<?php
if(!empty($desconto_class)){
?>
var desconto_tr = document.getElementsByClassName("desconto-class")[0];
var tds = desconto_tr.getElementsByTagName("td");
tds[2].innerHTML = "";
tds[4].innerHTML = "Cedido";
<?php } ?>
function viewHistory(numero, venda){
    openLoadingInElement("#history-parcela tbody td");
    var url = "ajax.php?code=7601&numero="+numero+"&venda="+venda;
    get(url, function(data){
        if(data.code == "0"){
            var pagamentos = data.data;
            if(pagamentos.length == 0){
                $("#history-parcela").slideUp('fast');
            } else {
                $("#history-parcela").slideDown('fast');
            }
            $("#history-parcela tbody").html("");
            for(var i = 0; i < pagamentos.length; i++){
                var pagamento = pagamentos[i];
                var row = "<tr>";
                row += "<td>R$ "+pagamento.valor+"</td>";
                row += "<td>"+pagamento.data+"</td>";
                row += "<td>"+pagamento.cobrador+"</td>";
                row += "<td>"+pagamento.autor+"</td>";
                row += "</tr>";
                $("#history-parcela tbody").append(row);
            }

            var content = $("#view-data-back .content")
            content.scrollTop(content.height())

        } else badAlert(data.message);
    });
}
function remarcar(parc,actual){
    ACTION_AFTER = function(){
        openViewDataMode('ajax.php?code=9923&vend=<?php echo $venda;?>');
    };
    ACTION_AFTER_NOT_CONFIRM = ACTION_AFTER;
    openViewDataMode('ajax.php?code=9733&nparc='+parc+'&vparc=<?php echo $venda;?>&actual='+actual);
}
function remarcar_selecionadas(){
    var ids = new Array();
    var checks = document.getElementsByClassName('check-remarcar');
    for(var i = 0, l = checks.length; i < l; i++){
        if(checks[i].checked){
            ids.push('nparc[]=' + checks[i].getAttribute("pnum"));
        }
    }
    if(!ids.length) {
        alert('Sem seleção');
        return;
    }
    ACTION_AFTER = function(){
        openViewDataMode('ajax.php?code=9923&vend=<?php echo $venda;?>');
    };
    ACTION_AFTER_NOT_CONFIRM = ACTION_AFTER;
    openViewDataMode('ajax.php?code=9733&'+ids.join("&")+'&vparc=<?php echo $venda;?>');
}
$('.check-remarcar').change(function(){
    $(this).parent().css('background-color', this.checked ? '#EEE' : 'initial'); 
});
<?php if($flagLancamento){ ?>
function darBaixa(numero, venda){
    var inputLanc = document.getElementById("preco-lancamento");
    var valor = floorMoney(inputLanc.value);
    if(valor == "" || valor == 0) return;
    valor = parseFloat(valor);
    var restanteTotal = parseFloat(document.getElementById("parcelas-table").getAttribute("restante"));
    if(restanteTotal < valor){
        return alert("O valor não pode ser maior do que o restante para quitar a dívida.");
    }
    var restante = parseFloat(floorMoney(inputLanc.getAttribute("restante")));
    if( valor > restante){
        if(!confirm("O valor a ser lançado é maior do que o restante para quitar a parcela, deseja redistribuir o valor para as outras parcelas?")){
            return;
        }
    }
    var selectCobrador = document.getElementById("cobrador-pagamento");
    var selectPrest = document.getElementById('prest-pagamento'); 
    var data = $("#data-pagamento-input").val();
    if(data == "") return alert("Informe a data do pagamento");
    var postObj = { numero: numero, 
                    venda: venda, 
                    data: data,
                    prest: selectPrest.value,
                    cobrador : selectCobrador.value,
                    valor: floorMoney(valor) };
    var cobradorNome = selectCobrador.options[selectCobrador.selectedIndex].innerHTML;
    var prestacaoSeq = selectPrest.options[selectPrest.selectedIndex].innerHTML;
    var parcelaNome = document.querySelector("#parcelas-table tbody tr:not(.has-quitada) td:first-child").textContent;
    var data_l = data.split("-").reverse().join("/");
    if(!confirm("Confirme as informação do lançamento:\nValor: R$ "+postObj.valor+"\nData Pagamento: "+data_l+"\nParcela: "+parcelaNome+"\nVenda: "+postObj.venda+"\nCobrador: "+cobradorNome+"\nPrest. Conta: "+prestacaoSeq)) return;
                
    var url = "ajax.php?code=4559";
    var oldHtml = $('#dar-baixa-td').html();
    openLoadingInElement('#dar-baixa-td');
    
    ACTION_AFTER = function(){
        openViewDataMode('ajax.php?code=9923&vend='+venda)
    };
    
    post(url, postObj, function(data){
       if(data.code == "0") {
           ACTION_AFTER();
           ACTION_AFTER = function(){};
           alert(data.message);
       } else { 
           badAlert(data.message);
           $('#dar-baixa-td').html(oldHtml);
       }
    });
}
$(function(){
    $("#preco-lancamento").maskMoney(MONEY_SETUP);
    $("#desconto-lancamento-total").maskMoney(MONEY_SETUP);
    recalcPercent();
    loadPrests(document.getElementById("cobrador-pagamento"), document.getElementById('prest-pagamento'));
});
function recalcPercent(){
    var desconto = parseFloat(floorMoney(document.getElementById("desconto-lancamento-total").value));
    var restante = parseFloat(document.getElementById("parcelas-table").getAttribute("restante-calc"));
    var restanteTotal = parseFloat(document.getElementById("parcelas-table").getAttribute("restante"));
    document.getElementById("valor-a-pagar-label").innerHTML = toMoney(restanteTotal - desconto);
    if(restante != 0){
        var percent = (desconto * 100) / restante;
        document.getElementById("desc-percent").innerHTML = "("+floorMoney(percent)+"%)";
    } else {
        document.getElementById("desc-percent").innerHTML = "(0%)"
    }
}
function darBaixaParcelasRestantes(venda){
    var desconto = document.getElementById("desconto-lancamento-total");
    if(parseFloat(floorMoney(desconto.value)) > parseFloat(floorMoney(desconto.getAttribute("max")))){
        return alert("Desconto não pode ser maior do que o máximo!");
    }
    if(!confirm("Realmente deseja dar baixa em todas as parcelas?")) return ;
   
    var prest = $('#prest-pagamento-total').val();
   
    POST_PARAM_AUX =  {
        venda : venda,
        cobrador : $("#cobrador-pagamento-total").val(),
        prest : prest ? prest : '',
        desconto : floorMoney(desconto.value),
        data: $("#data-pagamento-todas").val()
    };
    
    ACTION_AFTER = function(){ 
        var url = "ajax.php?code=8018";
        post(url, POST_PARAM_AUX, function(data){
           if(data.code == "0") {
               alert(data.message);
           } else badAlert(data.message);
        });
    };
    ACTION_AFTER_NOT_CONFIRM = function(){openViewDataMode('ajax.php?code=9923&vend='+venda)};
    openPasswordGerenteNeed();
}
function loadPrests(selectSource, selectTarget){
    if(!selectSource.value) return ;
    get('ajax.php?code=9117&cobrador='+selectSource.value, function(data){
        if(data.code == '0'){
            selectTarget.innerHTML = '';
            for(var i = 0, l = data.data.length; i < l; i++){
                var option = document.createElement('option');
                option.value = data.data[i].id;
                option.innerHTML = data.data[i].seq+'-'+maskData(data.data[i].dtInicial)+'-'+maskData(data.data[i].dtFinal);
                selectTarget.appendChild(option);
            }
        } else badAlert(data.message);
    });
}
function maskData(strData){
    return strData.split('-').reverse().join('/');
}
<?php } ?>
<?php if($hasBoleto) { ?>
function turnInCarne() {
    var venda = <?php echo $venda;?>;
    if(!confirm("Certeza que deseja alterar todas as parcelas para modo de cobrança por cobrador?")) return;
    ACTION_AFTER_NOT_CONFIRM = function(){openViewDataMode('ajax.php?code=9923&vend='+venda)};
    ACTION_AFTER = function() {
        get("ajax.php?code=1987&venda="+venda,function(data){
            if(data.code == 0){
                alert(data.message);
            } else badAlert(data.message);
        });
    };
    openPasswordGerenteNeed();
}
<?php } else { ?>
    $('#btn-todas-carne').hide();
<?php }?>
</script>