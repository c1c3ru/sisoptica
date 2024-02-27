<form id="form-cad-despesa" onsubmit="return false;">
    <div class="tool-bar-form" form="form-cad-despesa">
        <div onclick="openAddDespesa(this)" class="tool-button add-btn-tool-box" id='add-btn-subtool'> Adicionar </div>
        <div onclick="closeAddDespesa(this);closeCombustivelView();" class="tool-button cancel-btn-tool-box" id="cancel-btn-tool"> Cancelar </div>
        <div onclick="fecharCaixa()" class="tool-button caixa-btn-tool-box" style="float:right;"> Fechar Caixa </div>
    </div>
    <div class="hidden add-space-this-form">
        <fieldset>    
            <input type="hidden" name="for-update-id" id="for-update-id-desp" value=""/>
            <legend>&nbsp;informações sobre a despesa&nbsp;</legend>
            <label> Valor:
                <input type="text" class="input text-input" id="valor-despesa" name="valor" required/>
            </label>
            <span class="h-separator">&nbsp</span>
            <label>
                Natureza:
                <select name="natureza" id="natureza-despesa" 
                class="input select-input gray-grad-back small-input" 
                onchange="loadEntidades(this.value)" required>
                    <option value="">Selecione uma Natureza</option>
                    <?php 
                    include_once CONTROLLERS.'naturezaDespesa.php';
                    $naturezaController = new NaturezaDespesaController();
                    $naturezas          = $naturezaController->getAllNaturezas();
                    foreach($naturezas as $natureza){
                        echo '<option value=\''.$natureza->id.'\'>';
                        echo $natureza->nome;
                        echo '</option>';
                    }
                    ?>
                </select>
            </label>
            <span class="h-separator">&nbsp</span>
            <label>
                Entidade:
                <select name="entidade" id="entidade-despesa" required
                class="input select-input gray-grad-back small-input">
                    <option value="">Selecione uma Entidade</option>
                </select>
            </label>
            <span class="h-separator">&nbsp</span>
            <label> Observação:
                <textarea class="input text-input" name="observacao" id="observacao-despesa" rows="4"></textarea>
            </label>
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar" onclick="checkSubmit();"/>
            <div style="display: none;" id="combustivel-space">
                <p class="title-form"> Dados do Combustível </p>
                <label> Litros:
                    <input type="number" class="input text-input smaller-input" id="litros-combustivel" name="litros"  step="0.01" min="0.00"/>
                </label>
                <span class="h-separator">&nbsp</span>
                <label> Preço /p litro:
                    <input type="text" class="input text-input smaller-input" id="preco-combustivel" name="preco"/>
                </label>
                <span class="h-separator">&nbsp</span>
                <label> Km. Inicial:
                    <input type="number" class="input text-input smaller-input" id="km-inicial-combustivel" name="km-inicial" step="0.01" min="0.00"/>
                </label>
                <span class="h-separator">&nbsp</span>
                <label> Km. Final:
                    <input type="number" class="input text-input smaller-input" id="km-final-combustivel" name="km-final" step="0.01" min="0.00"/>
                </label>
            </div>
        </fieldset>
    </div>
