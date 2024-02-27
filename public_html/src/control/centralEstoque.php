<?php
include_once ENTITIES."central-estoque.php";
include_once MODELS."central-estoque.php";
include_once ENTITIES."central-estoque-produto.php";
include_once MODELS."central-estoque-produto.php";
include_once ENTITIES."loja-produto.php";
include_once MODELS."loja-produto.php";

class CentralEstoqueController {
    
    private $modelEstoque;
    private $modelEstoqueProduto;
    private $modelLojaProduto;
    
    public function __construct() {
        $this->modelEstoque         = new CentralEstoqueModel();
        $this->modelEstoqueProduto  = new CentralEstoqueProdutoModel();
        $this->modelLojaProduto     = new LojaProdutoModel();
    }
    
    /**
     * ===============================================
     * Operações de Central Estoque
     * ===============================================
     */
    public function addCentralEstoque(CentralEstoque $centralEstoque = null) {
        if($centralEstoque == null){
            $centralEstoque = new CentralEstoque();
            $this->linkCentralEstoque($centralEstoque);
            $return = false;
        }
        $config = Config::getInstance();
        $hasId  = $config->filter('for-update-id');
        $res    = false; 
        $this->modelEstoque->begin();
        if(!$hasId){
            $res = $this->modelEstoque->insert($centralEstoque);
            if($res){
                $arrProdutos    = $this->getProdutosFromRequest($centralEstoque);
                $sumValores     = 0;
                foreach($arrProdutos as $p) { $sumValores += $p->valor * $p->quantidadeEntrada; }
                $condition = CentralEstoqueModel::ID.'='.$centralEstoque->id;
                $res =  $this->modelEstoque->simpleUpdate(CentralEstoqueModel::VALOR, $sumValores, $condition) &&
                        $this->modelEstoqueProduto->inserts($arrProdutos);
            }
        } else {
            $centralEstoque->id = $hasId;
            $hasProblem         = $this->getCentralEstoque($hasId)->status == CentralEstoqueModel::STATUS_PROBLEMAS; 
            $res = $this->modelEstoque->update($centralEstoque);
            if($res){
                $arrProdutos    = $this->getProdutosFromRequest($centralEstoque);
                $res_sync       = $this->syncProdutosForUpdate($centralEstoque, $arrProdutos, $hasProblem);
                if(!$res_sync[0]){
                    $res = false;
                    $config->failInFunction($res_sync[1]);
                } else {
                    $sumValores     = 0;
                    foreach($arrProdutos as $p) { $sumValores += $p->valor * $p->quantidadeEntrada; }
                    $condition = CentralEstoqueModel::ID.'='.$centralEstoque->id;
                    $res = $this->modelEstoqueProduto->delete(null, $centralEstoque->id) &&
                           $this->modelEstoque->simpleUpdate(CentralEstoqueModel::VALOR, $sumValores, $condition) &&
                           $this->modelEstoqueProduto->inserts($arrProdutos);
                }
            }
        }
        //Atualizando os preços de compra dos produtos
        $produto_controller = new ProdutoController();
        foreach($arrProdutos as $pr){
            $produto = $produto_controller->getProduto($pr->produto);
            if($produto->precoCompra != $pr->valor){
                $produto->precoCompra = $pr->valor;
                if(!$produto_controller->updatePrecoCompra($produto)){
                    $config->failInFunction('Erro ao atualizar preço de compra de um produto');
                    $config->redirect('index.php?op=cad-central');
                }
            }
        }
        if($res && !$hasId) {
            $res_merge  = $this->mergeProdutosInEstoques($centralEstoque, $arrProdutos);
            $res        = $res_merge[0];
        }
        if(!isset($return)) { return $res; }
        if($res){
            $this->modelEstoque->commit();
            $config->successInFunction();
        } else {
            $this->modelEstoque->rollBack();
            if(!isset($res_merge)) {
                $config->failInFunction($this->modelEstoque->handleError());
            } else {
                $config->failInFunction($res_merge[1]);
            }
        }
        $config->redirect('index.php?op=cad-central');
    }
    
