<?php
$config         = Config::getInstance();
$id_natureza    = $config->filter('natureza');

$id_caixa       = $config->filter('caixa');
include_once CONTROLLERS.'caixa.php';
$caixaController    = new CaixaController();
$caixa              =   empty($id_caixa) ? 
                        $caixaController->getCaixaAberto() : 
                        $caixaController->getCaixa($id_caixa);

$natureza       = $config->currentController->getNatureza($id_natureza);
if(empty($natureza->id)){
    $config->throwAjaxError('Natureza Inválida');
}
$res = array();
switch ($natureza->tipo) {
    case NaturezaDespesaController::TIPO_FUNCIONARIO:
        include_once CONTROLLERS.'funcionario.php';
        $func_controller    = new FuncionarioController();
        $funcionarios       = $func_controller->getAllFuncionariosNaoInativosParaCaixa(false, $caixa->loja, true);
        foreach($funcionarios as $funcionario){
            $res[] = array('id' => $funcionario->id, 'nome' => $funcionario->nome); 
        }
    break;
    case NaturezaDespesaController::TIPO_LOJA:
        include_once CONTROLLERS.'loja.php';
        $loja_controller    = new LojaController();
        $lojas              = $loja_controller->getAllLojasNaoInativasParaCaixa();
        foreach($lojas as $loja){
            $res[] = array('id' => $loja->id, 'nome' => $loja->sigla); 
        }
    break;
    case NaturezaDespesaController::TIPO_VEICULO:
        include_once CONTROLLERS.'veiculo.php';
        $veic_controller    = new VeiculoController();
        $veiculos           = $veic_controller->getAllVeiculosNaoInativosParaCaixa(false, $caixa->loja);
        foreach($veiculos as $veiculo){
            $res[] = array('id' => $veiculo->id, 'nome' => $veiculo->nome); 
        }
    break;
}
$config->throwAjaxSuccess($res);
?>
