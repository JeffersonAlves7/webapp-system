<?php

// Utilizar phpoffice/phpspreadsheet para ler arquivos .xlsx

require_once "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

class PhpExcel
{
    public static function read($file, $maxColumns = 0)
    {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];

            foreach ($row->getCellIterator() as $cell) {
                $columnString = $cell->getColumn();
                $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnString);
                if ($maxColumns > 0 && $columnIndex > $maxColumns) {
                    break;
                }

                $cells[] = $cell->getValue();
            }

            if (count(array_filter($cells)) == 0) {
                break;
            }

            $rows[] = $cells;
        }

        return $rows;
    }

    public static function formatAsTable($rows)
    {
        $table = "<table border='1'>";
        foreach ($rows as $row) {
            $table .= "<tr>";
            foreach ($row as $cell) {
                $table .= "<td>$cell</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</table>";

        return $table;
    }
}
