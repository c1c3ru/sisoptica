<?php
namespace Sisoptica\Repository;
//Incluindo classes associadas a esse modelo
include_once MODELS."venda.php";
include_once MODELS."cliente.php";
include_once MODELS."localidade.php";

/**
 * Essa classe implementa o modelo da entidade Parcela.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ParcelaModel extends Database {

    /**
     * Nome da tabela de parcelas no banco de dados
     */
    const TABLE = "venda_parcela";

    /**
     * Nome da coluna do numero da parcela no banco de dados.
     */
    const NUMERO    = "numero_parcela";

    /**
     * Nome da coluna da data de vencimento da parcela no banco de dados.
     */
    const VALIDADE  = "data_parcela";

    /**
     * Nome da coluna da data de adiamento da cobrança de uma parcela no banco de dados
     */
    const REMARCACAO = "data_remarcacao";

    /**
     * Nome da coluna do valor da parcela no banco de dados.
     */
    const VALOR     = "valor_parcela";

    /**
     * Nome da coluna do status da parcela no banco de dados.
     */
    const STATUS    = "status_parcela";

    /**
     * Nome da coluna do identificador da venda da parcela no banco de dados.
     */
    const VENDA     = "id_venda";

    /**
     * Nome da coluna da flag que indica se a prestação de conta é por boleto no banco de dados.
     */
    const POR_BOLETO= "por_boleto";

    /**
     * Nome da coluna que indica se a parcela está cancelada ou não
     */
    const CANCELADA = "cancelada";

    /**
     * Quantidade de dias de tolerância na cobrança de parcelas atrasadas na modalidade boleto.
     */
    const DIAS_TOLERANCIA_BOLETO = '1';

    /**
     * Seleciona uma lista de parcelas com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de parcelas do tipo Parcela.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $anna = $this->getAnnalisses();
        $parcelas = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $parcelas[] = new Parcela(
                isset($row->{self::NUMERO})?$row->{self::NUMERO}:0,
                isset($row->{self::VALIDADE})?$row->{self::VALIDADE}:"",
                isset($row->{self::REMARCACAO})?$row->{self::REMARCACAO}:"",
                isset($row->{self::VALOR})?$row->{self::VALOR}:0,
                isset($row->{self::STATUS})?$row->{self::STATUS}: false,
                isset($row->{self::VENDA})?$row->{self::VENDA}: 0,
                isset($row->{self::POR_BOLETO})?$row->{self::POR_BOLETO} : true,
                isset($row->{self::CANCELADA})?$row->{self::CANCELADA} : false
            );
        }
        return $parcelas;
    }

    /**
     * Seleciona uma lista de identificadores de localidades de uma rota que possuem parcelas
     * de vendas atrasadas no periodo informado.
     * @param int $id_rota identificador da rota.
     * @param string $limit_up_date data limite superior da data da parcela
     * @param string $limit_down_date data limite inferior da data da parcela
     * @return array lista de identificadores de localidades com vendas com parcelas atrasadas
     */
    public function selectForRota($id_rota, $limit_up_date, $limit_down_date = ''){

        $field = "DISTINCT(".LocalidadeModel::TABLE.".".LocalidadeModel::ID.")";

        $joins[] = "LEFT JOIN ".VendaModel::TABLE." ON ".self::TABLE.".".self::VENDA." = ".VendaModel::TABLE.".".VendaModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".VendaModel::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;
        $joins[] = "LEFT JOIN ".LocalidadeModel::TABLE." ON ".ClienteModel::TABLE.".".ClienteModel::LOCALIDADE." = ".LocalidadeModel::TABLE.".".LocalidadeModel::ID;


        $condition  = LocalidadeModel::ROTA." = $id_rota AND ".self::STATUS." = FALSE AND ".self::CANCELADA." = FALSE ";

        $condition .= " AND ("; //Abrindo condição de datas

        $condition .= "( ".self::POR_BOLETO." = TRUE AND (
            (".self::REMARCACAO." IS NULL AND ( DATE_ADD(".self::VALIDADE.", INTERVAL ".self::DIAS_TOLERANCIA_BOLETO." DAY) <= '$limit_up_date' ".( !empty($limit_down_date)? "AND DATE_ADD(".self::VALIDADE.", INTERVAL ".self::DIAS_TOLERANCIA_BOLETO." DAY) >= '".$limit_down_date."'" : " ")." )) OR
            (".self::REMARCACAO." IS NOT NULL AND ( ".self::REMARCACAO." <= '$limit_up_date' ".( !empty($limit_down_date)? "AND ".self::REMARCACAO." >= '".$limit_down_date."'" : " ")."  ) )
        ) )
        OR
        (".self::POR_BOLETO." = FALSE AND (
            (".self::REMARCACAO." IS NULL AND ( ".self::VALIDADE." <= '$limit_up_date' ".( !empty($limit_down_date)? "AND ".self::VALIDADE." >= '".$limit_down_date."'" : " ")." )) OR
            (".self::REMARCACAO." IS NOT NULL AND ( ".self::REMARCACAO." <= '$limit_up_date' ".( !empty($limit_down_date)? "AND ".self::REMARCACAO." >= '".$limit_down_date."'" : " ")."  ) )
        ) )";

        $condition .= " )"; //Fechando condição de dadtas


        $condition .= " AND ".VendaModel::TABLE.".".VendaModel::STATUS." = ".VendaModel::STATUS_ATIVA;
        $condition .= " AND ".VendaModel::TABLE.".".VendaModel::DT_ENTREGA." IS NOT NULL ";
        $condition .= " ORDER BY ".LocalidadeModel::SEQ_ROTA;

        $res = parent::select(self::TABLE." ".implode(" ", $joins), $field, $condition);
        $ana = $this->getAnnalisses();
        $localidades = array();
        while (($row = $ana->fetchObject($res)) !== FALSE){
            $localidades[] = $row->id;
        }

        return $localidades;

    }

    /**
     * Seleciona uma lista de identificadores de vendas de uma localidade que possuem parcelas
     * de vendas atrasadas no periodo informado.
     * @param int $localidade_id identificador da localidade.
     * @param string $limit_up_date data limite superior da data da parcela
     * @param string $limit_down_date data limite inferior da data da parcela
     * @return array lista de identificadores de vendas com parcelas atrasadas
     */
    public function selectForLocalidade($localidade_id, $limit_up_date, $limit_down_date = ''){

        $field = "DISTINCT(".self::VENDA.")";

        $joins[] = "LEFT JOIN ".VendaModel::TABLE." ON ".self::TABLE.".".self::VENDA." = ".VendaModel::TABLE.".".VendaModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".VendaModel::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;

        $condition  = ClienteModel::LOCALIDADE." = $localidade_id AND ".self::STATUS." = FALSE AND ".self::CANCELADA." = FALSE ";
        $condition .= " AND ".VendaModel::TABLE.".".VendaModel::STATUS." = ".VendaModel::STATUS_ATIVA;
        $condition .= " AND ".VendaModel::TABLE.".".VendaModel::DT_ENTREGA." IS NOT NULL ";

        $condition .= " AND ("; //Abrindo condição de datas

        $condition .= "( ".self::POR_BOLETO." = TRUE AND (
            (".self::REMARCACAO." IS NULL AND ( DATE_ADD(".self::VALIDADE.", INTERVAL ".self::DIAS_TOLERANCIA_BOLETO." DAY) <= '$limit_up_date' ".( !empty($limit_down_date)? "AND DATE_ADD(".self::VALIDADE.", INTERVAL ".self::DIAS_TOLERANCIA_BOLETO." DAY) >= '".$limit_down_date."'" : " ")." )) OR
            (".self::REMARCACAO." IS NOT NULL AND ( ".self::REMARCACAO." <= '$limit_up_date' ".( !empty($limit_down_date)? "AND ".self::REMARCACAO." >= '".$limit_down_date."'" : " ")."  ) )
        ) )
        OR
        (".self::POR_BOLETO." = FALSE AND (
            (".self::REMARCACAO." IS NULL AND ( ".self::VALIDADE." <= '$limit_up_date' ".( !empty($limit_down_date)? "AND ".self::VALIDADE." >= '".$limit_down_date."'" : " ")." )) OR
            (".self::REMARCACAO." IS NOT NULL AND ( ".self::REMARCACAO." <= '$limit_up_date' ".( !empty($limit_down_date)? "AND ".self::REMARCACAO." >= '".$limit_down_date."'" : " ")."  ) )
        ) )";

        $condition .= " )"; //Fechando condição de dadtas

        $condition .= " ORDER BY ".ClienteModel::TABLE.".".ClienteModel::ENDERECO;

        $res = parent::select(self::TABLE." ".implode(" ", $joins), $field, $condition);
        $ana = $this->getAnnalisses();
        $localidades = array();
        while (($row = $ana->fetchObject($res)) !== FALSE){
            $localidades[] = $row->{self::VENDA};
        }

        return $localidades;
    }

    /**
     * Seleciona uma lista de parcelas de parcelas atrasadas de uma regiao
     * @param int $id_rota identificador da rota.
     * @param string $limit_date data limite da data da venda
     * @return array lista de parcelas atrasadas
     */
    public function selectForRegiao($regiao, $limit_date){

        $field = implode(",", array( self::VENDA, self::NUMERO, self::VALOR, self::POR_BOLETO, self::CANCELADA));

        $joins[] = "LEFT JOIN ".VendaModel::TABLE." ON ".self::TABLE.".".self::VENDA." = ".VendaModel::TABLE.".".VendaModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".VendaModel::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;
        $joins[] = "LEFT JOIN ".LocalidadeModel::TABLE." ON ".ClienteModel::LOCALIDADE." = ".LocalidadeModel::TABLE.".".LocalidadeModel::ID;
        $joins[] = "LEFT JOIN ".RotaModel::TABLE." ON ".LocalidadeModel::ROTA." = ".RotaModel::TABLE.".".RotaModel::ID;

        $condition  = RotaModel::REGIAO." = $regiao ";
        $condition .= " AND ".self::STATUS." = FALSE ";
        $condition .= " AND ".self::CANCELADA." = FALSE ";
        $condition .= " AND ( ".self::POR_BOLETO." = TRUE AND (
            (".self::REMARCACAO." IS NULL AND ( DATE_ADD(".self::VALIDADE.", INTERVAL ".self::DIAS_TOLERANCIA_BOLETO." DAY) <= '$limit_date') OR
            (".self::REMARCACAO." IS NOT NULL AND ( ".self::REMARCACAO." <= '$limit_date' ) )
        ) )
        OR
        (".self::POR_BOLETO." = FALSE AND (
            (".self::REMARCACAO." IS NULL AND ( ".self::VALIDADE." <= '$limit_date' )) OR
            (".self::REMARCACAO." IS NOT NULL AND ( ".self::REMARCACAO." <= '$limit_date'  ) )
        ) ) )";

        $res = parent::select(self::TABLE." ".implode(" ", $joins), $field, $condition);
        $ana = $this->getAnnalisses();
        $parcelas = array();
        while (($row = $ana->fetchObject($res)) !== FALSE){
            $parcela            = new Parcela();
            $parcela->numero    = $row->{self::NUMERO};
            $parcela->porBoleto = $row->{self::POR_BOLETO};
            $parcela->venda     = $row->{self::VENDA};
            $parcela->valor     = $row->{self::VALOR};
            $parcela->cancelada = $row->{self::CANCELADA};
            $parcelas[]         = $parcela;
        }

        return $parcelas;
    }

    /**
     * Insere uma parcela na base de dados.
     * @param Parcela $parcela parcela que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Parcela $parcela) {
        $fields = implode(",", array( self::NUMERO, self::VALIDADE, self::REMARCACAO, self::VALOR,
                                      self::STATUS, self::VENDA, self::POR_BOLETO, self::CANCELADA ));
        $vars = get_object_vars($parcela);
        return parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
    }

    /**
     * Insere uma lista parcelas na base de dados.
     * @param array $prodVenda parcelas (array de Parcelas) que vão ser inseridas
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function inserts($arrParcelas){
        if(empty($arrParcelas) || !is_array($arrParcelas)) return false;
        $values = array();
        foreach ($arrParcelas as $parcela){
            $vars = get_object_vars($parcela);
            unset($vars['remarcacao']);
            $values[] = "(".Database::turnInValues($vars).")";
        }
        $fields = implode(",", array( self::NUMERO, self::VALIDADE, self::VALOR, self::STATUS,
                                      self::VENDA, self::POR_BOLETO, self::CANCELADA ));
        return parent::insert(self::TABLE, $fields, $values);
    }

    /**
     * Realiza a remoção de uma parcela.
     * @param Parcela $parcela parcela a ser removida.
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete(Parcela $parcela) {
        $condition = self::NUMERO." = {$parcela->numero} AND ".self::VENDA." = {$parcela->venda}";
        return parent::delete(self::TABLE, $condition, true);
    }

    /**
     * Atualiza <i>field</i> com <i>value</i> de acordo com <i>condition</i>.
     * @param string $value campo da tabela
     * @param string $value valor a atribuido
     * @param string $condition cláusula WHERE
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function simpleUpdate($field, $value, $condition){
        return parent::update(self::TABLE, $field, $value, $condition);
    }

    /**
     * Atualiza <i>parcela</i> de acordo com o seu identificador.
     * @param Parcela $parcela parcela que vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Parcela $parcela) {
        $dic = array (
            self::VALIDADE  => $parcela->validade,
            self::REMARCACAO=> $parcela->remarcacao,
            self::VALOR     => $parcela->valor,
            self::STATUS    => $parcela->status,
            self::POR_BOLETO=> $parcela->porBoleto,
            self::CANCELADA => $parcela->cancelada
        );
        $condition = self::NUMERO." = {$parcela->numero} AND ".self::VENDA." = {$parcela->venda}";
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }

    public function valoresAReceber($conditions, $dateBegin, $dateEnd) {

        if (empty($conditions)) $conditions = ' 1 = 1 ';

        $tolerance = self::DIAS_TOLERANCIA_BOLETO;

        $rawQuery = <<<EOT
        SELECT
            regiao.nome_regiao as regiao,
            rota.nome_rota as rota,
            localidade.nome_localidade as localidade,
            SUM(venda_parcela.valor_parcela) as valor
        FROM
            venda_parcela
            INNER JOIN venda ON id_venda = venda.id
            INNER JOIN cliente ON id_cliente = cliente.id
            INNER JOIN localidade ON id_localidade = localidade.id
            INNER JOIN rota ON id_rota = rota.id
            INNER JOIN regiao ON id_regiao = regiao.id
        WHERE
            venda_parcela.status_parcela = FALSE
            AND venda_parcela.cancelada = FALSE
            AND (
              (venda_parcela.por_boleto = TRUE AND (
                (venda_parcela.data_remarcacao IS NULL AND ( DATE_ADD(venda_parcela.data_parcela, INTERVAL $tolerance DAY) <= '$dateEnd' AND DATE_ADD(venda_parcela.data_parcela, INTERVAL $tolerance DAY) >= '$dateBegin')) OR
                (venda_parcela.data_remarcacao IS NOT NULL AND ( venda_parcela.data_remarcacao <= '$dateEnd' AND venda_parcela.data_remarcacao >= '$dateBegin' ) )
              ))
              OR
              (venda_parcela.por_boleto = FALSE AND (
                  (venda_parcela.data_remarcacao IS NULL AND ( venda_parcela.data_parcela <= '$dateEnd' AND venda_parcela.data_parcela >= '$dateBegin' )) OR
                  (venda_parcela.data_remarcacao IS NOT NULL AND ( venda_parcela.data_remarcacao <= '$dateEnd' AND venda_parcela.data_remarcacao >= '$dateBegin' ) )
              ))
            )
            AND $conditions
            GROUP BY regiao, rota, localidade
            ORDER BY regiao, rota, localidade;
EOT;

        $ana = $this->getAnnalisses();
        $parcelas = array();
        $res = $ana->execute($rawQuery);
        while (($row = $ana->fetchObject($res)) !== FALSE) $parcelas[] = $row;
        return $parcelas;
    }

}
?>
