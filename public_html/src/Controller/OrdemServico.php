<?php
namespace Sisoptica\Controller;
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES.'ordem.php';
include_once ENTITIES.'tupla.php';

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS.'ordem.php';
/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de ordens de serviço.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class OrdemServicoController {
    
    /**
     * @var OrdemServicoModel instancia do modelo de ordens de serviços usado nesse controlador.
     * @access private
     */
    private $modelOS;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de ordens de serviço.
     * @return OrdemServicoController instancia de um controlador de ordens de serviço
     */
    public function __construct() {
        $this->modelOS = new OrdemServicoModel();
    }
    
    /**
     * Este método adiciona ou atualiza uma ordem de serviço no modelo.
     * @param OrdemServico $os ordem de serviço a ser inserida ou atualizada. 
     * Se for <b>null</b> os dados da requisição serão captados e atribuídos à <i>ordem de serviço</i>.
     * @param bool $ajaxMode indica se a resposta deve ser gravada em cookie ou escrita em JSON.
     * @return OrdemServico se a adição for em modo <i>ajax</i>, a OS adicionada será retornada.
     */
    public function addOS(OrdemServico $os = null, $ajaxMode = false){
        if(is_null($os)){
            $os = new OrdemServico();
            //Atribuindo dados da requisição
            $this->linkOS($os);
        }
        
        $config = Config::getInstance();
        
        $messageError = $ajaxMode ? "throwAjaxError" : "failInFunction";
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if( empty($os->numero) || empty($os->dataEnvioLab) || 
            empty($os->laboratorio) || empty($os->loja) ){
            $config->$messageError("Os campos Número,  Data de envio para o Laboratótio, Laboratório e Loja são obrigatórios");
        }
        
        //Checando se a data de recebimento é menor do que a de envio.
        if(!empty($os->dataRecebimentoLab) && !empty($os->dataRecebimentoLab) &&  
           (strtotime($os->dataRecebimentoLab) < strtotime($os->dataEnvioLab)) ) {
            $config->$messageError("Data de recebimento deve ser anterior à de envio");
            $config->redirect("index.php?op=cad-orde");    
        }
        
        $hasId = $config->filter("for-update-id");
        $res = false;
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inserção
            $res = $this->modelOS->insert($os);
        } else {
            //Atualização
            //Verificando se o gerente confirmou alteração
            if(!$config->checkGerentConfirm()){
                $config->$messageError("Essa ação necessita da confirmação do gerente");
                $config->redirect("index.php?op=cad-orde");
            }
            
            $os->id = $hasId;
            $res = $this->modelOS->update($os);
            //Verificando operação de reativação
            if(!is_null($config->filter("reativar"))){
                $this->modelOS->undelete($os->id);
            }
        }
        if($res){ 
            if(!$ajaxMode){
                $config->successInFunction();
            } else {
                return $os;
            }
        } else {$config->$messageError($this->modelOS->handleError());}
        $config->redirect("index.php?op=cad-orde");
    }
    
    /**
     * Cancela uma ordem de serviço de acordo com o identificador na requisição..
     */
    public function removerOS(){
        $config = Config::getInstance();
        
        //Verificando se o gerente confirmou alteração
        if(!$config->checkGerentConfirm()){
            $config->failInFunction("Essa ação necessita da confirmação do gerente");
            $config->redirect("index.php?op=cad-orde");
        }
        
        $id_ordem = $config->filter("orde");
        if(isset($id_ordem)){
            
            //Validando a restrição de venda, ou seja, caso a OS esteja associada à uma venda
            //esta não pode ser removida (canelada)
            include_once CONTROLLERS."venda.php";
            $venda_controller = new VendaController();
            $venda = $venda_controller->getVendaByOrdemServico($id_ordem);
            if(!empty($venda->id)){
                $config->failInFunction("Não foi possível cancelar essa OS. Existe uma venda associada à ela.");
            } else {
                if($this->modelOS->delete($id_ordem)){
                    $config->successInFunction();
                } else {
                    $config->failInFunction($this->modelOS->handleError());
                }        
            }
        } else {
            $config->failInFunction("Ordem de Serviço não informação");
        }
        
        $config->redirect("?op=cad-orde");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à ordem de serviço 
     * com um objeto passado como referência.
     * @param OrdemServico $os uma referência de uma ordem de serviço que vai ser 
     * preenchida com os dados da requisição
     * @access private
     */
    private function linkOS(OrdemServico &$os){
        $config = Config::getInstance();
        $os->numero             = mb_strtoupper($config->filter("numero"), 'UTF-8');
        $os->dataEnvioLab       = $config->filter("data-envio-lab");
        $os->dataRecebimentoLab = $config->filter("data-recebimento-lab");
        if(empty($os->dataRecebimentoLab)) $os->dataRecebimentoLab = null;
        $os->armacaoLoja        = is_null($config->filter("armacao-loja"))? false: true;
        $os->valor              = (float)  str_replace(',', '.', $config->filter("valor"));
        $os->laboratorio        = $config->filter("laboratorio");
        $os->loja               = $config->filter("loja");
        $os->autor              = !empty($_SESSION[SESSION_ID_FUNC]) ? $_SESSION[SESSION_ID_FUNC] : null;
    }
 
    /**
     * Obtém uma ordem de serviço em específico.
     * @param int $id_ordem identificador da ordem de serviço
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return OrdemServico ordem de serviço específica ou uma ordem de serviço vazia em caso de inexistência
     */
    public function getOrdemServico($id_ordem, $foreign_values = false){
        $condition = OrdemServicoModel::TABLE.".".OrdemServicoModel::ID." = $id_ordem";
        if(!$foreign_values) $ordem = $this->modelOS->select("*", $condition);
        else $ordem = $this->modelOS->superSelect($condition); 
        if(empty($ordem)) return new OrdemServico();
        return $ordem[0];
    }
    
    /**
     * Obtém todas as ordens de serviço.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de ordens de serviço do tipo OrdemServico
     */
    public function getAllOrdensDeServico($foreign_values = false){
        if($foreign_values)
            return $this->modelOS->superSelect ();
        return $this->modelOS->select();
    }
    
    /**
     * Obtém as ordens de serviço de uma loja.
     * @param int $lojaid identificador de ua loja
     * @param bool $not_useds indica se somentes OS não utilizadas (não associadas a vendas) podem ser consideradas. 
     * Se true somente as não usadas serão consideradas, se false todas serão consideradas.
     * @return array lista de ordens de serviço do tipo OrdemServico
     */
    public function getOrdensDeServicoByLoja($lojaid, $not_useds = true){
        $condition = OrdemServicoModel::LOJA." = $lojaid";
        if($not_useds){
            include_once MODELS."venda.php";
            $n_condition    = "SELECT COUNT(".VendaModel::ID.") FROM ".VendaModel::TABLE." WHERE ".VendaModel::OS." = ".OrdemServicoModel::TABLE.".".OrdemServicoModel::ID;
            $condition     .= " AND ($n_condition) = 0 ";
        }
        return $this->modelOS->select("*", $condition);
    }
    
    /**
     * Esse método realiza uma busca por ordens de serviço de acordo com os <i>fields_to_search</i>
     * @param array $fields_to_search dicionário que contém os parametros da busca (ver fieldsToSearch()).
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de ordens de serviço do tipo OrdemServico como resultado da busca.
     */
    public function searchOrdens($fields_to_search, $foreign_values = false){
        //Considerando somente as OS não canceladas
        $condition  = OrdemServicoModel::TABLE.".".OrdemServicoModel::CANCELADA." = 0 ";
        $params = [];
        //Adaptando os campos de busca para o SQL
        //$f é o campo, $v[0] o valor para a busca e $v[1] o modo de comparação
        foreach ($fields_to_search as $f => $v) {
            if(empty($v[0])) continue;
            switch ($v[1]){
                case SQL_CMP_CLAUSE_EQUAL:
                    $condition .= " AND $f = ? ";
                    $params[] = $v[0];
                    break;
                case SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES:
                    $condition .= " AND $f = ? ";
                    $params[] = $v[0];
                    break;
                case SQL_CMP_CLAUSE_LIKE:
                    $condition .= " AND $f LIKE ? ";
                    $params[] = "%{$v[0]}%";
                    break;
                case SQL_IS_NULL_CLAUSE:
                    $condition.= " AND $f IS NULL ";
                    break;
                case SQL_IS_NOT_NULL_CLAUSE:
                    $condition.= " AND $f IS NOT NULL ";
                    break;
                case SQL_CMP_BETWEEN_CLAUSE:
                    $condition.= " AND $f BETWEEN ? AND ? ";
                    if (is_array($v[0]) && count($v[0]) == 2) {
                        $params[] = $v[0][0];
                        $params[] = $v[0][1];
                    } else {
                        // fallback: tenta dividir por 'AND'
                        $parts = explode(' AND ', $v[0]);
                        $params[] = $parts[0] ?? $v[0];
                        $params[] = $parts[1] ?? $v[0];
                    }
                    break;
                case SQL_CMP_IN_CLAUSE:
                    if (is_array($v[0])) {
                        $inPlaceholders = implode(',', array_fill(0, count($v[0]), '?'));
                        $condition .= " AND $f IN ($inPlaceholders) ";
                        foreach($v[0] as $val) $params[] = $val;
                    } else {
                        $condition .= " AND $f IN (?) ";
                        $params[] = $v[0];
                    }
                    break;
                case SQL_CMP_NOT_IN_CLAUSE:
                    if (is_array($v[0])) {
                        $inPlaceholders = implode(',', array_fill(0, count($v[0]), '?'));
                        $condition .= " AND $f NOT IN ($inPlaceholders) ";
                        foreach($v[0] as $val) $params[] = $val;
                    } else {
                        $condition .= " AND $f NOT IN (?) ";
                        $params[] = $v[0];
                    }
                    break;
            }
        }
        //Ordenando por data de envio
        $condition .= ' ORDER BY '.OrdemServicoModel::DT_ENVIO;
        if($foreign_values)
            return $this->modelOS->superSelect($condition); // superSelect ainda não recebe params
        return $this->modelOS->select('*', $condition, $params);
    }
    
    /**
     * Obtém uma dicionário de parametros para busca de ordens de serviço. Entre os paramtros eles:
     * <ul> 
     * <li> ID da laboratório </li>  
     * <li> Nº da OS</li>
     * <li> ID Loja </li>
     * <li> Data de envio</li>
     * <li> Data de recebimento </li>
     * </ul>
     * @return array um dicionário que tem como chaves os campos das tabelas e 
     * como valor um outro array de 2 itens:
     * o item 0 é o valor que vai ser usado na busca (default é '') e o item 1 é 
     * uma constante que define que tipo de 
     * comparação SQL vai ser usada para o parametro de busca (ver constantes SQL_CMP_* e SQL_IS_*).
     */
    public function fieldsToSearch(){
        $arr[OrdemServicoModel::TABLE.".".OrdemServicoModel::LABORATORIO]   = array("", SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES);
        $arr[OrdemServicoModel::TABLE.".".OrdemServicoModel::LOJA]          = array("", SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES);
        $arr[OrdemServicoModel::TABLE.".".OrdemServicoModel::NUMERO]        = array("", SQL_CMP_CLAUSE_LIKE);
        $arr[OrdemServicoModel::TABLE.".".OrdemServicoModel::DT_ENVIO]      = array("", SQL_CMP_CLAUSE_EQUAL);
        $arr[OrdemServicoModel::TABLE.".".OrdemServicoModel::DT_RECEBIMENTO]= array("", SQL_CMP_CLAUSE_EQUAL);
        return $arr;
    }
    
    /**
     * Obtém as tuplas agrupadas por laboratório.
     * @param array $loja identificadores das lojas
     * @param string $init_date data limite inferior da data de envio 
     * @param string $end_date data limite superior da data de envio
     * @param mixed $labsFilter identificadores dos laboratórios desejados (array ou string separandos inteiros por ',')
     * @param bool $received indica se as OS recebidas devem ser considerados. 
     * Se false, somente enviadas seão consideradas, se true todas serão consideradas
     * @return array lista de tuplas do tipo Tupla.
     */
    public function getTuplasByLab($lojas, $ini_date = '', $end_date = '', $labsFilter = '', $received = false){
        $conditions[] = OrdemServicoModel::TABLE.'.'.OrdemServicoModel::LOJA.' IN ('.implode(',',$lojas).')';
        $conditions[] = OrdemServicoModel::DT_RECEBIMENTO.' IS'.( $received ? ' NOT' : '' ).' NULL ';
        if(!empty($ini_date) && !empty($end_date)) {
            $conditions[] = OrdemServicoModel::DT_ENVIO." BETWEEN '$ini_date' AND '$end_date' ";
        }
        if(!empty($labsFilter)){
            if(is_array($labsFilter)) $labsFilter = implode (',', $labsFilter);
            $conditions[] = LaboratorioModel::TABLE.'.'.LaboratorioModel::ID.' IN ('.$labsFilter.')';
        }
        $condition = implode(' AND ', $conditions);
        $joins[] = LaboratorioModel::leftJoin('LaboratorioModel', OrdemServicoModel::LABORATORIO);
        return $this->modelOS->selectTupla(
                LaboratorioModel::TABLE.'.'.LaboratorioModel::ID, 
                $joins, $condition, LaboratorioModel::NOME
        );
    }
    
}

?>
