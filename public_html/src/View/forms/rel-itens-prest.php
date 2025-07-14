<form action="print.php?code=0009" method="post" target="_blank" onsubmit="return false;" id="form-print">
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
        Tipos de Pagamento:
        <div style="display: inline-block;">
            <a href="javascript:;" onclick="selectAllTipos()"> Todos os Tipos </a><br/>
            <select name="tipos[]" size='3' required multiple  id="tipos-relatorio"
            class="input select-input gray-grad-back bigger-input" required>
            <?php 
            include_once CONTROLLERS.'tipoPagamento.php';
            $tipoController = new TipoPagamentoController();
            $tipos          = $tipoController->getAllTiposPagamento();
            foreach($tipos as $t){
                echo '<option value=\'' . $t->id . '\'>' . $t->nome . '</option>';
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
    <button class="btn submit green3-grad-back" name="submit-impressao"> 
        Imprimir <img src="<?php echo GRID_ICONS."impressora.png";?>" style="vertical-align: middle;">
    </button>
    <button class="btn submit green3-grad-back" name="submit-html"> 
        Visualizar <img src="<?php echo GRID_ICONS."documento.png";?>" style="vertical-align: middle;">
    </button>
</form>
<style> form button {float:right;margin-right:10px;margin-top: 40px;} </style>
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
function selectAllTipos(){
    $("#tipos-relatorio option").each(function (){
        this.selected = true;
    });
}
</script>