    private function linkCentralEstoque(CentralEstoque &$centralEstoque) {
        $config = Config::getInstance();
        $centralEstoque->data               = $config->filter("data");
        $centralEstoque->valor              = 0.00;
        $centralEstoque->usuarioEstoque     = $_SESSION[SESSION_ID_FUNC];
        $centralEstoque->usuarioConfirmacaoEntrada  = null; //Ver isso daqui
        $centralEstoque->usuarioConfirmacaoSaida    = null; //Ver isso daqui
        $centralEstoque->lojaOrigem         = $config->filter('origem');
        $centralEstoque->observacao         = mb_strtoupper($config->filter('observacao'), 'UTF-8');
        if(empty($centralEstoque->lojaOrigem) || $centralEstoque->lojaOrigem == "null"){
            $centralEstoque->lojaOrigem = null;
            $centralEstoque->status     = CentralEstoqueModel::STATUS_OK;
            $centralEstoque->lojaDestino= '1'; //Loja da Central 
        } else {
            $centralEstoque->status         = CentralEstoqueModel::STATUS_PENDENTE;
            $centralEstoque->lojaDestino        = $config->filter('destino');
        }
    }
    
    public static  function getTextualStatus($status) {
        switch ($status) {
            case CentralEstoqueModel::STATUS_PENDENTE:
                return "PENDENTE";
            case CentralEstoqueModel::STATUS_OK:
                return "OK";
            case CentralEstoqueModel::STATUS_PROBLEMAS:
                return "PROBLEMAS";
            default:
                return "INVÁLIDO";
        }
    }
    
    private function getProdutosFromRequest(CentralEstoque $centralEstoque){
        include_once CONTROLLERS.'produto.php';
        $controllerProduto = new ProdutoController();
        $config = Config::getInstance();
        $arr_codigos = $config->filter('p-codigo');
        $arr_qtds    = $config->filter('p-quantidade');
        $arr_valores = $config->filter('p-valor');
        $produtos    = array(); 
        for($i = 0, $l = count($arr_codigos); $i < $l; $i++){
            if(!isset($arr_valores[$i]) || empty($arr_valores[$i])){
                $arr_valores[$i] = FALSE;
            }
            list($codigo, $qtd, $valor_req) = array(
                (int) $arr_codigos[$i], 
                (int) $arr_qtds[$i], 
                $arr_valores[$i] ? (float) str_replace(",", ".", $arr_valores[$i]) : null
            );
            if(empty($codigo)){continue;}
            $produto = $controllerProduto->getProdutoByCodigo($codigo);
            if(empty($produto->id)){continue;}
            list($qtd_saida, $valor) =  empty($centralEstoque->lojaOrigem) ? 
                                        array($qtd, $valor_req) : 
                                        array(0,$produto->precoCompra);
            $produtoEstoque = new CentralEstoqueProduto(
                $produto->id, $centralEstoque->id, $qtd, $qtd_saida, $valor
            );
            $produtos[]     = $produtoEstoque;
        }
        return $produtos;
    }
    
    public function getAllCentraisEstoque($by_loja = false, $foreign_values = false){
        $condition = null;
        if($by_loja) {
            $condition  = CentralEstoqueModel::LOJA_DESTINO." = ".$by_loja;
            $condition .= " OR " . CentralEstoqueModel::LOJA_ORIGEM ." = ".$by_loja;
        }
        if($foreign_values) return $this->modelEstoque->superSulect($condition);
        return $this->modelEstoque->select('*', $condition);
    }
    
