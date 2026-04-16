<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'employee_number',
        'name',
        'department',
        'card_no',
        'shift_type',
        'custom_start_time',
        'custom_end_time',
        'break_minutes'
    ];

    protected $casts = [
        'custom_start_time' => 'datetime',
        'custom_end_time' => 'datetime',
    ];

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getSchedule()
    {
        $schedules = [
            'full_8_20' => ['start' => '08:00', 'end' => '20:00', 'break' => 60, 'name' => 'Turno Completo 8:00-20:00'],
            'full_7_19' => ['start' => '07:00', 'end' => '19:00', 'break' => 60, 'name' => 'Turno Completo 7:00-19:00'],
            'morning_8_14' => ['start' => '08:00', 'end' => '14:00', 'break' => 15, 'name' => 'Turno Mañana 8:00-14:00'],
            'morning_7_13' => ['start' => '07:00', 'end' => '13:00', 'break' => 15, 'name' => 'Turno Mañana 7:00-13:00'],
            'afternoon_14_20' => ['start' => '14:00', 'end' => '20:00', 'break' => 15, 'name' => 'Turno Tarde 14:00-20:00'],
            'afternoon_13_19' => ['start' => '13:00', 'end' => '19:00', 'break' => 15, 'name' => 'Turno Tarde 13:00-19:00'],
            'night_20_8' => ['start' => '20:00', 'end' => '08:00', 'break' => 60, 'name' => 'Turno Nocturno 20:00-08:00', 'is_night' => true],
            'night_19_7' => ['start' => '19:00', 'end' => '07:00', 'break' => 60, 'name' => 'Turno Nocturno 19:00-07:00', 'is_night' => true],
        ];

        if ($this->shift_type && isset($schedules[$this->shift_type])) {
            // Usar break_minutes de la base de datos si está configurado
            if ($this->break_minutes > 0) {
                $schedules[$this->shift_type]['break'] = $this->break_minutes;
            }
            return $schedules[$this->shift_type];
        }

        if ($this->shift_type == 'custom' && $this->custom_start_time && $this->custom_end_time) {
            return [
                'start' => $this->custom_start_time->format('H:i'),
                'end' => $this->custom_end_time->format('H:i'),
                'break' => $this->break_minutes ?? 0,
                'name' => 'Horario Personalizado'
            ];
        }

        return [
            'start' => '08:00',
            'end' => '20:00',
            'break' => $this->break_minutes ?? 60,
            'name' => 'Turno Completo 8:00-20:00'
        ];
    }

    public function getToleranceMinutes()
    {
        return 5;
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                     ->orWhere('employee_number', 'LIKE', "%{$term}%");
    }
}
