<?php
include_once CONTROLLERS."funcionario.php";
include_once CONTROLLERS."loja.php";
$func_controller    = new FuncionarioController();
$loja_controller    = new LojaController();
$loja               = $loja_controller->getLoja($_SESSION[SESSION_LOJA_FUNC]);
$gerente            = $func_controller->getFuncionario($loja->gerente);
?>
<h3 class="title-form"> Precisamos da confirmação do gerente da loja <?php echo $loja->sigla;?> ou de um diretor</h3>
<select class="input select-input gray-grad-back medium-input" id="tipo-confirmacao" onchange="checkTipo()"> 
    <option value="0"><?php echo $gerente->nome?></option>
    <?php
    $diretores          = $func_controller->getAllDiretores();
    foreach ($diretores as $d) {
        echo "<option value='{$d->id}'>{$d->nome}</option>";
    }
    ?>
</select>
<span class="h-separator"> &nbsp; </span>
<label> Senha do <b id="type-view">gerente</b>: 
    <input type="password" class="input text-input" 
    onkeypress="if(event.keyCode == 13){checaSenha();}"
    id="senha-gerente" name="senha" autofocus/>
</label>
<p style="text-align: right; margin-top: 10px;"> 
    <input type="submit" class="btn submit green3-grad-back" onclick="checaSenha()" value="Checar Senha"/>
</p>
<script>
function checaSenha(){
    var p = $("#senha-gerente").val();
    if(p != ""){
        var url = "ajax.php?code=8989";
        var param = {senha: p, tipo: $("#tipo-confirmacao").val()};
        post(url, param, function(data){
           if(data.code == "0"){
               ACTION_AFTER();
               ACTION_AFTER = function(){};
               closeViewDataMode();
           } else {
               event.returnValue = false;
               event.preventDefault();
               badAlert(data.message);
               return false;
           }
        });
    }
}
function checkTipo(){
    var tipo = document.getElementById("tipo-confirmacao").value;
    document.getElementById("type-view").innerHTML = tipo == "0" ? "gerente" : "diretor" ; 
}
</script>
