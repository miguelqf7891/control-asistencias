@extends('layouts.app')

@section('title', 'Gestión de Horarios')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">👥 Gestión de Horarios - Empleados</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-2 px-4 border">N° Empleado</th>
                        <th class="py-2 px-4 border">Nombre</th>
                        <th class="py-2 px-4 border">Turno Actual</th>
                        <th class="py-2 px-4 border">Horario</th>
                        <th class="py-2 px-4 border">Descanso</th>
                        <th class="py-2 px-4 border">Registros</th>
                        <th class="py-2 px-4 border">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    @php $schedule = $employee->getSchedule(); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border">{{ $employee->employee_number }}</td>
                        <td class="py-2 px-4 border">{{ $employee->name }}</td>
                        <td class="py-2 px-4 border">{{ $schedule['name'] }}</td>
                        <td class="py-2 px-4 border">{{ $schedule['start'] }} - {{ $schedule['end'] }}</td>
                        <td class="py-2 px-4 border">{{ $schedule['break'] }} minutos</td>
                        <td class="py-2 px-4 border">{{ $employee->attendance_records_count }}</td>
                        <td class="py-2 px-4 border">
                            <a href="{{ route('employees.shifts.edit', $employee) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">
                                ⏰ Configurar Horario
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
