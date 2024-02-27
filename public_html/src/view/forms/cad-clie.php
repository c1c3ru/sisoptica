<?php $config = Config::getInstance(); ?>
<form action="?op=add_clie" method="post" id="form-cad-clie"
      <?php if(defined("MODE_AJAX")){ ?>
      onsubmit="addCliente(); return false;"
      <?php } ?>
      >
    <?php if(!defined("MODE_AJAX")){ ?>
    <div class="tool-bar-form" form="form-cad-clie">
        <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <?php } ?>
    <div class="<?php if(!defined("MODE_AJAX")){ ?>hidden<?php } ?> add-space-this-form">
    <fieldset> 
        <legend>&nbsp;INFORMAÇÕES SOBRE O CLIENTE&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <table cellspacing="20" class="center">
            <tr> 
                <td colspan="2"> 
                    <label> Nome: <br/>
                        <input type="text" class="input text-input" id="nome-cliente" name="nome" required/>
                    </label>
                </td>
                <td>
                    <label> Apelido: <br/>
                        <input type="text" class="input text-input" id="apelido-cliente" name="apelido"/>
                    </label>
                </td>
                <td> 
                    <label> Nascimento: <br/>
                        <input type="date" class="input text-input" id="nascimento-cliente" name="nascimento"/>
                    </label>
                </td>
            </tr>
            <tr> 
                <td> 
                    <label> RG: <br/>
                        <input type="text" class="input text-input" id="rg-cliente" name="rg" />
                    </label>
                </td>
                <td> 
                    <label> Órgão Emissor: <br/>
                        <input type="text" class="input text-input" id="orgao-emissor-cliente" name="orgao-emissor" style="width: 100px;"/>
                    </label>
                </td>
                <td> 
                    <label> CPF: <br/>
                        <input type="text" class="input text-input" id="cpf-cliente" name="cpf" onkeypress="MascaraCPF(this)" maxlength="14"/>
                    </label>
                </td>
                <td> 
                    <label> Conjugue: <br/>
                        <input type="text" class="input text-input" id="conjugue-cliente" name="conjugue" />
                    </label>
                </td>
            </tr>
            <tr> 
                <td colspan="2"> 
                    <label> Nome Pai: <br/>
                        <input type="text" class="input text-input" id="nome-pai-cliente" name="nome-pai" />
                    </label>
                </td>
                <td colspan="2"> 
                    <label> Nome Mãe: <br/>
                        <input type="text" class="input text-input" id="nome-mae-cliente" name="nome-mae" />
                    </label>
                </td>
            </tr>
            <tr>
                <td colspan="2"> 
                    <label> Endereço: </label> <br/>
                    <select onclick="adjustPrefix('endereco-cliente')" onchange="adjustPrefix('endereco-cliente')" 
                            class="input select-input gray-grad-back"
                            id="prefix-end"> 
                        <option value=""> RUA </option>
                        <option> AV. </option>
                        <option> ROD. </option>
                    </select>
                    <input type="text" class="input text-input big-input" id="endereco-cliente" name="endereco" required/>
                </td>
                <td> 
                    <label> Número: <br/>
                        <input type="text" class="input text-input" id="numero-cliente" name="numero" required/>
                    </label>
                </td>
                <td rowspan="2"> 
                    <label> Observação: <br/>
                        <textarea name="observacao" class="input text-input bigger-input" id="observacao-cliente" rows="6"></textarea>
                    </label>
                </td>
            </tr>
            <tr>
                <td  colspan="2"> 
                    <label> Referência: <br/>
                        <input type="text" class="input text-input" id="referencia-cliente" name="referencia" />
                    </label>
                </td>
                <td> 
                    <label> Bairro: <br/>
                        <input type="text" class="input text-input" id="bairro-cliente" name="bairro" required/>
                    </label>
                </td>
            </tr>
            <tr>
                <td> 
                    <label> Casa Própria: <br/>
                        <select name="casa-propria" id="casa-propria-cliente" class="input select-input gray-grad-back">
                            <option value=""> Não </option>
                            <option value="1"> Sim </option>
                        </select>
                    </label>
                </td>
                <td>
                    <label> Tempo casa própria: <br/>
                        <input type="text" class="input text-input" id="tempo-casa-propria-cliente" name="tempo-casa-propria" />
                    </label>
                </td>
                <td> 
                    <label> Renda Mensal: <br/>
                        <input type="number" pattern="[0-9]+([\.|,][0-9]+)?" step="0.01" min="00.00" class="input text-input" id="renda-cliente" name="renda" />
                    </label>
                </td>
            </tr>
            <tr> 
                <td> 
                    <label> Cidade: <br/>
                        <select class="input select-input gray-grad-back bigger-input" id="select-cidade" onchange="loadLocalidades(this.value)">	
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
                </td>
                <td> 
                    <label> Localidades: <br/>
                        <select name="localidade" class="input select-input gray-grad-back bigger-input" id="localidade-cliente" required>	
                            <option value=""> Selecione uma localidade </option>
                        </select>
                    </label>
                </td>
                <td class="hidden"> 
                    <label> Bloquear Cliente:
                        <input type="checkbox" name="bloqueado" id="bloqueado-cliente" class="input notchecked" 
                        onchange="if(this.checked) this.checked = confirm('Deseja realmente bloquear esse cliente?');"/>
                    </label>
                </td>
            </tr>
            <tr> 
                <td colspan="4"> 
                    <label> Telefones : </label>
                    <br/><br/>
                    <div id="telefones-space">
                        <div class="telefone-row"> 
                            <input type="text" class="input text-input" name="telefone-1" maxlength='13' id="telefone-1" style="width:70%" onkeypress="MascaraTelefone(this)"/>
                            <span class="h-separator"> &nbsp; </span>
                            <span id="add-telefone" onclick="addTelefone()"> + </span>
                        </div>
                    </div>
                </td>
            </tr>   
        </table>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>
    </div>
