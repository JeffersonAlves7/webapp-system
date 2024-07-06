<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public static function generatePdf($headers, $data, $output_filename)
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
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
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
