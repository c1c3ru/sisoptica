<?php

date_default_timezone_set("America/Fortaleza");

$dbConfig = simplexml_load_file('src/dao/dbconfig.xml'); 

backup_tables($dbConfig->host, $dbConfig->user, $dbConfig->password, $dbConfig->dbname);

//Limitando diretório de buffer
$files  = array();
$dir    = opendir('backups/'); 
while(($f = readdir($dir)) !== FALSE){
    if(!strcmp($f, '.') || !strcmp($f, '..')) continue;
    $files[] = 'backups/'.$f;
}
closedir($dir);

if(count($files) >= 8) {
    usort($files, 'cmp');
    $size = count($files);
    while($size > 6){
        $tag = array_pop($files);
        unlink($tag);
        $size--;
    }
}

//Limitando diretório de logs
$files_log  = array();
$dir_log    = opendir('src/util/log/'); 
while(($f = readdir($dir_log)) !== FALSE){
    if(!strcmp($f, '.') || !strcmp($f, '..')) continue;
    $files_log[] = 'src/util/log/'.$f;
}
closedir($dir_log);

if(count($files_log) > 10) {
    usort($files_log, 'cmp_log');
    $size = count($files_log);
    while($size > 10){
        $tag = array_pop($files_log);
        unlink($tag);
        $size--;
    }
}

function cmp($f1, $f2){
    $p_1 = explode('_', $f1);
    $p_2 = explode('_', $f2);
    $t_1 = strtotime($p_1[1]);
    $t_2 = strtotime($p_2[1]);
    return $t_2 - $t_1;
}

function cmp_log($f1, $f2){
    $p_1 = implode("-", array_reverse(explode('_', $f1)));
    $p_2 = implode("-", array_reverse(explode('_', $f2)));
    $t_1 = strtotime($p_1);
    $t_2 = strtotime($p_2);
    return $t_2 - $t_1;
}

/* backup the db OR just a table */
function backup_tables($host, $user, $pass, $name, $tables = '*')
{
    $link = mysql_connect($host,$user,$pass);
    if($link === FALSE){
        exit('Fail to Connect');
    }
    mysql_select_db($name, $link);
    
    $dateName   = date('Y-m-d');
    $sufix      = strcmp(date('a'), 'am') ? '_NOITE.sql' : '_DIA.sql';

    //save file
    $backupFilename = 'backups/backup_'.$dateName.$sufix;
    $handle = fopen($backupFilename, 'w+');
    
    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while($row = mysql_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    
    //cycle through
    foreach($tables as $table)
    {
        $result = mysql_query('SELECT * FROM '.$table);
        if(!$result){
            $error_n = mysql_errno();
            echo '['.$error_n.'] '.mysql_error().'<br/>';
            if($error_n == 2006){
                mysql_close($link);
                $link = mysql_connect($host,$user,$pass);
                mysql_select_db($name, $link);
                echo "reconnect...<br/>";
                $result = mysql_query('SELECT * FROM '.$table);
                echo "result has error: ";
                var_dump($result);
            } else {
                continue;
            }
        }
        $num_fields = mysql_num_fields($result);

        fwrite($handle, 'DROP TABLE '.$table.';');
        $row2 = mysql_fetch_row( mysql_query('SHOW CREATE TABLE '.$table) );
        fwrite($handle, "\n\n".$row2[1].";\n\n");

        for ($i = 0; $i < $num_fields; $i++) 
        {
            while($row = mysql_fetch_row($result)) {
                fwrite($handle, 'INSERT INTO '.$table.' VALUES(');
                for($j=0; $j<$num_fields; $j++) 
                {
                    if($row[$j] === NULL){
                        fwrite($handle, 'NULL');
                    } else {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n","\\n",$row[$j]);
                        if (isset($row[$j])){
                            fwrite($handle, '"'.$row[$j].'"');
                        } else {
                            fwrite($handle, '""');
                        }
                    }
                    if ($j<($num_fields-1)) { 
                        fwrite($handle, ','); 
                    }
                }
                fwrite($handle, ");\n");
            }
        }
        fwrite($handle, "\n\n\n");
    }
    
    fclose($handle);
    mysql_close($link);

    $zip = new ZipArchive();
    $destination = $backupFilename.'.zip';
    if($zip->open($destination, ZIPARCHIVE::CREATE) !== true) {
        echo 'Fail to open zip file for save!';
        return ;
    }
    $zip->addFile($backupFilename, $backupFilename);
    $zip->close();

}
?>
