<?php
$config     = Config::getInstance();
$caixa_id   = $config->filter('caixa');
$caixa_hoje = $config->currentController->getCaixa($caixa_id);
if(empty($caixa_hoje->id)){
    exit('<h4>Caixa Inválido</h4>');
}
include_once CONTROLLERS.'despesa.php';
$despesaController  = new DespesaController();
$despesas           = $despesaController->getDespesasByCaixa($caixa_hoje->id);
?>
<h4> Lista de Despesas do Caixa Diário de <?php echo $config->maskData($caixa_hoje->data);?> </h4>
<div class="separted-info">
    <table style="text-align: center; width: 100%;">
        <thead>
            <tr> 
                <th class="same-info"><label>Natureza</label></th>
                <th class="same-info"><label>Entidade</label></th>
                <th class="same-info"><label>Observação</label></th>
                <th class="same-info"><label>E/S</label></th>
                <th class="same-info"><label>Valor</label></th>
            </tr>
        </thead>
        <tbody>
    <?php
    include_once CONTROLLERS.'naturezaDespesa.php';
    include_once CONTROLLERS.'funcionario.php';
    include_once CONTROLLERS.'loja.php';
    include_once CONTROLLERS.'veiculo.php';
    include_once CONTROLLERS.'caixa.php';
    $naturezaController     = new NaturezaDespesaController();
    $funcionarioController  = new FuncionarioController();
    $lojaController         = new LojaController();
    $veiculoController      = new VeiculoController();
    $caixaController        = new CaixaController();
    $naturezas = array();
    $entidades = array();
    
    $sumEntrada = 0;
    $sumSaida   = 0;
    
    foreach ($despesas as $despesa) {
        //Obtendo Natureza
        $natureza = new NaturezaDespesa();
        if(!array_key_exists($despesa->natureza, $naturezas)){
            $natureza = $naturezaController->getNatureza($despesa->natureza);
            $naturezas[$natureza->id] = $natureza;
            $entidades[$natureza->id] = array();
        } else {
            $natureza = $naturezas[$despesa->natureza];
        }
        $despesa->natureza = $natureza->nome;

        if($natureza->entrada){
            $sumEntrada += $despesa->valor;
        } else {
            $sumSaida   += $despesa->valor;
        }
        
        //Obtendo Entidade
        $entidade_id = 0;
        $entidade_nome = '';
        if(!array_key_exists($despesa->entidade, $entidades[$natureza->id])){
            switch ($natureza->tipo) {
                case NaturezaDespesaController::TIPO_FUNCIONARIO:
                    $entidade       = $funcionarioController->getFuncionario($despesa->entidade);
                    $entidade_id    = $entidade->id;
                    $entidade_nome  = $entidade->nome;
                break;
                case NaturezaDespesaController::TIPO_LOJA:
                    $entidade       = $lojaController->getLoja($despesa->entidade);
                    $entidade_id    = $entidade->id;
                    $entidade_nome  = $entidade->sigla;
                break;
                case NaturezaDespesaController::TIPO_VEICULO:
                    $entidade       = $veiculoController->getVeiculo($despesa->entidade);
                    $entidade_id    = $entidade->id;
                    $entidade_nome  = $entidade->nome;
                break;
                case NaturezaDespesaController::SEM_TIPO:
                    if($natureza->id == NaturezaDespesaController::ID_DESPESA_CAIXA){
                        $caixa          = $caixaController->getCaixa($despesa->entidade);
                        $entidade_id    = $caixa->id;
                        $entidade_nome  = 'CAIXA';
                        $despesa->observacao  = $config->maskData($caixa->data);
                    }
                break;
            }
            $entidades[$natureza->id][$entidade_id] = $entidade_nome;
        }
        
        $despesa->entidade = $entidades[$natureza->id][$despesa->entidade];
        
        echo '<tr>';
        echo '<td class=\'any-info\'><span>'.$despesa->natureza.'</span></td>';
        echo '<td class=\'any-info\'><span>'.$despesa->entidade.'</span></td>';
        $sub_observacao = substr($despesa->observacao, 0, 50);
        echo '<td class=\'any-info\' title=\''.$despesa->observacao.'\'><span>'.$sub_observacao.'</span></td>';
        echo '<td class=\'any-info\'><span>'.($natureza->entrada ? 'Entrada' : 'Saída').'</span></td>';
        echo '<td class=\'any-info\'><span>'.$config->maskDinheiro($despesa->valor).'</span></td>';
        echo '</tr>';
    }
    
    echo '<tr><th class=\'same-info\' colspan=\'5\'><label>Itens de Prestação de Conta</label></th></tr>';

    include_once CONTROLLERS.'prestacaoConta.php';
    $prestacaoController    = new PrestacaoContaController();
    $itens                  = $prestacaoController->getItensDinheiroByData(
        $caixa_hoje->data, $caixa_hoje->loja
    );
    $prestacoes             = array();
    foreach($itens as $item){ 
        echo '<tr>';
        echo '<td class=\'any-info\'><span>ITEM PRESTAÇÃO DE CONTA</span></td>';
        $prestacaoConta = new PrestacaoConta();
        if(!array_key_exists($item->prestacao, $prestacoes)){
            $prestacaoConta = $prestacaoController->getPrestacaoConta($item->prestacao);
            $prestacoes[$item->prestacao] = $prestacaoConta;
            $prestacaoConta->cobrador = $funcionarioController->getFuncionario(
                $prestacaoConta->cobrador, '', true
            );
            $prestacoes[$prestacaoConta->id] = $prestacaoConta;
        } else {
            $prestacaoConta = $prestacoes[$item->prestacao];
        }
        
        echo '<td class=\'any-info\'><span>'.$prestacaoConta->cobrador->loja.'</span></td>';
        echo '<td class=\'any-info\'><span>'.$prestacaoConta->cobrador->nome.' - '.$prestacaoConta->seq.'</span></td>';
        echo '<td class=\'any-info\'><span>Entrada</span></td>';
        echo '<td class=\'any-info\'><span>'.$config->maskDinheiro($item->valor).'</span></td>';
        echo '</tr>';
        $sumEntrada += $item->valor;
    }
    ?>
        </tbody>
    </table>
</div>
<p>
<label>Entradas: <span>R$ <?php echo $config->maskDinheiro($sumEntrada);?></span></label>
<label>Saídas: <span>R$ <?php echo $config->maskDinheiro($sumSaida);?></span></label>
<label>Saldo: <span>R$ <?php echo $config->maskDinheiro($sumEntrada - $sumSaida);?></span></label>
</p>
<style>
.same-info{background: white;border-radius:3px;}
.same-info label{margin: 5px;}
.any-info{border-bottom: white solid 1px;border-radius:5px;}
.any-info span{font-size: 10pt; color: #555;}
td.any-info.same-info{text-align: left;}
td.any-info.same-info label{margin-right: 2px;}
</style>
<script>expandViewDataMode();</script>