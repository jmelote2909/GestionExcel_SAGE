<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Balance;

#[Layout('components.layouts.app')]
class BudgetManager extends Component
{
    public $year;
    public $descriptions = [];
    public $budgets = []; // [description => [month => value]]
    public $reals = [];   // [description => [month => value]]
    
    public $months = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    public $totalAnnualBudget = 0;
    public $totalAnnualReal = 0;
    public $averageCompliance = 0;
    public $years = [];

    public function mount()
    {
        $this->year = (string)date('Y');
        $this->refreshYears();
        $this->loadData();
    }

    public function refreshYears()
    {
        $dbYears = Balance::distinct()->pluck('año')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->toArray();
        
        $currentYear = (int)$this->year;
        
        // Only show years with data + the year we are currently viewing
        $allYears = array_unique(array_merge($dbYears, [$currentYear, (int)date('Y')]));
        sort($allYears);
        
        $this->years = array_map('strval', $allYears);
    }

    public function nextYear()
    {
        $this->year = (string)((int)$this->year + 1);
        $this->refreshYears();
        $this->loadData();
    }

    public function prevYear()
    {
        $this->year = (string)((int)$this->year - 1);
        $this->refreshYears();
        $this->loadData();
    }

    public function loadData()
    {
        // Get all Sales descriptions (Grupo 1) ever recorded
        $this->descriptions = Balance::where('Grupo', '1')
            ->distinct()
            ->pluck('Descripcion')
            ->filter()
            ->toArray();

        // Get budgets and real values for the selected year
        $records = Balance::where('año', $this->year)
            ->where('Grupo', '1')
            ->get();

        $this->budgets = [];
        $this->reals = [];
        $this->totalAnnualBudget = 0;
        $this->totalAnnualReal = 0;

        foreach ($this->descriptions as $desc) {
            $desc = trim($desc);
            foreach ($this->months as $month) {
                $month = trim($month);
                $matches = $records->where('Descripcion', $desc)->where('mes', $month);
                
                $budget = (float)$matches->sum('presupuesto');
                $real = abs($matches->sum('corrected_saldo_float'));
                
                $this->budgets[$desc][$month] = $budget;
                $this->reals[$desc][$month] = $real;
                
                $this->totalAnnualBudget += $budget;
                $this->totalAnnualReal += $real;
            }
        }

        $this->averageCompliance = $this->totalAnnualBudget > 0 
            ? ($this->totalAnnualReal / $this->totalAnnualBudget) * 100 
            : 0;
    }

    public function updatedYear()
    {
        $this->loadData();
    }

    public function saveBudget($desc, $month, $value)
    {
        // Strip dots (thousands separator) and replace comma with dot (decimal)
        $cleanValue = str_replace('.', '', $value);
        $cleanValue = str_replace(',', '.', $cleanValue);
        
        if (!is_numeric($cleanValue)) $cleanValue = 0;

        // Find or create the record
        Balance::updateOrCreate(
            [
                'Descripcion' => trim($desc),
                'mes' => trim($month),
                'año' => trim($this->year),
                'Grupo' => '1',
                'is_summary' => false
            ],
            [
                'presupuesto' => (float)$cleanValue
            ]
        );

        session()->flash('message', "Presupuesto actualizado para $month.");
        $this->refreshYears();
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.budget-manager');
    }
}
