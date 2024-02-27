<?php
$config = Config::getInstance();

include_once CONTROLLERS.'funcionario.php';
include_once CONTROLLERS.'venda.php';
include_once CONTROLLERS.'cliente.php';
include_once CONTROLLERS.'produto.php';
include_once CONTROLLERS.'parcela.php';
include_once CONTROLLERS.'rota.php';
include_once CONTROLLERS.'loja.php';
$func_controller    = new FuncionarioController(); 
$venda_controller   = new VendaController();
$cliente_controller = new ClienteController();
$produto_controller = new ProdutoController();
$parc_controller    = new ParcelaController();
$rota_controller    = new RotaController();
$loja_controller    = new LojaController();

$loja               = $config->filter('loja');
if(empty($loja)){
    $lojas = $loja_controller->getAllLojas();
} else {
    $lojas = array($loja_controller->getLoja($loja));
}
//$dt_limit_inf    =  $config->filter('data-limite-inferior');
$dt_limit_sup    =  $config->filter('data-limite-superior');
$COUNTER = 1;

foreach($lojas as $loja){

    $regioes_id = $config->filter('regiao');
    $rotas_id = $config->filter('rota');
    if(empty($regioes_id)){
        $regioes = $config->currentController->getAllRegioes();
    } else {
        $regioes = array();
        foreach($regioes_id as $reg_id){
            $regioes[] = $config->currentController->getRegiao($reg_id);
        }
    }
    
    foreach ($regioes as $regiao) {

        $cobrador = $func_controller->getFuncionario($regiao->cobrador);

        echo "<p style='border:green solid 2px;padding:5px; margin:0; background: green; color: white;' class='content'>";
        echo "Loja: <b> {$loja->sigla} </b>";
        echo "Regiao: <b> {$regiao->nome} </b>";
        echo "Cobrador: <b> {$cobrador->nome} </b>";
        //echo 'De: <b>'.$config->maskData($dt_limit_inf).'</b>';
        echo 'Até: <b>'.$config->maskData($dt_limit_sup).'</b>';
        echo 'Gerado em: <b> '.date("d/m/Y").' </b>';
        echo "</p>";

        if(!$venda_controller->existsEntregasInRegiao($regiao->id, $dt_limit_sup/*, $dt_limit_inf*/)){
             echo '<div class=\'content\'> Sem Entregas </div>';
             continue;
        }

        if(empty($rotas_id)){
            $rotas = $rota_controller->getRotasByRegiao($regiao->id);
        } else {
            $rotas = $rota_controller->getRotas($rotas_id);
        }

        foreach ($rotas as $rota) {

            $vendas = $venda_controller->getVendasNaoEntregues($rota->id, $dt_limit_sup/*, $dt_limit_inf*/);

            if(empty($vendas)){ continue; };

            echo "<p class='name'> Rota: <b>{$rota->nome}</b> </p>";

            echo '<div class=\'content\'>';

            foreach($vendas as $venda){

                $venda->vendedor    = $func_controller->getFuncionario($venda->vendedor);
                $venda->cliente     = $cliente_controller->getCliente($venda->cliente, true);
                $venda->produtos    = $venda_controller->getProdutosVendaOfVenda($venda->id);
                $parcelas           = $parc_controller->getParcleasByVenda($venda->id);
                if($parcelas[0]->numero == '0'){
                    $comEntrada         = true;
                    if(count($parcelas) > 1) {
                        $primeira_parcela   = $parcelas[1]; 
                    } else {
                        $primeira_parcela   = null; 
                    }
                } else {
                    $comEntrada         = false;
                    $primeira_parcela   = $parcelas[0];
                }

                echo '<div class=\'content-venda\' style=\'border: none; border-bottom: green solid 1px;\'>';

                echo '<b>('.($COUNTER++).')</b>Venda: <b>'.$venda->id.'</b>';
                echo 'Prev. Entrega: <b>'.$config->maskData($venda->previsaoEntrega).'</b>';
                echo 'OS: <b>'.$venda->os.'</b>';
                echo 'Cliente: <b>'.$venda->cliente->nome.' ('.$venda->cliente->apelido.')</b> CPF: <b>'.$config->maskCPF($venda->cliente->cpf).'</b>';
                $telefones          = $cliente_controller->getAllTelefonesOfCliente($venda->cliente->id);
                if(!empty($telefones)){
                    echo 'Fones: '; 
                    $tels = array();
                    foreach ($telefones as $t) {
                        $tels[] = '<b>'.$config->maskTelefone($t->numero).'</b>';
                    }
                    echo implode(',',$tels);
                }
                echo 'End.: <b>'.$venda->cliente->endereco.', '.$venda->cliente->numero.'</b>Ref.: <b>'.$venda->cliente->referencia.'</b>';
                $localidade = explode(' - ', $venda->cliente->localidade);
                echo 'Localidade: <b>'.$localidade[0].'</b>';
                echo 'Cidade: <b>'.$localidade[1].'</b>';
                echo 'Produtos: ';
                $produtos = array();
                foreach ($venda->produtos as $pv) {
                    $produto    = $produto_controller->getProduto($pv->produto, true);
                    $produtos[]  = $produto->descricao.' - '.$produto->tipo.' - '.$produto->marca.' (R$ '.$config->maskDinheiro($pv->valor).')';
                }
                echo '<b> '.implode(',', $produtos).'</b>';
                echo 'Com Entrada: <b>'.($comEntrada?'Sim':'Nao').'</b>';
                echo 'Qtd. Parc.: <b>'.  count($parcelas).'</b>';
                echo 'Parcela 1: <b>'.($primeira_parcela == null ? '----' : 'R$' . $config->maskDinheiro($primeira_parcela->valor)).'</b>';
                echo 'Dt. Parc. 1: <b>'.($primeira_parcela == null ? '----' : $config->maskData($primeira_parcela->validade)).'</b>';
                echo 'Dt. Entrega:<b>(___/___/______)</b>';
                echo 'Recebido:______';
                echo '</div>';
            }

            echo '</div>';
        }
    
    }

    
    
    
}
?>