ESTRUCTURA DEL PROYECTO
control-asistencias/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AttendanceController.php      # Gestión de asistencias
│   │   │   ├── ImportCsvController.php       # Importación CSV
│   │   │   └── EmployeeShiftController.php   # Gestión de turnos
│   │   └── Kernel.php
│   │
│   └── Models/
│       ├── Employee.php                      # Modelo empleado
│       ├── AttendanceRecord.php              # Modelo registro asistencia
│       └── User.php                          # Usuarios (Breeze)
│
├── database/
│   ├── migrations/                           # Migraciones
│   │   ├── create_employees_table.php
│   │   ├── create_attendance_records_table.php
│   │   └── [fechas]_modify_type_and_status_columns.php
│   │
│   └── seeders/
│       └── DatabaseSeeder.php                # Datos de prueba
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php                 # Layout principal
│       │   ├── guest.blade.php               # Layout visitante
│       │   └── navigation.blade.php          # Menú navegación
│       │
│       ├── attendance/
│       │   ├── index.blade.php               # Listado asistencias
│       │   └── report.blade.php              # Reporte con almuerzo
│       │
│       ├── employees/
│       │   ├── index.blade.php               # Listado empleados
│       │   └── shifts.blade.php              # Configurar turnos
│       │
│       ├── auth/                             # Vistas Breeze
│       └── import-csv.blade.php              # Importar CSV
│
├── routes/
│   └── web.php                               # Rutas principales
│
├── .env                                       # Configuración
└── .env.example                               # Ejemplo configuración
