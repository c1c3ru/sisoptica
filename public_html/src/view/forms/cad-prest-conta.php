<?php if( in_array($_SESSION[SESSION_PERFIL_FUNC], array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE)) ){ ?>
<form action="?op=add_prest_conta" method="post" id="form-cad-prest-conta" onsubmit="return checkSubmit();">
    <div class="tool-bar-form" form="form-cad-prest-conta">
        <div onclick="openAddSpaceForm(this);addItem();" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this);onCancel();" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset style="display: inline-block;float: left;width: 47%;">
        <legend>&nbsp;Prestaçao de Conta&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <input type="hidden" name="itens-prestacao" id="itens-prestacao" value=""/>
        <label> Loja: 
            <select name="loja" class="input select-input gray-grad-back" 
            id="loja-prest-conta" onchange="loadCobradores(this.value)" required>	
                <option value=""> Selecione uma loja </option>	
                <?php
                include_once CONTROLLERS."loja.php";
                $loja_controller = new LojaController();
                $isWithFoerignValues = false;
                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
                    $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                else
                    $lojas = array(
                        $loja_controller->getLoja( $_SESSION[SESSION_LOJA_FUNC], $isWithFoerignValues)
                    );
                foreach($lojas as $loja){ ?>
                    <option value="<?php echo $loja->id; ?>"
                    <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                    ><?php echo $loja->sigla; ?></option>
                <?php } ?>
            </select>
        </label>
        <span class="h-separator">&nbsp;</span>
        <label> Cobrador:
            <select name="cobrador" id="cobrador-prest-conta" onchange=""
            class="input select-input gray-grad-back medium-input" required>
                <option value=""> Selecione cobrador </option>
            </select>
        </label>
        <p class="v-separator">&nbsp;</p>
        <label>
            Data Inicial:
            <input type="date" class="input text-input" name="data-inicial" required id="data-inicial-prestacao" />
        </label>
        <span class="h-separator">&nbsp;</span>
        <label>
            Data Finall:
            <input type="date" class="input text-input" name="data-final" required id="data-final-prestacao" />
        </label>
        <p class="v-separator">&nbsp;</p>
        <p class="title-form">
            Itens da prestação
            <a href="javascript:;" onclick="addItem()" style="margin-left: 10px;"> 
                <img src="images/tool-icons/add.png">
            </a>
        </p>
        <div id='itens-prestacao-space'></div>
        <p style="text-align: right;">
            <label style="float: left;margin-top:10px;"> 
                <input type="checkbox" name="status" class="input notchecked" id="status-prestacao" value="1"/> FECHAR PRESTAÇÃO 
            </label>
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>
    <fieldset style="display: inline-block;float: right;width: 47%;">
        <legend>Lançamentos vinculados</legend>
        <table id="pagamentos-table">
            <thead>
                <tr>
                    <th>Venda</th>
                    <th>Nº Parcela</th>
                    <th>Valor</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody id="tbody-pagamentos"></tbody>
        </table>
        <p style="font-size: 10pt; padding: 5px; margin-top: 5px; border-top:lightgray solid 1px;"> TOTAL LANÇAMENTOS: <b id="total-pagamentos"> R$ 0,00 </b> </p>
    </fieldset>
    </div>
