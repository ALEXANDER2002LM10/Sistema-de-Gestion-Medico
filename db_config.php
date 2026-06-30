<?php
// Ruta donde se guardará la base de datos (en la raíz del proyecto)
$ruta_db = __DIR__ . '/citas_medicas.db';

// Bandera para saber si la base de datos es nueva
$es_nueva = !file_exists($ruta_db);

try {
    // Si el archivo no existe, PDO lo crea automáticamente al conectarse
    $pdo = new PDO("sqlite:" . $ruta_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si la base de datos se acaba de crear, ejecutamos la estructura automáticamente
    if ($es_nueva) {
        $sql_estructura = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL
        );

        CREATE TABLE IF NOT EXISTS citas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            paciente TEXT NOT NULL,
            fecha TEXT NOT NULL,
            hora TEXT NOT NULL,
            detalle_multa TEXT
        );
        ";

        // Ejecuta el código SQL para crear todas las tablas automáticamente
        $pdo->exec($sql_estructura);
    }

} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>