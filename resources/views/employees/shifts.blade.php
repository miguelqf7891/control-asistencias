@extends('layouts.app')

@section('title', 'Configurar Horario')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">⏰ Configurar Horario de Trabajo</h2>

        <form method="POST" action="{{ route('employees.shifts.update', $employee) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Empleado</label>
                <input type="text" value="{{ $employee->name }} ({{ $employee->employee_number }})" disabled class="w-full border rounded p-2 bg-gray-100">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Profesión</label>
                    <select name="profesion" class="w-full border rounded p-2">
                        <option value="">Seleccionar profesión</option>
                        <option value="Médico" {{ $employee->profesion == 'Médico' ? 'selected' : '' }}>Médico</option>
                        <option value="Enfermero" {{ $employee->profesion == 'Enfermero' ? 'selected' : '' }}>Enfermero</option>
                        <option value="Técnico en Enfermería" {{ $employee->profesion == 'Técnico en Enfermería' ? 'selected' : '' }}>Técnico en Enfermería</option>
                        <option value="Administrativo" {{ $employee->profesion == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                        <option value="Psicólogo" {{ $employee->profesion == 'Psicólogo' ? 'selected' : '' }}>Psicólogo</option>
                        <option value="Nutricionista" {{ $employee->profesion == 'Nutricionista' ? 'selected' : '' }}>Nutricionista</option>
                        <option value="Farmacéutico" {{ $employee->profesion == 'Farmacéutico' ? 'selected' : '' }}>Farmacéutico</option>
                        <option value="Odontólogo" {{ $employee->profesion == 'Odontólogo' ? 'selected' : '' }}>Odontólogo</option>
                        <option value="Obstetra" {{ $employee->profesion == 'Obstetra' ? 'selected' : '' }}>Obstetra</option>
                        <option value="Trabajador Social" {{ $employee->profesion == 'Trabajador Social' ? 'selected' : '' }}>Trabajador Social</option>
                        <option value="Personal de Limpieza" {{ $employee->profesion == 'Personal de Limpieza' ? 'selected' : '' }}>Personal de Limpieza</option>
                        <option value="Personal de Seguridad" {{ $employee->profesion == 'Personal de Seguridad' ? 'selected' : '' }}>Personal de Seguridad</option>
                        <option value="Conductor" {{ $employee->profesion == 'Conductor' ? 'selected' : '' }}>Conductor</option>
                        <option value="Otro" {{ $employee->profesion == 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Condición</label>
                    <select name="condicion" class="w-full border rounded p-2">
                        <option value="">Seleccionar condición</option>
                        <option value="Nombrado" {{ $employee->condicion == 'Nombrado' ? 'selected' : '' }}>Nombrado</option>
                        <option value="Contratado" {{ $employee->condicion == 'Contratado' ? 'selected' : '' }}>Contratado</option>
                        <option value="CAS" {{ $employee->condicion == 'CAS' ? 'selected' : '' }}>CAS</option>
                        <option value="Locador" {{ $employee->condicion == 'Locador' ? 'selected' : '' }}>Locador</option>
                        <option value="Practicante" {{ $employee->condicion == 'Practicante' ? 'selected' : '' }}>Practicante</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Tipo de Turno</label>
                <select name="shift_type" id="shift_type" class="w-full border rounded p-2" required>
                    <option value="full_8_20" {{ $employee->shift_type == 'full_8_20' ? 'selected' : '' }}>🏥 Turno Completo 8:00 - 20:00 (1 hora descanso)</option>
                    <option value="full_7_19" {{ $employee->shift_type == 'full_7_19' ? 'selected' : '' }}>🏥 Turno Completo 7:00 - 19:00 (1 hora descanso)</option>
                    <option value="morning_8_14" {{ $employee->shift_type == 'morning_8_14' ? 'selected' : '' }}>🌅 Turno Mañana 8:00 - 14:00 (15 min descanso)</option>
                    <option value="morning_7_13" {{ $employee->shift_type == 'morning_7_13' ? 'selected' : '' }}>🌅 Turno Mañana 7:00 - 13:00 (15 min descanso)</option>
                    <option value="afternoon_14_20" {{ $employee->shift_type == 'afternoon_14_20' ? 'selected' : '' }}>🌇 Turno Tarde 14:00 - 20:00 (15 min descanso)</option>
                    <option value="afternoon_13_19" {{ $employee->shift_type == 'afternoon_13_19' ? 'selected' : '' }}>🌇 Turno Tarde 13:00 - 19:00 (15 min descanso)</option>
                    <option value="night_20_8" {{ $employee->shift_type == 'night_20_8' ? 'selected' : '' }}>🌙 Turno Nocturno 20:00 - 08:00 (1 hora descanso)</option>
                    <option value="night_19_7" {{ $employee->shift_type == 'night_19_7' ? 'selected' : '' }}>🌙 Turno Nocturno 19:00 - 07:00 (1 hora descanso)</option>
                    <option value="custom" {{ $employee->shift_type == 'custom' ? 'selected' : '' }}>⚙️ Horario Personalizado</option>
                </select>
            </div>

            <div id="custom_hours" style="display: none;">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Hora de Entrada</label>
                        <input type="time" name="custom_start_time" value="{{ $employee->custom_start_time ? $employee->custom_start_time->format('H:i') : '' }}" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Hora de Salida</label>
                        <input type="time" name="custom_end_time" value="{{ $employee->custom_end_time ? $employee->custom_end_time->format('H:i') : '' }}" class="w-full border rounded p-2">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Minutos de Descanso</label>
                    <input type="number" name="break_minutes" value="{{ $employee->break_minutes }}" class="w-full border rounded p-2">
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('employees.shifts.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    💾 Guardar Horario
                </button>
            </div>
        </form>

        <div class="mt-6 p-4 bg-blue-50 rounded">
            <h4 class="font-bold text-blue-800 mb-2">ℹ️ Información importante:</h4>
            <ul class="text-sm text-blue-700 list-disc list-inside">
                <li>Tolerancia de 15 minutos para entrada y salida</li>
                <li>Las tardanzas se calculan después de los 15 minutos de tolerancia</li>
                <li>Las salidas tempranas se calculan antes de los 15 minutos de tolerancia</li>
                <li>Los turnos nocturnos cruzan la medianoche automáticamente</li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('shift_type').addEventListener('change', function() {
        var customDiv = document.getElementById('custom_hours');
        if (this.value === 'custom') {
            customDiv.style.display = 'block';
        } else {
            customDiv.style.display = 'none';
        }
    });

    if (document.getElementById('shift_type').value === 'custom') {
        document.getElementById('custom_hours').style.display = 'block';
    }
</script>
@endsection
