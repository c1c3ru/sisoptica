<form action="print.php?code=0012" method="post" target="_blank" onsubmit="return false;" id="form-print">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja: 
        <select name="loja" class="input select-input gray-grad-back small-input" id="loja-relatorio">	
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
        Naturezas:
        <div style="display: inline-block;">
            <a href="javascript:;" onclick="selectAllNaturezas()"> Todas as Naturezas </a><br/>
            <select name="natureza[]" size='3' required multiple  id="natureza-relatorio"
            class="input select-input gray-grad-back bigger-input" required>
            <?php 
            $config = Config::getInstance();
            $naturezas = $config->currentController->getAllNaturezas();
            foreach($naturezas as $n){
                echo '<option value=\'' . $n->id . '\'>' . $n->nome . '</option>';
            }
            ?>
            </select>
        </div>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> De:
        <input type="date" name="data-limite-inferior" class="input text-input" value="<?php echo date("Y-m-d", strtotime("-1 month"));?>" required/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label> Até:
        <input type="date" name="data-limite-superior" class="input text-input" required value="<?php echo date("Y-m-d");?>"/>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <label title="Se marcado, o relatório mostrar venda por venda do agente">
        <input type="checkbox" name="resumida" />
        Resumido
    </label>
    <br/>
    <button class="btn submit green3-grad-back" name="submit-impressao"> 
        Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
    </button>
    <button class="btn submit green3-grad-back" name="submit-html"> 
        Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
    </button>
</form>
<style> form button {float:right; margin-left: 10px;} </style>
<script>
$(function(){
    $('#form-print button').click(function(){
        if(this.name.indexOf('impressao') != -1) document.getElementById('type-view').name = 'js';
        else document.getElementById('type-view').name = 'html';
        var form = document.getElementById('form-print');
        form.setAttribute('onsubmit', 'return true;');
        form.onsubmit();
    });
});
function selectAllNaturezas(){
    $("#natureza-relatorio option").each(function (){
        this.selected = true;
    });
}
</script>