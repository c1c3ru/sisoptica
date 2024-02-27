<?php

//TODO Tirar coluna do cobrador quando o filtro for por região, rota e localidade.

$config = Config::getInstance();

$ini_date = $config->filter('data-inicial');
$end_date = $config->filter('data-final');

include_once CONTROLLERS.'parcela.php';
include_once CONTROLLERS.'loja.php';
$parcela_controller = new ParcelaController();
$loja_controller = new LojaController();

$lojas_id = $config->filter('loja');
$filtro = (int) $config->filter('filtro');
$filtro_recb= (int) $config->filter('filtro-receb');
$is_resumido= $config->filter('resumida') != null;

$tipos_nomes = array('BOLETO E CARNÊ', 'BOLETO', 'CARNÊ');

foreach ($lojas_id as $loja_id) {

    $loja = $loja_controller->getLoja($loja_id);

    echo '<p class=\'name\'> Relatorios de lançamentos entre <b>'.
        $config->maskData($ini_date).'</b> a <b>'.$config->maskData($end_date).
        '</b> da '.$loja->sigla.'. Tipo: <b>'.$tipos_nomes[$filtro_recb].'</b></p>';

    echo '<div class=\'content\'>';

    $tuplas = $parcela_controller->getTuplasByFiltro($loja->id, $filtro, $ini_date, $end_date, $filtro_recb);

    if(empty($tuplas)){
        echo 'Sem registros nesse período';
        echo '</div>';
        continue;
    }

    echo '<table>';

    echo '<thead>';
    echo '<tr>';
    echo '<th>Cobrador</th>';
    if($filtro) {
        switch ($filtro) {
            case 1: echo '<th>Regiao</th>'; break;
            case 2: echo '<th>Rota</th>'; break;
            case 3: echo '<th>Localidade</th>'; break;
        }
    }
    echo '<th>Tipo</th>';
    echo '<th>Qtd. Cobrancas</th>';
    echo '<th>Valor</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $count = 0;

    $sum_pb = $sum_pc = $sum_qtd_pc = $sum_qtd_pb = 0;

    if($filtro){
        $last_idx_cobrador = null;
        $last_idx_filtro = null;
        $last = null;
        foreach ($tuplas as $tupla) {

            if($tupla->nome[0] == null){ $tupla->nome[0] = "BOLETO DIRETO CAIXA"; }

            $por_boleto = $tupla->nome[count($tupla->nome) - 1];

            if($is_resumido) {

                if($tupla->nome[0] !== $last_idx_cobrador || $tupla->nome[1] !== $last_idx_filtro){

                    if($last != null) {
                        draw_rows_resumed($last, $filtro_recb, TRUE, $count);
                        $count++;
                    }

                    $last_idx_cobrador = $tupla->nome[0];
                    $last_idx_filtro = $tupla->nome[1];
                    $last = $tupla;

                    if($por_boleto){
                        $last->quantidade_b = $tupla->quantidade;
                        $last->quantidade_c = 0;
                        $last->total_b = $tupla->total;
                        $last->total_c = 0;
                    } else {
                        $last->quantidade_b = 0;
                        $last->quantidade_c = $tupla->quantidade;
                        $last->total_b = 0;
                        $last->total_c = $tupla->total;
                    }

                } else {

                    if($por_boleto){
                        $last->quantidade_b += $tupla->quantidade;
                        $last->total_b += $tupla->total;
                    } else {
                        $last->quantidade_c += $tupla->quantidade;
                        $last->total_c += $tupla->total;
                    }

                }

            } else  {

                $class = $count % 2 ? 'par' : '';

                echo '<tr class=\''.$class.'\'>';
                echo '<td>'.$tupla->nome[0].'</td>';
                echo '<td>'.$tupla->nome[1].'</td>';
                echo '<td>'.($por_boleto ? "Boleto" : "Carnê").'</td>';
                echo '<td>'.$tupla->quantidade.'</td>';
                echo '<td>R$ '.$config->maskDinheiro($tupla->total).'</td>';
                echo '</tr>';

                $count++;

            }

            if($por_boleto){
                $sum_pb += $tupla->total;
                $sum_qtd_pb += $tupla->quantidade;
            } else {
                $sum_pc += $tupla->total;
                $sum_qtd_pc += $tupla->quantidade;
            }

        }

        if($last_idx_cobrador != null){
            draw_rows_resumed($last, $filtro_recb, TRUE, $count);
        }

    } else {

        $last_idx = null;
        $last = null;
        foreach ($tuplas as $tupla) {

            if($tupla->nome[0] == null){ $tupla->nome[0] = "BOLETO DIRETO CAIXA"; }

            $por_boleto = $tupla->nome[count($tupla->nome) - 1];

            if($is_resumido) {

                if($tupla->nome[0] !== $last_idx){
                    if($last != null) {
                        draw_rows_resumed($last, $filtro_recb, FALSE, $count);
                        $count++;
                    }
                    $last_idx = $tupla->nome[0];
                    $last = $tupla;
                    if($por_boleto){
                        $last->quantidade_b = $tupla->quantidade;
                        $last->quantidade_c = 0;
                        $last->total_b = $tupla->total;
                        $last->total_c = 0;
                    } else {
                        $last->quantidade_b = 0;
                        $last->quantidade_c = $tupla->quantidade;
                        $last->total_b = 0;
                        $last->total_c = $tupla->total;
                    }
                } else {
                    if($por_boleto){
                        $last->quantidade_b += $tupla->quantidade;
                        $last->total_b += $tupla->total;
                    } else {
                        $last->quantidade_c += $tupla->quantidade;
                        $last->total_c += $tupla->total;
                    }
                }

            } else  {

                $class = $count % 2 ? 'par' : '';

                echo '<tr class=\''.$class.'\'>';
                echo '<td>'.$tupla->nome[0].'</td>';
                echo '<td>'.($por_boleto ? "Boleto" : "Carnê") .'</td>';
                echo '<td>'.$tupla->quantidade.'</td>';
                echo '<td>R$ '.$config->maskDinheiro($tupla->total).'</td>';
                echo '</tr>';

                $count++;
            }


            if($por_boleto){
                $sum_pb += $tupla->total;
                $sum_qtd_pb += $tupla->quantidade;
            } else {
                $sum_pc += $tupla->total;
                $sum_qtd_pc += $tupla->quantidade;
            }

        }

        if($last_idx != null){
            draw_rows_resumed($last, $filtro_recb, FALSE, $count);
        }

    }



    echo '</tbody>';
    echo '</table>';
    echo '<p class=\'name\' style=\'text-align:right; margin-top: 2px;background:green;color:white;\'>';
    switch ($filtro_recb){
        case 0:
            echo "  Por Boleto: <b>$sum_qtd_pb</b> | <b>R$ {$config->maskDinheiro($sum_pb)}</b>
                    Por Carnê: <b>$sum_qtd_pc</b> | <b>R$ {$config->maskDinheiro($sum_pc)}</b> ";
            break;
        case 1:
            echo " Por Boleto: <b>$sum_qtd_pb</b> | <b>R$ {$config->maskDinheiro($sum_pb)}</b>";
            break;
        case 2:
            echo "Por Carnê: <b>$sum_qtd_pc</b> | <b>R$ {$config->maskDinheiro($sum_pc)}</b>";
            break;
    }
    echo 'Total: <b>'.($sum_qtd_pb + $sum_qtd_pc).'</b> | R$ <b>'.$config->maskDinheiro($sum_pb + $sum_pc).'</b></p>';
    echo '</div>';

}

