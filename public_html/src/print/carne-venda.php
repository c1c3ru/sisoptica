<style type=text/css>
@page {margin: 5px;}
body {margin: 5px;}
@media screen {
    body { width:230mm; margin: 0 auto;box-shadow:0 0 25px black;background: white;}
    html{ background: #3F3D3D;}
}
.cp { 
    font: bold 9px Arial; color: black; 
}
.ti { 
    font: 8px Arial, Helvetica, sans-serif; 
}
.ld { 
    font: bold 14px Arial; 
    color: #000000; 
}
.ct { 
    font: 8px "Arial Narrow"; color: #000033; 
} 
.cn { 
    font: 8px Arial; 
    color: #000000; 
}
.bc { 
    font: bold 20px Arial; 
    color: #000000; 
}
.ld2 { 
    font: bold 10px Arial; 
    color: #000000; 
}
.bc { 
    font: bold 20px Arial; 
    color: #000000; 
}
.style1 {
    font-size: 7px;
}
.folha {
    page-break-inside: avoid;
}

/* new style */
.parcelas-carne {
    text-align: center;
    font-family: sans-serif;
    font-size: 90%;
    color: #444;
    padding: 20px 0;
}

.parcela-carne {
    text-align: left;
    display: inline-block;
    width: calc(49% - 25px);
    padding: 5px 12.5px 15px 12.5px;
    position: relative;
    overflow: hidden;
    border-bottom: #ccc dashed 2px;
    border-right: #ccc dashed 2px;
    page-break-inside: avoid;
}

.parcela-carne:nth-child(2n) {
    border-right: none;    
    page-break-after: auto;
}

.parcela-carne-row {
    padding: 10px 0 5px 0;
    display: block;
    border-bottom: #ccc solid 1px;
    display: flex;
    justify-content: space-between;
    z-index: 1;
}

.parcela-carne-col {
    display: inline;
}

.parcela-carne-field {
    
}

.parcela-carne-value {
    font-weight: bold;
}

.parcela-carne-paga {
    text-align: center;
    font-size: 100px;
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 0;
    opacity: 0.1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.parcela-carne-assinatura {
    height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}

.linha-assinatura {
    width: 100%;
    border-top: #444 solid 2px;
}
</style>

<?php

$config = Config::getInstance();

$id_venda = $config->filter("venda");

if(empty($id_venda)){
    exit("Venda Inválida!");
}

$controller = $config->currentController;

include_once CONTROLLERS."venda.php";
include_once CONTROLLERS."cliente.php";
include_once CONTROLLERS."regiao.php";
include_once CONTROLLERS."localidade.php";
include_once CONTROLLERS."loja.php";

$venda_controller   = new VendaController();
$cliente_controller = new ClienteController();
$localidade_controller = new LocalidadeController();
$regiao_controller  = new RegiaoController();
$loja_controller    = new LojaController();

$venda              = $venda_controller->getVenda($id_venda);

if(empty($venda->id)){
    exit("Venda Inválida!");    
}

$cliente            = $cliente_controller->getCliente($venda->cliente);
$localidade         = $localidade_controller->getLocalidade($cliente->localidade);
$cidade             = $regiao_controller->getCidade($localidade->cidade);
$estado             = $regiao_controller->getEstado($cidade->estado);
$cidade->estado     = $estado; 
$localidade->cidade = $cidade;
$cliente->localidade= $localidade;
$venda->cliente     = $cliente; 
$venda->loja        = $loja_controller->getLoja($venda->loja);

$parcelas           = $controller->getParcleasByVenda($venda->id);

$length             = count($parcelas);

if(!$length) {
    exit("Sem Parcelas");
}

if ($parcelas[0]->numero == '0') {
    $length--;
}


initDrawCarne();

foreach($parcelas as $parcela){

    $parcela->venda = $venda;
    
    $parcela->counter = $length;
    
    // Desenhando carnê 2x: uma para cobrar e outra para destaque
    drawCarne($parcela);
    drawCarne($parcela);
   
}

endDrawCarne();


function initDrawCarne() {
    ?><div class="parcelas-carne"><?php 
}

function endDrawCarne() {
    ?></div><?php 
}

function drawCarne($parcela) {

    $config = Config::getInstance();
    $venda = $parcela->venda;
    $cliente = $venda->cliente;
    $localidade = $cliente->localidade;
    $cidade = $localidade->cidade;
    $estado = $cidade->estado;
?>

    <div class="parcela-carne">

        <?php if($parcela->status == TRUE): /* parcela paga */ ?>
            <div class="parcela-carne-paga">PAGO</div>
        <?php endif; ?>

        <div class="parcela-carne-row">
            <div class="parcela-carne-col">
                <span class="parcela-carne-field">Nome:</span>
                <span class="parcela-carne-value"><?php echo $cliente->nome; ?></span>
            </div>
            <div class="parcela-carne-col">
                <span class="parcela-carne-field">Cod. Venda:</span>
                <span class="parcela-carne-value"><?php echo $venda->id; ?></span>
            </div>
        </div>

        <div class="parcela-carne-row">
            <div class="parcela-carne-col">
                <span class="parcela-carne-field">Nº parcela:</span>
                <span class="parcela-carne-value"><?php echo $parcela->numero."/".$parcela->counter; ?></span>
            </div>
            <div class="parcela-carne-col">
                <span class="parcela-carne-field">Vencimento:</span>
                <span class="parcela-carne-value"><?php echo $config->maskData($parcela->validade); ?></span>
            </div>
            <div class="parcela-carne-col">
                <span class="parcela-carne-field">Valor:</span>
                <span class="parcela-carne-value"><?php echo $config->maskDinheiro($parcela->valor, 'R$ '); ?></span>
            </div>
        </div>

        <div class="parcela-carne-assinatura">
            <div class="linha-assinatura"></div>
            <span>Ass Cobrador</span>
        </div>

    </div>

<?php 

} // end of drawCarne

function initDrawBoleto() {

    include_once LIBS.'boleto/boleto.class.php';
    
    Boleto::initFunctions();
}


function drawBoleto($parcela) {

    $dadosboleto = Boleto::dadosBoleto($parcela);
    
    Boleto::drawInLayout($dadosboleto);
}

?>