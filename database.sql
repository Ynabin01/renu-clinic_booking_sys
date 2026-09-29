CREATE DATABASE IF NOT EXISTS clinic_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinic_booking;

CREATE TABLE IF NOT EXISTS patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clinics (
    clinic_id INT AUTO_INCREMENT PRIMARY KEY,
    clinic_name VARCHAR(150) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    clinic_id INT NOT NULL,
    doctor_name VARCHAR(120) NOT NULL,
    specialization VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doctor_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(clinic_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    clinic_id INT NOT NULL,
    doctor_id INT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_patient FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    CONSTRAINT fk_appointment_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(clinic_id) ON DELETE CASCADE,
    CONSTRAINT fk_appointment_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL
);

INSERT INTO clinics (clinic_name, address, phone, email)
SELECT 'City Care Clinic', 'Kathmandu', '01-4455667', 'info@citycare.test'
WHERE NOT EXISTS (SELECT 1 FROM clinics WHERE clinic_name='City Care Clinic');
INSERT INTO clinics (clinic_name, address, phone, email)
SELECT 'Healthy Life Clinic', 'Lalitpur', '01-5544332', 'info@healthylife.test'
WHERE NOT EXISTS (SELECT 1 FROM clinics WHERE clinic_name='Healthy Life Clinic');
INSERT INTO clinics (clinic_name, address, phone, email)
SELECT 'Family Health Centre', 'Bhaktapur', '01-6611223', 'info@familyhealth.test'
WHERE NOT EXISTS (SELECT 1 FROM clinics WHERE clinic_name='Family Health Centre');

INSERT INTO doctors (clinic_id, doctor_name, specialization, phone, email)
SELECT c.clinic_id, 'Dr. Anil Sharma', 'General Physician', '9800000001', 'anil@example.com'
FROM clinics c WHERE c.clinic_name='City Care Clinic' AND NOT EXISTS (SELECT 1 FROM doctors d WHERE d.clinic_id=c.clinic_id);
INSERT INTO doctors (clinic_id, doctor_name, specialization, phone, email)
SELECT c.clinic_id, 'Dr. Sita Karki', 'Dermatologist', '9800000002', 'sita@example.com'
FROM clinics c WHERE c.clinic_name='City Care Clinic' AND NOT EXISTS (SELECT 1 FROM doctors d WHERE d.clinic_id=c.clinic_id AND d.doctor_name='Dr. Sita Karki');
INSERT INTO doctors (clinic_id, doctor_name, specialization, phone, email)
SELECT c.clinic_id, 'Dr. Ramesh Thapa', 'General Physician', '9800000003', 'ramesh@example.com'
FROM clinics c WHERE c.clinic_name='Healthy Life Clinic' AND NOT EXISTS (SELECT 1 FROM doctors d WHERE d.clinic_id=c.clinic_id);
INSERT INTO doctors (clinic_id, doctor_name, specialization, phone, email)
SELECT c.clinic_id, 'Dr. Maya Shrestha', 'Paediatrician', '9800000004', 'maya@example.com'
FROM clinics c WHERE c.clinic_name='Family Health Centre' AND NOT EXISTS (SELECT 1 FROM doctors d WHERE d.clinic_id=c.clinic_id);
