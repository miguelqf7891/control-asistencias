@extends('layouts.app')

@section('title', 'Registro de Asistencias')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">📊 Registro de Asistencias</h2>

        <!-- Filtros -->
        <form method="GET" action="{{ route('attendance.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-bold mb-1">Empleado</label>
                <select name="employee_id" class="w-full border rounded p-2">
                    <option value="">Todos</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1">Fecha desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-bold mb-1">Fecha hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-bold mb-1">Estado</label>
                <select name="status" class="w-full border rounded p-2">
                    <option value="">Todos</option>
                    <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>✅ A tiempo</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>⚠️ Tardanza</option>
                    <option value="early_exit" {{ request('status') == 'early_exit' ? 'selected' : '' }}>🏃 Salida temprana</option>
                    <option value="lunch_break" {{ request('status') == 'lunch_break' ? 'selected' : '' }}>🍽️ Almuerzo</option>
                </select>
            </div>

            <div class="md:col-span-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🔍 Filtrar</button>
                <a href="{{ route('attendance.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 ml-2">🔄 Limpiar</a>
            </div>
        </form>

        <!-- Tabla de registros -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-2 px-4 border">Fecha/Hora</th>
                        <th class="py-2 px-4 border">Empleado</th>
                        <th class="py-2 px-4 border">N° Empleado</th>
                        <th class="py-2 px-4 border">Turno</th>
                        <th class="py-2 px-4 border">Tipo</th>
                        <th class="py-2 px-4 border">Estado</th>
                        <th class="py-2 px-4 border">Tardanza</th>
                        <th class="py-2 px-4 border">Salida Temprana</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $schedule = $record->employee->getSchedule();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border">{{ $record->check_time->format('d/m/Y H:i:s') }}</td>
                            <td class="py-2 px-4 border">{{ $record->employee->name }}</td>
                            <td class="py-2 px-4 border">{{ $record->employee->employee_number }}</td>
                            <td class="py-2 px-4 border">{{ $schedule['name'] }}</td>

                            {{-- Tipo con ícono --}}
                            <td class="py-2 px-4 border text-center">
                                @if($record->type == 'entry')
                                    <span class="text-green-600 font-bold">🚪 Entrada</span>
                                @elseif($record->type == 'exit')
                                    <span class="text-red-600 font-bold">🏠 Salida</span>
                                @elseif($record->type == 'lunch')
                                    <span class="text-blue-600 font-bold">🍽️ Almuerzo</span>
                                @else
                                    <span class="text-gray-600">{{ $record->type }}</span>
                                @endif
                            </td>

                            {{-- Estado con ícono --}}
                            <td class="py-2 px-4 border text-center">
                                @if($record->status == 'on_time')
                                    <span class="text-green-600">✅ A tiempo</span>
                                @elseif($record->status == 'late')
                                    <span class="text-yellow-600">⚠️ Tardanza</span>
                                @elseif($record->status == 'early_exit')
                                    <span class="text-orange-600">🏃 Salida temprana</span>
                                @elseif($record->status == 'lunch_break')
                                    <span class="text-blue-600">🍽️ Almuerzo</span>
                                @else
                                    <span class="text-gray-600">{{ $record->status }}</span>
                                @endif
                            </td>

                            {{-- Tardanza --}}
                            <td class="py-2 px-4 border text-center">
                                @if($record->late_minutes > 0)
                                    <span class="text-red-600">{{ $record->formatted_late_minutes }}</span>
                                @else
                                    <span class="text-green-600">-</span>
                                @endif
                            </td>

                            {{-- Salida Temprana --}}
                            <td class="py-2 px-4 border text-center">
                                @if($record->early_exit_minutes > 0)
                                    <span class="text-orange-600">{{ $record->formatted_early_exit }}</span>
                                @else
                                    <span class="text-green-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-gray-500">No hay registros de asistencia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-4">
            {{ $records->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
