# 🏗️ ARQUITECTURA MVC - SISTEMA DE ASISTENCIAS

## 📊 Diagrama de Flujo MVC
┌─────────────────────────────────────────────────────────────┐
│ USUARIO FINAL │
│ (Recursos Humanos) │
└─────────────────────────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ VISTAS (Views) │
│ • Tabla de asistencias │
│ • Formulario de reportes │
│ • Importación CSV │
│ • Gestión de horarios │
└─────────────────────────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ CONTROLADORES (Controllers) │
│ • AttendanceController → Lógica de asistencias │
│ • ImportCsvController → Importación de CSV │
│ • EmployeeShiftController → Gestión de horarios │
└─────────────────────────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ MODELOS (Models) │
│ • Employee → Empleados y sus horarios │
│ • AttendanceRecord → Registros y cálculos │
└─────────────────────────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ BASE DE DATOS (MySQL) │
│ • employees → Datos de empleados │
│ • attendance_records → Marcaciones biométricas │
└─────────────────────────────────────────────────────────────┘

## 📁 Estructura de Carpetas y Explicación

### 1. `app/Http/Controllers/` - Los Controladores (La Lógica)

```php
// Cada controlador maneja una funcionalidad específica:

AttendanceController.php      → Muestra asistencias y genera reportes
ImportCsvController.php       → Lee archivos CSV y guarda en BD
EmployeeShiftController.php   → Asigna horarios a empleados
ProfileController.php         → Perfil de usuario (Laravel Breeze)
Controller.php                → Controlador base (vacío)

2. app/Models/ - Los Modelos (Los Datos)
php
Employee.php           → Representa un empleado (nombre, número, horario)
AttendanceRecord.php   → Representa una marcación (fecha, hora, tipo)
User.php              → Usuario del sistema (login)
Relaciones:
Un Employee tiene muchos AttendanceRecord

Cada AttendanceRecord pertenece a un Employee

resources/views/ - Las Vistas (Lo que ve el usuario)
text
layouts/
├── app.blade.php        → Plantilla principal con menú
├── guest.blade.php      → Plantilla para páginas públicas (login)
└── navigation.blade.php → Menú de navegación

attendance/
├── index.blade.php      → Tabla de asistencias (página principal)
├── report.blade.php     → Formulario y resultados de reportes
└── debug.blade.php      → Para depuración (temporal)

employees/
├── index.blade.php      → Lista de empleados con sus horarios
└── shifts.blade.php     → Formulario para asignar turnos

auth/
├── login.blade.php           → Pantalla de inicio de sesión
├── register.blade.php        → Registro de usuarios
├── forgot-password.blade.php → Recuperar contraseña
├── reset-password.blade.php  → Restablecer contraseña
└── confirm-password.blade.php → Confirmar contraseña

import-csv.blade.php     → Formulario para subir archivos CSV

📝 Tecnologías Utilizadas
Backend: Laravel (PHP)

Frontend: Blade Templates, Bootstrap

Base de Datos: MySQL

Autenticación: Laravel Breeze

 CÓMO EJECUTAR ESTE PROYECTO LARAVEL DESDE CERO
📋 Requisitos Previos
Asegúrate de tener instalado:

Requisito	Versión	Comando para verificar
PHP	>= 8.1	php -v
Composer	Última	composer --version
MySQL	>= 5.7	mysql --version
Node.js	>= 16.x	node -v
NPM	Última	npm -v
🔧 Paso a Paso
1. Clonar o Crear el Proyecto
bash
# Opción A: Si tienes el código en un repositorio
git clone <url-del-repositorio>
cd nombre-del-proyecto

# Opción B: Crear un nuevo proyecto Laravel
composer create-project laravel/laravel sistema-asistencias
cd sistema-asistencias
2. Instalar Dependencias
bash
# Instalar dependencias de PHP
composer install

# Instalar Laravel Breeze (autenticación)
composer require laravel/breeze --dev

# Instalar Breeze con Blade
php artisan breeze:install blade

# Instalar dependencias de Node.js
npm install

# Compilar assets (CSS, JS)
npm run build
# O para desarrollo con watch:
npm run dev