/**
 * Desenha as linhas de resumo de uma entidade no relatório resumido.
 * @param Tupla $resumed_tuple tupla coms os valores acumulados
 * @param int $modo modo de recebimento (boleto e carnê, boleto ou carnê)
 * @param boolean $filtred indica o se o tipo de filtro exige mais de uma coluna além da do cobrador
 * @param int $counter controlador de coloração das linhas
 */
function draw_rows_resumed($resumed_tuple, $modo, $filtred, $counter){
    $config = Config::getInstance();
    $class = $counter % 2 ? 'par' : '';
    echo '<tr class=\''.$class.'\'>';
    echo '<td>'.$resumed_tuple->nome[0].'</td>';
    if($filtred) {
        echo '<td>'.$resumed_tuple->nome[1].'</td>';
    }
    echo '<td>Total</td>';
    $qtd = $resumed_tuple->quantidade_c + $resumed_tuple->quantidade_b;
    $total = $resumed_tuple->total_c + $resumed_tuple->total_b;
    echo '<td>'.$qtd.'</td>';
    echo '<td>R$ '.$config->maskDinheiro($total).'</td>';
    echo '</tr>';
    if($modo == 0 || $modo == 1) {
        echo '<tr class=\''.$class.'\'>';
        echo '<td> </td>';
        if($filtred) {
            echo '<td> </td>';
        }
        echo '<td>Boleto</td>';
        echo '<td>'.$resumed_tuple->quantidade_b.'</td>';
        echo '<td>R$ '.$config->maskDinheiro($resumed_tuple->total_b).'</td>';
        echo '</tr>';
    }
    if($modo == 0 || $modo == 2) {
        echo '<tr class=\''.$class.'\'>';
        echo '<td> </td>';
        if($filtred) {
            echo '<td> </td>';
        }
        echo '<td>Carnê</td>';
        echo '<td>'.$resumed_tuple->quantidade_c.'</td>';
        echo '<td>R$ '.$config->maskDinheiro($resumed_tuple->total_c).'</td>';
        echo '</tr>';
    }
}
?>
<style>
.content tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.content tbody tr.par { background: #eee; }
</style>
