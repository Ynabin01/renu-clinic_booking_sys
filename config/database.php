<?php
$host = 'localhost';
$db = 'clinic_booking';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Small automatic schema upgrade so the improved version also works with the old project database.
    $pdo->exec("CREATE TABLE IF NOT EXISTS doctors (
        doctor_id INT AUTO_INCREMENT PRIMARY KEY,
        clinic_id INT NOT NULL,
        doctor_name VARCHAR(120) NOT NULL,
        specialization VARCHAR(150) NOT NULL,
        phone VARCHAR(30),
        email VARCHAR(150),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_doctor_clinic_auto FOREIGN KEY (clinic_id) REFERENCES clinics(clinic_id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $hasDoctorColumn = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $db . "' AND TABLE_NAME='appointments' AND COLUMN_NAME='doctor_id'")->fetchColumn();
    if (!$hasDoctorColumn) {
        $pdo->exec('ALTER TABLE appointments ADD COLUMN doctor_id INT NULL AFTER clinic_id');
    }

    $hasDoctorForeignKey = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='" . $db . "' AND TABLE_NAME='appointments' AND COLUMN_NAME='doctor_id' AND REFERENCED_TABLE_NAME='doctors'")->fetchColumn();
    if (!$hasDoctorForeignKey) {
        $pdo->exec('ALTER TABLE appointments ADD CONSTRAINT fk_appointment_doctor_auto FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL');
    }
} catch (PDOException $e) {
    die('Database connection failed. Please check MySQL and database settings.');
}
