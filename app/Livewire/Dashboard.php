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
        if ($property === 'selectedYear' || $property === 'selectedMonth' || $property === 'selectedEmpresa') {
            $this->dispatch('summaryChartUpdated');
        }
    }

    public function render()
    {
        $baseQuery = Balance::where('is_summary', false);

        if ($this->selectedYear !== 'Todos') {
            $baseQuery->where('año', $this->selectedYear);
        }

        if ($this->selectedMonth !== 'Todos') {
            $baseQuery->where('mes', $this->selectedMonth);
        }

        if ($this->selectedEmpresa !== 'Todos') {
            $baseQuery->where('empresa', $this->selectedEmpresa);
        }

        $ventasGroup = (clone $baseQuery)->where('Grupo', '1')->get();
        $ventas = abs((float) $ventasGroup->sum('corrected_saldo_float'));
        $presupuestoVentas = abs((float) $ventasGroup->sum(fn($item) => (float)str_replace(',', '.', str_replace('.', '', (string)$item->presupuesto))));
        
        $compras = (float) (clone $baseQuery)->whereIn('Grupo', ['2', '4'])->get()->sum('corrected_saldo_float');
        $salarios = (float) (clone $baseQuery)->where('Grupo', '6')->get()->sum('corrected_saldo_float');
        $otrosGastos = (float) (clone $baseQuery)->where('Grupo', '7')->get()->sum('corrected_saldo_float');
        $financieros = (float) (clone $baseQuery)->where('Grupo', '14')->get()->sum('corrected_saldo_float');
        
        $resultado = $ventas - $compras - $salarios - $otrosGastos - $financieros;

        $this->chartData = [
            'ventas' => $ventas,
            'presupuesto' => $presupuestoVentas
        ];

        return view('livewire.dashboard', [
            'ventas' => $ventas,
            'compras' => $compras,
            'salarios' => $salarios,
            'otrosGastos' => $otrosGastos,
            'financieros' => $financieros,
            'resultado' => $resultado,
        ]);
    }
}
