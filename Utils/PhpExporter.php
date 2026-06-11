<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;


class PhpExporter
{
    /**
     * @param array $headers
     * @param array $rows
     * @param string $fileName
     * @param array|null $columnSelectValidations Lista de validações por coluna (índice 1 = coluna A).
     *   Cada item: ['column' => int, 'options' => string[]]. Gera dropdown (lista) nas células de dados.
     */
    public static function exportToExcel($headers, $rows, $fileName, $columnSelectValidations = null)
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

        $dataRowCount = count($rows);
        $lastDataRow = max(2, $dataRowCount + 1);
        $validationEndRow = max($lastDataRow, 1000);

        if (!empty($columnSelectValidations) && is_array($columnSelectValidations)) {
            self::applyColumnListValidations($spreadsheet, $sheet, $columnSelectValidations, $validationEndRow);
        }

        // Configurar cabeçalhos HTTP para o download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $dataSheet
     * @param array $columnSelectValidations
     * @param int $validationEndRow Última linha (inclusive) que recebe a lista suspensa
     */
    private static function applyColumnListValidations($spreadsheet, $dataSheet, $columnSelectValidations, $validationEndRow)
    {
        $listSheet = new Worksheet($spreadsheet, 'Listas');
        $spreadsheet->addSheet($listSheet);
        $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $listCol = 1;
        foreach ($columnSelectValidations as $spec) {
            if (empty($spec['column']) || empty($spec['options']) || !is_array($spec['options'])) {
                continue;
            }
            $options = array_values(array_unique(array_filter($spec['options'], function ($v) {
                return $v !== null && $v !== '';
            })));
            if ($options === []) {
                continue;
            }

            $targetCol = (int) $spec['column'];
            if ($targetCol < 1) {
                continue;
            }

            $startRow = 1;
            foreach ($options as $i => $opt) {
                $listSheet->setCellValue(
                    Coordinate::stringFromColumnIndex($listCol) . ($startRow + $i),
                    $opt
                );
            }
            $endOptRow = $startRow + count($options) - 1;
            $listColLetter = Coordinate::stringFromColumnIndex($listCol);
            $listSheetTitle = $listSheet->getTitle();
            $quotedTitle = str_replace("'", "''", $listSheetTitle);
            $formula = "'{$quotedTitle}'!\$" . $listColLetter . "\$" . $startRow . ":\$" . $listColLetter . "\$" . $endOptRow;

            $targetColLetter = Coordinate::stringFromColumnIndex($targetCol);
            $range = $targetColLetter . '2:' . $targetColLetter . $validationEndRow;

            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('=' . $formula);

            $dataSheet->setDataValidation($range, $validation);

            $listCol++;
        }
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
