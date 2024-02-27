<?php

include_once 'src/util/config.php';

//Obtendo controlador geral e auxiliar
$config = Config::getInstance();

//Verificando se o usuário está logado
if(!$config->isLoged()) {
    echo "Você não está logado no sistema";
    exit(0);
}

ini_set('memory_limit', '2G');
ini_set('max_execution_time', 300);

$auto_style = $config->filter('ns') == null;
?>
<?php if($auto_style){ ?>
<!DOCTYPE html>
<?php } else { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php } ?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
        <title> Relatório Optica Capital</title>
        <?php if($auto_style){ ?>
            <style>
            @page {margin: 2px;}
            body { margin: 2px; font-family: sans-serif; font-size: 9pt;}
            .name{color: green;border-left: green solid 2px;padding: 5px;margin: 0px;}
            .content{padding: 3px; border: green solid 2px;page-break-inside:auto;}
            .content-venda {padding: 2px; line-height: 15px; margin-bottom: 2px;page-break-inside:auto;}
            .content-venda b, .content b{margin-right: 5px;}
            .content table {text-align: center;width: 100%;}
            .content table thead th, 
            .content table tfoot td { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
            </style>
        <?php } ?>
    </head>
    <body> 
        <?php
        //Importando o controlador de relatórios
        $config->printController();
        ?>
    </body>
</html>
<?php
if(!is_null($config->filter("js"))){
    //Iniciando dialogo de impressão JavaScript
    echo "<script> window.print(); </script>";
    exit(0);
} 
?>