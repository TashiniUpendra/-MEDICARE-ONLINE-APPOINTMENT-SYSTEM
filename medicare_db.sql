-- Create Database
CREATE DATABASE medicare_db;
USE medicare_db;

-- =========================
-- USERS TABLE (Admin, Doctor, Patient)
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','doctor','patient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- DOCTORS TABLE
-- =========================
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    experience VARCHAR(50),
    available_time VARCHAR(100)
);

-- =========================
-- APPOINTMENTS TABLE
-- =========================
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    doctor_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    status ENUM('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- =========================
-- SAMPLE DATA (IMPORTANT FOR DEMO)
-- =========================

-- Admin User
INSERT INTO users (name, email, password, role)
VALUES ('Admin User', 'admin@medicare.com', '$2y$10$examplehash', 'admin');

-- Patients
INSERT INTO users (name, email, password, role)
VALUES 
('Nimal Perera', 'nimal@gmail.com', '$2y$10$examplehash', 'patient'),
('Kamal Fernando', 'kamal@gmail.com', '$2y$10$examplehash', 'patient');

-- Doctors
INSERT INTO doctors (name, specialization, experience, available_time)
VALUES
('Dr. John Silva', 'Cardiologist', '12 Years', '9AM - 2PM'),
('Dr. Maya Fernando', 'Dermatologist', '8 Years', '10AM - 4PM'),
('Dr. Ruwan Perera', 'Neurologist', '15 Years', '11AM - 5PM'),
('Dr. Nadeesha Karun', 'Pediatrician', '10 Years', '9AM - 1PM');

-- Appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
VALUES
(2, 1, '2026-01-12', '10:30:00', 'Heart checkup', 'Pending'),
(3, 2, '2026-01-13', '11:15:00', 'Skin allergy', 'Confirmed'),
(2, 3, '2026-01-14', '09:45:00', 'Headache', 'Cancelled');

ALTER TABLE doctors 
ADD image VARCHAR(255),
ADD phone VARCHAR(20),
ADD email VARCHAR(120);

ALTER TABLE appointments 
ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE appointments 
ADD notes TEXT;

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(120),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


SELECT COUNT(*) FROM doctors;
SELECT COUNT(*) FROM users WHERE role='patient';
SELECT COUNT(*) FROM appointments;


CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT,
    day VARCHAR(20),
    time VARCHAR(100),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

