<?php
$config = Config::getInstance();


include_once CONTROLLERS.'loja.php';
include_once CONTROLLERS.'despesa.php';
include_once CONTROLLERS.'caixa.php';
include_once CONTROLLERS.'funcionario.php';
include_once CONTROLLERS.'veiculo.php';
$lojaController     = new LojaController();
$despesaController  = new DespesaController(); 
$caixaController    = new CaixaController();
$funcController     = new FuncionarioController();
$veiculoController  = new VeiculoController();

$caixaBuff = \buffer\CtrlBuffer::newInstance($caixaController, 'getCaixa');
$funcBuff = \buffer\CtrlBuffer::newInstance($funcController, 'getFuncionario');
$veiculoBuff = \buffer\CtrlBuffer::newInstance($veiculoController, 'getVeiculo');

$data_ini   = $config->filter('data-limite-inferior');
$data_fim   = $config->filter('data-limite-superior');
$loja_id    = $config->filter('loja');
$detalhado  = $config->filter('resumida') == null;

if(empty($loja_id)){
    $lojas = $lojaController->getAllLojas();
} else {
    $lojas = array($lojaController->getLoja($loja_id)); 
}

$naturezas_ids = $config->filter('natureza');

$naturezas = $config->currentController->getAllNaturezas($naturezas_ids);

$sumGeral = 0;

foreach($lojas as $loja) {
    
    echo '<p class=\'name\'>';
    echo '[<b>'.$loja->sigla.'</b>]';
    echo ' - Relatorios das despesas por natureza';
    echo ' <b>' . $config->maskData($data_ini).'</b>'; 
    echo ' a'; 
    echo ' <b>' . $config->maskData($data_fim) . '</b>' ; 
    echo '</p>';

    echo '<div class=\'content\' style=\'padding:0;\'>';
    
    $sumLoja = 0;
    
    foreach ($naturezas as $natureza) {
        
        switch ($natureza->tipo) {
            case NaturezaDespesaController::TIPO_FUNCIONARIO:
                $buffer = $funcBuff;
            break;
            case NaturezaDespesaController::TIPO_LOJA:
                $buffer = 'loja';
                break;
            case NaturezaDespesaController::TIPO_VEICULO:
                $buffer = $veiculoBuff;
            break;
        }
        
        echo '<p class=\'name sub-name\'> <b>'.$natureza->nome.'</b></p>';

        echo '<div class=\'content\'>';
        
        $despesas = $despesaController->getDespesaByNaturezaInLoja(
            $natureza, $loja->id, $data_ini, $data_fim, !$detalhado
        );
        
        if(empty($despesas)){
            echo '<p class=\'\'>'.$loja->sigla.' sem itens da natureza '.$natureza->nome.' nesse perído </p>';
            echo '</div>';
            continue;
        }
        
        $sumNatureza = 0;
            
        if($detalhado) {
            
            $last_entidade = null;

            foreach ($despesas as $despesa) {

                if($buffer == 'loja') {
                    $entidade_nome  = $loja->sigla;
                } else {
                    $entidade       = $buffer->getEntry($despesa->entidade);
                    $entidade_nome  = $entidade->nome;
                }

                $caixa      = $caixaBuff->getEntry($despesa->caixa);

                if($entidade_nome != $last_entidade){
                    if ($last_entidade != null) {
                        echo '</tbody>';
                        echo '</table>';
                    }
                    echo "<p class='entity-name'>".$entidade_nome."</p>";
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>DATA</th>';
                    echo '<th>OBSERVAÇÃO</th>';
                    echo '<th>VALOR</th>';
                    echo '</tr>';
                    echo '</thread>';
                    echo '<tbody>';
                    $last_entidade = $entidade_nome;
                }

                echo '<tr>';
                echo '<td>' . $config->maskData($caixa->data) . '</td>';
                echo '<td>' . $despesa->observacao . '</td>';
                echo '<td>' . $config->maskDinheiro($despesa->valor) . '</td>';
                echo '</tr>';

                $sumNatureza += $despesa->valor;

            }

            echo '</tbody>';
            echo '</table>';

        } else {

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>NOME</th>';
            echo '<th>VALOR</th>';
            echo '</tr>';
            echo '</thread>';
            echo '<tbody>';

            foreach ($despesas as $despesa) {

                if($buffer == 'loja') {
                    $entidade_nome  = $loja->sigla;
                } else {
                    $entidade       = $buffer->getEntry($despesa->entidade);
                    $entidade_nome  = $entidade->nome; 
                }

                echo '<tr>';
                echo '<td>' . $entidade_nome . '</td>';
                echo '<td>' . $config->maskDinheiro($despesa->valor) . '</td>';
                echo '</tr>';
                
                $sumNatureza   += $despesa->valor;

            }

            echo '</tbody>';
            echo '</table>';

        }
        
        $sumLoja += $sumNatureza;

        echo '<p align=\'right\'>';
        echo 'Total: <b>R$ ' . $config->maskDinheiro($sumNatureza) . '</b>';
        echo '</p>';
        
        echo '</div>';
        
    }
    
    $sumGeral += $sumLoja;
    
    echo '<p>';
    echo 'Total da <b>'.$loja->sigla.'</b>: ';
    echo '<b>R$ ' . $config->maskDinheiro($sumLoja) . '</b>';
    echo '</p>';
    
    echo '</div>';

}

echo '<p class=\'name sum\'>';
echo 'Total Geral : ';
echo '<b>R$ ' . $config->maskDinheiro($sumGeral) . '</b>';
echo '</p>';

?>
<style>
table {width: 100%;}
table tr td {border-bottom:gray solid 1px;padding:10px;}
table tr td:last-child {width: 20%}
.entity-name {background: #eee;text-align:left;font-weight: bolder;padding:10px;margin:10px 0 0 0;}
.content .entity-name:first-child {margin-top: 0;}
.sub-name {background: #EEE; color: gray;border-top: lightgray solid 1px;}
.sum {background: green; color: white; text-shadow: 1px 1px 1px black; text-align: right;}
</style>
