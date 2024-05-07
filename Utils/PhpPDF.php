<?php
require_once "vendor/autoload.php";
require_once "Utils/PhpExcel.php";

use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PhpPDF
{
    public static function arrayToPdf(
        $headers = array('Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'),
        $arrayData = array(
            array('Data 1', 'Data 2', 'Data 3', 'Data 4', 'Data 5'),
        )
    ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Adicionar cabeçalhos
        $sheet->fromArray($headers, null, 'A1');
    
        // Adicionar dados
        foreach ($arrayData as $rowIndex => $row) {
            // Adicionar cada linha de dados
            $sheet->fromArray($row, null, 'A' . ($rowIndex + 2));
        }
    
        // Estilizar a planilha
        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => 'F2F2F2'),
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ),
        );
    
        // Calcular a última coluna
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
    
        // Aplicar estilo a todas as células
        $sheet->getStyle('A1:' . $lastColumn . (count($arrayData) + 1))->applyFromArray($styleArray);
    
        // Ajustar largura das colunas
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    
        // Adicionar título
        $sheet->setTitle('Planilha');
    
        // Criar PDF
        $writer = new Mpdf($spreadsheet);
        return $writer;
    }
    
}
