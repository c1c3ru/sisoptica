<?php
/**
 * Esse script gera o backup de relatórios que somente operadores podem acessar
 */

include_once 'src/util/config.php';
include_once 'src/print/offline/offline-printer.php';

OfflinePrinterUtils::checkRequirements();

$folder = OfflinePrinterUtils::randMkdirOnRoot();
$printers = OfflinePrinterUtils::getRequestPrinters();
$limit = OfflinePrinterUtils::limitDate();
$today = date("Y-m-d");

foreach ( $printers as $printer ) {
    $printer->printOn($folder, $limit);
}

// Criando o arquivo zipado
// -----------------------
$zip = new ZipArchive();

$zip_name = "OFFLINE-" . Config::getInstance()->getCurrentLojaSigla() . "_${today}_${limit}.zip";
if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== true) {
    OfflinePrinterUtils::clearFolder($folder);
    exit( "Não foi possível abrir <$zip_name>\n"  );
}

$files = array_diff(scandir($folder), array(".",".."));
foreach($files as $file) $zip->addFile($folder.$file, $file);

$zip->close();

// Elaborando o download do arquivo zipado
header("Content-Type: application/zip");
header("Content-Length: ".filesize($zip_name));
header("Content-Disposition: attachment; filename=".basename($zip_name));
readfile($zip_name);

unlink($zip_name);
OfflinePrinterUtils::clearFolder($folder);

?>
