<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use ZipArchive;

#[Layout('components.layouts.app')]
class ExcelViewer extends Component
{
    use WithFileUploads;

    public $rows = [];
    public $footers = [];
    public $metadata = [];
    public $years = [];
    public $selectedYear = 'Todos';
    public $selectedEmpresa = 'Todos';
    public $empresas = ['Todos', 'ARANCALO', 'CIMA', 'OTRO'];
    public $showImportReminder = false;
    public $file;
    public $hasData = false;
    public $savedRows = []; // To track which rows were recently saved for UI feedback
    public $selectedRows = []; // IDs of rows selected via checkboxes
    public $sortBy = 'Cuenta';  // Default sort column
    public $sortDirection = 'asc'; // 'asc' or 'desc'
    public $startMonth = 'Enero';
    public $endMonth = 'Diciembre';
    public $filterMode = 'single'; // 'single' or 'range'

    public function mount()
    {
        $this->hasData = \App\Models\Balance::exists();
        $this->years = \App\Models\Balance::distinct()->pluck('año')->filter()->sort()->prepend('Todos')->toArray();
        $this->loadData();
    }

    public function loadData()
    {
        $query = \App\Models\Balance::query();
        
        if ($this->filterMode === 'single') {
            if ($this->selectedMonth !== 'Todos') {
                $query->where('mes', $this->selectedMonth);
            }
        } else {
            $monthMap = [
                'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
            ];
            $start = $monthMap[$this->startMonth] ?? 1;
            $end = $monthMap[$this->endMonth] ?? 12;
            $monthNamesInRange = array_keys(array_filter($monthMap, fn($idx) => $idx >= $start && $idx <= $end));
            $query->whereIn('mes', $monthNamesInRange);
        }
        
        if ($this->selectedYear !== 'Todos') {
            $query->where('año', $this->selectedYear);
        }

        if ($this->selectedEmpresa !== 'Todos') {
            $query->where('empresa', $this->selectedEmpresa);
        }

        $allData = $query->get();

        $allData->transform(function ($item) {
            $item->saldo_final_formatted = number_format($item->corrected_saldo_float, 2, ',', '');
            foreach (['DebeP', 'HaberP', 'SaldoP', 'DebeA', 'HaberA', 'SaldoA', 'presupuesto', 'correccion'] as $field) {
                $val = (string)$item->$field;
                if (!empty($val)) {
                    // Only clean if it has a comma (manual/European format). 
                    // If it only has a dot, it's the DB numeric format.
                    if (strpos($val, ',') !== false) {
                        $val = str_replace('.', '', $val);
                        $val = str_replace(',', '.', $val);
                    }
                    if (is_numeric($val)) {
                        $item->$field = number_format((float)$val, 2, ',', '');
                    }
                }
            }
            return $item;
        });

        // Apply in-memory sort so computed fields (e.g. corrected_saldo_float) can be sorted
        $numericCols = ['SaldoP', 'correccion', 'Saldo Final', 'presupuesto'];
        $allData = $allData->sortBy(function ($item) use ($numericCols) {
            if (in_array($this->sortBy, $numericCols)) {
                if ($this->sortBy === 'Saldo Final') return $item->corrected_saldo_float;
                $raw = str_replace(',', '.', str_replace('.', '', (string)($item->{$this->sortBy} ?? '0')));
                return is_numeric($raw) ? (float)$raw : 0;
            }
            $field = $this->sortBy === 'Grupo' ? 'Grupo' : $this->sortBy;
            return strtolower((string)($item->$field ?? ''));
        }, SORT_REGULAR, $this->sortDirection === 'desc');

        $this->rows = $allData->where('is_summary', false)->keyBy('id')->toArray();

        $this->footers = $allData->where('is_summary', true)->values()->toArray();
        
        if (count($this->rows) > 0) {
            $first = reset($this->rows);
            if (!empty($first['metadata'])) {
                $this->metadata = json_decode($first['metadata'], true) ?: [];
            }
        }
    }
    public $columns = [
        'Cuenta', 'Descripcion', 'SaldoP', 'correccion', 'Saldo Final', 'presupuesto', 'Grupo', 'empresa', 'mes', 'año'
    ];
    public $selectedMonth = 'Todos';
    public $months = [
        'Todos', 'Enero', 'Febrero', 'Marzo', 'Abril',
        'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
        'Octubre', 'Noviembre', 'Diciembre'
    ];
    public $errorMessage = null;

