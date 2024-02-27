<?php  
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
?>
<form action="?op=add_loja" method="post" id="form-cad-loja">
    <div class="tool-bar-form" form="form-cad-loja">
        <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre a loja&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id"/>
        <label> Sigla:
            <input type="text" class="input text-input" id="sigla-loja" name="sigla" maxlength="4" onblur="checaSigla()" required/>
            <span class="info-input"> Ex: FOR1 </span>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Cidade: 
            <select name="cidade" class="input select-input gray-grad-back" id="cidade-loja" required>	
                <option value=""> Selecione uma cidade </option>
                <?php
                include_once CONTROLLERS."regiao.php";
                $regiao_controller = new RegiaoController();
                $estados = $regiao_controller->getEstados();
                foreach ($estados as $estado) {
                ?>
                    <optgroup label="<?php echo $estado->sigla;?>">  
                <?php    
                    $cidades = $regiao_controller->getCidadesByEstado($estado->id);
                    foreach ($cidades as $cidade) {
                ?>
                    <option value="<?php echo $cidade->id;?>"><?php echo $cidade->nome; ?></option>
                <?php } ?>
                    </optgroup>
                <?php } ?>

            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> CEP:
            <input type="text" class="input text-input" id="cep-loja" name="cep" onkeypress="MascaraCEP(this)" maxlength="10" required/>
        </label>
        <p class="v-separator"> &nbsp; </p>
        <label> Endereço: </label>
        <span class="h-separator"> &nbsp; </span>
        <select onclick="adjustPrefix('rua-loja')" onchange="adjustPrefix('rua-loja')" 
                class="input select-input gray-grad-back"
                id="prefix-end"> 
            <option value=""> RUA </option>
            <option> AV. </option>
            <option> ROD. </option>
        </select>
        <span class="h-separator"> &nbsp; </span>
        <input type="text" class="input text-input" id="rua-loja" name="rua" style="width: 35%" required/>
        <span class="h-separator"> &nbsp; </span>
        <label> Número:
            <input type="text" class="input text-input" id="numero-loja" name="numero"/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Bairro:
            <input type="text" class="input text-input small-input" id="bairro-loja" name="bairro" style="width: 20%" required/>
        </label>
        <p class="v-separator"> &nbsp; </p>
        <label> Telefones: </label>
        <br/><br/>
        <div id="telefones-space">
            <div class="telefone-row"> 
                <input type="text" class="input text-input" name="telefone-1" maxlength="13" id="telefone-1" style="width:70%" onkeypress="MascaraTelefone(this)"/>
                <span class="h-separator"> &nbsp; </span>
                <span id="add-telefone" onclick="addTelefone()"> + </span>
            </div>
        </div>
        <p class="v-separator"> &nbsp; </p>
        <label> CNPJ:
            <input type="text" class="input text-input" id="cnpj-loja" name="cnpj" maxlength="18" onkeypress="MascaraCNPJ(this)" required/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> CGC:
            <input type="text" class="input text-input" id="cgc-loja" name="cgc" required/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label>  Gerente: 
            <select name="gerente" class="input select-input gray-grad-back" id="gerente-loja">	
                <option value=""> Sem gerente </option>	
                <?php
                include_once CONTROLLERS."funcionario.php";
                $func_controller = new FuncionarioController();
                $gerentes = $func_controller->getAllGerentes();
                foreach($gerentes as $gerente){
                    ?>
                    <option value="<?php echo $gerente->id; ?>"><?php echo $gerente->nome;  ?></option>
                    <?php
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
#form-cad-loja .text-input{ text-transform: uppercase; }
</style>
<script src="script/mask.js"></script>
<script src="script/telefones.js"></script>
<script>
function openEditLojaMode(idloja){
    var url = "ajax.php?code=9981&loja="+idloja;
    get(url, function(data){
        if(data.code == "0"){
            
            $("#for-update-id").val(data.data.id);
            
            $("#sigla-loja").val(data.data.sigla);
            
            $("#rua-loja").val(data.data.rua);
            $("#numero-loja").val(data.data.numero);
            $("#bairro-loja").val(data.data.bairro);
            
            $("#cep-loja").val(data.data.cep);
            $("#cep-loja").keypress();
            
            $("#cnpj-loja").val(data.data.cnpj);
            $("#cnpj-loja").keypress();
            
            $("#cidade-loja").val(data.data.cidade);
            $("#cgc-loja").val(data.data.cgc);
            $("#gerente-loja").val(data.data.gerente);
            
            var telefones = data.data.telefones;
            if(telefones.length > 0) {
                clearTelefones();
                var i = 0;
                while(true){
                    var idTel = "#telefone-"+(i+1);
                    $(idTel).val(telefones[i]);
                    $(idTel).keypress();
                    i++;
                    if(i < telefones.length) addTelefone();
                    else break;
                }
            }
            
            openAddSpaceForm(document.getElementById("add-btn-tool"), true);
        } else {
            badAlert(data.message);
        }
    });
}
function checaSigla(){
    var strsigla = $("#sigla-loja").val();
    if(strsigla.length != 4) return $("#sigla-loja").addClass("invalid-input");
    for(i = 0; i < 3; i++){
        if((strsigla.charCodeAt(i) < 65 || strsigla.charCodeAt(i) > 90) &&
           (strsigla.charCodeAt(i) < 97 || strsigla.charCodeAt(i) > 122))
           return $("#sigla-loja").addClass("invalid-input");
    }
    if(strsigla.charCodeAt(3) < 48 || strsigla.charCodeAt(3) > 57)
        return $("#sigla-loja").addClass("invalid-input");
    $("#sigla-loja").removeClass("invalid-input");
}
</script>
<?php } ?>