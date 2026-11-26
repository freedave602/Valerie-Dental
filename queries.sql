-- Valerie Dental Practice | the queries and lookups behind the site (for Task 2: AC 2.5 / 2.6)
USE valeria_dental;

-- REPORTS (staff dashboard) ------------------------------------------------
SELECT * FROM v_patient_list ORDER BY last_name, first_name;          -- list of patients (with visit counts)
SELECT * FROM v_future_appointments;                                   -- future appointments
SELECT * FROM v_past_appointments;                                     -- past appointments

-- Appointments for one patient
SELECT a.appointment_date, a.appointment_time, t.name AS treatment, a.status
FROM appointments a JOIN treatments t ON t.treatment_id = a.treatment_id
WHERE a.patient_id = 1 ORDER BY a.appointment_date;

-- This week / this month
SELECT * FROM appointments WHERE YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1);
SELECT * FROM appointments WHERE MONTH(appointment_date) = MONTH(CURDATE()) AND YEAR(appointment_date) = YEAR(CURDATE());

-- Bookings by treatment (dashboard chart)
SELECT t.name, COUNT(*) AS total
FROM appointments a JOIN treatments t ON t.treatment_id = a.treatment_id
WHERE a.status <> 'Cancelled' GROUP BY t.treatment_id ORDER BY total DESC;

-- LOOKUPS (public site) ----------------------------------------------------
SELECT patient_id, first_name FROM patients WHERE email = 'ama.mensah@example.com';            -- booking: who is this patient?
SELECT treatment_id, name FROM treatments WHERE code = 'checkup';                              -- booking: treatment code -> id
SELECT TIME_FORMAT(appointment_time,'%H:%i') FROM appointments
WHERE appointment_date = CURDATE() AND status <> 'Cancelled';                                  -- booking: which slots are taken?
SELECT patient_id FROM patients WHERE email = 'ama.mensah@example.com' AND dob = '1994-03-12'; -- portal: email + DOB check
