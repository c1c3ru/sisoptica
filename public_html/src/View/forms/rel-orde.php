<form action="print.php?code=0006" method="post" target="_blank" id="form-print" onsubmit="return false;">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja: 
        <select name="loja" class="input select-input gray-grad-back smaller-input" id="loja-relatorio">	
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
    <label> Laboratório:
        <select name="lab[]" size="2" required multiple
        class="input select-input gray-grad-back">
            <?php
            include_once CONTROLLERS."laboratorio.php";
            $controller_lb = new LaboratorioController();
            $labs = $controller_lb->getAllLaboratorios();
            foreach ($labs as $lab){
                echo "<option value='{$lab->id}'";
                if($lab->principal) echo " selected ";
                echo "> {$lab->nome} </option>";
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
    <span class="h-separator"> &nbsp; </span>
    <label><b>Tipo:</b></label>
    <label> <input type="radio" name="mode" value="0" checked/> Enviadas </label>
    <span class="h-separator"> &nbsp; </span>
    <label> <input type="radio" name="mode" value="1"/> Recebidas </label>
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