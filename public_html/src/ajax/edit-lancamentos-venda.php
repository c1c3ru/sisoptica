<?php

$config = Config::getInstance();

$venda = $config->filter("vend");

if(empty($venda)) { 
    echo "<h3>Venda Inválida</h3>";
    exit(0);
}
$restante = $config->maskDinheiro($config->currentController->getRestanteOfVenda($venda));
echo "<script> var GLOBAL_VENDA = $venda; var RESTANTE_VENDA = \"$restante\";  </script>";

$controller = $config->currentController;

$parcelas       = $controller->getParcleasByVenda($venda);
$qtd_parcelas   = count($parcelas);

if(!$qtd_parcelas){
    echo "<h3> Sem Parcelas </h3>";
    exit(0);
}
$last_parcela = $parcelas[count($parcelas)-1];
if($last_parcela->valor < 0) define("DESCONTO", true);
else define("DESCONTO", false);

$func_op = !DESCONTO ? "without_desconto" : "with_desconto";

function with_desconto($param){ return ""; };
function without_desconto(Pagamento $p){
    $edit_img   = "<img src='".GRID_ICONS."editar.png' title='Editar Lançamento'/>";
    $link       = "<a href='javascript:;' onclick='editPagamento({$p->id})'>$edit_img</a>";
    $link_remove= removePagIconAction($p->id);
    if (!empty($link_remove)) {
        $link .= "&nbsp;&nbsp;&nbsp;";
        $link .= $link_remove;
    }
    return $link;
}
$isAdminOrDiretor = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ||
                    $_SESSION[SESSION_CARGO_FUNC] == CargoModel::COD_DIRETOR;
define("IS_DIRETOR_OR_ADMIN", $isAdminOrDiretor);
function removePagIconAction($id)
{
    if (IS_DIRETOR_OR_ADMIN) {
        $dele_img = "<img src='" . GRID_ICONS . "remover.png' title='Remover Lançamento'/>";
        return "<a href='javascript:;' onclick='removerPagamento({$id})'>$dele_img</a>";
    } else return "";
}
?>
<p class="title-form" style="font-size: 12pt;"> Lista de Pagamentos </p>
<table id="pagamentos-table" class="center">
    <thead> 
        <th> Parcela </th>
        <th> Valor </th>
        <th> Data </th>
        <th> Cobrador </th>
        <th> Prest. Conta </th>
        <th> </th>
    </thead>
    <tbody>