    public function validaOperacaoCentral() {
        $config     = Config::getInstance(); 
        $id_central = $config->filter('central');
        $central    = $this->getCentralEstoque($id_central);
        if(empty($central->id)){
            $config->failInFunction('Operação de central inválida');
            $config->redirect('index.php?op=cad-central');
        }
        $central->usuarioConfirmacaoSaida = $_SESSION[SESSION_ID_FUNC];
        $qtds_entrada       = $config->filter('qtds_chegada'); //Produtos marcardos como OK
        $produtos_c         = $this->getProdutosOfCentralEstoque($id_central); //Todos os produtos da operação
        $this->modelEstoque->begin();
        $central->status    = CentralEstoqueModel::STATUS_OK; //Atribuindo OK, caso alguma inconsistencia ocorra, um problema será atribuído
        for($i = 0, $l = count($produtos_c); $i < $l; $i++) {
            list($pc, $qtd) = array($produtos_c[$i], $qtds_entrada[$i]);
            if($pc->quantidadeEntrada != $qtd){
                $central->status = CentralEstoqueModel::STATUS_PROBLEMAS; //Relatando problemas à Operação da Central
            }
            $pc->quantidadeSaida = $qtd;
            if(!$this->mergeProdutoInEstoque($pc->produto, $central->lojaDestino, $qtd, '+')) 
            {
                //Tratamento básico de falha
                $this->modelEstoque->rollBack();
                $config->failInFunction('Falha operar entre os estoques de origem e destino. Ver níveis de estoque.');
                $config->redirect('index.php?op=cad-central');
            }
            if(!$this->modelEstoqueProduto->update($pc)) {
                //Tentativa de atualizar o status do produto
                $this->modelEstoque->rollBack();
                $config->failInFunction('Falha atualizar status de um produto na central.');
                $config->redirect('index.php?op=cad-central');
            }
        }
        $condition = CentralEstoqueModel::ID."=".$central->id;
        if(!$this->modelEstoque->simpleUpdate(CentralEstoqueModel::STATUS, $central->status, $condition)) {
            //Tentativa de atualizar o status da central
            $this->modelEstoque->rollBack();
            $config->failInFunction('Falha atualizar status da entrada da central.');
            $config->redirect('index.php?op=cad-central');
        }
        if($central->status == CentralEstoqueModel::STATUS_PROBLEMAS){
            try {
                $this->enviarEmailSobreProblema($central->id);
            } catch ( Exception $e) {
                //Do nothinng...
            }
        }
        $this->modelEstoque->commit(); //Suecesso
        $config->successInFunction();
        $config->redirect('index.php?op=cad-central');
    }
    
    /**
     * @return CentralEstoque central estoque correspondente ou uma CentralEstoque vazia
     */
    public function getCentralEstoque($id_central, $foreign_value = false){
        $condition = CentralEstoqueModel::TABLE.".".CentralEstoqueModel::ID."=".$id_central;
        if($foreign_value){
            $res = $this->modelEstoque->superSulect($condition);
        } else {
            $res = $this->modelEstoque->select("*", $condition);
        }
        if(empty($res)) return new CentralEstoque();
        return $res[0];
    }
    
    public function removeCentralEstoque($id_central = null, $return = false) {
        $config     = Config::getInstance();
        $id_central = $config->filter('central');
        if(empty($id_central)){
            $config->failInFunction('Operação de Central Inválida');
            $config->redirect('index.php?op=cad-central');
        }
        $central    = $this->getCentralEstoque($id_central);
        $this->modelEstoque->begin();
        if($central->status == CentralEstoqueModel::STATUS_PENDENTE){
            if(!$this->restoreEstoqueOfCentral($central)){
                $this->modelEstoque->rollBack();
                if($return) {
                    $config->failInFunction('Falha ao ajustar estoque da origem');
                    $config->redirect('index.php?op=cad-central');
                }
            }
        } else {
            $this->modelEstoque->rollBack();
            if($return) {
                $config->failInFunction('Status inválido para remoção. Somente Pendentes.');            
                $config->redirect('index.php?op=cad-central');
            }
        }
        $res        = $this->modelEstoque->delete($id_central);
        if($res) { 
            $this->modelEstoque->commit();
        } else {
            $this->modelEstoque->rollBack();
        }
        if($return) { return $res; }
        if($res){
            $config->successInFunction();         
        } else {
            $config->failInFunction($this->modelEstoque->handleError());
        }
        $config->redirect('index.php?op=cad-central');
    }
    
