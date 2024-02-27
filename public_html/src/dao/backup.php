<?php
class BackupModel extends Database {
    
    const TABLE_OS = "backup_mudancas_os";
    const TABLE_VENDA = "backup_mudancas_venda";
    
    const ID_OS = "os";
    const ID_VENDA = "venda";
    const ID = "id";
    const OBJETO = "objeto";
    const EDICAO = "edicao";
    
    public function select($table, $fields = " * ", $condition = null, $limit = null) {
        if(is_array($fields)) $fields = implode (",", $fields);
        switch ($table){
            case self::TABLE_OS: $tipo = self::ID_OS; break;
            case self::TABLE_VENDA: $tipo = self::ID_VENDA; break;
            default: return array();
        }
        $res = parent::select($table, $fields, $condition, $limit);
        $anna = $this->getAnnalisses();
        $backups = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $backups = new Backup(
                            isset($row->{self::ID}) ? $row->{self::ID}: 0,    
                            isset($row->{$tipo}) ? $row->{$tipo} : 0,
                            isset($row->{self::OBJETO}) ? $row->{self::OBJETO}: "",
                            isset($row->{self::EDICAO}) ? $row->{self::EDICAO} : true        
                       );
        }
        return $backups;
    }
    
    public function insert(Backup $backup, $table) {
        switch ($table){
            case self::TABLE_OS: $tipo = self::ID_OS; break;
            case self::TABLE_VENDA: $tipo = self::ID_VENDA; break;
            default: return false;
        }
        $fields = implode(",",  array($tipo, self::OBJETO, self::EDICAO));
        $backup->objeto = addslashes($backup->objeto);
        $vars = array($backup->tipo, $backup->objeto, $backup->edicao);
        return parent::insert($table, $fields, Database::turnInValues($vars));
    }
    
    public function delete($table, $id_backup) {
        switch ($table){
            case self::TABLE_OS: break;
            case self::TABLE_VENDA: break;
            default: return false;
        }
        $condition = self::ID." = $id_backup";
        return parent::delete($table, $condition);
    }
    
}
?>
