<?php

$config = Config::getInstance();

$loja   = $config->filter('loja');

if(empty($loja)){
    $regioes = $config->currentController->getAllRegioes();
} else {
    $regiao_id = $config->filter("regiao");

    if(empty($regiao_id)) {
        echo "Região Inválida";
        exit(0);
    }
    
    $regiao = $config->currentController->getRegiao($regiao_id);
    
    if(empty($regiao->id)) {
        echo "Região Inválida";
        exit(0);
    }

    $regioes = array($regiao);
}

$limit_down_date = '1970-01-01'; //$config->filter("inicial");
$limit_up_date   = $config->filter("final");

include_once CONTROLLERS.'funcionario.php';
include_once CONTROLLERS.'rota.php';
include_once CONTROLLERS.'localidade.php';
include_once CONTROLLERS.'cliente.php';
include_once CONTROLLERS.'venda.php';
include_once CONTROLLERS.'parcela.php';

$func_controller        = new FuncionarioController();
$rota_controller        = new RotaController();
$localidade_controller  = new LocalidadeController();
$parcela_controller     = new ParcelaController();
$venda_controller       = new VendaController();
$clie_controller        = new ClienteController();

foreach ($regioes as $regiao) {
    $cobrador   = $func_controller->getFuncionario($regiao->cobrador);    
    echo "<p style='border:green solid 2px;padding:5px;margin:0;background:green;color:white;' class='content'>";
    echo "Cobrador: <b> {$cobrador->nome} </b>";
    echo "Regiao: <b> {$regiao->nome} </b>";
    echo "Gerado em: <b>".date("d/m/Y")."</b>";
    /*if(!empty($limit_down_date)) {
        echo "Data Inicial: <b>".date("d/m/Y", strtotime($limit_down_date))."</b>";
    }*/
    echo "Data Final: <b>".date("d/m/Y", strtotime($limit_up_date))."</b>";
    echo "</p>";

    $rota = $config->filter('rota');

    $rotas = empty($rota) ? $rota_controller->getRotasByRegiao($regiao->id) : 
                            array($rota_controller->getRota($rota));

    $COUNTER = 0;

    foreach ($rotas as $rota) {
        $localidades = $parcela_controller->getLocalidadesWithParcelasAbertas($rota->id, $limit_up_date, $limit_down_date);
        if(empty($localidades)) continue;

        echo "<p class='name'> Rota: <b>{$rota->nome}</b> </p>";
        echo "<div class='content'>";
        foreach ($localidades as $localidade_id){

            $localidade = $localidade_controller->getLocalidade($localidade_id, true);

            $vendas = $parcela_controller->getVendaAbertasInLocalidade($localidade->id, $limit_up_date, $limit_down_date);

            foreach ($vendas as $venda_id) {

                $venda          = $venda_controller->getVenda($venda_id);
                $cliente        = $clie_controller->getCliente($venda->cliente);
                $restante_venda = $parcela_controller->getRestanteOfVenda($venda_id);
                $venda->valor   = $venda_controller->getValorOfVenda($venda_id); 
                $japago         = $venda->valor - $restante_venda;

                echo "<div class='content-venda'>";
                echo '<b>('.(++$COUNTER).")</b> Cliente: <b>{$cliente->nome} ({$cliente->apelido})</b>CPF: <b>".$config->maskCPF($cliente->cpf)."</b>";
                $cliente->telefones = $clie_controller->getAllTelefonesOfCliente($cliente->id);
                if(!empty($cliente->telefones)){
                    echo 'Telefones: ';
                    foreach ($cliente->telefones as $telefone) {
                        echo '<b>'.$config->maskTelefone($telefone->numero).'</b>';
                    }
                }
                echo " End.: <b>{$cliente->endereco}, {$cliente->numero}</b>Ref.: <b>{$cliente->referencia}</b>";
                echo " Localidade: <b>{$localidade->nome}</b>";
                echo " Cidade: <b>{$localidade->cidade}</b>";

                echo " Venda: <b>{$venda->id}</b>";
                echo " ValVenda/ ValVendaPg: <b> R$ ".$config->maskDinheiro($venda->valor)." / ".
                                               "R$ ".$config->maskDinheiro($japago)."</b>";

                $parcelas_quitadas  = $parcela_controller->getParcleasByVenda($venda_id, ParcelaController::PARCELAS_PAGAS);
                echo "UltimaParcQuit/ Val / DtUltPg : ";
                if(!empty($parcelas_quitadas)) {
                    $ultima_paga        = $parcelas_quitadas[count($parcelas_quitadas) - 1];
                    $lancamentos        = $parcela_controller->getPagamentosOfParcela($ultima_paga);
                    $ultimo_lancamento  = $lancamentos[count($lancamentos) - 1];
                    echo "<b>{$ultima_paga->numero} / ";
                    echo " R$ ".$config->maskDinheiro($ultima_paga->valor)." / ";
                    echo $config->maskData($ultimo_lancamento->data, '-')."</b>"; 

                } else {
                    echo "<b> - / - / - </b>";
                }    

                $last_pagamento = $parcela_controller->getLastPagamentoOfVenda($venda->id);
                echo "UltimpPg/ DtUltPg : ";
                echo "<b> R$ ".$config->maskDinheiro($last_pagamento->valor)." / ";
                echo $config->maskData($last_pagamento->data, '-')."</b>";
                $parcelas_em_aberto = $parcela_controller->getParcleasByVenda(  $venda->id, 
                                                                                ParcelaController::PARCELAS_NAO_PAGAS,
                                                                                $limit_up_date, $limit_down_date);
                $nums = array();
                foreach ($parcelas_em_aberto as $p) {$nums[] = $p->numero;}
                echo "<b>&nbsp;</b>Parc. Abertas: <b>(".implode(", ", $nums).")</b>";
                $mais_atrasada = $parcelas_em_aberto[0];
                echo "Valor Parc.: <b> R$ ".$config->maskDinheiro($mais_atrasada->valor).($mais_atrasada->porBoleto?" (Boleto)":"")."</b>";
                echo 'Vencimento: <b>'.$config->maskData($mais_atrasada->validade).'</b>';
                if(!empty($mais_atrasada->remarcacao))
                    echo 'DtRemarc: <b>'.$config->maskData($mais_atrasada->remarcacao).'</b>';
                $today = strtotime(date('Y-m-d'));
                $data_calc = empty($mais_atrasada->remarcacao) ? $mais_atrasada->validade : $mais_atrasada->remarcacao ; 
                $last_date = strtotime($data_calc);
                $dif =  $today - $last_date;
                echo "Dias de Atraso.: <b>".floor($dif/(60*60*24))."</b>";
                $spaces = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                $spaces .= $spaces.$spaces;
                echo "Recebido: <b style='border-bottom: #000 solid 1px; '>$spaces</b>"; 
                echo "DtRemarc: <b>_____/_____/__________</b>";
                echo "</div>";
            }

        }
        echo "</div>";
    }
}
?>
<style>
.content-venda{border: none; border-bottom: green solid 1px;font-size: 7pt;text-align:justify;line-height:1.2em}    
</style>
