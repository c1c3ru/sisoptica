<?php
if (!$link = mysql_connect('212.1.209.1', 'u293830981_os', 'Rafael@2024')) {
    echo 'Could not connect to mysql';
    exit;
}

if (!mysql_select_db('u293830981_os', $link)) {
    echo 'Could not select database';
    exit;
}

$sql    = 'SELECT funcionario FROM';
$result = mysql_query($sql, $link);

if (!$result) {
    echo "DB Error, could not query the database\n";
    echo 'MySQL Error: ' . mysql_error();
    exit;
}

while ($row = mysql_fetch_assoc($result)) {
    echo $row['foo'];
}

mysql_free_result($result);

?>