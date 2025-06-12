CREATE DATABASE college_event_management;

USE college_event_management;

CREATE TABLE events (
    
     id INT AUTO_INCREMENT PRIMARY KEY,
    
     title VARCHAR(255) NOT NULL,
    
     date DATE NOT NULL,
    
     description TEXT NOT NULL,
    
     admin_id INT,
    
     FOREIGN KEY (admin_id) REFERENCES administrators(id)
    
 );


CREATE TABLE students (
    
     id INT AUTO_INCREMENT PRIMARY KEY,
    
     name VARCHAR(100) NOT NULL,
    
     email VARCHAR(100) NOT NULL UNIQUE
    
 );

CREATE TABLE registrations (
    
     id INT AUTO_INCREMENT PRIMARY KEY,
    
     student_id INT,
    
     event_id INT,
    
     registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
     FOREIGN KEY (student_id) REFERENCES students(id),
    
     FOREIGN KEY (event_id) REFERENCES events(id)
    
 );