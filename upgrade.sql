USE clinic_booking;

CREATE TABLE IF NOT EXISTS doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    clinic_id INT NOT NULL,
    doctor_name VARCHAR(120) NOT NULL,
    specialization VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doctor_clinic_upgrade FOREIGN KEY (clinic_id) REFERENCES clinics(clinic_id) ON DELETE CASCADE
);

ALTER TABLE appointments ADD COLUMN doctor_id INT NULL AFTER clinic_id;
ALTER TABLE appointments ADD CONSTRAINT fk_appointment_doctor_upgrade FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL;

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
