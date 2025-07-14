<?php
namespace Sisoptica\Controller;

//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."parcela.php";
include_once ENTITIES."pagamento.php";
include_once ENTITIES."tupla.php";
include_once ENTITIES."venda.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."parcela.php";
include_once MODELS."pagamento.php";
include_once MODELS."cargo.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de parcelas e 
 * do modelo de pagamentos.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ParcelaController {
    
    /**
     * @var ParcelaModel instancia do modelo de parcelas usado nesse controlador
     * @access private
     */
    private $modelParcela;
    
    /**
     * @var PagamentoModel instancia do modelo de pagamentos usado nesse controlador
     * @access private
     */
    private $modelPagamento;
    
    /**
     * Constante usada nos filtros de seleção multipla para identificar <b>todas as parcelas</b>
     */
    const TODAS_AS_PARCELAS = 0;
    
    /**
     * Constante usada nos filtros de seleção multipla para identificar <b>parcelas pagas</b>
     */
    const PARCELAS_PAGAS = 1;
    
    /**
     * Constante usada nos filtros de seleção multipla para identificar <b>parcelas não pagas</b>
     */
    const PARCELAS_NAO_PAGAS = 2;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de parcelas e 
     * do modelo de pagamentos.
     * @return ParcelaController instancia de um controlador de parcelas
     */
    public function __construct() {
        $this->modelParcela     = new ParcelaModel();
        $this->modelPagamento   = new PagamentoModel(); 
    }
    
    /**
     * Adiciona uma parcela no modelo
     * @param Parcela $parcela parcela que irá ser adiciona na base de dados
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function addParcela(Parcela $parcela){
        return $this->modelParcela->insert($parcela);
    }
    
    /**
     * Adiciona as parcelas de uma venda na base de dados. Os valores, datas e quatidade das parcelas
     * são calculadas com base nos dados da <i>venda</i> e dos parametros de requisição.
     * @param Venda $venda venda da qual as parcelas serão criadas
     * @param bool $is_renegociação indica se a parcela especial (parcela de entrada ou primeira parcela)
     * deve ser inserida na base da dados.
     * @return bool se todas as parcelas forem inseridas e, no caso de uma entrada, se ela foi quitada
     */
    public function addParcelas(Venda $venda, $is_renegociacao = false){
        $config = Config::getInstance();
        //Esse valor vai ser usado para calcular as parcelas
        $valorTotal = $venda->valor;
        $comEntrada = !is_null($config->filter("entrada-recebida"));
        
        if($comEntrada) {
            //Parcela especial será a entrada que já deve ser quitada
            $valorEntrada   = str_replace(',', '.', $config->filter("entrada")); 
            $dataEntrada    = $venda->dataVenda;
            $entrada        = new Parcela(0, $dataEntrada, null, $valorEntrada, true, $venda->id);
            //Decrementando o valor total com o valor da entrada
            $valorTotal    -= $valorEntrada;
        
            $uiParcela      = $entrada;
            
        } else if(!$is_renegociacao){
            //Parcela especial será parcela 1, que vai ser cobrada no dia da entrega
            $dataParcela1   = $config->filter("data-entrada");
            $valorParcela1  = str_replace(',', '.', $config->filter("entrada"));;
            $parcela1       = new Parcela(1, $dataParcela1, null, $valorParcela1, false, $venda->id);
            $valorTotal    -= $valorParcela1;
            
            $uiParcela      = $parcela1;
            
        }
        
        $qtdParcelas    = $config->filter("qtd-parcelas");
        $dataDemais     = $config->filter("data-parcela");
        //Computando as parcelas
        $parcelas       = $this->calculeParcelas($valorTotal, $qtdParcelas, 
                                                 $dataDemais, $venda->id, $comEntrada);
        //Se não for renegociação, a parcela especial será contabilizada
        if(!$is_renegociacao) $parcelas[]     = $uiParcela;
        //Adicionando as parcelas
        if(!$this->modelParcela->inserts($parcelas)) return false;
        //Caso haja entrada, ela deve ser quitada
        if($comEntrada && !$is_renegociacao){
            
            //Inserindo o item de prestação de conta
            include_once CONTROLLERS.'prestacaoConta.php';
            $prestacaoController    = new PrestacaoContaController();
            $idPrest                = $config->filter('prestacao-conta-entrada');
            $prestacaoConta         = $prestacaoController->getPrestacaoConta($idPrest);

            if (!empty($prestacaoConta->id) && $prestacaoConta->status == 0) {
                $valPrestacaoContaReal = $this->getValorOfPrestacao($prestacaoConta->id) + $entrada->valor;
                $valPrestacaoContaVirtual = $prestacaoController->getValorOfPrestacao($prestacaoConta->id);
                if ($valPrestacaoContaReal <= $valPrestacaoContaVirtual) {
                    return $this->darBaixaEmParcela(
                        $uiParcela, $entrada->valor,
                        $prestacaoConta->cobrador,
                        $entrada->validade, $prestacaoConta->id
                    );
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Obtém as parcelas da venda.
     * @param int $id_venda identificador da venda
     * @param int $flag_pagas determina que tipo de parcelas devem ser retornadas 
     * (quitadas, não quitadas ou todas). Obs.: Use as constantes de calsse.
     * @param string $limit_up_date limite superior das datas de vencimento das parcelas.
     * @param string $limit_down_date limite inferior das datas de vencimento das parcelas.
     * @param boolean $canceladas indica se as canceldas devem ser inclusas (<i>true</i>)
     * Se o valor for null é desconsiderado
     * @return array as parcelas por venda
     */
    public function getParcleasByVenda( $id_venda, $flag_pagas = 0, $limit_up_date = '', 
                                        $limit_down_date = '', $canceladas = false ){
        $condition = ParcelaModel::VENDA." = $id_venda";
        switch ($flag_pagas) {
            case self::PARCELAS_PAGAS:
                $condition .= " AND ".ParcelaModel::STATUS." = TRUE ";
                break;
            case self::PARCELAS_NAO_PAGAS:
                $condition .= " AND ".ParcelaModel::STATUS." = FALSE ";
                break;
        }
        if(!$canceladas){
            $condition .= " AND ".ParcelaModel::CANCELADA." = FALSE ";
        }
        if(!empty($limit_up_date)){
            $condition .= " AND ( ";
            $condition .= " ( ".ParcelaModel::REMARCACAO." IS NULL AND ( ".ParcelaModel::VALIDADE." <= '$limit_up_date' ".( !empty($limit_down_date) ? " AND ".ParcelaModel::VALIDADE." >= '$limit_down_date' " : " " ).") )"; 
            $condition .= " OR ";
            $condition .= " ( ".ParcelaModel::REMARCACAO." IS NOT  NULL AND ( ".ParcelaModel::REMARCACAO." <= '$limit_up_date' ".( !empty($limit_down_date) ? " AND ".ParcelaModel::REMARCACAO." >= '$limit_down_date' " : " " ).") )"; 
            $condition .= " ) ";
        }
        $condition .= " ORDER BY ".ParcelaModel::NUMERO;
        return $this->modelParcela->select("*", $condition);
    }
    
    /**
     * Obtém o último pagamento de uma venda.
     * @param int $venda_id identificador da venda
     * @return Pagamento ultimo pagamento realizado ou um pagamento vazio caso não haja pagamentos
     */
    public function getLastPagamentoOfVenda($venda_id){
        $condition  = PagamentoModel::VENDA_PARCELA." = $venda_id ";
        $condition .= " AND ".PagamentoModel::ID." = ( SELECT MAX(".PagamentoModel::ID.") FROM ".
                      PagamentoModel::TABLE." WHERE ".PagamentoModel::VENDA_PARCELA." = $venda_id ) ";
        $pagamento = $this->modelPagamento->select("*", $condition);
        if(empty($pagamento)) return new Pagamento();
        return $pagamento[0];
    }
    
    /**
     * Obtém uma parcela específica de uma venda
     * @param int $numero numero da parcela
     * @param int $venda numero da venda
     * @return Parcela a parcela de numero $numero da venda $venda, 
     * ou uma parcela vazia caso não exista.
     */
    public function getParcela($numero, $venda){
        $condition  = ParcelaModel::VENDA." = {$venda}";
        $condition .= " AND ".ParcelaModel::NUMERO." = {$numero} ";
        $parcela    = $this->modelParcela->select("*", $condition);
        if(empty($parcela)) return new Parcela();
        return $parcela[0];
    }
    
    /**
     * Obtém um pagamento específico
     * @param int $id_pgto identifiador do pagamento
     * @return Pagamento pagamento do id correspodente ou pagamento vazio caso não exista o identificador
     */
    public function getPagamento($id_pgto){
        $condition = PagamentoModel::ID."=$id_pgto";
        $pagamento = $this->modelPagamento->select("*", $condition);
        if(empty($pagamento)) return new Pagamento();
        return $pagamento[0];
    }

    public function getValorEntrega(Venda $venda) {
        $condition = PagamentoModel::VENDA_PARCELA." = ".$venda->id;
        $condition .= " AND ".PagamentoModel::DATA." = '{$venda->dataEntrega}' ";
        $total = 0.0;
        $pagamentos = $this->modelPagamento->select('*', $condition);
        foreach ($pagamentos as $pg) { $total += $pg->valor; }
        return $total;
    }
    
    /**
     * Calcula as parcelas de uam venda.
     * @param float $valor_total valor total da venda que vai ser calculada
     * @param int $qtd_parcelas quantidade de parcelas da venda
     * @param string $data_primeira data de vencimento da primeira parcela
     * @param int $id_venda identificador da venda
     * @param bool $entrada indica se a venda foi com entrada ou não.
     * @return array lista de parcelas da venda
     */
    public function calculeParcelas($valor_total, $qtd_parcelas, $data_primeira, 
                                    $id_venda = 0, $entrada = false){
        //Caso a quantidade de parcelas seja vazia
        if($qtd_parcelas <= 0) return array();
        //Definindo o incrementador do numero de parcela.
        //Se houver entrada então a primeira parcela começa com 1, caso contrário com 2
        $inc            = $entrada ? 1 : 2; 
        $parcelas       = array();
        //Determinando o valor padrão das parcelas
        $valorParcela   = $valor_total / $qtd_parcelas;
        //Caso seja um valor vazio
        if(empty($valorParcela)) return $parcelas;
        //Formatando o valor das parcelas
        $valor          = number_format($valorParcela, 2, ".", '');
        $time_primeira  = strtotime($data_primeira);
        $id_venda = $id_venda ? $id_venda : 0;
        for($i = 0; $i < $qtd_parcelas; $i++){
            $parcela = new Parcela($i+$inc);
            $parcela->validade = date("Y-m-d", strtotime("+$i month", $time_primeira));
            $parcela->valor = $valor;
            $parcela->venda = $id_venda;
            $parcelas[] = $parcela;
        }
        return $parcelas;
    }
    
    /**
     * Obtém todos os pagementos de uma parcela
     * @param Parcela $p parcela que se deseja obter os pagamentos
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de pagamentos do tipo Pagamento.
     */
    public function getPagamentosOfParcela(Parcela $p, $foreign_values = false){
        $condition  = $this->whereId($p, true);
        $condition .= " ORDER BY ".PagamentoModel::DATA;
        if($foreign_values) 
            return $this->modelPagamento->superSelect($condition);
        return $this->modelPagamento->select("*", $condition);
    }
    
    /**
     * Obtém um resumo dos lançamentos em uma loja, num dado período de tempo.
     * @param int $loja_id identificador da loja
     * @param string $dt_inicial data incial do período
     * @param string $dt_final data final do período
     * @return Tupla resumo dos lançamentos dessa loja
     */
    public function getResumoPagamentosInLoja($loja_id, $dt_inicial, $dt_final) {
        $sub_select = ' SELECT '.VendaModel::ID.' FROM '.VendaModel::TABLE.' WHERE '.VendaModel::LOJA.'='.$loja_id;
        $condition  = PagamentoModel::DATA." BETWEEN '$dt_inicial' AND '$dt_final' ";
        $condition .= ' AND ' . PagamentoModel::VENDA_PARCELA. ' IN ( '.$sub_select.' )';
        $res        = $this->modelPagamento->selectTupla(PagamentoModel::ID, array(), array(), $condition);
        if(empty($res)) return new Tupla();
        return $res[0];
    }
    
    /**
     * Obtém um resumo das parcelas atrasadas em uma loja, num dado período de tempo.
     * @param int $loja_id identificador da loja
     * @param string $dt_inicial data incial do período
     * @param string $dt_final data final do período
     * @return int quantidade das parcelas atrasadas dessa loja
     */
    public function getParcelasAtrasdasInLoja($loja_id, $dt_inicial, $dt_final){
        $condition  = "( ( ".ParcelaModel::POR_BOLETO." = TRUE AND ( 
            (".ParcelaModel::REMARCACAO." IS NULL AND ( DATE_ADD(".ParcelaModel::VALIDADE.", INTERVAL 15 DAY) <= '$dt_final' AND DATE_ADD(".ParcelaModel::VALIDADE.", INTERVAL 15 DAY) >= '".$dt_inicial."')) OR 
            (".ParcelaModel::REMARCACAO." IS NOT NULL AND ( ".ParcelaModel::REMARCACAO." <= '$dt_final' AND ".ParcelaModel::REMARCACAO." >= '".$dt_inicial."' ) ) 
        ) ) 
        OR 
        (".ParcelaModel::POR_BOLETO." = FALSE AND (
            (".ParcelaModel::REMARCACAO." IS NULL AND ( ".ParcelaModel::VALIDADE." <= '$dt_final' AND ".ParcelaModel::VALIDADE." >= '".$dt_inicial."')) OR 
            (".ParcelaModel::REMARCACAO." IS NOT NULL AND ( ".ParcelaModel::REMARCACAO." <= '$dt_final' AND ".ParcelaModel::REMARCACAO." >= '".$dt_inicial."' ) )
        ) ) )";
        $sub_select = ' SELECT '.VendaModel::ID.' FROM '.VendaModel::TABLE.' WHERE '.VendaModel::LOJA.'='.$loja_id;
        $condition .= ' AND '.ParcelaModel::VENDA. ' IN ( '.$sub_select.' )';
        return $this->modelParcela->select('*', $condition);
    }
    
    
    /**
     * Obtém as tuplas de um cobrador agrupadas ou em regiões, ou em rotas ou em localidades (depende do filtro).
     * @param int $loja loja dos pagamentos
     * @param int $filtro indentificador de filtro (geralmente vem da requisição)
     * @param string $init_date data limite inferior dos lançamentos (pagamentos)
     * @param string $end_date data limite superior dos lançamentos (pagamentos)
     * @param int $filtro_receb indica o tipo de recebimento (boleto:1, carnê:2 ou os dois:0)
     * @return array lista de tuplas do tipo Tupla
     */
    public function getTuplasByFiltro($loja, $filtro = 0, $init_date = '', $end_date = '', $filtro_receb = 0){
        
        $condition = VendaModel::TABLE.'.'.VendaModel::LOJA.' = '.$loja;
        if($filtro_receb) {
            $condition .= ' AND '.ParcelaModel::POR_BOLETO." = ".($filtro_receb == 1 ? 'TRUE' : 'FALSE' );
        }
        
        $joins[] = ' INNER JOIN '.ParcelaModel::TABLE.' ON '.ParcelaModel::VENDA.' = '.PagamentoModel::VENDA_PARCELA.' AND '.ParcelaModel::NUMERO.' = '.PagamentoModel::NUMERO_PARCELA; 
        $joins[] = PagamentoModel::innerJoin('VendaModel', ParcelaModel::VENDA);
        $joins[] = PagamentoModel::innerJoin('ClienteModel', VendaModel::CLIENTE);
        $joins[] = PagamentoModel::innerJoin('LocalidadeModel', ClienteModel::LOCALIDADE);
        $joins[] = PagamentoModel::innerJoin('RotaModel', LocalidadeModel::ROTA);
        $joins[] = PagamentoModel::innerJoin('RegiaoModel', RotaModel::REGIAO);
        $joins[] = PagamentoModel::leftJoin('FuncionarioModel', PagamentoModel::TABLE.'.'.PagamentoModel::COBRADOR);
        
        if(!empty($init_date) && !empty($end_date)){
            $condition .= ' AND '.PagamentoModel::DATA  ." BETWEEN '$init_date' AND '$end_date'";
        }        
        
        //Campo para nome do cobrado (que sempre virá na consulta)
        $cobrador = FuncionarioModel::TABLE.'.'.FuncionarioModel::NOME;
        
        //Estabelecendo o filtro de agrupamento
        switch ($filtro) {
            case 0: //Para agrupamento apenas em cobradores
                return $this->modelPagamento->selectTupla(
                    PagamentoModel::TABLE.'.'.PagamentoModel::COBRADOR, 
                    array($cobrador, ParcelaModel::POR_BOLETO), 
                    $joins, $condition
                );
            case 1: //Para agrupamento em regiões
                return $this->modelPagamento->selectTupla(
                    PagamentoModel::TABLE.'.'.PagamentoModel::COBRADOR, 
                    array($cobrador, RegiaoModel::TABLE.'.'.RegiaoModel::NOME,  ParcelaModel::POR_BOLETO), 
                    $joins, $condition
                );
            case 2: //Para agrupamento em rotas
                return $this->modelPagamento->selectTupla(
                    PagamentoModel::TABLE.'.'.PagamentoModel::COBRADOR, 
                    array($cobrador, RotaModel::TABLE.'.'.RotaModel::NOME,  ParcelaModel::POR_BOLETO),
                    $joins, $condition
                );
            case 3: //Para agrupamento em localidades
                return $this->modelPagamento->selectTupla(
                    PagamentoModel::TABLE.'.'.PagamentoModel::COBRADOR, 
                    array($cobrador, LocalidadeModel::TABLE.'.'.LocalidadeModel::NOME, ParcelaModel::POR_BOLETO),
                    $joins, $condition
                );
            
        }
        
    }

    public function getPagamentosByBoletoInRangeByLoja($lojaId, $dataInicio, $dataFim) {
        return $this->getPagamentosRangeByLoja($lojaId, $dataInicio, $dataFim, array(
            PagamentoModel::TABLE.".".PagamentoModel::PREST_CONTA . ' IS NULL '
        ));
    }

    public function getPagamentosByCobradorInRangeByLoja($lojaId, $dataInicio, $dataFim) {
        return $this->getPagamentosRangeByLoja($lojaId, $dataInicio, $dataFim, array(
            PagamentoModel::TABLE.".".PagamentoModel::PREST_CONTA . ' IS NOT NULL ',
            PagamentoModel::TABLE.".".PagamentoModel::COBRADOR . ' IS NOT NULL '
        ));
    }

    private function getPagamentosRangeByLoja($lojaId, $dataInicio, $dataFim, $extraConditions) {
        $conditions = array(
            PagamentoModel::VENDA_ALIAS.".".VendaModel::LOJA . " = $lojaId ",
            PagamentoModel::TABLE.".".PagamentoModel::DATA . " BETWEEN '$dataInicio' and '$dataFim' "
        );
        $conditions = array_merge($conditions, $extraConditions);
        return $this->modelPagamento->superSelect(implode(' AND ', $conditions));
    }

    /**
     * Realiza um lançamento de uma parcela. Caso o valor execeda o valor da parcela, 
     * os valores serão automaticamente destribuídos nas parcelas seguintes.
     * @param Parcela $parcela parcela à qual será dada a baixa
     * @param float $valor valor a ser dado baixa na parcela
     * @param int $cobrador indeitificador do cobrador que realizou o pagamento
     * @param strnig $data data de lançamento
     * @param int $prestacao identificador da prestação de conta dos pagamentos
     * @param bool $with_desconto indica se os lançamento que irão ser inseridos 
     * foram feito juntos com o desonto feito no rateio do lançamento
     * @return boolean <i>true</i> se todos lançamentos foram efetuados, <i>false</i> caso contrário
     */
    public function darBaixaEmParcela(Parcela $p, $valor, $cobrador, $data, $prestacao = null, $with_desconto = false){
        
        //Parcela cancelda
        if($p->cancelada){
            return false;
        }
        
        //Valor já pago da parcela
        $japago     = $this->getValorPagoOfParcela($p);
        $restante   = $p->valor - $japago;
        //Identificando o autor do pagamento
        $autor      = @(!empty($_SESSION[SESSION_ID_FUNC])? $_SESSION[SESSION_ID_FUNC] : null);
        if($valor > $restante){
            //O valor é maior e quer redistribuir
            $res = false;
            //Capta todas as próximas parcelas que não foram pagas
            $condition  = ParcelaModel::VENDA." = {$p->venda}";
            $condition .= " AND ".ParcelaModel::NUMERO." >= {$p->numero} ";
            $condition .= " AND ".ParcelaModel::STATUS." = FALSE ";
            $condition .= " AND ".ParcelaModel::CANCELADA." = FALSE ";
            $condition .= " ORDER BY ".ParcelaModel::NUMERO;
            $parcelas = $this->modelParcela->select("*", $condition);
            //Percorre as parcelas enquanto tiver valor e parcelas
            while($valor > 0){
                
                //Se o valor atual for menor que o restante da parcela corrente
                //o valor a ser pago será o dinheiro que resta
                //Caso contrário, será pago toda o restante e a parcela ficará quitada
                if($valor < $restante) $restante = $valor;
                else { 
                    //Quitando...
                    $this->modelParcela->simpleUpdate(ParcelaModel::STATUS, 'TRUE', $this->whereId($p));            
                }
                
                //Inserido o pagamento com o valor
                $pagamento = new Pagamento();
                $pagamento->cobrador        = $cobrador;
                $pagamento->data            = $data;
                $pagamento->dataBaixa       = date("Y-m-d"); //Today
                $pagamento->valor           = $restante;
                $pagamento->prestacaoConta  = $prestacao;
                $pagamento->numeroParcela   = $p->numero;
                $pagamento->vendaParcela    = $p->venda;
                $pagamento->autor           = $autor;
                $pagamento->comDesconto     = $with_desconto;
                
                //Se houver valor para pagar, insere
                if($pagamento->valor > 0){
                    $res = $this->modelPagamento->insert($pagamento);
                    if(!$res) return false;
                }
                
                //Reduz o valor total com o valor pago
                $valor -= $pagamento->valor;
                
                //Avança o array de parcelas e recalcula o restante
                $p = next($parcelas);
                if($p == FALSE) break;
                $japago = $this->getValorPagoOfParcela($p);
                $restante = $p->valor - $japago;
            }
            return true;
        } else {
            //O valor é menor, paga o valor
            $pagamento = new Pagamento();
            $pagamento->cobrador        = $cobrador;
            $pagamento->data            = $data;
            $pagamento->dataBaixa       = date("Y-m-d"); //Today
            $pagamento->valor           = $valor;
            $pagamento->numeroParcela   = $p->numero;
            $pagamento->vendaParcela    = $p->venda;
            $pagamento->prestacaoConta  = $prestacao;
            $pagamento->autor           = $autor;
            $pagamento->comDesconto     = $with_desconto;
            
            $res = $this->modelPagamento->insert($pagamento);
            if($res){
                $this->checkIfQuitadaParcela($p, $cobrador, $data, $prestacao);
                return true;
            } 
            return false;
        } 
    }
    
    /**
     * Insere um pagamento na base de dados
     * @param Pagamento $pagamento pagamento a ser inserido
     * @return bool <i>true</i> caso seja inserido, ou <i>false</i> caso contrário
     */
    public function addPagamento(Pagamento $pagamento) {
        return $this->modelPagamento->insert($pagamento);
    }
    
    /**
     * Obtém o valor já pago de uma parcela
     * @param Parcela $p parcela que se deseja saber o valor pago
     * @return float valor já pago da parcela
     */
    public function getValorPagoOfParcela(Parcela $p){
        $pagamentos = $this->modelPagamento->select(PagamentoModel::VALOR, $this->whereId($p, true));
        return array_reduce($pagamentos, 'sum', 0);
    }
    
    /**
     * Obtém a próxima parcela de uma parcela da mesma venda.
     * @param Parcela $parcela parcela atual
     * @return Parcela próxima parcela, ou FALSE em caso de inexistência da próxima
     */
    public function nextOf(Parcela $p){
        $condition  = ParcelaModel::VENDA." = {$p->venda}";
        $condition .= " AND ".ParcelaModel::NUMERO." = ({$p->numero}+1) ";
        $parcela    = $this->modelParcela->select("*", $condition);
        if(empty($parcela)) return FALSE;
        return $parcela[0];
    }
    
    /**
     * Verifica se a parcela em questão foi quitada teroricamente 
     * (quando todas os lançamentos dela são igual ao valor dela) e torna ela quitada. <br/>
     * Caso o valor pago execeda o valor da parcela, esse restante é destribuído 
     * para as próximas parcelas não quitadas
     * @param Parcela $p parcela em questão
     * @param int $cobrador identificador do cobrador
     * @return bool true em caso de sucesso, ou false em caso de falhas
     */
    public function checkIfQuitadaParcela(Parcela $p, $cobrador, $data, $prestacao){
        $soma = $this->getValorPagoOfParcela($p);
        if($soma == $p->valor){
            return $this->modelParcela->simpleUpdate(ParcelaModel::STATUS, 'TRUE', $this->whereId($p));
        } else if($soma > $p->valor){
            $dif = $soma - $p->valor;
            if(($p = $this->nextOf($p)) !== FALSE){
                return $this->darBaixaEmParcela($p, $dif, $cobrador, $data, $prestacao);
            }
        }
    }
    
    /**
     * Obtém a instrução SQL que identifica uma parcela (já que essa entidade não possui identificador)
     * @param Parcela $p parcela em questão
     * @param bool $in_pgmto indica se é na tabela pagamento ou na tabela parcela. 
     * Se for true, é na tabela de pagamentos. Se for false, é na tabela de parcelas
     * @return string instrução de identificação de uma parcela
     */
    public function whereId(Parcela $p, $in_pgmto = false){
        if(!$in_pgmto) {
            $condition  = ParcelaModel::VENDA." = {$p->venda}";
            $condition .= " AND ".ParcelaModel::NUMERO." = {$p->numero} ";
        } else {
            $condition  = PagamentoModel::VENDA_PARCELA." = {$p->venda}";
            $condition .= " AND ".PagamentoModel::NUMERO_PARCELA." = {$p->numero} ";
        }
        return $condition;   
    }
    
    /**
     * Obtém o valor restante (saldo a quitar) de uma venda.
     * @param int $venda_id identificador da venda
     * @param bool $only_more_month se for true delimita as parcelas que não ultrapassam mais de um mês.
     * Ou seja, somente as parcelas que não se vencem até o próximo mês serão consideradas no cálculo
     * @return float valor restatente a ser pago da venda
     */
    public function getRestanteOfVenda($venda_id, $only_more_month = false){
        //Obtém as parcelas
        $parcelas   = $this->getParcleasByVenda($venda_id, self::PARCELAS_NAO_PAGAS);
        $total      = 0;
        $pago       = 0;
        if(!$only_more_month) {
            //Casualmente, calculando com todas as parcelas
            foreach ($parcelas as $parcela) {
                if($parcela->valor < 0) continue;
                $total  += $parcela->valor;
                $pago   += $this->getValorPagoOfParcela($parcela);
            }
        } else {
            //Restringindo pela data de vencimento das parcelas
            $next_month  = strtotime("next month");
            foreach ($parcelas as $parcela) {
                $validade = strtotime($parcela->validade);
                //Verificando a restrição da validade
                if($validade < $next_month || $parcela->valor < 0) continue;
                $total  += $parcela->valor;
                $pago   += $this->getValorPagoOfParcela($parcela);
            }
        }
        //Contabilizando a diferença
        $restante = $total - $pago;
        return $restante;
    }

    /**
     * Deleta um pagamento da base de dados.
     * @param Pagamento $p pagamento que irá ser deletado.
     * @param bool $turn_non_quitada_parcela se for true, ao remover o pagamento, 
     * a parcela a qual ele pertence, se torna não quitada novamente 
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function removePagamento(Pagamento $p, $turn_non_quitada_parcela = false){
        $res = $this->modelPagamento->delete($p->id);
        if(!$turn_non_quitada_parcela) return $res;
        if($res){
            $parcela = $this->getParcela($p->numeroParcela, $p->vendaParcela);
            if($parcela->status) $this->turnNonQuitada ($parcela);
        }
        return $res;
    }
    
    /**
     * Aualiza um pagamento. O método encapsula uma data de edição e um autor da edição.
     * @param Pagmento $p pagemento que irá ser atualizado.
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function updatePagamento(Pagamento $p){
        $p->autor       = @(!empty($_SESSION[SESSION_ID_FUNC])? $_SESSION[SESSION_ID_FUNC] : null);
        $p->dataEdicao  = date("Y-m-d");
        return $this->modelPagamento->update($p);
    }
    
    /**
     * Transforma uma parcela em não quitada. Mudando o status para '0'.
     * @param Parcela $p parcela em questão
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function turnNonQuitada(Parcela $p){
        return $this->modelParcela->simpleUpdate(ParcelaModel::STATUS, '0', $this->whereId($p));
    }
    
    /**
     * Transforma uma parcela em quitada. Mudando o status para '1'.
     * @param Parcela $p parcela em questão
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function turnQuitada(Parcela $p){
        return $this->modelParcela->simpleUpdate(ParcelaModel::STATUS, '1', $this->whereId($p));
    }
    
    /**
     * Delta uma parcela.
     * @param Parcela $p parcela em questão.
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function removerParcela(Parcela $p){
        return $this->modelParcela->delete($p);
    }
    
    /**
     * Deleta todos os pagamentos que foram realizados junto ao desconto.
     * @param int $venda_id identificador da venda
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function removePagamentoComODesconto($venda_id){
        $condition  = PagamentoModel::VENDA_PARCELA." = $venda_id ";
        $condition .= " AND ".PagamentoModel::COM_DESCONTO." = 1 ";
        $pagamentos = $this->modelPagamento->select(
                        array(PagamentoModel::ID, PagamentoModel::NUMERO_PARCELA, PagamentoModel::VENDA_PARCELA), 
                        $condition
                      );
        foreach($pagamentos as $pagamento){
            if($this->modelPagamento->delete($pagamento->id)){
                $condition  = ParcelaModel::VENDA." = {$pagamento->vendaParcela}";
                $condition .= " AND ".ParcelaModel::NUMERO." = {$pagamento->numeroParcela} ";
                $this->modelParcela->simpleUpdate(ParcelaModel::STATUS, '0', $condition);
            } else return false;
        }
        return true;
    }
    
    /**
     * Obtém a parcela de desconto de uma venda.
     * @param int $venda_id identificador da venda
     * @return Parcela a parcela de desconto da venda ou uma parcela vazia 
     * em caso de inexistência de desconto
     */
    public function getDescontoOfVenda($venda_id){
        $condition  = ParcelaModel::VENDA." = $venda_id ";
        $condition .= " AND ".ParcelaModel::VALOR." < 0 ";
        $parcela    = $this->modelParcela->select("*", $condition);
        if(empty($parcela)) return false;
        return $parcela[0];
    }
    
    /**
     * Obtém os identificadores das localidades com parcelas atrasadas até a data limite.
     * @param int $id_rota identificador da rota.
     * @param string $limit_up_date data limite superior da data da parcela
     * @param string $limit_down_date data limite inferior da data da parcela
     * @return array lista de identificadores de localidades com vendas com parcelas atrasadas
     */
    public function getLocalidadesWithParcelasAbertas($id_rota, $limit_up_date, $limit_down_date = ''){
        return $this->modelParcela->selectForRota($id_rota, $limit_up_date, $limit_down_date);
    }
    
    /**
     * Seleciona uma lista de identificadores de vendas de uma localidade que possuem parcelas
     * de vendas atrasadas no periodo informado.
     * @param int $localidade_id identificador da localidade.
     * @param string $limit_up_date data limite superior da data da parcela
     * @param string $limit_down_date data limite inferior da data da parcela
     * @return array lista de identificadores de vendas com parcelas atrasadas
     */
    public function getVendaAbertasInLocalidade($localidade_id, $limit_up_date, $limit_down_date = ''){
        return $this->modelParcela->selectForLocalidade($localidade_id, $limit_up_date, $limit_down_date);
    }
    
    /**
     * Seleciona uma lista de parcelas de parcelas atrasadas de uma regiao
     * @param int $id_rota identificador da rota.
     * @param string $limit_date data limite da data da venda
     * @return array lista de parcelas atrasadas
     */
    public function getParcelasAtrasadasInRegiao($regiao_id, $data_limite){
        return $this->modelParcela->selectForRegiao($regiao_id, $data_limite);
    }
    
    /**
     * Verifica se uma venda é editável nos padrões do modelo de parcela. 
     * <br/>
     * Uma venda é editável para o modelo de parcelas quando:
     * <ul>
     * <li> Não possuí nenhum lançamento</li>
     * </ul>
     * @param int $venda_id identificador da venda
     * @param bool $for_data se for true, ele não inclui a entrada na verificação.
     * @return bool true caso seja editável, false caso não seja.
     */
    public function isEditableVenda($venda_id, $for_data = false){
        $condition  = PagamentoModel::VENDA_PARCELA." = $venda_id ";
        if($for_data) $condition .= ' AND '.PagamentoModel::NUMERO_PARCELA.' > 0';
        $pagamentos = $this->modelPagamento->select(PagamentoModel::ID, $condition);
        return empty($pagamentos);
    }
    
    /**
     * Verifica se uma venda  é quitada. Uma venda é quiatada quando não há nenhuma parcela em aberto.
     * @param int $id_venda identificador da venda
     * @return bool true caso não haja parcelas em aberto, ou false caso tenha alguma em aberto.
     */
    public function vendaIsQuitada($id_venda){
        $condition  = ParcelaModel::VENDA." = $id_venda AND ".ParcelaModel::STATUS." = 0";
        $res        = $this->modelParcela->select(ParcelaModel::NUMERO, $condition) ;
        return empty($res);
    }
    
    /**
     * Remarca o dia de cobrança de parcelas de uma mesma venda. Atribui <i>data</i> como valor
     * da data de remarcação das parcelas no modelo.
     * A data de remarcação não pode ser maior do que a data limite estabelecida.
     * @param array $parcelas numeros parcelas as quais vão ser atualizadas
     * @param int $venda identificador da venda das parcelas
     * @param string $data data da próxima cobrança
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function remarcarParcela($parcelas, $venda, $data){
        if (empty($parcelas)) return true;
        if (!empty($data) && strtotime($data) > (time() + MAIN_LIMIT_DATE)) return false;
        $data = empty($data) ? ' NULL ' : "'$data'";
        $condition  = ParcelaModel::VENDA.' = '.$venda;
        $condition .= ' AND ' . ParcelaModel::NUMERO . ' IN (' . implode(',', $parcelas) . ')';
        return $this->modelParcela->simpleUpdate(ParcelaModel::REMARCACAO, $data, $condition);
    }
    
    /**
     * Obtém os pagamentos associados a uma prestação de conta.
     * @param int $prestacao indetiificador de uma prestação de conta
     * @return array lista de pagamentos de uma prestação de conta do tipo Pagamento
     */
    public function getPagamentosByPrestacao($prestacao){
        $condition = PagamentoModel::PREST_CONTA.' = '.$prestacao;
        return $this->modelPagamento->select('*', $condition);
    }
    
    /**
     * Obtém as somas dos valores dos pagamentos associados a uma prestação de conta.
     * @param int $prestacao indetiificador de uma prestação de conta
     * @return float soma dos valores dos pagamentos associados a uma prestação de conta
     */
    public function getValorOfPrestacao($prestacao){
        $condition  = PagamentoModel::PREST_CONTA.' = '.$prestacao;
        $res        = $this->modelPagamento->select(PagamentoModel::VALOR, $condition);
        return array_reduce($res, 'sum', 0);
    }
 
    /**
     * Transforma todas as parcelas de uma venda em modo de cobrança cobrador,
     * atualizando o <i>por_boleto</i> para false.
     * @param int $venda_id identificador da venda.
     * @return boolean <i>true</i> se todas as parcelas foram alteradas, ou <i>false</i> caso contrário.
     */
    public function turnInCarnes($venda_id) {
        $condition = ParcelaModel::VENDA.' = '.$venda_id;
        return $this->modelParcela->simpleUpdate(ParcelaModel::POR_BOLETO, 'FALSE', $condition);
    }
    
    
    /**
     * Executa a função de análise de retornos dos boletos bancários.
     */
    public function avaliacaoRetornos() {
        
        include_once LIBS.'retorno/parser.php';
        
        $arquivos = $_FILES['retornos'];

        for ($i = 0, $l = count($arquivos['name']); $i  < $l; $i++) {
            $name = $arquivos['name'][$i];
            if(strtolower(substr($name, -4)) != '.ret'){ 
                echo "<p class='title-form'>O arquivo $name selecionado não é um arquivo de retorno válido.</p>";
                continue; 
            }
            $tmp_file = $arquivos['tmp_name'][$i];
            $boletos = parser_ret($tmp_file);
            if($boletos == null) {
                echo "<p class='title-form'>Erro ao analisar arquivo $name</p>";
                continue;
            }            
            echo '<p class=\'title-form\'>'.$name.'</a>';
            $this->handleBoletosFromParser($boletos);
        }
    }
    
    /**
     * Manipula os boletos do parser para associar a lançamentos de parcelas.
     * @param array $boletos vetor de saída do parser de um arquivo de retorno.
     * @return string uma saída de usuário para análise de execução
     */
    public function handleBoletosFromParser($boletos){
        
        $config = Config::getInstance();
        
        include_once CONTROLLERS."venda.php";
        $venda_controller = new VendaController();
        
        echo "<p class='info-input'>PC: Programa da Caixa</span><p/>
            <table id='result-boletos'>
                <thead>
                    <tr> 
                        <th>Nosso Num.</th>
                        <th>Loja</th>
                        <th>Venda</th>
                        <th>Parcela</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Pago em</th>
                        <th>Status</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach($boletos as $boleto) {
       
            $nosso_numero   = $boleto['t_id_titulo_banco'];
            
            $has_processado = nn_is_evaluated($nosso_numero);
            
            $id_loja        = (int) substr($nosso_numero, 0, 3);
            $n_parcela      = (int) substr($nosso_numero, 3, 3);
            $n_venda        = (int) substr($nosso_numero, 6, 9);
            
            $parcela        = $this->getParcela($n_parcela, $n_venda);
            
            $parcela_valida = !empty($parcela->venda) && !$parcela->cancelada;
            
            if($parcela_valida && !$parcela->porBoleto ){
                echo "<tr><td>$nosso_numero</td>";
                echo "<td colspan='6'></td>";
                echo "<td class='status-not-processed'>Não Processado</td></tr>";
                register_nn($nosso_numero);
                continue;
            } else if(!$parcela_valida){            
                echo "<tr><td>$nosso_numero (PC)</td>";
            } else {
                echo "<tr><td>$nosso_numero</td>";
            }
            
            $dt_ocorrencia  = $boleto['u_dt_efetivacao'];
            
            $this->modelParcela->begin();
            
            if($parcela_valida && !$has_processado && !$this->darBaixaEmParcela($parcela, $parcela->valor, null, $dt_ocorrencia)) {
                
                $this->modelParcela->rollBack();
                $valor  = $config->maskDinheiro($parcela->valor);
                $data   = $config->maskData($parcela->validade);
                $pago   = $config->maskData($dt_ocorrencia);

                echo "  <td>$id_loja</td>
                        <td>{$parcela->venda}</td>
                        <td>{$parcela->numero}</td>
                        <td>$valor</td>
                        <td>$data</td>
                        <td>$pago</td>
                        <td class='status-fail'>Falha ao quitar</td>
                </tr>";
                continue;
            }
            
            //Veririficando e atualizando, caso necessário, o status da venda
            $venda_controller->checkAndTurnStatus($parcela->venda);
            
            $this->modelParcela->commit();
            
            if($parcela_valida) {

                $valor  = $config->maskDinheiro($parcela->valor);
                $data   = $config->maskData($parcela->validade);
                $pago   = $config->maskData($dt_ocorrencia);

                echo "  <td>$id_loja</td>
                        <td>{$parcela->venda}</td>
                        <td>{$parcela->numero}</td>
                        <td>$valor</td>
                        <td>$data</td>
                        <td>$pago</td>";
                
                if(!$has_processado){
                    echo "<td class='status-sucess'>Processado</td>";
                } else {
                    echo "<td class='status-evaluated'>Já Processado</td>";
                }
                echo "</tr>";
                
            } else {
                
                $valor  = $config->maskDinheiro((float)$boleto['u_v_liquido']);
                $data   = "__/__/____";
                $pago   = $config->maskData($dt_ocorrencia);

                echo " 
                    <td colspan='3'></td>
                    <td>$valor</td>
                    <td>$data</td>
                    <td>$pago</td>";
                if(!$has_processado){
                    echo "<td class='status-manual'>Boleto Desconhecido</td>";
                } else {
                    echo "<td class='status-evaluated'>Já Processado</td>";
                }
                echo "</tr>";
                
            }
            
            register_nn($nosso_numero);
            
        }
        
        echo "</tbody></table>";
        
        echo "<style>
        #result-boletos{width:100%;font-size:10pt;border-spacing:0;border-collapse:collapse;}
        #result-boletos td, #result-boletos th{padding:10px; text-align:center;}
        #result-boletos th:first-child{border-radius:3px 0 0 3px;}
        #result-boletos th:last-child{border-radius:0 3px 3px 0;}
        #result-boletos th{background:#EEE;font-weight:bolder;}
        #result-boletos td{border-bottom:lightgray solid 1px;}
        #result-boletos tr td:first-child{background:rgb(250,250,250);}
        #result-boletos td.status-manual{background:lightyellow;color:gray;font-weight:bolder}
        #result-boletos td.status-sucess{background:green;color:white;font-weight:bolder}
        #result-boletos td.status-faili{background:brown;color:white;font-weight:bolder}
        #result-boletos td.status-not-processed{background:salmon;color:black;font-weight:bolder}
        #result-boletos td.status-evaluated{background:lightblue;color:white;font-weight:bolder}
        </style>";
    }
    
    /**
     * Cancela todas as parcelas aberta de uma venda.
     * @param int $id_venda identificador da venda
     * @return boolean <i>true</i> se canceladas com sucesso, ou <i>false</i> caso contrário
     */
    public function cancelarParcelasAbertas($id_venda){
        $condition   = ParcelaModel::VENDA." = ".$id_venda;
        $condition  .= " AND ". ParcelaModel::STATUS." = FALSE ";
        return $this->modelParcela->simpleUpdate(ParcelaModel::CANCELADA, 'TRUE', $condition);
    }
    
    /**
     * Executa a função de cancelamento de parcelas de uma venda vinda da requisição.
     */
    public function cancelarParcelas($return = false){
        $config = Config::getInstance();
        $venda  = $config->filter('v'); 
        $this->modelParcela->begin();
        $gerent_confirm = $config->checkGerentConfirm();
        if(empty($venda)){
            $this->modelParcela->rollBack();
            $config->failInFunction('Venda Inválida');
        } else if(!$gerent_confirm){
            $this->modelParcela->rollBack();
            $config->failInFunction('Sem autorização');
        } else if($this->cancelarParcelasAbertas($venda)){
            include_once CONTROLLERS.'venda.php';
            include_once MODELS.'cancelamento.php';
            include_once ENTITIES.'cancelamento.php';
            $vendaController    = new VendaController();
            $modelCancelamento  = new CancelamentoModel(); 
            $cancelamento       = new Cancelamento($venda, $_SESSION[SESSION_ID_FUNC], $gerent_confirm , date('Y-m-d'));
            if( $vendaController->turnVendaDevolvida($venda,  /*$devolver_produtos:*/ false) &&
                $modelCancelamento->insert($cancelamento)   ) {
                $this->modelParcela->commit();
                if($return) {
                    return true;
                }
                $config->successInFunction();
            } else {
                $this->modelParcela->rollBack();
                if($return) {
                    return false;
                }
                $config->failInFunction('Falha ao cancelar venda');
            }
        } else {
            $this->modelParcela->rollBack();
            if($return) {
                return false;
            }
            $config->failInFunction();
        }
        if(!$return) {
            $config->redirect('index.php?op=cad-lanc');
        }
    }

    /**
     * @param object $filters
     * @param string $dateBegin
     * @param string $dateEnd
     */
    public function getValoresAReceber($filters, $dateBegin, $dateEnd) {

        $conditions = array();

        $inOrEquals = function($attr, $value) {
            if (is_array($value)) {
                list($value, $comparator) = array( '(' . implode(',', $value) . ')', 'IN');
            } else {
                $comparator = '=';  
            }
            return " $attr $comparator $value ";
        };

        if (isset($filters->loja) && !empty($filters->loja)) {
            $conditions[] = $inOrEquals(VendaModel::TABLE.'.'.VendaModel::LOJA, $filters->loja);
        }
        if (isset($filters->regiao) && !empty($filters->regiao)) {
            $conditions[] = $inOrEquals(RegiaoModel::TABLE.'.'.RegiaoModel::ID, $filters->regiao);
        }
        if (isset($filters->rota) && !empty($filters->rota)) {
            $conditions[] = $inOrEquals(RotaModel::TABLE.'.'.RotaModel::ID, $filters->rota);
        }
        if (isset($filters->localidade) && !empty($filters->localidade)) {
            $conditions[] = $inOrEquals(LocalidadeModel::TABLE.'.'.LocalidadeModel::ID, $filters->localidade);
        }
        

        $condition = implode(' AND ', $conditions);
        
        return $this->modelParcela->valoresAReceber($condition, $dateBegin, $dateEnd);
    }
}
/**
 * Esssa função reduz arrays de pagamentos à soma dos valores dos pagamentos.
 * @param float $v acumulador da função
 * @param Pagamento $p pagamento corrente do array
 * @return float acumulador incrementado com o valor do pagamento corrente.
 */
function sum($v, Pagamento $p){
    $v += $p->valor;
    return $v;
}

/**
 * Verifica se o "nosso número" de um boleto foi avaliaado.
 * @param string $nn nosso número 
 * @return boolean <i>true</i> caso já tenha sido analisado, <i>false</i> caso contrário
 */
function nn_is_evaluated($nn) {
    $f = fopen(UTIL.'nn_evaluated', 'r');
    if(!$f) {
        return true;
    }
    while(!feof($f)){
        $line = fgets($f);
        if(strcmp(trim($line), $nn)) continue;
        else {
            fclose($f);
            return true;
        }
    }
    fclose($f);
    return false;
}

/**
 * Registra um "nosso número" caso não tenha sido analisado.
 * @param string $nn nosso número
 */
function register_nn($nn){
    $f = fopen(UTIL.'nn_evaluated', 'a');
    if(!$f) return false;
    if(!nn_is_evaluated($nn)) {
        fwrite($f, $nn . "\n");
    }
    fclose($f);
    return true;
}
?>
