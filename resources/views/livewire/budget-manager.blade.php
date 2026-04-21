<div class="flex flex-col h-full bg-gray-50/50 overflow-hidden">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-8 py-6 shrink-0 z-20 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Presupuestos</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Planificación anual de objetivos de venta</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center bg-white border border-gray-200 rounded-xl shadow-sm p-1">
                    <button wire:click="prevYear" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    
                    <div class="px-4 py-1.5 flex flex-col items-center min-w-[100px]">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Año Seleccionado</span>
                        <span class="text-lg font-black text-indigo-900 border-none p-0 focus:ring-0">{{ $year }}</span>
                    </div>

                    <button wire:click="nextYear" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

            </div>
        </div>
    </header>
    
    @if (session()->has('error'))
        <div class="px-8 mt-4">
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-6 py-4 rounded-2xl flex items-center gap-4">
                <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div class="font-bold text-sm">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 rounded-2xl shadow-lg border border-indigo-500/20 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-indigo-100 text-xs font-bold uppercase tracking-wider mb-2">Presupuesto Total Anual</p>
                    <h3 class="text-3xl font-black">{{ number_format($totalAnnualBudget, 0, ',', '.') }} €</h3>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-10">
                    <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Ventas Reales Acumuladas</p>
                <h3 class="text-3xl font-black text-gray-900">{{ number_format($totalAnnualReal, 0, ',', '.') }} €</h3>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Cumplimiento General</p>
                    <h3 class="text-3xl font-black {{ $averageCompliance >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ number_format($averageCompliance, 1, ',', '.') }}%
                    </h3>
                </div>
                <div class="w-full bg-gray-100 h-2 rounded-full mt-4 overflow-hidden">
                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000" style="width: {{ min(100, $averageCompliance) }}%"></div>
                </div>
            </div>
        </div>

        @if(empty($descriptions))
            <div class="bg-white rounded-2xl p-20 text-center border-2 border-dashed border-gray-200">
                <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">No hay datos de ventas</h3>
                <p class="text-gray-500 max-w-xs mx-auto mt-2">Importa un archivo Excel primero para generar las categorías de venta.</p>
            </div>
        @endif

        <!-- Category Cards -->
        <div class="space-y-8">
            @foreach($descriptions as $desc)
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden group hover:border-indigo-300 transition-all duration-300">
                    <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-indigo-500 rounded-full"></div>
                            <h3 class="text-lg font-bold text-gray-800">{{ $desc }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Total Anual</p>
                            <p class="text-sm font-black text-indigo-600">
                                {{ number_format(array_sum($budgets[$desc] ?? []), 0, ',', '.') }} €
                            </p>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <div class="grid grid-cols-2 lg:grid-cols-6 gap-6">
                            @foreach($months as $month)
                                @php
                                    $budget = $budgets[$desc][$month] ?? 0;
                                    $real = $reals[$desc][$month] ?? 0;
                                    $compliance = $budget > 0 ? ($real / $budget) * 100 : 0;
                                    $statusColor = $compliance >= 100 ? 'emerald' : ($compliance >= 80 ? 'amber' : 'rose');
                                @endphp
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 hover:bg-white hover:shadow-md hover:border-indigo-200 transition-all group/cell">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $month }}</span>
                                        @if($real > 0)
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                                                {{ round($compliance) }}%
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            value="{{ number_format($budget, 0, ',', '.') }}"
                                            wire:blur="saveBudget('{{ addslashes($desc) }}', '{{ addslashes($month) }}', $event.target.value)"
                                            class="w-full bg-transparent border-none p-0 text-lg font-black text-gray-900 focus:ring-0 placeholder-gray-300"
                                            placeholder="0"
                                        >
                                        <div class="text-[10px] text-gray-400 font-medium mt-1">
                                            Ventas: {{ number_format($real, 0, ',', '.') }} €
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-8 right-8 z-50">
            <div class="bg-gray-900 border border-gray-800 text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif
</div>
