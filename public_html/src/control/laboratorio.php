<?php
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."laboratorio.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."laboratorio.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de laboratórios.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class LaboratorioController {
    
    /**
     * @var LaboratorioModel instancia do modelo de laboratorio usado nesse controlador
     * @access private
     */
    private $modelLaboratorio;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de laboratórios.
     * @return LaboratorioController instancia de um controlador de ordens de laboratórios
     */
    public function __construct() {
        $this->modelLaboratorio = new LaboratorioModel();
    }
    
    /**
     * Este método adiciona ou atualiza uma laboratorio no modelo.
     * @param Laboratorio $laboratorio laboratorio a ser inserida ou atualizada. 
     * Se for <b>null</b> os dados da requisição serão captados e atribuídos à <i>laboratorio</i>
     */
    public function addLaboratorio(Laboratorio $laboratorio = null){
        if($laboratorio == null){
            $laboratorio = new Laboratorio();
            //Atribuindo dados da requisição
            $this->linkLaboratorio($laboratorio);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if( empty($laboratorio->nome) ){
            $config->failInFunction("O campo Nome é necessários para inserção");
            $config->redirect("index.php?op=cad-labo");
        }
            
        $hasId = $config->filter("for-update-id");
        $res = false;
        
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inserção 
            $res = $this->modelLaboratorio->insert($laboratorio);
        } else {
            //Atualização
            $laboratorio->id = $hasId;
            $res = $this->modelLaboratorio->update($laboratorio);
        }
        
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelLaboratorio->handleError());                
        }
        
        $config->redirect("index.php?op=cad-labo");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à laboratório 
     * com um objeto passado como referência
     * @param Laboratorio $laboratorio uma referência de um laboratório que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkLaboratorio(Laboratorio &$laboratorio){
        $config = Config::getInstance();
        $laboratorio->nome = mb_strtoupper($config->filter("nome"), 'UTF-8');
        $laboratorio->telefone = $config->filter("telefone");
        $cnpj = $config->filter("cnpj");
        $laboratorio->cnpj = str_replace(array(".","-","/"), "", $cnpj);
        $laboratorio->principal = !is_null($config->filter("principal"));
    }
    
    /**
     * Remove um laboratório da base de dados com base no identificador passado na requisição.
     */
    public function removerLaboratorio(){
        $config = Config::getInstance();
        $id_laboratorio = $config->filter("labo");
        if(isset($id_laboratorio)){
            if($this->modelLaboratorio->delete($id_laboratorio)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelLaboratorio->handleError());
            }    
        } else {
            $config->failInFunction("Laboratório não informado");
        }
        $config->redirect("?op=cad-labo");
    }
    
    /**
     * Obtém todos os laboratórios do sistema.
     * @return array lista de laboratórios do tipo Laboratório
     */
    public function getAllLaboratorios(){
        return $this->modelLaboratorio->select();
    }
    
    /**
     * Obtém uma laboratório em específico.
     * @param int $id_laboratorio identificador do laboratório
     * @return Laboratorio laboratório específico, ou um laboratório vazio caso de inexistência.
     */
    public function getLaboratorio($id_laboratorio){
        $condition = LaboratorioModel::ID." = $id_laboratorio";
        $laboratorio = $this->modelLaboratorio->select("*", $condition);
        if(!empty($laboratorio)) return $laboratorio[0];
        return new Laboratorio();
    }
    
    
}
?>
