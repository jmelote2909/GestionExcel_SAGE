<div class="h-full flex flex-col min-h-0">
    <!-- Header Matching excel-viewer -->
    <header class="bg-indigo-600 text-white shadow-md p-4 flex items-center justify-between z-30 shrink-0">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                Cuadro de Mando
            </h2>
            <p class="text-indigo-200 text-sm">Control económico empresarial</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- Nota button has been removed -->
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar">
        
        <div class="max-w-[1600px] mx-auto space-y-8">
            <!-- Filter bar -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-600 text-white p-3 rounded-xl shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-0.5">Control de Periodo</p>
                        <h3 class="font-extrabold text-gray-800 text-xl">Filtro Inteligente</h3>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-6">
                    <div class="min-w-[140px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Año</label>
                        <select wire:model.live="selectedYear" class="w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm border px-4 py-2.5 text-sm bg-gray-50/50 cursor-pointer hover:bg-white transition-all">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[140px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Mes</label>
                        <select wire:model.live="selectedMonth" class="w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm border px-4 py-2.5 text-sm bg-gray-50/50 cursor-pointer hover:bg-white transition-all">
                            @foreach($months as $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[140px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Empresa</label>
                        <select wire:model.live="selectedEmpresa" class="w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm border px-4 py-2.5 text-sm bg-gray-50/50 cursor-pointer hover:bg-white transition-all">
                            @foreach($empresas as $emp)
                                <option value="{{ $emp }}">{{ $emp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                
                <!-- Ventas -->
                <a href="{{ route('category.show', ['category' => 'ventas', 'year' => $selectedYear, 'month' => $selectedMonth, 'empresa' => $selectedEmpresa]) }}" 
                   class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    <div class="bg-blue-500 text-white p-4 rounded-2xl shadow-lg ring-4 ring-blue-50 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <h4 class="text-xs text-gray-400 font-bold tracking-widest uppercase mb-2">VENTAS</h4>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($ventas, 2, ',', '.') }} €</p>
                    <div class="mt-4 text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ver detalle →</div>
                </a>

                <!-- Compras -->
                <a href="{{ route('category.show', ['category' => 'compras', 'year' => $selectedYear, 'month' => $selectedMonth, 'empresa' => $selectedEmpresa]) }}" 
                   class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    <div class="bg-orange-500 text-white p-4 rounded-2xl shadow-lg ring-4 ring-orange-50 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h4 class="text-xs text-gray-400 font-bold tracking-widest uppercase mb-2">COMPRAS</h4>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($compras, 2, ',', '.') }} €</p>
                    <div class="mt-4 text-xs font-medium text-orange-600 bg-orange-50 px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ver detalle →</div>
                </a>

                <!-- Salarios -->
                <a href="{{ route('category.show', ['category' => 'salarios', 'year' => $selectedYear, 'month' => $selectedMonth, 'empresa' => $selectedEmpresa]) }}" 
                   class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    <div class="bg-purple-500 text-white p-4 rounded-2xl shadow-lg ring-4 ring-purple-50 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h4 class="text-xs text-gray-400 font-bold tracking-widest uppercase mb-2">SALARIOS</h4>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($salarios, 2, ',', '.') }} €</p>
                    <div class="mt-4 text-xs font-medium text-purple-600 bg-purple-50 px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ver detalle →</div>
                </a>

                <!-- Otros Gastos -->
                <a href="{{ route('category.show', ['category' => 'otros-gastos', 'year' => $selectedYear, 'month' => $selectedMonth, 'empresa' => $selectedEmpresa]) }}" 
                   class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    <div class="bg-red-500 text-white p-4 rounded-2xl shadow-lg ring-4 ring-red-50 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h4 class="text-xs text-gray-400 font-bold tracking-widest uppercase mb-2">OTROS GASTOS</h4>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($otrosGastos, 2, ',', '.') }} €</p>
                    <div class="mt-4 text-xs font-medium text-red-600 bg-red-50 px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ver detalle →</div>
                </a>

                <!-- Financieros -->
                <a href="{{ route('category.show', ['category' => 'financieros', 'year' => $selectedYear, 'month' => $selectedMonth, 'empresa' => $selectedEmpresa]) }}" 
                   class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    <div class="bg-sky-500 text-white p-4 rounded-2xl shadow-lg ring-4 ring-sky-50 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h4 class="text-xs text-gray-400 font-bold tracking-widest uppercase mb-2">FINANCIEROS</h4>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($financieros, 2, ',', '.') }} €</p>
                    <div class="mt-4 text-xs font-medium text-sky-600 bg-sky-50 px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ver detalle →</div>
                </a>

                <!-- Resultado -->
                <a href="{{ route('category.show', ['category' => 'resultado', 'year' => $selectedYear, 'month' => $selectedMonth, 'empresa' => $selectedEmpresa]) }}" 
                   class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    <div class="bg-emerald-500 text-white p-4 rounded-2xl shadow-lg ring-4 ring-emerald-50 mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-xs text-gray-400 font-bold tracking-widest uppercase mb-2">RESULTADO</h4>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($resultado, 2, ',', '.') }} €</p>
                    <div class="mt-4 text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ver detalle →</div>
                </a>
            </div>

            <!-- Summary Chart: Sales vs Budget -->
            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -z-10 opacity-50"></div>
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 tracking-tight">Ventas vs Objetivos</h3>
                        <p class="text-sm text-gray-400 font-medium">Cumplimiento global del presupuesto en el periodo seleccionado</p>
                    </div>
                </div>
                
                <div class="h-[350px] w-full">
                    <canvas id="summaryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const ctx = document.getElementById('summaryChart');
            let chart;

            function initChart(ventas = {{ $chartData['ventas'] }}, presupuesto = {{ $chartData['presupuesto'] }}) {
                if (chart) chart.destroy();
                
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Totales del Periodo'],
                        datasets: [
                            {
                                label: 'Presupuesto Total',
                                data: [presupuesto],
                                backgroundColor: '#1e3a8a',
                                borderRadius: 8,
                                barPercentage: 0.5,
                            },
                            {
                                label: 'Ventas Reales',
                                data: [ventas],
                                backgroundColor: '#3b82f6',
                                borderRadius: 8,
                                barPercentage: 0.5,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.x !== null) {
                                            label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(context.parsed.x);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('es-ES', { notation: 'compact', compactDisplay: 'short' }).format(value) + ' €';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            initChart();

            Livewire.on('summaryChartUpdated', () => {
                // We use @this to get the data as it's just two simple values
                initChart(@this.chartData.ventas, @this.chartData.presupuesto);
            });
        });
    </script>
</div>
