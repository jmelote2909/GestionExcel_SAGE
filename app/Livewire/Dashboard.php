<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Balance;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public $selectedYear = 'Todos';
    public $selectedMonth = 'Todos';
    public $startMonth = 'Enero';
    public $endMonth = 'Diciembre';
    public $filterMode = 'single'; // 'single' or 'range'
    public $selectedEmpresa = 'Todos';
    
    public $years = [];
    public $empresas = ['Todos', 'ARANCALO', 'CIMA', 'OTRO'];
    public $months = [
        'Todos', 'Enero', 'Febrero', 'Marzo', 'Abril',
        'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
        'Octubre', 'Noviembre', 'Diciembre'
    ];

    public $chartData = [];

    public function mount()
    {
        $this->years = Balance::distinct()->pluck('año')->filter()->sort()->prepend('Todos')->toArray();
    }

    public function updated($property)
    {
        if (in_array($property, ['selectedYear', 'selectedMonth', 'selectedEmpresa', 'startMonth', 'endMonth', 'filterMode'])) {
            $this->dispatch('summaryChartUpdated');
        }
    }

    private function getMonthMap()
    {
        return [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];
    }

    public function render()
    {
        $baseQuery = Balance::where('is_summary', false);

        if ($this->selectedYear !== 'Todos') {
            $baseQuery->where('año', $this->selectedYear);
        }

        if ($this->filterMode === 'single') {
            if ($this->selectedMonth !== 'Todos') {
                $baseQuery->where('mes', $this->selectedMonth);
            }
        } else {
            $monthMap = $this->getMonthMap();
            $start = $monthMap[$this->startMonth] ?? 1;
            $end = $monthMap[$this->endMonth] ?? 12;
            
            // Generate list of month names in the range
            $monthNamesInRange = array_keys(array_filter($monthMap, fn($idx) => $idx >= $start && $idx <= $end));
            $baseQuery->whereIn('mes', $monthNamesInRange);
        }

        if ($this->selectedEmpresa !== 'Todos') {
            $baseQuery->where('empresa', $this->selectedEmpresa);
        }

        $ventasGroup = (clone $baseQuery)->where('Grupo', '1')->get();
        $ventas = abs((float) $ventasGroup->sum('corrected_saldo_float'));
        
        // FIX: Use the new corrected_presupuesto_float accessor and avoid summing string-manipulated values directly
        $presupuestoVentas = abs((float) $ventasGroup->sum('corrected_presupuesto_float'));
        
        $comprasData = (clone $baseQuery)->whereIn('Grupo', ['2', '4'])->get();
        $compras = (float) $comprasData->sum('corrected_saldo_float');
        $compras60 = (float) $comprasData->filter(fn($item) => str_starts_with((string)$item->Cuenta, '60'))->sum('corrected_saldo_float');
        $compras61 = (float) $comprasData->filter(fn($item) => str_starts_with((string)$item->Cuenta, '61'))->sum('corrected_saldo_float');
        
        $salarios = (float) (clone $baseQuery)->where('Grupo', '6')->get()->sum('corrected_saldo_float');
        $otrosGastos = (float) (clone $baseQuery)->where('Grupo', '7')->get()->sum('corrected_saldo_float');
        $financieros = (float) (clone $baseQuery)->where('Grupo', '14')->get()->sum('corrected_saldo_float');
        
        $resultado = $ventas - $compras - $salarios - $otrosGastos - $financieros;

        // Calculate percentages relative to ventas
        $ventasRef = $ventas > 0 ? $ventas : 1; // Avoid division by zero
        $p_compras = ($compras / $ventasRef) * 100;
        $p_salarios = ($salarios / $ventasRef) * 100;
        $p_otrosGastos = ($otrosGastos / $ventasRef) * 100;
        $p_financieros = ($financieros / $ventasRef) * 100;
        $p_resultado = ($resultado / $ventasRef) * 100;

        $this->chartData = [
            'ventas' => $ventas,
            'presupuesto' => $presupuestoVentas
        ];

        return view('livewire.dashboard', [
            'ventas' => $ventas,
            'compras' => $compras,
            'compras60' => $compras60,
            'compras61' => $compras61,
            'salarios' => $salarios,
            'otrosGastos' => $otrosGastos,
            'financieros' => $financieros,
            'resultado' => $resultado,
            'p_compras' => $p_compras,
            'p_salarios' => $p_salarios,
            'p_otrosGastos' => $p_otrosGastos,
            'p_financieros' => $p_financieros,
            'p_resultado' => $p_resultado,
        ]);
    }
}
