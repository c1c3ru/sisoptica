<form action="print.php?code=0005" method="post" target="_blank" onsubmit="return false;" id="form-print">
    <input type="hidden" id="type-view" name="" value='sim'/>
    <label> Loja:
        <div style="display: inline-block;">
            <a href="javascript:;" onclick="selectAllLojas()"> Todas as Lojas </a><br/>
            <select name="loja[]" size='3' required multiple
            class="input select-input gray-grad-back bigger-input" id="loja-relatorio">
                <?php
                include_once CONTROLLERS."loja.php";
                $loja_controller = new LojaController();
                $isWithFoerignValues = false;
                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
                    $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
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
        </div>
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
    <label> Filtro:
        <select class="input select-input gray-grad-back" name='filtro' required> 
            <option value='0' selected> POR COBRADOR </option>
            <option value='1'> POR REGIÃO </option>
            <option value='2'> POR ROTA </option>
            <option value='3'> POR LOCALIDADE </option>
        </select>
    </label>
    <span class="h-separator"> &nbsp; </span>
    <button class="btn submit green3-grad-back" name="submit-html"> 
        Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
    </button>
    <button class="btn submit green3-grad-back" name="submit-impressao"> 
        Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
    </button>
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
function selectAllLojas(){
    $("#loja-relatorio option").each(function (){
        this.selected = true;
    });
}
</script>