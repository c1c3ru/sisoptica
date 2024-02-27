<form action="?op=add_repasse" method="post" id="form-cad-repasse" onsubmit="return checkSubmit();">
    <div class="tool-bar-form" form="form-cad-repasse">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre o repasse&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label> Dt. Chegada:
            <input type="date" class="input text-input" name="dt-chegada" id="dt-chegada-repasse" required/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Dt. Envio Conserto:
            <input type="date" class="input text-input" name="dt-envio-conserto" id="dt-envio-conserto-repasse" />
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Dt. Rec. Conserto:
            <input type="date" class="input text-input" name="dt-recebimento-conserto" id="dt-recebimento-conserto-repasse" />
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Dt. Envio Cliente:
            <input type="date" class="input text-input" name="dt-envio-cliente" id="dt-envio-cliente-repasse" />
        </label>
        <p class="v-separator"> &nbsp;</p>
        <label> Loja:
            <select name="loja" class="input select-input gray-grad-back" id="loja-repasse" onchange="loadCobradores(this.value)">	
                <option value=""> Selecione uma loja </option>	
                <?php
                include_once CONTROLLERS."loja.php";
                $loja_controller = new LojaController();
                $isWithFoerignValues = false;
                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
                    $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                } else {
                    $lojas = array($loja_controller->getLoja(
                            $_SESSION[SESSION_LOJA_FUNC], 
                            $isWithFoerignValues)
                        );
                }
                foreach($lojas as $loja){ ?>
                    <option value="<?php echo $loja->id; ?>"
                    <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                    ><?php echo $loja->sigla; ?></option>
                <?php } ?>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Cobrador:
            <select name="cobrador" id="cobrador-repasse" class="input select-input gray-grad-back small-input" required>
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
        <span class="h-separator"> &nbsp; </span>
        <label> Nº Venda:  
            <input type="text" name="venda" id="venda-repasse" class="input text-input smaller-input" onkeypress="mascaraInteiro()" onblur="loadVenda(this.value)"/>
            <a href="javascript:;" onclick="floatElement('search-venda');$('#search-venda-field').focus();" style="vertical-align: middle;">
                <img src="<?php echo GRID_ICONS.'visualizar.png'?>" title="Procurar por vendas"/>
            </a>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Observação:
            <textarea class="input text-input" name="observacao" id="observacao-repasse" rows="4"></textarea>
        </label>
        <p class="v-separator"> &nbsp;</p>
        <label class="lb-venda-data"> Dt. Venda: <span id="span-dt-venda">-</span> </label>
        <label class="lb-venda-data"> Cliente: <span id="span-cliente">-</span> </label>
        <label class="lb-venda-data"> End.: <span id="span-end">-</span> </label>
        <p style="text-align: right">
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>
    </div>
    <div class="hidden parent-to-hide float-input" id="search-venda"> 
        <p style="text-align: center;" class="parent-to-hide"> 
            <input type="text" class="input text-input big-input" id="search-venda-field"
            onkeyup="searchVenda(this.value)" placeholder="Nome do Cliente" autocomplete="off"/> 
        </p>
        <div id="content-search"> </div>
    </div>
