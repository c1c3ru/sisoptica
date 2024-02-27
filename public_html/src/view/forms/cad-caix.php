<form action="?op=add_caix" method="post" id="form-cad-caixa">
    <div class="tool-bar-form" form="form-cad-caixa">
        <div onclick="openAddSpaceForm(this);document.getElementById('data-caixa').value='<?php echo date("Y-m-d");?>'" 
        class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre a caixa diário&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label>
                Data: <input type="date" id="data-caixa" class="input text-input" 
                        name="data" value="<?php echo date("Y-m-d");?>" required/>			
        </label>
        <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
    </fieldset>
    </div>
</form>
<style>
#form-cad-caixa .text-input{text-transform: uppercase;}
#form-cad-caixa input[type='submit']{float: right;}
</style>
<script>
function fecharCaixa(loja){
    if(confirm("Deseja realmente fechar o caixa diário?")){
        window.location='index.php?op=fec_caix&loja='+loja;
    }
}
</script>