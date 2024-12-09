-- Create Database
CREATE DATABASE ConnectCare;

-- Create Users Table
CREATE TABLE users (
    users_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(25) NOT NULL,
    user_type VARCHAR(25) NOT NULL
);

-- Create Doctors Table
CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT NOT NULL UNIQUE,
    doctor_name VARCHAR(50) NOT NULL,
    workplace VARCHAR(50) NOT NULL,
    FOREIGN KEY (users_id) REFERENCES users(users_id)
);

-- Create Patients Table
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
	last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    FOREIGN KEY (users_id) REFERENCES users(users_id)
);

-- Create Medical Records Table
CREATE TABLE medicalrecords (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT,
    record_date DATE NOT NULL,
    description VARCHAR(200) NOT NULL,
    record_file VARCHAR(200),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);

-- Create Messages Table
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    doctor_id INT,
    patient_id INT,
    message_text VARCHAR(200) NOT NULL,
    sent_date TIMESTAMP NOT NULL,
    FOREIGN KEY (sender_id) REFERENCES users(users_id),
    FOREIGN KEY (receiver_id) REFERENCES users(users_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
);


-- Create Medical Bills Table
CREATE TABLE medicalbills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    bill_amount DECIMAL NOT NULL,
    bill_status VARCHAR(25) NOT NULL,
    bill_due_date DATE NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
);

-- Create Appointments Table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
	appointment_info VARCHAR(100) NOT NULL,
    appointment_date TIMESTAMP NOT NULL,
    appointment_status VARCHAR(25) NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);

-- Create Appointment Cancellation Reasons Table
CREATE TABLE appointment_cancellations (
    cancellation_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    cancellation_reason VARCHAR(255) NOT NULL,
    cancellation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);


-- Create Password Reset Requests Table
CREATE TABLE PasswordResetRequests (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT NOT NULL,
    reset_code VARCHAR(10) NOT NULL,
    created_date TIMESTAMP NOT NULL,
    expires_date TIMESTAMP NOT NULL,
    FOREIGN KEY (users_id) REFERENCES users(users_id)
);



-- Example Insert Statements

-- Insert into Users
INSERT INTO users (email, password, user_type)
VALUES 
('john.doe@example.com', 'password123', 'doctor'),
('jane.doe@example.com', 'password456', 'patient');

-- Insert into Doctors
INSERT INTO doctors (users_id, workplace)
VALUES 
(1, 'General Hospital');

-- Insert into Patients
INSERT INTO patients (users_id, first_name, last_name, date_of_birth)
VALUES 
(2, 'Jane', 'Doe', '1990-05-15');

-- Insert into Medical Records
INSERT INTO medicalrecords (patient_id, doctor_id, record_date, description, record_file)
VALUES 
(1, 1, '2024-11-15', 'Routine checkup notes', 'record_001.pdf');

-- Insert into Messages
INSERT INTO messages (sender_id, receiver_id, message_text, sent_date)
VALUES 
(2, 1, 'Can I get a follow-up appointment?', CURRENT_TIMESTAMP);

-- Insert into Medical Bills
INSERT INTO medicalbills (patient_id, bill_amount, bill_status, bill_due_date)
VALUES 
(1, 150.00, 'Pending', '2024-12-01');

-- Insert into Appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_info, appointment_date, appointment_status)
VALUES 
(1, 1, 'flu shot', '2024-12-10 10:00:00', 'Scheduled');

-- Insert into Password Reset Requests
INSERT INTO PasswordResetRequests (users_id, reset_code, created_date, expires_date)
VALUES 
(2, 'A1B2C3', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP + INTERVAL 15 MINUTE);
