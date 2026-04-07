<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor SAGE Excel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 selection:bg-indigo-300 selection:text-indigo-900 flex h-screen overflow-hidden text-gray-800">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col hidden lg:flex shadow-sm z-20 shrink-0">
        <!-- Sidebar Header -->
        <div class="p-6 border-b border-gray-100 bg-white">
            <h1 class="text-xl font-bold text-indigo-900 tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                SAGE Panel
            </h1>
            <p class="text-xs text-gray-500 mt-1 font-medium ml-8">Control Económico</p>
        </div>
        
        <!-- Sidebar Content -->
        <div class="flex-1 overflow-y-auto py-6 px-4">
            <nav class="space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('importar') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('importar') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Gestor Excel
                </a>
                <a href="{{ route('presupuestos') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('presupuestos') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Presupuestos
                </a>
                
                <div class="pt-4 mt-4 border-t border-gray-100"></div>

                <a href="{{ route('category.show', ['category' => 'ventas', 'year' => request('year', 'Todos'), 'month' => request('month', 'Todos')]) }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('category.show') && request('category') === 'ventas' ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                     <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Ventas
                </a>
                <a href="{{ route('category.show', ['category' => 'compras', 'year' => request('year', 'Todos'), 'month' => request('month', 'Todos')]) }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('category.show') && request('category') === 'compras' ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Compras
                </a>
                <a href="{{ route('category.show', ['category' => 'salarios', 'year' => request('year', 'Todos'), 'month' => request('month', 'Todos')]) }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('category.show') && request('category') === 'salarios' ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Salarios
                </a>
                <a href="{{ route('category.show', ['category' => 'otros-gastos', 'year' => request('year', 'Todos'), 'month' => request('month', 'Todos')]) }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('category.show') && request('category') === 'otros-gastos' ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Otros Gastos
                </a>
                <a href="{{ route('category.show', ['category' => 'financieros', 'year' => request('year', 'Todos'), 'month' => request('month', 'Todos')]) }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('category.show') && request('category') === 'financieros' ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Financieros
                </a>
                <a href="{{ route('category.show', ['category' => 'resultado', 'year' => request('year', 'Todos'), 'month' => request('month', 'Todos')]) }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('category.show') && request('category') === 'resultado' ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium' }} rounded-lg text-sm transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Resultado
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative z-10 w-full bg-gray-50/50">
        {{ $slot }}
    </main>

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
    @livewireScripts
</body>
</html>
