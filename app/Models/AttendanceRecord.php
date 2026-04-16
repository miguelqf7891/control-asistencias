<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    protected $fillable = [
        'employee_id',
        'check_time',
        'location_id',
        'verify_code',
        'type',
        'scheduled_entry',
        'scheduled_exit',
        'status',
        'notes',
        'late_minutes',
        'early_exit_minutes'
    ];

    protected $casts = [
        'check_time' => 'datetime',
        'scheduled_entry' => 'datetime',
        'scheduled_exit' => 'datetime',
        'late_minutes' => 'integer',
        'early_exit_minutes' => 'integer'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Verificar si una marcación está dentro del horario de almuerzo
     */
    private static function isInLunchBreak($employee, $checkTime)
    {
        $schedule = $employee->getSchedule();
        $breakMinutes = $schedule['break'] ?? 0;

        // Si no tiene break configurado, no es almuerzo
        if ($breakMinutes == 0) {
            return false;
        }

        $hour = (int)$checkTime->format('H');
        $minute = (int)$checkTime->format('i');

        // Calcular minutos desde medianoche
        $currentMinutes = ($hour * 60) + $minute;

        // Horario de almuerzo: 13:00 (780 minutos) hasta 13:00 + breakMinutes
        $lunchStartMinutes = 13 * 60; // 780 minutos (13:00)
        $lunchEndMinutes = $lunchStartMinutes + $breakMinutes;

        // Verificar si está dentro del horario de almuerzo
        $isLunch = ($currentMinutes >= $lunchStartMinutes && $currentMinutes < $lunchEndMinutes);

        return $isLunch;
    }

    /**
     * Inferir el tipo de marcación basado en la hora
     */
    private static function inferType($employee, $checkTime)
    {
        // PRIMERO: Verificar si es almuerzo
        if (self::isInLunchBreak($employee, $checkTime)) {
            return 'lunch';
        }

        $schedule = $employee->getSchedule();
        $hour = (int)$checkTime->format('H');

        // Para turno completo de 8:00 a 20:00
        if ($schedule['start'] == '08:00' && $schedule['end'] == '20:00') {
            // Antes de las 14:00 es entrada, después es salida
            // El almuerzo ya fue filtrado arriba (13:00-14:00)
            if ($hour < 14) {
                return 'entry';
            } else {
                return 'exit';
            }
        }

        // Para otros horarios
        $startTime = Carbon::parse($checkTime->format('Y-m-d') . ' ' . $schedule['start']);
        $endTime = Carbon::parse($checkTime->format('Y-m-d') . ' ' . $schedule['end']);

        // Si es turno nocturno
        if (isset($schedule['is_night']) && $schedule['is_night']) {
            if ($checkTime->hour >= 20 || $checkTime->hour < 8) {
                $distanceToStart = abs($checkTime->diffInMinutes($startTime));
                $distanceToEnd = abs($checkTime->diffInMinutes($endTime));
                return $distanceToStart < $distanceToEnd ? 'entry' : 'exit';
            }
        }

        // Regla general
        if ($hour < 14) {
            return 'entry';
        } else {
            return 'exit';
        }
    }

    /**
     * Procesar asistencia
     */
    public static function processAttendance($employee, $checkTime, $type = null)
    {
        $schedule = $employee->getSchedule();
        $tolerance = $employee->getToleranceMinutes();

        // Si no se especificó tipo, inferirlo
        if (!$type) {
            $type = self::inferType($employee, $checkTime);
        }

        // Si es marcación de almuerzo
        if ($type === 'lunch') {
            return [
                'status' => 'lunch_break',
                'late_minutes' => 0,
                'early_exit_minutes' => 0,
                'type' => 'lunch'
            ];
        }

        $checkDate = $checkTime->format('Y-m-d');

        // Crear horarios programados
        $startDateTime = Carbon::parse($checkDate . ' ' . $schedule['start']);
        $endDateTime = Carbon::parse($checkDate . ' ' . $schedule['end']);

        // Si es turno nocturno, ajustar fecha de salida
        if (isset($schedule['is_night']) && $schedule['is_night']) {
            if ($endDateTime->hour < $startDateTime->hour) {
                $endDateTime = $endDateTime->addDay();
            }
        }

        $lateMinutes = 0;
        $earlyExitMinutes = 0;
        $status = 'on_time';

        if ($type === 'entry') {
            $entryLimit = $startDateTime->copy()->addMinutes($tolerance);

            if ($checkTime->gt($entryLimit)) {
                $status = 'late';
                $lateMinutes = $checkTime->diffInMinutes($startDateTime);
            }
        } elseif ($type === 'exit') {
            $exitLimit = $endDateTime->copy()->subMinutes($tolerance);

            if ($checkTime->lt($exitLimit)) {
                $status = 'early_exit';
                $earlyExitMinutes = $endDateTime->diffInMinutes($checkTime);
            }
        }

        return [
            'status' => $status,
            'late_minutes' => abs($lateMinutes),
            'early_exit_minutes' => abs($earlyExitMinutes),
            'type' => $type
        ];
    }

    // Accessor para tardanza formateada
    public function getFormattedLateMinutesAttribute()
    {
        if (!$this->late_minutes || $this->late_minutes <= 0) {
            return '-';
        }

        $hours = floor($this->late_minutes / 60);
        $minutes = $this->late_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        return "{$minutes} min";
    }

    // Accessor para salida temprana formateada
    public function getFormattedEarlyExitAttribute()
    {
        if (!$this->early_exit_minutes || $this->early_exit_minutes <= 0) {
            return '-';
        }

        $hours = floor($this->early_exit_minutes / 60);
        $minutes = $this->early_exit_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        return "{$minutes} min";
    }
}
