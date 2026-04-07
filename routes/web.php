<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ExcelViewer;

use App\Livewire\Dashboard;
use App\Livewire\CategoryViewer;
use App\Livewire\BudgetManager;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/importar', ExcelViewer::class)->name('importar');
Route::get('/categoria/{category}', CategoryViewer::class)->name('category.show');
Route::get('/presupuestos', BudgetManager::class)->name('presupuestos');
