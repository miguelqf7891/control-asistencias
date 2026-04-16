@extends('layouts.app')

@section('title', 'Importar CSV')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">📂 Importar archivo CSV</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 whitespace-pre-line">
                {!! session('success') !!}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('import.csv.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Archivo CSV</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full border rounded p-2">
                <p class="text-sm text-gray-500 mt-1">Formato: Department,Name,No.,Date/Time,Location ID,ID Number,VerifyCode,CardNo</p>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-upload"></i> Importar
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded">
            <h3 class="font-bold mb-2">📌 Instrucciones:</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
                <li>El archivo debe estar en formato CSV (separado por comas)</li>
                <li>Primera fila debe contener los encabezados</li>
                <li>Fecha/hora en formato: D/M/AAAA HH:MM</li>
                <li>Se usar la columna "No." como identificación del empleado</li>
                <li>Tolerancia de 15 minutos para entrada y salida</li>
            </ul>
        </div>

        @php
            $totalEmployees = \App\Models\Employee::count();
            $totalRecords = \App\Models\AttendanceRecord::count();
        @endphp
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-3 rounded">
                <div class="text-sm text-blue-800">Empleados registrados</div>
                <div class="text-xl font-bold">{{ $totalEmployees }}</div>
            </div>
            <div class="bg-green-50 p-3 rounded">
                <div class="text-sm text-green-800">Registros de asistencia</div>
                <div class="text-xl font-bold">{{ $totalRecords }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
