<?php
include_once ENTITIES."backup.php";

include_once MODELS."backup.php";

class BackupController {

    private $modelBackup;
    
    public function __construct() {
        $this->modelBackup = new BackupModel();
    }
    
    public function addBackupVenda(Backup $backup){
        return $this->modelBackup->insert($backup, BackupModel::TABLE_VENDA);
    }
    public function addBackupOrdemServico(Backup $backup){
        return $this->modelBackup->insert($backup, BackupModel::TABLE_OS);
    }
    public function removerBackupVenda($id_backup){
        return $this->modelBackup->delete(BackupModel::TABLE_VENDA, $id_backup);
    }
    public function removerBackupOrdemServico($id_backup){
        return $this->modelBackup->delete(BackupModel::TABLE_OS, $id_backup);
    }
    public function getBackupsByVenda($id_venda){
        $condition = BackupModel::ID_VENDA." = $id_venda";
        return $this->modelBackup->select(BackupModel::TABLE_VENDA, "*", $condition);
    }
    public function getBackupsByOrdemServico($id_ordem){
        $condition = BackupModel::ID_OS." = $id_ordem";
        return $this->modelBackup->select(BackupModel::TABLE_VENDA, "*", $condition);
    }
    public function getBackupOfVenda($id_backup){
        $condition = BackupModel::ID." = $id_backup";
        $backup = $this->modelBackup->select(BackupModel::TABLE_VENDA, "*", $condition);
        if(empty($backup)) return new Backup();
        return $backup[0];
    }
    public function getBackupOfOrdemServico($id_backup){
        $condition = BackupModel::ID." = $id_backup";
        $backup = $this->modelBackup->select(BackupModel::TABLE_OS, "*", $condition);
        if(empty($backup)) return new Backup();
        return $backup[0];
    }
    public function backupTo(Backup $backup, $clasname){
        $obj = $clasname();
        $vars = array_keys(get_object_vars($obj));
        $objJson = json_decode($backup->objeto, true);
        foreach ($vars as $var){
            $obj->{$var} = $objJson[$var];
        }
        return $obj;
    }
    public function anyToBackup($obj){
        $vars = get_object_vars($obj);
        return json_encode($vars);
    }
}
?>
