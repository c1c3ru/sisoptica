<?php 
$config = Config::getInstance(); 
$action = !defined("IS_VENDA") ? "cad-lanc" : 
          (!defined("IS_VENDA_RENE") ? "cad-vend" : "cad-rene");
?>
<form action="<?php echo "index.php?op=$action"?>" method="post" onsubmit="return clearCPF();" id="form-lancamento">
    <fieldset>
        <legend>&nbsp;PESQUISAR VENDA&nbsp;</legend>
        <label> Nº Venda:  
            <input type="text" name="venda_id" class="input text-input" value="<?php echo $config->filter("venda_id");?>" style="width: 5%;"/>
        </label>
        <?php if(!defined("IS_VENDA")) { ?>
        <span class="h-separator"> &nbsp; </span>
        <label > Num. OS:
            <input type="text" name="ordem_servico_numero" class="input text-input" value="<?php echo $config->filter("ordem_servico_numero");?>" style="width: 5%;"/>
        </label>
        <?php } ?>
        <span class="h-separator"> &nbsp; </span>
        <label> CPF: 
            <input type="text" name="cpf-pesquisa" id="cpf-pesquisa" class="input text-input smaller-input" maxlength="14"
                   value="<?php echo $config->filter("cpf-pesquisa");?>" onkeypress="MascaraCPF(this)" />
            <input type="hidden" id="cpf-pesquisa-hidden" name="cliente_cpf" />
        </label> 
        <span class="h-separator"> &nbsp; </span>
        <label> Nome Cliente:
            <input type="text" name="cliente_nome" class="input text-input" value="<?php echo $config->filter("cliente_nome");?>"/>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <label>  Loja: 
            <select name="venda_id_loja" class="input select-input gray-grad-back" style="width: 6.5%;">
                <?php
                include_once CONTROLLERS."loja.php";
                $loja_controller = new LojaController();
                if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
                    $lojas = array(new Loja("", "TODAS"));
                    $lojas = array_merge($lojas, $loja_controller->getAllLojas(false));
                } else  {
                    $lojas = array(new Loja($_SESSION[SESSION_LOJA_FUNC], 
                                            $_SESSION[SESSION_LOJA_SIGLA_FUNC]));
                }
                foreach($lojas as $loja){ ?>
                    <option value="<?php echo $loja->id; ?>"
                    <?php if($loja->id == $_SESSION[SESSION_LOJA_FUNC]) echo "selected"; ?>
                    ><?php echo $loja->sigla; ?></option>
                <?php } ?>
            </select>
        </label>
        <span class="h-separator"> &nbsp; </span>
        <?php if(defined("IS_VENDA")) { ?>
        <label> Data: <input type="date" class="input text-input" name="venda_data_venda" value="<?php echo $config->filter("venda_data_venda");?>"/> </label>
            <?php if(!defined("IS_VENDA_RENE")){?>
                <span class="h-separator"> &nbsp; </span>
                <label> Status: 
                    <select class="input select-input gray-grad-back" style="width: 5%" name="status" id="status-pesquisa"> 
                        <option value="0"> Todos </option>
                        <option value="1" selected> Ativa </option>
                        <option value="2"> Cancelada </option>
                        <option value="3"> Quitada </option>
                        <option value="4"> Renegociada </option>
                    </select> 
                </label>
            <?php } ?>
        <?php } ?>
        <input type="submit" class="btn submit green3-grad-back" id="pesquisar_lancamento" 
        name="pesquisar_lancamento" value="Pesquisar"/>
    </fieldset>
</form>
<style> 
#form-lancamento .input { text-transform: uppercase; }
#form-lancamento .h-separator { margin-left: 2px; margin-right: 2px;}
#pesquisar_lancamento { float: right; margin-top: -5px; }
<?php if(defined("IS_VENDA")){ ?>
#form-lancamento input[type='submit']{top: 5px; position: relative;}
<?php } ?>
</style>
<script src="script/mask.js"> </script>
<script> 
<?php if(!is_null($config->filter("status"))){ ?>
document.getElementById('status-pesquisa').value = "<?php echo $config->filter("status");?>";   
<?php } ?>
function clearCPF(){
    var v = document.getElementById("cpf-pesquisa").value;
    var nv = v.replace(/\./g,'').replace(/\-/g,'');
    document.getElementById("cpf-pesquisa-hidden").value = nv;
    return true;
}
</script>