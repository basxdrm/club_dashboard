# 🎯 Club Management Dashboard

ระบบจัดการชมรม (Club Management System) สร้างสำหรับใช้บริหารจัดการสมาชิก งาน การเงิน และอุปกรณ์ของชมรม

![PHP](https://img.shields.io/badge/PHP-8.0+-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple?logo=bootstrap)

---

## ✨ Features

- **👥 ระบบสมาชิก** — เพิ่ม/แก้ไข/ลบสมาชิก พร้อม role-based access control (Member / Board / Advisor / Admin)
- **🏷️ Role System** — 4 roles ที่ permission แตกต่างกัน, แสดง/ซ่อน UI ตาม role อัตโนมัติ
- **📋 Task Management** — สร้าง assign และติดตาม tasks ภายในชมรม
- **💰 Finance** — บันทึกรายรับ-รายจ่าย พร้อมระบบอนุมัติธุรกรรม
- **🔧 Equipment** — จัดการอุปกรณ์ ยืม-คืน พร้อมระบบอนุมัติ
- **📅 Calendar** — ปฏิทินกิจกรรมรายเดือน
- **👨‍🏫 Advisor Page** — หน้าแสดงอาจารย์ที่ปรึกษาแยกจากสมาชิก
- **🔒 Security** — CSRF Protection, Session Management, Password Hashing (Argon2ID / Bcrypt fallback)

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0+ |
| Database | MySQL / MariaDB |
| Frontend | Bootstrap 5, Vanilla JS |
| Icons | Unicons, Material Design Icons |
| Charts | ApexCharts |
| Auth | Session-based + Remember Me token |

---

## 🚀 Setup

### Requirements
- PHP 8.0+
- MySQL 8.0+ / MariaDB 10.4+
- Apache / Nginx (หรือ XAMPP)

### Installation

1. **Clone repository**
   ```bash
   git clone https://github.com/basxdrm/club_dashboard.git
   cd club_dashboard
   ```

2. **ตั้งค่า Database**
   ```bash
   # สร้าง database
   mysql -u root -p -e "CREATE DATABASE your_db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Import schema
   mysql -u root -p your_db_name < database/schema.sql
   ```

3. **ตั้งค่า Config**
   ```bash
   cp config/database.example.php config/database.php
   ```
   แล้วแก้ไข `config/database.php` ใส่ credentials ของคุณ

4. **ตั้งค่า Web Server**
   - วางโฟลเดอร์ใน `htdocs/` (XAMPP) หรือ Document Root
   - เปิด `http://localhost/club-dashboard/`

---

## 📁 Project Structure

```
club-dashboard/
├── api/                    # REST API endpoints
├── assets/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript
│   └── images/            # Static images
├── config/
│   ├── database.php        # DB config (gitignored)
│   ├── database.example.php # Template config
│   └── security.php       # Security settings
├── database/
│   └── schema.sql         # Database structure
├── includes/
│   ├── auth.php           # Authentication & roles
│   ├── sidebar.php        # Navigation
│   └── modals/            # Reusable modals
├── pages/                 # Page views
└── index.php              # Dashboard
```

---

## 🔐 Default Roles

| Role | ความสามารถ |
|------|-----------|
| `member` | ดูข้อมูล, ยืมอุปกรณ์ |
| `board` | + จัดการ Tasks, อนุมัติ |
| `advisor` | เดียวกับ board (อาจารย์ที่ปรึกษา) |
| `admin` | Full access |

---

## 📝 License

MIT License — feel free to use and modify.
