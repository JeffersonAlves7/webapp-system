<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Dompdf\Dompdf;
use Dompdf\Options;


class PhpExporter
{
    public static function exportToExcel($headers, $rows, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Adicionar cabeçalhos
        $columnIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . '1', $header);
            $columnIndex++;
        }

        // Adicionar dados
        $rowIndex = 2;
        foreach ($rows as $row) {
            $columnIndex = 1;
            foreach ($row as $cell) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex, $cell);
                $columnIndex++;
            }
            $rowIndex++;
        }

        // Configurar cabeçalhos HTTP para o download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function exportToPdf($headers, $data, $output_filename)
    {
        // Configurar as opções do Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Gerar HTML para a tabela
        $html = '<html><body>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr>';

        // Adicionar cabeçalhos
        foreach ($headers as $header) {
            $html .= '<th style="background-color: #f2f2f2;">' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        // Adicionar dados
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $cell_value = empty($cell) ? 'N/A' : htmlspecialchars($cell);
                $html .= '<td>' . $cell_value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</body></html>';

        // Carregar o HTML no Dompdf
        $dompdf->loadHtml($html);

        // Definir o tamanho e a orientação do papel
        $dompdf->setPaper('A4', 'portrait');

        // Renderizar o HTML como PDF
        $dompdf->render();

        // Enviar o PDF para o navegador
        $dompdf->stream($output_filename, array('Attachment' => 0));
    }
}
