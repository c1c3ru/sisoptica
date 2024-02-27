<?php

//Habilitando a exibição dos erros
ini_set("display_errors", "0");
// error_reporting(E_CORE_WARNING);
// error_reporting(E_ALL ^ E_STRICT);
set_time_limit(0);
date_default_timezone_set("America/Fortaleza");

/**
 * Essa classe padroniza as respoas via JSON. Utlizada nos serviços ajax.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class Answer {
    /**
     * @var int código de resposta, geralmente é atribuído: 0 para sucesso e qualquer outro valor para erro.
     */
    var $code;

    /**
     * @var string messagem de retorno do serviço.
     */
    var $message;

    /**
     * @var mixed qualquer informação necessária para resposta.
     */
    var $data;

    /**
     * Essa classe padroniza as respoas via JSON. Utlizada nos serviços ajax.
     * @param int $code código de resposta
     * @param mixed $ data qualquer informação ou entidade necessária para a resposta do serviço
     * @param string $message mensagem de retorno
     * @return Answer objeto de resposta de um serviço
     */
    public function __construct($code = "0", $data = null, $message = ""){
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
    }

    /**
     * Transforma a resposta em um objeto JSON.
     * @return string objeto resposta codificado no formato JSON
     */
    public function toJSON(){
        $res = array(   "code" => $this->code,
                        "data" => $this->data,
                        "message" => $this->message );
        return json_encode($res);
    }
}

/**
 * Essa classe mapeia os atributos de um serviço AJAX registrado no arquivo <i>services.ini</i>
 */
class Service {

    /**
     * @var int código que identifica o serviço AJAX.
     */
    var $code;

    /**
     * @var string nome do arquivo que oferece o serviço
     */
    var $name;

    /**
     * @var string prefixo do controlador do serviço
     */
    var $prefix;
}

