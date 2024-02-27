<form action="?op=add_veic" method="post" id="form-cad-veiculo">
    <div class="tool-bar-form" form="form-cad-veiculo">
        <div onclick="openAddSpaceForm(this)" class="tool-button add-btn-tool-box" id='add-btn-tool'> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre o veículo&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <label>
                Nome: <input type="text" id="nome-veiculo" class="input text-input" name="nome" required/>			
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label>
            Placa: <input type="text" id="placa-veiculo" class="input text-input" name="placa" required maxlength="8"/>			
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label> Loja:
            <select name="loja" id="loja-veiculo" class="input select-input gray-grad-back" 
                    <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {?>
                        onchange="loadMotoristas(this.value)" 
                    <?php } ?> required>
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
        <span class="h-separator"> &nbsp; </span>
        <label> Motorista:
            <select name="motorista" id="motorista-veiculo" class="input select-input gray-grad-back small-input" required>
                <option value=""> Sem Motorista </option>
            </select>
        </label>
        <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
    </fieldset>
    </div>
</form>
<style>
#form-cad-veiculo .text-input{text-transform: uppercase;}
#form-cad-veiculo input[type='submit']{float: right;}
</style>
<script>
function loadMotoristas(idLoja){    
    var opDefault = document.createElement("option");
    opDefault.value = "";
    opDefault.innerHTML = "Sem Motorista";
    $("#motorista-veiculo").html( opDefault );
    
    if(idLoja == "") return;
    
    var url = "ajax.php?code=8802&loja="+idLoja;
    
    get(url, function(data){
        if(data.code == "0"){
            var motoristas = data.data;
            for(var i = 0; i < motoristas.length; i++){
                var op = document.createElement("option");
                op.innerHTML = motoristas[i].nome; 
                op.value = motoristas[i].id;
                $("#motorista-veiculo").append( op );
            }
            if(waitMotorista){
                $("#motorista-veiculo").val(waitMotorista);
                waitMotorista = false;
            }
        }
    });
}
$(function(){
    loadMotoristas(document.getElementById('loja-veiculo').value);
});

var waitMotorista = false;
function openEditVeiculoMode(idVel){
    if(!idVel) return;
    get('ajax.php?code=3009&veiculo='+idVel,function(data){
        if(data.code == '0'){
            $('#for-update-id').val(data.data.id);
            $('#nome-veiculo').val(data.data.nome);
            $('#placa-veiculo').val(data.data.placa);
            waitMotorista = data.data.motorista;
            $('#loja-veiculo').val(data.data.loja);
            $('#loja-veiculo').change();
            openAddSpaceForm(document.getElementById('add-btn-tool'), true);
        }
    });
}
</script>