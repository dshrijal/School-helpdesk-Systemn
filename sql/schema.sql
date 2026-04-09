-- Database Schema for School Helpdesk System (SHS-23)

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('student', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create queries table
CREATE TABLE IF NOT EXISTS queries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Create replies table
CREATE TABLE IF NOT EXISTS replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    query_id INT NOT NULL,
    admin_id INT NOT NULL,
    reply_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (query_id) REFERENCES queries(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Create lost_found table
CREATE TABLE IF NOT EXISTS lost_found (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    type ENUM('lost', 'found') NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    status ENUM('open', 'approved', 'rejected', 'resolved') DEFAULT 'open',
    moderated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (moderated_by) REFERENCES users(id)
);

-- Create indexes for better performance
CREATE INDEX idx_student_id ON queries(student_id);
CREATE INDEX idx_query_id ON replies(query_id);
CREATE INDEX idx_admin_id ON replies(admin_id);
CREATE INDEX idx_lf_student ON lost_found(student_id);
CREATE INDEX idx_lf_status ON lost_found(status);
CREATE INDEX idx_query_status ON queries(status);

-- Sample data (optional - remove for production)
-- INSERT INTO users VALUES (1, 'admin1', 'admin123', 'admin@school.edu', 'admin', NOW());
-- INSERT INTO users VALUES (2, 'student1', 'student123', 'student1@school.edu', 'student', NOW());
