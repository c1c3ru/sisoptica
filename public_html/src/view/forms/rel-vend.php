<form action="print.php?code=0003" method="post" target="_blank" onsubmit="return false;" id="form-print">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <table class="center">
        <tr> 
            <td class="cell-select"> 
                <label> Loja: 
                    <select name="loja" class="input select-input gray-grad-back" 
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
            </td>
            <td class='info-type'> <label><input type="radio" checked name="tipo-periodo" value="m"> Por mês: </label></td>
            <td class='info-type'>
                <label> Incial:
                    <input type="month" name="m-i" class="input text-input" onfocus="checkTipoPeriodo(0)" value="<?php echo date("Y-m");?>" />
                </label>
                <span class="h-separator"> &nbsp; </span>
                <label> Final:
                    <input type="month" name="m-f" class="input text-input" onfocus="checkTipoPeriodo(0)" value="<?php echo date("Y-m", strtotime("+1 month"));?>"/>
                </label>
            </td>
        </tr>
        <tr> 
            <td rowspan="2" class="cell-select"> 
                <label> Região:
                    <select name="regiao[]" id="regiao-relatorio" size="4" class="input select-input gray-grad-back" multiple >
                    </select>
                </label>
            </td>
            <td class='info-type'><label><input type="radio" name="tipo-periodo" value="w" > Por Semana: </label></td>
            <td class='info-type'><label><input type="week" name="w" class="input text-input" onfocus="checkTipoPeriodo(1)" value="<?php echo date("Y-m");?>"/></label></td>
            <td>
                <button class="btn submit green3-grad-back" name="submit-html"> 
                    Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
                </button>
            </td>
        </tr>
        <tr> 
            <td class='info-type'><label><input type="radio" name="tipo-periodo" value="d"> Por Dia: </label></td>
            <td class='info-type'><label><input type="date" name="d" class="input text-input" onfocus="checkTipoPeriodo(2)" value="<?php echo date("Y-m");?>"/></label></td>
            <td>
                <button class="btn submit green3-grad-back" name="submit-impressao"> 
                    Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
                </button>
            </td>
        </tr>
    </table>
    
</form>
<style>
form table td{padding: 5px;}
.cell-select{text-align:right;padding-right:20px;}
.info-type{border-bottom: lightgray solid 1px;}
</style>
<script> 
function checkTipoPeriodo(idx){
    document.getElementsByName('tipo-periodo')[idx].checked = true;
}    
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