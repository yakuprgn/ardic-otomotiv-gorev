<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class XlsxExporter
{
    public function download(string $filename, array $headers, iterable $rows): BinaryFileResponse
    {
        $directory = storage_path('app/tmp/exports');
        $path = $directory.DIRECTORY_SEPARATOR.'export-'.Str::uuid()->toString().'.xlsx';
        $spreadsheet = null;

        try {
            File::ensureDirectoryExists($directory, 0750, true);
            if (! is_dir($directory) || ! is_writable($directory)) throw new RuntimeException('Export directory is not writable.');

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet()->setTitle('Veriler');
            foreach (array_values($headers) as $column => $header) {
                $sheet->setCellValueExplicit([$column + 1, 1], (string) $header, DataType::TYPE_STRING);
            }

            $rowNumber = 2;
            foreach ($rows as $row) {
                foreach (array_values($row) as $column => $value) $this->writeCell($sheet, $column + 1, $rowNumber, $value);
                $rowNumber++;
            }

            $lastColumn = $sheet->getHighestColumn();
            $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF183B66']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->freezePane('A2');
            $sheet->setAutoFilter('A1:'.$lastColumn.'1');
            foreach (range('A', $lastColumn) as $column) $sheet->getColumnDimension($column)->setAutoSize(true);

            (new Xlsx($spreadsheet))->save($path);
            if (! is_file($path) || filesize($path) === 0) throw new RuntimeException('Workbook was not written.');
        } catch (\Throwable $exception) {
            if (is_file($path)) @unlink($path);
            Log::error('XLSX export generation failed.', ['exception'=>$exception::class, 'message'=>$exception->getMessage()]);
            throw new RuntimeException('Excel dosyası oluşturulurken bir sorun oluştu. Lütfen tekrar deneyin.', 0, $exception);
        } finally {
            $spreadsheet?->disconnectWorksheets();
        }

        return response()->download($path, $filename, ['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend();
    }

    private function writeCell($sheet, int $column, int $row, mixed $value): void
    {
        $coordinate = [$column, $row];
        if ($value instanceof DateTimeInterface) {
            $sheet->setCellValue($coordinate, Date::dateTimeToExcel($value));
            $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode('dd.mm.yyyy hh:mm');
        } elseif (is_int($value) || is_float($value)) {
            $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC);
        } else {
            $text = (string) ($value ?? '');
            if (preg_match('/^[=+\-@]/u', $text)) $text = "'".$text;
            $sheet->setCellValueExplicit($coordinate, $text, DataType::TYPE_STRING);
        }
    }
}
