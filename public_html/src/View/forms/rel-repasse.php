<form action="print.php?code=0013" method="post" target="_blank" id="form-print" onsubmit="return false;">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja: 
        <select name="loja" class="input select-input gray-grad-back smaller-input" id="loja-relatorio" onchange="loadCobradores(this.value)">	
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
    <span class="h-separator"> &nbsp; </span>
    <label> Cobrador:
        <select name="cobrador" id="cobrador-relatorio" class="input select-input gray-grad-back small-input">
            <option value=""> TODOS </option>
            <?php
            if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR) {
                $func_controller = new FuncionarioController();
                $cobradores = $func_controller->getAllCobradores($_SESSION[SESSION_LOJA_FUNC]);
                foreach($cobradores as $cobrador){
                    echo "<option value='{$cobrador->id}'> {$cobrador->nome} </option>";
                }
            }
            ?>
        </select>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> Data Inicial:
        <input type="date" name="data-inicial" class="input text-input" required/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> Data Final:
        <input type="date" name="data-final" class="input text-input" required/>
    </label>
    <p class="v-separator">&nbsp;</p>
    <label><b>Incluir na busca:</b> &nbsp;</label>
    <label>
        Dt. Chegada
        <input type="checkbox" name="data[]" value="dt-chegada" checked/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label>
        Dt. Env. Conserto
        <input type="checkbox" name="data[]" value="dt-envio-conserto" checked/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label>
        Dt. Recb. Conserto
        <input type="checkbox" name="data[]" value="dt-recebimento-conserto" checked/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label>
        Dt. Env. Cliente
        <input type="checkbox" name="data[]" value="dt-envio-cliente" checked/>
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
<script>
function loadCobradores(idLoja){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "TODOS";
    $("#cobrador-relatorio").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=4471&loja="+idLoja;
    
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
    loadCobradores(document.getElementById('loja-relatorio').value);
    $('#form-print button').click(function(){
        var checked = false;
        $('#form-print input[type=\'checkbox\']').each(function(){
            if(this.checked) { 
                checked = true; 
            }
        });
        if(!checked) {
            alert('Sem datas para a busca. Você deve marcar ao menos uma');
            return ;
        }
        if(this.name.indexOf('impressao') != -1) document.getElementById('type-view').name = 'js';
        else document.getElementById('type-view').name = 'html';
        var form = document.getElementById('form-print');
        form.setAttribute('onsubmit', 'return true;');
        form.onsubmit();
    });
});
</script>