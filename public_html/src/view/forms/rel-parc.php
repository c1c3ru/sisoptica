<form action="print.php?code=0004" method="post" target="_blank" id="form-print" onsubmit="return false;">
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
        <select name="regiao[]" id="regiao-relatorio" size="5"
        class="input select-input gray-grad-back small-input" multiple >
        </select>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> Data:
        <input type="date" name="data" class="input text-input" value="<?php echo date("Y-m");?>" required/>
    </label>
    <button class="btn submit green3-grad-back" name="submit-impressao"> 
        Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
    </button>
    <button class="btn submit green3-grad-back" name="submit-html"> 
        Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
    </button>
</form>
<style>
form button{float: right;margin-top:20px;margin-right:5px;}
</style>
<script> 
function loadRegioes(idLoja){
    if(idLoja == "") {
        $("#regiao-relatorio").attr("required", false);
        return $("#regiao-relatorio").html("<option value='all'> TODAS AS REGIÕES </option>");
    }
    $("#regiao-relatorio").attr("required", true);
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
    $("#loja-relatorio").change(); 
});
</script>