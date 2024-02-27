<?php 
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
?>
<form action="?op=add_labo" method="post" id="form-cad-labo">
    <div class="tool-bar-form" form="form-cad-labo">
        <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre o laboratório&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id"/>
        <label> Nome:
            <input type="text" class="input text-input" id="nome-laboratorio" name="nome" required/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Telefone:
            <input type="text" class="input text-input" id="telefone-laboratorio" maxlength='13' name="telefone" onkeypress="MascaraTelefone(this)"/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> CNPJ:
            <input type="text" class="input text-input" id="cnpj-laboratorio" name="cnpj" maxlength="18" onkeypress="MascaraCNPJ(this)"/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label class="checkbox">
            <input type="checkbox" class="input" id="principal-laboratorio" name="principal"/>	 
            <span class="h-separator"> &nbsp; </span> 	
            Principal
        </label>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>
    </div>
</form>
<style>
#form-cad-labo .text-input{text-transform: uppercase;}
</style>
<script src="script/mask.js"></script>
<script>
function openEditLaboratorioMode(idlabo){
    var url = "ajax.php?code=4447&labo="+idlabo;
    get(url, function(data){
       if(data.code == "0"){
           $("#for-update-id").val(data.data.id);
           $("#nome-laboratorio").val(data.data.nome);
           $("#telefone-laboratorio").val(data.data.telefone);
           $("#telefone-laboratorio").keypress();
           $("#cnpj-laboratorio").val(data.data.cnpj);
           $("#cnpj-laboratorio").keypress();
           if(data.data.principal == "1"){
                $("#principal-laboratorio").attr("checked",true);
           } else {
                $("#principal-laboratorio").attr("checked",false);
           }
           openAddSpaceForm(document.getElementById("add-btn-tool"), true);
       } else 
           badAlert(data.message);
    });
}    
</script>
<?php } ?>