    public function restoreEstoqueOfCentral(CentralEstoque $central){
        $produtos = $this->getProdutosOfCentralEstoque($central->id);
        foreach($produtos as $p){
            if(!$this->mergeProdutoInEstoque($p->produto, $central->lojaOrigem, $p->quantidadeEntrada, '+')){
                return false;
            }
        }
        return true;
    }
    
    public function enviarEmailSobreProblema($operacao){
        $operacao   = $this->getCentralEstoque($operacao, TRUE);
        $origem     = $operacao->lojaOrigem == null ? "Fornecedor" : $operacao->lojaOrigem;
        $destino    = $operacao->lojaDestino;
        $data       = Config::getInstance()->maskData($operacao->data);
        $headers    = array();
        $headers[]  = "MIME-Version: 1.0";
        $headers[]  = "Content-type: text/html; charset=urf-8";
        $headers[]  = "From: OpticaCapitalSys <opticacapitalsys@gmail.com>";
        $headers[]  = "X-Mailer: PHP/" . phpversion();
        $header     = implode("\r\n", $headers);
		$message	= "<table>";
		$message   .= "<tr><td><b>ORIGEM</b></td><td><b>DESTINO</b></td><td><b>DATA</b></td></tr>";
		$message   .= "<tr><td>$origem</td><td>$destino</td><td>$data</td></tr>";		
		$message   .= "</table>";
		$subject    = "INCONSISTENCIA EM OPERAÇÃO DE ESTOQUE";
        return mail("opticacapitalsys@gmail.com", $subject, $message, $header);
    }
    
    /**
     * ===============================================
     * Operações de Central Estoque e Produtos
     * ===============================================
     */
    public function getProdutosOfCentralEstoque($id_central){
        $condition = CentralEstoqueProdutoModel::CENTRAL." = ".$id_central;
        return $this->modelEstoqueProduto->select("*", $condition);
    }
    
    /**
     * Retorna um array com dois elementos: <br/> 
     * - um boolean que indica sucesso (true) ou falha (false) <br/>
     * - e a mensagem correspondente.
     */
    public function syncProdutosForUpdate(CentralEstoque $central, &$arrProdutos, $hasProblem = false){
        $actuals = $this->getProdutosOfCentralEstoque($central->id);
        foreach($actuals as $actual){
            foreach ($arrProdutos as &$new){
                if($actual->produto == $new->produto){
                    $diff = $actual->quantidadeEntrada - $new->quantidadeEntrada;
                    $new->quantidadeSaida = $actual->quantidadeSaida;
                    if($diff != 0 && !$this->mergeProdutoInEstoque($actual->produto, $central->lojaOrigem, $diff)){
                        return array(false, 'Erro ao sincronizar estoque da loja de origem. Ver níveis.');
                    }
                }
            }
        }
        if($hasProblem) {
            $central->status = CentralEstoqueModel::STATUS_OK;
            $condition = CentralEstoqueModel::ID."=".$central->id;
            if(!$this->modelEstoque->simpleUpdate(CentralEstoqueModel::STATUS, $central->status, $condition)) {
                return array(false, 'Erro ao atualizar status da entrada na central.');
            }
        }
        return array(true, true);
    }
    
    public function getMovimentacoes($origem, $destino, $dli = '', $dls = '', $foreign_values = false){
        $conditions = array();
        if(!empty($origem)) {
            if($origem == "null") {
                $conditions[] = CentralEstoqueModel::LOJA_ORIGEM . " IS NULL";
            } else {
                $conditions[] = CentralEstoqueModel::LOJA_ORIGEM . "=" . $origem;        
            }
        }
        if(!empty($destino)) {
            $conditions[] = CentralEstoqueModel::LOJA_DESTINO . "=" . $destino;
        }
        if(!empty($dli)){
            $conditions[] =  CentralEstoqueModel::DATA . " >= '" . $dli . "'";
        }
        if(!empty($dls)){
            $conditions[] =  CentralEstoqueModel::DATA . " <= '" . $dls . "'";
        }
        $conditions[] = ' 1=1 ORDER BY '.CentralEstoqueModel::DATA;
        if($foreign_values) return $this->modelEstoque->superSulect (implode(' AND ', $conditions));
        return $this->modelEstoque->select('*', implode(' AND ', $conditions));
    }


