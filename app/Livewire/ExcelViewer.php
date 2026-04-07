<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

#[Layout('components.layouts.app')]
class ExcelViewer extends Component
{
    use WithFileUploads;

    public $rows = [];
    public $footers = [];
    public $metadata = [];
    public $years = [];
    public $selectedYear = 'Todos';
    public $showImportReminder = false;
    public $file;
    public $hasData = false;
    public $savedRows = []; // To track which rows were recently saved for UI feedback

    public function mount()
    {
        $this->hasData = \App\Models\Balance::exists();
        $this->years = \App\Models\Balance::distinct()->pluck('año')->filter()->sort()->prepend('Todos')->toArray();
        $this->loadData();
    }

    public function loadData()
    {
        $query = \App\Models\Balance::query();
        
        if ($this->selectedMonth !== 'Todos') {
            $query->where('mes', $this->selectedMonth);
        }
        
        if ($this->selectedYear !== 'Todos') {
            $query->where('año', $this->selectedYear);
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
                    'empresa' => '',
                    'mes' => $mesValue,
                    'año' => $this->metadata['ejercicio'] ?? '',
                    'is_summary' => $isSummary,
                    'metadata' => $newMetadataJson
                ];

                // Logic to merge with existing budget or previous import
                $dbRow = \App\Models\Balance::updateOrCreate(
                    [
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
        if ($property === 'selectedMonth' || $property === 'selectedYear') {
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
            'Grupo' => '', 'correccion' => '', 'empresa' => '', 'mes' => $this->selectedMonth !== 'Todos' ? $this->selectedMonth : '', 'año' => $this->metadata['ejercicio'] ?? '',
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

            $headerStyle = [
                'font' => ['bold' => true],
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            $titleStyle = [
                'font' => ['bold' => true, 'size' => 14]
            ];

            $sheet->setCellValue('E1', $this->metadata['titulo'] ?? 'Balance de Sumas y Saldos');
            $sheet->getStyle('E1')->applyFromArray($titleStyle);

            $sheet->setCellValue('A2', 'NIF: ' . ($this->metadata['nif'] ?? ''));
            $sheet->setCellValue('H2', 'Ejercicio: ' . ($this->metadata['ejercicio'] ?? ''));
            
            $sheet->setCellValue('A3', 'Empresa: ' . ($this->metadata['empresa'] ?? ''));
            $sheet->setCellValue('H3', $this->metadata['moneda'] ?? 'EUROS');

            $headers = ['Cuenta', 'Descripcion', 'DebeP', 'HaberP', 'SaldoP', 'presupuesto', 'DebeA', 'HaberA', 'SaldoA', 'Grupo', 'correccion', 'empresa', 'mes', 'año'];
            
            $colIndex = 1;
            foreach ($headers as $header) {
                $cell = $sheet->getCell([$colIndex, 6]);
                $cell->setValue($header);
                $sheet->getStyle([$colIndex, 6])->applyFromArray($headerStyle);
                $colIndex++;
            }

            $currentRow = 7;
            
            $filteredData = $this->rows;

            foreach ($filteredData as $row) {
                $colIndex = 1;
                foreach ($headers as $header) {
                    $sheet->setCellValue([$colIndex, $currentRow], $row[$header] ?? '');
                    $colIndex++;
                }
                $currentRow++;
            }

            foreach ($this->footers as $row) {
                $colIndex = 1;
                foreach ($headers as $header) {
                    $sheet->setCellValue([$colIndex, $currentRow], $row[$header] ?? '');
                    $colIndex++;
                }
                $currentRow++;
            }

            foreach (range(1, count($headers)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, 'balance_export.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
        }
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
        if (str_starts_with($c, '607')) return '3';
        if (str_starts_with($c, '60')) return '4';
        if (str_starts_with($c, '64')) return '6';
        if (str_starts_with($c, '62') || str_starts_with($c, '63')) return '7';
        if (str_starts_with($c, '678') || str_starts_with($c, '778')) return '12';
        if (str_starts_with($c, '66') || str_starts_with($c, '76')) return '14';
        return '';
    }

    public function render()
    {
        return view('livewire.excel-viewer');
    }
}
