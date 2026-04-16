<?php


use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ImportCsvController;
use App\Http\Controllers\EmployeeShiftController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('attendance.index');
});


require __DIR__.'/auth.php';


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('attendance.index');
    })->name('dashboard');


    Route::get('/asistencias', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/reportes', [AttendanceController::class, 'reportForm'])->name('attendance.report.form');
    Route::get('/reportes/generar', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('/reportes/exportar-excel', [AttendanceController::class, 'exportExcel'])->name('attendance.report.export'); // NUEVA RUTA


    Route::get('/importar-csv', [ImportCsvController::class, 'index'])->name('import.csv');
    Route::post('/importar-csv', [ImportCsvController::class, 'import'])->name('import.csv.process');


    Route::get('/empleados/horarios', [EmployeeShiftController::class, 'index'])->name('employees.shifts.index');
    Route::get('/empleados/{employee}/horario', [EmployeeShiftController::class, 'edit'])->name('employees.shifts.edit');
    Route::put('/empleados/{employee}/horario', [EmployeeShiftController::class, 'update'])->name('employees.shifts.update');
    // Reporte mensual con formato salud
    Route::get('/reportes/mensual/exportar', [AttendanceController::class, 'exportMonthlyReport'])->name('attendance.monthly.export');
});
