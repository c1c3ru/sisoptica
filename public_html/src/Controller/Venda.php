<?php
namespace Sisoptica\Controller;
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."venda.php";
include_once ENTITIES."produto-venda.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."venda.php";
include_once MODELS."produto-venda.php";
include_once MODELS."cliente.php";

//Impotando as classes de controladores usadas nesse controlador
include_once CONTROLLERS."consulta.php";
include_once CONTROLLERS."parcela.php";
include_once CONTROLLERS."equipe.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de vendas e
 * do modelo de produtos das vendas.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class VendaController {

    /**
     * @var VendaModel instancia do modelo de vendas usado nesse controlador
     * @access private
     */
    private $modelVenda;

    /**
     * @var ProdutoVendaModel instancia do modelo de produtos das vendas usado nesse controlador
     * @access private
     */
    private $modelProdutoVenda;

    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de vendas e
     * do modelo de produtos das vendas.
     * @return VendaController instancia de um controlador de vendas
     */
    public function __construct() {
        $this->modelVenda           = new VendaModel();
        $this->modelProdutoVenda    = new ProdutoVendaModel();
    }

    /**
     * Este método adiciona ou atualiza uma venda no modelo.
     * @param Venda $venda venda a ser inserida ou atualizada. Se for <b>null</b> os dados da
     * requisição serão captados e atribuídos à <i>venda</i>
     */
    public function addVenda(Venda $venda = null){

        if($venda == null){
            $venda = new Venda();
            //Atribuindo dados da requisição
            $this->linkVenda($venda);
        }

        $config = Config::getInstance();

        //Checando se os dados obigratórios estão sendo enviados dos dados
        if(!empty($venda->dataVenda) && !empty($venda->previsaoEntrega) &&
           (strtotime($venda->previsaoEntrega) < strtotime($venda->dataVenda))) {
            $config->failInFunction("Data de venda deve ser menor do que a previsão de entrega");
            $config->redirect("index.php?op=cad-vend");
        }

        //Verifica se a data das parcelas ultrapassa os 60 dias
        $dataPrimeira = $config->filter("data-entrada"); // se chama entrada, mas não é a entrada de fato
        if (!empty($dataPrimeira)) {
            $dataDemais = $config->filter("data-parcela");
            $dataDiff = strtotime($dataDemais) - strtotime($dataPrimeira);
            if ($dataDiff > MAIN_LIMIT_DATE) {
                $config->failInFunction("Data das demais parcelas não pode ser maior do que 60 dias");
                $config->redirect("index.php?op=cad-vend");
            }
        }

        include_once CONTROLLERS.'produto.php';
        $produto_controller = new ProdutoController();

        $hasId = $config->filter("for-update-id");
        $res = false;
        // Verificando a existência de um id na requisção.
        // Caso exista a função de atualização do modelo será chamada, caso contrário,
        // a de inserção será chamada.
        if(empty($hasId)){

            //INSERÇÃO

            //Iniciando a transação de inserção
            $this->modelVenda->begin();

            $venda->dataEntrega = null;
            $res = $this->modelVenda->insert($venda);

            if($res){

                //Inserido os produtos associados a venda
                $produtos = $venda->produtos;
                $ids_produtos = array();
                foreach($produtos as $produto) {
                    $produto->venda = $venda->id;
                    $ids_produtos[] = $produto->produto;
                }
                $map_cat = $produto_controller->getMapaCategoria($ids_produtos);

                if(!$this->modelProdutoVenda->inserts($produtos)){
                    $this->modelVenda->rollBack(); //Rollback changes
                    $config->failInFunction("Falha ao associar produtos");
                    $config->redirect("index.php?op=cad-vend");
                }

                //Sincronizando o estoque
                include_once CONTROLLERS.'centralEstoque.php';
                $centralEstoque = new CentralEstoqueController();
                foreach($produtos as $p){
                    if($map_cat[$p->produto] == ProdutoController::CAT_LENTE){ continue; }
                    if(!$centralEstoque->mergeProdutoInEstoque($p->produto, $venda->loja, 1, '-')){
                        $this->modelVenda->rollBack(); //Rollback changes
                        $config->failInFunction("Falha no estoque desse produto na loja.");
                        $config->redirect("index.php?op=cad-vend");
                    }
                }

                //Ajuastando as parcelas da venda
                $venda->valor = $this->getValorOfVenda($venda->id);
                $parcela_controller = new ParcelaController();
                if(!$parcela_controller->addParcelas($venda)){
                    $this->modelVenda->rollBack(); //Rollback changes
                    $config->failInFunction("Falha ao criar parcelas. Verifique o valor e o status da prestação de conta.");
                    $config->redirect("index.php?op=cad-vend");
                }

                //Criando a consulta
                $controller_consulta = new ConsultaController();
                if(!$controller_consulta->addConsulta(null, $venda->id)){
                    $this->modelVenda->rollBack(); //Rollback changes
                    $config->failInFunction("Falha ao registrar consluta");
                    $config->redirect("index.php?op=cad-vend");
                }

                //Comitando as mudanças
                $this->modelVenda->commit();
            } else {
                //Rollback no cao de falha na inserção da venda
                $this->modelVenda->rollBack();
            }

        } else {

            //ATUALIZAÇÃO

            //Verificando a confrimação do gerente
            if(!$config->checkGerentConfirm()){
                $config->failInFunction("Essa ação necessita da confirmação do gerente");
                $config->redirect("index.php?op=cad-vend");
            }
            //Verificando se a venda pode ser editada
            $parcela_controller = new ParcelaController();
            if(!$parcela_controller->isEditableVenda($venda->id)){
                $config->failInFunction("Essa venda não pode ser editada.");
                $config->redirect("index.php?op=cad-vend");
            }

            //Iniciando a transação de atualização
            $this->modelVenda->begin();

            $venda->id = $hasId;
            $res = $this->modelVenda->update($venda);
            if($res) {
                //Atualizando os produtos da venda
                $produtos = $venda->produtos;
                $ids_produtos = array();
                foreach($produtos as $produto) {
                    $produto->venda = $venda->id;
                    $ids_produtos[] = $produto->produto;
                }

                $map_cat = $produto_controller->getMapaCategoria($ids_produtos);

                if(!$this->modelProdutoVenda->deleteOfVenda($venda->id) ||
                   !$this->modelProdutoVenda->inserts($produtos)){
                    $this->modelVenda->rollBack(); //Rollback das mudanças
                    $config->failInFunction("Falha ao associar produtos");
                    $config->redirect("index.php?op=cad-vend");
                }

                //Sincronizando o estoque
                include_once CONTROLLERS.'centralEstoque.php';
                $centralEstoque = new CentralEstoqueController();
                $ids_produtos_velha_venda = array();
                $olds_products = $this->getProdutosVendaOfVenda($venda->id);
                foreach($olds_products as $op){
                    if(!isset($ids_produtos_velha_venda[$op->produto]))
                        $ids_produtos_velha_venda[$op->produto] = 0;
                    $ids_produtos_velha_venda[$op->produto]++;
                }
                foreach($produtos as $p){
                    if(array_key_exists($p->produto, $ids_produtos_velha_venda)){
                        $ids_produtos_velha_venda[$p->produto]--;
                        if($ids_produtos_velha_venda[$p->produto] >= 0) continue;
                    }
                    if($map_cat[$p->produto] == ProdutoController::CAT_LENTE){ continue; }
                    if(!$centralEstoque->mergeProdutoInEstoque($p->produto, $venda->loja, 1, '-')){
                        $this->modelVenda->rollBack(); //Rollback changes
                        $config->failInFunction("Falha no estoque desse produto na loja.");
                        $config->redirect("index.php?op=cad-vend");
                    }
                }

                $venda->valor = $this->getValorOfVenda($venda->id);
                //Removendo todas as parcelas
                $parcelas   = $parcela_controller->getParcleasByVenda($venda->id);
                foreach ($parcelas as $p) {
                    $res = $parcela_controller->removerParcela($p);
                }

                //Inserido as novas parcelas
                if(!$parcela_controller->addParcelas($venda)){
                    $this->modelVenda->rollBack(); //Rollback das mudanças
                    $config->failInFunction("Falha ao criar parcelas");
                    $config->redirect("index.php?op=cad-vend");
                }

                //Reativando venda
                if(!is_null($config->filter("reativar"))){
                    $this->turnVendaAtiva($venda->id);
                }

                //Atualizando as consulta
                $controller_consulta = new ConsultaController();
                if(!$controller_consulta->addConsulta(null, $venda->id)){
                    $this->modelVenda->rollBack(); //Rollback das mudanças
                    $config->failInFunction("Falha ao registrar consluta");
                    $config->redirect("index.php?op=cad-vend");
                }

                //Comitando as mudanças
                $this->modelVenda->commit();

            } else {
                //Rollback no cao de falha na atualização da venda
                $this->modelVenda->rollBack();
            }
        }
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelVenda->handleError());
        }
        $config->redirect("index.php?op=cad-vend");
    }

    /**
     * Esse método linka os dados da requisção que diz respeito à venda
     * com um objeto passado como referência
     * @param Venda $venda uma referência de uma venda que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkVenda(Venda &$venda){
        $config = Config::getInstance();
        $venda->dataVenda       = $config->filter("data-venda");
        $venda->previsaoEntrega = $config->filter("previsao-entrega");
        if(empty($venda->previsaoEntrega)) $venda->previsaoEntrega = null;
        $venda->dataEntrega     = $config->filter("data-entrega");
        if(empty($venda->dataEntrega)) $venda->dataEntrega = null;
        $venda->cliente         = $config->filter("cliente");
        $venda->loja            = $config->filter("loja");
        $venda->vendedor        = $config->filter("vendedor");
        $venda->agenteVendas    = $config->filter("agente");
        $venda->os              = $config->filter("ordem-servico");
        $venda->equipe          = $config->filter("equipe");
        $this->linkEquipe($venda);
        //Obtendo produtos
        $strProdutos    = $config->filter("produtos");
        //Produtos são separados por vírgula
        $produtosValor  = explode(",", $strProdutos);
        $produtos       = array();
        foreach ($produtosValor as $produtoValorStr){
            //Cada produto é um par: id do produto : valor atribuido
            $produtoValor       = explode(":", $produtoValorStr);
            $produto            = new ProdutoVenda();
            $produto->produto   = $produtoValor[0];
            $produto->valor     = $produtoValor[1];
            $produtos[]         = $produto;
        }
        $this->validaProdutos($produtos);
        $venda->produtos = $produtos;
    }

    /**
     * Essa função valida os produtos de uma adição ou edição de venda.
     * A validação ocorre através da verificação de existência e da verificação de preço mínimo de venda.
     * Se o produto não existir ou se o preço a ser vendido for menor que o "Preço Mínimo de Venda"
     * a requisição será desfeita e o reirecionamento será efetuado.
     * @param array $arrProdutosVenda array de produtosVenda que foram captados da requisição
     */
    private function validaProdutos($arrProdutosVenda){
        include_once CONTROLLERS.'produto.php';
        $produtoController = new ProdutoController();
        foreach($arrProdutosVenda as $pvenda){
            $produto = $produtoController->getProduto($pvenda->produto);
            if(empty($produto->id)) {
                $this->checkTransictionAndRedirectFail('Um ou mais produtos não existem na base de dados');
            }
            if($pvenda->valor < $produto->precoVendaMin){
                $this->checkTransictionAndRedirectFail('Um ou mais produtos extrapolaram o preço mínimo');
            }
        }
    }

    private function checkTransictionAndRedirectFail($detail = null) {
        if($this->modelVenda->inTransaction()){
            $this->modelVenda->rollBack();
        }
        Config::getInstance()->failInFunction($detail);
        Config::getInstance()->redirect('index.php?op=cad-vend');
    }

    /**
     * Esse método cancela uma venda de acordo do id da venda informado na requisição.
     */
    public function cancelarVenda(){

        $config = Config::getInstance();

        //Check if gerente confirm action
        $id_confirm = $config->checkGerentConfirm();
        if(!$id_confirm){
            $config->failInFunction("Essa ação necessita da confirmação do gerente");
            $config->redirect("index.php?op=cad-vend");
        }

        $id_vend = $config->filter("vend");

        //Verificando se a venda pode ser cancelada
        $parcela_controller = new ParcelaController();
        if(!$parcela_controller->isEditableVenda($id_vend)){
            $config->failInFunction("Essa venda não pode ser cancelada.");
            $config->redirect("index.php?op=cad-vend");
        }
        if($this->turnVendaCancelada($id_vend, /*$devolver_produtos:*/ false)){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelVenda->handleError());
        }
        $config->redirect("?op=cad-vend");
    }
    /**
     * Obtém uma lista de todas as vendas.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indentificador da loja, esse parametro, se for um inteiro válido,
     * filtra as vendas por loja.
     * @return array lista de vendas do tipo Venda
     */
    public function getAllVendas($foreign_values = false, $byLoja = false){
        $condition = null;
        if($byLoja && empty($byLoja)) $condition = VendaModel::TABLE.".".VendaModel::LOJA." = $byLoja";
        if($foreign_values)
            return $this->modelVenda->superSelect ($condition);
        return $this->modelVenda->select("*", $condition);
    }

    /**
     * Esse método realiza uma busca por vendas de acordo com os <i>fields_to_search</i>
     * @param array $fields_to_search dicionário que contém os parametros da busca (ver fieldsToSearch()).
     * @param string $status status da venda
     * (todos = '0', ativa = '1', cancelada = '2', quitada = '3', renegociada = '4')
     * @param bool $lanc_fields como o mesmo método é utilizado na busca do módulo de lançamentos,
     * essa flag indica se somente campos da para grid de lançamentos deve ser retornado na lista.
     * @return array lista de vendas como resultado da busca.
     */
    public function searchVendas($fields_to_search, $status, $lanc_fields = true)
    {
        $params = [];
        $conditions = array();
        //Caso seja um status diferente da venda renegociada ou todas as vendas
        if($status != VendaModel::STATUS_TODOS && $status != VendaModel::STATUS_RENEGOCIADA)
            if(substr($status, 0, 1)=="-"){
                $status = substr($status, 1);
                $conditions[] = VendaModel::TABLE.".".VendaModel::STATUS." <> ? ";
                $params[] = $status;
            } else {
                $conditions[] = VendaModel::TABLE.".".VendaModel::STATUS." = ? ";
                $params[] = $status;
            }
        else {
            //Caso seja uma venda renegociada
            switch ($status) {
                case VendaModel::STATUS_RENEGOCIADA:
                    $conditions[] = VendaModel::VENDA_ANTIGA." IS NOT NULL ";
                    break;
            }
        }
        //Caso não seja um administrador logado, a loja do funcionário deve ser fixa
        if($_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR){
            $fields_to_search[VendaModel::TABLE.".".VendaModel::LOJA][0] = $_SESSION[SESSION_LOJA_FUNC];
        }
        //Adaptando os campos de busca para o SQL
        foreach ($fields_to_search as $f => $v) {
            if(empty($v[0])) continue;
            switch ($v[1]){
                case SQL_CMP_CLAUSE_EQUAL:
                    $conditions[] = " $f = ? ";
                    $params[] = $v[0];
                    break;
                case SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES:
                    $conditions[] = " $f = ? ";
                    $params[] = $v[0];
                    break;
                case SQL_CMP_CLAUSE_LIKE:
                    $conditions[] = " $f LIKE ? ";
                    $params[] = "%{$v[0]}%";
                    break;
                case SQL_IS_NULL_CLAUSE:
                    $conditions[] = " $f IS NULL ";
                    break;
                case SQL_IS_NOT_NULL_CLAUSE:
                    $conditions[] = " $f IS NOT NULL ";
                    break;
                case SQL_CMP_BETWEEN_CLAUSE:
                    $conditions[] = " $f BETWEEN ? AND ? ";
                    if (is_array($v[0]) && count($v[0]) == 2) {
                        $params[] = $v[0][0];
                        $params[] = $v[0][1];
                    } else {
                        $parts = explode(' AND ', $v[0]);
                        $params[] = $parts[0] ?? $v[0];
                        $params[] = $parts[1] ?? $v[0];
                    }
                    break;
                case SQL_CMP_IN_CLAUSE:
                    if (is_array($v[0])) {
                        $inPlaceholders = implode(',', array_fill(0, count($v[0]), '?'));
                        $conditions[] = " $f IN ($inPlaceholders) ";
                        foreach($v[0] as $val) $params[] = $val;
                    } else {
                        $conditions[] = " $f IN (?) ";
                        $params[] = $v[0];
                    }
                    break;
                case SQL_CMP_NOT_IN_CLAUSE:
                    if (is_array($v[0])) {
                        $inPlaceholders = implode(',', array_fill(0, count($v[0]), '?'));
                        $conditions[] = " $f NOT IN ($inPlaceholders) ";
                        foreach($v[0] as $val) $params[] = $val;
                    } else {
                        $conditions[] = " $f NOT IN (?) ";
                        $params[] = $v[0];
                    }
                    break;
            }
        }
        //Juntando as condições de busca
        $condition = implode(" AND ", $conditions);
        //Decidindo os campos de busca
        $fields = array(VendaModel::TABLE.".".VendaModel::ID,
                        VendaModel::TABLE.".".VendaModel::OS,
                        VendaModel::TABLE.".".VendaModel::CLIENTE,
                        VendaModel::TABLE.".".VendaModel::LOJA,
                        VendaModel::TABLE.'.'.VendaModel::STATUS);
        if(!$lanc_fields){
            $fields[] = VendaModel::TABLE.".".VendaModel::STATUS;
            $fields[] = VendaModel::TABLE.".".VendaModel::DT_ENTREGA;
        }
        $vendas = $this->modelVenda->selectForSearch($fields, $condition, $params);
        return $vendas;
    }

    /**
     * Esse método executa a renegociação de uma venda.
     */
    public function renegociarVenda(){

        $config = Config::getInstance();

        //Verificando se uma venda foi enviada na requisição
        $id_venda  = $config->filter("venda");
        if(empty($id_venda)) {
            $config->failInFunction("Venda Inválida");
            $config->redirect("index.php?op=cad-rene");
        }

        //Verifica se a data das parcelas ultrapassa os 60 dias
        $dataDemais = $config->filter("data-parcela");
        $dataDiff =  strtotime($dataDemais) - time();
        if ($dataDiff > MAIN_LIMIT_DATE) {
            $config->failInFunction("Data das demais parcelas não pode ser maior do que 60 dias");
            $config->redirect("index.php?op=cad-rene");
        }

        //Verifica a existência da venda
        $venda = $config->currentController->getVenda($id_venda);
        if(empty($venda->id) || $venda->status != VendaModel::STATUS_ATIVA) {
            $config->failInFunction("Venda Inválida/Não ativa");
            $config->redirect("index.php?op=cad-rene");
        }

        //Incluindo controladores auxiliares
        include_once CONTROLLERS."parcela.php";
        include_once CONTROLLERS."produto.php";
        $parcela_controller = new ParcelaController();
        $produto_controller = new ProdutoController();


        $restante           = $parcela_controller->getRestanteOfVenda($venda->id);
        $tipo               = $config->filter("tipo");
        //Obtendo valor da taxa de renegociação
        $multaoudesconto    = str_replace(',', '.', $config->filter("multaoudesconto"));
        //Em caso de desconto: atribuir valor negativo à taxa
        if($tipo{0} == 'd'){ $multaoudesconto *= -1; }
        $parcelas           = $parcela_controller->getParcleasByVenda($id_venda, ParcelaController::PARCELAS_NAO_PAGAS);
        $data               = date("Y-m-d");

        //Iniciando transação
        $this->modelVenda->begin();

        //Dando baixa em todas as parcelas restantes
        if(!$parcela_controller->darBaixaEmParcela($parcelas[0], $restante, null, $data, null, true)){
            $this->modelVenda->rollBack();
            $config->failInFunction("Falha ao ajustar parcelas da venda anterior.");
            $config->redirect("index.php?op=cad-rene");
        }

        //Adicionando o desconto para equilibrar o caixa
        $parcela_desconto = new Parcela( $parcelas[count($parcelas) - 1]->numero + 1,
                                         date("Y-m-d"), null, -$restante, true, $venda->id, false );
        $pagamento = new Pagamento();
        $pagamento->cobrador        = $parcela_desconto->cobrador;
        $pagamento->data            = date("Y-m-d");
        $pagamento->dataBaixa       = date("Y-m-d");
        $pagamento->valor           = -$restante;
        $pagamento->numeroParcela   = $parcela_desconto->numero;
        $pagamento->vendaParcela    = $parcela_desconto->venda;
        $pagamento->prestacaoConta  = null;
        $pagamento->autor           = @(!empty($_SESSION[SESSION_ID_FUNC])? $_SESSION[SESSION_ID_FUNC] : null);;
        $pagamento->comDesconto     = true;
        if(!$parcela_controller->addParcela($parcela_desconto) ||
            !$parcela_controller->addPagamento($pagamento)){
            $this->modelVenda->rollBack();
            $config->failInFunction("Falha ao registrar desconto em caixa da venda anterior.");
            $config->redirect("index.php?op=cad-rene");
        }

        //Alterando o status da venda para quitada
        $this->turnVendaQuitada($venda->id);

        //Copiando a venda para a nova venda
        $n_venda = new Venda();
        $n_venda->cliente           = $venda->cliente;
        $n_venda->agenteVendas      = $venda->agenteVendas;
        $n_venda->dataEntrega       = $venda->dataEntrega;
        $n_venda->dataVenda         = date("Y-m-d");
        $n_venda->loja              = $venda->loja;
        $n_venda->os                = null;
        $n_venda->previsaoEntrega   = $venda->previsaoEntrega;
        $n_venda->vendedor          = $venda->vendedor;
        //Relacioando a nova venda coma antiga
        $n_venda->vendaAntiga       = $venda->id;

        //Inserindo a nova venda.
        if(!$this->modelVenda->insert($n_venda)){
            $this->modelVenda->rollBack();
            $config->failInFunction("Falha ao registrar a nova venda.");
            $config->redirect("index.php?op=cad-rene");
        }

        //Criando os dois "produtos" da nova venda, que são a taxa e o restante da venda antiga
        $produtos_id        = $produto_controller->getProdutosOfNegociacao();
        $produto_taxa       = new ProdutoVenda(0, $produtos_id[0], $n_venda->id, $multaoudesconto);
        $produto_restante   = new ProdutoVenda(0, $produtos_id[1], $n_venda->id, $restante);
        if(!$this->modelProdutoVenda->inserts(array($produto_taxa, $produto_restante))){
            $this->modelVenda->rollBack();
            $config->failInFunction("Falha ao registrar a multa e valor restante.");
            $config->redirect("index.php?op=cad-rene");
        }

        //Ajustando as novas parcelas
        $n_venda->valor = $this->getValorOfVenda($n_venda->id);

        //Caso a renegociação seja com entrada, o calculo das parcelas já leva em consideração
        //o valor da entrada.
        //Caso a renegociação não seja com entrada, uma flag com true deve informar que é a renegociação
        //quem está calculando as parcelas para ajustar a numeração das parcelas
        $_REQUEST["entrada-recebida"] = true;
        $valor_entrada  = (float) str_replace(',', '.', $config->filter("entrada"));
        if(empty($valor_entrada)){
            $res = $parcela_controller->addParcelas($n_venda, true);
        } else {
            $res = $parcela_controller->addParcelas($n_venda);
        }

        if(!$res){
            $this->modelVenda->rollBack(); //Rollback das mudanças
            $config->failInFunction("Falha ao criar parcelas");
            $config->redirect("index.php?op=cad-rene");
        }

        $this->modelVenda->commit(); //Comitando as mudanças

        $config->successInFunction();

        $config->redirect("index.php?op=cad-rene");
    }

    /**
     * Obtém uma dicionário de parametros para busca de vendas. Entre os paramtros eles:
     * <ul>
     * <li> ID da venda </li>
     * <li> Nº da OS</li>
     * <li> CPF do cliente</li>
     * <li> Nome do cliente</li>
     * <li> ID Loja </li>
     * <li> Data da venda </li>
     * <li> Data de entrega </li>
     * </ul>
     * @return array um dicionário que tem como chaves os campos das tabelas e
     * como valor um outro array de 2 itens:
     * o item 0 é o valor que vai ser usado na busca (default é '') e o item 1 é
     * uma constante que define que tipo de
     * comparação SQL vai ser usada para o parametro de busca (ver constantes SQL_CMP_* e SQL_IS_*).
     */
    public function fieldsToSearch(){
        $arr[VendaModel::TABLE.".".VendaModel::ID]                      = array("",SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES);
        $arr[OrdemServicoModel::TABLE.".".OrdemServicoModel::NUMERO]    = array("",SQL_CMP_CLAUSE_LIKE);
        $arr[ClienteModel::TABLE.".".ClienteModel::CPF]                 = array("",SQL_CMP_CLAUSE_LIKE);
        $arr[ClienteModel::TABLE.".".ClienteModel::NOME]                = array("",SQL_CMP_CLAUSE_LIKE);
        $arr[VendaModel::TABLE.".".VendaModel::LOJA]                    = array("",SQL_CMP_CLAUSE_EQUAL_WITHOUT_QUOTES);
        $arr[VendaModel::TABLE.".".VendaModel::DT_VENDA]                = array("",SQL_CMP_CLAUSE_EQUAL);
        $arr[VendaModel::TABLE.".".VendaModel::DT_ENTREGA]              = array("",SQL_CMP_CLAUSE_EQUAL);
        return $arr;
    }

    /**
     * Obtém uam venda específica do modelo.
     * @param int $id_venda identificador da venda
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Venda venda correspodente, se o id não existe vem uma venda com valores padrões
     */
    public function getVenda($id_venda , $foreign_values = false){
        $condition = VendaModel::TABLE.".".VendaModel::ID." = $id_venda";
        if($foreign_values) $venda = $this->modelVenda->superSelect($condition);
        else $venda = $this->modelVenda->select("*", $condition);
        if(empty($venda)) return new Venda();
        return $venda[0];
    }

    /**
     * Esse método é utlizado para vendas renegociadas, das quais se obtém a venda primária.
     * <br/>
     * Ex.: Se a venda 120 foi renegociada, e teve o id 180 na nova venda,
     * e a venda 180 foi renegociada, e
     * teve o id 215 da nova venda, se 215 for passado com parametro de
     * getPrimatyVend, o retorno é um objeto da vendo com o id 120. A mesma saída
     * ocorre com a entrada 180.
     * @param int $id_venda venda renegociada
     * @return Venda venda primária de uma venda renegociada.
     */
    public function getPrimaryVenda($id_venda){
        $venda_curr = $this->getVenda($id_venda);
        while($venda_curr->vendaAntiga) $venda_curr = $this->getVenda($venda_curr->vendaAntiga);
        return $venda_curr;
    }

    /**
     * Obtém o valor total de uma venda.
     * @param int $vendaid identificador da venda
     * @return float soma dos valores de uma venda
     */
    public function getValorOfVenda($vendaid){
        return $this->modelProdutoVenda->valorVenda($vendaid);
    }

    /**
     * Obtém a venda de uma ordem de serviço.
     * @param int $id_os identificador da ordem de serviço
     * @return Venda venda correspodente, se o id não existe vem uma venda com valores padrões
     */
    public function getVendaByOrdemServico($id_os){
        $condition = VendaModel::OS." = $id_os ";
        $venda = $this->modelVenda->select("*", $condition);
        if(empty($venda)) return new Venda();
        return $venda[0];
    }

    /**
     * Obtém a lista de produtos de uma venda.
     * <br/>
     * Obs.: Não são do tipo Produto e sim <b>ProdutoVenda</b>.
     * @param int $vendaid identificador da venda
     * @return array lista de produtos associados a venda do tipo ProdutoVenda.
     */
    public function getProdutosVendaOfVenda($vendaid){
        $condition = ProdutoVendaModel::VENDA." = $vendaid";
        return $this->modelProdutoVenda->select("*", $condition);
    }

    /**
     * Obtém o identificador do cobrador de uma venda
     * @param int $id_venda identificador da venda
     * @return int identificador do cobrador de uma venda
     */
    public function getCobradorByVenda($id_venda){
        return $this->modelVenda->cobradorForVenda($id_venda);
    }

    /**
     * Atualiza a data de entrega de uma venda.
     * <br/>
     * Os valores são passados via requisição.
     */
    public function alterDataEntrega(){
        $config     = Config::getInstance();
        $venda      = $config->filter("venda");
        $data       = $config->filter("data-entrega");
        $condition  = VendaModel::ID." = $venda ";
        $field_data = $config->filter('is_previsao') == null ? VendaModel::DT_ENTREGA : VendaModel::DT_PREVISAO ;
        if($this->modelVenda->simpleUpdate($field_data, empty($data) ? 'NULL' : "'$data'", $condition)){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelVenda->handleError());
        }
        $config->redirect("index.php?op=cad-vend");
    }

    /**
     * Obtém uma par de valores sobre o resumo quantidade e valor de vendas de
     * uma região em um periodo <i>date</i>.
     * <br/>
     * O par é chaveado por 'qtd' e 'val', correspondendo à quantiade e soma
     * dos valores das vendas no determinado periodo respectivamente.
     * @param int $regiao identificador da região
     * @param string $date mes ou dia usado na filtragem (usar formato padrão YYYY-MM ou YYYY-MM-DD)
     * @return array par de valores da quantdidade de vendas e soma dos valores das vendas.
     */
    public function getVendasByRegiaoInDate($regiao, $date){
        $condition  = VendaModel::DT_VENDA." LIKE '$date%' ";
        $condition .= ' AND '.VendaModel::VENDA_ANTIGA.' IS NULL ';
        $vendas     = $this->modelVenda->selectForRegiao($regiao, VendaModel::TABLE.".".VendaModel::ID, $condition);
        $sum        = 0;
        foreach ($vendas as $venda) {
            $sum += $this->getValorOfVenda($venda->id);
        }
        return array("qtd" => count($vendas), "val" => $sum );
    }

    public function getVendasByOthersRegioesInDate($loja, $date) {
        $condition  = VendaModel::DT_VENDA." LIKE '$date%' ";
        $condition .= ' AND ' . VendaModel::TABLE.".".VendaModel::LOJA." <> ".RegiaoModel::TABLE.".".RegiaoModel::LOJA;
        $condition .= ' AND '.VendaModel::VENDA_ANTIGA.' IS NULL ';
        $vendas     = $this->modelVenda->selectForRegiao(null, VendaModel::TABLE.".".VendaModel::ID, $condition);
        $sum        = 0;
        foreach ($vendas as $venda) {
            $sum += $this->getValorOfVenda($venda->id);
        }
        return array("qtd" => count($vendas), "val" => $sum );
    }

    /**
     * Muda o status de uma venda para quitada.
     * @param int $venda_id identificador da venda
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function turnVendaQuitada($venda_id){
        $condition = VendaModel::ID." = $venda_id ";
        return $this->modelVenda->simpleUpdate(VendaModel::STATUS, VendaModel::STATUS_QUITADA, $condition );
    }
    /**
     * Muda o status de uma venda para devolvida.
     * @param int $venda_id identificador da venda
     * @param boolean $devolver_produtos indica se os produtos devem ser retornados ao estoque
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function turnVendaDevolvida($venda_id, $devolver_produtos = true){
        if($devolver_produtos && !$this->retornarProdutosVenda($venda_id)){
            return false;
        }
        $condition = VendaModel::ID." = $venda_id ";
        return $this->modelVenda->simpleUpdate(VendaModel::STATUS, VendaModel::STATUS_DEVOLVIDA, $condition);
    }

    /**
     * Muda o status de uma venda para cancelada.
     * @param int $venda_id identificador da venda
     * @param boolean $devolver_produtos indica se os produtos devem ser retornados ao estoque
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function turnVendaCancelada($venda_id, $devolver_produtos = true){
        if($devolver_produtos && !$this->retornarProdutosVenda($venda_id)){
            return false;
        }
        $condition = VendaModel::ID." = $venda_id ";
        return $this->modelVenda->simpleUpdate(VendaModel::STATUS, VendaModel::STATUS_CANCELADA, $condition );
    }

    /**
     * Retorna os produtos da venda para o estoque da loja em que a venda foi realizada;
     * @param int $venda_id identificador da venda
     * @return boolean true caso as operações forem realizadas com sucesso, false caso contrário
     */
    public function retornarProdutosVenda($venda_id){
        $venda = $this->getVenda($venda_id);
        if(empty($venda->id)) { return false; }
        include_once CONTROLLERS.'centralEstoque.php';
        $centralEstoque = new CentralEstoqueController();
        $produtos_venda = $this->getProdutosVendaOfVenda($venda_id);
        $pre_transc     = $this->modelVenda->inTransaction();
        if(!$pre_transc) { $this->modelVenda->begin();}
        foreach($produtos_venda as $pv){
            if(!$centralEstoque->mergeProdutoInEstoque($pv->produto, $venda->loja, 1)){
                if(!$pre_transc){ $this->modelVenda->rollBack(); }
                return false;
            }
        }
        if(!$pre_transc){ $this->modelVenda->commit(); }
        return true;
    }

    /**
     * Muda o status de uma venda para ativa.
     * @param int $venda_id identificador da venda
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function turnVendaAtiva($venda_id){
        $condition = VendaModel::ID." = $venda_id ";
        return $this->modelVenda->simpleUpdate(VendaModel::STATUS, VendaModel::STATUS_ATIVA, $condition );
    }

    /**
     * Automatiza a verificação de status de uma venda com base na quantidade
     * de parcelas não pagas.
     * <br/>
     * Se existir parcelas não pagas, este método atualiza o status da venda
     * para ativa, se não a venda se torna quitada.
     * @param int $venda_id identificador da venda
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function checkAndTurnStatus($venda_id){
        include_once CONTROLLERS."parcela.php";
        $parcela_controller = new ParcelaController();
        $res = count($parcela_controller->getParcleasByVenda($venda_id, ParcelaController::PARCELAS_NAO_PAGAS));
        return $res > 0? $this->turnVendaAtiva($venda_id) : $this->turnVendaQuitada($venda_id);
    }

    /**
     * Obtém o nome de um status para exibição na grid.
     * @param int $status identificador do status
     * @return string nome do status
     */
    public function getStatusName($status){
        $dis = array(VendaModel::STATUS_ATIVA       => "Ativa",
                     VendaModel::STATUS_CANCELADA   => "Cancelada",
                     VendaModel::STATUS_QUITADA     => "Quitada",
                     VendaModel::STATUS_RENEGOCIADA => "Renegociada",
                     VendaModel::STATUS_DEVOLVIDA   => "Devolvida" );
        return isset($dis[$status])? $dis[$status] : "Undf.";
    }

    /**
     * Obtém as vendas de uma rota não possuem data de entrega preenchida,
     * mas já possuem OS com data de recebimento preenchida.
     * @param int $rota identificador da rota
     * @param string $data_limite_superior data máxima das datas de entrega das ordens de serviço
     * @param string $data_limite_inferior data mínima das datas de entrega das ordens de serviço
     * @return array lista de vendas do tipo venda
     */
    public function getVendasNaoEntregues($rota, $data_limite_superior, $data_limite_inferior = ''){
        $condition  = VendaModel::DT_ENTREGA.' IS NULL AND '.VendaModel::DT_PREVISAO.' <= \''.$data_limite_superior.'\' ';
        if(!empty($data_limite_inferior))
            $condition .= ' AND '.VendaModel::DT_PREVISAO.' >= \''.$data_limite_inferior.'\' ';
        $condition .= ' AND '.OrdemServicoModel::DT_RECEBIMENTO. ' IS NOT NULL ';
        $condition .= ' AND '.VendaModel::TABLE.'.'.VendaModel::STATUS.' = '.VendaModel::STATUS_ATIVA;
        $fields     = array(VendaModel::TABLE.'.'.VendaModel::ID, VendaModel::VENDEDOR,
                            VendaModel::DT_VENDA, VendaModel::DT_PREVISAO,
                            VendaModel::CLIENTE,
                            OrdemServicoModel::TABLE.'.'.OrdemServicoModel::NUMERO.' AS '.VendaModel::OS);
        return $this->modelVenda->selectForRota($rota, $fields, $condition);
    }

    public function getVendasEntregues($idLoja = null, $dtIni = '', $dtFinal = '', $idAgente = null) {
        $conditions = array();
        $conditions[] = VendaModel::DT_ENTREGA.' IS NOT NULL ';
        $conditions[] = VendaModel::VENDA_ANTIGA.' IS NULL ';

        if ($idLoja != null) {
            $conditions[] = VendaModel::LOJA . ' = ' . $idLoja;
        }

        if ($idAgente != null) {
            $conditions[] = VendaModel::AGENTE . ' = ' . $idAgente;
        }

        if (!empty($dtIni)) {
            $conditions[] = VendaModel::DT_ENTREGA . ' >= ' . "'".$dtIni."'";
        }
        if (!empty($dtFinal)) {
            $conditions[] = VendaModel::DT_ENTREGA . ' <= ' . "'".$dtFinal."'";
        }

        return $this->modelVenda->select('*', implode(' AND ', $conditions));
    }

    public function getAllVendasQuitadas($idLoja = null, $dtIni = '', $dtFinal = '') {
        include_once MODELS."parcela.php";

        $joins[] = "INNER JOIN ".ParcelaModel::TABLE." ON ".ParcelaModel::VENDA." = ".VendaModel::TABLE.".".VendaModel::ID;
        $joins[] = "INNER JOIN ".PagamentoModel::TABLE." ON (".PagamentoModel::VENDA_PARCELA." = ".ParcelaModel::VENDA." AND ".PagamentoModel::NUMERO_PARCELA." = ".ParcelaModel::NUMERO.")";

        $conditionStatus = VendaModel::STATUS . ' = ' . VendaModel::STATUS_QUITADA;
        $conditions = array($conditionStatus);

        $conditions[] = $this->getNotRenegociadaCondition();

        if ($idLoja != null) {
            $conditions[] = VendaModel::LOJA . ' = ' . $idLoja;
        }

        $conditions[] = ParcelaModel::NUMERO . " = (SELECT MAX(".ParcelaModel::NUMERO.") FROM ".ParcelaModel::TABLE.
            " WHERE ".ParcelaModel::VENDA." = ".VendaModel::TABLE.".".VendaModel::ID.")";

        $condition = implode(' AND ', $conditions) . " GROUP BY ".VendaModel::TABLE.".".VendaModel::ID.
            " HAVING PAG_MAX BETWEEN '$dtIni' AND '$dtFinal' ".
            " ORDER BY PAG_MAX ";

        return $this->modelVenda->selectJoins(array(VendaModel::TABLE.'.*', 'MAX('.PagamentoModel::DATA.') AS PAG_MAX'),
            $joins, $condition);
    }

    public function getVendasRangeDate($idLoja,  $dtIni = '', $dtFinal = '') {
        $usefulStatus = array(VendaModel::STATUS_ATIVA, VendaModel::STATUS_QUITADA);
        $condition  = VendaModel::LOJA . ' = ' . $idLoja;
        $condition .= ' AND ' . VendaModel::DT_VENDA . " BETWEEN '$dtIni' AND '$dtFinal' ";
        $condition .= ' AND ' . VendaModel::STATUS . ' IN ( ' . implode(',', $usefulStatus) .' ) ';
        return $this->modelVenda->select("*", $condition);
    }

    /**
     * Verifica se existem venda em uma região que não possuem data de entrega preenchida,
     * mas já possuem OS com data de recebimento preenchida.
     * @param int $regiao identificador da região
     * @param string $data_limite_superior data máxima das datas de entrega das ordens de serviço
     * @param string $data_limite_inferior data mínima das datas de entrega das ordens de serviço
     * @return bool true se existir entregas na região, ou false se nao existir.
     */
    public function existsEntregasInRegiao($regiao, $data_limite_superior, $data_limite_inferior = ''){
        $condition  = VendaModel::DT_ENTREGA.' IS NULL AND '.VendaModel::DT_PREVISAO.' <= \''.$data_limite_superior.'\' ';
        if(!empty($data_limite_inferior))
            $condition .= ' AND '.VendaModel::DT_PREVISAO.' >= \''.$data_limite_inferior.'\' ';
        $condition .= ' AND '.OrdemServicoModel::DT_RECEBIMENTO. ' IS NOT NULL ';
        $condition .= ' AND '.VendaModel::TABLE.'.'.VendaModel::STATUS.' = '.VendaModel::STATUS_ATIVA;
        $vendas = $this->modelVenda->selectForRegiao($regiao, VendaModel::TABLE.'.'.VendaModel::ID, $condition);
        return !empty($vendas);
    }

    /**
     * Verifica se uma venda é editável ou não. Uma venda é editável para o modelo de venda se:
     * <ul>
     * <li>Está ativa</li>
     * <li>e não é renegociada</li>
     * </ul>
     * @param Venda $v venda que será verificada
     * @return bool true se a venda é editável ou false se não for editável.
     */
    public function isEditableVenda(Venda $v){
        return $v->status == VendaModel::STATUS_ATIVA && empty($v->vendaAntiga);
    }

    /**
     * Verifica se uma venda foi renegociada
     * @param int $id_venda identificador da venda
     * @return bool true se a venda é pai de alguma outra venda ou false se não tiver sido renegociada
     */
    public function hasRenegociada($id_venda){
        $vendas = $this->modelVenda->select(VendaModel::ID, VendaModel::VENDA_ANTIGA.' = '.$id_venda);
        return !empty( $vendas );
    }

    /**
     * Obtém as vendas de um agente de vendas
     * @param int $id_agente identificador do agente de vendas
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @param string $data_ini data inicial das vendas
     * @param string $data_fim data final das vendas
     * @return array lista de vendas do tipo Venda
     */
    public function getVendasByAgente($id_agente, $data_ini = '', $data_fim = '') {
        return $this->getVendasByTypeFunc(VendaModel::AGENTE, $id_agente, VendaModel::ALIAS_AGENTE, $data_ini, $data_fim);
    }

    /**
     * Obtém as vendas de um agente de vendas
     * @param int $id_vendedor identificador do vendedor
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @param string $data_ini data inicial das vendas
     * @param string $data_fim data final das vendas
     * @return array lista de vendas do tipo Venda
     */
    public function getVendasByVendedor($id_vendedor, $data_ini = '', $data_fim = '') {
        return $this->getVendasByTypeFunc(VendaModel::VENDEDOR, $id_vendedor, VendaModel::ALIAS_VENDEDOR, $data_ini, $data_fim);
    }

    /**
     * Obtém as vendas de um agente de vendas
     * @param int $id_lider identificador do lider de equipe
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @param string $data_ini data inicial das vendas
     * @param string $data_fim data final das vendas
     * @return array lista de vendas do tipo Venda
     */
    public function getVendasByLiderEquipe($id_lider, $data_ini = '', $data_fim = '') {
        return $this->getVendasByTypeFunc(VendaModel::LIDER_EQUIPE, $id_lider, VendaModel::ALIAS_LIDER, $data_ini, $data_fim);
    }

    private function getVendasByTypeFunc($table_field, $ids, $alias, $data_ini, $data_fim) {
        $condition = $table_field;
        if(is_array($ids)){ $condition .= ' IN ('.implode(',', $ids).')'; }
        else { $condition .= ' = '.$ids; }
        $condition .= ' AND '.VendaModel::DT_VENDA.' BETWEEN \''.$data_ini.'\' AND \''.$data_fim.'\'';
        $condition .= ' AND '.VendaModel::TABLE.".".VendaModel::LOJA.' = '.$alias.'.'.FuncionarioModel::LOJA;
        $condition .= ' AND '.VendaModel::TABLE.".".VendaModel::VENDA_ANTIGA." IS NULL ";
        $condition .= ' AND '.$alias.'.'.FuncionarioModel::STATUS.' = TRUE ';
        return $this->modelVenda->superSelect($condition);
    }

    private function getNotRenegociadaCondition() {
        $subselect  = " SELECT COUNT(V.".VendaModel::ID.") FROM ".VendaModel::TABLE." V ";
        $subselect .= " WHERE V.".VendaModel::VENDA_ANTIGA." = ".VendaModel::TABLE.".".VendaModel::ID;
        return " ($subselect) = 0 ";
    }

    public function getVendasFromLojaByForeignAgente($loja_id, $data_ini = '', $data_fim = '') {
        return $this->getVendasFromLojaByForeignFunc(VendaModel::ALIAS_AGENTE, $loja_id, $data_ini, $data_fim);
    }

    public function getVendasFromLojaByForeignVendedor($loja_id, $data_ini = '', $data_fim = '') {
        return $this->getVendasFromLojaByForeignFunc(VendaModel::ALIAS_VENDEDOR, $loja_id, $data_ini, $data_fim);
    }

    public function getVendasFromLojaByForeignLiderEquipe($loja_id, $data_ini = '', $data_fim = '') {
        return $this->getVendasFromLojaByForeignFunc(VendaModel::ALIAS_LIDER, $loja_id, $data_ini, $data_fim);
    }

    private function getVendasFromLojaByForeignFunc($func_alias, $loja_id, $data_ini = '', $data_fim = '') {
        $condition  = VendaModel::TABLE.".".VendaModel::LOJA . " = $loja_id ";
        $condition .= ' AND '.VendaModel::DT_VENDA.' BETWEEN \''.$data_ini.'\' AND \''.$data_fim.'\'';
        $condition .= ' AND '.VendaModel::TABLE.".".VendaModel::LOJA . " <> $func_alias.".FuncionarioModel::LOJA;
        $condition .= ' AND '.VendaModel::TABLE.".".VendaModel::VENDA_ANTIGA." IS NULL ";
        return $this->modelVenda->superSelect($condition);
    }

    /**
     * Obtém os cancelamentos de venda por loja.
     * @param int $loja_id identificador da loja
     * @param string $min_date data mínima para cancelamento
     * @param string $max_date data máxima para cancelamento
     * @return array lista de cancelamentos do tipo Cancelamento
     */
    public function getCancelamentosByLoja($loja_id, $min_date, $max_date){
       include_once MODELS.'cancelamento.php';
       include_once ENTITIES.'cancelamento.php';
       $condition  = VendaModel::TABLE.'.'.VendaModel::LOJA . ' = ' . $loja_id;
       $condition .= ' AND ' . CancelamentoModel::DATA." BETWEEN '$min_date' AND '$max_date' ";
       $modelCancelamento = new CancelamentoModel();
       return $modelCancelamento->select('*', $condition);
    }

    /**
     * Obtém as vendas por integrantes de uma equipe de vendas de vendas.
     * @param mixed $intgrantes pode ser um vetor de identificadores ou um identificador de um integrante
     * @param string $data_ini data incial do período de busca
     * @param string $data_fim data final do período de busca
     * @return array lista de vendas do tipo Venda
     */
    public function getVendasByIntegranteDeEquipe($integrantes, $data_ini = '', $data_fim = ''){
        $condition = VendaModel::AGENTE ;
        if(is_array($integrantes)){ $condition .= ' IN ('.implode(',', $integrantes).')'; }
        else { $condition .= ' = '.$integrantes; }
        $condition .= ' AND ' . VendaModel::DT_VENDA.' BETWEEN \''.$data_ini.'\' AND \''.$data_fim.'\'';
        return $this->modelVenda->select("*", $condition);
    }

    public function getVendasByEquipe($equipeId, $data_ini = '', $data_fim = ''){
        $condition = VendaModel::EQUIPE . " = $equipeId ";
        $condition .= ' AND ' . VendaModel::DT_VENDA.' BETWEEN \''.$data_ini.'\' AND \''.$data_fim.'\'';
        return $this->modelVenda->select("*", $condition);
    }

    public function getVendasByNullEquipe($loja_id, $data_ini = '', $data_fim = ''){
        $condition = VendaModel::EQUIPE . " IS NULL AND " . VendaModel::LOJA . " =  $loja_id " ;
        $condition .= ' AND ' . VendaModel::DT_VENDA.' BETWEEN \''.$data_ini.'\' AND \''.$data_fim.'\'';
        return $this->modelVenda->select("*", $condition);
    }

    /**
     * Obtém e atribui o lider da equipe da venda, caso a equipe exista.
     *
     * @param Venda $venda venda alvo
     *
     * @return true caso a equipe da venda exista e false caso contrário.
     */
    private function linkEquipe(Venda &$venda) {
        $equipeController = new EquipeController();
        $equipe = $equipeController->getEquipe($venda->equipe);
        if (!empty($equipe->id)) {
            $venda->liderEquipe = $equipe->lider;
            return true;
        } else return false;
    }

}
?>
