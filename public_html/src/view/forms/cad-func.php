<?php 
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
$config = Config::getInstance(); 
?>
<form action="?op=add_func" method="post" id="form-cad-func">
    <div class="tool-bar-form" form="form-cad-func">
        <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre o funcionário&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <table cellspacing="20" class="center">
            <tr>
                <td colspan="2"> 
                    <label> Nome: <br/>
                        <input type="text" class="input text-input" id="nome-funcionario" name="nome" required/>
                    </label>
                </td>
                <td> 
                    <label> CPF: <br/>
                        <input type="text" class="input text-input" id="cpf-funcionario" name="cpf" required onkeypress="MascaraCPF(this)" maxlength="14"/>
                    </label>
                </td>
                <td> 
                    <label> RG: <br/>
                        <input type="text" class="input text-input" id="rg-funcionario" name="rg" required/>
                    </label>
                </td>
            </tr>
            <tr>
                <td> 
                    <label> Cargo: <br/>
                        <select name="cargo" class="input select-input gray-grad-back" id="cargo-funcionario" required>	
                            <option value=""> Selecione um cargo </option>	
                            <?php
                            $cargos = $config->currentController->getAllCargos();
                            foreach($cargos as $cargo){ ?>
                                <option value="<?php echo $cargo->id; ?>"><?php echo $cargo->nome;  ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </td>
                <td> 
                    <label> Loja: <br/>
                        <select name="loja" class="input select-input gray-grad-back" id="loja-funcionario" required>	
                            <option value=""> Selecione uma loja </option>	
                            <?php
                            include_once CONTROLLERS."loja.php";
                            $loja_controller = new LojaController();
                            $isWithFoerignValues = false;
                            $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                            foreach($lojas as $loja){ ?>
                                <option value="<?php echo $loja->id; ?>"><?php echo $loja->sigla;  ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </td>
                <td> 
                    <label> Email:
                        <input type="email" class="input text-input" id="email-funcionario" name="email"/>
                    </label>
                </td>
                <td> 
                    <label> Nascimento: <br/>
                        <input type="date" class="input text-input" id="nascimento-funcionario" name="nascimento"/>
                    </label>
                </td>
            </tr>
            <tr>
                <td> 
                    <label> Cidade: <br/>
                    <select name="cidade" class="input select-input gray-grad-back" id="cidade-funcionario">	
                        <option value=""> Selecione uma cidade </option>
                        <?php
                        include_once CONTROLLERS."regiao.php";
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
                </td>
                <td> </td>
                <td> 
                    <label> Admissao: <br/>
                        <input type="date" class="input text-input" id="admissao-funcionario" name="admissao"/>
                    </label>
                </td>
                <td> 
                    <label> Demissao: <br/>
                        <input type="date" class="input text-input" id="demissao-funcionario" name="demissao"/>
                    </label>
                </td>
            </tr>
            <tr>
                <td colspan="2"> 
                    <label> Endereço: </label> <br/>
                    <select onclick="adjustPrefix('rua-funcionario')" onchange="adjustPrefix('rua-funcionario')" 
                            class="input select-input gray-grad-back small-input"
                            id="prefix-end"> 
                        <option value=""> RUA </option>
                        <option> AV. </option>
                        <option> ROD. </option>
                    </select>
                    <input type="text" class="input text-input" id="rua-funcionario" name="rua"/>
                </td>
                <td>
                    <label> Número: <br/>
                        <input type="text" class="input text-input medium-input" id="numero-funcionario" name="numero"/>
                    </label>
                </td>  
                <td> 
                    <label> Bairro:<br/>
                        <input type="text" class="input text-input" id="bairro-funcionario" name="bairro"/>
                    </label>
                </td>
            </tr>
            <tr>
                <td> 
                    <label> CEP:<br/>
                        <input type="text" class="input text-input" id="cep-funcionario" name="cep" onkeypress="MascaraCEP(this)" maxlength="10"/>
                    </label>
                </td>
                <td> 
                    <label> CPT:<br/>
                        <input type="text" class="input text-input" id="cpt-funcionario" name="cpt"/>
                    </label>
                </td>
                <td colspan="2"> 
                    <label> Referencia:<br/>
                        <input type="text" class="input text-input" id="referencia-funcionario" name="referencia"/>
                    </label>
                </td>
            </tr>
            <tr>
                <td> 
                    <label> Banco:<br/>
                        <input type="text" class="input text-input" id="banco-funcionario" name="banco"/>
                    </label>
                </td>
                <td> 
                    <label> Agência:<br/>
                        <input type="text" class="input text-input medium-input" id="agencia-funcionario" name="agencia"/>
                    </label>
                </td>
                <td> 
                    <label> Conta:<br/>
                        <input type="text" class="input text-input medium-input" id="conta-funcionario" name="conta"/>
                    </label>
                </td>
                <td id="td-reativar" class="hidden"> 
                    <label> Reativar Conta 
                        <input type="checkbox" name="reativar" id="reativar-funcionario" class="input notchecked"
                         onchange="if(this.checked) this.checked = confirm('Confirma reativação?');"/>
                    </label>
                </td>
            </tr>
            <tr>
                <td> 
                    <label> Login: <br/>
                        <input type="text" class="input text-input" id="login-funcionario" name="login" onblur="checaLogin()"/>
                    </label>
                </td>
                <td> 
                    <label> Senha: <br/>
                        <input type="password" class="input text-input" id="senha-funcionario" name="senha" onblur="checaSenha()"/>
                    </label>
                </td>
                <td> 
                    <label> Confirmar Senha: <br/>
                        <input type="password" class="input text-input" id="confirma-senha-funcionario" onblur="checaSenha()"/>
                    </label>
                </td>
                <td> 
                    <label> Perfil: <br/>
                        <select disabled name="perfil" class="input select-input gray-grad-back" id="perfil-funcionario">	
                            <option value=""> Selecione um perfil </option>	
                            <?php
                            $noAdmin = true;
                            if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
                                $noAdmin = false;
                            $perfis = $config->currentController->getAllPerfis($noAdmin);
                            foreach($perfis as $perfil){ ?>
                                <option value="<?php echo $perfil->id; ?>"><?php echo $perfil->nome; ?></option>
                            <?php } ?>

                        </select>
                    </label>
                </td>
            </tr>
            <tr> 
                <td colspan="4"> 
                    <label> Telefones : </label>
                    <br/><br/>
                    <div id="telefones-space">
                        <div class="telefone-row"> 
                            <input type="text" class="input text-input" maxlength='13' name="telefone-1" id="telefone-1" style="width:70%" onkeypress="MascaraTelefone(this)"/>
                            <span class="h-separator"> &nbsp; </span>
                            <span id="add-telefone" onclick="addTelefone()"> + </span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" id="submit-form" value="Cadastrar"/>
        </p>
    </fieldset>
    </div>
</form>
<style>
#form-cad-func table {width: 90%}
#form-cad-func table td {text-align: left; width: 25%;}
#form-cad-func table .text-input{text-transform: uppercase;}
#form-cad-func table input[type="text"]{width: 100%;}
#form-cad-func table #rua-funcionario {width: 80%;}
#form-cad-func table input[type="password"]{width: 100%;}
#form-cad-func table input[type="email"]{width: 100%;}
#form-cad-func table select{width:100%;}
</style>
<script src="script/mask.js"></script>
<script src="script/telefones.js"></script>
<script>
function openEditFuncionarioMode(idfunc){
    var url = "ajax.php?code=6610&func="+idfunc;
    get(url, function(data){
        if(data.code == "0"){
            
            $("#for-update-id").val(data.data.id);
            $("#nome-funcionario").val(data.data.nome);
            $("#nascimento-funcionario").val(data.data.nascimento);
            $("#admissao-funcionario").val(data.data.admissao);
            $("#demissao-funcionario").val(data.data.demissao);
            $("#cidade-funcionario").val(data.data.cidade);
            $("#rua-funcionario").val(data.data.rua);
            $("#numero-funcionario").val(data.data.numero);
            $("#bairro-funcionario").val(data.data.bairro);
            
            $("#cep-funcionario").val(data.data.cep);
            $("#cep-funcionario").keypress();
            
            $("#cpf-funcionario").val(data.data.cpf);
            $("#cpf-funcionario").keypress();
            
            $("#rg-funcionario").val(data.data.rg);
            $("#email-funcionario").val(data.data.email);
            $("#cargo-funcionario").val(data.data.cargo);
            $("#loja-funcionario").val(data.data.loja);
            $("#perfil-funcionario").val(data.data.perfil);
            $("#login-funcionario").val(data.data.login);
            checaLogin();
            $("#cpt-funcionario").val(data.data.cpt);
            $("#referencia-funcionario").val(data.data.referencia);
            $("#banco-funcionario").val(data.data.banco);
            $("#agencia-funcionario").val(data.data.agencia);
            $("#conta-funcionario").val(data.data.conta);
            
            if(data.data.status == "0"){
                $("#td-reativar").show();
            }
            
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
function checaLogin(){
    var login = document.getElementById("login-funcionario").value.trim();
    document.getElementById("perfil-funcionario").disabled = (login == "");
    if(login == ""){ 
        checaSenha();
        document.getElementById("perfil-funcionario").value = "";
    }
}
function checaSenha(){
    
    if(!document.getElementById("perfil-funcionario").disabled) {
        
        var senha = $("#senha-funcionario");
        var confirm_senha = $("#confirma-senha-funcionario");

        if(senha.val().trim() == "" || confirm_senha.val().trim() == ""){
            $("#submit-form").addClass("disabled-btn");
            document.getElementById("submit-form").disabled = true;
            return;
        }

        if(senha.val().trim() != confirm_senha.val().trim()){
            senha.addClass("invalid-input");
            confirm_senha.addClass("invalid-input");
            $("#submit-form").addClass("disabled-btn");
            document.getElementById("submit-form").disabled = true;
        } else {
            senha.removeClass("invalid-input");
            confirm_senha.removeClass("invalid-input");
            $("#submit-form").removeClass("disabled-btn");
            document.getElementById("submit-form").disabled = false;
        }
    
    } else {
         $("#submit-form").removeClass("disabled-btn");
         document.getElementById("submit-form").disabled = false;
    }
}
</script>
<?php } ?>