</form>
<style>
#form-cad-repasse .text-input{text-transform: uppercase;}
.lb-venda-data {
    margin-right: 10px;
}
.lb-venda-data span {
    font-weight: bolder;
    color: gray;
}
.result-search-venda {
    border-bottom:lightgray solid 1px;
    color:gray;
    font-size:10pt;
    margin-top:5px;
    padding:5px; 
    cursor: pointer;
}
.result-search-venda:hover{ background: #eee; }
#form-cad-repasse label a {
    text-decoration: none;
}
#search-venda{
    z-index: 100;
}
</style>
<script src="script/mask.js"></script>
<script>
function checkSubmit(){
    var dtChegada = document.getElementById('dt-chegada-repasse');
    var dtEnvCons = document.getElementById('dt-envio-conserto-repasse');
    var dtRecCons = document.getElementById('dt-recebimento-conserto-repasse');
    var dtEnvClie = document.getElementById('dt-envio-cliente-repasse');
    if(dtEnvCons.value.length > 0){ 
        if(getTime(dtEnvCons) < getTime(dtChegada)){
            badAlert("Data de envio para o conserto deve ser posterior a data de chegada");
            return false;
        }
        if(dtRecCons.value.length > 0){
            if(getTime(dtRecCons) < getTime(dtEnvCons)){
                badAlert("Data de recebimento do conserto deve ser posterior a data de envio para o conserto");
                return false;
            }
            if(dtEnvCons.value.length > 0){
                if(getTime(dtEnvClie) < getTime(dtRecCons)){
                    badAlert("Data de envio para o cliente deve ser posterior a data de recebimento do conserto");
                    return false;
                }
            } else {
                dtEnvClie.value = "";
                return true;
            }
        } else {
            dtRecCons.value = "";
            dtEnvClie.value = "";
            return true;
        }
    } else {
        dtEnvCons.value = "";
        dtRecCons.value = "";
        dtEnvClie.value = "";
        return true;
    }
    return true;
}
function getTime(input){
    return new Date(input.value).getTime();
}
var waitCobrador = false;
function loadVenda(idVenda){
    if(!idVenda) {
        $("#span-dt-venda").html("");
        $("#span-cliente").html("");
        $("#span-end").html("");
        return;
    }
    var url = "ajax.php?code=5577&resum=1&vend="+idVenda;
    get(url, function(data){
        if(data.code == "0"){
            $("#span-dt-venda").html(data.data.dataVenda);
            $("#span-cliente").html(data.data.cliente.nome);
            $("#span-end").html(data.data.cliente.endereco);
        } else {
            $("#span-dt-venda").html("");
            $("#span-cliente").html("");
            $("#span-end").html("");
            badAlert(data.message);
        }
    });
}
function loadCobradores(idLoja){
    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Cobrador";
    $("#cobrador-repasse").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=4471&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var cobradores = data.data;
            for(i = 0; i < cobradores.length; i++){
                var op = document.createElement("option");
                op.innerHTML = cobradores[i].nome; 
                op.value = cobradores[i].id;
                $("#cobrador-repasse").append( op );
            }
            if(waitCobrador){
                $("#cobrador-repasse").val(waitCobrador);
                waitCobrador = false;
            }
        }
    });
    
}
function openEditRepasseMode(idRepasse) {
    if(!idRepasse) return;
    var url = "ajax.php?code=7570&repasse="+idRepasse;
    get(url, function(data){
        if(data.code == "0"){
            
            $("#dt-chegada-repasse").val(data.data.dtChegada);
            $("#dt-envio-conserto-repasse").val(data.data.dtEnvioConserto);
            $("#dt-recebimento-conserto-repasse").val(data.data.dtRecebimentoConserto);
            $("#dt-envio-cliente-repasse").val(data.data.dtEnvioCliente);
            $("#observacao-repasse").val(data.data.observacao);
            $("#venda-repasse").val(data.data.venda);
            loadVenda(data.data.venda);
            
            waitCobrador = data.data.cobrador;
            
            $("#loja-repasse").val(data.data.loja);
            $("#loja-repasse").change();
            
            $("#for-update-id").val(data.data.id);

            openAddSpaceForm(document.getElementById("add-btn-tool"), true);

        } else badAlert(data.message);
    });
}
function searchVenda(txt){
    if(!txt.length) return;
    txt = txt.toUpperCase();
    openLoadingInElement('#content-search');
    get("ajax.php?code=1991&nome="+txt, function(data){
        if(data.code == "0"){
            var vendas = data.data;
            var space = document.getElementById('content-search');
            space.innerHTML = "";
            for(var i = 0, l = vendas.length; i < l; i++){
                var venda = vendas[i];
                var p = document.createElement('p');
                p.className = "result-search-venda";
                p.innerHTML = venda.id + " - " + venda.cliente.replace(txt, "<b>"+txt+"</b>");
                p.onclick = function(){
                    document.getElementById('venda-repasse').value = venda.id;
                    loadVenda(venda.id);
                }
                space.appendChild(p);
            }
        } else badAlert(data.message);
    });
}
</script>