<?php 
$allow = array(PERFIL_GERENTE, PERFIL_OPERADOR, PERFIL_ADMINISTRADOR);
if(in_array($_SESSION[SESSION_PERFIL_FUNC], $allow)) { 
?>
<form action="?op=add_rota" method="post" id="form-cad-rota">
    <div class="tool-bar-form" form="form-cad-rota">
        <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre a rota&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id"/>
        <label> Nome:
            <input type="text" class="input text-input" id="nome-rota" name="nome" required/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Loja:
            <select name="loja" id="loja-rota" class="input select-input gray-grad-back" 
                    <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {?>
                        onchange="loadCobradores(this.value)" 
                    <?php } ?>>
                <option value=""> Sem Loja </option>
                <?php
                    include_once CONTROLLERS."loja.php";
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
            <select name="cobrador" id="cobrador-rota" class="input select-input gray-grad-back" onchange="loadRegioes(this.value)">
                <option value=""> Sem Cobrador </option>
                <?php
                if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR) {
                    include_once CONTROLLERS."funcionario.php";
                    $func_controller = new FuncionarioController();
                    $cobradores = $func_controller->getAllCobradores($_SESSION[SESSION_LOJA_FUNC]);
                    foreach($cobradores as $cobrador){
                        echo "<option value='{$cobrador->id}'> {$cobrador->nome} </option>";
                    } 
                }
                ?>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Região:
            <select name="regiao" id="regiao-rota" class="input select-input gray-grad-back" required>
                <option value=""> Selecione região </option>
            </select>
        </label>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>    
    </div>
</form>
<style>
#form-cad-rota .text-input{text-transform: uppercase;}
</style>
<script>
function openEditRotaMode(idrota){
    var url = "ajax.php?code=5582&rota="+idrota;
    get(url, function(data){
        if(data.code == "0"){
            $("#for-update-id").val(data.data.id);
            $("#nome-rota").val(data.data.nome);
            $("#loja-rota").val(data.data.loja);
            waitCobrador = data.data.cobrador;
            waitRegiao = data.data.regiao;
            if(typeof loadCobradores == 'function'){
                loadCobradores(data.data.loja);
            } else {
                $("#cobrador-rota").val(waitCobrador);
            }
            loadRegioes(waitCobrador);
            openAddSpaceForm(document.getElementById("add-btn-tool"), true);
        } else {
            badAlert(data.message);
        }
    });
    
}
var waitCobrador = false;
var waitRegiao = false;
<?php  if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
function loadCobradores(idLoja){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Cobrador";
    $("#cobrador-rota").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=4471&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var cobradores = data.data;
            for(var i = 0; i < cobradores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = cobradores[i].nome; 
                op.value = cobradores[i].id;
                $("#cobrador-rota").append( op );
            }
            if(waitCobrador){
                $("#cobrador-rota").val(waitCobrador);
                waitCobrador = false;
            }
        }
    });
}
<?php } ?>
function loadRegioes(idCobrador){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Região";
    $("#regiao-rota").html( opDefault );
    
    if(idCobrador == "") return;
    
    var url = "ajax.php?code=4416&cobr="+idCobrador;
    
    get(url, function(data){
        if(data.code == "0"){
            var regioes = data.data;
            for(var i = 0; i < regioes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = regioes[i].nome; 
                op.value = regioes[i].id;
                $("#regiao-rota").append( op );
            }
            if(waitRegiao){
                $("#regiao-rota").val(waitRegiao);
                waitRegiao = false;
            }
        }
    });
}
</script>
<?php } ?>