<?php
$total = 0;
foreach($parcelas as $parcela){
    
    $nome = $parcela->numero == 0 ? "Entrada" : "Parcela ".$parcela->numero;
    
    $pagamentos        = $controller->getPagamentosOfParcela($parcela, true);
    $valor             = $config->maskDinheiro($parcela->valor);
    $qtd_pagamentos    = count($pagamentos);
    $total            += $qtd_pagamentos;
    
    if($qtd_pagamentos) {
        $primeiro_pgto = $pagamentos[0];
        $hasQuitda      = "";
        $restanteParcela = 0;
        if($parcela->status) {
            $hasQuitda  = "has-quitada";
        } else {
            $restanteParcela = $parcela->valor - $config->currentController->getValorPagoOfParcela($parcela);
        }
        $restanteParcelaMoney = $config->maskDinheiro($restanteParcela);
        echo "<tr id='pgto-{$primeiro_pgto->id}' class='first-pgto' restante='".$restanteParcelaMoney."' father='pgto-{$primeiro_pgto->id}'>";
        echo "<td rowspan='$qtd_pagamentos' class='nome-parcela $hasQuitda'> <span>$nome</span> <br/>"; 
        echo "<span class='info-input'> Valor: <b>R$ $valor</b> </span> ";
        if($restanteParcela) echo "<br/> <span class='info-input restante'> Restante: <b>R$ ".$restanteParcelaMoney."</b></span>";
        else echo "<br/> <span class='info-input'> Quitada </span>";
        echo "</td>";
        echo "<td class='info-data'> R$ {$config->maskDinheiro($primeiro_pgto->valor)} </td>";
        echo "<td class='info-data'> {$config->maskData($primeiro_pgto->data)}</td>";
        echo "<td class='info-data cobrador-td'>".$primeiro_pgto->cobrador."</td>";
        $prestacao_p    = explode('|', $primeiro_pgto->prestacaoConta);
        if(count($prestacao_p) == 3 ){
            $prestacao_p[1] = $config->maskData($prestacao_p[1]);
            $prestacao_p[2] = $config->maskData($prestacao_p[2]);
            $primeiro_pgto->prestacaoConta = implode('-', $prestacao_p);
        }
        echo "<td class='info-data'cobrador-td'>".$primeiro_pgto->prestacaoConta."</td>";
        echo "<td> ".$func_op($primeiro_pgto)." </td>";
        echo "</tr>";
        for($i = 1; $i < $qtd_pagamentos; $i++){
            $pagamento = $pagamentos[$i];
            echo "<tr id='pgto-{$pagamento->id}' father='pgto-{$primeiro_pgto->id}'>";
            echo "<td class='info-data'> R$ {$config->maskDinheiro($pagamento->valor)} </td>";
            echo "<td class='info-data'> {$config->maskData($pagamento->data)}</td>";
            echo "<td class='info-data cobrador-td'>".$pagamento->cobrador."</td>";
            $prestacao_p    = explode('|', $pagamento->prestacaoConta);
            if(count($prestacao_p) == 3 ){
                $prestacao_p[1] = $config->maskData($prestacao_p[1]);
                $prestacao_p[2] = $config->maskData($prestacao_p[2]);
                $pagamento->prestacaoConta = implode('-', $prestacao_p);
            }
            echo "<td class='info-data'>".$pagamento->prestacaoConta."</td>";
            echo "<td> ".$func_op($pagamento)." </td>";
            echo "</tr>";
        }
    }
    
}

include_once CONTROLLERS.'venda.php';
$venda_controller = new VendaController();
$hasRene = $venda_controller->hasRenegociada($venda);
if(DESCONTO){
    echo "<tr class='desconto-class'>";
    echo "<td> Desconto </td>";
    $valor      = $last_parcela->valor * -1;
    echo "<td> {$config->maskDinheiro($valor)}</td>";
    echo "<td> {$config->maskData($last_parcela->validade)}</td>";
    echo "<td></td>";
    echo "<td></td>";
    $link = "";
    if(!$hasQuitda && $isAdminOrDiretor){
        $dele_img   = "<img src='".GRID_ICONS."remover.png' title='Remover Desconto'/>";
        $link       = "<a href='javascript:;' onclick='removerDesconto({$last_parcela->venda})'>$dele_img</a>";
    }
    echo "<td> $link </td>";
    echo "</tr>";
}
if(!$total) 
    echo "<tr> <td colspan='5' style='font-size:12pt;padding-top:10px;padding-bottom:10px;'> Sem Lançamentos </td> </tr>";
?>
    </tbody>
