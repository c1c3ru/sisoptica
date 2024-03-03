<?php
require_once '../../util/define.php';

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR) {
    $config = Config::getInstance();
    ?>

    <form action="?op=add_equipe_vendas" method="post" id="form-cad-equipe-vendas">
        <div class="tool-bar-form" form="form-cad-equipe-vendas">
            <div onclick="openAddSpaceForm(this)" id='add-btn-tool' class="tool-button add-btn-tool-box"> Adicionar </div>
            <div onclick="closeAddSpaceForm(this)" class="tool-button cancel-btn-tool-box"> Cancelar </div>
        </div>
        <div class="hidden add-space-this-form">
            <fieldset>
                <legend>&nbsp;Informações sobre a equipe de vendas&nbsp;</legend>
                <table cellspacing="20" class="center">
                    <tr>
                        <td colspan="2">
                            <label> Nome: <br/>
                                <input type="text" class="input text-input" id="nome-equipe-vendas" name="nome" required/>
                            </label>
                        </td>
                        <td>
                            <label> Loja: <br/>
                                <select name="id_loja" class="input select-input gray-grad-back" id="loja-equipe-vendas" required>
                                    <option value=""> Selecione uma loja </option>
                                    <?php
                                    include_once CONTROLLERS."loja.php";
                                    $loja_controller = new LojaController();
                                    $isWithFoerignValues = false;
                                    $lojas = $loja_controller->getAllLojas(false);
                                    foreach($lojas as $loja){ ?>
                                        <option value="<?php echo $loja->id; ?>"><?php echo $loja->nome;  ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </td>
                        <td>
                            <label> Funcionário Líder: <br/>
                                <select name="id_funcionario_lider" class="input select-input gray-grad-back" id="lider-equipe-vendas" required>
                                    <option value=""> Selecione um funcionário líder </option>
                                    <?php
                                    // Aqui você precisa recuperar uma lista de funcionários que podem ser líderes da equipe de vendas
                                    // Substitua este bloco de código pelo código apropriado que recupera essa lista do banco de dados
                                    $funcionarios = $config->currentController->getAllFuncionarios(); // Exemplo fictício
                                    foreach($funcionarios as $funcionario){ ?>
                                        <option value="<?php echo $funcionario->id; ?>"><?php echo $funcionario->nome; ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </td>
                    </tr>
                </table>
                <p style="text-align: right;">
                    <input type="submit" class="btn submit green3-grad-back" name="submit" id="submit-form-equipe-vendas" value="Cadastrar"/>
                </p>
            </fieldset>
        </div>
    </form>

    <style>
        #form-cad-equipe-vendas table {width: 90%}
        #form-cad-equipe-vendas table td {text-align: left; width: 25%;}
        #form-cad-equipe-vendas table .text-input{text-transform: uppercase;}
        #form-cad-equipe-vendas table input[type="text"]{width: 100%;}
        #form-cad-equipe-vendas table select{width:100%;}
    </style>

    <script>
        <?php if(defined("MODE_AJAX")){?>
        function addEquipeVendas(){
            const equipe = {
                "nome": $("#nome-equipe-vendas").val(),
                "id_loja": $("#loja-equipe-vendas").val(),
                "id_funcionario_lider": $("#lider-equipe-vendas").val()
            };
            const url = "ajax.php?code=XXXX"; // Substitua XXXX pelo código correto para adicionar uma equipe de vendas
            post(url, equipe, function(data){
                if(data.code === "0"){
                    // Equipe adicionada com sucesso
                    const equipe = data.data;
                    // Aqui você pode adicionar qualquer ação desejada após adicionar a equipe de vendas
                } else {
                    // Ocorreu um erro ao adicionar a equipe de vendas
                    badAlert(data.message);
                }
            });
        }
        <?php } ?>
    </script>

<?php } ?>
