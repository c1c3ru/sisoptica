<?php

//Impotando os modelos e as entidades relacionadas
include_once ENTITIES.'item-prestacao-conta.php';
include_once ENTITIES.'prestacao-conta.php';
include_once MODELS.'item-prestacao-conta.php';
include_once MODELS.'prestacao-conta.php';

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de tipos de
 * prestação de contas e de itens de prestação de contas.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class PrestacaoContaController {

    /**
     * @var PrestacaoContaModel instancia do modelo de prestações de conta
     * @access private
     */
    private $modelPrestacao;
    
    /**
     * @var ItemPrestacaoContaModel instancia do modelo de itens de prestação de conta
     * @access private
     */
    private $modelItem;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de tipos de
     * prestação de contas e de itens de prestação de contas.
     * @return PrestacaoContaController instância de um controlador de prestação de conta
     */
    public function __construct() {
        $this->modelPrestacao   = new PrestacaoContaModel();
        $this->modelItem        = new ItemPrestacaoContaModel();
    }
    
    /**
     * Este método adiciona ou atualiza uma prestação de conta no modelo.
     * @param PrestacaoConta $prestacao prestação de conta a ser inserida ou atualizada. 
     * Se for <b>null</b> os dados da requisição serão captados e atribuídos à <i>prestacao</i>
     */
    public function addPrestacao(PrestacaoConta $prestacao = null){
        
        //Verificando a necessidade de linkar os dados da requisição
        if($prestacao == null){
            $prestacao = new PrestacaoConta();
            $this->linkPrestacao($prestacao);
        }
        
        $config = Config::getInstance();
        
        //Validando os dados informados
        if(empty($prestacao->cobrador) || empty($prestacao->dtFinal) || empty($prestacao->dtInicial)){
            $config->failInFunction('Os Campos Cobrador, Data Incial e Data Final são obrigatórios');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        
        //Checando as datas
        if(strtotime($prestacao->dtInicial) > strtotime($prestacao->dtFinal)){
            $config->failInFunction('Data final deve ser maior do que a inicial');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        
//        if(empty($prestacao->itens)){
//            $config->failInFunction('Itens inválidos');
//            $config->redirect('index.php?op=cad-prest-conta');
//        }
        
        $hasId  = $config->filter('for-update-id');
        
        //Verificando se é necessário o fechamento de uma prestação de conta
        if($prestacao->status){
            //Verificando a existência dos itens associados 
            //(uma prestação de conta só pode ser fechada quando exisytem itens associados)
            if(empty($prestacao->itens)){
                $config->failInFunction('Sem itens para fechar a prestação');
                $config->redirect('index.php?op=cad-prest-conta');
            }
            if(!empty($hasId)){
                //Validação dos valores da prestação de conta
                include_once CONTROLLERS.'parcela.php';
                $parcela_controller = new ParcelaController();
                $pagamentos         = $parcela_controller->getPagamentosByPrestacao($hasId);
                //Total contabilizado nos pagamentos
                $totalPag           = 0;
                //Total contabilizado nos itens
                $totalItens         = 0;
                foreach ($pagamentos as $p) { $totalPag += $p->valor; }
                foreach ($prestacao->itens as $i) {$totalItens += $i->valor;}
                //Validação real
                if($totalItens != $totalPag){
                    $config->failInFunction('Os totais de pagamentos e de itens deve ser iguais!');
                    $config->redirect('index.php?op=cad-prest-conta');
                }
            }
        }
        
        //Resultado da operação
        $res    = false;
        
        //Iniciando transação
        $this->modelPrestacao->begin();
        
        //Vereficando tipo de operação (inserção ou atualização)
        if(empty($hasId)){
            //Operação de inserção
            $prestacao->seq = $this->modelPrestacao->maxSeq($prestacao->cobrador) + 1;
            $res = $this->modelPrestacao->insert($prestacao);
            if($res && !empty($prestacao->itens)){
                //Adicionando os itens da prestação de contas
                $this->checkItens($prestacao);
                $res = $this->modelItem->inserts($prestacao->itens);
            }
        } else {
            //Operação de atualização
            $prestacao->id = $hasId;
            
            //Atualizando e removendo os itens
            $res =  $this->modelPrestacao->update($prestacao);
            //Caso haja novos itens...
            if(!empty($prestacao->itens)){ 
                //... Eles serão inseridos
                $this->checkItens($prestacao);
                $res =  $res && 
                        $this->modelItem->deleteFromPrestacao($prestacao->id) &&
                        $this->modelItem->inserts($prestacao->itens);
            }
        }
        if($res){
            //Sucesso na operação
            $this->modelPrestacao->commit();
            $config->successInFunction();
        }  else {
            //Falha na operação
            $this->modelPrestacao->rollBack();
            $config->failInFunction($this->modelPrestacao->handleError());
        }
        $config->redirect('index.php?op=cad-prest-conta');
    }
    
    /**
     * Atribui o id da prestação de conta aos itens e verifica a associação de 
     * itens à prestções de conta. Em caso de falha, esta função aborta a transação
     * e redireciona a página com a mensagem de erro adequada.
     * @param PrestacaoConta $prestacao prestação de conta em questão
     */
    private function checkItens(PrestacaoConta $prestacao){
        $config             = Config::getInstance();
        //Iniciando controladores
        include_once CONTROLLERS.'tipoPagamento.php';
        include_once CONTROLLERS.'caixa.php';
        $caixaController    = new CaixaController();
        $tipoController     = new TipoPagamentoController(); 
        
        //Obtendo os identificadores do tipo dinheiro
        $tipos_dinheiro     = $tipoController->tiposDinheiro(true);
        
        //Backup de caixas diários
        $caixas             = array();
        
        //Verificado sea operação é de atualização
        $hasId              = $config->filter('for-update-id');
        $isUpdate           = !empty($hasId);
        if($isUpdate){
            //Obtendo os antigos itens do tipo dinheiro
            $oldItensDin    = $this->getItensDinheiroByPrestacao($prestacao->id);
        }
        
        //Backup dos itens do tipo dinheiro
        $itensDinheiro      = array();
        foreach ($prestacao->itens as &$item){ 
            //Verificando se o tipo do item é em dinheiro
            if(in_array($item->tipo, $tipos_dinheiro)){
                
                //Otimizando obtenção de um caixa diário através do backup
                if(!isset($caixas[$item->data])){
                    $caixa = $caixaController->getCaixaByData($item->data);
                    $caixas[$item->data] = $caixa;
                } else {
                    $caixa = $caixas[$item->data];
                }
                //Em caso de inexistência de caixa diário...
                if(empty($item->id) && (empty($caixa->id) || $caixa->status != CaixaModel::STATUS_ABERTO)){
                    //... emitindo mensagem de erro e redirecionando a página
                    $this->modelItem->rollBack();
                    $config->failInFunction(
                        'Não Existe o caixa aberto para o dia '.
                        $config->maskData($item->data)
                    );
                    $config->redirect('index.php?op=cad-prest-conta');
                }
                
                if($isUpdate && !empty($item->id)){
                    
                    //Adicionando um item que está sendo "atualizado" numa lista
                    //de verificação
                    $itensDinheiro[] = $item->id;
                    
                    //Antigo item
                    $oldItem = $this->getItemPrestacaoConta($item->id);
                    
                    //Otimizando a obtenção de caixas utilizando o backup
                    if(!isset($caixas[$oldItem->data])){
                        $n_caixa = $caixaController->getCaixaByData($oldItem->data);
                        $caixas[$oldItem->data] = $n_caixa;
                    } else {
                        $n_caixa = $caixas[$oldItem->data];
                    }
                    
                    //Validando se o item que possuí caixa diário fechado foi alterado...
                    if(
                        !empty($n_caixa->id) && $n_caixa->status == CaixaModel::STATUS_FECHADO &&
                        ($item->valor != $oldItem->valor || $item->data != $oldItem->data)
                    ){
                        //... registrado mensagem de falha e redirencionando a página
                        $config->failInFunction('Inconsistência nos itens que pertecem à um caixa diário');
                        $config->redirect('index.php?op=cad-prest-conta');
                    }
                }
            }
            $item->prestacao = $prestacao->id; 
        }
        //Verifiando se algum item não foi submetido
        if(!empty($oldItensDin)){
            foreach($oldItensDin as $i){
                //Obtendo caixa...
                if(!isset($caixas[$i->data])){
                    $caixa = $caixaController->getCaixaByData($i->data);
                    $caixas[$i->data] = $caixa;
                } else {
                    $caixa = $caixas[$i->data];
                }
                
                //Validando existência caso o caixa já esteja fechado
                if(!empty($caixa->id) && $caixa->status == CaixaModel::STATUS_FECHADO){
                    if(array_search($i->id, $itensDinheiro) === FALSE){
                        $config->failInFunction('Itens de uma caixa diário não foi submetido');
                        $config->redirect('index.php?op=cad-prest-conta');
                    }
                }
            }
        }
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à prestação de conta 
     * com um objeto passado como referência
     * @param PrestacaoConta $prestacao uma referência de uma prestação de contas que 
     * vai ser preenchida com os dados da requisição
     * @access private
     */
    public function linkPrestacao(PrestacaoConta &$prestacao){
        $config = Config::getInstance();
        $prestacao->cobrador    = $config->filter('cobrador');
        $status                 = $config->filter('status');
        $prestacao->status      = !empty( $status );
        $prestacao->dtInicial   = $config->filter('data-inicial');
        $timeInicial            = strtotime($prestacao->dtInicial);
        $prestacao->dtFinal     = $config->filter('data-final');
        $timeFinal              = strtotime($prestacao->dtFinal);
        $prestacao->itens       = array();
        $itens = explode(';', $config->filter('itens-prestacao'));
        foreach ($itens as $itemStr) {
            if(empty($itemStr)) continue;
            $item   = explode(':', $itemStr);
            $base   = count($item) == 4 ? 1 : 0;
            $id     = $base == 1 ? $item[0] : 0;
            $valor  = (float) str_replace(',', '.', $item[ $base ]);
            $tipo   = (int) $item[$base + 1];
            $data   = $item[ $base + 2]; 
            $timeData   = strtotime($data);
            if(empty($data) || $timeData < $timeInicial || $timeData > $timeFinal){
                $prestacao->itens = null;
                break;
            }
            if(empty($valor) || empty($tipo)) continue;
            $prestacao->itens[] = new ItemPrestacaoConta($id, $valor, $tipo, $data);
        }
    }
    
    /**
     * Insere um item no modelo de itens de prestação de conta. A prestação de conta 
     * do item deve está com status aberto e não deve estar cancelada.
     * @param ItemPrestacaoConta $item  item a ser inserido
     * @return boolean true em caso de sucesso ou false em caso de falha
     */
    public function addItemPrestacao(ItemPrestacaoConta $item){
        $prestacaoConta = $this->getPrestacaoConta($item->prestacao);
        if($prestacaoConta->status == PrestacaoContaModel::STATUS_FECHADA ||
           $prestacaoConta->cancelada){
            return false;
        }
        $t_item = strtotime($item->data);
        $t_prest_i = strtotime($prestacaoConta->dtInicial);
        $t_prest_f = strtotime($prestacaoConta->dtFinal);
        if($t_item < $t_prest_i && $t_item > $t_prest_f){
            return false;
        }
        include_once CONTROLLERS.'tipoPagamento.php';
        include_once CONTROLLERS.'caixa.php';
        $caixaController    = new CaixaController();
        $tipoController     = new TipoPagamentoController(); 
        
        $tipos_dinheiro     = $tipoController->tiposDinheiro(true);
        
        if(in_array($item->tipo, $tipos_dinheiro)){
            
            $caixa = $caixaController->getCaixaAberto();
            
            if(empty($caixa->id) || strtotime($caixa->data) != strtotime($item->data)){
                return false;
            }
            
        }
        
        return $this->modelItem->insert($item);
    }
    
    /**
     * Remove uma prestação de conta da base de dados de acordo com o identificador passado na requisição
     */
    public function removePrestacao(){
        $config = Config::getInstance();
        if(!$config->checkGerentConfirm()){
            $config->failInFunction('Essa função necessita da autorização do gerente');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $prest  = $config->filter('prest');
        if(!empty($prest)){
            
            $itens = $this->getItensDinheiroByPrestacao($prest);
            if(!empty($itens)){
                $config->failInFunction('Existem itens em dinheiro nessa prestação de conta.');
                $config->redirect('index.php?op=cad-prest-conta');
            }
            
            $condition = PrestacaoContaModel::ID. ' = ' . $prest;
            if($this->modelPrestacao->simpleUpdate(PrestacaoContaModel::CANCELADA, '1', $condition)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelPrestacao->handleError());
            }
        } else {
            $config->failInFunction('Prestação não informada');
        }
        $config->redirect('index.php?op=cad-prest-conta');
    }
    
    /**
     * Obtém todas as prestações de conta do modelo.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indica se deve ser filtrador por loaj, se false é desconsiderado
     * @param int $status indica o status (0: abertas, 1: fechadas, -1: todas) das prestações de contas que devem ser retornadas
     * @return array lista de prestações de conta do tipo PrestacaoConta.
     */
    public function getAllPrestacaoConta($foreign_values = false, $byLoja = false, $status = -1){
        $condition = PrestacaoContaModel::CANCELADA.' = 0 ';
        if($byLoja){
            $condition .= ' AND '. FuncionarioModel::LOJA.' = '.$byLoja;
        }
        if($status != -1) {
            $condition .= ' AND ' . PrestacaoContaModel::TABLE . '.' . PrestacaoContaModel::STATUS . ' = ' . $status;
        }
        $condition .= ' ORDER BY '.PrestacaoContaModel::COBRADOR.', '.PrestacaoContaModel::SEQ;
        if($foreign_values) return $this->modelPrestacao->superSelect($condition);
        return $this->modelPrestacao->select('*', $condition);
    }
    
    /**
     * Obtém as prestações de conta com data final contida numa faixa de datas.
     * @param string $data_ini data inicial da faixa de data
     * @param string $dta_fim data final da faixa de data
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * @param int $byLoja indica se deve ser filtrador por loaj, se false é desconsiderado
     * @return todas as prestações de contas no período
     */
    public function getAllPrestacaoContaInRange($data_ini, $data_fim, $foreign_values = false, $byLoja = false){
        $condition = PrestacaoContaModel::CANCELADA.' = 0 ';
        if($byLoja){
            $condition .= ' AND '. FuncionarioModel::LOJA.' = '.$byLoja;
        }
        $condition .= ' AND ' . PrestacaoContaModel::DT_FINAL." BETWEEN '$data_ini' AND '$data_fim' ";
        $condition .= ' ORDER BY '.PrestacaoContaModel::COBRADOR.', '.PrestacaoContaModel::SEQ;
        if($foreign_values) return $this->modelPrestacao->superSelect($condition);
        return $this->modelPrestacao->select('*', $condition);
    }
    
    /**
     * Obtém uma prestação de conta em específica do modelo.
     * @param int $id_prest valor do identificador de uma prestação de conta
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return PrestacaoConta uma prestação de conta correspondente ao identificador 
     * ou uma vazia em caso de inexistência
     */
    public function getPrestacaoConta($id_prest, $foreing_values = false){
        $condition  = PrestacaoContaModel::TABLE.'.'.PrestacaoContaModel::ID.' = '.$id_prest;
        if($foreing_values) $res = $this->modelPrestacao->superSelect($condition);
        else $res = $this->modelPrestacao->select('*', $condition);
        if(empty($res)) return new PrestacaoConta();
        return $res[0];
    }
    
    /**
     * Obtém os itens associados a uma prestação de conta
     * @param int $prest identificador da prestação de conta
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de prestações de conta do tipo PrestacaoConta.
     */
    public function getItensByPrestacao($prest, $foreign_values = false){
        $condition = ItemPrestacaoContaModel::PRESTACAO.' = '.$prest;
        if($foreign_values) return $this->modelItem->superSelect($condition);
        return $this->modelItem->select('*', $condition);
    }
    
    /**
     * Obtém as prestações de conta associadas a um cobrador
     * @param int $cobrador identificador do cobrador
     * @param int $status indica o status (0: abertas, 1: fechadas, -1: todas) das prestações de contas que devem ser retornadas
     * @param string $dataInicial data inical para as datas de arrecadação (inicial e final).
     * @param string $dataFinal data final para as datas de arrecadação (inicial e final).
     * @param bool $foreignValues indica se os valores estrangeiros que se relacionam com essa entidade devem ser retornados
     * @return array lista de prestações de conta do tipo PrestacaoConta.
     */
    public function getPrestacoesByCobrador($cobrador, $status = -1, $dataInicial = '', $dataFinal = '',
                                            $foreignValues = false){
        $condition  = PrestacaoContaModel::COBRADOR.' = '.$cobrador;
        if(!empty($dataInicial) && !empty($dataFinal)){
            $condition .= ' AND ' . PrestacaoContaModel::DT_INICIAL . " >= '$dataInicial' ";
            $condition .= ' AND ' . PrestacaoContaModel::DT_FINAL . " <= '$dataFinal' ";
        }
        $condition .= ' AND '.PrestacaoContaModel::CANCELADA.' = 0 ';
        if($status != -1) {
            $condition .= ' AND ' . PrestacaoContaModel::TABLE . '.' . PrestacaoContaModel::STATUS . ' = ' . $status;
        }
        $condition .= ' ORDER BY '.PrestacaoContaModel::COBRADOR.', '.PrestacaoContaModel::SEQ;
        if($foreignValues) return $this->modelPrestacao->superSelect($condition);
        return $this->modelPrestacao->select('*', $condition);
    }
 
    /**
     * Obtém o valor de uma prestação de conta, somando os itens associados a ela.
     * @param int $prestacao identificador da prestação de conta
     * @return float soma dos valores dos itens projetados 
     */
    public function getValorOfPrestacao($prestacao){
        $condition = ItemPrestacaoContaModel::PRESTACAO.' = '.$prestacao;
        return $this->modelItem->getSomaValores($condition);
    }
    
    /**
     * Altera o status de uma prestação de conta para '0', onde significa a abertura dela.
     * Os identificadores são obtidos através da requisição.
     */
    public function reabrirPrestacao(){
        
        $config = Config::getInstance();
        if(!$config->checkGerentConfirm()){
            $config->failInFunction('Essa função necessita da autorização do gerente');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $prest      = $config->filter('prest');
        $condition  = PrestacaoContaModel::ID.' = '.$prest;
        if($this->modelPrestacao->simpleUpdate(PrestacaoContaModel::STATUS, '0', $condition)){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelPrestacao->handleError());
        }
        $config->redirect('index.php?op=cad-prest-conta');
    }
    
    /**
     * Obtén os itens de prestação de conta do tipo dinheiro lançados em uma data.
     * @param string $data a data
     * @param mixed $by_loja filtrando pela loja
     * @return array lista de itens de prestação de conta do tipo ItemPrestacaoConta
     */
    public function getItensDinheiroByData($data, $by_loja = false){
        $condition  = ItemPrestacaoContaModel::DATA." = '".$data."'";
        $condition .= ' AND '.TipoPagamentoModel::NOME." LIKE '%DINHEIRO%'";
        if($by_loja){
            $condition .= ' AND '.FuncionarioModel::LOJA.' = '.$by_loja;
        }
        return $this->modelItem->superSelect($condition);
    }
    
    /**
     * Obtém os itens de prestação de conta do tipo dinheiro
     * @param int $id_prest identificador da prestação de conta
     * @return array lista de itens de prestação de conta do tipo ItemPrestacaoConta
     */
    public function getItensDinheiroByPrestacao($id_prest){
        $condition  = ItemPrestacaoContaModel::PRESTACAO." = ".$id_prest;
        $condition .= ' AND '.TipoPagamentoModel::NOME." LIKE '%DINHEIRO%'";
        return $this->modelItem->superSelect($condition);        
    }
    
    /**
     * Obtém um item de prestação de conta em específico.
     * @param int $id_item identificador de item
     * @return ItemPrestacaoConta item correspondente ou um item vazio em caso de falha
     */
    public function getItemPrestacaoConta($id_item){
        $condition  = ItemPrestacaoContaModel::TABLE.'.'.ItemPrestacaoContaModel::ID.' = '.$id_item;
        $res        = $this->modelItem->select('*', $condition);
        if(empty($res)) return new ItemPrestacaoConta();
        return $res[0];
    }
    
    
    /**
     * Obté itens de prestação de conta ordenados por tipo numa faixa de datas.
     * @param int $loja identificador da loja do cobrador da prestação de conta dos itens
     * @param string $dataInicio data inicial
     * @param string $dataFim data final
     * @param boolean $byItemsData se true, indica que data do período é a do item, se false, indica as datas da prestação.
     * @return array lista de itens do tipo ItemPrestacaoConta
     */
    public function getItensRangeData($tipo, $loja, $dataInicio, $dataFim, $byItemsData = false){

        if (is_object($tipo)) $tipo = $tipo->id;

        $dataCols = $byItemsData ? array( ItemPrestacaoContaModel::DATA, ItemPrestacaoContaModel::DATA ) :
                                   array( PrestacaoContaModel::DT_INICIAL, PrestacaoContaModel::DT_FINAL );

        $condition  = $dataCols[0] . " >= '$dataInicio'";
        $condition .= " AND ".$dataCols[1]." <= '$dataFim'";

        $condition .= " AND ".FuncionarioModel::LOJA.' = '.$loja;

        if(!is_array($tipo)) {
            $condition .= " AND ".  ItemPrestacaoContaModel::TIPO." = ".$tipo;
        } else {
            $condition .= " AND ". ItemPrestacaoContaModel::TIPO. " IN (".implode(",", $tipo).") "; 
        }

        $condition .= " ORDER BY ".ItemPrestacaoContaModel::TIPO.','.ItemPrestacaoContaModel::PRESTACAO;
        return $this->modelItem->select('*', $condition);
    }

    /**
     * Executa a função de auditoria de uma prestção de conta.
     * Recebe os parâmetros da requisição, que são o id da prestação e o arquivo,
     * e salva esse arquivo em um diretório de auditorias.
     */
    public function auditarPrestacao(){
        $config = Config::getInstance();
        $id_presta = $config->filter('prestacao');
        if(empty($id_presta)){
            $config->failInFunction('Sem prestação');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $prestacao = $this->getPrestacaoConta($id_presta);
        if(empty($prestacao->id)){
            $config->failInFunction('Prestação de conta inválida');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $arquivo = $_FILES['arquivo'];
        if(empty($arquivo['name'])){
            $config->failInFunction('Sem Arquivo');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $f_name     = $arquivo['name'];
        $f_tmp_name = $arquivo['tmp_name'];
        $ext        = substr($f_name, strrpos($f_name, '.'));
        $n_f_name   = $prestacao->id . $ext;
        if(!move_uploaded_file($f_tmp_name, 'prestacoes_conta/' . $n_f_name)){
            $config->failInFunction('Falha ao mover arquivo');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $condition  = PrestacaoContaModel::ID . '=' . $prestacao->id; 
        $this->modelPrestacao->simpleUpdate(PrestacaoContaModel::AUDITADA, '1', $condition);
        $config->successInFunction();
        $config->redirect('index.php?op=cad-prest-conta');
    }
    
    /**
     * Hbailita o usuário a baixar o arquivo de auditoria de uma prestação de conta
     * com o identificador informado por argumentos de requisição
     */
    public function auditoria(){
        $config = Config::getInstance();
        $id_presta = $config->filter('prest');
        if(empty($id_presta)){
            $config->failInFunction('Sem prestação');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $prestacao = $this->getPrestacaoConta($id_presta, true);
        if(empty($prestacao->id)){
            $config->failInFunction('Prestação de conta inválida');
            $config->redirect('index.php?op=cad-prest-conta');
        }
        $files = array_diff(scandir('prestacoes_conta'),array('.','..'));
        foreach($files as $f){
            $id_f = substr($f, 0, strpos($f, '.'));
            $name = $prestacao->cobrador.'-'.$prestacao->seq;
            if($id_presta == $id_f){
                include UTIL.'mime.php';
                $path = 'prestacoes_conta/'.$f;
                header("Content-Type: ".  mime_content_type($path));
                header("Content-Length: ".filesize($path)); 
                header("Content-Disposition: attachment; filename=".  urldecode($name));
                readfile($path);
                exit();
            }
        }        
        $config->failInFunction('Sem Auditoria');
        $config->redirect('index.php?op=cad-prest-conta');
    }
    
}
?>
