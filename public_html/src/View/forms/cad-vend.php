<?php
include_once CONTROLLERS.'caixa.php';
$caixaController    = new CaixaController();
$caixa              = $caixaController->getCaixaAberto();
echo '<script>';
echo 'var time_caixa = new Date(\''.$caixa->data.'\').getTime();';
echo '</script>';
?>
<form action="?op=add_vend" method="post" id="form-cad-vend" 
    onsubmit="return checkRequireds() && gerenteConfirmEditVenda();">
    <div class="tool-bar-form" form="form-cad-vend">
        <div onclick="openAddSpaceForm(this);" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this); GCEV = true;" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div> 
    <div class="hidden add-space-this-form">
        <div id="venda-space"> 
            <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
            <input type="hidden" name="consulta" id="consulta-venda" value=""/>
            <table cellspacing="5" class="center" width="100%"> 
                <tr>
                    <td colspan="3" class="separted-info">
                        <p style="text-align: right">
                            <label id="reativar-venda" class="hidden"> 
                                <input type="checkbox" name="reativar" class="input notchecked" 
                                id="reativar-venda-input" onchange="confirmChange()"/>
                                Reativar Venda
                            </label>
                        </p>    
                        <fieldset>
                            <legend>&nbsp;DADOS GERAIS &nbsp;</legend>
                            <table style="width: 100%" cellspacing="10" class="subtable-cad-venda"> 
                                <tr>
                                    <td colspan=""> 
                                        <input type="hidden" name="cliente" id="cliente-venda" value=""/>
                                        <label> Cliente: <br/>
                                            <span id="cliente-nome-venda" class="cleanable" placeholder="Sem Cliente"> </span> 
                                        </label>
                                        <a href="javascript:;" onclick="buscarClientesServ()" title="Buscar Clientes"> <img src="<?php echo GRID_ICONS?>visualizar.png"/></a>
                                    </td>
                                    <td>
                                        <label> Localidade : <br/>
                                            <span id="cliente-localidade-venda" class="cleanable" placeholder="Sem Localidade"> </span>
                                        </label>
                                    </td>
                                    <td>
                                        <label> Endereço: <br/>
                                            <span id="cliente-endereco-venda" class="cleanable" placeholder="Sem Endereço"> </span> 
                                        </label>
                                    </td>
                                </tr>
                                <tr> 
                                    <td>
                                        <label> Data da Venda: <br/>
                                            <input type="date" class="input text-input" id="data-venda-venda" name="data-venda" 
                                            onblur="checaDatasVenda(true);loadPrestacoes(document.getElementById('loja-venda').value);" required/>
                                        </label>
                                    </td>
                                    <td>    
                                        <label> Previsão Entrega: <br/>
                                            <input type="date" class="input text-input" id="previsao-entrega-venda" name="previsao-entrega" onblur="checaDatasVenda(true)" required/>
                                        </label>
                                        <br/>
                                        <a href="javascript:;" style="font-size: 10pt;" class="hidden" id="recover-entrega-anchor"
                                           onclick="floatElement('data-entrega-space')" > Data Entrega </a>
                                        <div class="hidden parent-to-hide float-input" id="data-entrega-space"> 
                                            <label class="parent-to-hide"> Data da Entrega: <br/>
                                                <input type="date" class="input text-input" id="data-entrega-venda" name="data-entrega"/>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <label> Loja: <br/>
                                            <select name="loja" class="input select-input gray-grad-back bigger-input" 
                                            id="loja-venda" required
                                            onchange="loadVendedores(this.value); loadAgentesVenda(this.value); loadOS(this.value); loadPrestacoes(this.value)">	
                                                <option value=""> Selecione uma loja </option>	
                                                <?php
                                                include_once CONTROLLERS."loja.php";
                                                $loja_controller = new LojaController();
                                                $isWithFoerignValues = false;
                                                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
                                                    $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                                                else
                                                    $lojas = array(
                                                        $loja_controller->getLoja( $_SESSION[SESSION_LOJA_FUNC], $isWithFoerignValues)
                                                    );
                                                foreach($lojas as $loja){ ?>
                                                    <option value="<?php echo $loja->id; ?>"
                                                    <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                                                    ><?php echo $loja->sigla; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label> 
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label> Ordem de Serviço: <br/>
                                            <input type="hidden" name="ordem-servico" id="ordem-servico-venda">
                                            <span id="ordem-servico-view" class="cleanable" placeholder="Sem Ordem de Serviço"> Sem Ordem de Serviço </span>
                                        </label>
                                        &nbsp;
                                        <a href="javascript:;" title="Buscar por OS"
                                        onclick="floatElement('search-os-space'); 
                                        document.getElementById('search-os-field').focus();"><img src="<?php echo GRID_ICONS."visualizar_add.png"; ?>" width="16px"/></a>
                                        &nbsp;
                                        <a href="javascript:;" onclick="openCadOS()" style="font-size: 10pt;">(+)</a>
                                        <div class="hidden parent-to-hide float-input" id="search-os-space" style="z-index: 100;"> 
                                            <p style="text-align: center;" class="parent-to-hide"> 
                                                <input type="text" class="input text-input big-input" id="search-os-field"
                                                       onkeyup="searchOs(this.value)" placeholder="Nº DA OS" autocomplete="off"/>
                                            </p>
                                            <div id="content-search-os"> </div>
                                        </div>
                                    </td>
                                    <td>
                                        <label> Vendedor: <br/>
                                            <select name="vendedor" id="vendedor-venda" 
                                                    class="input select-input gray-grad-back bigger-input" required>
                                                <option value=""> Sem Vendedor </option>
                                            </select>
                                        </label>
                                    </td>
                                    <td>
                                        <label> Agente de Venda: <br/>
                                            <select name="agente" id="agente-venda" 
                                                    class="input select-input gray-grad-back bigger-input" required>
                                                <option value=""> Sem Agente </option>
                                            </select>
                                        </label>
                                    </td>     
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <td class="separted-info" colspan="3">
                        <fieldset id="fieldset-produtos">
                            <legend>&nbsp;PRODUTOS&nbsp;
                            <a href="javascript:;" title="Buscar por produtos"
                            onclick="floatElement('search-produto'); document.getElementById('search-produto-field').focus();"> 
                                <img src="<?php echo GRID_ICONS."visualizar_add.png"; ?>" width="16px"/>
                            </a>
                            </legend> 
                            <input type="hidden" name="produtos" id="produtos-venda" value=""/>    
                            <table id="table-product">
                                <thead> 
                                    <tr>
                                        <th> Código Produto </th>
                                        <th> Produto </th>
                                        <th> Valor Venda </th>
                                        <th> Desconto </th>
                                        <th> Valor Total </th>
                                    </tr>
                                </thead>
                                <tbody id="table-product-tbody" class="cleanable-space"> 
                                </tbody>
                            </table>
                        </fieldset>
                    </td>
                </tr>    
                <tr>
                    <td class="separted-info" colspan="3">
                        <fieldset id="fieldset-parcelamento">
                            <legend>&nbsp;PARCELAMENTO&nbsp;</legend>
                            <label> Valor Venda:
                                <input type="hidden" id="valor-venda" name="valor"/>
                                <span id="view-valor-span"> R$ <b class="cleanable"  placeholder="0,00"> 0,00 </b> </span>
                            </label>
                            <span class="h-separator"> &nbsp; </span>
                            <label> 
                                <input type="checkbox" name="entrada-recebida" id="entrada-recebida-venda" class="input" onchange="checkEntrada()"/>
                                Entrada
                            </label>
                            <span class="h-separator"> &nbsp; </span>
                            <label> <b>Valor Entrada</b>:
                                <input type="text" onblur="calcParcelas()" name="entrada" class="input text-input smaller-input" id="entrada-venda">
                            </label>
                            <p class="v-separator"> 
                                <label>                                   
                                    Tipo Pagamento.:
                                    <select name="tipo-pagamento-entrada" id="tipo-pagamento-entrada-venda"
                                    class="input select-input gray-grad-back">	
                                        <option value="">Selecione o tipo</option>
                                    <?php
                                    include_once CONTROLLERS.'tipoPagamento.php';
                                    $tipoController = new TipoPagamentoController();
                                    $tipos          = $tipoController->getAllTiposPagamento();
                                    foreach($tipos as $t){
                                        echo '<option value=\''.$t->id.'\'>'.$t->nome.'</option>';
                                    }
                                    ?>
                                    </select>
                                </label>
                                <span class="h-separator"> &nbsp; </span>
                                <label>
                                    Prestação de Conta:
                                    <select name="prestacao-conta-entrada" id="prestacao-conta-entrada-venda"
                                    class="input select-input gray-grad-back">	
                                        <option value="">Selecione Prest. Conta</option>
                                    </select>
                                </label>
                            </p>
                            <label>
                                Data Parcela 1:
                                <input type="date" class="input text-input" id="data-entrada-venda" 
                                name="data-entrada" onblur="checkDataParcelas()" style="width: 135px"/>
                            </label>
                            <label> Qtd. Parcelas:
                                <input type="number" min="1" step="1" onblur="calcParcelas()" required style="width:30px"
                                name="qtd-parcelas" class="input text-input requirable" id="qtd-parcelas-venda" />
                            </label>
                            <span class="h-separator"> &nbsp; </span>
                            <label> Val. Parcelas: 
                                <span id="valor-parcelas-preview"> R$ <b class="cleanable"  placeholder="0,00"> 0,00 </b> </span>
                            </label>
                            <span class="h-separator"> &nbsp; </span>
                            <label id="data-demais-label"> <b>Demais Parcelas</b>: 
                                <input type="date" class="input text-input requirable" style="width: 135px"
                                id="data-parcela-venda" name="data-parcela" onblur="checkDataParcelas()" required/>
                            </label>
                        </fieldset>
                    </td>
                </tr>                
            </table>
        </div>
        <div id="consulta-space">
            <fieldset>
                <legend>&nbsp;CONSULTA&nbsp;</legend>
                <?php include_once FORMS."cad-cons.php"; ?>
            </fieldset>
        </div>
        <p class="v-separator"> &nbsp; </p>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </div>
</form>
<!-- SEARCH SALES FILTER -->
<?php define("IS_VENDA", true);         ?>
<?php include_once FORMS."cad-lanc.php";?>
<!-- SEARCH SALES FILTER -->
<!-- FLOOAT DIV FOR PRODUCT -->
<div class="hidden parent-to-hide float-input" id="search-produto"> 
    <p style="text-align: center;" class="parent-to-hide"> 
        <input type="text" class="input text-input big-input" id="search-produto-field"
               onkeyup="searchProdutos(this.value)" placeholder="Descrição ou Código" autocomplete="off"/> 
    </p>
    <div id="content-search"> </div>
</div>
<!-- FLOOAT DIV FOR PRODUCT -->
<style>
#venda-space{
    float: left;
    width: 75%;
    height: 100%;
}
#consulta-space {
    float: left;
    width: 25%;
    height: 100%;
}
#form-cad-vend .subtable-cad-venda {width: 100%;}
#form-cad-vend .subtable-cad-venda td {text-align: left; width: 33%;}
#form-cad-vend .subtable-cad-venda .text-input{text-transform: uppercase;}
#form-cad-vend .subtable-cad-venda .select-input{ width: 100%; }
#table-product { width: 100% !important; }
#table-product thead th { 
    font-size: 9pt; 
    font-weight: bolder; 
    width: 19.5%; 
    text-align: center;
    background: gray;
    color: white;
}
#table-product-tbody td {
    width: 19.5% !important;
    font-size: 9pt;
    font-weight: bolder;
    text-align: center !important;
}
.result-search-produto {
    border-bottom:lightgray solid 1px;
    color:gray;
    font-size:10pt;
    margin-top:5px;
    padding:5px; 
    cursor: pointer;
}
.result-search-produto:hover{ background: #eee; }
.lock-edit {display: none; position: absolute; background: rgba(255,255,255,0.5);}
#cliente-venda-nome{ color: gray; font-size: 10pt; font-weight: bolder;}
#data-entrega-space {width: auto;}
#fieldset-parcelamento .h-separator{
    margin-left: 1px; margin-right: 1px;
}
</style>
<script src="script/mask.js"></script>
<script src="script/jquery.maskMoney.js"></script>
<script> 
$(function(){
    var obj = { thousands:'', decimal:',', allowZero: true, prefix: 'R$ ', affixesStay: false};
    $("#entrada-venda").maskMoney(obj);
    $("#loja-venda").change();
});    
var GCEV = true;
function gerenteConfirmEditVenda(){
    if(!GCEV){
        ACTION_AFTER = function(){ 
            GCEV = true;
            $("#form-cad-vend input[type='submit']").click();
        }
        openPasswordGerenteNeed();
    }
    return GCEV;
}    
function checkRequireds(){
    if($("#for-update-id").val() != "") return true;
    if(document.getElementById("cliente-venda").value == ""){
        badAlert("Venda precisa ter cliente");
        return false;
    }
    if(document.getElementById("produtos-venda").value == ""){
        badAlert("Venda precisa ter no minínimo um produto");
        return false;
    }
    var entradavalue = parseInt(floorMoney(document.getElementById("entrada-venda").value));
    var checked = document.getElementById("entrada-recebida-venda").checked;
    if(entradavalue == 0) {
        return checked? alert("Informe o valor da entrada") : alert("Informe o valor da primeira parcela");
    }
    if(!checked){
        var demais = new Date(document.getElementById("data-parcela-venda").value).getTime();
        var primeira = new Date(document.getElementById("data-entrada-venda").value).getTime();
        if(primeira > demais){
            alert("Data da primeira precisa ser antes das demais parcelas");
            return false;
        }
    } else {
        var t_venda  = new Date(document.getElementById('data-venda-venda').value).getTime();
        var selectTipo = document.getElementById('tipo-pagamento-entrada-venda');
        var txt_tipo = selectTipo.options[selectTipo.selectedIndex].innerHTML.toUpperCase();
        if(t_venda != time_caixa && (txt_tipo.indexOf('DINHEIRO') != -1) ){
            alert('Não existe um caixa diário aberto para essa data da venda');
            return false;
        }
    }
    if(!checaDatasVenda(false)){
        badAlert('Data da venda tem que ser menor do que a previsão');
        document.getElementById('data-venda-venda').focus();
        return false;
    }
    if(!checkDataParcelas()){
        badAlert('Data das demais parcelas tem que ser maior do que a data da primeira');
        document.getElementById('data-parcela-venda').focus();
        return false;
    }
    return confirm("Confirma o cadastro da venda?");
}
function buscarClientesServ(){
    var serv = "ajax.php?code=1230";
    openViewDataMode(serv);
}
var OS_TO_SEARCH = new Array();
function searchOs(txt){
    $("#content-search-os").html("");
    if(txt == "") return;
    txt = txt.toLowerCase();
    for(var i = 0; i < OS_TO_SEARCH.length; i++){
        var num = new String(OS_TO_SEARCH[i].numero);
        var id = new String(OS_TO_SEARCH[i].id);
        if(num.toLowerCase().indexOf(txt) != -1){
            var os_obj = "{numero: '"+num+"', id: "+id+"}";
            var os_row = "<p class='result-search-produto' title='Selecionar esta OS' onclick=\"osAdded("+os_obj+")\">";
            os_row += num;
            os_row += "</p>";
            $("#content-search-os").append(os_row);
        }        
   }   
}
function loadOS(lojaid){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Ordem de Serviço";
    $("#ordem-servico-venda").html( opDefault );
    if(lojaid == "") return;
    var url = "ajax.php?code=1118&loja="+lojaid;
    get(url, function(data){
       if(data.code == "0"){
            var oss = data.data;
            OS_TO_SEARCH = [];
            OS_TO_SEARCH.length = 0;
            for(var i = 0; i < oss.length; i++){
                var obj = {numero: oss[i].numero, id: oss[i].id};
                OS_TO_SEARCH.push(obj);
            }
       } else badAlert(data.message);
    });
}
function openCadOS(){
    var serv = "ajax.php?code=9001";
    openViewDataMode(serv);
}
function osAdded(ordem){
    document.getElementById("ordem-servico-venda").value = ordem.id;
    document.getElementById("ordem-servico-view").innerHTML = ordem.numero;
    var obj = {numero: ordem.numero, id: ordem.id};
    OS_TO_SEARCH.push(obj);
    closeViewDataMode();
}
function selectCliente(cliente_nome, cliente_localidade, 
                       cliente_endereco, cliente_id){
    $("#cliente-nome-venda").html(cliente_nome);
    $("#cliente-localidade-venda").html(cliente_localidade);
    $("#cliente-endereco-venda").html(cliente_endereco);
    $("#cliente-venda").val(cliente_id);
    $(".close-data-view").click();
}
function loadVendedores(idLoja){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Vendedor";
    $("#vendedor-venda").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=7776&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var vendedores = data.data;
            for(i = 0; i < vendedores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = vendedores[i].nome; 
                op.value = vendedores[i].id;
                $("#vendedor-venda").append( op );
            }
            if(waitVendedor){
                $("#vendedor-venda").val(waitVendedor);
                waitVendedor = false;
            }
        }
    });
}
function loadAgentesVenda(idLoja){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Agente";
    $("#agente-venda").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=7755&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var agentes = data.data;
            for(i = 0; i < agentes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = agentes[i].nome; 
                op.value = agentes[i].id;
                $("#agente-venda").append( op );
            }
            if(waitAgenteVendas){
                $("#agente-venda").val(waitAgenteVendas);
                waitAgenteVendas = false;
            }
        }
    });
}
var waitVendedor = false;
var waitAgenteVendas = false;
function openVendaTipoMode(idvenda){
    var url = "ajax.php?code=5577&vend="+idvenda;
    get(url, function(data){
        if(data.code == "0"){
            $("#form-cad-vend .requirable").attr("required",false);
            $("#recover-entrega-anchor").show();
            //CANCELAMENTO
            if(data.data.cancelada == "1"){
                $("#reativar-venda").show();
            }
            //DADOS GERIAS
            $("#for-update-id").val(data.data.id);
            $("#data-venda-venda").val(data.data.dataVenda);
            $("#previsao-entrega-venda").val(data.data.previsaoEntrega);
            $("#data-entrega-venda").val(data.data.dataEntrega);
            $("#loja-venda").val(data.data.loja);
            $("#consulta-venda").val(data.data.consulta.id);
            
            //PRODUTOS
            $("#table-product-tbody").html("");
            var produtos_venda = data.data.produtos;
            for(var i = 0; i < produtos_venda.length; i++){
                addProductSpace();
                for(var j = 0; j < produtos.length; j++){
                    if(produtos[j].id == produtos_venda[i].id){
                        var id = "produto-"+i;
                        var row = document.getElementById(id);
                        
                        var select = row.childNodes[0].childNodes[0];
                        select.value = j;
                        select.onchange();
                        
                        var fieldValue = row.childNodes[3].childNodes[0];
                        var desconto = produtos[j].valor - produtos_venda[i].valor_vendido;
                        fieldValue.value = floorMoney(desconto).replace(".", ",");
                        fieldValue.onblur();
                        
                        break;
                    }
                }
            }
            
            //PARCELAMENTO
            var parcelamento = data.data.parcelamento;
            if(parcelamento.com_entrada){
                var valor = floorMoney(parcelamento.valor_entrada).replace(".", ",");
                $("#entrada-venda").val(valor);                
            } else {
                var valor = floorMoney(parcelamento.p_parcela.valor).replace(".", ",");
                $("#data-entrada-venda").val(parcelamento.p_parcela.data);
                $("#entrada-venda").val(valor);
            }
            $("#entrada-recebida-venda").attr("checked",parcelamento.com_entrada);
            checkEntrada();
            $("#qtd-parcelas-venda").val(parcelamento.q_parcelas).blur();
            $("#data-parcela-venda").val(parcelamento.data_demais);
            
            waitVendedor = data.data.vendedor;
            waitAgenteVendas = data.data.agenteVendas;
            
            osAdded(data.data.os);
            
            loadVendedores(data.data.loja);
            loadAgentesVenda(data.data.loja);
            
            var cliente = data.data.cliente;
            
            selectCliente(cliente.nome, cliente.localidade, cliente.endereco, cliente.id);
            
            loadFormConsulta(data.data.consulta);
            
            GCEV = false;
            
            openAddSpaceForm(document.getElementById("add-btn-tool"), true);
            
        } else badAlert(data.message);
    });
}
function delVenda(idvenda){
    window.location = "?op=del_vend&vend="+idvenda;
}
function checaDatasVenda(sync){
    var dtVenda = new Date($("#data-venda-venda").val());
    var dtEntrega = new Date($("#previsao-entrega-venda").val());
    if(dtEntrega.getTime() < dtVenda.getTime()){
        $("#previsao-entrega-venda").addClass("invalid-input");
        $("#data-venda-venda").addClass("invalid-input");
        return false;
    } else { 
        $("#previsao-entrega-venda").removeClass("invalid-input");
        $("#data-venda-venda").removeClass("invalid-input");
        if(sync)
            $("#data-entrada-venda").val($("#previsao-entrega-venda").val());
        return true;
    }
}
function checkDataParcelas(){
    if(document.getElementById("entrada-recebida-venda").checked) return true;
    var dtDemias = new Date($("#data-parcela-venda").val());
    var dtPrimeira = new Date($("#data-entrada-venda").val());
    if(dtDemias.getTime() <= dtPrimeira.getTime()){
        $("#data-parcela-venda").addClass("invalid-input");
        $("#data-entrada-venda").addClass("invalid-input");
        return false;
    } else { 
        $("#data-parcela-venda").removeClass("invalid-input");
        $("#data-entrada-venda").removeClass("invalid-input");
        return true;
    }
}
function calcParcelas(){
    var qtdParcelas = $("#qtd-parcelas-venda").val();
    if(qtdParcelas == "0" || qtdParcelas == ""){
        $("#qtd-parcelas-venda").val(1);   
        qtdParcelas = 1;
    } 
    var entrada = parseFloat(floorMoney($("#entrada-venda").val()));
    var valor = ( ($("#valor-venda").val() - entrada) / qtdParcelas) + "";
    valor = valor.replace(".",",");
    var idx = valor.indexOf(",");
    if(idx != -1) valor = valor.substr(0, idx+3);
    else valor += ",00"; 
    $("#valor-parcelas-preview b").html(valor);
    if(valor == "0,00") $("#data-demais-label").hide().
                        children("#data-parcela-venda").attr("required", false);
    else $("#data-demais-label").show().
         children("#data-parcela-venda").attr("required", true);
}
function checkEntrada(){
    var checked = document.getElementById("entrada-recebida-venda").checked;
    
    var dataEntrada = document.getElementById("data-entrada-venda");
    if(checked) $(dataEntrada.parentNode).hide();
    else $(dataEntrada.parentNode).show();
    $("#data-entrada-venda").attr("required", !checked);
    
    var entradaVenda = document.getElementById("entrada-venda");
    entradaVenda.parentNode.getElementsByTagName("b")[0].innerHTML = checked ? "Valor Entrada" :  "Valor Parcela 1";
    
    document.getElementById("data-parcela-venda").parentNode.getElementsByTagName("b")[0].innerHTML = (
        !checked ? "Demais Parcelas" : "Data 1ª Parcela" 
    );
    var selectTipo = document.getElementById('tipo-pagamento-entrada-venda');
    selectTipo.parentNode.style.display = checked ? 'inline' : 'none';
    selectTipo.required = checked;
    var selectPrest = document.getElementById('prestacao-conta-entrada-venda');
    selectPrest.parentNode.style.display = checked ? 'inline' : 'none';
    selectPrest.required = checked;
    if(checked){ loadPrestacoes(document.getElementById('loja-venda').value); }
}
function changeDataEntrega(id_venda){
    var serv = "ajax.php?code=9733&venda="+id_venda;
    openViewDataMode(serv);
}
<?php
include_once CONTROLLERS."produto.php";
$controller_prod = new ProdutoController();
$produtos = $controller_prod->getAllProdutos();
?>
var produtos = new Array();
<?php
foreach($produtos as $p){
    $desc = str_replace("\n", " ", addslashes($p->descricao));
    echo "produtos.push({id:{$p->id},codigo:{$p->codigo}, descricao:\"{$desc}\", valor:{$p->precoVenda}, minimo:{$p->precoVendaMin}});\n";        
}
?>
function getSelectProdutos(produtoRow){
    var select = "<select class='product-inlist input select-input gray-grad-back' ";
    select += "onchange=\"selectProdutoFromRow('"+produtoRow+"')\">";
    select += "<option value='-1'> Nenhum </option>";
    <?php
    $count = 0;
    foreach($produtos as $p){
        echo "select += \"<option value='$count'>{$p->codigo}</option>\";\n";
        $count++;
    }
    ?>
    select += "</select>";
    return select;
}
function clearProdutoRow(idProdutoRow){
    var row = document.getElementById(idProdutoRow);
    row.childNodes[1].innerHTML = "";
    row.childNodes[2].innerHTML = "";
    row.childNodes[4].innerHTML = "";
    row.childNodes[3].childNodes[0].value = "";
    recalc(idProdutoRow);
}
function selectProdutoFromRow(idProdutoRow){
    var row = document.getElementById(idProdutoRow);
    var idx = row.childNodes[0].childNodes[0].value;
    if(idx == -1) {
        clearProdutoRow(idProdutoRow);
    } else {
        var produto = produtos[idx];
        row.childNodes[1].innerHTML = produto.descricao;
        row.childNodes[2].innerHTML = toMoney(produto.valor);
        row.childNodes[3].childNodes[0].value = "";
        row.childNodes[4].innerHTML = toMoney(produto.valor);
    }
    recalc(idProdutoRow);
}
function calcDesconto(idProdutoRow, input){
    var row = document.getElementById(idProdutoRow);
    var idx = row.childNodes[0].childNodes[0].value;
    if(idx == -1) return clearProdutoRow(idProdutoRow);
    var produto = produtos[idx];
    var desconto = floorMoney(input.value);
    if(desconto == "") desconto = 0;
    var valor = produto.valor - parseFloat(desconto);
    row.childNodes[4].innerHTML = toMoney(valor);
    recalc(idProdutoRow);
}
function calcTotal(idProdutoRow){
    var total = 0;
    var table = document.getElementById("table-product-tbody");
    var count = table.rows.length;
    for(var i = 0; i < count; i++){
        var row = document.getElementById("produto-"+i);
        if(!row) continue;
        var idx = row.childNodes[0].childNodes[0].value;
        if(idx == -1) continue;
        var produto = produtos[idx]; 
        var desconto = floorMoney(row.childNodes[3].childNodes[0].value);
        if(desconto == "") desconto = 0; 
        var valor = produto.valor - parseFloat(desconto);
        total += valor;
    }
    document.getElementById("valor-venda").value = total;
    $("#view-valor-span b").html( toMoney(total) );
}
function addProductSpace(){
    var table = document.getElementById("table-product-tbody");
    var count = table.rows.length;
    var row = table.insertRow(-1);
    var idProdutoRow = "produto-"+count;
    row.id = idProdutoRow;
    var colCod = row.insertCell(0);
    colCod.innerHTML = getSelectProdutos(idProdutoRow);
    row.insertCell(1); row.insertCell(2);
    var colDesconto = row.insertCell(3);
    var input = "<input type='text' class='input text-input medium-input' onblur=\"calcDesconto('"+idProdutoRow+"', this)\"/>";
    colDesconto.innerHTML = input;
    $(colDesconto).ready(function(){
        var obj = { thousands:'', decimal:',', allowZero: true, prefix: 'R$ ', affixesStay: false};
        $(colDesconto).children("input").maskMoney(obj);
    });
    row.insertCell(4);
    var removeAnchor = document.createElement("a");
    removeAnchor.href = "javacript:void(0)";
    removeAnchor.setAttribute("onclick","removeProductRow('"+idProdutoRow+"');");
    removeAnchor.innerHTML = "<img src='images/tool-icons/del.png'/>";
    removeAnchor.style.color = "red";
    row.insertCell(5).appendChild(removeAnchor);
}
function removeProductRow(idProductRow){
    $("#"+idProductRow).remove();
    recalc(idProductRow);
}
function checkIdsProdutos(){
    var ids = new Array();
    var table = document.getElementById("table-product-tbody");
    var count = table.rows.length;
    for(var i = 0; i < count; i++){
        var row = document.getElementById("produto-"+i);
        if(!row) continue;
        var idx = row.childNodes[0].childNodes[0].value;
        if(idx == -1) continue;
        var produto     = produtos[idx]; 
        var desconto    = floorMoney(row.childNodes[3].childNodes[0].value);
        if(desconto == "") desconto = "0";
        var total = produto.valor - parseFloat(desconto);
        var str = produto.id+":"+total;
        ids.push(str);
    }
    document.getElementById("produtos-venda").value = ids.join(",");
}
function searchProdutos(txt){
    $("#content-search").html("");
    if(txt == "") return;
    txt = txt.toLowerCase();
    for(var i = 0; i < produtos.length; i++){
        var desc = produtos[i].descricao+"";
        var code = produtos[i].codigo+"";
        if(desc.toLowerCase().indexOf(txt) != -1 || 
           code.toLowerCase().indexOf(txt) != -1 ){
            var prod = "<p class='result-search-produto' title='Adicionar este produto' onclick='selectProdutoOfSearch("+i+")'>";
            prod += desc+" ("+code+")";
            prod += "</p>";
            $("#content-search").append(prod);
        }        
    }
}
function selectProdutoOfSearch(idx){
    addProductSpace();
    var table = document.getElementById("table-product-tbody");
    var select = table.rows[table.rows.length-1].childNodes[0].childNodes[0];
    select.value = idx;
    select.onchange();
}
function confirmChange(){
    var checked = document.getElementById("reativar-venda-input").checked;
    if(checked){
        checked = confirm("Deseja realmente reativar venda?");
         document.getElementById("reativar-venda-input").checked = checked;
    }
}
function recalc(idProductRow){
    calcTotal(idProductRow);
    checkIdsProdutos();
    calcParcelas();
}
var waitPrestacao = false;
function loadPrestacoes(idLoja){
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Selecione Prest. Conta";
    $("#prestacao-conta-entrada-venda").html( opDefault );
    
    if(idLoja == "") return;
    var dt_venda = $('#data-venda-venda').val();
    if(dt_venda == '') return;

    var url = "ajax.php?code=5508&loja="+idLoja+"&dt-venda="+dt_venda;
    
    get(url, function(data){
        if(data.code == "0"){
            var prestacoes = data.data;
            for(i = 0; i < prestacoes.length; i++){
                var op = document.createElement("option");
                op.innerHTML = prestacoes[i].nome; 
                op.value = prestacoes[i].id;
                $("#prestacao-conta-entrada-venda").append( op );
            }
            if(waitPrestacao){
                $("#prestacao-conta-entrada-venda").val(waitPrestacao);
                waitPrestacao = false;
            }
        }
    });
}
checkEntrada();
</script>