    public function upload()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ], [
            'file.mimes' => 'El archivo debe ser un Excel (.xlsx, .xls)'
        ]);

        $this->errorMessage = null;

        try {
            $spreadsheet = IOFactory::load($this->file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            
            $this->metadata['titulo'] = $this->getCellValue($sheet, 1, 'E');
            $this->metadata['nif'] = $this->extractAfter($this->getCellValue($sheet, 2, 'A'), 'NIF:');
            $this->metadata['ejercicio'] = $this->extractAfter($this->getCellValue($sheet, 2, 'H'), 'Ejercicio:');
            $this->metadata['empresa'] = $this->extractAfter($this->getCellValue($sheet, 3, 'A'), 'Empresa:');
            $this->metadata['moneda'] = $this->getCellValue($sheet, 3, 'H');

            $dataRows = [];
            $footerRows = [];

            $newMetadataJson = json_encode($this->metadata);

            $highestRow = $sheet->getHighestRow();
            $maxRows = min(500, $highestRow);

            // Detectar si existe una columna "mes" en la fila 6 (cabeceras)
            $mesColumn = '';
            for ($col = 'A'; $col !== 'AA'; $col++) {
                $headerVal = strtolower(trim((string)$this->getCellValue($sheet, 6, $col)));
                if ($headerVal === 'mes') {
                    $mesColumn = $col;
                    break;
                }
            }

            for ($i = 7; $i <= $maxRows; $i++) {
                $cuenta = $this->getCellValue($sheet, $i, 'A');
                $descripcion = $this->getCellValue($sheet, $i, 'B');

                if (empty(trim((string)$cuenta)) && empty(trim((string)$descripcion))) {
                    continue;
                }
                
                $isSummary = $this->isSummaryRow($cuenta, $descripcion);

                $mesValue = $mesColumn !== '' ? $this->getCellValue($sheet, $i, $mesColumn) : '';

                $extractedEmpresa = strtoupper($this->metadata['empresa'] ?? '');
                $mappedEmpresa = '';
                if (str_contains($extractedEmpresa, 'ARANCALO')) {
                    $mappedEmpresa = 'ARANCALO';
                } elseif (str_contains($extractedEmpresa, 'CIMA')) {
                    $mappedEmpresa = 'CIMA';
                } elseif (str_contains($extractedEmpresa, 'OTRO')) {
                    $mappedEmpresa = 'OTRO';
                }

                $row = [
                    'Cuenta' => $cuenta,
                    'Descripcion' => $descripcion,
                    'DebeP' => $this->getNumericCellValue($sheet, $i, 'C'),
                    'HaberP' => $this->getNumericCellValue($sheet, $i, 'D'),
                    'SaldoP' => $this->getNumericCellValue($sheet, $i, 'E'),
                    'DebeA' => $this->getNumericCellValue($sheet, $i, 'F'),
                    'HaberA' => $this->getNumericCellValue($sheet, $i, 'G'),
                    'SaldoA' => $this->getNumericCellValue($sheet, $i, 'H'),
                    'Grupo' => $this->getGrupoFromCuenta($cuenta),
                    'correccion' => '',
                    'empresa' => $mappedEmpresa,
                    'mes' => $mesValue,
                    'año' => $this->metadata['ejercicio'] ?? '',
                    'is_summary' => $isSummary,
                    'metadata' => $newMetadataJson
                ];

                // Logic to merge with existing budget or previous import
                $dbRow = \App\Models\Balance::updateOrCreate(
                    [
                        'Cuenta' => trim((string)$cuenta),
                        'Descripcion' => trim($descripcion),
                        'mes' => trim($row['mes']),
                        'año' => trim($row['año']),
                        'is_summary' => $isSummary
                    ],
                    $row
                );

                if ($isSummary) {
                    $footerRows[] = $dbRow->toArray();
                } else {
                    $dataRows[] = $dbRow->toArray();
                }
            }

            foreach ($dataRows as $row) {
                foreach (['DebeP', 'HaberP', 'SaldoP', 'DebeA', 'HaberA', 'SaldoA'] as $field) {
                    if (is_numeric($row[$field]) || preg_match('/^-?\d+\.\d+$/', (string)$row[$field])) {
                        $row[$field] = number_format((float)$row[$field], 2, ',', '');
                    }
                }
                $this->rows[$row['id']] = $row;
            }
            $this->footers = array_merge($this->footers, $footerRows);
            $this->years = \App\Models\Balance::distinct()->pluck('año')->filter()->sort()->prepend('Todos')->toArray();
            $this->hasData = true;
            $this->showImportReminder = true;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al procesar el archivo: ' . $e->getMessage();
        }
    }

    public function updatedFile()
    {
        $this->upload();
    }

    public function updated($property, $value)
    {
        if (in_array($property, ['selectedMonth', 'selectedYear', 'selectedEmpresa', 'startMonth', 'endMonth', 'filterMode'])) {
            $this->loadData();
            return;
        }

        // Livewire 3 property matching
        if (preg_match('/^rows\.(\d+)\.([a-zA-ZñÑ_]+)$/', $property, $matches)) {
            $this->performRowUpdate($matches[1], $matches[2], $value);
        }
    }

    public function updatedRows($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $this->performRowUpdate($parts[0], $parts[1], $value);
        }
    }

    private function performRowUpdate($id, $field, $value)
    {
        if ($field === 'Cuenta') {
            $this->rows[$id]['Grupo'] = $this->getGrupoFromCuenta($value);
            \App\Models\Balance::where('id', $id)->update([
                'Cuenta' => $value,
                'Grupo' => $this->rows[$id]['Grupo']
            ]);
            return;
        }

        $dbValue = $value;
        if (in_array($field, ['DebeP', 'HaberP', 'SaldoP', 'DebeA', 'HaberA', 'SaldoA', 'presupuesto', 'correccion'])) {
            $valStr = (string)$value;
            // Only clean dots if there is a comma (European format input)
            if (strpos($valStr, ',') !== false) {
                $valStr = str_replace('.', '', $valStr);
                $valStr = str_replace(',', '.', $valStr);
            }
            
            if (is_numeric($valStr)) {
                // IMPORTANT: We do NOT update $this->rows[$id][$field] here anymore.
                // If we update it with a formatted value like '100,00', it will overwrite 
                // what the user is currently typing in the browser, causing "flickering".
                // We only prepare the $dbValue for the database.
                $dbValue = number_format((float)$valStr, 2, '.', '');
            } else {
                $dbValue = $value;
            }
        }

        $record = \App\Models\Balance::find($id);
        if ($record) {
            $record->{$field} = $dbValue;
            $record->save();
            
            // Re-sync UI
            $this->rows[$id]['saldo_final_formatted'] = number_format($record->corrected_saldo_float, 2, ',', '');
        }
    }

    public function saveRow($id)
    {
        \Illuminate\Support\Facades\Log::info("Manual saveRow triggered for ID=$id");
        $row = $this->rows[$id];
        $record = \App\Models\Balance::find($id);
        if ($record) {
            foreach (['Cuenta', 'Descripcion', 'SaldoP', 'correccion', 'presupuesto', 'empresa', 'mes', 'año'] as $field) {
                if (isset($row[$field])) {
                    $val = (string)$row[$field];
                    $dbValue = $val;
                    if (in_array($field, ['SaldoP', 'correccion', 'presupuesto'])) {
                        if (strpos($val, ',') !== false) {
                            $val = str_replace('.', '', $val);
                            $val = str_replace(',', '.', $val);
                        }
                        if (is_numeric($val)) {
                            $dbValue = number_format((float)$val, 2, '.', '');
                        }
                    }
                    $record->{$field} = $dbValue;
                }
            }
            $record->save();
            $this->savedRows[$id] = true;
            $this->loadData(); // Refresh UI
            \Illuminate\Support\Facades\Log::info("Manual saveRow SUCCESS for ID=$id");
        }
    }

    public function addRow()
    {
        $newRow = [
            'Cuenta' => '', 'Descripcion' => '', 'DebeP' => '', 'HaberP' => '', 
            'SaldoP' => '', 'presupuesto' => '', 'DebeA' => '', 'HaberA' => '', 'SaldoA' => '',
            'Grupo' => '', 'correccion' => '', 
            'empresa' => $this->selectedEmpresa !== 'Todos' ? $this->selectedEmpresa : '', 
            'mes' => $this->selectedMonth !== 'Todos' ? $this->selectedMonth : '', 'año' => $this->metadata['ejercicio'] ?? '',
            'is_summary' => false, 'metadata' => json_encode($this->metadata ?? [])
        ];
        $dbRow = \App\Models\Balance::create($newRow);
        $this->rows[$dbRow->id] = $dbRow->toArray();
    }

    public function removeRow($id)
    {
        \App\Models\Balance::where('id', $id)->delete();
        unset($this->rows[$id]);
        if (\App\Models\Balance::count() === 0) {
            $this->hasData = false;
        }
    }

    public function export()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Balance');

            $headers = ['Cuenta', 'Descripcion', 'DebeP', 'HaberP', 'SaldoP', 'presupuesto', 'DebeA', 'HaberA', 'SaldoA', 'Grupo', 'correccion', 'empresa', 'mes', 'año'];

            $exportRows = array_merge(array_values($this->rows), $this->footers);

            // NativePHP's bundled PHP may not include XMLWriter, so keep an XLSX
            // fallback instead of degrading the export to CSV.
            $canWriteXlsx = extension_loaded('xmlwriter') && class_exists('XMLWriter');

            if ($canWriteXlsx) {
                // Full XLSX export with headers and metadata
                $headerStyle = [
                    'font'    => ['bold' => true],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
                ];

                $sheet->setCellValue('E1', $this->metadata['titulo'] ?? 'Balance de Sumas y Saldos');
                $sheet->getStyle('E1')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);
                $sheet->setCellValue('A2', 'NIF: '      . ($this->metadata['nif']      ?? ''));
                $sheet->setCellValue('H2', 'Ejercicio: '. ($this->metadata['ejercicio'] ?? ''));
                $sheet->setCellValue('A3', 'Empresa: '  . ($this->metadata['empresa']   ?? ''));
                $sheet->setCellValue('H3', $this->metadata['moneda'] ?? 'EUROS');

                foreach ($headers as $i => $header) {
                    $cell = $sheet->getCell([$i + 1, 6]);
                    $cell->setValue($header);
                    $sheet->getStyle([$i + 1, 6])->applyFromArray($headerStyle);
                }

                $currentRow = 7;
            }

            if ($canWriteXlsx) {
                foreach ($exportRows as $row) {
                    foreach ($headers as $i => $header) {
                        $sheet->setCellValue([$i + 1, $currentRow], $row[$header] ?? '');
                    }
                    $currentRow++;
                }

                foreach (range(1, count($headers)) as $col) {
                    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                }
            }

            // Write to a temp file first to avoid streaming / XMLWriter issues
            $tmpFile = tempnam(sys_get_temp_dir(), 'sage_export_') . '.xlsx';

            if ($canWriteXlsx) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $defaultName = 'balance_export.xlsx';
                $writer->save($tmpFile);
            } else {
                $defaultName = 'balance_export.xlsx';
                $this->writeFallbackXlsx($tmpFile, $headers, $exportRows);
                \Illuminate\Support\Facades\Log::info('XMLWriter not found - using fallback XLSX writer');
            }


            // Use native OS save dialog only when running inside NativePHP/Electron.
            // In web/dev context the Dialog facade is not available, so fall back to streamDownload.
            $dialogAvailable = class_exists(\Native\Laravel\Facades\Dialog::class);

            if ($dialogAvailable) {
                try {
                    $path = \Native\Laravel\Facades\Dialog::save()
                        ->title('Guardar exportación')
                        ->defaultPath($defaultName)
                        ->show();

                    if ($path) {
                        $ext = '.xlsx';
                        if (!str_ends_with(strtolower($path), $ext)) {
                            $path .= $ext;
                        }
                        copy($tmpFile, $path);
                        $this->dispatch('notify', message: 'Archivo guardado en ' . $path);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Native Dialog failed: ' . $e->getMessage());
                    $this->errorMessage = 'No se pudo abrir el diálogo de guardado: ' . $e->getMessage();
                } finally {
                    @unlink($tmpFile);
                }
            } else {
                $content = file_get_contents($tmpFile);
                @unlink($tmpFile);

                return response()->streamDownload(
                    fn() => print($content),
                    $defaultName,
                    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                );
            }


        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
        }
    }

    private function writeFallbackXlsx(string $path, array $headers, array $rows): void
    {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo XLSX temporal.');
        }

        $sheetRows = [];
        $sheetRows[] = $this->buildXlsxRow(1, ['', '', '', '', $this->metadata['titulo'] ?? 'Balance de Sumas y Saldos']);
        $sheetRows[] = $this->buildXlsxRow(2, ['NIF: ' . ($this->metadata['nif'] ?? ''), '', '', '', '', '', '', 'Ejercicio: ' . ($this->metadata['ejercicio'] ?? '')]);
        $sheetRows[] = $this->buildXlsxRow(3, ['Empresa: ' . ($this->metadata['empresa'] ?? ''), '', '', '', '', '', '', $this->metadata['moneda'] ?? 'EUROS']);
        $sheetRows[] = $this->buildXlsxRow(6, $headers, 1);

        $currentRow = 7;
        foreach ($rows as $row) {
            $sheetRows[] = $this->buildXlsxRow(
                $currentRow,
                array_map(fn ($header) => $row[$header] ?? '', $headers)
            );
            $currentRow++;
        }

        $zip->addFromString('[Content_Types].xml', $this->fallbackContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->fallbackRootRelsXml());
        $zip->addFromString('docProps/app.xml', $this->fallbackAppXml());
        $zip->addFromString('docProps/core.xml', $this->fallbackCoreXml());
        $zip->addFromString('xl/workbook.xml', $this->fallbackWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->fallbackWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->fallbackStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildFallbackWorksheetXml($sheetRows, count($headers), max(6, $currentRow - 1)));
        $zip->close();
    }

    private function buildFallbackWorksheetXml(array $rows, int $columnCount, int $lastRow): string
    {
        $dimension = 'A1:' . $this->columnLetter($columnCount) . $lastRow;
        $cols = [];

        for ($i = 1; $i <= $columnCount; $i++) {
            $cols[] = '<col min="' . $i . '" max="' . $i . '" width="18" customWidth="1"/>';
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <dimension ref="{$dimension}"/>
  <sheetViews><sheetView workbookViewId="0"/></sheetViews>
  <sheetFormatPr defaultRowHeight="15"/>
  <cols>{$this->implodeXml($cols)}</cols>
  <sheetData>{$this->implodeXml($rows)}</sheetData>
</worksheet>
XML;
    }

    private function buildXlsxRow(int $rowNumber, array $values, int $styleIndex = 0): string
    {
        $cells = [];

        foreach ($values as $index => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $cellRef = $this->columnLetter($index + 1) . $rowNumber;
            $style = $styleIndex > 0 ? ' s="' . $styleIndex . '"' : '';
            $cells[] = '<c r="' . $cellRef . '" t="inlineStr"' . $style . '><is><t>' . $this->escapeXml((string) $value) . '</t></is></c>';
        }

        return '<row r="' . $rowNumber . '">' . $this->implodeXml($cells) . '</row>';
    }

    private function fallbackContentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    private function fallbackRootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    private function fallbackWorkbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Balance" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private function fallbackWorkbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function fallbackStylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="1">
    <fill><patternFill patternType="none"/></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
  </cellXfs>
</styleSheet>
XML;
    }

    private function fallbackAppXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>GestionExcel_SAGE</Application>
