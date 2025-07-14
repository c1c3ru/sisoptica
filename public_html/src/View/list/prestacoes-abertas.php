<div style="width: 50%;font-size: 9pt;">
    <h2 class="title-form"> Prestações de Conta em Aberto </h2>
    <hr style="margin-bottom: 10px;"/>
<?php 

$config = Config::getInstance();

include_once CONTROLLERS.'prestacaoConta.php';
include_once CONTROLLERS.'funcionario.php';

$prestacao_controller   = new PrestacaoContaController();
$func_controller        = new FuncionarioController();

$byLoja = $_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR ? false : $_SESSION[SESSION_LOJA_FUNC];

$img_edit       = "<img src='".GRID_ICONS."editar.png' title='Editar Prestação de Conta'>";
$img_view       = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Prestação'>";        

$prestacoes = $prestacao_controller->getAllPrestacaoConta(false, $byLoja, true);
echo '<table class=\'table-prestacao\'>';
echo '<thead>';
echo '<tr>';
echo '<th>Loja</th>';
echo '<th>Cobrador</th>';
echo '<th>Seq.</th>';
echo '<th>Data Inicial</th>';
echo '<th>Data Final</th>';
echo '<th>Valor</th>';
echo '<th>Ações</th>';
echo '</tr>'; 
echo '</thead>';
echo '<tbody>';
$count          = 0;
$funcionarios   = array();
foreach ($prestacoes as $prestacao) {
    
    if(!array_key_exists($prestacao->cobrador, $funcionarios)){
        $funcionarios[$prestacao->cobrador] = $func_controller->getFuncionario(
            $prestacao->cobrador, '', true
        );
    }
    $cobrador = $funcionarios[$prestacao->cobrador];
    
    echo '<tr '.($count % 2 ? '' : 'class=\'par\'' ).' >';
    echo '<td>'.$cobrador->loja.'</td>';
    echo '<td>'.substr($cobrador->nome, 0, 25).'</td>';
    echo '<td>'.$prestacao->seq.'</td>';
    echo '<td>'.$config->maskData($prestacao->dtInicial).'</td>';
    echo '<td>'.$config->maskData($prestacao->dtFinal).'</td>';
    echo '<td>'.$config->maskDinheiro($prestacao_controller->getValorOfPrestacao($prestacao->id)).'</td>';
    $url = 'index.php?op=cad-prest-conta&alias_edit='.$prestacao->id;
    echo '<td>'; 
    echo "<a href='$url'>$img_edit</a>";
    echo "&nbsp;&nbsp;";
    $service = "ajax.php?code=8881&type=html&prest={$prestacao->id}";
    echo "<a href='javascript:;' onclick=\"openViewDataMode('$service')\">$img_view</a>";
    echo '</td>';
    echo '</tr>';
    $count++;
}
echo '</tbody>';
echo '</table>';
?>
</div>
<style>
.table-prestacao {text-align: center;width: 100%;}
.table-prestacao thead th { background: gray; color: white; padding: 5px; border: #666 solid 1px;}
.table-prestacao tbody tr td { padding: 5px; border-bottom: lightgray solid 1px; border-left: lightgray solid 1px; }
.table-prestacao tbody tr.par { background: #eee; }
</style>