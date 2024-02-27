<?php
function consulta_input_olho($name, $required = false, $onblur = ""){
    $input  = "<input type=\"number\" pattern=\"[0-9]+([\.|,][0-9]+)?\" step=\"0.01\" id=\"$name-consulta\" ";
    if($required) $input .= " required ";
    if(!empty($onblur)) $input .= " onblur=\"$onblur\" ";
    $input .= " class=\"input text-input big-input\" name=\"$name\" onkeypress=\"mascaraFloat()\"/>";
    echo $input;
}
?>
<div id="form-cad-cons">
    <table cellspacing="5" class="center" id="table-top"> 
        <tr>
            <td colspan="3"> 
                <label> Nome Paciente: 
                    <a href="javascript:;" onclick="clienteNameConsulta();">(Cliente)</a>
                    <br/>
                    <input type="text" class="input text-input" style="width: 97%"
                    id="nome-paciente-consulta" name="nome-paciente" required/>
                </label>
            </td>
        </tr>
        <tr> 
            <td> </td>
            <td> <label> Olho Dir. </label> </td>
            <td> <label> Olho Esq. </label> </td>
        </tr>
        <tr> 
            <td class="side-data"> Esférico (<b>+,-</b>)</td>
            <td class="separted-info"> <?php consulta_input_olho("esferico-olho-direito", true);    ?> </td>
            <td class="separted-info"> <?php consulta_input_olho("esferico-olho-esquerdo", true);   ?> </td>
        </tr>
        <tr>    
            <td class="side-data"> Cilíndrico (<b>-</b>)</td>
            <td class="separted-info"> <?php consulta_input_olho("cilindrico-olho-direito", true); ?> </td>
            <td class="separted-info"> <?php consulta_input_olho("cilindrico-olho-esquerdo", true);  ?> </td>
        </tr>
        <tr> 
            <td class="side-data"> Eixo (<b>º</b>) </td>
            <td class="separted-info"> <?php consulta_input_olho("eixo-olho-direito", true); ?> </td>
            <td class="separted-info"> <?php consulta_input_olho("eixo-olho-esquerdo", true);  ?> </td>
        </tr>
        <tr>    
            <td class="side-data"> D.N.P </td>
            <td class="separted-info"> <?php consulta_input_olho("dnp-olho-direito", true);  ?> </td>
            <td class="separted-info"> <?php consulta_input_olho("dnp-olho-esquerdo", true);   ?> </td>
        </tr>
    </table>
    <table cellspacing="5" class="center" id="table-bottom">
        <tr>
            <td class="side-data"> DP: </td>
            <td class="separted-info"> <?php consulta_input_olho("dp", true); ?> </td>
            <td class="side-data">  Adição:  </td>
            <td class="separted-info"> <?php consulta_input_olho("adicao", true, "checaPositivo(this)"); ?> </td>
        </tr>
        <tr>
            <td class="side-data"> C.O: </td>
            <td class="separted-info"> <?php consulta_input_olho("co", true); ?> </td>
            <td class="side-data"> Altura: </td>
            <td class="separted-info"> <?php consulta_input_olho("altura", true); ?> </td>
        </tr>
        <tr> 
            <td class="side-data"> Lente: </td>
            <td class="separted-info" colspan="3" style="text-align: center;"> <input type="text" class="input text-input big-input" name="lente" id="lente-consulta"/> </td>
        </tr>
        <tr> 
            <td class="side-data"> Cor: </td>
            <td class="separted-info" colspan="3" style="text-align: center;"> <input type="text" class="input text-input big-input" name="cor" id="cor-consulta"/> </td>
        </tr>
        <tr>
            <td colspan="4" class="separted-info">
            <label> Oculista: <br/>
                <select class="input select-input gray-grad-back bigger-input" name="oculista" id="oculista-consulta" required>	
                    <option value=""> Selecione um oculista </option>
                    <?php
                    include_once CONTROLLERS."funcionario.php";
                    $controller_func = new FuncionarioController();
                    $oculistas = $controller_func->getAllOculistas();
                    foreach ($oculistas as $oculista){
                        echo "<option value='{$oculista->id}'> {$oculista->nome} </option>";
                    }
                    ?>
                </select>
            </label>
            </td>    
        </tr>
        <tr> 
            <td colspan="4" class="separted-info">
                <label> Observação: <br/>
                    <textarea class="input text-input" name="observacao" id="observacao-consulta" rows="4"></textarea>
                </label>
            </td>
        </tr>
    </table>
</div>
<style>
#form-cad-cons table {width: 100%;}
#form-cad-cons #table-top td:first-child{ width: 40%; }
#form-cad-cons #table-bottom .side-data{width: 20%;}
#form-cad-cons table td {text-align: left;}
#form-cad-cons .text-input,.select-input{ text-transform: uppercase;} 
#form-cad-cons .side-data {
        background: gray; 
        color: white; 
        font-size: 9pt; 
        border-radius: 3px; 
        text-align: center;
}
</style>
<script>
function loadFormConsulta(consultaObj){
    $("#nome-paciente-consulta").val(consultaObj.nomePaciente);
    $("#esferico-olho-esquerdo-consulta").val(consultaObj.esfericoOE);
    $("#esferico-olho-direito-consulta").val(consultaObj.esfericoOD);
    $("#cilindrico-olho-esquerdo-consulta").val(consultaObj.cilindricoOE);
    $("#cilindrico-olho-direito-consulta").val(consultaObj.cilindricoOD);
    $("#eixo-olho-esquerdo-consulta").val(consultaObj.eixoOE);
    $("#eixo-olho-direito-consulta").val(consultaObj.eixoOD);
    $("#dnp-olho-esquerdo-consulta").val(consultaObj.dnpOE);
    $("#dnp-olho-direito-consulta").val(consultaObj.dnpOD);
    $("#dp-consulta").val(consultaObj.dp);
    $("#adicao-consulta").val(consultaObj.adicao);
    $("#co-consulta").val(consultaObj.co);
    $("#altura-consulta").val(consultaObj.altura);
    $("#oculista-consulta").val(consultaObj.oculista);
    $("#lente-consulta").val(consultaObj.lente);
    $("#cor-consulta").val(consultaObj.cor);
    $("#observacao-consulta").val(consultaObj.observacao);
}
function checaPositivo(field){
    var value = parseFloat(field.value);
    if(value < 0){
       field.value = "0,00"; 
    }
}
function clienteNameConsulta(){
    var span = document.getElementById("cliente-nome-venda");
    var val = span.innerHTML.trim();
    var def = span.getAttribute("placeholder");
    if(val != def){
        $("#nome-paciente-consulta").val(val);
    }
}
$(function(){
   $("#cilindrico-olho-esquerdo-consulta").attr("max", "0");
   $("#cilindrico-olho-direito-consulta").attr("max", "0"); 
});
</script>