<div class="h-full flex flex-col bg-gray-50/50 font-sans text-gray-800">
    <!-- Header -->
    <header class="bg-indigo-600 text-white shadow-md p-4 flex items-center justify-between z-30 shrink-0">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Gestor Excel SAGE
            </h2>
            <p class="text-indigo-200 text-sm ml-10">Visualización y gestión de balances</p>
        </div>
        <div class="flex items-center gap-4">
            @if(count($rows) > 0)
                <button wire:click="export" class="flex items-center gap-2 bg-indigo-500 hover:bg-indigo-400 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Exportar Excel
                </button>
            @endif
        </div>
    </header>

    <div class="p-6 max-w-[95%] mx-auto w-full flex-1 overflow-y-auto custom-scrollbar relative">
        @if ($errorMessage)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-6 shadow-sm" role="alert">
                <strong class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Error!
                </strong>
                <span class="block sm:inline ml-7">{{ $errorMessage }}</span>
                <button wire:click="$set('errorMessage', null)" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500 hover:text-red-700" role="button" xmlns="http://www.getty.org/svg" viewBox="0 0 20 20"><title>Cerrar</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </button>
            </div>
        @endif

        @if ($showImportReminder)
            <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm flex items-center gap-4" role="alert">
                <div class="bg-indigo-600 p-2 rounded-lg text-white flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-grow">
                    <h3 class="font-bold text-indigo-900 text-sm">¡Archivo importado correctamente!</h3>
                    <p class="text-indigo-700 text-sm">Recuerda que debes incluir las partidas <span class="font-bold underline">610</span> y <span class="font-bold underline">520</span>.</p>
                </div>
                <button wire:click="$set('showImportReminder', false)" class="ml-auto text-indigo-400 hover:text-indigo-800 p-1 rounded-lg hover:bg-indigo-100 transition-all" title="Cerrar">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 flex flex-wrap gap-6 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Subir archivo Excel (.xlsx)</label>
                <div class="flex items-center gap-2">
                    <input type="file" wire:model="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div wire:loading wire:target="file" class="text-indigo-600 text-sm animate-pulse flex items-center gap-2 font-medium">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Procesando...
                    </div>
                </div>
            </div>

            @if($hasData)
                <div class="ml-auto flex items-center gap-4">
                    <div class="flex gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrar por Año</label>
                            <select wire:model.live="selectedYear" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border px-3 py-2 text-sm bg-white cursor-pointer hover:border-indigo-400 transition-colors">
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrar por Mes</label>
                            <select wire:model.live="selectedMonth" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border px-3 py-2 text-sm bg-white cursor-pointer hover:border-indigo-400 transition-colors">
                                @foreach($months as $month)
                                    <option value="{{ $month }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($hasData)
            <div class="mb-4">
                <h2 class="text-sm font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Datos del último archivo importado:
                </h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 transition-all hover:shadow-md">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Empresa</p>
                    <p class="text-lg font-bold text-gray-800 truncate">{{ $metadata['empresa'] ?? 'N/A' }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 transition-all hover:shadow-md">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">NIF</p>
                    <p class="text-lg font-bold text-gray-800">{{ $metadata['nif'] ?? 'N/A' }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 transition-all hover:shadow-md">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Ejercicio</p>
                    <p class="text-lg font-bold text-gray-800">{{ $metadata['ejercicio'] ?? 'N/A' }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 transition-all hover:shadow-md">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Moneda</p>
                    <p class="text-lg font-bold text-gray-800">{{ $metadata['moneda'] ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                <div class="p-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-medium">Mostrando {{ count($rows) }} filas de datos</span>
                    <button wire:click="addRow" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all px-3 py-1.5 rounded-md text-sm font-bold flex items-center gap-1 border border-emerald-200">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Añadir Fila
                    </button>
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
                                <th scope="col" class="sticky right-0 px-3 py-3 bg-gray-100 z-20 w-12 border-l border-gray-200 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.05)]">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($rows as $id => $row)
                                <tr class="hover:bg-indigo-50/50 group transition-colors" wire:key="row-{{ $id }}">
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <input type="text" wire:model.live.debounce.150ms="rows.{{ $id }}.Cuenta" @keydown.enter="$el.blur()" class="w-28 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800 font-medium">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <input type="text" wire:model.live.debounce.500ms="rows.{{ $id }}.Descripcion" @keydown.enter="$el.blur()" class="w-64 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap relative">
                                        <input type="text" wire:model.live.debounce.500ms="rows.{{ $id }}.SaldoP" @keydown.enter="$el.blur()" class="w-24 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800 text-right">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <input type="text" wire:model.live.debounce.500ms="rows.{{ $id }}.correccion" @keydown.enter="$el.blur()" class="w-32 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-indigo-600 text-right bg-indigo-50/30 rounded">
                                        {{ $row['saldo_final_formatted'] ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <input type="text" wire:model.live.debounce.500ms="rows.{{ $id }}.presupuesto" @keydown.enter="$el.blur()" class="w-24 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800 text-right" placeholder="0,00">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <div class="px-2 py-1 text-sm bg-gray-100 text-gray-600 rounded font-bold w-12 text-center select-none shadow-inner border border-gray-200">
                                            {{ $row['Grupo'] ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <input type="text" wire:model.live.debounce.500ms="rows.{{ $id }}.empresa" @keydown.enter="$el.blur()" class="w-32 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <select wire:model.live="rows.{{ $id }}.mes" class="w-28 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm rounded bg-transparent hover:bg-white focus:bg-white py-1">
                                            <option value=""></option>
                                            @foreach(array_slice($months, 1) as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <input type="text" wire:model.live.debounce.500ms="rows.{{ $id }}.año" @keydown.enter="$el.blur()" class="w-20 border-transparent hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded text-sm px-2 py-1 bg-transparent hover:bg-white focus:bg-white transition-all text-gray-800">
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-right sticky right-0 bg-white group-hover:bg-indigo-50/50 border-l border-gray-100 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.02)]">
                                        <div class="flex items-center gap-1 justify-end">
                                            <!-- Success State (Explicitly set from server) -->
                                            @if(isset($savedRows[$id]) && $savedRows[$id])
                                                <span class="text-green-500" x-init="setTimeout(() => $wire.set('savedRows.{{ $id }}', false), 2000)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </span>
                                            @endif
                                            
                                            <!-- Syncing State (Livewire Loading) -->
                                            <div wire:loading wire:target="rows.{{ $id }}.Cuenta, rows.{{ $id }}.Descripcion, rows.{{ $id }}.SaldoP, rows.{{ $id }}.correccion, rows.{{ $id }}.presupuesto" class="text-indigo-400">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>

                                            <button wire:click="removeRow({{ $id }})" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md transition-all opacity-50 group-hover:opacity-100" title="Eliminar Fila">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
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
                <h3 class="text-xl font-bold text-gray-800">Ningún archivo cargado</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-sm text-center">Selecciona un archivo Excel (.xlsx) conteniendo el balance de sumas y saldos para comenzar.</p>
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
