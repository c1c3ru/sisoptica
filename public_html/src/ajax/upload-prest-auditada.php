<?php


$config = Config::getInstance();

$prestacao_id = $config->filter('prest');

if(empty($prestacao_id)){ exit('Prestação de conta inválida'); }

$prestacao = $config->currentController->getPrestacaoConta($prestacao_id, true);

if(empty($prestacao->id)){ exit('Prestação de conta inválida'); }

?>
<h3> Submissão de Auditoria de Prest. Conta</h3>
<label> Cobrador: <span><?php echo $prestacao->cobrador;?></span></label>
<label> Seq.: <span><?php echo $prestacao->seq;?></span></label>
<br/>
<form method="post" enctype="multipart/form-data" action="index.php?op=up-prest">
    <input type="hidden" name="prestacao" value="<?php echo $prestacao->id;?>"/>
    <label> Arquivo:
        <input type="file" name="arquivo" class="input text-input"/>
    </label>
    <input type="submit" class="btn submit green-back"
           name="submit" value="Submeter" required/>
</form>