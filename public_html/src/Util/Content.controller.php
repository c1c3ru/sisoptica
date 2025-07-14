<?php
namespace Sisoptica\Util;

//Obtendo controlador geral e auxiliar 
$config = Config::getInstance();

//Captando o código da opção
$op = @ $_REQUEST["op"];

//Include for CargoModel
include_once CONTROLLERS.'funcionario.php';

//Lista de opções válidas
//Cada item desse array é um outro array contendo: 
//* [Chave] o código da opção válida
//* [form] o arquivo formulário; 
//* [list] o arquivo de lista; 
//* [control] o prefixo do controlador desse conteúdo
$valids = array(    
//MÓDULO 1
    'cad-clie' => array('form' => FORMS.'cad-clie.php', 'list' => LISTS.'cad-clie.php', 'control' => 'cliente'),
    'cad-prod' => array('form' => FORMS.'cad-prod.php', 'list' => LISTS.'cad-prod.php', 'control' => 'produto'),
    'cad-func' => array('form' => FORMS.'cad-func.php', 'list' => LISTS.'cad-func.php', 'control' => 'funcionario'),
    'cad-labo' => array('form' => FORMS.'cad-labo.php', 'list' => LISTS.'cad-labo.php', 'control' => 'laboratorio'),
    'cad-loja' => array('form' => FORMS.'cad-loja.php', 'list' => LISTS.'cad-loja.php', 'control' => 'loja'),
    'cad-regi' => array('form' => FORMS.'cad-regi.php', 'list' => LISTS.'cad-regi.php', 'control' => 'regiao'),
    'cad-loca' => array('form' => FORMS.'cad-loca.php', 'list' => LISTS.'cad-loca.php', 'control' => 'localidade'),
    'cad-rota' => array('form' => FORMS.'cad-rota.php', 'list' => LISTS.'cad-rota.php', 'control' => 'rota'),
    'cad-marc' => array('form' => FORMS.'cad-marc.php', 'list' => LISTS.'cad-marc.php', 'control' => 'produto'),
    'cad-tipo' => array('form' => FORMS.'cad-tipo.php', 'list' => LISTS.'cad-tipo.php', 'control' => 'produto'),
//MÓDULO 2
    'cad-orde' => array('form' => FORMS.'cad-orde.php', 'list' => LISTS."cad-orde.php", 'control' => 'ordemServico'), 
    'cad-vend' => array('form' => FORMS.'cad-vend.php', 'list' => LISTS."cad-vend.php", 'control' => 'venda'),
//MÓDULO 3
    'cad-lanc' => array('form' => FORMS.'cad-lanc.php', 'list' => LISTS."cad-lanc.php", 'control' => 'venda'),
//MÓDULO 4
    'rel-cobr' => array('form' => FORMS.'rel-cobr.php', 'control' => "regiao"), 
    'rel-vend' => array('form' => FORMS.'rel-vend.php', 'control' => "regiao", "not_access" => array(PERFIL_OPERADOR)), 
    'rel-parc' => array('form' => FORMS.'rel-parc.php', 'control' => "regiao", "not_access" => array(PERFIL_OPERADOR)),
//MÓDULO 5
    'cad-rene' => array('form' => FORMS.'cad-rene.php', 'list' => LISTS.'cad-rene.php', 'control' => 'venda'),
    'rel-lanc' => array('form' => FORMS.'rel-lanc.php', 'control' => 'regiao', "not_access" => array(PERFIL_OPERADOR)), 
    'rel-orde' => array('form' => FORMS.'rel-orde.php', 'control' => 'regiao'),
    'rel-entr' => array('form' => FORMS.'rel-entr.php', 'control' => 'regiao'),
//MÓDULO 6 - PRESTACAO DE CONTA
    'cad-tipo-pgmto'    => array('form' => FORMS.'cad-tipo-pgmto.php', 'list' => LISTS.'cad-tipo-pgmto.php', 'control' => 'tipoPagamento', 'not_acess' => array(PERFIL_VENDEDOR)),
    'cad-prest-conta'   => array('form' => FORMS.'cad-prest-conta.php', 'list' => LISTS.'cad-prest-conta.php', 'control' => 'prestacaoConta', 'not_access' => array(PERFIL_VENDEDOR)),
    'rel-prest'         => array('form' => FORMS.'rel-prest.php', 'control' => "prestacaoConta", "not_access" => array(PERFIL_OPERADOR)),
    'home'              => array('list' => LISTS.'prestacoes-abertas.php'),
//MÓDULO 7 - CAIXA
    'cad-veic' => array('form' => FORMS.'cad-veic.php', 'list' => LISTS.'cad-veic.php','control' => 'veiculo'),
    'cad-natu' => array('form' => FORMS.'cad-natu.php', 'list' => LISTS.'cad-natu.php','control' => 'naturezaDespesa'),
    'cad-caix' => array('form' => FORMS.'cad-caix.php', 'list' => LISTS.'cad-caix.php','control' => 'caixa', 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, PERFIL_OPERADOR, 'cargo' => array(CargoModel::COD_DIRETOR))),
