<form action="?op=add_tipo_pgmto" method="post" id="form-cad-tipo-pgmto">
    <div class="tool-bar-form" form="form-cad-tipo-pgmto">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre o tipo de pagamento&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label>
            Tipo: <input type="text" id="nome-tipo" class="input text-input" name="nome" required/>			
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label>
            Observação: <textarea name="observacao" id="observacao-tipo" class="input text-input"></textarea>			
        </label>
        <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
    </fieldset>
    </div>
</form>
<style>
#form-cad-tipo-pgmto .text-input{text-transform: uppercase;}
#form-cad-tipo-pgmto input[type='submit']{float: right;}
</style>
<script>
function openEditTipoPagamentoMode(idtipo){
    var url = "ajax.php?code=9700&tipo="+idtipo;
    get(url, function(data){
       if(data.code == "0"){
           $("#for-update-id").val(data.data.id);
           $("#nome-tipo").val(data.data.nome);
           $("#observacao-tipo").val(data.data.observacao);
           openAddSpaceForm(document.getElementById("add-btn-tool"), true);
       } else 
           badAlert(data.message);
    });
}
</script>