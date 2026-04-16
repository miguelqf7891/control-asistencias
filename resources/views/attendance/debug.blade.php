@extends('layouts.app')

@section('title', 'Depuración de Datos')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">🔍 Depuración - Empleados y Asistencias</h2>

        <h3 class="text-xl font-bold mt-4 mb-2">Empleados registrados:</h3>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-2 px-4 border">ID</th>
                        <th class="py-2 px-4 border">N° Empleado</th>
                        <th class="py-2 px-4 border">Nombre</th>
                        <th class="py-2 px-4 border">Departamento</th>
                        <th class="py-2 px-4 border">Registros</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td class="py-2 px-4 border">{{ $employee->id }}</td>
                        <td class="py-2 px-4 border">{{ $employee->employee_number }}</td>
                        <td class="py-2 px-4 border">{{ $employee->name }}</td>
                        <td class="py-2 px-4 border">{{ $employee->department }}</td>
                        <td class="py-2 px-4 border">{{ $employee->attendance_records_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="text-xl font-bold mt-4 mb-2">Últimos registros de asistencia:</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-2 px-4 border">Fecha/Hora</th>
                        <th class="py-2 px-4 border">Empleado</th>
                        <th class="py-2 px-4 border">Tipo</th>
                        <th class="py-2 px-4 border">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td class="py-2 px-4 border">{{ $record->check_time->format('d/m/Y H:i:s') }}</td>
                        <td class="py-2 px-4 border">{{ $record->employee->name }}</td>
                        <td class="py-2 px-4 border">{{ $record->type }}</td>
                        <td class="py-2 px-4 border">{{ $record->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