//MÓDULO 8 - OUTROS RELATÓRIOS
    'rel-itens'=> array('form' => FORMS.'rel-itens-prest.php', 'control' => 'prestacaoConta', "not_access" => array(PERFIL_OPERADOR)),
    'rel-agt-v'=> array('form' => FORMS.'rel-agente-vendas.php', 'control' => 'venda', "not_access" => array(PERFIL_OPERADOR)),
    'rel-combu'=> array('form' => FORMS.'rel-combustivel.php', 'control' => 'veiculo'),
    'rel-natur'=> array('form' => FORMS.'rel-naturezas.php', 'control' => 'naturezaDespesa', "not_access" => array(PERFIL_OPERADOR)),
    'rel-gerencial'=> array('form' => FORMS.'rel-gerencial.php', 'control' => 'loja', "not_access" => array(PERFIL_OPERADOR)),
    'rel-equipe'=> array('form' => FORMS.'rel-equipe.php', 'control' => 'equipe', "not_access" => array(PERFIL_OPERADOR)),
//MÓDULO 9 - REPASSE
    'cad-repasse' => array('form' => FORMS.'cad-repasse.php', 'list' => LISTS.'cad-repasse.php', 'control' => 'repasse'),
    'rel-repasse' => array('form' => FORMS.'rel-repasse.php', 'control'=> 'repasse'),
//MÓDULO 1O - BOLETO
    'val-retornos'=> array('form' => FORMS.'retorno-boleto.php', 'control' => 'funcionario', 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR))),
    'rel-cancela' => array('form' => FORMS.'rel-cancel.php', 'control' => 'venda', 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR)), "not_access" => array(PERFIL_OPERADOR), "not_access" => array(PERFIL_OPERADOR)),
//MÓDULO 11 - ESTOQUE
    'cad-central' => array('form' => FORMS.'cad-central-estoque.php', 'list' => LISTS.'cad-central-estoque.php', 'control' => 'centralEstoque', 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR))),
    'rel-estoque' => array('form' => FORMS.'rel-estoque.php', 'control' => 'centralEstoque', 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR)), "not_access" => array(PERFIL_OPERADOR)),
    'rel-central' => array('form' => FORMS.'rel-central.php', 'control' => 'centralEstoque', 'access' => array(PERFIL_ADMINISTRADOR, PERFIL_GERENTE, 'cargo' => array(CargoModel::COD_DIRETOR)), "not_access" => array(PERFIL_OPERADOR)),
    'cad-equipe'  => array('form' => FORMS.'cad-equipe.php', 'list' => LISTS.'cad-equipe.php', 'control' => 'equipe'),
//NOMODULE
    'rel-ventr' => array('form' => FORMS.'rel-vendas-entre.php', 'control' => 'loja'),
    'rel-vend-qtd' => array('form' => FORMS.'rel-venda-qtd.php', 'control' => 'loja'),
    'rel-valores-a-receber' => array('form' => FORMS.'rel-valores-a-receber.php', 'control' => 'loja'),
);

if(empty($op)){
    $op = 'home';
}

//Verificando existência da opção (se $op é válida)
if(!empty($valids[$op])){
    
    //Obtendo a opção como objeto
    $opcao = (object) $valids[$op]; 
    
    //Verificando permissões de acesso
    if(@ isset($opcao->not_access)){
        if(in_array($_SESSION[SESSION_PERFIL_FUNC], $opcao->not_access)){
            js_redirection('<b>Permissões negadas</b>');
        }
    }
    //Verificando permissões de acesso
    if(@ isset($opcao->access)){
        if(!in_array($_SESSION[SESSION_PERFIL_FUNC], $opcao->access)){
            if( !( @ isset($opcao->access['cargo']) ) || 
                ! in_array($_SESSION[SESSION_CARGO_FUNC], $opcao->access['cargo'])
               ) {
                js_redirection('<b>Permissões negadas</b>');
            }
        }
    }

    //Caso haja um controlador settado, carrregá-lo
    if(isset($opcao->control)){
        $config->loadCurrentController($opcao->control);
    }
    //Caso haja um formulário, importá-lo
    if(isset($opcao->form) && file_exists($opcao->form)) {
        echo "<div class='mini-content'>";
        include_once $opcao->form;
        echo "</div><br/>";
    }
    //Caso haja uma lista, importá-la
    if(isset($opcao->list) && file_exists($opcao->list)){
        echo "<div class='mini-content list-mini-content' >";
        $config->addJSDependencie("$('.wait-results').remove()");
        $config->addJSDependencie("$('.generic-table').show()");
        include_once $opcao->list;
        echo "</div><br/>";
    }
    //Verificando a necessidade de abrir um formulário
    //Geralmente essa ação vem do menu lateral
    $openMode = $config->filter(OPEN_CAD);
    if(!empty($openMode)){
        $config->addJSDependencie(JS_OPEN_CAD);
    }
}

function js_redirection($msg_befeore){
    echo $msg_befeore;
    exit( "<script> window.setTimeout(function(){window.location='index.php'}, 2000)</script>" );
}

?>