</Properties>
XML;
    }

    private function fallbackCoreXml(): string
    {
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:creator>GestionExcel_SAGE</dc:creator>
  <cp:lastModifiedBy>GestionExcel_SAGE</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">{$timestamp}</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">{$timestamp}</dcterms:modified>
</cp:coreProperties>
XML;
    }

    private function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function implodeXml(array $items): string
    {
        return implode('', $items);
    }

    private function getCellValue($sheet, $row, $col)
    {
        $cell = $sheet->getCell($col . $row);
        return $cell ? trim((string)$cell->getFormattedValue()) : '';
    }

    private function getNumericCellValue($sheet, $row, $col)
    {
        $cell = $sheet->getCell($col . $row);
        if (!$cell) return '';

        $val = $cell->getCalculatedValue();
        if (is_numeric($val)) {
            return number_format((float)$val, 2, '.', '');
        }

        $formatted = trim((string)$cell->getFormattedValue());
        $clean = str_replace(',', '.', $formatted);
        if (is_numeric($clean)) {
            return number_format((float)$clean, 2, '.', '');
        }

        return $formatted;
    }

    private function extractAfter($text, $label)
    {
        if (str_contains((string)$text, $label)) {
            return trim(substr((string)$text, strpos((string)$text, $label) + strlen($label)));
        }
        return $text;
    }

    private function isSummaryRow($cuenta, $descripcion)
    {
        if (trim((string)$cuenta) === '*') return true;
        if (empty(trim((string)$descripcion))) return false;
        if (preg_match('/^[\d\s\.,\-]+$/', trim((string)$cuenta))) return false;

        $desc = strtolower(trim((string)$descripcion));
        return str_contains($desc, 'total balance') || 
               str_contains($desc, 'resultados') || 
               str_contains($desc, 'total empresa') || 
               str_starts_with($desc, 'total ');
    }

    private function getGrupoFromCuenta($cuenta)
    {
        if (empty($cuenta)) return '';
        $c = trim((string)$cuenta);
        if (str_starts_with($c, '70')) return '1';
        if (str_starts_with($c, '610')) return '2';
        if (str_starts_with($c, '607')) return '3';
        if (str_starts_with($c, '60')) return '4';
        if (str_starts_with($c, '64')) return '6';
        if (str_starts_with($c, '62') || str_starts_with($c, '63')) return '7';
        if (str_starts_with($c, '678') || str_starts_with($c, '778')) return '12';
        if (str_starts_with($c, '66') || str_starts_with($c, '76') || str_starts_with($c, '520')) return '14';
        return '';
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->selectedRows = [];
        $this->loadData();
    }

    public function toggleSelectAll(bool $checked): void
    {
        if ($checked) {
            $this->selectedRows = array_keys($this->rows);
        } else {
            $this->selectedRows = [];
        }
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedRows)) return;

        \App\Models\Balance::whereIn('id', $this->selectedRows)->delete();
        foreach ($this->selectedRows as $id) {
            unset($this->rows[$id]);
        }
        $this->selectedRows = [];

        if (\App\Models\Balance::count() === 0) {
            $this->hasData = false;
        }
    }

    public function render()
    {
        return view('livewire.excel-viewer');
    }
}