</form>
<style>
#form-cad-caixa .text-input{text-transform: uppercase;}
#form-cad-caixa input[type='submit']{float: right;margin-top: 1.5%;}
#combustivel-space{margin-top: 10px;}
</style>
<script src="script/mask.js"></script>
<script src="script/jquery.maskMoney.js"></script>
<script>
$(function(){
    adjustsMoneyFields();
});
function adjustsMoneyFields(){
    var obj = { thousands:'', decimal:',', allowZero: true, prefix: 'R$ ', affixesStay: false};
    $('#valor-despesa').maskMoney(obj);
    $('#preco-combustivel').maskMoney(obj);
}
function checkSubmit(){
    if(!document.getElementById('form-cad-despesa').checkValidity() ||
        !parseFloat(document.getElementById('valor-despesa').value.replace(',','.'))){
        alert("Está faltando dados no formulário");
        return ;
    }
    if($('#litros-combustivel').attr('required')){
        if(parseFloat($('#km-inicial-combustivel').val()) > parseFloat($('#km-final-combustivel').val())){
            alert('Kilometragem inicial deve ser menor do que a final');
            return;
        }
    }
    var obj = formPostObject(document.getElementById('form-cad-despesa')); 
    obj['caixa'] = <?php echo defined("NO_CAIXA")? NO_CAIXA : "''" ;?>;
    $('#cancel-btn-tool').click();
    post('ajax.php?code=9004', obj, function(data){
        if(data.code == '0'){
            alert("Sucesso na operação");
            openViewDataMode('ajax.php?code=8181&caixa=<?php echo Config::getInstance()->filter('caixa');?>');
        } else badAlert(data.message);
    });
    
}
function closeCombustivelView(){
    $('#combustivel-space').slideUp('fast');
    $('#litros-combustivel').attr('required', false);
    $('#preco-combustivel').attr('required', false);
    $('#km-inicial').attr('required', false);
    $('#km-final-combustivel').attr('required', false);
}
function openCombustivelView(){
    $('#combustivel-space').slideDown('fast');
    $('#litros-combustivel').attr('required', true);
    $('#preco-combustivel').attr('required', true);
    $('#km-inicial').attr('required', true);
    $('#km-final-combustivel').attr('required', true);
}
var waitDespesa = false;
function loadEntidades(idNatu){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Selecione uma Entidade";
    $("#entidade-despesa").html( opDefault );
    
    if(idNatu == "") return;
    
    var selectNatu = document.getElementById('natureza-despesa');
    
    if( selectNatu.options[selectNatu.selectedIndex].innerHTML.toUpperCase().indexOf("COMBUSTIVEL") != -1 ||
        selectNatu.options[selectNatu.selectedIndex].innerHTML.toUpperCase().indexOf("COMBUSTÍVEL") != -1){
        openCombustivelView();
    } else {
        closeCombustivelView();
    }
    
    var url = 'ajax.php?code=1333&natureza='+idNatu;
    
    get(url, function(data){
        if(data.code == '0'){
            var entidades = data.data;
            for(var i = 0; i < entidades.length; i++){
                var op = document.createElement("option");
                op.innerHTML = entidades[i].nome; 
                op.value = entidades[i].id;
                $("#entidade-despesa").append( op );
            }
            if(waitDespesa){
                $('#entidade-despesa').val(waitDespesa);
                waitDespesa = false;
            }
        } else badAlert(data.message);
    });
}
function openEditDespesaMode(idDesp){
    if(!idDesp) return;
    get('ajax.php?code=9992&despesa='+idDesp, function(data){
        if(data.code == '0'){
            $("#for-update-id-desp").val(data.data.id);
            $("#valor-despesa").val(toMoney(data.data.valor).replace('R$',''));
            waitDespesa = data.data.entidade;
            $("#natureza-despesa").val(data.data.natureza);
            $("#natureza-despesa").change();
            if(data.data.isCombustivel){
                var combustivel = data.data.combustivel;
                $('#litros-combustivel').val(combustivel.litros);
                $('#preco-combustivel').val(toMoney(combustivel.preco).replace('R$', ''));
                $('#km-inicial-combustivel').val(combustivel.kmInicial);
                $('#km-final-combustivel').val(combustivel.kmFinal);
                openCombustivelView();
            }
            $("#observacao-despesa").val(data.data.observacao);
            openAddDespesa(document.getElementById("add-btn-subtool"), true);
        } else badAlert(data.message);
    });
}
function fecharCaixa(){
    if(confirm("Deseja realmente fechar o caixa diário?")){
        window.location='index.php?op=fec_caix';
    }
}
function openAddDespesa(btn, update_mode){
    var frameid = btn.parentNode.getAttribute("form");
    if($(btn.parentNode).children(".cancel-btn-tool-box").css("display") == "none")
        $(btn.parentNode).children(".cancel-btn-tool-box").css({display:'inline-block'});
    else
        $(btn.parentNode).children(".cancel-btn-tool-box").css({display:'none'});
    var addSpace = $("#"+frameid).children(".add-space-this-form");
    var submit = $("#"+frameid+" .submit");
    if(update_mode){
        submit.val("Atualizar");
    }else{
        clearForm(frameid);
        submit.val("Cadastrar");
    }
    $("#add-btn-subtool").css("display","none");
    if(update_mode && addSpace.is(":visible")){ 
        $(btn.parentNode).children(".cancel-btn-tool-box").css({display:'inline-block'});
        return;
    }
    addSpace.slideToggle("fast");
}
function closeAddDespesa(btn){
    var frameid = btn.parentNode.getAttribute("form");
    if($(btn).css("display") == "none")
        $(btn).css({display:'inline-block'});
    else{
        $("#add-btn-subtool").css("display","inline-block");
        $(btn).css({display:'none'}); 
    }
    clearForm(frameid);
    var submit = $("#"+frameid+" .submit");
    submit.val("Cadastrar");
    var addSpace = $("#"+frameid).children(".add-space-this-form");
    addSpace.slideToggle("fast");
}
function deleteDespesa(idDespesa){
    if(!idDespesa) return;
    get('ajax.php?code=8701&despesa='+idDespesa, function(data){
        if(data.code == '0'){
            alert('Sucesso na operação');
            openViewDataMode('ajax.php?code=8181&caixa=<?php echo Config::getInstance()->filter('caixa');?>');
        } else badAlert(data.message);
    });
}
</script>