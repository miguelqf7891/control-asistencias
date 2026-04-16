@extends('layouts.app')

@section('title', 'Reporte de Asistencias')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">📈 Reporte de Asistencias</h2>

        <!-- Formulario para reporte normal -->
        <form method="GET" action="{{ route('attendance.report') }}" class="mb-6 p-4 bg-gray-50 rounded-lg border">
            <h3 class="font-bold text-lg mb-3">📊 Reporte por Rango de Fechas</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Fecha desde *</label>
                    <input type="date" name="date_from" required value="{{ request('date_from') }}" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">Fecha hasta *</label>
                    <input type="date" name="date_to" required value="{{ request('date_to') }}" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">Empleado</label>
                    <select name="employee_id" class="w-full border rounded p-2">
                        <option value="">Todos los empleados</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">📊 Generar Reporte</button>

                    @if(isset($records) && $records->count() > 0)
                    <a href="{{ route('attendance.report.export', request()->all()) }}"
                       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m-6 4H5a2 2 0 01-2-2V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2h-3"></path>
                        </svg>
                        Exportar a Excel
                    </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Formulario para reporte mensual salud -->
        <form method="GET" action="{{ route('attendance.monthly.export') }}" class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
            <h3 class="font-bold text-lg mb-3 text-green-800">🏥 Reporte Mensual - RED DE SERVICIOS DE SALUD CUSCO SUR</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Seleccionar Mes *</label>
                    <input type="month" name="month" required value="{{ request('month', date('Y-m')) }}" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">Empleado (Opcional)</label>
                    <select name="employee_id" class="w-full border rounded p-2">
                        <option value="">Todos los empleados</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->profesion ?? 'Sin profesión' }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Si selecciona un empleado, generará un solo reporte. Si no, generará un reporte por cada empleado.</p>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">
                        📋 Exportar Reporte Mensual Salud
                    </button>
                </div>
            </div>
        </form>

        @if(isset($records) && $records->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-100 p-4 rounded">
                <div class="text-sm text-blue-800">Total registros</div>
                <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-green-100 p-4 rounded">
                <div class="text-sm text-green-800">A tiempo</div>
                <div class="text-2xl font-bold">{{ $stats['on_time'] }}</div>
                <div class="text-xs">{{ round(($stats['on_time']/$stats['total'])*100, 1) }}%</div>
            </div>
            <div class="bg-yellow-100 p-4 rounded">
                <div class="text-sm text-yellow-800">Tardanzas</div>
                <div class="text-2xl font-bold">{{ $stats['late'] }}</div>
                <div class="text-xs">{{ round(($stats['late']/$stats['total'])*100, 1) }}%</div>
            </div>
            <div class="bg-orange-100 p-4 rounded">
                <div class="text-sm text-orange-800">Salidas tempranas</div>
                <div class="text-2xl font-bold">{{ $stats['early_exit'] }}</div>
                <div class="text-xs">{{ round(($stats['early_exit']/$stats['total'])*100, 1) }}%</div>
            </div>
            <div class="bg-red-100 p-4 rounded">
                <div class="text-sm text-red-800">Minutos totales</div>
                <div class="text-2xl font-bold">{{ $stats['total_late_minutes'] + $stats['total_early_minutes'] }}</div>
                <div class="text-xs">Tardanza: {{ $stats['total_late_minutes'] }} min</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-2 px-4 border">Fecha</th>
                        <th class="py-2 px-4 border">Empleado</th>
                        <th class="py-2 px-4 border">Profesión</th>
                        <th class="py-2 px-4 border">Condición</th>
                        <th class="py-2 px-4 border">Turno</th>
                        <th class="py-2 px-4 border">🚪 Entrada</th>
                        <th class="py-2 px-4 border">🍽️ Salida Almuerzo</th>
                        <th class="py-2 px-4 border">🍽️ Retorno Almuerzo</th>
                        <th class="py-2 px-4 border">🏠 Salida</th>
                        <th class="py-2 px-4 border">⏰ Tardanza</th>
                        <th class="py-2 px-4 border">🏃 Salida Temprana</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Agrupar por empleado y fecha
                        $grouped = $records->groupBy(function($item) {
                            return $item->employee_id . '_' . $item->check_time->format('Y-m-d');
                        });
                    @endphp

                    @foreach($grouped as $group)
                        @php
                            // Separar registros por tipo
                            $entry = $group->where('type', 'entry')->first();
                            $exit = $group->where('type', 'exit')->first();

                            // Registros de almuerzo (ordenados por tiempo)
                            $lunchRecords = $group->where('type', 'lunch')->sortBy('check_time')->values();
                            $lunchOut = $lunchRecords->first();  // Salida a almuerzo
                            $lunchIn = $lunchRecords->count() > 1 ? $lunchRecords->last() : null;  // Retorno de almuerzo

                            $employee = $entry ? $entry->employee : ($exit ? $exit->employee : ($lunchOut ? $lunchOut->employee : null));
                            $date = $entry ? $entry->check_time->format('d/m/Y') : ($exit ? $exit->check_time->format('d/m/Y') : ($lunchOut ? $lunchOut->check_time->format('d/m/Y') : ''));
                            $schedule = $employee ? $employee->getSchedule() : [];
                        @endphp

                        @if($employee)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border text-center">{{ $date }}</td>
                            <td class="py-2 px-4 border">{{ $employee->name ?? 'N/A' }}</td>
                            <td class="py-2 px-4 border text-center">
                                @if($employee->profesion)
                                    <span class="text-purple-600 font-medium">{{ $employee->profesion }}</span>
                                @else
                                    <span class="text-gray-400">---</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border text-center">
                                @if($employee->condicion)
                                    <span class="text-indigo-600 font-medium">{{ $employee->condicion }}</span>
                                @else
                                    <span class="text-gray-400">---</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border">{{ $schedule['name'] ?? 'N/A' }}</td>

                            {{-- ENTRADA --}}
                            <td class="py-2 px-4 border text-center">
                                @if($entry)
                                    <span class="text-green-600 font-bold">{{ $entry->check_time->format('H:i:s') }}</span>
                                @else
                                    <span class="text-gray-400">---</span>
                                @endif
                            </td>

                            {{-- SALIDA A ALMUERZO --}}
                            <td class="py-2 px-4 border text-center">
                                @if($lunchOut)
                                    <span class="text-blue-600 font-bold">
                                        🍽️ {{ $lunchOut->check_time->format('H:i:s') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">---</span>
                                @endif
                            </td>

                            {{-- RETORNO DE ALMUERZO --}}
                            <td class="py-2 px-4 border text-center">
                                @if($lunchIn)
                                    <span class="text-blue-600 font-bold">
                                        🍽️ {{ $lunchIn->check_time->format('H:i:s') }}
                                    </span>
                                @elseif($lunchOut && !$lunchIn)
                                    <span class="text-red-600">⚠️ Sin retorno</span>
                                @else
                                    <span class="text-gray-400">---</span>
                                @endif
                            </td>

                            {{-- SALIDA FINAL --}}
                            <td class="py-2 px-4 border text-center">
                                @if($exit)
                                    <span class="text-red-600 font-bold">{{ $exit->check_time->format('H:i:s') }}</span>
                                @else
                                    <span class="text-gray-400">---</span>
                                @endif
                            </td>

                            {{-- TARDANZA --}}
                            <td class="py-2 px-4 border text-center">
                                @if($entry && $entry->late_minutes > 0)
                                    @php
                                        $lateHours = floor($entry->late_minutes / 60);
                                        $lateMinutes = $entry->late_minutes % 60;
                                    @endphp
                                    <span class="text-red-600 font-bold">
                                        @if($lateHours > 0)
                                            {{ $lateHours }}h {{ $lateMinutes }}min
                                        @else
                                            {{ $lateMinutes }} min
                                        @endif
                                    </span>
                                @else
                                    <span class="text-green-600">✓</span>
                                @endif
                            </td>

                            {{-- SALIDA TEMPRANA --}}
                            <td class="py-2 px-4 border text-center">
                                @if($exit && $exit->early_exit_minutes > 0)
                                    @php
                                        $earlyHours = floor($exit->early_exit_minutes / 60);
                                        $earlyMinutes = $exit->early_exit_minutes % 60;
                                    @endphp
                                    <span class="text-orange-600 font-bold">
                                        @if($earlyHours > 0)
                                            {{ $earlyHours }}h {{ $earlyMinutes }}min
                                        @else
                                            {{ $earlyMinutes }} min
                                        @endif
                                    </span>
                                @else
                                    <span class="text-green-600">✓</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-sm text-gray-600">
            <p>📌 <strong>Leyenda:</strong></p>
            <ul class="list-disc list-inside">
                <li>🍽️ <span class="text-blue-600">Salida/Retorno de Almuerzo</span> - Marcaciones entre 13:00 y 14:00</li>
                <li>🚪 <span class="text-green-600">Entrada</span> - Marcación de ingreso</li>
                <li>🏠 <span class="text-red-600">Salida</span> - Marcación de salida final</li>
                <li>⚠️ <span class="text-red-600">Sin retorno</span> - Marcó salida a almuerzo pero no retorno</li>
                <li>✓ <span class="text-green-600">A tiempo</span> - Sin tardanza ni salida temprana</li>
            </ul>
        </div>

        @elseif(request()->has('date_from') && request()->has('date_to'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            No hay registros en el periodo seleccionado
        </div>
        @endif
    </div>
</div>
@endsection