</form>
<?php if(!defined("MODE_AJAX")){ ?>
<form id="form-busca-clientes" method="post" action="index.php?op=cad-clie" onsubmit="return clearCPF();">
    <fieldset> 
        <legend>&nbsp;pesquisar por clientes&nbsp;</legend>
        <label> Nome: <input type="text" class="input text-input medium-input" name="nome" value="<?php echo $config->filter("nome");?>"/> </label>
        <span class="h-separator"> &nbsp; </span>
        <label> CPF: <input type="text" class="input text-input small-input" name="cpf-pesquisa" maxlength="14"
                    id="cpf-pesquisa" value="<?php echo $config->filter("cpf-pesquisa");?>" onkeypress="MascaraCPF(this)" /> </label>
        <input type="hidden" name="cpf" id="cpf-pesquisa-hidden" value=""/>
        <input type="submit" class="btn submit green3-grad-back" name="pesquisar-clientes" id="pesquisar-clientes" value="Pesquisar"/>
    </fieldset>    
</form>
<?php } ?>                
<script> 
function clearCPF(){
    var v = document.getElementById("cpf-pesquisa").value;
    var nv = v.replace(/\./g,'').replace(/\-/g,'');
    document.getElementById("cpf-pesquisa-hidden").value = nv;
    return true;
}
</script>
<style>
#form-cad-clie fieldset {width: 99%}
#form-cad-clie table {width: 85%}
#form-cad-clie table td {text-align: left; width: 25%;}
#form-cad-clie table input[type="text"]{width: 100%;}
#form-cad-clie #tempo-casa-propria-cliente { width: auto; }
#form-cad-clie table #endereco-cliente{width: 80%;}
#form-cad-clie table input[type="date"]{width: 100%;}
#form-cad-clie .text-input{ text-transform: uppercase; } 
#form-busca-clientes .text-input{ text-transform: uppercase; } 
#form-busca-clientes #pesquisar-clientes {float: right; margin-top: -5px;}
</style>
<script src="script/mask.js"></script>
<script src="script/telefones.js"></script>
<script>
function loadLocalidades(idcidade){
    var url= "ajax.php?code=8876&cidade="+idcidade;
    $("#localidade-cliente").html("<option value=''> Selecione uma localidade </option>");
    get(url, function(data){
       if(data.code == "0"){
           var localidades = data.data;
           for(i = 0; i < localidades.length; i++) {
                var option = document.createElement("option");
                option.value = localidades[i].id;
                option.innerHTML = localidades[i].nome;
                $("#localidade-cliente").append(option);
           }
           if(waitLocalidade){
               $("#localidade-cliente").val(waitLocalidade);
               waitLocalidade = false;
           }
       } 
    });
}
var waitLocalidade = false;
function openEditClienteMode(idcliente){
    var url = "ajax.php?code=4770&clie="+idcliente;
    get(url, function(data){
       if(data.code == "0"){
            $("#for-update-id").val(data.data.id);
            $("#nome-cliente").val(data.data.nome);
            $("#nascimento-cliente").val(data.data.nascimento);
            $("#apelido-cliente").val(data.data.apelido);
            $("#rg-cliente").val(data.data.rg);
            $("#orgao-emissor-cliente").val(data.data.orgaoEmissor);
            $("#cpf-cliente").val(data.data.cpf);
            $("#cpf-cliente").keypress();
            $("#conjugue-cliente").val(data.data.conjugue);
            $("#nome-pai-cliente").val(data.data.nomePai);
            $("#nome-mae-cliente").val(data.data.nomeMae);
            $("#endereco-cliente").val(data.data.endereco);
            $("#numero-cliente").val(data.data.numero);
            $("#bairro-cliente").val(data.data.bairro);
            $("#referencia-cliente").val(data.data.referencia);
            $("#casa-propria-cliente").val(data.data.casaPropria);
            $("#tempo-casa-propria-cliente").val(data.data.tempoCasaPropria);
            $("#observacao-cliente").val(data.data.observacao);
            $("#renda-cliente").val(data.data.rendaMensal);
           
            $("#select-cidade").val(data.data.cidade);
           
            waitLocalidade = data.data.localidade;
           
            loadLocalidades(data.data.cidade)
            
            $("#bloqueado-cliente").parent().parent().show();
            $("#bloqueado-cliente").attr("checked", data.data.bloqueado != "0");
            
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
<?php if(defined("MODE_AJAX")){?>
function addCliente(){
    var cliente = {
        "nome": $("#nome-cliente").val(),
        "nascimento": $("#nascimento-cliente").val(),
        "apelido": $("#apelido-cliente").val(),
        "rg": $("#rg-cliente").val(),
        "orgao-emissor": $("#orgao-emissor-cliente").val(),
        "cpf":$("#cpf-cliente").val(),
        "conjugue":$("#conjugue-cliente").val(),
        "nome-pai":$("#nome-pai-cliente").val(),
        "nome-mae":$("#nome-mae-cliente").val(),
        "endereco":$("#endereco-cliente").val(),
        "numero":$("#numero-cliente").val(),
        "bairro":$("#bairro-cliente").val(),
        "referencia":$("#referencia-cliente").val(),
        "casa-propria":$("#casa-propria-cliente").val(),
        "tempo-casa-propria":$("#tempo-casa-propria-cliente").val(),
        "observacao":$("#observacao-cliente").val(),
        "renda":$("#renda-cliente").val(),
        "localidade":$("#localidade-cliente").val()
    };
    for(var i = 0; i < telefones_count;i++){
        cliente["telefone-"+(i+1)] = $("#telefone-"+(i+1)).val();
    }
    var url = "ajax.php?code=7565";
    post(url, cliente, function(data){
        if(data.code == "0"){
            var cliente = data.data;
            openLoadingState();
            window.setTimeout(function(){
                endLoadingState();
                selectCliente(cliente.nome, cliente.localidade, cliente.endereco, cliente.id);
            }, 1000);
            
        } else badAlert(data.message);
    });
}
<?php } ?>
</script>