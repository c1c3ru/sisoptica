<?php

//Obtendo controlador geral e auxiliar
$config = Config::getInstance();

//Captando operação
$op = @ $_REQUEST['op'];

//Include for CargoModel
include_once CONTROLLERS.'funcionario.php';

//Defindo modos de acesso
define('MODE_ADMIN', 1); //Somente administrador tem acesso
define('MODE_LOJA', 2); //Somente operações sobre a loja do funcionário logado pode ser efetuada
define('NO_MODE', 23); //Sem nível de acesso definido (todos podem executar)


//Lista de funções válidas
//Cada item desse array é um outro array contendo:
//* [Chave] o código da função válida
//* [control] o prefixo do controlador dessa função
//* [function] o nome da função do controlador;
//* [mode] o modo de acesso (ver constantes de modo de acesso);
$functions = array(

    'add_loca' => array('control' => 'localidade', 'function' => 'addLocalidade', 'mode' => MODE_LOJA, 'not_access' => array(PERFIL_GERENTE, PERFIL_OPERADOR)) ,
    'del_loca' => array('control' => 'localidade', 'function' => 'removerLocalidade', 'mode' => MODE_LOJA, 'not_access' => array(PERFIL_OPERADOR)),

    'add_regi' => array('control' => 'regiao', 'function' => 'addRegiao', 'mode' => MODE_LOJA, 'not_access' => array(PERFIL_GERENTE, PERFIL_OPERADOR)),
    'del_regi' => array('control' => 'regiao', 'function' => 'removerRegiao', 'mode' => MODE_LOJA),

    'add_rota' => array('control' => 'rota', 'function' => 'addRota', 'mode' => MODE_LOJA, 'not_access' => array(PERFIL_GERENTE, PERFIL_OPERADOR)),
    'del_rota' => array('control' => 'rota', 'function' => 'removeRota', 'mode' => MODE_LOJA),

    'add_loja' => array('control' => 'loja', 'function' => 'addLoja', 'mode' => MODE_ADMIN),
    'del_loja' => array('control' => 'loja', 'function' => 'removeLoja', 'mode' => MODE_ADMIN),

    'add_func' => array('control' => 'funcionario', 'function' => 'addFuncionario', 'mode' => MODE_ADMIN),
    'del_func' => array('control' => 'funcionario', 'function' => 'removeFuncionario', 'mode' => MODE_ADMIN),

    'add_clie' => array('control' => 'cliente', 'function' => 'addCliente', 'mode' => NO_MODE),
    'del_clie' => array('control' => 'cliente', 'function' => 'removeCliente', 'mode' => NO_MODE),

    'add_labo' => array('control' => 'laboratorio', 'function' => 'addLaboratorio', 'mode' => MODE_ADMIN),
    'del_labo' => array('control' => 'laboratorio', 'function' => 'removerLaboratorio', 'mode' => MODE_ADMIN),

    'add_prod' => array('control' => 'produto', 'function' => 'addProduto', 'mode' => MODE_ADMIN),
    'del_prod' => array('control' => 'produto', 'function' => 'removerProduto', 'mode' => MODE_ADMIN),

    'add_tipo' => array('control' => 'produto', 'function' => 'addTipoProduto', 'mode' => MODE_ADMIN),
    'del_tipo' => array('control' => 'produto', 'function' => 'removerTipoProduto', 'mode' => MODE_ADMIN),

    'add_marc' => array('control' => 'produto', 'function' => 'addMarca', 'mode' => MODE_ADMIN),
    'del_marc' => array('control' => 'produto', 'function' => 'removerMarca', 'mode' => MODE_ADMIN),

    'add_orde' => array('control' => 'ordemServico', 'function' => 'addOS', 'mode' => NO_MODE),
    'del_orde' => array('control' => 'ordemServico', 'function' => 'removerOS', 'mode' => NO_MODE),

    'add_vend' => array('control' => 'venda', 'function' => 'addVenda', 'mode' => MODE_LOJA),
    'del_vend' => array('control' => 'venda', 'function' => 'cancelarVenda', 'mode' => MODE_LOJA, 'access' => array(PERFIL_ADMINISTRADOR, 'cargo' => array(CargoModel::COD_DIRETOR))),

    'dt_ent_v' => array('control' => 'venda', 'function' => 'alterDataEntrega', 'mode' => NO_MODE),

    'rene_venda' => array('control' =>'venda', 'function' => 'renegociarVenda', 'mode' => NO_MODE),

    'add_tipo_pgmto' => array('control' => 'tipoPagamento', 'function' => 'addTipo', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR)),
    'del_tipo_pgmto' => array('control' => 'tipoPagamento', 'function' => 'removeTipo', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR)),

    'add_prest_conta' => array('control' => 'prestacaoConta', 'function' => 'addPrestacao', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR)),
    'del_prest_conta' => array('control' => 'prestacaoConta', 'function' => 'removePrestacao', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR, PERFIL_GERENTE)),
    'reabrir_prestacao' => array('control' => 'prestacaoConta', 'function' => 'reabrirPrestacao', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR, PERFIL_GERENTE)),

    'add_veic' => array('control' => 'veiculo', 'function' => 'addVeiculo', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR, PERFIL_GERENTE)),
    'del_veic' => array('control' => 'veiculo', 'function' => 'removeVeiculo', 'mode' => NO_MODE, 'not_access' => array(PERFIL_VENDEDOR, PERFIL_OPERADOR, PERFIL_GERENTE)),

    'add_natu' => array('control' => 'naturezaDespesa', 'function' => 'addNatureza', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, 'cargo' => array(CargoModel::COD_DIRETOR))),
    'del_natu' => array('control' => 'naturezaDespesa', 'function' => 'removeNatureza', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, 'cargo' => array(CargoModel::COD_DIRETOR))),

    'add_caix' => array('control' => 'caixa', 'function' => 'addCaixa', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, PERFIL_OPERADOR)),
    //'del_caix' => array('control' => 'naturezaDespesa', 'function' => 'removeNatureza', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE))

    'add_desp' => array('control' => 'despesa', 'function' => 'addDespesa', 'mode' => NO_MODE),
    'del_desp' => array('control' => 'despesa', 'function' => 'removeDespesa', 'mode' => NO_MODE),

    'fec_caix' => array('control' => 'caixa', 'function' => 'fecharCaixa', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE)),

    'add_repasse' => array('control' => 'repasse', 'function' => 'addRepasse', 'mode' => NO_MODE),
    'del_repasse' => array('control' => 'repasse', 'function' => 'removeRepasse', 'mode' => NO_MODE),

    'avalicao_ret'=> array('control' => 'parcela', 'function' => 'avaliacaoRetornos', 'mode' => NO_MODE),

    'cancel_parc' => array('control' => 'parcela', 'function' => 'cancelarParcelas', 'mode' => NO_MODE, 'not_access' => array(PERFIL_GERENTE, PERFIL_OPERADOR)),

    'add_central_estoque' => array('control' => 'centralEstoque', 'function' => 'addCentralEstoque', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR))),
    'valida_central'=> array('control' => 'centralEstoque', 'function' => 'validaOperacaoCentral', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR))),
    'del_central'   => array('control' => 'centralEstoque', 'function' => 'removeCentralEstoque', 'mode' => NO_MODE, 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR))),
    'up-prest'      => array('control' => 'prestacaoConta', 'function' => 'auditarPrestacao', 'mode' => NO_MODE),
    'ver-auditoria' => array('control' => 'prestacaoConta', 'function' => 'auditoria', 'mode' => NO_MODE),

    'add_equipe'    => array('control' => 'equipe', 'function' => 'addEquipe', 'mode' => NO_MODE, 'not_access' => array(PERFIL_OPERADOR)),
    'del_equipe'    => array('control' => 'equipe', 'function' => 'removeEquipe', 'mode' => NO_MODE, 'not_access' => array(PERFIL_OPERADOR))
);

