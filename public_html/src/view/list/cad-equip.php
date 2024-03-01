<?php
$config = Config::getInstance();

$controller = $config->currentController;

$isWithForeignValues = true;
$funcionarios = $controller->getAllFuncionarios(true);

$withOperations = false;
if($_SESSION[SESSION_PERFIL_FUNC] == PERFIL_ADMINISTRADOR){
    $withOperations = true;
}


if(!empty($funcionarios)){

    echo "<h3 class=\"title-form\"> Lista de Funcionários </h3>";

    $headers = array( "nome" => "Nome",
        "cpf" => "CPF",
        "cargo" => "Cargo",
        "login" => "Login",
        "perfil" => "Perfil",
        "loja" => "Loja",
        "operations" => "Operações");

    if($withOperations) {

        $img_del = "<img src='".GRID_ICONS."remover.png"."' title='Remover Funcionário'>";
        $img_edit = "<img src='".GRID_ICONS."editar.png"."' title='Editar Funcionário'>";
        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Funcionário'>";

        for($i = 0, $l = count($funcionarios); $i < $l; $i++){
            $vars = get_object_vars($funcionarios[$i]);
            $link_del = "";
            if($vars["status"])
                $link_del = "<a href='?op=del_func&func={$vars["id"]}' class='ask' ask='desativar' >$img_del</a>";
            $link_edit = "<a href='javascript:openEditFuncionarioMode({$vars["id"]})'>$img_edit</a>";
            $service = "'ajax.php?code=6610&func={$vars["id"]}&type=html'";
            $link_view = "<a href=\"javascript:openViewDataMode($service)\"> $img_view </a>";
            $vars["operations"] = "$link_del $link_edit $link_view";
            $vars["cpf"] = $config->maskCPF($vars["cpf"]);
            $funcionarios[$i] = (object) $vars;
        }
    } else {

        $img_view = "<img src='".GRID_ICONS."visualizar.png"."' title='Visualizar Funcionário'>";
        for($i = 0, $l = count($funcionarios); $i < $l; $i++){
            $vars = get_object_vars($funcionarios[$i]);
            $vars["cpf"] = $config->maskCPF($vars["cpf"]);
            $service = "'ajax.php?code=6610&func={$vars["id"]}&type=html'";
            $link_view = "<a href=\"javascript:openViewDataMode($service)\"> $img_view </a>";
            $vars["operations"] = "$link_view";
            $funcionarios[$i] = (object) $vars;
        }
    }
    include_once LISTS."table-generic.php";

    $table = new GenericTable($headers, $funcionarios);
    $table->id = "lista-funcionarios";

    $table->draw();

} else {
    echo "<h3 class=\"title-form\"> Sem Funcionários </h3>";
}
if(!empty($funcionarios)){
    ?>
    <script>
        import dependencies from "../../../images/script/jquery";

        dependencies.push(function(){

            const lTable = $("#lista-funcionarios").dataTable({
                "bPaginate": true,
                "bJQueryUI": true,
                "sPaginationType": "full_numbers",
                "aoColumns": [
                    {"asSorting": ["desc", "asc"]},
                    {"asSorting": ["desc", "asc"]},
                    {"asSorting": ["desc", "asc"]},
                    {"asSorting": ["desc", "asc"]},
                    {"asSorting": ["desc", "asc"]},
                    {"asSorting": ["desc", "asc"]},
                    {"asSorting": []},
                ],
                "sScrollY": "250px",
                "bScrollCollapse": false
            });

            lTable.$('tr').hover( function() {
                $(this).addClass('highlighted');
            }, function() {
                lTable.$('tr.highlighted').removeClass('highlighted');
            });
        });
    </script>
<?php } ?>
