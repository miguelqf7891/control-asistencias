<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeShiftController extends Controller
{
    public function index()
    {
        $employees = Employee::withCount('attendanceRecords')->orderBy('name')->get();
        return view('employees.index', compact('employees'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.shifts', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'shift_type' => 'required|string',
            'custom_start_time' => 'nullable|date_format:H:i',
            'custom_end_time' => 'nullable|date_format:H:i',
            'break_minutes' => 'nullable|integer|min:0|max:180',
            'profesion' => 'nullable|string|max:255',
            'condicion' => 'nullable|string|max:255'
        ]);

        // Actualizar profesión y condición
        $employee->profesion = $request->profesion;
        $employee->condicion = $request->condicion;
        $employee->shift_type = $request->shift_type;

        if ($request->shift_type == 'custom') {
            $employee->custom_start_time = $request->custom_start_time;
            $employee->custom_end_time = $request->custom_end_time;
            $employee->break_minutes = $request->break_minutes;
        } else {
            $employee->custom_start_time = null;
            $employee->custom_end_time = null;
            $breaks = [
                'full_8_20' => 60, 'full_7_19' => 60,
                'morning_8_14' => 15, 'morning_7_13' => 15,
                'afternoon_14_20' => 15, 'afternoon_13_19' => 15,
                'night_20_8' => 60, 'night_19_7' => 60
            ];
            $employee->break_minutes = $breaks[$request->shift_type] ?? 60;
        }

        $employee->save();

        return redirect()->route('employees.shifts.index')->with('success', 'Horario actualizado correctamente');
    }
}
