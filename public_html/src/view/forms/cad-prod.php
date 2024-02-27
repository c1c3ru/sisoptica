<?php 
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
    $config = Config::getInstance();
?>
<form action="?op=add_prod" method="post" id="form-cad-prod">
    <div class="tool-bar-form" form="form-cad-prod">
        <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
        <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
    </div>
    <div class="hidden add-space-this-form">
    <fieldset>    
        <legend>&nbsp;informações sobre o produto&nbsp;</legend>
        <input type="hidden" name="for-update-id" id="for-update-id" value=""/>
        <table cellspacing="20" class="center">
            <tr>
                <td>
                    <label> Código:
                        <input type="text" class="input text-input" id="codigo-produto" name="codigo" required/>
                    </label>
                </td>
                <td rowspan="2">
                    <label> Descrição:
                        <textarea name="descricao" class="input text-input" id="descricao-produto" rows="6" required></textarea>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label> Preço de Compra:
                        <input type="text" class="input text-input" id="preco-compra-produto" name="preco-compra"/>
                    </label>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <label> Preço de Venda:
                        <input type="text" class="input text-input" id="preco-venda-produto" name="preco-venda"/>
                    </label> 
                </td>
                <td>
                    <label> Preço Mínimo de Venda:
                        <input type="text" class="input text-input" id="preco-venda-min-produto" name="preco-venda-min"/>
                    </label>
                </td>
            </tr>
            <tr> 
                <td> 
                    <label> Tipo : 
                        <select name="tipo" class="input select-input gray-grad-back" id="tipo-produto" required>	
                            <option value=""> Selecione um tipo </option>
                            <?php
                            $tipos = $config->currentController->getAllTiposProduto();
                            foreach ($tipos as $tipo){
                                echo "<option value='{$tipo->id}'> {$tipo->nome} </option>";
                            }
                            ?>
                        </select>
                    </label>
                </td>
                <td> 
                    <label> Marca :
                        <select name="marca" class="input select-input gray-grad-back" id="marca-produto" required>	
                            <option value=""> Selecione uma marca </option>
                            <?php
                            $marcas = $config->currentController->getAllMarcas();
                            foreach ($marcas as $marca){
                                echo "<option value='{$marca->id}'> {$marca->nome} </option>";
                            }
                            ?>
                        </select>
                    </label>
                </td>
                <td> 
                    <label> Categoria :
                        <select name="categoria" class="input select-input gray-grad-back" id="categoria-produto" required>	
                            <option value='0'> Generico</option>";
                            <option value='1'> Lente </option>";
                        </select>
                    </label>
                </td>
            </tr>
        </table>
        <p style="text-align: right;"> 
            <input type="submit" class="btn submit green3-grad-back" name="submit" value="Cadastrar"/>
        </p>
    </fieldset>
    </div>
</form>
<style>
    #form-cad-prod table td {text-align: right;}
</style>
<script src="script/mask.js"></script>
<script src="script/jquery.maskMoney.js"></script>
<script>
$(function(){
    var obj = { thousands:'', decimal:',', allowZero: true, prefix: 'R$ ', affixesStay: false};
    $('#preco-compra-produto').maskMoney(obj);
    $('#preco-venda-min-produto').maskMoney(obj);
    $('#preco-venda-produto').maskMoney(obj); 
});    
function openEditProdutoMode(idprod){
    var url = "ajax.php?code=7781&prod="+idprod;
    get(url, function(data){
        if(data.code == "0"){
            $("#for-update-id").val(data.data.id);
            $("#codigo-produto").val(data.data.codigo);
            $("#descricao-produto").val(data.data.descricao);
            <?php if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) { ?>
            $("#preco-compra-produto").val(data.data.precoCompra);
            <?php } ?>
            $("#preco-venda-produto").val(data.data.precoVenda);
            $("#preco-venda-min-produto").val(data.data.precoVendaMin);
            $("#tipo-produto").val(data.data.tipo);
            $("#marca-produto").val(data.data.marca);
            $("#categoria-produto").val(data.data.categoria);
            openAddSpaceForm(document.getElementById("add-btn-tool"), true);
        } else 
            badAlert(data.message);
    });
}    
</script>
<?php } ?>