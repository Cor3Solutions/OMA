-- Oriental Muayboran Academy Database Schema
-- Version: 1.0
-- Date: January 2026

-- Drop existing tables if they exist
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS event_gallery;
DROP TABLE IF EXISTS course_materials;
DROP TABLE IF EXISTS khan_members;
DROP TABLE IF EXISTS instructors;
DROP TABLE IF EXISTS affiliates;
DROP TABLE IF EXISTS users;

-- Users Table (for authentication and user management)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    role ENUM('admin', 'instructor', 'member') DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Affiliates Table (for partner organizations)
CREATE TABLE affiliates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    logo_path VARCHAR(500),
    description TEXT,
    website_url VARCHAR(500),
    facebook_url VARCHAR(500),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Instructors Table (Kru/Masters)
CREATE TABLE instructors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    photo_path VARCHAR(500),
    khan_level VARCHAR(50) NOT NULL,
    title VARCHAR(100),
    location VARCHAR(255),
    specialization TEXT,
    bio TEXT,
    facebook_url VARCHAR(500),
    email VARCHAR(255),
    phone VARCHAR(50),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_khan_level (khan_level),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Khan Members Table (students with their progress)
CREATE TABLE khan_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    current_khan_level INT DEFAULT 1,
    khan_color VARCHAR(50),
    date_joined DATE NOT NULL,
    date_promoted DATE,
    instructor_id INT,
    training_location VARCHAR(255),
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL,
    INDEX idx_khan_level (current_khan_level),
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course Materials Table
CREATE TABLE course_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('beginner', 'intermediate', 'advanced', 'instructor', 'weapon') NOT NULL,
    khan_level_min INT DEFAULT 1,
    khan_level_max INT DEFAULT 16,
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    video_url VARCHAR(500),
    thumbnail_path VARCHAR(500),
    duration_minutes INT,
    display_order INT DEFAULT 0,
    is_public TINYINT(1) DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_khan_level (khan_level_min, khan_level_max),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event Gallery Table
CREATE TABLE event_gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    location VARCHAR(255),
    image_path VARCHAR(500) NOT NULL,
    category VARCHAR(100),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact Messages Table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
INSERT INTO users (name, email, password, role, status) VALUES 
('Administrator', 'admin@oma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert sample affiliates
INSERT INTO affiliates (name, logo_path, description, website_url, display_order, status) VALUES
('Sit Kru Sane Siamyout', 'assets/images/a.png', 'Main lineage organization', 'https://www.facebook.com/sitkrusane', 1, 'active'),
('E.L. Kubo', 'assets/images/b.png', 'Partner martial arts school', 'https://www.facebook.com/elkubo', 2, 'active'),
('Brawlers Lab', 'assets/images/c.png', 'Training facility partner', 'https://www.facebook.com/brawlerslab', 3, 'active'),
('Cor3 Solutions', 'assets/images/d.png', 'Corporate training partner', 'https://www.facebook.com/cor3solutions', 4, 'active'),
('OMA', 'assets/images/e.png', 'Oriental Muayboran Academy', 'https://www.facebook.com/oma', 5, 'active');

-- Insert sample instructors
INSERT INTO instructors (name, photo_path, khan_level, title, location, display_order, status) VALUES
('Rusha Mae Bayacsan', 'assets/images/atemai.png', 'Khan 11 (Kru)', 'Combat Hunters Tribe Co-founder', 'Quezon City', 1, 'active'),
('Earl Victa', 'assets/images/ako.png', 'Khan 11 (Kru)', 'Combat Arsensai Founder', 'Cavite', 2, 'active'),
('Vincent Hisona', 'assets/images/atemai.png', 'Khan 11 (Kru)', 'SKSSPh-Cavite', 'Cavite', 3, 'active'),
('Roberto Serdone Jr.', 'assets/images/ako.png', 'Khan 11 (Kru)', 'Redneos Iron Gladiator Founder', 'Cebu', 4, 'active'),
('Fredelyn Miraflor', 'assets/images/atemai.png', 'Khan 11 (Kru)', '426 MMA-Rizal', 'Rizal', 5, 'active'),
('Vince Miraflor', 'assets/images/ako.png', 'Khan 11 (Kru)', '426 MMA-Rizal', 'Rizal', 6, 'active'),
('Rho Fajutrao', 'assets/images/atemai.png', 'Khan 11 (Kru)', 'Sports and Fitness Center Founder', 'Iloilo City', 7, 'active'),
('Krisna Limbaga', 'assets/images/ako.png', 'Khan 11 (Kru)', 'International Instructor', 'Qatar', 8, 'active');

-- Insert sample event gallery items
INSERT INTO event_gallery (title, description, image_path, category, display_order, status) VALUES
('Unarmed Defense Training', 'Unarmed defense is not about aggression, it\'s about control, confidence, and readiness. We teach practitioners how to manage threats using the mechanical advantages found in traditional Muay Thai.', 'assets/images/unarmed.png', 'Training', 1, 'active'),
('MCJFD Personnel Empowerment', 'Manila, Philippines—The Manila City Jail Female Dormitory conducted a Seminar on Muay Thai-based Unarmed Self-Defense. This enhanced the capability and preparedness of personnel in managing critical situations without weapons.', 'assets/images/mcjfd.png', 'Seminar', 2, 'active'),
('QCJFD Unarmed Combat Skills', 'Isinagawa sa Quezon City Jail Female Dormitory ang pagsasanay sa Muay Thai. Layunin nito na palakasin ang kakayahan ng mga kawani sa pagtatanggol sa sarili bilang bahagi ng kanilang propesyonal na tungkulin.', 'assets/images/QCJFD .png', 'Training', 3, 'active'),
('Safety & Facility Order', 'Mahalaga ang pagtuturo ng Muay Thai sa mga personnel dahil nakatutulong ito upang maging handa sila sa anumang sitwasyong maaaring magbanta sa kanilang kaligtasan at mapanatili ang kaayusan ng pasilidad.', 'assets/images/Safety.png', 'Institutional', 4, 'active');

-- Insert sample course materials
INSERT INTO course_materials (title, description, category, khan_level_min, khan_level_max, display_order, is_public, status) VALUES
('Khan 1-3: Foundation Techniques', 'Basic stances, footwork, and fundamental strikes for beginners', 'beginner', 1, 3, 1, 0, 'published'),
('Khan 4-6: Intermediate Combinations', 'Advanced striking combinations and defensive techniques', 'intermediate', 4, 6, 2, 0, 'published'),
('Khan 7-10: Advanced Applications', 'Complex techniques, sparring strategies, and clinch work', 'advanced', 7, 10, 3, 0, 'published'),
('Instructor Training Program', 'Teaching methodology and curriculum for aspiring Kru', 'instructor', 11, 16, 4, 0, 'published'),
('Krabi Krabong Fundamentals', 'Introduction to traditional Thai weaponry', 'weapon', 1, 16, 5, 0, 'published');
