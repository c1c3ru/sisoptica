<?php

$config = Config::getInstance();

$id_equipe = $config->filter('equipe');
if(empty($id_equipe)) $config->throwAjaxError('Sem Equipe');

$is_html = $config->filter('type') == 'html';

if(is_array($id_equipe)) {

    foreach ($id_equipe as $id) {
        $equipe = $config->currentController->getEquipe($id, true);
        if(empty($equipe->id)) continue;
        $equipe->integrantes = $config->currentController->getIntegrantes($equipe, true);
        html($equipe);
        
        echo vsep();
        echo '<hr/>';
        echo vsep();
        
    }
    
} else {
 
    $equipe = $config->currentController->getEquipe($id_equipe, $is_html);
    if(empty($equipe->id)) $config->throwAjaxError('Equipe Inválida');


    $equipe->integrantes = $config->currentController->getIntegrantes($equipe, $is_html);

    if(!$is_html){

        include_once CONTROLLERS.'funcionario.php';
        $funcController = new FuncionarioController();

        foreach($equipe->integrantes as &$integrante) {
            $funcionario = $funcController->getFuncionario($integrante->funcionario);
            $integrante->funcionario_nome = $funcionario->nome;
        }

        $liderFunc = $funcController->getFuncionario($equipe->lider);
        $equipe->lojaLider = $liderFunc->loja;


        $config->throwAjaxSuccess(get_object_vars($equipe));
    }

    html($equipe);

    
}
function html(Equipe $equipe){
    $config = Config::getInstance();
?>
<h3>Equipe <?php echo $equipe->nome;?></h3>
<label>Nome:<span><?php echo $equipe->nome;?></span></label>
<label>Loja:<span><?php echo $equipe->loja;?></span></label>
<br/>
<label>Líder:<span><?php echo $equipe->lider;?></span></label>
<br/>
<h4> Integrantes </h4>
<ul>
<?php foreach ($equipe->integrantes as $integrante) { ?>
    <li> <?php echo $integrante->funcionario?> 
        <span class="info-input">(entrou em <?php echo $config->maskData($integrante->dataEntrada)?>)</span> 
    </li>
<?php } ?>
</ul>
<?php } ?>
