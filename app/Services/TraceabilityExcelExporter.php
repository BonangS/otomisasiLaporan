<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class TraceabilityExcelExporter
{
    /**
     * @param  array{rows: list<array<string, string|null>>}  $report
     */
    public function export(array $report): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip belum aktif, sehingga file XLSX tidak bisa dibuat.');
        }

        $templatePath = (string) config('services.report_template.path');

        if ($templatePath === '' || ! is_file($templatePath)) {
            throw new RuntimeException('Template Excel tidak ditemukan. Atur REPORT_TEMPLATE_PATH di .env ke file template .xlsx.');
        }

        $outputDirectory = (string) config('services.report_template.output_path', storage_path('app/reports'));

        if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0755, true) && ! is_dir($outputDirectory)) {
            throw new RuntimeException('Folder penyimpanan Excel tidak bisa dibuat.');
        }

        $outputPath = $outputDirectory.DIRECTORY_SEPARATOR.$this->outputFilename();

        if (! copy($templatePath, $outputPath)) {
            throw new RuntimeException('Gagal menyalin template Excel.');
        }

        $zip = new ZipArchive;

        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException('Gagal membuka template Excel.');
        }

        $zip->deleteName('xl/worksheets/sheet1.xml');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($report['rows']));
        $zip->close();

        return $outputPath;
    }

    private function outputFilename(): string
    {
        return 'laporan-traceability-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.xlsx';
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     */
    private function worksheetXml(array $rows): string
    {
        $headers = [
            'TANGGAL',
            'NOMOR FCL',
            'MERK',
            'SIZE',
            'QTY PCS',
            'JAM FILLING',
            'MESIN',
            'KODE MOULD',
            'KODE OPERATOR',
            'TEMPERATURE',
            'RESIN',
        ];

        $keys = ['tanggal', 'nomor_fcl', 'merk', 'size', 'qty_pcs', 'jam_filling', 'mesin', 'kode_mould', 'kode_operator', 'temperature', 'resin'];
        $sheetRows = [$this->rowXml(1, $headers, true)];

        foreach (array_values($rows) as $index => $row) {
            $values = array_map(fn (string $key): string => (string) ($row[$key] ?? ''), $keys);
            $sheetRows[] = $this->rowXml($index + 2, $values);
        }

        $lastRow = max(count($rows) + 1, 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<dimension ref="A1:K'.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A2" sqref="A2"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18"/>'
            .'<cols><col min="1" max="3" width="24" customWidth="1"/><col min="4" max="5" width="12" customWidth="1"/><col min="6" max="11" width="18" customWidth="1"/></cols>'
            .'<sheetData>'.implode('', $sheetRows).'</sheetData>'
            .'<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            .'</worksheet>';
    }

    /**
     * @param  list<string>  $values
     */
    private function rowXml(int $number, array $values, bool $header = false): string
    {
        $cells = [];

        foreach ($values as $index => $value) {
            $reference = $this->columnName($index + 1).$number;
            $style = $header ? ' s="1"' : '';
            $cells[] = '<c r="'.$reference.'" t="inlineStr"'.$style.'><is><t>'.$this->escape($value).'</t></is></c>';
        }

        return '<row r="'.$number.'">'.implode('', $cells).'</row>';
    }

    private function columnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
