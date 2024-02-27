<form action="print.php?code=0002" method="post" target="_blank" onsubmit="return false;" id="form-print">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja: 
        <select name="loja" class="input select-input gray-grad-back smaller-input" 
        id="loja-relatorio" onchange="loadCobradores(this.value)">	
            <?php
            include_once CONTROLLERS."loja.php";
            $loja_controller = new LojaController();
            $isWithFoerignValues = false;
            if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
                $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                echo "<option value=\"\"> TODAS </option>";
            } else {
                $lojas = array(
                    $loja_controller->getLoja( $_SESSION[SESSION_LOJA_FUNC], $isWithFoerignValues)
                );
            }
            foreach($lojas as $loja){ ?>
                <option value="<?php echo $loja->id; ?>"
                <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                ><?php echo $loja->sigla; ?></option>
            <?php } ?>
        </select>
    </label>
    <label> Cobrador:
            <select name="cobrador" id="cobrador-relatorio" onchange="loadRegioes(this.value)"
            class="input select-input gray-grad-back small-input">
                <option value=""> Selecione cobrador </option>
            </select>
    </label>
    <label> Região:
        <select name="regiao" id="regiao-relatorio" onchange="loadRotas(this.value)"
        class="input select-input gray-grad-back smaller-input" required>
            <option value=""> Sem Região </option>
        </select>
    </label>
    <label> Rota:
        <select name="rota" id="rota-relatorio" class="input select-input gray-grad-back smaller-input">
            <option value=""> TODAS </option>
        </select>
    </label>
    <label> Inicío:
        <input type="date" name="inicial" class="input text-input" value=""/>
    </label>
    <label> Fim:
        <input type="date" name="final" required class="input text-input" value="<?php echo date("Y-m-d");?>"/>
    </label>
    <p style="text-align: right;margin-top:5px;">
        <button class="btn submit green3-grad-back" name="submit-html"> 
            Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
        </button>
        <button class="btn submit green3-grad-back" name="submit-impressao"> 
            Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
        </button>
    </p>
</form>
<style>
#form-print label{margin-right: 0.5em;}
</style>
<script> 
function getOpDefault(txt){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = txt;
    return opDefault;
}
function allAll(){
    $("#cobrador-relatorio").html(getOpDefault('TODOS'));
    $("#regiao-relatorio").html(getOpDefault('TODAS'));
    $("#rota-relatorio").html(getOpDefault('TODAS'));
    $("#cobrador-relatorio").attr('required', false);
    $("#regiao-relatorio").attr('required', false);
}
function allNotAll(){
    $("#cobrador-relatorio").html(getOpDefault('Selecione cobrador'));
    $("#regiao-relatorio").html(getOpDefault('Sem região'));
    $("#rota-relatorio").html(getOpDefault('TODAS'));
    $("#cobrador-relatorio").attr('required', true);
    $("#regiao-relatorio").attr('required', true);
}
function loadCobradores(id_loja){    
    if(id_loja == "") {
        allAll();
        return;
    } else {
        allNotAll();
    }
    
    var url = "ajax.php?code=4471&loja="+id_loja;
    
    get(url, function(data){
        if(data.code == "0"){
            var cobradores = data.data;
            for(i = 0; i < cobradores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = cobradores[i].nome; 
                op.value = cobradores[i].id;
                $("#cobrador-relatorio").append( op );
            }
        }
    });
}
function loadRegioes(idCobrador){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Região";
    $("#regiao-relatorio").html( opDefault );
    $("#regiao-relatorio").attr('required', true);
    
    if(idCobrador == "") return;
    
    var url = "ajax.php?code=4416&cobr="+idCobrador;
    
    get(url, function(data){
        if(data.code == "0"){
            var regioes = data.data;
            for(i = 0; i < regioes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = regioes[i].nome; 
                op.value = regioes[i].id;
                $("#regiao-relatorio").append( op );
            }
        }
    });
}
function loadRotas(idRegiao){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "TODAS";
    $("#rota-relatorio").html( opDefault );
    
    if(idRegiao == "") return;
    
    var url = "ajax.php?code=4516&regi="+idRegiao;
    
    get(url, function(data){
        if(data.code == "0"){
            var regioes = data.data;
            for(i = 0; i < regioes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = regioes[i].nome; 
                op.value = regioes[i].id;
                $("#rota-relatorio").append( op );
            }
        }
    });
}
$(function(){
    $('#form-print button').click(function(){
        if(this.name.indexOf('impressao') != -1) document.getElementById('type-view').name = 'js';
        else document.getElementById('type-view').name = 'html';
        var form = document.getElementById('form-print');
        form.setAttribute('onsubmit', 'return true;');
        form.onsubmit();
    });
    loadCobradores(document.getElementById("loja-relatorio").value);
});
</script>
