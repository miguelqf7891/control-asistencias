<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCsvController extends Controller
{
    public function index()
    {
        return view('import-csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');

        $header = fgetcsv($handle, 0, ',');

        $header = array_map(function($col) {
            return trim($col, "\xEF\xBB\xBF \t\n\r\0\x0B");
        }, $header);

        // Mapeo de columnas
        $departmentIndex = 0;
        $nameIndex = 1;
        $employeeNoIndex = 2;
        $dateTimeIndex = 3;
        $locationIdIndex = 4;
        $verifyCodeIndex = 6;

        $imported = 0;
        $errors = [];
        $employeesProcessed = [];
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;

                if (count($row) < 4 || empty(trim($row[$nameIndex] ?? ''))) {
                    continue;
                }

                $department = trim($row[$departmentIndex] ?? 'OUR COMPANY');
                $name = trim($row[$nameIndex] ?? '');
                $employeeNumber = trim($row[$employeeNoIndex] ?? '');
                $dateTimeStr = trim($row[$dateTimeIndex] ?? '');
                $locationId = intval($row[$locationIdIndex] ?? 103);
                $verifyCode = trim($row[$verifyCodeIndex] ?? '');

                if (empty($employeeNumber)) {
                    $errors[] = "Fila $rowNumber: Número de empleado vacío";
                    continue;
                }

                if (empty($name)) {
                    $errors[] = "Fila $rowNumber: Nombre vacío";
                    continue;
                }

                if (empty($dateTimeStr)) {
                    $errors[] = "Fila $rowNumber: Fecha/Hora vacía";
                    continue;
                }

                // Parsear fecha
                $checkTime = $this->parseDateTime($dateTimeStr);
                if (!$checkTime) {
                    $errors[] = "Fila $rowNumber: Formato de fecha inválido: $dateTimeStr";
                    continue;
                }

                // Crear o actualizar empleado
                $employee = Employee::updateOrCreate(
                    ['employee_number' => $employeeNumber],
                    [
                        'name' => $name,
                        'department' => $department,
                    ]
                );

                $employeesProcessed[$employeeNumber] = $employee->name;

                // 🔥 NUEVO: Usar el método processAttendance sin especificar tipo
                // para que el mismo método determine si es entrada, salida o almuerzo
                $attendanceData = AttendanceRecord::processAttendance($employee, $checkTime, null);

                // Verificar duplicados
                $exists = AttendanceRecord::where('employee_id', $employee->id)
                    ->where('check_time', $checkTime)
                    ->exists();

                if (!$exists) {
                    // Si es marcación de almuerzo, guardarla también pero con tipo 'lunch'
                    if ($attendanceData['type'] === 'lunch') {
                        AttendanceRecord::create([
                            'employee_id' => $employee->id,
                            'check_time' => $checkTime,
                            'location_id' => $locationId,
                            'verify_code' => $verifyCode ?: 'FACE',
                            'type' => 'lunch',
                            'status' => 'lunch_break',
                            'late_minutes' => 0,
                            'early_exit_minutes' => 0
                        ]);
                        $imported++;
                    } else {
                        AttendanceRecord::create([
                            'employee_id' => $employee->id,
                            'check_time' => $checkTime,
                            'location_id' => $locationId,
                            'verify_code' => $verifyCode ?: 'FACE',
                            'type' => $attendanceData['type'],
                            'status' => $attendanceData['status'],
                            'late_minutes' => $attendanceData['late_minutes'],
                            'early_exit_minutes' => $attendanceData['early_exit_minutes']
                        ]);
                        $imported++;
                    }
                }
            }

            DB::commit();
            fclose($handle);

            $message = "✅ ¡Importación exitosa!\n";
            $message .= "📊 $imported registros de asistencia importados\n";
            $message .= "👥 " . count($employeesProcessed) . " empleados procesados\n";

            if (!empty($errors)) {
                $message .= "\n⚠️ Advertencias: " . implode(", ", array_slice($errors, 0, 5));
            }

            return redirect()->back()->with('success', nl2br($message));

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            Log::error('Error importando CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    private function parseDateTime($dateTimeStr)
    {
        $dateTimeStr = trim($dateTimeStr);

        $formats = [
            'd/m/Y H:i',
            'd/m/Y H:i:s',
            'j/n/Y H:i',
            'd/m/y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateTimeStr);
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($dateTimeStr);
        } catch (\Exception $e) {
            return null;
        }
    }
}
