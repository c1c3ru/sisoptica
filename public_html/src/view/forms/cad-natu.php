<?php
include_once CONTROLLERS.'funcionario.php';
if($_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR &&
   $_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR){
    js_redirection('<b>Permissão Negada</b>');
}
?>
<form action="?op=add_natu" method="post" id="form-cad-natureza">
    <div class="tool-bar-form" form="form-cad-natureza">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre a natureza de despesa&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label>
                Nome: <input type="text" id="nome-natureza" class="input text-input" name="nome" required/>			
        </label>
        <span class="h-separator">&nbsp;</span>
        <label>
            Tipo de Entidade: <select name="tipo" class="input select-input gray-grad-back" id="tipo-natureza" required>	
                <option value="">Selecione um Tipo</option>
                <?php
                $config = Config::getInstance();
                $tipos  = $config->currentController->tiposNatureza;
                unset($tipos[NaturezaDespesaController::SEM_TIPO]);
                foreach ($tipos as $id => $tipo){
                    echo "<option value='{$id}'> {$tipo} </option>";
                }
                ?>
            </select>
        </label>
        <span class="h-separator">&nbsp;</span>
        <label>Movimentação:</label>
        &nbsp;&nbsp;
        <label><input type="radio" name="movimentacao" class="with-default" id="movimentacao-natureza-e" dvalue="e" value="e">Entrada</label>
        &nbsp;
        <label><input type="radio" name="movimentacao" class="with-default" id="movimentacao-natureza-s" dvalue="s" value="s" checked>Saída</label>
        <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
    </fieldset>
    </div>
</form>
<style>
#form-cad-natureza .text-input{text-transform: uppercase;}
#form-cad-natureza input[type='submit']{float: right;}
</style>
<script>
function openEditNaturezaMode(idnat){
    var url = "ajax.php?code=1177&natureza="+idnat;
    get(url, function(data){
       if(data.code == "0"){
           $("#for-update-id").val(data.data.id);
           $("#nome-natureza").val(data.data.nome);
           $("#tipo-natureza").val(data.data.tipo);
           if(data.data.entrada == '1' || data.data.entrada != false){
               $('#movimentacao-natureza-e').attr('checked', true);
           } else {
               $('#movimentacao-natureza-s').attr('checked', true);
           }
           openAddSpaceForm(document.getElementById("add-btn-tool"), true);
       } else 
           badAlert(data.message);
    });
}
</script>