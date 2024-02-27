<form action="print.php?code=0010" method="post" target="_blank" onsubmit="return false;" id="form-print">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja: 
        <select name="loja" class="input select-input gray-grad-back small-input" 
                id="loja-relatorio" onchange="loadAgentesVenda(this.value)">	
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
    <label>
        Agente de Vendas:
         <select name="agente" id="agente-relatorio" class="input select-input gray-grad-back small-input">
            <option value=""> TODOS </option>
        </select>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> Inicío:
        <input type="date" name="data-inicial" required class="input text-input"/>
    </label>
    <label> Fim:
        <input type="date" name="data-final" required class="input text-input"/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label title="Se marcado, o relatório mostrar venda por venda do agente">
        <input type="checkbox" name="resumida" />
        Resumido
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
<style> form button {float:right;margin-right:10px;} </style>
<script>
function loadAgentesVenda(idLoja){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "TODOS";
    $("#agente-relatorio").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=7755&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var agentes = data.data;
            for(i = 0; i < agentes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = agentes[i].nome; 
                op.value = agentes[i].id;
                $("#agente-relatorio").append( op );
            }
        }
    });
}
$(function(){
    loadAgentesVenda(document.getElementById('loja-relatorio').value);
    $('#form-print button').click(function(){
        if(this.name.indexOf('impressao') != -1) document.getElementById('type-view').name = 'js';
        else document.getElementById('type-view').name = 'html';
        var form = document.getElementById('form-print');
        form.setAttribute('onsubmit', 'return true;');
        form.onsubmit();
    });
});
</script>
