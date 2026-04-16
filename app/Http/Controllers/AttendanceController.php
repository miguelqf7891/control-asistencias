<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('check_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('check_time', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderBy('check_time', 'desc')->paginate(50);
        $employees = Employee::orderBy('name')->get();

        return view('attendance.index', compact('records', 'employees'));
    }

    public function reportForm()
    {
        $employees = Employee::orderBy('name')->get();
        return view('attendance.report', compact('employees'));
    }

    public function report(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        $query = AttendanceRecord::with('employee')
            ->whereBetween('check_time', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $records = $query->orderBy('check_time')->get();

        $stats = [
            'total' => $records->count(),
            'on_time' => $records->where('status', 'on_time')->count(),
            'late' => $records->where('status', 'late')->count(),
            'early_exit' => $records->where('status', 'early_exit')->count(),
            'total_late_minutes' => $records->sum('late_minutes'),
            'total_early_minutes' => $records->sum('early_exit_minutes')
        ];

        $employees = Employee::orderBy('name')->get();

        return view('attendance.report', compact('records', 'stats', 'request', 'employees'));
    }

    // MÉTODO PARA EXPORTAR A EXCEL (REPORTE NORMAL)
    public function exportExcel(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        $query = AttendanceRecord::with('employee')
            ->whereBetween('check_time', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $records = $query->orderBy('check_time')->get();

        // Agrupar por empleado y fecha
        $grouped = $records->groupBy(function($item) {
            return $item->employee_id . '_' . $item->check_time->format('Y-m-d');
        });

        // Crear spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título del reporte
        $sheet->setTitle('Reporte Asistencias');

        // Título principal
        $sheet->setCellValue('A1', 'REPORTE DE ASISTENCIAS');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Información de filtros
        $sheet->setCellValue('A2', 'Período:');
        $sheet->setCellValue('B2', Carbon::parse($request->date_from)->format('d/m/Y') . ' - ' . Carbon::parse($request->date_to)->format('d/m/Y'));
        $sheet->mergeCells('B2:K2');

        $row = 3;
        if ($request->filled('employee_id')) {
            $employee = Employee::find($request->employee_id);
            $sheet->setCellValue('A3', 'Empleado:');
            $sheet->setCellValue('B3', $employee->name);
            $sheet->mergeCells('B3:K3');
            $row = 4;
        }

        // Estadísticas
        $stats = [
            'total' => $records->count(),
            'on_time' => $records->where('status', 'on_time')->count(),
            'late' => $records->where('status', 'late')->count(),
            'early_exit' => $records->where('status', 'early_exit')->count(),
            'total_late_minutes' => $records->sum('late_minutes'),
            'total_early_minutes' => $records->sum('early_exit_minutes')
        ];

        $sheet->setCellValue('A' . ($row + 1), 'Estadísticas:');
        $sheet->setCellValue('A' . ($row + 2), 'Total registros:');
        $sheet->setCellValue('B' . ($row + 2), $stats['total']);
        $sheet->setCellValue('C' . ($row + 2), 'A tiempo:');
        $sheet->setCellValue('D' . ($row + 2), $stats['on_time'] . ' (' . round(($stats['on_time']/$stats['total'])*100, 1) . '%)');
        $sheet->setCellValue('E' . ($row + 2), 'Tardanzas:');
        $sheet->setCellValue('F' . ($row + 2), $stats['late'] . ' (' . round(($stats['late']/$stats['total'])*100, 1) . '%)');
        $sheet->setCellValue('G' . ($row + 2), 'Salidas tempranas:');
        $sheet->setCellValue('H' . ($row + 2), $stats['early_exit'] . ' (' . round(($stats['early_exit']/$stats['total'])*100, 1) . '%)');

        // Encabezados de columnas (agregamos Profesión y Condición)
        $headers = ['Fecha', 'Empleado', 'Profesión', 'Condición', 'Turno', 'Entrada', 'Salida Almuerzo', 'Retorno Almuerzo', 'Salida', 'Tardanza', 'Salida Temprana'];
        $column = 'A';
        $headerRow = $row + 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($column . $headerRow, $header);
            $column++;
        }

        // Estilo de encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->applyFromArray($headerStyle);

        // Llenar datos
        $dataRow = $headerRow + 1;
        foreach ($grouped as $group) {
            // Separar registros por tipo
            $entry = $group->where('type', 'entry')->first();
            $exit = $group->where('type', 'exit')->first();

            // Registros de almuerzo
            $lunchRecords = $group->where('type', 'lunch')->sortBy('check_time')->values();
            $lunchOut = $lunchRecords->first();
            $lunchIn = $lunchRecords->count() > 1 ? $lunchRecords->last() : null;

            $employee = $entry ? $entry->employee : ($exit ? $exit->employee : ($lunchOut ? $lunchOut->employee : null));
            $date = $entry ? $entry->check_time->format('d/m/Y') : ($exit ? $exit->check_time->format('d/m/Y') : ($lunchOut ? $lunchOut->check_time->format('d/m/Y') : ''));
            $schedule = $employee ? $employee->getSchedule() : [];

            if ($employee) {
                $sheet->setCellValue('A' . $dataRow, $date);
                $sheet->setCellValue('B' . $dataRow, $employee->name ?? 'N/A');
                $sheet->setCellValue('C' . $dataRow, $employee->profesion ?? '---');
                $sheet->setCellValue('D' . $dataRow, $employee->condicion ?? '---');
                $sheet->setCellValue('E' . $dataRow, $schedule['name'] ?? 'N/A');

                // Entrada
                $sheet->setCellValue('F' . $dataRow, $entry ? $entry->check_time->format('H:i:s') : '---');

                // Salida almuerzo
                $sheet->setCellValue('G' . $dataRow, $lunchOut ? $lunchOut->check_time->format('H:i:s') : '---');

                // Retorno almuerzo
                $lunchInValue = '---';
                if ($lunchIn) {
                    $lunchInValue = $lunchIn->check_time->format('H:i:s');
                } elseif ($lunchOut && !$lunchIn) {
                    $lunchInValue = '⚠️ Sin retorno';
                }
                $sheet->setCellValue('H' . $dataRow, $lunchInValue);

                // Salida final
                $sheet->setCellValue('I' . $dataRow, $exit ? $exit->check_time->format('H:i:s') : '---');

                // Tardanza
                $lateValue = '✓';
                if ($entry && $entry->late_minutes > 0) {
                    $lateHours = floor($entry->late_minutes / 60);
                    $lateMinutes = $entry->late_minutes % 60;
                    $lateValue = ($lateHours > 0) ? "{$lateHours}h {$lateMinutes}min" : "{$lateMinutes} min";
                }
                $sheet->setCellValue('J' . $dataRow, $lateValue);

                // Salida temprana
                $earlyValue = '✓';
                if ($exit && $exit->early_exit_minutes > 0) {
                    $earlyHours = floor($exit->early_exit_minutes / 60);
                    $earlyMinutes = $exit->early_exit_minutes % 60;
                    $earlyValue = ($earlyHours > 0) ? "{$earlyHours}h {$earlyMinutes}min" : "{$earlyMinutes} min";
                }
                $sheet->setCellValue('K' . $dataRow, $earlyValue);

                $dataRow++;
            }
        }

        // Autoajustar anchos de columna
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Aplicar bordes a los datos
        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];
        if ($dataRow > $headerRow + 1) {
            $sheet->getStyle('A' . $headerRow . ':K' . ($dataRow - 1))->applyFromArray($borderStyle);
        }

        // Centrar contenido de celdas de datos
        $sheet->getStyle('A' . ($headerRow + 1) . ':K' . ($dataRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Crear archivo Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_asistencias_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // NUEVO MÉTODO PARA EXPORTAR REPORTE MENSUAL CON FORMATO SALUD
    public function monthlyReportForm()
    {
        $employees = Employee::orderBy('name')->get();
        return view('attendance.monthly_report', compact('employees'));
    }

    public function exportMonthlyReport(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        $month = Carbon::parse($request->month);
        $dateFrom = $month->copy()->startOfMonth();
        $dateTo = $month->copy()->endOfMonth();

        $query = AttendanceRecord::with('employee')
            ->whereBetween('check_time', [
                $dateFrom->startOfDay(),
                $dateTo->endOfDay()
            ]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
            $employees = Employee::where('id', $request->employee_id)->get();
        } else {
            $employees = Employee::orderBy('name')->get();
        }

        // Crear spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Mensual');

        // Estilos
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];

        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ]
        ];

        $currentRow = 1;

        foreach ($employees as $employee) {
            // Filtrar registros por empleado
            $employeeRecords = $query->where('employee_id', $employee->id)->get();

            // Agrupar por día
            $dailyRecords = $employeeRecords->groupBy(function($item) {
                return $item->check_time->format('Y-m-d');
            });

            // Fila 1: Título principal
            $sheet->setCellValue('A' . $currentRow, 'RED DE SERVICIOS DE SALUD CUSCO SUR');
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);
            $sheet->getStyle('A' . $currentRow)->getFont()->setSize(16);
            $currentRow++;

            // Fila 2: INSTITUCION PRESTADORA DE SALUD con logo a la derecha
            $sheet->setCellValue('A' . $currentRow, 'INSTITUCION PRESTADORA DE SALUD:');
            $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);

            // Agregar logo en la misma fila, en la columna J
            $logoPath = public_path('images/logo.png');
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo Institucional');
                $drawing->setPath($logoPath);
                $drawing->setHeight(50);
                $drawing->setWidth(50);
                $drawing->setCoordinates('J' . $currentRow);
                $drawing->setOffsetX(0);
                $drawing->setOffsetY(0);
                $drawing->setWorksheet($sheet);
            }
            $currentRow++;

            // Fila 3: REPORTE MENSUAL
            $sheet->setCellValue('A' . $currentRow, 'REPORTE MENSUAL DE CONTROL Y ASISTENCIA');
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);
            $currentRow++;

            // Fila 4: MES
            $sheet->setCellValue('A' . $currentRow, 'MES: ' . strtoupper($month->locale('es')->monthName) . ' - ' . $month->year);
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);
            $currentRow++;

            $currentRow++; // Espacio

            // Guardar la fila donde empieza la tabla
            $tableStartRow = $currentRow;

            // Encabezados de la tabla
            $headers = ['EMPLEADO', 'NRO', 'DIA', 'PROFESIÓN', 'CONDICIÓN', 'TURNO', 'ENTRADA', 'SALIDA', 'OBSERVACIONES', 'TARDANZAS'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $currentRow, $header);
                $col++;
            }

            // Establecer ancho de columnas
            $sheet->getColumnDimension('A')->setWidth(25); // EMPLEADO
            $sheet->getColumnDimension('B')->setWidth(5);  // NRO
            $sheet->getColumnDimension('C')->setWidth(12); // DIA
            $sheet->getColumnDimension('D')->setWidth(20); // PROFESIÓN
            $sheet->getColumnDimension('E')->setWidth(12); // CONDICIÓN
            $sheet->getColumnDimension('F')->setWidth(25); // TURNO
            $sheet->getColumnDimension('G')->setWidth(12); // ENTRADA
            $sheet->getColumnDimension('H')->setWidth(12); // SALIDA
            $sheet->getColumnDimension('I')->setWidth(35); // OBSERVACIONES
            $sheet->getColumnDimension('J')->setWidth(15); // TARDANZAS

            $sheet->getStyle('A' . $currentRow . ':J' . $currentRow)->applyFromArray($headerStyle);
            $currentRow++;

            // Obtener días del mes
            $daysInMonth = $dateTo->day;
            $totalLateMinutes = 0;
            $totalAbsences = 0;
            $startRow = $currentRow;
            $endRow = $startRow + $daysInMonth - 1;

            // Crear todas las filas de días
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::create($month->year, $month->month, $day);
                $dateKey = $currentDate->format('Y-m-d');
                $dayName = ucfirst($currentDate->locale('es')->dayName);

                $record = $dailyRecords->get($dateKey);

                // Determinar estado del día
                $entry = $record ? $record->where('type', 'entry')->first() : null;
                $exit = $record ? $record->where('type', 'exit')->first() : null;

                $entryTime = $entry ? $entry->check_time->format('H:i:s') : '';
                $exitTime = $exit ? $exit->check_time->format('H:i:s') : '';

                $observation = '';

                if (!$entry && !$exit) {
                    $observation = 'FALTA';
                    $totalAbsences++;
                } elseif (!$entry) {
                    $observation = 'FALTA REGISTRO ENTRADA';
                } elseif (!$exit) {
                    $observation = 'FALTA REGISTRO SALIDA';
                }

                // Calcular tardanza
                $tardanza = '';
                if ($entry && $entry->late_minutes > 0) {
                    $lateHours = floor($entry->late_minutes / 60);
                    $lateMins = $entry->late_minutes % 60;
                    $tardanza = ($lateHours > 0) ? "{$lateHours}h {$lateMins}min" : "{$lateMins} min";
                    $totalLateMinutes += $entry->late_minutes;

                    if (empty($observation)) {
                        $observation = 'TARDANZA';
                    } elseif ($observation != 'FALTA' && $observation != 'FALTA REGISTRO ENTRADA' && $observation != 'FALTA REGISTRO SALIDA') {
                        $observation .= ' - TARDANZA';
                    }
                }

                // Salida temprana
                if ($exit && $exit->early_exit_minutes > 0) {
                    $earlyHours = floor($exit->early_exit_minutes / 60);
                    $earlyMins = $exit->early_exit_minutes % 60;
                    $earlyText = ($earlyHours > 0) ? "{$earlyHours}h {$earlyMins}min" : "{$earlyMins} min";
                    if (empty($observation)) {
                        $observation = "SALIDA TEMPRANA {$earlyText}";
                    } elseif ($observation != 'FALTA' && $observation != 'FALTA REGISTRO ENTRADA' && $observation != 'FALTA REGISTRO SALIDA') {
                        $observation .= " - SALIDA TEMPRANA {$earlyText}";
                    }
                }

                $schedule = $employee->getSchedule();

                // Llenar datos
                $sheet->setCellValue('A' . $currentRow, '');
                $sheet->setCellValue('B' . $currentRow, $day);
                $sheet->setCellValue('C' . $currentRow, $dayName);
                $sheet->setCellValue('D' . $currentRow, $employee->profesion ?? '---');
                $sheet->setCellValue('E' . $currentRow, $employee->condicion ?? '---');
                $sheet->setCellValue('F' . $currentRow, $schedule['name'] ?? '---');
                $sheet->setCellValue('G' . $currentRow, $entryTime);
                $sheet->setCellValue('H' . $currentRow, $exitTime);
                $sheet->setCellValue('I' . $currentRow, $observation);
                $sheet->setCellValue('J' . $currentRow, $tardanza);

                $currentRow++;
            }

            // Fusionar celdas de la columna A para el nombre del empleado
            if ($startRow <= $endRow) {
                $sheet->mergeCells('A' . $startRow . ':A' . $endRow);
                $sheet->setCellValue('A' . $startRow, $employee->name);
                $sheet->getStyle('A' . $startRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);
            }

            // Aplicar bordes a toda la tabla
            $sheet->getStyle('A' . ($tableStartRow) . ':J' . ($currentRow - 1))->applyFromArray($borderStyle);

            // Centrar verticalmente todas las celdas de la tabla
            $sheet->getStyle('A' . ($tableStartRow + 1) . ':J' . ($currentRow - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Agregar fila de resumen
            $currentRow++;
            $sheet->setCellValue('A' . $currentRow, 'RESUMEN DEL MES:');
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
            $currentRow++;

            $daysWorked = $dailyRecords->count();

            $sheet->setCellValue('A' . $currentRow, 'Días trabajados:');
            $sheet->setCellValue('B' . $currentRow, $daysWorked);
            $sheet->setCellValue('D' . $currentRow, 'Faltas:');
            $sheet->setCellValue('E' . $currentRow, $totalAbsences);
            $currentRow++;

            $sheet->setCellValue('A' . $currentRow, 'Total minutos de tardanza:');
            $sheet->setCellValue('B' . $currentRow, $totalLateMinutes . ' minutos');

            $lateHoursTotal = floor($totalLateMinutes / 60);
            $lateMinsTotal = $totalLateMinutes % 60;
            if ($lateHoursTotal > 0) {
                $sheet->setCellValue('D' . $currentRow, 'Equivalente a:');
                $sheet->setCellValue('E' . $currentRow, "{$lateHoursTotal} horas y {$lateMinsTotal} minutos");
            }

            $currentRow += 2;

            // Firma
            $sheet->setCellValue('A' . $currentRow, '_________________________________________');
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $currentRow++;
            $sheet->setCellValue('A' . $currentRow, strtoupper(auth()->user()->name ?? 'RESPONSABLE'));
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow++;
            $sheet->setCellValue('A' . $currentRow, 'FIRMA Y SELLO');
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow += 3;

            // Si hay más empleados, agregar salto de página
            if ($employees->count() > 1 && !$request->filled('employee_id') && $currentRow > 1) {
                $sheet->setBreak('A' . $currentRow, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
            }
        }

        // Crear archivo Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_mensual_' . $month->format('Y-m') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
