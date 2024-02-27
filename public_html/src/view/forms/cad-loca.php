<?php
$allow = array(PERFIL_GERENTE, PERFIL_OPERADOR, PERFIL_ADMINISTRADOR);
if(in_array($_SESSION[SESSION_PERFIL_FUNC], $allow)) {     
    include_once CONTROLLERS."rota.php";
    include_once CONTROLLERS."regiao.php";
?>
<form action="?op=add_loca" method="post" id="form-cad-localidade">
    <div class="tool-bar-form" form="form-cad-localidade">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre o localidade&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label> Cidade: 
            <select name="cidade" class="input select-input gray-grad-back" id="cidade-localidade" required>	
                <option value=""> Selecione uma cidade </option>
                <?php
                $controller_regiao = new RegiaoController();
                $estados = $controller_regiao->getEstados();
                foreach ($estados as $estado) {
                ?>
                    <optgroup label="<?php echo $estado->sigla;?>">  
                <?php    
                    $cidades = $controller_regiao->getCidadesByEstado($estado->id);
                    foreach ($cidades as $cidade) {
                ?>
                    <option value="<?php echo $cidade->id;?>"><?php echo $cidade->nome; ?></option>
                <?php } ?>
                    </optgroup>
                <?php } ?>

            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label>
                Nome: <input type="text" id="nome-localidade" class="input text-input" name="nome" required/>			
        </label>
        <p class="v-separator"> &nbsp;</p>
        <label> Loja:
            <select name="loja" id="loja-localidade" class="input select-input gray-grad-back" 
                    <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {?>
                        onchange="loadCobradores(this.value)" 
                    <?php } ?> >
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
            <select name="cobrador" id="cobrador-localidade" class="input select-input gray-grad-back" onchange="loadRegioes(this.value)">
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
        <p class="v-separator"> &nbsp;</p>
        <label> Região:
            <select name="regiao" id="regiao-localidade" class="input select-input gray-grad-back" onchange="loadRotas(this.value)">
                <option value=""> Selecione região </option>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Rota: 
            <select id="rota-localidade" onchange="loadPositions()" required class="input select-input gray-grad-back" name="rota">
                <option value=""> Sem Rota </option>
            </select>			
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Posição na Rota:
            <select id="ordem-localidade" class="input select-input gray-grad-back" name="ordem" required> 
                <option value=""> Sem Posição </option>
            </select>
        </label>
        <p style="text-align: right">
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>    
    </div>
</form>
<style>
#form-cad-localidade .text-input{text-transform: uppercase;}
</style>
<script>
function openEditLocalidadeMode(idlocalidade){
    var url = "ajax.php?code=7571&loca="+idlocalidade;
    get(url, function(data){
        if(data.code == "0"){
            
            $("#cidade-localidade").val(data.data.cidade);
            $("#nome-localidade").val(data.data.nome);
            $("#loja-localidade").val(data.data.loja);
            
            waitCobrador = data.data.cobrador;
            waitRegiao = data.data.regiao;
            waitRota = data.data.rota;
            if(typeof loadCobradores == 'function'){
                loadCobradores(data.data.loja);
            } else {
                $("#cobrador-localidade").val(waitCobrador);
            }
            loadRegioes(data.data.cobrador);
            loadRotas(data.data.regiao);
            
            posicaoWait = data.data.ordem;
            
            loadPositions();
            
            $("#for-update-id").val(data.data.id);
            
            openAddSpaceForm(document.getElementById("add-btn-tool"), true);

        } else {
            badAlert(data.message);
            clearForm("form-cad-localidade");
        }
    });
}

var posicaoWait = false;

function loadPositions(){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Posição";
    $("#ordem-localidade").html( opDefault );
    
    var idRota = $("#rota-localidade").val();
    if(idRota == "") return;
    
    var url = "ajax.php?code=4460&rota="+idRota;
    
    get(url, function(data){
        if(data.code == "0"){
            var limit = data.data;
            if(!posicaoWait) limit++;
            for(i = 1; i <= limit; i++){
                var op = document.createElement("option");
                op.innerHTML = op.value = i;
                $("#ordem-localidade").append( op );
            }
            if(!posicaoWait) $("#ordem-localidade").val(limit);
            else $("#ordem-localidade").val(posicaoWait);
            posicaoWait = false;
        } 
        
    });
}

var waitCobrador = false;
var waitRegiao = false;
var waitRota = false;
<?php  if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
function loadCobradores(idLoja){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Cobrador";
    $("#cobrador-localidade").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=4471&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var cobradores = data.data;
            for(i = 0; i < cobradores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = cobradores[i].nome; 
                op.value = cobradores[i].id;
                $("#cobrador-localidade").append( op );
            }
            if(waitCobrador){
                $("#cobrador-localidade").val(waitCobrador);
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
    $("#regiao-localidade").html( opDefault );
    
    if(idCobrador == "") return;
    
    var url = "ajax.php?code=4416&cobr="+idCobrador;
    
    get(url, function(data){
        if(data.code == "0"){
            var regioes = data.data;
            for(i = 0; i < regioes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = regioes[i].nome; 
                op.value = regioes[i].id;
                $("#regiao-localidade").append( op );
            }
            if(waitRegiao){
                $("#regiao-localidade").val(waitRegiao);
                waitRegiao = false;
            }
        }
    });
}
function loadRotas(idRegiao){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Rota";
    $("#rota-localidade").html( opDefault );
    
    if(idRegiao == "") return;
    
    var url = "ajax.php?code=4516&regi="+idRegiao;
    
    get(url, function(data){
        if(data.code == "0"){
            var regioes = data.data;
            for(i = 0; i < regioes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = regioes[i].nome; 
                op.value = regioes[i].id;
                $("#rota-localidade").append( op );
            }
            if(waitRota){
                $("#rota-localidade").val(waitRota);
                loadPositions();
                waitRota = false;
            }
        }
    });
}
</script>
<?php } ?>
