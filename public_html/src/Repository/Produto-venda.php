<?php
namespace Sisoptica\Repository;
//Incluindo classes associadas a esse modelo
include_once MODELS."venda.php";
include_once MODELS."produto.php";

/**
 * Essa classe implementa o modelo da entidade ProdutoVendaModel.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ProdutoVendaModel extends Database {
    
    /**
     * Nome da tabela de produtos das vendas no banco de dados 
     */
    const TABLE     = "produto_venda";
    
    /**
     * Nome da coluna do identificador do produto associado a venda no banco de dados.
     */
    const ID        = "id";
    
    /**
     * Nome da coluna do identificador do produto no banco de dados.
     */
    const PRODUTO   = "id_produto";
    
    /**
     * Nome da coluna do identificador da venda no banco de dados.
     */
    const VENDA     = "id_venda";
    
    /**
     * Nome da coluna do valor que o produto foi vendido na venda no banco de dados.
     */
    const VALOR     = "valor_venda_produto";
    
    /**
     * Seleciona uma lista de produtos das vendas com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de produtos das vendas do tipo ProdutVenda.
     */
    public function select($fields = " * ", $condition = null, $count = false) {
        if(is_array($fields)) $fields = implode (",", $fields);
        
        $res = parent::select(self::TABLE, $fields, $condition);
        $anna = $this->getAnnalisses();
        $produtos = array();
        if(!$count){
            while(($row = $anna->fetchObject($res)) !== FALSE){
                $produtos[] = new ProdutoVenda(
                                isset($row->{self::ID}) ? $row->{self::ID} : 0,
                                isset($row->{self::PRODUTO}) ? $row->{self::PRODUTO} : 0,
                                isset($row->{self::VENDA}) ? $row->{self::VENDA} : 0,
                                isset($row->{self::VALOR}) ? $row->{self::VALOR} : 0        
                            );
            }
        }
        return $produtos;
    }
    
    /**
     * Insere um produto da venda na base de dados.
     * @param ProdutoVenda $prodVenda produto da venda (ProdutoVenda) que vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(ProdutoVenda $prodVenda) {
        $fields = implode(",", array(
            self::PRODUTO, self::VENDA, self::VALOR
        ));
        $vars = get_object_vars($prodVenda);
        unset($vars['id']);
        return parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
    }
    
    /**
     * Insere uma lista produtos da venda na base de dados.
     * @param array $prodVenda produtos da venda (array de ProdutoVenda) que vão ser inseridos
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function inserts($arrProdVenda){
        if(empty($arrProdVenda) || !is_array($arrProdVenda)) return false;
        $values = array();
        foreach ($arrProdVenda as $prodVenda){
            $vars = get_object_vars($prodVenda);
            unset($vars["id"]);
            $values[] = "(".Database::turnInValues($vars).")";
        }
        $fields = implode(",", array(
            self::PRODUTO, self::VENDA, self::VALOR
        ));
        return parent::insert(self::TABLE, $fields, $values);
    }
    
    /**
     * Obtém o valor de uma venda, ou seja, a soma dos valores do produtos
     * associados a <i>id_venda</i>
     * @param int $id_venda identificador da venda.
     * @return float soma dos valores dos produtos associados a venda em questão
     */
    public function valorVenda($id_venda){
        $field      = "SUM(".self::VALOR.") as valor";
        $condition  = self::VENDA." = $id_venda "; 
        $res        = parent::select(self::TABLE, $field, $condition);
        $anna       = $this->getAnnalisses();
        if(($row = $anna->fetchObject($res)) !== FALSE) {
            return $row->valor;
        }
        return 0;
    }
    
    /**
     * Realiza a remoção de um produto associado à venda (ProdutoVenda).
     * @param int $idProdVenda identificador da associação produto/venda (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($idProdVenda) {
        $condition = self::ID." = $idProdVenda";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Realiza a remoção de <b>todos</b> produtos associado à venda (ProdutoVenda).
     * @param int $id_venda identificador da venda (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function deleteOfVenda($id_venda){
        $condition = self::VENDA." = $id_venda";
        return parent::delete(self::TABLE, $condition);
    }
    
}
?>