</form>
<script src="script/mask.js"></script>
<script src="script/jquery.maskMoney.js"></script>
<style>
#pagamentos-table {text-align: center; font-size: 10pt; width: 100%; max-height: 251.5px;overflow-y: auto;}
#pagamentos-table thead th {background: lightgray;}
#pagamentos-table tr td{border-bottom:lightgray solid 1px;}
#pagamentos-table td, th{padding: 5px; padding-left: 10px; padding-right: 10px; border-radius: 3px;}    
</style>
<script>
var itens = new Array();
<?php 
include_once CONTROLLERS.'tipoPagamento.php';
$controller    = new TipoPagamentoController();
$tipos         = $controller->getAllTiposPagamento();
foreach ($tipos as $tipo) {
    echo 'itens.push({\'id\':'.$tipo->id.', \'nome\':\''.addslashes($tipo->nome).'\'});';
}
?>
$(function(){
    loadCobradores($('#loja-prest-conta').val());
});
function onCancel(){
    $('#itens-prestacao-space').html('');
    $('#loja-prest-conta').attr('disabled',false);
    $('#cobrador-prest-conta').attr('disabled',false);
    $('#tbody-pagamentos').html("");
    $('#total-pagamentos').html("R$ 0,00");
}
function checkSubmit(){
    var dtInicial = new Date(document.getElementById('data-inicial-prestacao').value).getTime();
    var dtFinal   = new Date(document.getElementById('data-final-prestacao').value).getTime(); 
    if(dtInicial > dtFinal){
        alert("Data final deve ser maior do que a inicial");
        return false;
    }
    var itens = document.getElementsByClassName('item-row');
    var status = document.getElementById('status-prestacao');
    if(status.checked && !itens.length) {
        alert("Sem itens para fechar a prestação");
        return false;
    }
    var itensArr = new Array();
    var totalLocal = 0;
    var existsEmpty = false;
    for(var i = 0, l = itens.length; i < l; i++){
        var id    = itens[i].getAttribute("oid"); 
        var valor = itens[i].childNodes[0].childNodes[1].value;
        var tipo  = itens[i].childNodes[2].childNodes[1].value;
        var data  = itens[i].childNodes[4].childNodes[1].value;
        if((!tipo || tipo == '') || ( !valor || valor == '0,00') || (data == '')){
            existsEmpty = true;
            continue;
        }
        var tdata = new Date(data).getTime();
        if(tdata < dtInicial || tdata > dtFinal){
            alert('Existe item com data fora do período');
            $(itens[i].childNodes[4].childNodes[1]).addClass("invalid-input");
            return false;
        }
        itensArr.push((id != '' ? (id + ':') : '')+valor+':'+tipo+':'+data);
        totalLocal += parseFloat(floorMoney(valor));
    }
    
    $(".item-row input[type='date']").removeClass("invalid-input");
    if(status.checked && totalLocal != totalPagamentos){
        alert('A soma dos itens deve ser igual ao total dos pagamentos!');
        return false;
    }
    document.getElementById('itens-prestacao').value = itensArr.join(';');
    var actionStr = document.getElementsByName('for-update-id')[0].value == '' ?
                    'cadastrar' : 'atualizar';
    var c = confirm( (existsEmpty ? 'Existe alguns itens sem tipo ou sem valor.\n' : '')+'Deseja relamente '+actionStr+' essa prestação de conta?');
    if(c){
        $('#loja-prest-conta').attr('disabled',false);
        $('#cobrador-prest-conta').attr('disabled',false);
    }
    return c;
}
var waitCobrador = false;
function loadCobradores(id_loja){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Cobrador";
    $("#cobrador-prest-conta").html( opDefault );
    
    if(id_loja == "") return;
    
    var url = "ajax.php?code=4471&loja="+id_loja;
    
    get(url, function(data){
        if(data.code == "0"){
            var cobradores = data.data;
            for(i = 0; i < cobradores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = cobradores[i].nome; 
                op.value = cobradores[i].id;
                $("#cobrador-prest-conta").append( op );
            }
            if(waitCobrador){
                $("#cobrador-prest-conta").val(waitCobrador);
                waitCobrador = false;
            }
        }
    });
}
var idCount = 0;
function addItem(){
    var item = document.createElement('div');
    item.setAttribute('class', 'item-row');
    item.setAttribute('id', 'item-prestacao-'+idCount);
    item.setAttribute('oid', '');
    
    var lbValor     = document.createElement('label');
    var inputValor  = document.createElement('input');
    inputValor.setAttribute('type', 'text');
    inputValor.setAttribute('class', 'input text-input valor-item small-input');
    turnMoneyInput(inputValor);
    lbValor.innerHTML = 'Valor: ';
    lbValor.appendChild(inputValor);
    
    var lbTipo      = document.createElement('label'); 
    var inputTipo   = document.createElement('select');
    inputTipo.setAttribute('class', 'input select-input gray-grad-back tipo-item');
	inputTipo.setAttribute('style', 'width:20%');
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "SEM TIPO";
    inputTipo.appendChild(opDefault);
    for(var i = 0, len = itens.length; i <len; i++){
        var _item = itens[i];
        var childTipo = document.createElement('option');
        childTipo.setAttribute('value', _item.id); 
        childTipo.innerHTML = _item.nome;
        inputTipo.appendChild(childTipo);
    }
    lbTipo.innerHTML = 'Tipo: ';
    lbTipo.appendChild(inputTipo);
    
    var lbData = document.createElement('label');
    var inputData = document.createElement('input');
    inputData.setAttribute("type", "date");
    inputData.setAttribute('class', 'input text-input data-item');
    lbData.innerHTML = 'Data: ';
    lbData.appendChild(inputData);
    
    inputTipo.onchange = function(){
        var selectedText = inputTipo.options[inputTipo.selectedIndex].innerHTML;
        if(inputData.value == '' && selectedText.toLowerCase().indexOf("dinheiro") != -1){
            inputData.value = "<?php echo date('Y-m-d');?>";
        }
    };
    
    
    var span = '<span class=\'h-separator\'>&nbsp;</span>';
    
    item.appendChild(lbValor);
    $(item).append(span);
    item.appendChild(lbTipo);
    $(item).append(span);
    item.appendChild(lbData);
    
    var btnDel = document.createElement('a');
    btnDel.className = 'btn-remove-item'
    btnDel.setAttribute('href', 'javascript:;');
    btnDel.setAttribute('onclick', '$(\'#item-prestacao-'+idCount+'\').remove();');
    btnDel.innerHTML = '<img src=\'images/tool-icons/del.png\'>';
    btnDel.style.marginLeft = '10px';
    btnDel.style.position = 'relative';
    btnDel.style.top = '5px';
    item.appendChild(btnDel);
    
    $("#itens-prestacao-space").append(item);
    idCount++;
    return item;
}
function turnMoneyInput(input){
    var obj = { thousands:'', decimal:',', allowZero: true, prefix: 'R$ ', affixesStay: false};
    $(input).maskMoney(obj);
}
function delPrest(id){
    ACTION_AFTER = function(){
        window.location = "index.php?op=del_prest_conta&prest="+id;
    };
    openPasswordGerenteNeed();
}
var totalPagamentos = 0;
function openEditPrestContaMode(idPrest){
    if(!idPrest) return ;
    get('ajax.php?code=8881&prest='+idPrest, function(data){
        if(data.code == '0'){
            var prest = data.data;
            $('#for-update-id').val(prest.id);
            $('#loja-prest-conta').val(prest.loja);
            waitCobrador = prest.cobrador;
            loadCobradores(prest.loja);
            $('#data-final-prestacao').val(prest.dtFinal);
            $('#data-inicial-prestacao').val(prest.dtInicial);
            var itens = prest.itens;
            $('#itens-prestacao-space').html('');
            idCount = 0;
            for(var i = 0, l = itens.length; i < l; i++){
                var item = addItem();
                item.setAttribute('oid', itens[i].id);
                item.childNodes[0].childNodes[1].value = toMoney(itens[i].valor).replace('R$ ', '');
                item.childNodes[2].childNodes[1].value = itens[i].tipo;
                item.childNodes[4].childNodes[1].value = itens[i].data;
                if(itens[i].nonCaixa){
                    item.childNodes[0].childNodes[1].disabled = true;
                    item.childNodes[2].childNodes[1].disabled = true;
                    item.childNodes[4].childNodes[1].disabled = true;
                    item.removeChild(item.childNodes[5]);
                }
            }
            $('#status-prestacao').attr('checked', prest.status != '0' || prest.status != false);
            $('#loja-prest-conta').attr('disabled',true);
            $('#cobrador-prest-conta').attr('disabled',true);
            
            var pagamentos = prest.pagamentos;
            var tbody = document.getElementById('tbody-pagamentos');
            var rows = '';
            totalPagamentos = 0;
            for(var i = 0, l = pagamentos.length; i < l;i++){
                var row = '<tr>';
                row += '<td>'+pagamentos[i].vendaParcela+'</td>';
                row += '<td>'+pagamentos[i].numeroParcela+'</td>';
                row += '<td>'+toMoney(pagamentos[i].valor)+'</td>';
                row += '<td>'+pagamentos[i].data.split('-').reverse().join('/')+'</td>';
                row += '</tr>';
                rows += row;
                totalPagamentos += parseFloat(floorMoney(pagamentos[i].valor));
            }
            tbody.innerHTML = rows;
            document.getElementById('total-pagamentos').innerHTML = toMoney(totalPagamentos);
            openAddSpaceForm(document.getElementById("add-btn-tool"), true);
        } else badAlert(data.message);
    })
}
function reabrirPrestacao(prest){
    ACTION_AFTER = function(){
        window.location = 'index.php?op=reabrir_prestacao&prest='+prest;
    };
    openPasswordGerenteNeed();
}
<?php 
$opend_id = Config::getInstance()->filter('alias_edit');
if(!empty($opend_id)){?>
    dependencies.push(function(){ openEditPrestContaMode(<?php echo $opend_id;?>) });
<?php } ?>
</script>
<?php } ?>