//Verificando existência da função (se $op é válida)
if(!empty($functions[$op])) {

    //Obtendo a função válida como objeto
    $function = (object) $functions[$op];

    //Verificando permissões de acesso
    if(@ isset($function->not_access)){
        if(in_array($_SESSION[SESSION_PERFIL_FUNC], $function->not_access)){
            $config->failInFunction('Permissões negadas');
            $config->redirect('index.php');
        }
    }
    //Verificando permissões de acesso
    if(@ isset($function->access)){
        if(!in_array($_SESSION[SESSION_PERFIL_FUNC], $function->access)){
            $config->failInFunction('Permissões negadas');
            $config->redirect('index.php');
        }
    }


    //Verificando o modo de acesso
    switch ($function->mode){
        case MODE_ADMIN:
            //Restrito ao administradores
            if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR){
                $config->failInFunction('Você não tem permissões para essa função');
                $config->redirect('index.php');
            }
            break;
        case MODE_LOJA:
            //Restrito à loja do usuário logado quando ele não um administrador
            if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR){
                //Parametro loja da requisição
                $loja = $config->filter('loja');
                //Captando sigla da loja do usuário logado
                $loja_sigla_func = $_SESSION[SESSION_LOJA_SIGLA_FUNC];
                //Captando o identificador da loja do usuário logado
                $loja_id_func = $_SESSION[SESSION_LOJA_FUNC];
                if( strcmp($loja, $loja_id_func) && strcmp($loja, $loja_sigla_func) ) {
                    $config->failInFunction('Essa função está restrita somente a sua loja');
                    $config->redirect('index.php');
                }
            }
            break;
    }
    //Carregando controlador
    $config->loadCurrentController($function->control);
    $controller = $config->currentController;
    $func_name = $function->function;
    //Executando a função
    $controller->$func_name();
}

?>