/**
 * Essa classe é o controlador geral e auxiliar do sistema. Realiza funções de utilidade geral.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class Config {

    /**
     * @var object controldor corrente da requisição. Muito utilizado para serviços e para conteúdos.
     */
    var $currentController;

    /**
     * @var string identifica o nome do vetor de dependencias do JavaScript.<br/>
     * Depedencias são trechos de código javascript que só serão executados no final da página.
     * @access private
     */
    private $dependencies = "dependencies";

    /**
     * Instancia única da classe Config
     * @var Config singleton da classe Config
     * @static
     */
    private static $instance = null;

    private static $cwd_default = null;

    /**
     * Obtém a instancia única do controlador Config
     * @return Config instancia única da classe Config
     */
    public static function getInstance(){
        if(self::$instance == null) self::$instance = new Config();
        return self::$instance;
    }

    /**
     * Essa classe é o controlador geral e auxiliar do sistema. Realiza funções de utilidade geral.
     * Ao instanciar um objeto Config, o banco de dados já efetua a conexão.
     * @return Config instancia de um controlador geral.
     */
    private function __construct(){
        $this->initDefines();
        include_once build_path(MODELS, 'database.php');
        if(!Database::connect()) {
            die ('Error ao conectar a base dados');
        }

        self::$cwd_default = getcwd();
    }

    /**
     * Encerra a conexão com o banco.
     */
    public function __destruct() {
        Database::close();
        register_log(self::$cwd_default);
    }

    /**
     * Importa e cria a maioria das constantes necessárias para a aplicação.
     * Essas defines são criadas no arquivo <i>define.php</i>.
     */
    private function initDefines(){
        include_once build_path('src', 'util', 'define.php');
        include_once build_path('html.php');
        include_once build_path('src', 'util', 'util.php');
    }

    /**
     * Importa o controlador de serviços AJAX defindo no arquivo <i>ajax.controller.php</i>
     */
    public function ajaxController(){
        include_once AJAX_CONTROLLER;
    }

    /**
     * Importa o controlador de conteúdo das páginas defindo no arquivo <i>content.controller.php</i>
     */
    public function contentController(){
        include_once CONTENT_CONTROLLER;
    }

    /**
     * Importa o controlador de funções do sistema defindo no arquivo <i>function.controller.php</i>
     */
    public function functionController(){
        include_once FUNCTION_CONTROLLER;
    }

    /**
     * Importa o controlador de geração de relatórios defindo no arquivo <i>print.controller.php</i>
     */
    public function printController(){
        include_once PRINT_CONTROLLER;
    }

    /**
     * Verifica se é necessário perguntar ao usuário (operador) se é preciso fazer o downloads de relatórios de cobrança.
     * @return bool se true, é por que existe a necessidade, se false, não existe a necessidad.
     */
    public function isAskDownload(){
       $res = isset($_SESSION["askDownload"]);
       if($res) unset ($_SESSION["askDownload"]);
       return $res;
    }

    /**
     * Filtra e obtém os valores dos parametros de requisição (GET e POST).
     * @param string $requestParam nome do parametro de requisição
     * @param bool $removeTags aplica um strip_tags no valor do parametro, retirando tags html do conteúdo
     * @return mixed valor do parametro, seja ele string, numerico ou array. Ou null em caso de inexistência.
     */
    public function filter($requestParam, $removeTags = false){
        if(isset($_REQUEST[$requestParam])){
            $requestParam = $_REQUEST[$requestParam];
            if(is_string($requestParam)) {
                $this->clearString($requestParam, $removeTags);
            } else if(is_array($requestParam)){
                for($i = 0, $l = count($requestParam); $i < $l; $i++){
                    $this->clearString($requestParam[$i], $removeTags);
                }
            }

        } else  {
            $requestParam = null;
        }
        return $requestParam;
    }

    /**
     * Limpa uma string de conteúdo inapropriado e evita SQLInjection adicionando barras antes das aspas.
     * @param string $str referência à string que se deseja 'limpar'
     * @param bool $removeTags aplica um strip_tags no valor do parametro, retirando tags html do conteúdo
     */
    public function clearString(&$str, $removeTags = false){
        if(!get_magic_quotes_gpc()) $str = addslashes($str);
        $str = trim($str);
        if($removeTags) $str = strip_tags($str);
    }

    /**
     * Adiciona uma máscara de CPF na string passada como parametro.
     * @param string $cpf valor do CPF numérico
     * @return string CPF mascarádo no padrão (xxx.xxx.xxx-xx)
     */
    public function maskCPF($cpf){
        $out_cpf = substr($cpf, 0, 3);
        $out_cpf .= ".";
        $out_cpf .= substr($cpf, 3, 3);
        $out_cpf .= ".";
        $out_cpf .= substr($cpf, 6, 3);
        $out_cpf .= "-";
        $out_cpf .= substr($cpf, 9, 2);
        return $out_cpf;
    }

    /**
     * Adiciona uma máscara de CEP na string passada como parametro.
     * @param string $cep valor do CEP numérico
     * @return string CEP mascarádo no padrão (xx.xxx-xxx)
     */
    public function maskCEP($cep){
        $out_cep = substr($cep, 0, 2);
        $out_cep .= ".";
        $out_cep .= substr($cep, 2, 3);
        $out_cep .= "-";
        $out_cep .= substr($cep, 5, 3);
        return $out_cep;
    }

    /**
     * Adiciona uma máscara de telefone na string passada como parametro.
     * @param string $telefone valor do telefone numérico
     * @return string telefone mascarádo no padrão (xx) xxxx-xxxxx ou (xx) xxxx-xxxx
     */
    public function maskTelefone($telefone){
        $out = "(".substr($telefone, 0, 2).") ";
        $out .= substr($telefone, 2, 4);
        $out .= "-";
        if (strlen($telefone) == 11) {
            $out .= substr($telefone, 6, 5);
        } else {
            $out .= substr($telefone, 6, 4);
        }
        return $out;
    }

    /**
     * Adiciona uma máscara de CNPJ na string passada como parametro.
     * @param string $cnpj valor do CNPJ numérico
     * @return string CNPJ mascarádo no padrão (xx.xxx.xxx/xxx-xx)
     */
    public function maskCNPJ($cnpj){
        $out = substr($cnpj, 0, 2);
        $out .= ".";
        $out .= substr($cnpj, 2, 3);
        $out .= ".";
        $out .= substr($cnpj, 5, 3);
        $out .= "/";
        $out .= substr($cnpj, 8, 4);
        $out .= "-";
        $out .= substr($cnpj, 12, 2);
        return $out;
    }

    /**
     * Adiciona uma máscara de data na string passada como parametro.
     * @param string $data data no padrão YYYY-MM-DD
     * @param string $sep separador da máscara
     * @return string data mascarádo no padrão (DD[sep]MM[sep]YYYY)
     */
    public function maskData($data, $sep = '/'){
        if(empty($data) || !strcmp($data, "0000-00-00")) return "";
        return date("d{$sep}m{$sep}Y", strtotime($data));
    }

    /**
     * Adiciona uma máscara monetária no valor passada como parametro.
     * @param float $valor  valor monetário numérico
     * @param string $moeda  símbolo da moeda
     * @return string valor monetário mascarado com ',' separando os centavos e '.' as milhares
     */
    public function maskDinheiro($valor, $moeda = ''){
        return $moeda . number_format($valor, 2, ",", ".");
    }

    /**
     * Lança uma resposta de sucesso para um serviço AJAX. <br/>
     * <b> * Importante: </b> Depois de chamada, essa função <b>encerra</b> o fluxo de execução com exit(0)
     * @param mixed $data informação de retorno do serviço
     * @param string $message mensagem de retorno do serviço
     */
    public function throwAjaxSuccess($data, $message = ""){
        $answer = new Answer();
        $answer->data = $data;
        $answer->message = $message;
        echo $answer->toJSON();
        exit(0);
    }

    /**
     * Lança uma resposta de falha no serviço AJAX.<br/>
     * <b> * Importante: </b> Depois de chamada, essa função <b>encerra</b> o fluxo de execução com exit(0)
     * @param string $message mensagem de retorno do serviço
     */
    public function throwAjaxError($message){
        $answer = new Answer("1", null, $message);
        echo $answer->toJSON();
        exit(0);
    }

    /**
     * Carrega um serviço AJAX com base no código.
     * @param int $servie_code identificador do serviço
     * @return Service instancia do serviço solicitado (olhar classe Service)
     */
    public function loadAjaxService($service_code){
        //Obtendo arquivo de serviço
        $str = file_get_contents(SERVICES_FILE);
        //Separando as string que definem os serviços
        $str_services = explode("\n", $str);
        foreach($str_services as $str_service){
            if(empty($str_service)) continue;
            //Obtendo detalhes do serviço
            $service = explode(":", $str_service);
            //Verificndo o código...
            if($service[0] == $service_code) {
                //Criando e retornando o serviço
                $servobj = new Service();
                $servobj->code = trim($service[0]);
                $servobj->name = trim($service[1]);
                $servobj->prefix = trim($service[2]);

                return $servobj;
            }
        }
        //Serviço não encontrado
        return null;
    }

    /**
     * Importa o arquivo que oferece o serviço <i>service</i>.
     * @param Service $service serviço em questão
     */
    public function requestService(Service $service){
        include_once SERVICES.$service->name.".php";
    }

    /**
     * Carrega o controlador corrente de um serviço e o setta como controlador corrente da aplicação.
     * @param mixed $service serviço ou string do prefixo do controlador
     */
    public function loadCurrentController($service){
        $prefix = is_string($service) ? $service : $service->prefix;
        $fullpath = CONTROLLERS.$prefix.".php";
        if(file_exists($fullpath)){
            //Importando e instanciando o controlador
            include_once $fullpath;
            $class = ucfirst($prefix)."Controller";
            $this->currentController = new $class();
        } else {
            //Controlador inexistente
            $this->throwAjaxError("Falha Interna.\nErro ao carregar controlador");
        }
    }

    /**
     * Incia o vetor de dependencias JavaScript.
     */
    public function iniJSDependencies(){
        echo "<script> var {$this->dependencies} = new Array(); </script>";
    }

    /**
     * Adiciona o trecho de código JavaScript pendente para execução final do requisição;
     * @param string $query trecho que código que vai ser encapsulado no vetor de dependencias.
     */
    public function addJSDependencie($query){
        echo "<script> {$this->dependencies}.push(function(){ $query }); </script>";
    }

    /**
     * Executa todas as pendencias de JavaScript adicionadas no vetor de pendencias.
     */
    public function execJSDependencies(){
        echo "<script>";
        echo "for(i = 0; i < {$this->dependencies}.length; i++){";
        echo "{$this->dependencies}[i]();";
        echo "}";
        echo "</script>";
    }

    /**
     * Registra em cookie a mensagem de sucesso de uma função.
     * @param string $detail detalhe do sucesso da função.
     */
    public function successInFunction($detail = null){
        //O '+' indica que é uma mensagem positiva
        $message = "+Sucesso na operação.";
        if(!is_null($detail))
            $message .= "<br/>$detail";
        $this->recordMessage($message);
    }

    /**
     * Registra em cookie a mensagem de falha de uma função.
     * @param string $detail detalhe da falha da função.
     */
    public function failInFunction($detail = null){
        //O '-' indica que é uma mensagem negativa
        $message = "-Falha na operação.";
        if(!is_null($detail))
            $message .= "<br/>$detail";
        $this->recordMessage($message);
    }

    /**
     * Registra uma mensagem por tempo indeterminado no cookie de mensagens.
     * @param string $message conteúdo da mensagem a ser registrado em cookie
     */
    private function recordMessage($message){
        setcookie(MESSAGE_COOKIE, $message, 0, "/");
    }

    /**
     * Verifica a existência de uma mensagem. Caso exista uma mensagem ela será
     * retornada em forma de um alerta JavaScript e o cookie de mensagem será limpado.
     * @return string conteúdo da mensagem ou string vazia em caso de inexistência do conteúdo.
     */
    public function checkMessage(){
        //Verificando a existência
        if(isset($_COOKIE[MESSAGE_COOKIE]) && !empty($_COOKIE[MESSAGE_COOKIE])){
            //Otendo conteúdo
            $message = $_COOKIE[MESSAGE_COOKIE];
            //Defindo tipo de alerta
            $alert = "alert";
            if(! strcmp(substr($message, 0, 1), "-") ) $alert = "badAlert";
            //Retirando indicador
            $message = substr($message, 1);
            //Definido mensagem
            $message = " $alert(\"$message\"); ";
            //Limpado o cookie de mensagem
            setcookie(MESSAGE_COOKIE, "", 1, "/");
            return $message;
        }
        return "";
    }

    /**
     * Registra a confirmação do gerente para a execução de uma funcionalidade.
     */
    public function gerentConfirm($id){
        setcookie(PASSWORD_GERENTE_COOKIE, md5("password_gerente_confirmed") . "###" . $id , 0, "/");
    }

    /**
     * Verifica se a confirmação do gerente foi efetuada.
     * @return mixed id do gerente confirmador em caso de confirmação,
     *  ou <i>false</i> caso o gerente não tenha autorizado a operação
     */
    public function checkGerentConfirm(){
        $checked = false;
        //Verificando existência e compatibilidade
        if(isset($_COOKIE[PASSWORD_GERENTE_COOKIE]) && !empty($_COOKIE[PASSWORD_GERENTE_COOKIE])){
            $str        = $_COOKIE[PASSWORD_GERENTE_COOKIE];
            $parts      = explode("###", $str);
            if(count($parts) < 2) {
                return false;
            }
            $checked    = !strcmp($parts[0], md5("password_gerente_confirmed"));
        }
        //Limpando cookie de registro
        setcookie(PASSWORD_GERENTE_COOKIE, "", 1, "/");
        return $checked ? $parts[1] : false;
    }

    /**
     * Redireciona a aplicação para uma página
     * @param string $page endereço da página destino
     * @param int $countdown atraso de redirecionamento
     */
    public function redirect($page, $countdown = 0){
        unset($_POST);
        unset($_GET);
        header("refresh: $countdown; url=$page");
        exit(0);
    }

    /**
     * Verifica se existe um usuário logado.
     * @return bool true caso exista um usuário logado na sessão, ou false caso não exista
     */
    public function isLoged(){
        //Identificador da sessão
        $this->startSession();
        
        return isset($_SESSION[SESSION_ID_FUNC]);
    }

    /**
     * Executa o procedimento de saída de um usuário da aplicação (logout).
     */
    public function logout(){
        //Identificador da sessão
        $this->startSession();
        
        if(isset($_SESSION)){

            // reset na sessão
            $_SESSION = array();
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
            session_destroy();

            $this->redirect("index.php");
        }
    }

    /**
     * Verifica se a operção de login ou logou está sendo solicitada pela requisição.
     */
    public function checkLogin(){
        $op = $this->filter("op");
        if( ! is_null($op) ) {
            switch ($op){
                case "login": $this->login(); break;
                case "logout": $this->logout(); break;
            }
        }
    }

    /**
     * Executa o procedimento de entrada de um usuário na aplicação (login).
     * Somente funcionários podem logar na aplicação.
     */
    private function login(){

        //Obtendo parametros do login
        $usuario    = $this->filter("login");
        $senha      = $this->filter("senha");

        if($usuario == null || $senha == null){
            $this->failInFunction("Todos os dados são necessários");
            $this->redirect("index.php");
        }

        //Inciando controlador de funcionários
        include_once CONTROLLERS."funcionario.php";
        $controllerFuncionario = new FuncionarioController();
        $funcionario = $controllerFuncionario->getFuncionario($usuario, $senha);

        if(empty($funcionario->id)){
            //Inexistência do funcionário
            $this->failInFunction("Dados Inválidos");
        } else if (empty ($funcionario->perfil)){
            //Perfil não apropriado para acesso da aplicação
            $this->failInFunction("Você não pode entrar no sistema. Você não tem perfil de usuário");
        } else if (!$funcionario->status) {
            //Funcionário desativado
            $this->failInFunction("Você não pode entrar no sistema. Seu usuário foi desativado");
        } else {
            //Login foi sucesso
            //Registrando informações sobre o usuário na sessão
            $this->startSession();
            
            $_SESSION[SESSION_ID_FUNC]      = $funcionario->id;
            $_SESSION[SESSION_PERFIL_FUNC]  = $funcionario->perfil;
            $_SESSION[SESSION_LOJA_FUNC]    = $funcionario->loja;
            $_SESSION[SESSION_CARGO_FUNC]   = $funcionario->cargo;

            include_once CONTROLLERS."loja.php";
            $loja_controller = new LojaController();
            $loja = $loja_controller->getLoja($funcionario->loja);
            $_SESSION[SESSION_LOJA_SIGLA_FUNC]  = $loja->sigla;

            $_SESSION[SESSION_NOME_FUNC]        = $funcionario->nome;

            //Registando pergunta sobre o download dos relatório caso o usuário seja operador
            if($funcionario->perfil == PERFIL_OPERADOR){
                $_SESSION["askDownload"] = true;
            }
        }
        $this->redirect("index.php?op=home");
    }

    public function getCurrentProfile() {
        return $_SESSION[SESSION_PERFIL_FUNC];
    }

    public function getCurrentLojaSigla() {
        return $_SESSION[SESSION_LOJA_SIGLA_FUNC];
    }

    public function getCurrentLoja() {
        return $_SESSION[SESSION_LOJA_FUNC];
    }

    private function startSession() {
        
        // Setting session id
        $SID = $this->filter("sid");
        if($SID != null) session_id($SID);

        $lifetime = 900; // 15 minutos
        session_set_cookie_params($lifetime);
        session_start();
        setcookie(session_name(), session_id(), time() + $lifetime);
    }

}

