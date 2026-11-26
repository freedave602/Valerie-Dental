-- Valerie Dental Practice | database schema, sample data and report views
-- Import in phpMyAdmin (http://localhost/phpmyadmin > Import) with XAMPP's MySQL running.

CREATE DATABASE IF NOT EXISTS valeria_dental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE valeria_dental;

DROP VIEW  IF EXISTS v_patient_list, v_future_appointments, v_past_appointments;
DROP TABLE IF EXISTS appointments, messages, patients, treatments, staff;

CREATE TABLE treatments (
  treatment_id  INT AUTO_INCREMENT PRIMARY KEY,
  code          VARCHAR(20)  NOT NULL UNIQUE,
  name          VARCHAR(80)  NOT NULL,
  duration_min  INT          NOT NULL DEFAULT 30,
  price_ghs     DECIMAL(8,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE patients (
  patient_id      INT AUTO_INCREMENT PRIMARY KEY,
  first_name      VARCHAR(60)  NOT NULL,
  last_name       VARCHAR(60)  NOT NULL,
  email           VARCHAR(120) NOT NULL UNIQUE,
  phone           VARCHAR(25)  NOT NULL,
  dob             DATE         NOT NULL,
  address         VARCHAR(255) NOT NULL,
  medical_history TEXT,
  consent_given   TINYINT(1)   NOT NULL DEFAULT 0,
  registered_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_patient_name (last_name, first_name)
) ENGINE=InnoDB;

CREATE TABLE appointments (
  appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
  patient_id       INT  NOT NULL,
  treatment_id     INT  NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  notes            VARCHAR(500),
  status           ENUM('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- stops two live bookings for the same slot; cancelled slots become free again
  slot_key VARCHAR(20) AS (IF(status = 'Cancelled', NULL, CONCAT(appointment_date, ' ', appointment_time))) STORED,
  UNIQUE KEY uq_live_slot (slot_key),
  INDEX idx_apt_date (appointment_date),
  CONSTRAINT fk_apt_patient   FOREIGN KEY (patient_id)   REFERENCES patients(patient_id)     ON DELETE CASCADE,
  CONSTRAINT fk_apt_treatment FOREIGN KEY (treatment_id) REFERENCES treatments(treatment_id)
) ENGINE=InnoDB;

CREATE TABLE messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(120) NOT NULL,
  message    TEXT         NOT NULL,
  is_read    TINYINT(1)   NOT NULL DEFAULT 0,
  sent_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE staff (
  staff_id      INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          VARCHAR(40)  NOT NULL DEFAULT 'Receptionist'
) ENGINE=InnoDB;

-- Report views (the "queries and lookups" the practice asked for)
CREATE VIEW v_patient_list AS
  SELECT p.patient_id, p.first_name, p.last_name, p.email, p.phone, p.dob, p.address, p.registered_at,
         COUNT(a.appointment_id) AS total_appointments
  FROM patients p LEFT JOIN appointments a ON a.patient_id = p.patient_id
  GROUP BY p.patient_id;

CREATE VIEW v_future_appointments AS
  SELECT a.appointment_id, a.appointment_date, TIME_FORMAT(a.appointment_time,'%H:%i') AS appointment_time,
         CONCAT(p.first_name,' ',p.last_name) AS patient, p.phone, t.name AS treatment, a.status, a.notes
  FROM appointments a JOIN patients p ON p.patient_id = a.patient_id JOIN treatments t ON t.treatment_id = a.treatment_id
  WHERE a.appointment_date >= CURDATE() AND a.status <> 'Cancelled'
  ORDER BY a.appointment_date, a.appointment_time;

CREATE VIEW v_past_appointments AS
  SELECT a.appointment_id, a.appointment_date, TIME_FORMAT(a.appointment_time,'%H:%i') AS appointment_time,
         CONCAT(p.first_name,' ',p.last_name) AS patient, p.phone, t.name AS treatment, a.status, a.notes
  FROM appointments a JOIN patients p ON p.patient_id = a.patient_id JOIN treatments t ON t.treatment_id = a.treatment_id
  WHERE a.appointment_date < CURDATE()
  ORDER BY a.appointment_date DESC, a.appointment_time DESC;

-- Lookup data
INSERT INTO treatments (code, name, duration_min, price_ghs) VALUES
 ('checkup','Regular Checkup',30,150), ('cleaning','Teeth Cleaning',45,250), ('filling','Dental Filling',60,400),
 ('crown','Crown / Bridge Work',90,1800), ('whitening','Teeth Whitening',60,900),
 ('emergency','Emergency Treatment',30,350), ('consultation','Consultation',30,120);

-- Staff logins (passwords are bcrypt-hashed, never stored as plain text)
--   staff@gmail.com / sharp123      reception@clinic.com / frontdesk
INSERT INTO staff (full_name, email, password_hash, role) VALUES
 ('Admin', 'staff@gmail.com', '$2y$10$hO8TtXESXNGeKm83I0J62uZIP/g7PaWrG4iwJYv.rB1jpwk2q7pIG', 'Administrator'),
 ('Receptionist', 'reception@clinic.com', '$2y$10$Ek4Iriok2oZ6oSl8rN2jWepoB3jq1IyNbnUV/m5tBDaNghxst8s6a', 'Receptionist');

-- Sample patients and appointments so the reports have something to show
INSERT INTO patients (first_name,last_name,email,phone,dob,address,medical_history,consent_given) VALUES
 ('Ama','Mensah','ama.mensah@example.com','+233244000111','1994-03-12','Community 5, Tema','Penicillin allergy',1),
 ('Kwame','Boateng','kwame.boateng@example.com','+233200222333','1988-11-02','Ashaiman, Accra','None',1),
 ('Efua','Owusu','efua.owusu@example.com','+233277444555','2001-07-25','Sakumono, Tema','Asthma',1),
 ('Yaw','Asante','yaw.asante@example.com','+233244666777','1979-01-30','Community 1, Tema','Hypertension',1);

INSERT INTO appointments (patient_id,treatment_id,appointment_date,appointment_time,notes,status) VALUES
 (1,1,DATE_SUB(CURDATE(), INTERVAL 21 DAY),'09:00','Routine visit','Completed'),
 (2,3,DATE_SUB(CURDATE(), INTERVAL 9 DAY),'10:30','Lower left molar','Completed'),
 (3,2,DATE_SUB(CURDATE(), INTERVAL 3 DAY),'14:00',NULL,'Cancelled'),
 (4,5,DATE_ADD(CURDATE(), INTERVAL 2 DAY),'11:00','Wedding in two weeks','Scheduled'),
 (1,2,DATE_ADD(CURDATE(), INTERVAL 5 DAY),'09:30',NULL,'Scheduled'),
 (3,7,DATE_ADD(CURDATE(), INTERVAL 8 DAY),'15:00','Wants braces advice','Scheduled');
