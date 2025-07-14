<form action="print.php?code=0007" method="post" target="_blank" onsubmit="return false;" id="form-print">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja: 
        <select name="loja" class="input select-input gray-grad-back small-input" 
        id="loja-relatorio" onchange="loadRegioes(this.value)">	
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
    <label> Região:
        <select name="regiao[]" id="regiao-relatorio" onchange="loadRotas(this.value)" multiple
        class="input select-input gray-grad-back small-input" required size="4">
            <option value=""> Selecione região </option>
        </select>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> De:
        <input type="date" name="data-limite-inferior" class="input text-input" value=""/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> Até:
        <input type="date" name="data-limite-superior" class="input text-input" required value="<?php echo date("Y-m-d", strtotime("+1 month"));?>"/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <button class="btn submit green3-grad-back" name="submit-impressao"> 
        Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
    </button>
    <button class="btn submit green3-grad-back" name="submit-html"> 
        Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
    </button>
</form>
<style> form button {float:right;margin-right:10px;margin-top:20px;} </style>
<script>
$(function(){ loadRegioes(document.getElementById('loja-relatorio').value); });
function loadRegioes(idLoja){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    
    if(idLoja == "") {
        opDefault.innerHTML = "TODAS";
        $("#regiao-relatorio").html( opDefault );
        $("#regiao-relatorio").attr( 'required', false );
        return;
    }
    opDefault.innerHTML = "Sem Região";
    $("#regiao-relatorio").html( opDefault );
    $("#regiao-relatorio").attr( 'required', true );
    
    $("#regiao-relatorio").html("");
    
    var url = "ajax.php?code=4415&loja="+idLoja;
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
$(function(){
    $('#form-print button').click(function(){
        if(this.name.indexOf('impressao') != -1) document.getElementById('type-view').name = 'js';
        else document.getElementById('type-view').name = 'html';
        var form = document.getElementById('form-print');
        form.setAttribute('onsubmit', 'return true;');
        form.onsubmit();
    });
});
</script>