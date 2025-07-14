<?php
namespace Sisoptica\Util;

include_once CONTROLLERS.'funcionario.php';

//Obtendo controlador geral e auxiliar
$config = Config::getInstance();

//Defindo modos de acesso
define('MODE_ADMIN', 1); //Somente administrador tem acesso
define('MODE_ADMIN_DIRETOR', 1); //Somente administrador tem acesso
define('MODE_LOJA', 2); //Somente operações sobre a loja do funcionário logado pode ser efetuada
define('NO_MODE', 23); //Sem nível de acesso definido (todos podem executar)

//Lista de relatórios válidas
//Cada item desse array é um outro array contendo:
//* [Chave] o código do relatório válido
//* [src] o arquivo (script) que gera o relatório
//* [control] o prefixo do controlador desse relatório
//* [mode] o modo de acesso (ver constantes de modo de acesso);
$valids = array(
    '0001' => array('src' => PRINTERS.'carne-venda.php', 'control' => 'parcela', 'mode' => NO_MODE),
    '0002' => array('src' => PRINTERS.'relat-cobra.php', 'control' => 'regiao', 'mode' => NO_MODE),
    '0003' => array('src' => PRINTERS.'relat-venda.php', 'control' => 'regiao', 'mode' => NO_MODE),
    '0004' => array('src' => PRINTERS.'relat-parce.php', 'control' => 'regiao', 'mode' => NO_MODE),
    '0005' => array('src' => PRINTERS.'relat-lanca.php', 'control' => 'funcionario', 'mode' => NO_MODE),
    '0006' => array('src' => PRINTERS.'relat-ordem.php', 'control' => 'ordemServico', 'mode' => NO_MODE),
    '0007' => array('src' => PRINTERS.'relat-entre.php', 'control' => 'regiao', 'mode' => NO_MODE),
    '0008' => array('src' => PRINTERS.'relat-prest.php', 'control' => 'prestacaoConta', 'mode' => NO_MODE),
    '0009' => array('src' => PRINTERS.'relat-itens.php', 'control' => 'prestacaoConta', 'mode' => NO_MODE),
    '0010' => array('src' => PRINTERS.'relat-agent.php', 'control' => 'venda', 'mode' => NO_MODE),
    '0011' => array('src' => PRINTERS.'relat-combu.php', 'control' => 'veiculo', 'mode' => NO_MODE),
    '0012' => array('src' => PRINTERS.'relat-natur.php', 'control' => 'naturezaDespesa', 'mode' => NO_MODE),
    '0013' => array('src' => PRINTERS.'relat-repasse.php', 'control' => 'repasse', 'mode' => NO_MODE),
    '0014' => array('src' => PRINTERS.'relat-cancel.php', 'control' => 'venda', 'mode' => NO_MODE),
    '0015' => array('src' => PRINTERS.'impr-prest.php', 'control' => 'prestacaoConta', 'mode' => NO_MODE),
    '0016' => array('src' => PRINTERS.'relat-estoque.php', 'control' => 'centralEstoque', 'mode' => MODE_LOJA),
    '0017' => array('src' => PRINTERS.'relat-central.php', 'control' => 'centralEstoque', 'mode' => NO_MODE),
    '0018' => array('src' => PRINTERS.'relat-gerencial.php', 'control' => 'loja', 'mode' => NO_MODE),
    '0019' => array('src' => PRINTERS.'relat-equipe.php', 'control' => 'equipe', 'mode' => NO_MODE),
    '0020' => array('src' => PRINTERS.'relat-vendas-entre.php', 'control' => 'loja', 'mode' => NO_MODE),
    '0021' => array('src' => PRINTERS.'relat-venda-qtd.php', 'control' => 'venda', 'mode' => NO_MODE),
    '0022' => array('src' => PRINTERS.'relat-valores-a-receber.php', 'control' => 'venda', 'mode' => NO_MODE)
);

//Obtendo o código
$code   = $config->filter('code');

//Verificando a existencia
if(isset($valids[$code])){

    //Obtendo objeto do relatório
    $printer = (object) $valids[$code];


    //Verificando o modo de acesso
    switch ($printer->mode){
        case MODE_ADMIN_DIRETOR:
        case MODE_ADMIN:
            //Restrito ao administradores
            if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR &&
                $_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR){
                $config->failInFunction('Você não tem permissões para essa função');
                $config->redirect('index.php');
            }
            break;
        case MODE_LOJA:
            //Restrito à loja do usuário logado quando ele não um administrador
            if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR &&
                $_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR){
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

    //Carregando controlador padrão
    $config->loadCurrentController($printer->control);

    //Importando script que gera o relatório
    include_once $printer->src;

} else {
    echo 'Opção de Relatório Indisponível';
}

?>
