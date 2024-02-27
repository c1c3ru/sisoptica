<?php

include_once CONTROLLERS.'parcela.php';
include_once CONTROLLERS.'loja.php';

$config             = Config::getInstance();
$parcelaController  = new ParcelaController();
$lojaController     = new LojaController();
$loja               = $config->filter('loja');
$regiao             = $config->filter('regiao');
$rota               = $config->filter('rota');
$localidade         = $config->filter('localidade');
$dataInicio         = $config->filter('data-limite-inferior');
$dataFim            = $config->filter('data-limite-superior');
$resumido           = $config->filter('resumida');

$severalOrAll = function(&$parameter) {
    // If ( the parameter is array AND the first element is empty ) 
    // OR ( if not is a array, but it's empty ), THEN set as an empty array
    
    if ((is_array($parameter) && !empty($parameter) && empty($parameter[0])) || 
        empty($parameter)) {
        $parameter = array();
    }
};
$severalOrAll($loja);
$severalOrAll($regiao);
$severalOrAll($rota);
$severalOrAll($localidade);


if (empty($loja)) {
    $loja = $lojaController->getAllLojas();
    $filters = (object) array( /* empty */ );
} else {
    $loja = array( $lojaController->getLoja($loja) );
    $filters = (object) array(
        'regiao'        => $regiao,
        'rota'          => $rota,
        'localidade'    => $localidade,
    );
}

$totalGeral = 0;

foreach ($loja as $l) {
    
    $filters->loja = $l->id;
    
    $aReceber = $parcelaController->getValoresAReceber($filters, $dataInicio, $dataFim);
    
    if (empty($aReceber)) {
?>
    <p class='name'>
        NÃO há valores a receber da loja <b> <?php echo $l->sigla; ?> </b>
        de <b><?php echo $config->maskData($dataInicio); ?></b>
        até <b><?php echo $config->maskData($dataFim); ?></b>
    </p>

<?php        
        continue;
    }
    
?>

<p class='name'>
    Valores a receber da loja <b> <?php echo $l->sigla; ?> </b>
    de <b><?php echo $config->maskData($dataInicio); ?></b>
    até <b><?php echo $config->maskData($dataFim); ?></b>
</p>

<div class="content">

    <table>

        <thead>
            <tr>
                <th> Região </th>
                <th> Rota </th>
                <?php if(!$resumido) { ?> <th> Localidade </th> <?php } ?>
                <th> Valor (R$) </th>
            </tr>
        </thead>

        <tbody>

        <?php

        $acc = $resumido ? 
            print_tuples_resumido($aReceber) : 
            print_tuples($aReceber) ;
        
        $totalGeral += $acc;
        
        ?>
        </tbody>

    </table>

    <p class="name total">TOTAL: <b>R$ <?php echo $config->maskDinheiro($acc); ?></b></p>

</div>

<?php

}

function print_tuples($tuples) {
    $acc = 0;
    $config = Config::getInstance();

    foreach ($tuples as $tuple) { ?>

        <tr>
            <td><?php echo $tuple->regiao; ?></td>
            <td><?php echo $tuple->rota; ?></td>
            <td><?php echo $tuple->localidade; ?></td>
            <td><?php echo $config->maskDinheiro($tuple->valor); ?></td>
        </tr>

    <?php
        $acc += $tuple->valor;
    }
    return $acc;
}

function print_tuples_resumido($tuples) {
    
    $acc = 0;
    $config = Config::getInstance();

    $printTuple = function($tuple) use ($config) {
    ?>
        <tr>
            <td><?php echo $tuple->regiao; ?></td>
            <td><?php echo $tuple->rota; ?></td>
            <td><?php echo $config->maskDinheiro($tuple->valor); ?></td>
        </tr>
    <?php
    };

    $oldTuple = $tuples[0];

    for ($i = 1, $l = count($tuples); $i < $l; $i++) {
        $tuple = $tuples[$i];
        if (strcmp($oldTuple->rota, $tuple->rota) === 0) {
            $oldTuple->valor += $tuple->valor;
        } else {
            $acc += $oldTuple->valor;
            $printTuple($oldTuple);
            $oldTuple = $tuple;
        }
    }

    $acc += $oldTuple->valor;
    $printTuple($oldTuple);

    return $acc;
}

?>

<p class="name total">TOTAL GERAL: <b>R$ <?php echo $config->maskDinheiro($totalGeral); ?></b></p>

<style>
.content table {text-align: center; width: 100%;}
.content table thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px;}
.content tbody tr:nth-child(even) { background: #eee; }
.content {border-top: 0;}
.name.total { text-align: right; margin-top: 2px; background: green;  color: white; }
</style>
