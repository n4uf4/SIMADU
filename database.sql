-- Sistem Pengaduan Mahasiswa Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS pengaduanmhs;
USE pengaduanmhs;

-- Kategori table
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table (Mahasiswa dan Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    no_hp VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Laporan (Complaints) table
CREATE TABLE IF NOT EXISTS laporan (
    id_laporan INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    isi_laporan TEXT NOT NULL,
    id_kategori INT,
    status ENUM('Menunggu', 'Diproses', 'Selesai') DEFAULT 'Menunggu',
    anonim ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
    privat ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);

-- Foto bukti (Photo evidence)
CREATE TABLE IF NOT EXISTS foto_laporan (
    id_foto INT PRIMARY KEY AUTO_INCREMENT,
    id_laporan INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE
);

-- Tanggapan Admin (Admin responses)
CREATE TABLE IF NOT EXISTS tanggapan (
    id_tanggapan INT PRIMARY KEY AUTO_INCREMENT,
    id_laporan INT NOT NULL,
    id_admin INT NOT NULL,
    tanggapan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE,
    FOREIGN KEY (id_admin) REFERENCES users(id) ON DELETE CASCADE
);

-- Arsip (Archive)
CREATE TABLE IF NOT EXISTS arsip (
    id_arsip INT PRIMARY KEY AUTO_INCREMENT,
    id_laporan INT NOT NULL,
    diarsipkan_oleh INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan) ON DELETE CASCADE,
    FOREIGN KEY (diarsipkan_oleh) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default kategori
INSERT INTO kategori (nama_kategori) VALUES 
('Akademik'),
('Fasilitas'),
('Keuangan'),
('Dosen'),
('Lainnya')
ON DUPLICATE KEY UPDATE nama_kategori=nama_kategori;

-- Insert default demo accounts
INSERT INTO users (nim, nama_lengkap, no_hp, password, role) VALUES
('111111111', 'Administrator', '-', '$2y$10$nLffx51bfZ1LKSkF7q.3S.zL5JlEiwmY1gbZ.FZknGthlaLl31T7S', 'admin'),
('257411023', 'Mahasiswa Demo', '-', '$2y$10$nw6Fsnk9kKBgrZHYhy72ouNWtKfUXDKc8HB9iJyq.ke.EFiCRmJ/W', 'mahasiswa')
ON DUPLICATE KEY UPDATE
    nama_lengkap = VALUES(nama_lengkap),
    no_hp = VALUES(no_hp),
    password = VALUES(password),
    role = VALUES(role);

-- Create indexes for better query performance
CREATE INDEX idx_laporan_id_user ON laporan(id_user);
CREATE INDEX idx_laporan_status ON laporan(status);
CREATE INDEX idx_tanggapan_id_laporan ON tanggapan(id_laporan);
CREATE INDEX idx_foto_id_laporan ON foto_laporan(id_laporan);
