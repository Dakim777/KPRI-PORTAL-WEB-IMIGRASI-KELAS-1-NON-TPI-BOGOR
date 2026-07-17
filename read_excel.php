<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileType = 'Xlsx';
$inputFileName = 'penjualan pembelian.xlsx';

$reader = IOFactory::createReader($inputFileType);
$spreadsheet = $reader->load($inputFileName);

$sheetNames = $spreadsheet->getSheetNames();

foreach ($sheetNames as $sheetName) {
    echo "Sheet: $sheetName\n";
    $sheet = $spreadsheet->getSheetByName($sheetName);
    
    // Get headers (first 5 rows just in case)
    $maxCell = $sheet->getHighestRowAndColumn();
    $data = $sheet->rangeToArray('A1:' . $maxCell['column'] . '5');
    
    foreach ($data as $rowIndex => $row) {
        // filter out completely empty rows
        $isEmpty = true;
        foreach($row as $cell) {
            if (!empty(trim($cell))) { $isEmpty = false; break; }
        }
        if (!$isEmpty) {
            echo "Row " . ($rowIndex + 1) . ": " . implode(" | ", $row) . "\n";
        }
    }
    echo "--------------------------\n";
}
