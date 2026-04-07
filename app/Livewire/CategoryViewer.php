<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Balance;

#[Layout('components.layouts.app')]
class CategoryViewer extends Component
{
    public $categoria;

    #[Url]
    public $year = 'Todos';

    #[Url]
    public $month = 'Todos';

    public $rows = [];
    public $years = [];
    public $months = [
        'Todos', 'Enero', 'Febrero', 'Marzo', 'Abril',
        'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
        'Octubre', 'Noviembre', 'Diciembre'
    ];
    public $hasData = false;
    
    public $chartLabels = [];
    public $chartReal = [];
    public $chartBudget = [];

    public $columns = [
        'Cuenta', 'Descripcion', 'SaldoP', 'correccion', 'Saldo Final', 'Grupo', 'empresa', 'mes', 'año'
    ];

    public function mount($category)
    {
        $this->categoria = strtolower($category);
        $this->hasData = Balance::exists();
        $this->years = Balance::distinct()->pluck('año')->filter()->sort()->prepend('Todos')->toArray();
        $this->loadData();
        $this->updateChartData();
    }

    public function loadData()
    {
        $query = Balance::where('is_summary', false);

        $groups = [];
        switch ($this->categoria) {
            case 'ventas':
                $groups = ['1'];
                break;
            case 'compras':
                $groups = ['2', '4'];
                break;
            case 'salarios':
                $groups = ['6'];
                break;
            case 'otros-gastos':
            case 'otros_gastos':
            case 'otrosgastos':
                $groups = ['7'];
                break;
            case 'financieros':
                $groups = ['14'];
                break;
            case 'resultado':
                $groups = ['1', '2', '4', '6', '7', '14'];
                break;
            default:
                break;
        }

        if (!empty($groups)) {
            $query->whereIn('Grupo', $groups);
        }

        if ($this->year !== 'Todos') {
            $query->where('año', $this->year);
        }

        if ($this->month !== 'Todos') {
            $query->where('mes', $this->month);
        }

        $allData = $query->get();

        $allData->transform(function ($item) {
            $item->saldo_final_formatted = number_format($item->corrected_saldo_float, 2, ',', '');
            foreach (['DebeP', 'HaberP', 'SaldoP', 'DebeA', 'HaberA', 'SaldoA', 'correccion'] as $field) {
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

        $this->rows = $allData->keyBy('id')->toArray();
    }

    public function updated($property, $value)
    {
        if ($property === 'month' || $property === 'year') {
            $this->loadData();
            $this->updateChartData();
            $this->dispatch('chartUpdated', [
                'labels' => $this->chartLabels,
                'real' => $this->chartReal,
                'budget' => $this->chartBudget
            ]);
        }
    }

    public function updateChartData()
    {
        if ($this->categoria !== 'ventas') return;

        $query = Balance::where('is_summary', false)->where('Grupo', '1');

        if ($this->year !== 'Todos') {
            $query->where('año', $this->year);
        }

        if ($this->month !== 'Todos') {
            $query->where('mes', $this->month);
        }

        $chartData = $query->select('Descripcion', 'SaldoP', 'correccion', 'presupuesto')
            ->get()
            ->groupBy('Descripcion')
            ->map(function($group) {
                return [
                    'real' => abs($group->sum('corrected_saldo_float')),
                    'budget' => abs($group->sum(fn($item) => (float)$item->presupuesto))
                ];
            });

        $this->chartLabels = $chartData->keys()->toArray();
        $this->chartReal = $chartData->pluck('real')->toArray();
        $this->chartBudget = $chartData->pluck('budget')->toArray();
    }

    public function getCategoryNameProperty()
    {
        return match ($this->categoria) {
            'ventas' => 'Ventas',
            'compras' => 'Compras',
            'salarios' => 'Salarios',
            'otros-gastos', 'otros_gastos', 'otrosgastos' => 'Otros Gastos',
            'financieros' => 'Financieros',
            'resultado' => 'Resultado',
            default => ucfirst($this->categoria)
        };
    }

    public function render()
    {
        return view('livewire.category-viewer');
    }
}