</table>
<style>
#pagamentos-table {text-align: center; font-size: 10pt; width: 100%; text-shadow: 1px 1px 1px white;}
#pagamentos-table thead th {background: lightgray;}
#pagamentos-table thead th:first-child{background: transparent;}
#pagamentos-table thead th:last-child{background: transparent;}
#pagamentos-table tbody .nome-parcela {background: #eee; text-shadow: 1px 1px 1px white;}
#pagamentos-table tbody .nome-parcela .info-input {color: green !important;}
#pagamentos-table tbody .nome-parcela .restante {color: brown !important;}
#pagamentos-table tbody tr td {border-bottom: lightgray solid 1px;}
#pagamentos-table tbody td:last-child {background: #EEE; padding-left: 10px; padding-right: 10px;}
#pagamentos-table tbody tr .info-data {}
#pagamentos-table tbody tr .cobrador-td  {}
#pagamentos-table tbody tr:hover .info-data {background: #eee;}
#pagamentos-table tbody .has-quitada {background: lightgreen;}
#pagamentos-table td, th{padding-top: 3px;padding-bottom: 3px; border-radius: 3px; line-height: 20px;}
#pagamentos-table .desconto-class td {background: rgba(100, 0,0,0.5);}
</style>
<script src="script/jquery.maskMoney.js"></script>
<script> 
expandViewDataMode();
<?php if(!DESCONTO){ ?>
$("#pagamentos-table tr").addClass("no-selection").dblclick(function(){
    var id = this.id.replace("pgto-", "");
    editPagamento(id);
});
function getMoneyInput(default_value){
    var input = document.createElement("input");
    input.type = "text";
    input.setAttribute("class","input text-input medium-input");
    $(input).maskMoney(MONEY_SETUP);
    $(input).val(floorMoney(default_value).replace(".",","));
    return input;
}
function getCobradorInput(default_value){
    var input = document.createElement("select");
    input.setAttribute("class", "input select-input gray-grad-back big-input");
<?php
include_once CONTROLLERS."funcionario.php";
$func_controller    = new FuncionarioController();
$cobradores         =  $func_controller->getAllCobradores();
foreach ($cobradores as $c) {
?>
    var option = document.createElement("option");
    option.value = "<?php echo $c->id;?>";
    option.innerHTML = "<?php echo utf8_encode($c->nome);?>";
    input.appendChild(option);    
<?php
}
?>    
    input.value = default_value;
    return input;
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

function getPrestacaoInput(){
    var input = document.createElement("select");
    input.setAttribute("class", "input select-input gray-grad-back big-input");
    return input;
}

function getDateInput(default_value){
    var input = document.createElement("input");
    input.type = "date";
    input.setAttribute("class","input text-input");
    input.value = default_value;
    return input;
}

var EDIT_ARR = {};

function editPagamento(pgto_id){
    var url = "ajax.php?code=3554&pgto="+pgto_id;
    get(url, function(data){
        if(data.code == "0"){
            var pgto = data.data;
            var tr = document.getElementById("pgto-"+pgto.id);
            var tdValor, tdData, tdCobrador, tdPrestacao;
            if(tr.className.indexOf("first-pgto") != -1){ 
                tdValor = tr.childNodes[1];
                tdData = tr.childNodes[2];
                tdCobrador = tr.childNodes[3];
                tdPrestacao = tr.childNodes[4];
            } else {
                tdValor = tr.childNodes[0];
                tdData = tr.childNodes[1];
                tdCobrador = tr.childNodes[2];
                tdPrestacao = tr.childNodes[3];
            }
            
            var inputValor = getMoneyInput(pgto.valor);
            inputValor.setAttribute("old-valor",pgto.valor);
            var inputData = getDateInput(pgto.data);
            var inputCobrador = getCobradorInput(pgto.cobrador);
            inputCobrador.setAttribute("id", 'cobrador-select-'+pgto_id);
            var inputPrestacao = getPrestacaoInput();
            inputPrestacao.setAttribute("id", 'prestacao-select-'+pgto_id);
            inputCobrador.onchange = function(){
                loadPrests(inputCobrador, inputPrestacao);
            };
            inputCobrador.onchange();
            
            tdValor.innerHTML = ""; tdValor.appendChild(inputValor);
            tdData.innerHTML = ""; tdData.appendChild(inputData);
            tdCobrador.innerHTML = ""; tdCobrador.appendChild(inputCobrador);
            tdPrestacao.innerHTML = ""; tdPrestacao.appendChild(inputPrestacao);
            
            var btn = document.createElement("button");
            btn.setAttribute("class", "btn submit green-back");
            btn.innerHTML = "Ok";
            btn.onclick = function(){ confirmChange(pgto.id); };
            
            var edit = tr.childNodes[tr.childNodes.length-1].innerHTML;
            
            tr.childNodes[tr.childNodes.length-1].innerHTML = "";
            tr.childNodes[tr.childNodes.length-1].appendChild(btn);
            
            EDIT_ARR[pgto.id] = {valor:inputValor, data: inputData, cobrador: inputCobrador, edit: edit, prest: inputPrestacao}
            
        } else badAlert(data.message);  
    });
}

function removerPagamento(pgto_id){
    var tr = document.getElementById("pgto-"+pgto_id);
    var trParcela = document.getElementById(tr.getAttribute("father"));
    
    var cobradorNome = tr.childNodes[tr.childNodes.length-3].innerHTML;
    var valor = tr.childNodes[tr.childNodes.length-4].innerHTML;
    var parcelaNome = trParcela.getElementsByClassName("nome-parcela")[0].getElementsByTagName('span')[0].innerHTML;
    
    if(!confirm("Deseja realmente deletar o lançamento de "+valor+" da "+parcelaNome+" com o "+cobradorNome+" como cobrador?")) return;
    var url = "ajax.php?code=9081";
    post(url, { "pgto" : pgto_id}, function(data){
        if(data.code == "0"){
            alert(data.message);
        } else badAlert(data.message);
    });
}

function confirmChange(idx){
    var inputs = EDIT_ARR[idx];
    var valor = floorMoney(inputs.valor.value);
    if(! parseInt(valor) ) return;
    var oldValor = parseFloat(floorMoney(inputs.valor.getAttribute("old-valor")));
    var restante = parseFloat(floorMoney(RESTANTE_VENDA));
    if(parseFloat(valor) > (restante+oldValor)){
        return alert("O valor não pode ser editado, pois ultrapassa o restante para quitar a divida");
    }
    var tr = document.getElementById(document.getElementById("pgto-"+idx).getAttribute("father"));
    restante = parseFloat(floorMoney(tr.getAttribute("restante")));
    var dif = parseFloat(valor) - oldValor;
    if( dif > restante && 
        !confirm("O valor ultrapassa o restante da parcela, o sistema irá redistribuir os valores com as parcelas não quitadas. Deseja Continuar?")){
        return ;
    }
    var pagamento = {
        pgto    : idx,
        valor   : valor,
        data    : inputs.data.value,
        cobrador: inputs.cobrador.value,
        prest   : inputs.prest.value
    }
    var date = pagamento.data.split("-").reverse().join("/");
    var cobradorNome = inputs.cobrador.options[inputs.cobrador.selectedIndex].innerHTML;
    var parcelaNome = tr.getElementsByClassName("nome-parcela")[0].getElementsByTagName('span')[0].innerHTML;
    var prestNome = inputs.prest.options[inputs.prest.selectedIndex].innerHTML;
    if(!confirm("Confirme as informação do lançamento:\nParcela: "+parcelaNome+"\nValor: R$ "+pagamento.valor+"\nData: "+date+"\nCobrador: "+cobradorNome+"\nPrest. Conta: "+prestNome)) return;
    POST_PARAM_AUX = pagamento;
    ACTION_AFTER = function(){
        var url = "ajax.php?code=4537";
        post(url, POST_PARAM_AUX, function(data){
            if(data.code == "0"){
                alert(data.message);
            } else badAlert(data.message);
        });
    }
    
    ACTION_AFTER_NOT_CONFIRM = function(){openViewDataMode('ajax.php?code=8004&vend='+GLOBAL_VENDA)};
    
    openPasswordGerenteNeed();
    
}
<?php } else { ?>
function removerDesconto(id_venda){
    if(!confirm("Se remover o desconto, os lançamentos feitos, junto ao desconto, para quitar a dívida serão cancelados. Deseja continuar?")) return ;
    POST_PARAM_AUX = { vend : id_venda};
    var url = "ajax.php?code=9082";
    post(url, POST_PARAM_AUX, function(data){
        if(data.code == "0"){
            alert(data.message);
        } else badAlert(data.message);
    });
}
<?php } ?>
</script>
