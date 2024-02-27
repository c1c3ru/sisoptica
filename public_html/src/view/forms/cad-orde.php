<?php $config = Config::getInstance(); ?>
<form action="?op=add_orde" method="post" id="form-cad-orde"
    <?php if(defined("MODE_AJAX")){ ?>
      onsubmit="addOS(); return false;"
    <?php } else { ?>
      onsubmit="return gerenteConfirmEditOS();"
    <?php } ?>  
    >
    <?php if(!defined("MODE_AJAX")){ ?>
    <div class="tool-bar-form" form="form-cad-orde">
        <div onclick="openAddSpaceForm(this); autoSetNumOS();" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this); GCEO = true;" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <?php } ?>
    <div class="<?php if(!defined("MODE_AJAX")){ ?>hidden<?php } ?> add-space-this-form">
    <fieldset>
        <legend>&nbsp;informações sobre a ordem de serviço&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <table class="center" cellspacing="20px"> 
            <tr> 
                <td> 
                    <label> Laboratório : 
                        <select name="laboratorio" class="input select-input gray-grad-back" id="laboratorio-ordem" required>	
                            <option value=""> Selecione um laboratório </option>
                            <?php
                            include_once CONTROLLERS."laboratorio.php";
                            $controller_lb = new LaboratorioController();
                            $labs = $controller_lb->getAllLaboratorios();
                            foreach ($labs as $lab){
                                echo "<option value='{$lab->id}'";
                                if($lab->principal) echo " selected ";
                                echo "> {$lab->nome} </option>";
                            }
                            ?>
                        </select>
                    </label>
                </td>
                <td> 
                    <label> Loja:
                        <select name="loja" class="input select-input gray-grad-back" id="loja-ordem" required onchange="autoSetNumOS();">	
                            <option value=""> Selecione uma loja </option>	
                            <?php
                            include_once CONTROLLERS."loja.php";
                            $loja_controller = new LojaController();
                            $isWithFoerignValues = false;
                            if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
                                $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                            else
                                $lojas = array($loja_controller->getLoja(
                                        $_SESSION[SESSION_LOJA_FUNC], 
                                        $isWithFoerignValues)
                                    );
                            foreach($lojas as $loja){ ?>
                                <option value="<?php echo $loja->id; ?>"
                                <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                                ><?php echo $loja->sigla;  ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </td>
                <td> </td>
            </tr>
            <tr> 
                <td> 
                    <label>
                        Número da Ordem : 
                        <input type="hidden" name="numero" id="numero-ordem-hidden"/>
                        <span class="input-info" id="sigla-loja-label"> () </span>
                        <input type="text" class="input text-input small-input" id="numero-ordem" 
                        required onkeypress="mascaraInteiro()" onblur="managerNumOs()"/>			
                    </label>
                </td>
                <td> 
                    <label>
                        Data de Envio : 
                        <input type="date" class="input text-input" id="data-envio-lab-ordem" name="data-envio-lab" required/>			
                    </label>
                </td>
                <td> 
                    <label>
                        <input type="checkbox" class="input checked" id="armacao-loja-ordem" checked name="armacao-loja"/>	 
                        &nbsp;	
                        Armação da Loja
                    </label>
                </td>
            </tr>
            <tr> 
                <td>
                    <?php if( ( $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ||
                                $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_GERENTE ) && 
                                !defined("MODE_AJAX") ){ ?>
                    <label> Valor: 
                        <input type="text" class="input text-input" id="valor-ordem" name="valor"/>
                    </label>
                    <?php } ?>    
                </td>
                <td> 
                    <?php if(!defined("MODE_AJAX")){ ?>
                    <label>
                        Data de Recebimento : 
                        <input type="date" class="input text-input" id="data-recebimento-lab-ordem" name="data-recebimento-lab" onblur="checaDatasOS()"/>			
                    </label>
                    <?php } ?>
                </td>
                <td> 
                    <label id="reativar-ordem" class="hidden">
                        <input type="checkbox" class="input notchecked" name="reativar" id="reativar-ordem-input" onchange="confirmChange()"/>	 
                        Reativar
                    </label>
                </td>
            </tr>
        </table>
        <p class="v-separator"> &nbsp; </p>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>    
    </div>
</form>
<?php if(!defined("MODE_AJAX")){ ?>
<form id="form-busca-ordem" method="post" action="index.php?op=cad-orde" >
    <fieldset> 
        <legend>&nbsp;pesquisar por ordens de serviço&nbsp;</legend>
        <label> Lab.: 
            <select name="ordem_servico_id_laboratorio" class="input select-input gray-grad-back smaller-input">	
                <option value=""> TODOS </option>
                <?php
                include_once CONTROLLERS."laboratorio.php";
                $controller_lb = new LaboratorioController();
                $labs = $controller_lb->getAllLaboratorios();
                if(is_null($config->filter("ordem_servico_id_laboratorio"))){
                    foreach ($labs as $lab){
                        echo "<option value='{$lab->id}'";
                        if($lab->principal) echo " selected ";
                        echo "> {$lab->nome} </option>";
                    }
                } else {
                    $selected = (int)$config->filter("ordem_servico_id_laboratorio");
                    foreach ($labs as $lab){
                        echo "<option value='{$lab->id}'";
                        if((int)$lab->id == $selected) echo " selected ";
                        echo "> {$lab->nome} </option>";
                    }
                }
                ?>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Loja: 
            <select name="ordem_servico_id_loja" class="input select-input gray-grad-back smaller-input" value="<?php echo $config->filter("ordem_servico_id_loja");?>">	
                <option value=""> TODAS </option>	
                <?php
                include_once CONTROLLERS."loja.php";
                $loja_controller = new LojaController();
                $isWithFoerignValues = false;
                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR)
                    $lojas = $loja_controller->getAllLojas($isWithFoerignValues);
                else
                    $lojas = array($loja_controller->getLoja(
                            $_SESSION[SESSION_LOJA_FUNC], 
                            $isWithFoerignValues)
                        );
                if(is_null($config->filter("ordem_servico_id_loja"))){
                    foreach($lojas as $loja){ ?>
                        <option value="<?php echo $loja->id; ?>"
                        <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                        ><?php echo $loja->sigla;  ?></option>
                    <?php }
                } else {
                    $selected = (int) $config->filter("ordem_servico_id_loja");
                    foreach($lojas as $loja){ ?>
                        <option value="<?php echo $loja->id; ?>"
                        <?php if($loja->id == $selected) echo "selected"; ?>
                        ><?php echo $loja->sigla;  ?></option>
                    <?php } 
                } ?>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Nº Ordem: <input type="text" class="input text-input smaller-input" name="ordem_servico_numero" value="<?php echo $config->filter("ordem_servico_numero");?>"/> </label>
        <span class="h-separator"> &nbsp; </span>
        <label>Envio: <input type="date" class="input text-input" name="ordem_servico_data_envio_lab" value="<?php echo $config->filter("ordem_servico_data_envio_lab");?>"/> </label>
        <span class="h-separator"> &nbsp; </span>
        <label>Recebimento: <input type="date" class="input text-input " name="ordem_servico_data_recebimento_lab" value="<?php echo $config->filter("ordem_servico_data_recebimento_lab");?>"/> </label>
        <input type="submit" class="btn submit green3-grad-back" name="pesquisar-ordens" id="pesquisar-ordens" value="Pesquisar"/>
    </fieldset>    
</form>
<?php } ?>
<style>
#form-cad-orde table td {text-align: right;}
#form-cad-orde table .input {text-transform: uppercase;}
#form-busca-ordem .input {text-transform: uppercase;}
#form-busca-ordem #pesquisar-ordens {float: right;}
</style>
<script src="script/mask.js"> </script>
<?php if(!defined("MODE_AJAX")){?>
<script src="script/jquery.maskMoney.js"></script>
<?php } ?>
<script>
var GCEO = true;
function gerenteConfirmEditOS(){
    if(document.getElementById("numero-ordem-hidden").value.indexOf("-") == -1 ||
       document.getElementById("numero-ordem-hidden").value == ""){
       alert("Nº da com formatação incorreta");
       return false;
    }
    managerNumOs();
    if(!GCEO){
        ACTION_AFTER = function(){ 
            GCEO = true; 
            $("#form-cad-orde input[type='submit']").click();
        };
        openPasswordGerenteNeed();
    }
    return GCEO;
}
function openEditOrdemMode(idOrdem){
    var url = "ajax.php?code=7723&orde="+idOrdem;
    get(url, function(data){
        if(data.code == "0"){
            $("#for-update-id").val(data.data.id);
            var num = data.data.numero;
            
            if(num.indexOf("-") != -1)
                $("#numero-ordem").val(num.split("-")[1]);
            else 
                $("#numero-ordem").val(num);
            
            $("#numero-ordem-hidden").val(num);
            $("#data-envio-lab-ordem").val(data.data.dataEnvioLab);
            $("#data-recebimento-lab-ordem").val(data.data.dataRecebimentoLab);
            if(data.data.armacaoLoja == "1"){
                $("#armacao-loja-ordem").attr("checked",true);
            } else {
                $("#armacao-loja-ordem").attr("checked",false);
            }
            <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR || $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_GERENTE){ ?>
            $("#valor-ordem").val(data.data.valor);
            <?php } ?>
            $("#laboratorio-ordem").val(data.data.laboratorio);
            $("#loja-ordem").val(data.data.loja);
            autoSetNumOS();
            managerNumOs();
            if(data.data.cancelada == "1"){
                $("#reativar-ordem").show();
            }
            GCEO = false;
            openAddSpaceForm(document.getElementById('add-btn-tool'), true);
        } else
            badAlert(data.message);
    });
}
function delOrdem(idOrdem){
    var url = "index.php?op=del_orde&orde="+idOrdem;
    window.location = url;
}
function checaDatasOS(){
    var dtEnvio = new Date($("#data-envio-lab-ordem").val());
    var dtRecebimento = new Date($("#data-recebimento-lab-ordem").val());
    if(dtRecebimento.getTime() < dtEnvio.getTime()) $("#data-recebimento-lab-ordem").addClass("invalid-input");
    else $("#data-recebimento-lab-ordem").removeClass("invalid-input");
}
<?php if(defined("MODE_AJAX")){?>
expandViewDataMode();
function addOS(){
    var os = {
        "numero" : managerNumOs(),
        "data-envio-lab" : $("#data-envio-lab-ordem").val(),
        "data-recebimento-lab" : null,
        "laboratorio" : $("#laboratorio-ordem").val(),
        "loja" : $("#loja-ordem").val(),
        "valor" : 0
    };
    if($("#armacao-loja-ordem").attr("checked")){
        os["armacao-loja"] = true;
    }
    var url = "ajax.php?code=9002";
    post(url, os, function(data){
        if(data.code == "0"){
            osAdded(data.data);
        } else badAlert(data.message);
    });
}
<?php } ?>
function confirmChange(){
    var checked = document.getElementById("reativar-ordem-input").checked;
    if(checked){
        checked = confirm("Deseja realmente reativar ordem de serviço?");
         document.getElementById("reativar-ordem-input").checked = checked;
    }
}
function autoSetNumOS(){
    var selectLoja = document.getElementById("loja-ordem");
    if(selectLoja.value == "") return;
    document.getElementById("sigla-loja-label").innerHTML = selectLoja.options[selectLoja.selectedIndex].innerHTML+"-";
}
function managerNumOs(){
    var numero  = $("#numero-ordem").val();
    var sigla   = document.getElementById("sigla-loja-label").innerHTML; 
    var num =  sigla+numero;
    document.getElementById("numero-ordem-hidden").value = num;
    return num;
}
var obj = { thousands:'', decimal:',', allowZero: true, prefix: 'R$ ', affixesStay: false};
$("#valor-ordem").maskMoney(obj);
autoSetNumOS();
</script>