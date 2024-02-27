<form action="print.php?code=0008" method="post" target="_blank" onsubmit="return false;" id="form-print">
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
    <span class="h-separator">&nbsp;</span>
    <label> Cobrador:
            <select name="cobrador" id="cobrador-relatorio" onchange="loadRegioes(this.value)"
            class="input select-input gray-grad-back small-input">
            </select>
    </label>
    <span class="h-separator">&nbsp;</span>
    <label> Inicío:
        <input type="date" name="inicial" class="input text-input" value=""/>
    </label>
    <span class="h-separator">&nbsp;</span>
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
function loadCobradores(id_loja){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "TODOS OS COBRADORES";
    $("#cobrador-relatorio").html( opDefault );
    
    if(id_loja == "") return;
    
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