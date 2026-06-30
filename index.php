<?php
// index.php
session_start();
require_once 'db_config.php';

// Control de acceso simple
if (!isset($_SESSION['login'])) {
    if (isset($_POST['login_btn'])) {
        $usuario = $_POST['usuario'] ?? '';
        $clave = $_POST['clave'] ?? '';
        if ($usuario === 'admin' && $clave === '1234') {
            $_SESSION['login'] = true;
        } else {
            $error_login = "Credenciales incorrectas.";
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$mensaje = "";

// ACCIONES DE LAS CITAS OPERATIVAS
if (isset($_SESSION['login'])) {
    
    // 1. Registrar Cita
    if (isset($_POST['registrar_cita'])) {
        $nombre = $_POST['nombre'] ?? '';
        $especialidad = $_POST['especialidad'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';

        if (!empty($nombre) && !empty($especialidad) && !empty($fecha) && !empty($hora)) {
            $stmt = $pdo->prepare("INSERT INTO citas (nombre, especialidad, fecha, hora, estado) VALUES (?, ?, ?, ?, 'Pendiente')");
            $stmt->execute([$nombre, $especialidad, $fecha, $hora]);
            $mensaje = "Cita registrada con éxito.";
        }
    }

    // 2. Cambiar Estado (Realizada / Cancelada)
    if (isset($_POST['cambiar_estado'])) {
        $id = $_POST['id'] ?? '';
        $nuevo_estado = $_POST['nuevo_estado'] ?? '';
        if (!empty($id) && !empty($nuevo_estado)) {
            $stmt = $pdo->prepare("UPDATE citas SET estado = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $id]);
            $mensaje = "Estado actualizado a '$nuevo_estado'.";
        }
    }

    // 3. Reagendar Cita
    if (isset($_POST['reagendar_cita'])) {
        $id = $_POST['id'] ?? '';
        $nueva_fecha = $_POST['nueva_fecha'] ?? '';
        $nueva_hora = $_POST['nueva_hora'] ?? '';

        if (!empty($id) && !empty($nueva_fecha) && !empty($nueva_hora)) {
            $stmt = $pdo->prepare("UPDATE citas SET fecha = ?, hora = ?, estado = 'Pendiente' WHERE id = ?");
            $stmt->execute([$nueva_fecha, $nueva_hora, $id]);
            $mensaje = "Cita reagendada correctamente.";
        }
    }
}

// Obtener registros para la tabla
$citas = [];
if (isset($_SESSION['login'])) {
    $stmt = $pdo->query("SELECT * FROM citas ORDER BY fecha ASC, hora ASC");
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Citas Médicas - Avanzado</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Bloqueo temprano para evitar destellos visuales al recargar
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
</head>
<body>

<div class="container" style="max-width: 920px;">
    <div class="topbar">
        <div class="brand">
            <div class="brand-icon">🩺</div>
            <div class="brand-text">
                <span class="brand-title">Sistema de Citas Médicas</span>
                <span class="brand-subtitle">Panel administrativo</span>
            </div>
        </div>
        <button type="button" id="themeToggle" class="theme-toggle-btn">🌓 Cambiar tema</button>
    </div>

    <?php if (!isset($_SESSION['login'])): ?>
        <div class="login-wrapper">
            <h2>Iniciar Sesión</h2>
            <?php if (isset($error_login)): ?>
                <div class="alert alert-error">⚠️ <?= $error_login ?></div>
            <?php endif; ?>
            <form action="index.php" method="POST">
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" required autocomplete="off" placeholder="Ingrese su usuario">
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="clave" required placeholder="••••••••">
                </div>
                <button type="submit" name="login_btn">Ingresar</button>
            </form>
        </div>

    <?php else: ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin-bottom:0;">Panel de Control Médico</h2>
            <a href="index.php?logout=1" class="logout-link">↩ Cerrar Sesión</a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info">✅ <?= $mensaje ?></div>
        <?php endif; ?>

        <div class="card">
            <form action="index.php" method="POST">
                <h3>📅 Agendar Nueva Cita</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Paciente</label>
                        <input type="text" name="nombre" required placeholder="Nombre completo">
                    </div>
                    <div class="form-group">
                        <label>Especialidad</label>
                        <select name="especialidad" required>
                            <option value="">Seleccione...</option>
                            <option value="Medicina General">Medicina General</option>
                            <option value="Pediatría">Pediatría</option>
                            <option value="Cardiología">Cardiología</option>
                            <option value="Odontología">Odontología</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" name="hora" required>
                    </div>
                </div>
                <button type="submit" name="registrar_cita" style="margin-top: 10px;">Guardar Cita</button>
            </form>
        </div>

        <h3>🗂️ Listado de Citas Operativas</h3>
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha / Hora</th>
                    <th>Estado</th>
                    <th>Acciones Operativas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <span class="empty-icon">📭</span>
                            No hay registros clínicos en el sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $cita): 
                        $estado_limpio = strtolower(trim($cita['estado'] ?? 'pendiente'));

                        $badge_class = 'badge-pendiente';
                        if ($estado_limpio === 'realizada') $badge_class = 'badge-realizada';
                        if ($estado_limpio === 'cancelada') $badge_class = 'badge-cancelada';
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cita['nombre'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($cita['especialidad'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cita['fecha'] ?? '') ?> a las <?= htmlspecialchars($cita['hora'] ?? '') ?></td>
                            <td>
                                <span class="badge <?= $badge_class ?>">
                                    <?= htmlspecialchars($cita['estado'] ?? 'Pendiente') ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <?php if ($estado_limpio === 'pendiente' || empty($estado_limpio)): ?>
                                    <div class="action-row">
                                        <form action="index.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $cita['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="Realizada">
                                            <button type="submit" name="cambiar_estado" class="btn-sm btn-success">✓ Realizada</button>
                                        </form>
                                        <form action="index.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $cita['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="Cancelada">
                                            <button type="submit" name="cambiar_estado" class="btn-sm btn-danger">✕ Cancelar</button>
                                        </form>
                                    </div>

                                    <form action="index.php" method="POST" class="reschedule-row">
                                        <input type="hidden" name="id" value="<?= $cita['id'] ?>">
                                        <input type="date" name="nueva_fecha" required>
                                        <input type="time" name="nueva_hora" required>
                                        <button type="submit" name="reagendar_cita" class="btn-sm btn-info">↻ Reagendar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="no-actions">Sin acciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<script>
    // 1. Alternador interactivo de Temas Visuales
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
        const isLight = document.documentElement.classList.toggle('light-theme');
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    });

    // 2. Temporizador para desvanecer el recuadro de mensaje (alert) tras 3 segundos
    document.addEventListener("DOMContentLoaded", function() {
        const alertBox = document.querySelector('.alert');
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s ease";
            setTimeout(function() {
                alertBox.style.opacity = "0";
                setTimeout(function() {
                    alertBox.style.display = "none";
                }, 500);
            }, 3000);
        }
    });
</script>

</body>
</html>