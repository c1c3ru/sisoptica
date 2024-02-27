<?php 
$allow = array(PERFIL_GERENTE, PERFIL_OPERADOR, PERFIL_ADMINISTRADOR);
if(in_array($_SESSION[SESSION_PERFIL_FUNC], $allow)) { 
    include_once CONTROLLERS."loja.php";
    include_once CONTROLLERS."funcionario.php";    
?>
<form action="?op=add_regi" method="post" id="form-cad-regiao">
    <div class="tool-bar-form" form="form-cad-regiao">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre a região&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value="">
        <label> Nome:
            <input type="text" class="input text-input" id="nome-regiao" name="nome" required/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Loja:
            <select name="loja" id="loja-regiao" class="input select-input gray-grad-back" 
                    <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {?>
                        onchange="loadCobradores(this.value)" 
                    <?php } ?> 
            required>
                
                <option value=""> Sem Loja </option>
                <?php
                    $controller_loja = new LojaController();
                    if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
                        $lojas = $controller_loja->getAllLojas();
                    } else {
                        $lojas = array($controller_loja->getLoja($_SESSION[SESSION_LOJA_FUNC]));
                    }
                    foreach($lojas as $loja){
                        echo "<option value='$loja->id'> $loja->sigla </option>";
                    }
                ?>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Cobrador:
            <select name="cobrador" id="cobrador-regiao" class="input select-input gray-grad-back" required>
                <option value=""> Sem Cobrador </option>
                <?php
                if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR) {
                    $func_controller = new FuncionarioController();
                    $cobradores = $func_controller->getAllCobradores($_SESSION[SESSION_LOJA_FUNC]);
                    foreach($cobradores as $cobrador){
                        echo "<option value='{$cobrador->id}'> {$cobrador->nome} </option>";
                    }
                }
                ?>
            </select>
        </label>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>
    </div>
</form>
<style>
#form-cad-regiao .text-input{text-transform: uppercase;}
</style>
<script>
function openEditRegiaoMode(idregi){
    var url = "ajax.php?code=5567&egi="+idregi;
    get(url, function(data){
       if(data.code == "0"){
            $("#for-update-id").val(data.data.id);
            $("#nome-regiao").val(data.data.nome);
            waitCobrador = data.data.cobrador;
            $("#loja-regiao").val(data.data.loja);
            <?php  if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
            loadCobradores(data.data.loja);
            <?php } else { ?>
            $("#cobrador-regiao").val(waitCobrador);
            <?php } ?>
            openAddSpaceForm(document.getElementById('add-btn-tool'), true);
       } else {
           badAlert(data.message);
       } 
    });
}
var waitCobrador = false;
<?php  if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
function loadCobradores(idLoja){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Cobrador";
    $("#cobrador-regiao").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=4471&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var cobradores = data.data;
            for(i = 0; i < cobradores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = cobradores[i].nome; 
                op.value = cobradores[i].id;
                $("#cobrador-regiao").append( op );
            }
            if(waitCobrador){
                $("#cobrador-regiao").val(waitCobrador);
                waitCobrador = false;
            }
        }
    });
}
<?php } ?>
</script>
<?php } ?>