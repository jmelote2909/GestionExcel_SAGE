<div class="h-full flex flex-col font-sans text-gray-800">
    <div class="bg-indigo-600 text-white shadow-md p-4 flex items-center justify-between sticky top-0 z-50">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Detalle de {{ $this->categoryName }}
            </h1>
            <p class="text-indigo-200 text-sm">Desglose de registros por categoría</p>
        </div>
        <div class="flex gap-4 items-center">
            <a href="{{ route('dashboard') }}" class="bg-indigo-500 hover:bg-indigo-400 px-4 py-2 rounded-md shadow transition-all font-medium flex items-center gap-2 text-white text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="p-6 max-w-[95%] mx-auto w-full flex-1 overflow-y-auto custom-scrollbar relative">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 flex flex-wrap gap-6 items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 p-2.5 rounded-lg text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-0.5">Filtros Activos</p>
                    <h3 class="font-bold text-gray-800 text-lg">Personaliza la vista</h3>
                </div>
            </div>

            @if($hasData)
                <div class="flex items-center gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Año</label>
                        <select wire:model.live="year" class="border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border px-3 py-2 text-sm bg-white cursor-pointer hover:border-indigo-400 transition-colors w-32">
                            @foreach($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Mes</label>
                        <select wire:model.live="month" class="border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border px-3 py-2 text-sm bg-white cursor-pointer hover:border-indigo-400 transition-colors w-32">
                            @foreach($months as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Empresa</label>
                        <select wire:model.live="empresa" class="border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border px-3 py-2 text-sm bg-white cursor-pointer hover:border-indigo-400 transition-colors w-32">
                            @foreach($empresas as $emp)
                                <option value="{{ $emp }}">{{ $emp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>

        @if($categoria === 'ventas')
            <!-- Sales vs Budget Chart -->
            <div class="bg-white p-8 rounded-2xl border border-gray-200 shadow-sm mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Gráfica de Ventas por Línea vs Presupuesto</h3>
                        <p class="text-sm text-gray-500">Comparativa de ventas reales frente a objetivos presupuestados para este periodo</p>
                    </div>
                </div>
                <div class="h-[400px] w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('livewire:initialized', () => {
                    const ctx = document.getElementById('salesChart');
                    let chart;

                    function initChart(chartLabels = @json($chartLabels), chartBudget = @json($chartBudget), chartReal = @json($chartReal)) {
                        if (chart) chart.destroy();
                        
                        chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartLabels,
                                datasets: [
                                    {
                                        label: 'Presupuesto',
                                        data: chartBudget,
                                        backgroundColor: '#1e3a8a', // Azul oscuro
                                        borderRadius: 4,
                                        barPercentage: 0.8,
                                        categoryPercentage: 0.6
                                    },
                                    {
                                        label: 'Real',
                                        data: chartReal,
                                        backgroundColor: '#3b82f6', // Azul claro/normal
                                        borderRadius: 4,
                                        barPercentage: 0.8,
                                        categoryPercentage: 0.6
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                            font: {
                                                size: 12,
                                                weight: 'bold'
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                        titleColor: '#1e293b',
                                        bodyColor: '#475569',
                                        borderColor: '#e2e8f0',
                                        borderWidth: 1,
                                        padding: 12,
                                        displayColors: true,
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) label += ': ';
                                                if (context.parsed.y !== null) {
                                                    label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(context.parsed.y);
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            drawBorder: false,
                                            color: '#f1f5f9'
                                        },
                                        ticks: {
                                            font: { size: 11 },
                                            callback: function(value) {
                                                return new Intl.NumberFormat('es-ES', { notation: 'compact', compactDisplay: 'short' }).format(value) + ' €';
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    }

                    initChart();

                    Livewire.on('chartUpdated', (data) => {
                        const chartData = Array.isArray(data) ? data[0] : data;
                        initChart(chartData.labels, chartData.budget, chartData.real);
                    });
                });
            </script>
        @endif

        @if($hasData)
            <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                <div class="p-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-medium">Mostrando {{ count($rows) }} filas de datos para <strong>{{ $this->categoryName }}</strong></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 relative">
                        <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                            <tr>
                                @foreach($columns as $col)
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">
                                        {{ $col }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($rows as $id => $row)
                                <tr class="hover:bg-indigo-50/50 group transition-colors">
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $row['Cuenta'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800">{{ $row['Descripcion'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800 text-right">{{ $row['SaldoP'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800">{{ $row['correccion'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-indigo-600 text-right bg-indigo-50/30">{{ $row['saldo_final_formatted'] ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="px-2 py-1 text-sm bg-gray-100 text-gray-600 rounded font-bold w-12 text-center select-none shadow-inner border border-gray-200">
                                            {{ $row['Grupo'] ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800">{{ $row['empresa'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800">{{ $row['mes'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800">{{ $row['año'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) }}" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        No hay registros que coincidan con la categoría y periodo seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100 mt-8 opacity-70">
                <div class="bg-indigo-50 p-4 rounded-full mb-4">
                    <svg class="h-12 w-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800">No hay datos en el sistema</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-sm text-center">Por favor sube un archivo Excel desde el Gestor para poder visualizar las categorías.</p>
            </div>
        @endif
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
</div>
