<?php
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."localidade.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."localidade.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de localidade.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class LocalidadeController{
    
    /**
     * @var LocalidadeModel instancia do modelo de localidade usado nesse controlador
     * @access private
     */
    private $modelLocalidade;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de localidade.
     * @return LocalidadeController instancia de um controlador de localidades
     */
    public function __construct() {
        $this->modelLocalidade = new LocalidadeModel();
    }
    
    /**
     * Este método adiciona ou atualiza uma localidade no modelo.
     * @param Localidade $localidade localidade a ser inserida ou atualizada. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>localidade</i>
     */
    public function addLocalidade(Localidade $localidade = null){
        if(is_null($localidade)) {
            $localidade = new Localidade();
            //Atribuindo dados da requisição
            $this->linkLocalidade($localidade);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if(empty($localidade->nome) || empty($localidade->cidade) ||
           empty($localidade->rota) || empty($localidade->ordem)){
            $config->failInFunction("Os campos Nome, Cidade, Rota e Sequência são necessários para inserção.");            
            $config->redirect("?op=cad-loca");
        }
        $res = false; $res_pos = false;
        $updateId = $config->filter("for-update-id");
        $this->modelLocalidade->begin(); //Iniciando transação
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($updateId)) {
            //Inserção
            //Ajustando as posições
            if($this->adjustsLocalidadesPositionsInInsert($localidade)){
                $res = $this->modelLocalidade->insert($localidade);
            } else {
                $this->modelLocalidade->rollBack();//Rollback das mudanças
                $config->failInFunction("Não foi possível atualizar as posições das localidades na rota");
                $res_pos = true; 
            }
        } else {
            //Atualização
            $localidade->id = $updateId;
            //Ajustando as posições
            if($this->adjustsLocalidadesPositionsInUpdate($localidade)){
                $res = $this->modelLocalidade->update($localidade);
            } else {
                $this->modelLocalidade->rollBack();//Rollback das mudanças
                $config->failInFunction("Não foi possível atualizar as posições das localidades na rota");
                $res_pos = true;
            }
        }    
                
        if($res){
            $this->modelLocalidade->commit();//Commitando as mudanças
            $config->successInFunction();
        } else if(!$res_pos) {
            $this->modelLocalidade->rollBack();//Rollback das mudanças
            $config->failInFunction($this->modelLocalidade->handleError());
        } 
        
        $config->redirect("index.php?op=cad-loca");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à localidade 
     * com um objeto passado como referência
     * @param Localidade $localidade uma referência de uma localidade que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkLocalidade(Localidade &$localidade){
        $config = Config::getInstance();
        $localidade->nome = mb_strtoupper($config->filter("nome"), 'UTF-8');
        $localidade->cidade = $config->filter("cidade");
        $localidade->rota = $config->filter("rota");
        $localidade->ordem = $config->filter("ordem");
    }
    
    /**
     * Remove uma localidade de uma base de dados, informando o identificador na requisição.
     */
    public function removerLocalidade(){
        $config = Config::getInstance();
        $localidade = $config->filter("loca");
        
        $this->modelLocalidade->begin();
        
        //Ajustando as posições
        if(!$this->adjustsLocalidadesPositionsInDelete($this->getLocalidade($localidade))){
            $this->modelLocalidade->rollBack();
            $config->failInFunction("Falha ao atualizar posições das localidades");
            $config->redirect("index.php?op=cad-loca");
        }
        
        $condition = LocalidadeModel::ID. " = ".$localidade;
        $res = $this->modelLocalidade->delete($condition);
        if($res){
            $this->modelLocalidade->commit();
            $config->successInFunction();
        } else {
            $this->modelLocalidade->rollBack();
            $config->failInFunction($this->modelLocalidade->handleError());
        }
        $config->redirect("index.php?op=cad-loca");
    }
    
    /**
     * Obtém uma localidade em específico.
     * @param int $localidade identificador da localidade.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Localidade localidade específico ou uma localidade vazio em caso de inexistência.
     */
    public function getLocalidade($localidade, $foreign_values = false){
        $condition = LocalidadeModel::TABLE.".".LocalidadeModel::ID." = ".$localidade;
        if(!$foreign_values) $res = $this->modelLocalidade->select("*", $condition);
        else $res = $this->modelLocalidade->superSelect ($condition);
        if(!empty($res))return $res[0];
        return new Localidade();
    }
    
    /**
     * Obtém uma lista das localidades do sistema.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indentificador da loja, esse parametro, se for um inteiro válido, 
     * filtra as vendas por loja.
     * @return array lista de localidades do tipo Localidade
     */
    public function getAllLocalidades($foreign_value = false, $byLoja = false){
        $condition = null;
        if($byLoja){
            $condition = RegiaoModel::LOJA." = $byLoja";
        }
        if($foreign_value){
            return $this->modelLocalidade->superSelect($condition);
        }
        $fields = array(self::NOME, self::CIDADE, self::ROTA, self::SEQ_ROTA, RegiaoModel::LOJA);
        return $this->modelLocalidade->select($fields, $condition);
    }

    /**
     * Obtém uma lista de localidades filtrados por cidade.
     * @param int $id_cidade indetificador da cidade
     * @return array lista de localidade do tipo Localidade
     */
    public function getLocalidadesByCidade($id_cidade){
        $condition = LocalidadeModel::CIDADE." = $id_cidade";
        return $this->modelLocalidade->select("*", $condition);
    }
    
    /**
     * Obtém o maior número da sequência das rotas entre as localidade de uma rota.
     * @param int $rota identificador da rota
     * @return int valor máximo de ordem na rota
     */
    public function getNumberOfPositionsByRota($rota){
        return $this->modelLocalidade->maxPositionInRota($rota);
    }
    
    /**
     * Obtém uma lista de localidades filtrados pela rota.
     * @param int $rota identificador da rota
     * @param bool $not_null indica se as localidade com sequencia nula serão consideradas
     * @return array uma lista de localidades do tipo Localidade
     */
    public function getLocalidadesByRota($rota, $not_null = true){
        $condition =  LocalidadeModel::ROTA." = $rota ";
        if($not_null){
            $condition .= ' AND '.LocalidadeModel::SEQ_ROTA.' IS NOT NULL ';
            $condition .= ' ORDER BY '.LocalidadeModel::SEQ_ROTA;
        } else {
            $condition .= ' AND '.LocalidadeModel::SEQ_ROTA.' IS NULL ';
        }
        return $this->modelLocalidade->select("*", $condition);
    }
    
    /**
     * Atualiza a posição de uma localidade na rota.
     * @param int $localidade_id identificador da localidade
     * @param int $valor nova sequência do localidade na rota
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function updateOrdemLocalidade($localidade_id, $valor){
        return $this->modelLocalidade->simpleUpdate(LocalidadeModel::SEQ_ROTA, 
                                                    $valor,
                                                    LocalidadeModel::ID.' = '.$localidade_id);
    }
    
    /**
     * Verifica se a posição <i>ordem</i> está livre na <i>rota</i>.
     * @param int $rota identificador da rota
     * @param int $ordem valor da posição.
     * @return bool true se não existir nenhuma localidade na rota ou false se 
     * alguma localidade estiver na posição
     */
    public function isEmptyOrdem($rota, $ordem){
        $res = $this->modelLocalidade->select( 
                    LocalidadeModel::ID , 
                    LocalidadeModel::SEQ_ROTA." = $ordem AND ".LocalidadeModel::ROTA." = $rota "
                );
        return empty($res);
    }
    
    /**
     * Aplica as mudanças de posição nas localidades.
     * @param array $ids identificadores das localidade que serão alteradas
     * @param string $op operação aplicada ordem de cada localidade, 
     * pode ser incremeto ($op = '+') ou decremento ($op = '-')
     * @return bool true se todas as posições foram alteradas com sucesso, ou false se alguma falha ocorrer
     */
    private function applyChangesOrdem($ids, $op){
        foreach ($ids as $id){
            if( !$this->modelLocalidade->simpleUpdate(
                LocalidadeModel::SEQ_ROTA, 
                LocalidadeModel::SEQ_ROTA." $op 1", 
                LocalidadeModel::ID.'= '.$id) ) return false;
        }
        return true;
    }
    
    /**
     * Ajusta as posições quando a localidade <i>new</i> for ser inserida.
     * @param Localidade $new localidade que vai ser inserida no sistema.
     * @return bool true se os ajustes ocorreram sucesso, o false se alguma falha ocorrer
     */
    private function adjustsLocalidadesPositionsInInsert(Localidade $new){
        //Se for a posição estiver desocupada, mudanças não precisam ser realizadas
        if($this->isEmptyOrdem($new->rota, $new->ordem)) return true;
        //Obtendo todas as localidades que estão acima e na posição de $new
        $condition = LocalidadeModel::ROTA." = ".$new->rota." AND ".LocalidadeModel::SEQ_ROTA." >= ".$new->ordem;
        $condition  .= " ORDER BY ".LocalidadeModel::SEQ_ROTA." DESC";
        $localidades = $this->modelLocalidade->select(array(LocalidadeModel::ID,
                                                            LocalidadeModel::SEQ_ROTA), $condition);
        //Obtendo somentes os identificadores
        $ids = array();
        foreach ($localidades as $localidade){
            $ids[] = $localidade->id;
        }
        //Aplicando as mudanças
        return $this->applyChangesOrdem($ids, '+');
        
    }
    
    /**
     * Ajusta as posições quando a localidade <i>new</i> for ser atualizada.
     * @param Localidade $new localidade que vai ser inserida no sistema.
     * @return bool true se os ajustes ocorreram sucesso, o false se alguma falha ocorrer
     */
    private function adjustsLocalidadesPositionsInUpdate(Localidade $new){
        //Obtendo a antiga localidade
        $old = $this->getLocalidade($new->id);
        //Se a rota for nova, uma operção de remoção na antiga rota e de inserção
        // na nova rota precisa ser realizada
        if((int)$new->rota != (int)$old->rota){
            return  $this->adjustsLocalidadesPositionsInDelete($old) &&
                    $this->adjustsLocalidadesPositionsInInsert($new);
        }
        //Se a ordem for a mesma, nenhuma alteração precisa ser realizada
        if((int)$new->ordem == (int)$old->ordem) return true;
        
        //Obtendo as localidades da faixa de alteração
        $condition = LocalidadeModel::ROTA." = ".$new->rota;
        if($new->ordem < $old->ordem){
            //Quando a nova posição é menor do que a antiga, todas localidades
            //menores do a antiga e maiores ou igual à nova são selecionadas.
            $condition .= " AND ".LocalidadeModel::SEQ_ROTA." < ".$old->ordem;
            $condition .= " AND ".LocalidadeModel::SEQ_ROTA." >= ".$new->ordem;
            $condition .= " ORDER BY ".LocalidadeModel::SEQ_ROTA." DESC ";
            //Atribuindo o peração de incremento
            $op = '+';
        } else if($new->ordem > $old->ordem) {
            //Quando a nova posição é maior do que a antiga, todas localidades
            //maiores do a antiga e menores ou igual à nova são selecionadas.
            $condition .= " AND ".LocalidadeModel::SEQ_ROTA." > ".$old->ordem;
            $condition .= " AND ".LocalidadeModel::SEQ_ROTA." <= ".$new->ordem;
            $condition .= " ORDER BY ".LocalidadeModel::SEQ_ROTA;
            //Atribuindo o operação de decremento
            $op = '-';
        }
        
        $localidades = $this->modelLocalidade->select(
                array(LocalidadeModel::ID, LocalidadeModel::SEQ_ROTA), $condition
        );
        //Obtendo somentes os identificadores
        $ids = array();
        foreach ($localidades as $localidade){
            $ids[] = $localidade->id;
        }
        //Liberando a antiga antiga posição da rota 
        $this->modelLocalidade->simpleUpdate(LocalidadeModel::SEQ_ROTA, '-1000', 
                                             LocalidadeModel::ID." = ".$old->id);
        //Aplicando as mudanças
        return $this->applyChangesOrdem($ids, $op);
        
    }
    
    /**
     * Ajusta as posições quando a localidade <i>new</i> for ser removida.
     * @param Localidade $new localidade que vai ser inserida no sistema.
     * @return bool true se os ajustes ocorreram sucesso, o false se alguma falha ocorrer
     */
    private function adjustsLocalidadesPositionsInDelete(Localidade $old){
        //Obtendo todas as localidades maiores ou iguais a posição da localidade
        $condition   = LocalidadeModel::ROTA." = ".$old->rota." AND ".  LocalidadeModel::SEQ_ROTA." > ".$old->ordem;
        $condition  .= " ORDER BY ".LocalidadeModel::SEQ_ROTA;
        $localidades = $this->modelLocalidade->select(array(LocalidadeModel::ID, 
                                                            LocalidadeModel::SEQ_ROTA), $condition);
        //Obtendo os identificadores das localidades
        $ids = array();
        foreach ($localidades as $localidade){
            $ids[] = $localidade->id;
        }
        //Liberando a posição
        $this->modelLocalidade->simpleUpdate(LocalidadeModel::SEQ_ROTA, "NULL", 
                                             LocalidadeModel::ID." = ".$old->id);
        //Aplicando mudanças
        return $this->applyChangesOrdem($ids, '-');
    }

    public function filterLocalidades($loja, $cobrador, $regiao, $rota) {

        $conditions = array();
        if (!empty($loja)) {
            $conditions[] = RegiaoModel::TABLE.'.'.RegiaoModel::LOJA.'='.$loja;
        }
        if (!empty($cobrador)) {
            $conditions[] = RegiaoModel::TABLE.'.'.RegiaoModel::COBRADOR . '=' . $cobrador;
        }
        if (!empty($regiao)) {
            $conditions[] = RegiaoModel::TABLE.'.'.RegiaoModel::ID . '=' . $regiao;
        }
        if (!empty($rota)) {
            $conditions[] = LocalidadeModel::TABLE.'.'.LocalidadeModel::ROTA . '=' . $rota;
        }

        return $this->modelLocalidade->superSelect(implode(" AND ", $conditions));
    }

}
?>
