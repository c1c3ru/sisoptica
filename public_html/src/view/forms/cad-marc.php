<?php 
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
?>
<form action="?op=add_marc" method="post" id="form-cad-marca">
    <div class="tool-bar-form" form="form-cad-marca">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre a marca&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label>
                Nome: <input type="text" id="nome-marca" class="input text-input" name="nome" required/>			
        </label>
        <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
    </fieldset>
    </div>
</form>
<style>
#form-cad-marca .text-input{text-transform: uppercase;}
</style>
<script>
function openEditMarcaMode(idmarc){
    var url = "ajax.php?code=7711&marc="+idmarc;
    get(url, function(data){
       if(data.code == "0"){
           $("#for-update-id").val(data.data.id);
           $("#nome-marca").val(data.data.nome);
           openAddSpaceForm(document.getElementById("add-btn-tool"), true);
       } else 
           badAlert(data.message);
    });
}
</script>
<?php } ?>