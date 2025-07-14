<?php
namespace Sisoptica\Repository;
//Incluindo classes associadas a esse modelo
include_once MODELS."tipo-produto.php";
include_once MODELS."marca.php";

/**
 * Essa classe implementa o modelo da entidade Produto.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ProdutoModel extends Database {
    
    /**
     * Nome da tabela de produtos no banco de dados 
     */
    const TABLE = "produto";
    
    /**
     * Nome da coluna do identificador do produto no banco de dados.
     */
    const ID                = "id";
    
    /**
     * Nome da coluna do codigo do produto no banco de dados.
     */
    const CODIGO            = "cod_produto";
    
    /**
     * Nome da coluna da descrição do produto no banco de dados.
     */
    const DESCRICAO         = "descricao";
    
    /**
     * Nome da coluna do preço de compra do produto no banco de dados.
     */
    const PRECO_COMPRA      = "preco_compra";
    
    /**
     * Nome da coluna do preço de venda do produto no banco de dados.
     */
    const PRECO_VENDA       = "preco_venda";
    
    /**
     * Nome da coluna do preço mínimo de venda  no banco de dados.
     */
    const PRECO_VENDA_MIN   = "preco_venda_min";
    
    /**
     * Nome da coluna do identificador do tipo do produto no banco de dados.
     */
    const TIPO              = "id_tipo_produto";
    
    /**
     * Nome da coluna do identificador da marca do produto no banco de dados.
     */
    const MARCA             = "id_marca_produto";
    
    /**
     * Nome da coluna do identificador da categoria do produto no banco de dados.
     */
    const CATEGORIA         = "categoria";
    
    /**
     * Constante para identificar a categoria geral de produtos
     */
    const CAT_PRODUTO       = 0;
    
    /**
     * Constatnte para identificar a categoria lente
     */
    const CAT_LENTE       = 1;
    
    /**
     * Identificador do produto que representa taxa de renegociação de uma venda
     */
    const ID_PRODUTO_TAXA       = "1000000";
    
    /**
     * Identificador do produto que representa a valor que restava para quitar uma venda 
     * que foi renegociada
     */
    const ID_PRODUTO_RESTANTE   = "1000001";
    
    /**
     * Seleciona uma lista de produtos com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de produtos do tipo Produto.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $produtos = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $produtos[] = new Produto(
                isset($row->{ self::ID })? $row->{ self::ID } : 0,
                isset($row->{ self::CODIGO })? $row->{ self::CODIGO } : "",
                isset($row->{ self::DESCRICAO })? ($row->{ self::DESCRICAO }) : "",
                isset($row->{ self::PRECO_COMPRA })? $row->{ self::PRECO_COMPRA } : 0,
                isset($row->{ self::PRECO_VENDA })? $row->{ self::PRECO_VENDA } : 0,
                isset($row->{ self::PRECO_VENDA_MIN })? $row->{ self::PRECO_VENDA_MIN } : 0,
                isset($row->{ self::TIPO })? $row->{ self::TIPO } : 0,
                isset($row->{ self::MARCA })? $row->{ self::MARCA } : 0,
                isset($row->{ self::CATEGORIA })? $row->{ self::CATEGORIA } : self::CAT_PRODUTO
            );
        }
        return $produtos;
    }
    
    /**
     * Seleciona uma lista de produtos com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (tipo, marca).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de produtos do tipo Produto.
     */
    public function superSelect($condition = null, $limit = null){
        
        $fields = array( self::TABLE.".".self::ID, self::CODIGO, self::DESCRICAO,
                         self::PRECO_COMPRA, self::PRECO_VENDA, self::PRECO_VENDA_MIN,
                         TipoProdutoModel::NOME, MarcaModel::NOME, self::CATEGORIA);
        $fields_joined = implode(",", $fields);
        
        $this_condition = self::TIPO." = ".TipoProdutoModel::TABLE.".".TipoProdutoModel::ID;
        $this_condition .= " AND ".self::TABLE.".".self::MARCA." = ".MarcaModel::TABLE.".".MarcaModel::ID;
        
        if(is_null($condition)) $condition = $this_condition;
        else $condition .= " AND ".$this_condition;
        
        $tables = implode(",", array(self::TABLE, TipoProdutoModel::TABLE, MarcaModel::TABLE));
        
        $res = parent::select($tables, $fields_joined, $condition, $limit);
        $ana = $this->getAnnalisses();
        
        $produtos = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $produtos[] = new Produto(
                $row->{ self::ID },
                $row->{ self::CODIGO },
                $row->{ self::DESCRICAO },
                $row->{ self::PRECO_COMPRA },
                $row->{ self::PRECO_VENDA },
                $row->{ self::PRECO_VENDA_MIN },
                $row->{ TipoProdutoModel::NOME },
                $row->{ MarcaModel::NOME },
                $row->{ self::CATEGORIA }
            );
        }
        return $produtos;
    }
    
    
    /**
     * Insere um produto na base de dados.
     * @param Produto $produto produto que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Produto $produto){
        $values = get_object_vars($produto);
        unset($values["id"]);
        $fields = implode(",", array( self::CODIGO, self::DESCRICAO, self::PRECO_COMPRA,
                                      self::PRECO_VENDA, self::PRECO_VENDA_MIN,
                                      self::TIPO, self::MARCA, self::CATEGORIA ));
        return parent::insert(self::TABLE, $fields, Database::turnInValues($values));
    }
    
    /**
     * Atualiza <i>produto</i> de acordo com o seu identificador.
     * @param Produto $produto produto que vai ser aualizado
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Produto $produto){
        $dic = array(
            self::CODIGO => $produto->codigo,
            self::DESCRICAO => $produto->descricao,
            self::PRECO_COMPRA => $produto->precoCompra,
            self::PRECO_VENDA => $produto->precoVenda,
            self::PRECO_VENDA_MIN => $produto->precoVendaMin,
            self::TIPO => $produto->tipo,
            self::MARCA => $produto->marca,
            self::CATEGORIA => $produto->categoria
        );
        $condition = self::ID." = ".$produto->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }

    /**
     * Atualização simples de um quantidade específica de campos.
     * @param mixed $fields campos a serem atualizados
     * @param mixed $values valores correspondentes
     * @param string $condition condição para especificar que linhas serão atualizadas
     * @return boolean true em caso de sucesso, ou false em caso de falha
     */
    public function simpleUpdate($fields, $values, $condition){
        return parent::update(self::TABLE, $fields, $values, $condition);
    }
    
    /**
     * Realiza a remoção de um produto.
     * @param int $id_produto identificador do produto (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_produto){
        $condition = self::ID." = $id_produto";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Obtém um mapa onde o índice é um produto e o valor é sua categoria.
     * @param mixed $ids identificadores dos produtos
     * @return array mapa de categoria
     */
    public function catMap($ids){
        if(is_array($ids)) $ids = implode (',', $ids);
        $condition = self::ID.' IN ('.$ids.')';
        $res = parent::select(self::TABLE, self::ID.','.self::CATEGORIA, $condition);
        list($map, $anna) = array(array(), $this->getAnnalisses());
        while(($row = $anna->fetchObject($res)) != FALSE){
            $map[$row->{self::ID}] = $row->{ self::CATEGORIA };
        }
        return $map;
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) return "Não pode haver produto duplicado";
        return parent::handleError();
    }
} 
?>