/**
 * Registra um log básico de acesso
 * @param string $cwd diretório corrente de trabalho,
 * para dá um caminho relativo ao arquivo de log
 */
function register_log($cwd){
    $linha = "[" . $_SERVER['REMOTE_ADDR'] . " " . date("H:i:s") . "]\n";
    if(isset($_SESSION[SESSION_ID_FUNC])){
        $linha .= "uid = ".$_SESSION[SESSION_ID_FUNC]."\n";
        $linha .= "uname = \"".$_SESSION[SESSION_NOME_FUNC]."\"\n";
    } else {
        $linha .= "uid = -\n";
    }
    if(isset($_COOKIE[MESSAGE_COOKIE])){
        $linha .= "msg = \"".$_COOKIE[MESSAGE_COOKIE]."\"\n";
    }
    $params = array();
    foreach($_REQUEST as $p => $v){
        if (is_array($v)) $v = implode('||', $v);
        $params[] = $p."=".$v;
    }
    $linha .= "params = \"".implode("&", $params)."\"\n";
    $linha .= "uri = \"".$_SERVER['REQUEST_URI']."\"\n";
	$log_name = date("d_m_Y") . ".log";
    $f = fopen($cwd ."/" . UTIL . "log/" . $log_name, 'a');
    fwrite($f, $linha);
    fclose($f);
}


function build_path() {
    return implode(DIRECTORY_SEPARATOR, func_get_args());
}

function build_dir_path() {
    return implode(DIRECTORY_SEPARATOR, func_get_args()) . DIRECTORY_SEPARATOR;
}

?>