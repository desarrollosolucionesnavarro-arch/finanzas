-- sql/schema.sql
CREATE DATABASE IF NOT EXISTS finanzas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finanzas;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE,
  password_hash VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tipo ENUM('quincenal','mensual') NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  fecha_pago DATE NOT NULL,
  nota VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_payments_user_id ON payments (user_id);
CREATE INDEX idx_payments_created_at ON payments (created_at);
CREATE INDEX idx_payments_fecha_pago ON payments (fecha_pago);

CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  payment_id INT,
  category_id INT,
  monto DECIMAL(12,2) NOT NULL,
  descripcion TEXT,
  motivo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX (payment_id),
  INDEX (created_at)
);
CREATE INDEX idx_expenses_user_id ON expenses (user_id);

-- Datos iniciales de ejemplo
INSERT INTO categories (nombre) VALUES ('Alimentos'), ('Transporte'), ('Servicios'), ('Entretenimiento'), ('Otros');