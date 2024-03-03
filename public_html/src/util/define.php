<?php

if(!defined('DEFINES_OPTICA_CAPITAL')) {

    define('DEFINES_OPTICA_CAPITAL', 'DEFINEDS_OPTICA_CAPITAL_CONSTANTS');
    
    define("HTML", build_dir_path("src", "view"));
    define("FORMS", build_dir_path(HTML, "forms"));
    define("LISTS", build_dir_path(HTML, "list"));
    define("MENU", build_dir_path(HTML, "menu"));
    define("UTIL", build_dir_path("src", "util"));
    define("CONTENT_CONTROLLER", build_path(UTIL, "content.controller.php"));
    define("FUNCTION_CONTROLLER", build_path(UTIL, "function.controller.php"));
    define("PRINT_CONTROLLER", build_path(UTIL, "print.controller.php"));
    define("AJAX_CONTROLLER", build_path(UTIL, "ajax.controller.php"));
    define("SERVICES_FILE", build_path(UTIL, "services.ini"));

    define("SERVICES", build_dir_path("src", "ajax"));
    define("CONTROLLERS", build_dir_path("src", "control"));
    define("MODELS", build_dir_path("src", "dao"));
    define("ENTITIES", build_dir_path("src", "entity"));
    define("PRINTERS", build_dir_path("src", "print"));
    define("LIBS", build_dir_path("src", "lib"));
    
    define("LOGIN_FORM", build_path(FORMS, "login.php"));

    define("IMAGES", build_dir_path("images"));
    define("GRID_ICONS", build_dir_path(IMAGES, "grid-icons"));

    define("SESSION_ID_FUNC", "id_funcionario");
    define("SESSION_PERFIL_FUNC", "perfil_funcionario");
    define("SESSION_LOJA_FUNC", "loja_funcionario");
    define("SESSION_LOJA_SIGLA_FUNC", "loja_sigla_funcionario");
    define("SESSION_NOME_FUNC", "nome_funcionario");
    define("SESSION_CARGO_FUNC", "cargo_funcionario");

    define("MESSAGE_COOKIE", "message_cookie");
    define("PASSWORD_GERENTE_COOKIE", "pass_gerent_cookie");
    define("OPEN_CAD", "open-cad");
    define("JS_OPEN_CAD", "document.getElementById('add-btn-tool').onclick();");

    define("PERFIL_OPERADOR", '2');
    define("PERFIL_VENDEDOR", '3');
    define("PERFIL_GERENTE", '4');
    define("PERFIL_ADMINISTRADOR", '1');
    
    define("SQL_CMP_CLAUSE_LIKE", "LIKE");
    define("SQL_CMP_CLAUSE_EQUAL", "=");
    define("SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES", "= NO ''");
    define("SQL_IS_NULL_CLAUSE", 'IS_NULL');
    define("SQL_IS_NOT_NULL_CLAUSE", 'IS_NOT_NULL');
    define("SQL_CMP_BETWEEN_CLAUSE", 'BETWEEN');
    define("SQL_CMP_IN_CLAUSE", 'IN');
    define("SQL_CMP_NOT_IN_CLAUSE", 'NOT_INT');

    define("MAIN_LIMIT_DATE", 60 * 60 * 24 * 60);
    define("DAYS_OFFLINE_INTERVAL", "+3 days");

}
?>