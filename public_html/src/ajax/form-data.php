<?php

$config = Config::getInstance();

$nparcela = $config->filter('nparc');

$isForParcela = $nparcela != null;
$isForVenda = !$isForParcela;

if($isForVenda) {

    $venda_id   = $config->filter("venda");
    $venda      = $config->currentController->getVenda($venda_id);

    if(empty($venda->id)){
        echo "<h3>Venda Inválida!</h3>";
        exit(0);
    }
    if(!empty($venda->dataEntrega)){
        echo "<h3>Esta operação não está disponível para essa venda</h3>";
        exit(0);
    }
    
} else {

    $vparcela = $config->filter('vparc');

}
?>
<form action="?op=dt_ent_v" method="post"
    <?php if($isForParcela){ echo 'onsubmit=\'remarcar(); return false;\''; }?>>

    <?php if($isForVenda){ ?>
        <input type="hidden" name="venda" value="<?php echo $venda->id;?>" />
    <?php } ?>

    <p class="title-form" id="title-data-form">
        Informe a <?php echo $isForVenda ? 'data da entrega': 'nova data de cobrança';?>
    </p>
    <br/>

    <?php if($isForVenda){ ?>
        <label>
            <input type="checkbox" name="is_previsao" onchange="toggleEntregaPrev(this.checked)" value="1"
                   title="Se marcado, a data informada alterará a previsão de entrega dessa venda">
            Previsão de Entrega
        </label>
        <br/>
        <br/>
    <?php } ?>

    <label> 
        <text> Data <?php echo $isForVenda ? 'Entrega':'Cobrança' ; ?> </text> :
        <input name="<?php echo $isForVenda ? "data-entrega" : "data-remarc"; ?>"
               value="<?php echo $config->filter('actual');?>"
               type="date" class="input text-input"
               <?php if($isForParcela) { ?>
                   max="<?php echo date('Y-m-d', time() + MAIN_LIMIT_DATE); ?>"
               <?php } ?>
               <?php if($isForVenda) { ?>
                   required
               <?php } ?> />

        <?php if($isForParcela){ ?>
            <p class="info-input">* Se for não informada, a data da remarcação será removida</p>
        <?php } ?>
        
    </label>
    <input type="submit" class="btn submit green-back" style="float: right;" name="submit" value="Atualizar"/>
</form>
<script>

<?php if($isForParcela) { ?>

    function remarcar(){
        if(!confirm('Deseja realmente remarcar cobrança dessa parcela?')) return;
        var postObj = {
            nparc: <?php echo is_array($nparcela) ? '['.implode(',', $nparcela).']' : $nparcela;?>,
            vparc: <?php echo $vparcela;?>,
            data: document.getElementsByName('data-remarc')[0].value
        };
        post('ajax.php?code=8700', postObj, function(data){
            if(data.code == "0"){
                ACTION_AFTER();
                ACTION_AFTER = function(){};
                ACTION_AFTER_NOT_CONFIRM = function(){};
                alert(data.message);
            } else badAlert(data.message);
        });
    }

<?php } else { ?>

    function toggleEntregaPrev(status){
        document.getElementById('title-data-form').innerHTML = 'Informe a data de '+(status ? 'previsão de' : '')+' entrega ';
        document.getElementsByTagName('text')[0].innerHTML = 'Data '+(status? 'Previsão':'Entrega');
    }

<?php } ?>

</script>

