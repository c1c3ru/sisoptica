<?php
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."produto.php";
include_once ENTITIES."tipo-produto.php";
include_once ENTITIES."marca.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."produto.php";
include_once MODELS."tipo-produto.php";
include_once MODELS."marca.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de produtos, 
 * do modelo de tipos de produtos e de marcas de produtos.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ProdutoController {
    
    private $modelProduto;
    private $modelTipo;
    private $modelMarca;
    
    /**
     * Alias para ProdutoModel::CAT_LENTE, que representa o valor de identificação 
     * da categoria LENTE
     */
    const CAT_LENTE = ProdutoModel::CAT_LENTE;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de produtos, 
     * do modelo de tipos de produtos e de marcas de produtos.
     * @return ProdutoController instancia de um controlador de produtos
     */
    public function __construct() {
        $this->modelProduto = new ProdutoModel();
        $this->modelTipo    = new TipoProdutoModel();
        $this->modelMarca   = new MarcaModel();
    }
    
    //Ações relevantes ao model de Produtos
    /**
     * Este método adiciona ou atualiza um produto no modelo.
     * @param Produto $produto produto a ser inserido ou atualizado. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>produto</i>
     */
    public function addProduto(Produto $produto = null){
        if(is_null($produto)){
            $produto = new Produto();
            //Atribuindo dados da requisição
            $this->linkProduto($produto);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if( (empty($produto->codigo) && $produto->codigo != '0') || empty($produto->descricao) || 
            empty($produto->tipo) || empty($produto->marca)){
            $config->failInFunction("Os campos: Código, Descrição, Tipo e Marca, são necessários para a inserção");
            $config->redirect("?op=cad-prod");
        }
        
        $hasId = $config->filter("for-update-id");
        $res = false;
        
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inserção
            $res = $this->modelProduto->insert($produto);
        } else {
            //Atualização
            $produto->id = $hasId;
            $res = $this->modelProduto->update($produto);
        }
        
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelProduto->handleError());                
        }
        $config->redirect("index.php?op=cad-prod");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito ao produto 
     * com um objeto passado como referência
     * @param Produto $produto uma referência de um produto que vai ser preenchido com os dados da requisição
     * @access private
     */
    private function linkProduto(Produto &$produto){
        $config = Config::getInstance();
        $produto->codigo        = $config->filter("codigo");
        $produto->descricao     = mb_strtoupper($config->filter("descricao"), 'UTF-8');
        $produto->precoCompra   = str_replace(',', '.', $config->filter("preco-compra"));
        $produto->precoVenda    = str_replace(',', '.', $config->filter("preco-venda"));
        $produto->precoVendaMin = str_replace(',', '.', $config->filter("preco-venda-min"));
        $produto->tipo          = $config->filter("tipo");
        $produto->marca         = $config->filter("marca");
        $produto->categoria     = $config->filter('categoria');
    }
    
    /**
     * Remove um produto do modelo com base no identificador passado na requisição.
     */
    public function removerProduto(){
        $config = Config::getInstance();
        $id_produto = $config->filter("prod");
        if(isset($id_produto)){
            if($this->modelProduto->delete($id_produto)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelProduto->handleError());
            }    
        } else {
            $config->failInFunction("Produto não informado");
        }
        $config->redirect("?op=cad-prod");
    }
    
    /**
     * Obtém todos os produtos do modelo.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de produtos do tipo Produto
     */
    public function getAllProdutos($foreign_values = false){
        $condition = ProdutoModel::TABLE.".".ProdutoModel::ID." NOT IN (".ProdutoModel::ID_PRODUTO_TAXA.",".ProdutoModel::ID_PRODUTO_RESTANTE.") ";
        if($foreign_values)
            return $this->modelProduto->superSelect($condition);
        return $this->modelProduto->select("*", $condition);
    }
    
    /**
     * Obtém todos os produtos do modelo da categoria lente.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de produtos do tipo Produto
     */
    public function getAllLLentes($foreign_values = false){
        $condition  = ProdutoModel::TABLE.".".ProdutoModel::ID." NOT IN (".ProdutoModel::ID_PRODUTO_TAXA.",".ProdutoModel::ID_PRODUTO_RESTANTE.") ";
        $condition .= ' AND ' . ProdutoModel::CATEGORIA . " = " . ProdutoModel::CAT_LENTE;
        if($foreign_values)
            return $this->modelProduto->superSelect($condition);
        return $this->modelProduto->select("*", $condition);
    }
    
    /**
     * Obtém um produto em específico.
     * @param int $id_produto identificador do produto.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Produto produto específico ou um produto vazio em caso de inexistência.
     */
    public function getProduto($id_produto, $foreign_values = false){
        $condition = ProdutoModel::TABLE.'.'.ProdutoModel::ID." = $id_produto";
        if($foreign_values) $produto = $this->modelProduto->superSelect ($condition);
        else $produto = $this->modelProduto->select("*", $condition);
        if(!empty($produto)) return $produto[0];
        return new Produto();
    }
    
    /**
     * Obtém um produto em específico com base no seu código.
     * @param int $cod_produto codigo do produto.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Produto produto específico ou um produto vazio em caso de inexistência.
     */
    public function getProdutoByCodigo($cod_produto, $foreign_values = false){
        $condition = ProdutoModel::TABLE.'.'.ProdutoModel::CODIGO." = $cod_produto";
        if($foreign_values) $produto = $this->modelProduto->superSelect ($condition);
        else $produto = $this->modelProduto->select("*", $condition);
        if(!empty($produto)) return $produto[0];
        return new Produto();
    }


    /**
     * Obtém os identificadores dos produtos que são usados na renegociação:
     * <ul>
     * <li>[0] Taxa de renegociação</li>
     * <li>[1] Restante da venda a ser renegociada</li>
     * </ul>
     * @return array tupla com os identificadores dos produtos de renegociação.
     */
    public function getProdutosOfNegociacao(){
        return array(ProdutoModel::ID_PRODUTO_TAXA, ProdutoModel::ID_PRODUTO_RESTANTE);
    }
    
    /**
     * Obtém o valor textual da categoria de um produto.
     * @param int $categoria identificação da categoria
     * @return string categoria em forma de texto
     */
    public static function getTextualCategoria($categoria){
        switch ($categoria){
            case ProdutoModel::CAT_LENTE: return "Lente";
            case ProdutoModel::CAT_PRODUTO: return "Produto (Genérica)";
            default: return "Inválida";
        }
    }
    
    /**
     * Obtém um mapa onde o índice é um produto e o valor é sua categoria.
     * @param mixed $ids identificadores dos produtos
     * @return array mapa de categoria
     */
    public function getMapaCategoria($ids_produtos){
        return $this->modelProduto->catMap($ids_produtos);
    }


    //Ações relevantes ao model de Tipo de Produtos
    /**
     * Este método adiciona ou atualiza uma tipo de produto no modelo.
     * @param TipoProduto $tipo tipo de produto a ser inserido ou atualizado. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>tipo</i>
     */
    public function addTipoProduto(TipoProduto $tipo = null){
        if(is_null($tipo)){
            $tipo = new TipoProduto();
            //Atribuindo dados da requisição
            $this->linkTipoProduto($tipo);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if(empty($tipo->nome)){
            $config->failInFunction("O campo Nome é necessário para inserção");
            $config->redirect("?op=cad-tipo");
        }
        
        $hasId = $config->filter("for-update-id");
        $res = false;
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inserção
            $res = $this->modelTipo->insert($tipo);
        } else {
            //Atualização
            $tipo->id = $hasId;
            $res = $this->modelTipo->update($tipo);
        }
        
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelTipo->handleError());                
        }
        
        $config->redirect("index.php?op=cad-tipo");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito ao tipo de produto 
     * com um objeto passado como referência
     * @param TipoProduto $tipo uma referência de um tipo de produto que vai ser preenchido com os dados da requisição
     * @access private
     */
    private function linkTipoProduto(TipoProduto &$tipo){
        $config = Config::getInstance();
        $tipo->nome =  mb_strtoupper($config->filter("nome"), 'UTF-8');
    }
    
    /**
     * Remove um tipo produto do modelo com base no identificador passado na requisição.
     */
    public function removerTipoProduto(){
        $config = Config::getInstance();
        $id_tipo = $config->filter("tipo");
        if(isset($id_tipo)){
            if($this->modelTipo->delete($id_tipo)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelTipo->handleError());
            }    
        } else {
            $config->failInFunction("Tipo de produto não informado");
        }
        $config->redirect("?op=cad-tipo");
    }
    
    /**
     * Obtém todos os tipos de produtos do modelo.
     * @return array lista de tipos produtos do tipo TipoProduto
     */
    public function getAllTiposProduto(){
        return $this->modelTipo->select();
    }
    
    /**
     * Obtém um tipo de produto em específico.
     * @param int $id_tipo identificador do tipo de produto.
     * @return TipoProduto tipo de produto específico ou um tipo de produto vazio em caso de inexistência.
     */
    public function getTipoProduto($id_tipo){
        $tipo = $this->modelTipo->select(" * ", TipoProdutoModel::ID." = $id_tipo");
        if(!empty($tipo)) return $tipo[0];
        return new TipoProduto();
    }
    
    //Ações relevantes ao model de Marcas de Produtos
    /**
     * Este método adiciona ou atualiza uma marca de produto no modelo.
     * @param Marca $marca marca a ser inserida ou atualizada. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>marca</i>
     */
    public function addMarca(Marca $marca = null){
        if(is_null($marca)){
            $marca = new Marca();
            //Atribuindo dados da requisição
            $this->linkMarca($marca);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if(empty($marca->nome)){
            $config->failInFunction("O campo Nome é necessário para inserção");
            $config->redirect("?op=cad-marc");
        }
        
        $hasId = $config->filter("for-update-id");
        $res = false;
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inserção
            $res = $this->modelMarca->insert($marca);
        } else {
            //Atualização
            $marca->id = $hasId;
            $res = $this->modelMarca->update($marca);
        }
        
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelMarca->handleError());                
        }
        
        $config->redirect("index.php?op=cad-marc");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à marca 
     * com um objeto passado como referência
     * @param Marca $marca uma referência de uma marca que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkMarca(Marca &$marca){
        $config = Config::getInstance();
        $marca->nome = mb_strtoupper($config->filter("nome"), 'UTF-8');
    }
    
    /**
     * Remove uma marca do modelo com base no identificador passado na requisição.
     */
    public function removerMarca(){
        $config = Config::getInstance();
        $id_marca = $config->filter("marc");
        if(isset($id_marca)){
            if($this->modelMarca->delete($id_marca)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelMarca->handleError());
            }    
        } else {
            $config->failInFunction("Marca não informada");
        }
        $config->redirect("?op=cad-marc");
    }
    
    /**
     * Obtém todas as marcas de produtos do modelo.
     * @return array lista de marcas do tipo Marca
     */
    public function getAllMarcas(){
        return $this->modelMarca->select();
    }
    
    /**
     * Obtém uma marca de produto em específico.
     * @param int $id_marca identificador da marca.
     * @return Marca marca específica ou uma marca vazia em caso de inexistência.
     */
    public function getMarca($id_marca){
        $marca = $this->modelMarca->select(" * ", MarcaModel::ID." = $id_marca");
        if(!empty($marca)) return $marca[0];
        return new Marca();   
    }
    
    /**
     * Atualiza o valor de compra de um produto.
     * @param Produto $p produto que irá ser atualizado
     * @return boolean true em caso de sucesso, ou false em caso de falha
     */
    public function updatePrecoCompra(Produto $p){
        $condition = ProdutoModel::ID." = ".$p->id;
        return $this->modelProduto->simpleUpdate(ProdutoModel::PRECO_COMPRA, $p->precoCompra, $condition);
    }
}
?>