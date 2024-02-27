<?php 
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
?>
<form action="?op=add_tipo" method="post" id="form-cad-tipo-produto">
    <div class="tool-bar-form" form="form-cad-tipo-produto">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre o tipo&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label>
                Nome: <input type="text" id="nome-tipo" class="input text-input" name="nome" required/>			
        </label>
        <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
    </fieldset>
    </div>
</form>
<style>
#form-cad-tipo-produto .text-input{text-transform: uppercase;}
</style>
<script>
function openEditTipoMode(idtipo){
    var url = "ajax.php?code=5560&tipo="+idtipo;
    get(url, function(data){
       if(data.code == "0"){
           $("#for-update-id").val(data.data.id);
           $("#nome-tipo").val(data.data.nome);
           openAddSpaceForm(document.getElementById("add-btn-tool"), true);
       } else 
           badAlert(data.message);
    });
}
</script>
<?php } ?>