    /**
     * ===============================================
     * Operações de Loja Produto
     * ===============================================
     */
    /**
     * @return LojaProduto relacionamento produto/loja em estoque
     */
    public function getEstoqueOfProduto($id_loja, $id_produto){
        $condition  = LojaProdutoModel::LOJA."=".$id_loja;
        $condition .= " AND " . LojaProdutoModel::PRODUTO."=".$id_produto;
        $res        = $this->modelLojaProduto->select('*', $condition);
        if(empty($res)) return new LojaProduto();
        return $res[0];
    }
    
    public function getEstoqueInLoja($id_loja){
        $condition  = LojaProdutoModel::LOJA."=".$id_loja;
        return $this->modelLojaProduto->select('*', $condition);
    }
    
    public function getEachProdutoInEstoque($lojas){
        $produtos   = $this->modelLojaProduto->distinctProdutos();
        $res        = array();
        include_once CONTROLLERS.'produto.php';
        $produto_controller = new ProdutoController();
        foreach($produtos as $produto_id){
            $produto = $produto_controller->getProduto($produto_id);
            foreach($lojas as $loja){
                $estoque = $this->getEstoqueOfProduto($loja, $produto_id);
                $produto->{'_'.$loja} = $estoque->quantidade;
            }
            $res[] = $produto;
        }
        return $res;
    }
    
    /**
     * Retorna um array com dois elementos: <br/> 
     * - um boolean que indica sucesso (true) ou falha (false) <br/>
     * - e a mensagem correspondente.
     */
    public function mergeProdutosInEstoques(CentralEstoque $entradaCentral, $arrProdutos){
        if(empty($entradaCentral->lojaOrigem)){
            //Operação a partir do fornecedor para uma loja
            foreach($arrProdutos as $p){
                if(!$this->mergeProdutoInEstoque($p->produto, $entradaCentral->lojaDestino, $p->quantidadeEntrada))
                    return array(false, 'Falha ao registrar produto em central');
            }
            return array(true, true);
        } else {
            //Operação a partir de uma loja para uma outra loja
            foreach($arrProdutos as $p){
                $estoque_produto = $this->getEstoqueOfProduto($entradaCentral->lojaOrigem, $p->produto);
                if(empty($estoque_produto->id)){
                     return array(false, 'Produto referenciado não está no estoque. Ver níveis.');
                }
                if($estoque_produto->quantidade < $p->quantidadeEntrada){
                    return array(false, 'Alguns produtos estão com estoque abaixo do que a operação exige. Ver níveis.');
                } else {
                    if($entradaCentral->lojaDestino == $entradaCentral->lojaOrigem) {continue;}
                    if(!$this->mergeProdutoInEstoque($p->produto, $entradaCentral->lojaOrigem, $p->quantidadeEntrada, '-'))
                        return array(false, 'Falha ao registrar saída da origem');
                }
            }
            return array(true, true);
        }
    }
    
    public function mergeProdutoInEstoque($id_produto, $id_loja, $qtd, $op = '+' /*'+' ou '-'*/){
        $_produto = $this->getEstoqueOfProduto($id_loja, $id_produto);
        if(empty($_produto->id)) {
            $_produto->loja         = $id_loja;
            $_produto->produto      = $id_produto;
            $_produto->quantidade   = $qtd; 
            return $this->modelLojaProduto->insert($_produto);
        } else {
            if($op == '-') {
                if($_produto->quantidade < $qtd){
                    return false;
                }
                $_produto->quantidade -= $qtd;
            }
            else $_produto->quantidade += $qtd;
            return $this->modelLojaProduto->update($_produto);
        }
    }
    
}
?>
