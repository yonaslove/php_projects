# 🔐 Secure PHP Login & Logout System (OOP + MySQL + Sessions + Cookies)

This project is a **secure login and logout page** built with **PHP (Object-Oriented Programming)**, **MySQL**, **Sessions**, and **Cookies**.  
It provides a clean, simple, and secure example for user authentication.

---

## 🌟 Features

✅ Object-Oriented PHP Design (`Database` and `UserAuth` classes)  
✅ Secure password hashing (`password_hash()` / `password_verify()`)  
✅ Session management for login state  
✅ “Remember Me” feature with cookies  
✅ Responsive, simple HTML/CSS interface  
✅ Single-file version for learning simplicity  

---

## 🧩 Folder Structure


---

## ⚙️ Requirements

- PHP 7.4+ (or later)
- MySQL Database
- Apache server (e.g. XAMPP, WAMP, Laragon)
- Web browser
- VS Code (optional but recommended)

---

## 🗃️ Database Setup (SQL Script)

Run the following SQL commands in **phpMyAdmin** or **MySQL CLI**:

```sql
-- Create database
CREATE DATABASE oop_login_secure;

-- Select the database
USE oop_login_secure;

-- Create users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- Insert a default user (username: admin, password: 12345)
-- The password below is hashed using password_hash('12345', PASSWORD_DEFAULT)
INSERT INTO users (username, password)
VALUES ('admin', '$2y$10$usCkO63Qti3.CkQv1tXSmuaJrbGP2mSC1Evsfi7mrQegjDL7CrDkC');
