<?php
include_once 'src/util/config.php';

//Obtendo controlador geral e auxiliar
$config = Config::getInstance();

//Verificando operação de login ou logout
$config->checkLogin();

//Atribuindo estado da sessão
//Indica se existe um usuário logado (true) ou não (false)
$isLoged = $config->isLoged();

//Iniciando buffer para captura de saídas de execuções de funções.
ob_start();

//Iniciando controlador de funcções
$config->functionController();

//Obtendo o conteúdo de saídas das dunções
$func_content = ob_get_clean();

//Verificando e atribuindo a existência de mensagens
$message = $config->checkMessage();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <link rel="shortcut icon" href="optica.ico" />
        <link rel="stylesheet" href="css/colors.css" type="text/css"/>
        <link rel="stylesheet" href="css/index.css" type="text/css"/>
        <link rel="stylesheet" href="css/tool.css" type="text/css"/>
        <link rel="stylesheet" href="css/jquery.dataTables.css" type="text/css"/>
        <link rel="stylesheet" href="css/le-frog/jquery-ui.css" type="text/css"/>
        <title> Óptica Capital :: Principal </title>
        <script src="script/jquery.js"></script>
        <!-- Iniciando vetor de pedencias JavaScript -->
        <?php $config->iniJSDependencies(); ?>
        <?php if(!empty($message)){ $config->addJSDependencie($message); } ?>
    </head>
    <body>
        <div id="body" class="">
        <?php
        //Condição para exibição da mensagem de manutenção no site
        //Se if(true) for declarado uma mensagem de manutenção será exibida
        if(false){
            echo "<h2 style='text-align:center; color: gray; text-shadow: 1px -1px 1px #ccc;'> Em Manutenção </h2>";
            echo "<p style='text-align:center;'><img src='".IMAGES."/manutencao.png' />";
            echo "</body>";
            echo "</html>";
            exit(0);
        }
        ?>
        <?php
        if($isLoged) {
            //Conteúdo para usuários logados
            if($config->isAskDownload()){
                //Perguntando ao operador se ele deseja realizar os downloads dos relatórios
                echo "<script> ";
                echo "if(confirm('Deseja realizar o download dos relatórois dos próximos três dias?')) { ";
                echo "      window.open('offline.php', '_blank');";
                echo "}";
                echo"</script>";
            }
        ?>
            <!--Menu space-->
            <div id="top-menu" class="box green-back">
                <ul id="menu-top-list">
                    <li class="parent-op-top-menu">
                        <div class="span-item selectable">  <a href="index.php">Home</a> </div>
                    </li>
                    <li class="parent-op-top-menu">
                        <div class="span-item">  Cadastros </div>
                        <ul class="hidden sub-menu box">
                            <li class="list-section-header"> Selecione um cadastro </li>
                            <?php
                            //Lista de opções para o menu de Cadastros
                            $opCadList = array(
                                "cad-clie" => "Cadastro de Clientes",
                                "cad-prod" => "Cadastro de Produtos",
                                "cad-loja" => "Cadastro de Lojas",
                                "cad-func" => "Cadastro de Funcionários",
                                "cad-regi" => "Cadastro de Regiões",
                                "cad-rota" => "Cadastro de Rotas",
                                "cad-loca" => "Cadastro de Localidades",
                                "cad-tipo" => "Cadastro de Tipos de Produto",
                                "cad-marc" => "Cadastro de Marcas de Produtos",
                                "cad-labo" => "Cadastro de Laboratórios",
                                "cad-equipe"        => "Cadastro de Equipes",
                                "cad-tipo-pgmto"    => "Cadastro de Tipos de Recebimento",
                                "cad-veic" => "Cadastro de Veículos",
                                "cad-natu" => "Natureza de Despesa de Caixa"
                            );
                            asort($opCadList, SORT_STRING);
                            foreach($opCadList as $op => $val_op){ ?>
                                <li class="sub-menu-item selectable"><a href="?op=<?php echo $op ."\">" . $val_op; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li class="parent-op-top-menu">
                        <div class="span-item">  Vendas </div>
                        <ul class="hidden sub-menu box">
                            <li class="list-section-header"> Selecione uma opção de venda </li>
                            <?php
                            //Lista de opções para o menu de Vendas
                            $opVendList = array(
                                "cad-orde"      => "Ordem de Serviços",
                                "cad-vend"      => "Cadastro de Vendas",
                                "cad-repasse"   => "Cadastro de Repasse"
                            );
                            asort($opVendList, SORT_STRING);
                            foreach($opVendList as $op => $val_op){ ?>
                                <li class="sub-menu-item selectable"><a href="?op=<?php echo $op ."\">" . $val_op; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li class="parent-op-top-menu">
                        <div class="span-item">  Cobrança </div>
                        <ul class="hidden sub-menu box">
                            <li class="list-section-header"> Selecione uma opção de cobrança </li>
                            <?php
                            //Lista de opções para o menu de Cobrança
                            $opLanList = array(
                                "cad-lanc" => "Lançamentos",
                                "cad-rene" => "Renegociação",
                                "cad-prest-conta"   => "Prestação de Conta",
                                "val-retornos"      => "Validação de Retornos"
                            );
                            asort($opLanList, SORT_STRING);
                            foreach($opLanList as $op => $val_op){ ?>
                                <li class="sub-menu-item selectable"><a href="?op=<?php echo $op ."\">" . $val_op; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li class="parent-op-top-menu">
                        <div class="span-item selectable"><a href="?op=cad-caix">Caixa Diário</a></div>
                    </li>
                    <li class="parent-op-top-menu">
                        <div class="span-item selectable"><a href="?op=cad-central">Central de Estoque</a></div>
                    </li>
                    <li class="parent-op-top-menu">
                        <div class="span-item"> Relatórios </div>
                        <div class="hidden sub-menu box dual-submenu">
                        <ul style="list-style: none">
                            <li class="list-section-header"> Selecione um relatório </li>
                            <?php
                            //Lista de opções para o menu de Relatórios
                            $opRelList = array(
                                "rel-cobr" => "Rel. Cobrança",
                                "rel-vend" => "Rel. Vendas",
                                "rel-parc" => "Rel. Parcelas Atrasadas",
                                "rel-lanc" => "Rel. Lançamentos",
                                "rel-orde" => "Rel. OS",
                                "rel-entr" => "Rel. Entregas",
                                "rel-prest"=> "Rel. Prestação Conta",
                                "rel-itens"=> "Rel. Itens de Prestação de Conta",
                                "rel-agt-v"=> "Rel. Vendas p/ Agentes",
                                "rel-ventr"=>  "Rel. Vendas Entregues",
                                "rel-valores-a-receber" => "Rel. Valores a Receber"
                            );
                            asort($opRelList, SORT_STRING);
                            foreach($opRelList as $op => $val_op){ ?>
                                <li class="sub-menu-item selectable"><a href="?op=<?php echo $op ."\">" . $val_op; ?></a> </li>
                            <?php } ?>
                        </ul>
                        <ul style="list-style: none">
                            <li class="list-section-header"> Selecione um relatório </li>
                            <?php
                            //Lista de opções para o menu de Relatórios
                            $opRelList_2 = array(
                                "rel-combu"=> "Rel. Combustível",
                                "rel-natur"=> "Rel. Naturezas",
                                "rel-repasse" => "Rel. Repasses",
                                "rel-cancela" => "Rel. Cancelamento",
                                "rel-estoque" => "Rel. Estoque",
                                "rel-central" => "Rel. Movimentações do Estoque",
                                "rel-gerencial" => "Rel. Gerencial",
                                "rel-equipe"    => "Rel. Vendas por Equipes",
                                "rel-vend-qtd"    => "Rel. Vendas Quitadas"
                            );
                            asort($opRelList_2, SORT_STRING);
                            foreach($opRelList_2 as $op => $val_op){ ?>
                                <li class="sub-menu-item selectable"><a href="?op=<?php echo $op ."\">" . $val_op; ?></a> </li>
                            <?php } ?>
                            <li class="sub-menu-item selectable" ><a href="offline.php" target="_blank">Rel. Off Line</a></li>
                        </ul>
                        </div>
                        <?php $opRelList = array_merge($opRelList, $opRelList_2); ?>
                    </li>
                </ul>
                <!--Logo space-->
                <img id="logo" src="images/logo.jpg" />
                <!--Welcome space-->
                <div id="welcome-space">
                    <span> Bem vindo <b><?php echo $_SESSION[SESSION_NOME_FUNC]?></b></span>
                    <a href="?op=logout"><div class="selectable btn" id="logout-btn"> Sair </div></a>
                </div>
            </div>
            <!--Toolbar vertical esquerda (Menu Rápido)-->
            <?php include_once HTML."main-toolbar.html"; ?>
            <!--Conteúdo central da aplicação-->
            <div id="content" class="box green-grad-back">
                <?php
                //Construíndo lista de opções (submenus)
                $father_ops = array(
                    'cad-caix' => 'Caixa Diário',
                    'cad-central' => 'Estoque'
                );
                $ops = array_merge($opCadList, $opLanList, $opVendList, $opRelList, $father_ops);
                $op = $config->filter("op");
                if($op != null && isset($ops[$op])){
                    //Criando faixa de navegação
                    $message = $ops[$op];
                    echo "<p class='title-form' style='border-bottom:lightgray dashed 1px;'> ";
                    echo $message;
                    echo "</p>";
                }

                //Inserindo o conteúdo de saída da execução de uma função
                echo $func_content;

                //Iniciando controlador de conteúdo
                $config->contentController();
                ?>
            </div>
        </div>
        <!--Painel auxiliar modal de conteúdo-->
        <div id="view-data-back" class="hidden">
            <a href="javascript:;" onclick="closeViewDataMode()" class="close-data-view"> x </a>
            <div class="content">
            </div>
        </div>
        <?php } else {
            //Conteúdo para usuários não logados
            //Incluindo o painel de logon
            include_once LOGIN_FORM;
         } ?>
        <!--Painel de alerta-->
        <div id="alert" class="hidden">
            <a href="javascript:closeAlert()" id="close-alert" title='Fechar'> X </a>
            <div id="content-alert"> </div>
        </div>
    </body>
    <?php if($isLoged) {?>
    <script src="script/jquery-ui.js"></script>
    <script src="script/datatable.js"></script>
    <?php } ?>
    <script src="script/index.js"></script>
    <script src="script/tool.js"></script>
    <?php $config->execJSDependencies(); ?>
</html>
