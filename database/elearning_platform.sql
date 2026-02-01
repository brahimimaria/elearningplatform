DROP DATABASE IF EXISTS elearning_platform;
CREATE DATABASE elearning_platform;

USE elearning_platform;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
);

-- Resources Table (formerly Stock)
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL, -- e.g., Video, PDF, Book
    upload_date DATE,
    author VARCHAR(100)
);

-- Courses Table (formerly Prosthetics)
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100),
    instructor_name VARCHAR(100),
    status ENUM('Active', 'Completed', 'Upcoming') DEFAULT 'Upcoming'
);

-- Schedule Table (formerly Calendar)
CREATE TABLE schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_name VARCHAR(100),
    class_date DATE,
    class_time TIME,
    topic VARCHAR(100)
);

-- Default Admin User (password: admin123)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$t9Ac104GU/kI7rvHRwM0kOjvP3.G0caydx8kk5CSUOGLcLoj6.fVy', 'admin');
