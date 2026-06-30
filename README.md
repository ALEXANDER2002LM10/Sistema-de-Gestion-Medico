# Sistema de Citas Médicas

Panel administrativo simple para registrar, actualizar y reagendar citas médicas, construido en PHP con sesiones nativas y PDO.

## Características

- Inicio de sesión básico (usuario/contraseña).
- Registro de nuevas citas (paciente, especialidad, fecha y hora).
- Cambio de estado de cita: **Pendiente → Realizada / Cancelada**.
- Reagendado de citas pendientes.
- Tema claro/oscuro con preferencia guardada en el navegador.
- Interfaz responsiva (se adapta a celular).

## Requisitos

- PHP 7.4 o superior
- MySQL / MariaDB
- Extensión PDO habilitada

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/tu-usuario/sistema-citas-medicas.git
   cd sistema-citas-medicas
   ```

2. Crea tu archivo de configuración a partir de la plantilla:
   ```bash
   cp db_config.example.php db_config.php
   ```
   Luego edita `db_config.php` con los datos reales de tu base de datos.
   **Este archivo no se sube a GitHub** (está en `.gitignore`).

3. Crea la base de datos y la tabla `citas`. Ejemplo de estructura mínima:
   ```sql
   CREATE TABLE citas (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nombre VARCHAR(150) NOT NULL,
       especialidad VARCHAR(100) NOT NULL,
       fecha DATE NOT NULL,
       hora TIME NOT NULL,
       estado VARCHAR(20) DEFAULT 'Pendiente'
   );
   ```

4. Sirve el proyecto con tu servidor local (XAMPP, Laragon, `php -S`, etc.) y abre `index.php` en el navegador.

## Estructura del proyecto

```
.
├── index.php                 # Lógica y vista principal
├── db_config.example.php     # Plantilla de conexión a BD (sin credenciales)
├── db_config.php             # Conexión real (ignorado por git)
├── css/
│   └── style.css             # Estilos (modo claro/oscuro)
└── .gitignore
```

## Credenciales de prueba

> ⚠️ Cambia estas credenciales antes de usar el sistema en producción. Actualmente están escritas directamente en `index.php`.

- Usuario: `admin`
- Contraseña: `1234`

## Notas de seguridad pendientes

- Las credenciales de acceso están hardcodeadas en el código; lo ideal es moverlas a variables de entorno o a una tabla de usuarios con contraseñas encriptadas.
- Falta protección CSRF en los formularios.
