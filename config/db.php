<?php
// config/db.php

class Database {
    private static $host = "localhost";
    private static $db_name = "ecovive_db";
    private static $username = "root";
    private static $password = ""; // Cambia esto según la configuración de tu MySQL
    private static $charset = "utf8mb4";
    
    private static $conn = null;

    /**
     * Obtiene la instancia única de la conexión PDO.
     * @return PDO|null
     */
    public static function getConnection() {
        if (self::$conn === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=" . self::$charset;
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en caso de error
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Retorna arreglos asociativos
                    PDO::ATTR_EMULATE_PREPARES   => false,                // Usa consultas preparadas nativas del SGBD
                ];

                self::$conn = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // En producción es recomendable registrar el error en log sin mostrar detalles internos
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$conn;
    }
}