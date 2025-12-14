# 🚀 IT Club Management System

A comprehensive web-based management system for college IT clubs, built with PHP, MySQL, and modern web technologies. This platform enables efficient management of events, members, faculty, and club activities with separate dashboards for faculty and members.

![IT Club](https://img.shields.io/badge/IT%20Club-Management%20System-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Project Structure](#-project-structure)
- [User Roles](#-user-roles)
- [Screenshots](#-screenshots)
- [Configuration](#-configuration)
- [Usage Guide](#-usage-guide)
- [Security Features](#-security-features)
- [Contributing](#-contributing)
- [License](#-license)
- [Contact](#-contact)

---

## ✨ Features

### 🎯 Core Features
- **Event Management** - Create, edit, delete, and manage club events
- **User Management** - Separate dashboards for Faculty and Members
- **Event Registration** - Students can register for events online
- **Winners Showcase** - Display competition winners and achievements
- **Event Gallery** - Photo galleries for past events
- **Query System** - Students can submit queries about events
- **Feedback & Ratings** - Event feedback and rating system
- **Responsive Design** - Mobile-friendly interface
- **Dynamic Settings** - Customizable site settings from admin panel

### 👨‍🏫 Faculty Features
- Full administrative access
- Add/Edit/Delete events
- Manage members and other faculty
- View event registrations
- Respond to student queries
- Upload event photos
- Manage winners
- Site settings configuration
- Dashboard analytics

### 👨‍🎓 Member Features
- Limited administrative access
- View events and registrations
- Manage event gallery
- View queries
- Profile management
- Dashboard overview

### 🌐 Public Features
- Browse upcoming and past events
- View event details
- Event registration
- Submit queries
- View winners
- Contact form
- Social media integration

---

## 🛠️ Tech Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with animations
- **JavaScript (ES6+)** - Interactive features
- **Bootstrap 5.3** - Responsive framework
- **AOS Library** - Scroll animations
- **Font Awesome 6** - Icons

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 8.0+** - Database management
- **mysqli** - Database connectivity

### Additional Libraries
- **jQuery** - DOM manipulation
- **Bootstrap Bundle** - JS components

---

## 💻 System Requirements

### Server Requirements
- **Web Server**: Apache 2.4+ (XAMPP/WAMP/LAMP)
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 8.0 or higher
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

### Recommended Specifications
- **RAM**: 2GB minimum
- **Storage**: 500MB free space
- **Processor**: Dual-core 2.0 GHz or higher

---

## 📥 Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/it-club-management.git
cd it-club-management
```

### Step 2: Setup Web Server
1. Install XAMPP/WAMP/LAMP on your system
2. Copy the project folder to your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\IT Club\`
   - **WAMP**: `C:\wamp64\www\IT Club\`
   - **LAMP**: `/var/www/html/IT Club/`

### Step 3: Start Services
1. Start Apache server
2. Start MySQL server
3. Open phpMyAdmin: `http://localhost/phpmyadmin`

### Step 4: Create Database
1. Create a new database named `it_club_new`
2. The system will automatically create tables on first run

### Step 5: Configure Database Connection
Edit `user/db_config.php`:
```php
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL password
$dbname = "it_club_new";
```

### Step 6: Access the Application
- **Homepage**: `http://localhost/IT Club/user/index.php`
- **Admin Login**: `http://localhost/IT Club/user/login.php`

---

## 🗄️ Database Setup

### Automatic Setup
The system automatically creates the following tables on first run:
- `events` - Event information
- `users` - Faculty and member accounts
- `event_queries` - Student queries
- `event_feedback` - Event ratings and reviews
- `event_registrations` - Event registrations
- `winners` - Competition winners
- `gallery` - Event photos
- `settings` - Site configuration

### Default Admin Account
```
Username: admin
Password: admin123
Role: Faculty
```

**⚠️ Important**: Change the default password after first login!

---

## 📁 Project Structure

```
IT Club/
├── admin/                      # Admin panel files
│   ├── add_event.php          # Add new events
│   ├── manage_events.php      # Manage all events
│   ├── edit_event.php         # Edit event details
│   ├── view_registrations.php # View registrations
│   ├── view_queries.php       # Manage queries
│   ├── manage_winners.php     # Manage winners
│   ├── manage_gallery.php     # Event gallery management
│   ├── manage_members.php     # Member management
│   ├── manage_faculty.php     # Faculty management
│   ├── settings.php           # Site settings
│   ├── dashboard.php          # Admin dashboard
│   ├── profile.php            # User profile
│   ├── sidebar.php            # Navigation sidebar
│   ├── admin_style.css        # Admin panel styles
│   └── admin_script.js        # Admin panel scripts
│
├── user/                       # Public user interface
│   ├── index.php              # Homepage
│   ├── events.php             # Events listing
│   ├── event_details.php      # Event details page
│   ├── winners.php            # Winners showcase
│   ├── contact.php            # Contact page
│   ├── login.php              # Login page
│   ├── register.php           # Registration page
│   ├── db_config.php          # Database configuration
│   ├── style.css              # Main stylesheet
│   └── optimize.js            # Frontend scripts
│
├── config/                     # Configuration files
│   └── database.php           # Database connection class
│
├── includes/                   # Shared PHP includes
│   └── settings_helper.php    # Settings management functions
│
├── uploads/                    # User uploaded files
│   ├── events/                # Event images
│   ├── gallery/               # Gallery photos
│   └── winners/               # Winner photos
│
├── assets/                     # Static assets
│   ├── images/                # Site images
│   └── logos/                 # Logos
│
└── README.md                   # This file
```

---

## 👥 User Roles

### 1. Faculty (Admin)
**Access Level**: Full administrative control

**Capabilities**:
- ✅ Create, edit, delete events
- ✅ Manage all users (members & faculty)
- ✅ View and manage registrations
- ✅ Respond to queries
- ✅ Upload and manage gallery
- ✅ Manage winners
- ✅ Configure site settings
- ✅ Access all dashboard features

**Login**: `http://localhost/IT Club/user/login.php?role=faculty`

### 2. Member
**Access Level**: Limited administrative access

**Capabilities**:
- ✅ View events and registrations
- ✅ Manage event gallery
- ✅ View queries (read-only)
- ✅ Update own profile
- ❌ Cannot add/edit events
- ❌ Cannot manage users
- ❌ Cannot change site settings

**Login**: `http://localhost/IT Club/user/login.php?role=member`

### 3. Public User
**Access Level**: View-only with registration

**Capabilities**:
- ✅ Browse events
- ✅ Register for events
- ✅ Submit queries
- ✅ View winners
- ✅ Contact club
- ❌ No admin access

---

## 📸 Screenshots

### Homepage
![Homepage](screenshots/homepage.png)
*Modern, responsive homepage with event listings*

### Admin Dashboard
![Dashboard](screenshots/dashboard.png)
*Comprehensive admin dashboard with analytics*

### Event Management
![Events](screenshots/events.png)
*Easy-to-use event management interface*

### Mobile View
![Mobile](screenshots/mobile.png)
*Fully responsive mobile design*

---

## ⚙️ Configuration

### Site Settings
Configure from Admin Panel → Settings:
- Site title and description
- College name and department
- Contact information (email, phone, address)
- Social media links (Instagram, Facebook, Twitter, LinkedIn)
- Footer text
- About text
- Default event location

### Email Configuration
Edit `config/email.php` for email notifications:
```php
$smtp_host = "smtp.gmail.com";
$smtp_port = 587;
$smtp_username = "your-email@gmail.com";
$smtp_password = "your-app-password";
```

### Upload Settings
Configure upload limits in `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

---

## 📖 Usage Guide

### For Faculty

#### Adding an Event
1. Login as Faculty
2. Navigate to **Add Event**
3. Fill in event details:
   - Title, Date, Location
   - Description
   - Upload event image
   - Set registration deadline
4. Click **Create Event**

#### Managing Registrations
1. Go to **View Registrations**
2. Select event from dropdown
3. View all registered students
4. Export to Excel if needed

#### Responding to Queries
1. Navigate to **View Queries**
2. Click on pending query
3. Write response
4. Mark as answered

### For Members

#### Uploading Gallery Photos
1. Login as Member
2. Go to **Event Gallery**
3. Select event
4. Upload photos (multiple selection supported)
5. Add captions

### For Public Users

#### Registering for Events
1. Browse **Events** page
2. Click on event card
3. Click **Register Now**
4. Fill registration form
5. Submit

#### Submitting Queries
1. Go to event details
2. Scroll to **Ask a Question** section
3. Fill in name, email, and query
4. Submit

---

## 🔒 Security Features

### Implemented Security Measures
- ✅ **SQL Injection Prevention** - Prepared statements with mysqli
- ✅ **XSS Protection** - htmlspecialchars() on all outputs
- ✅ **Password Hashing** - PHP password_hash() with bcrypt
- ✅ **Session Management** - Secure session handling
- ✅ **CSRF Protection** - Token-based form validation
- ✅ **File Upload Validation** - Type and size restrictions
- ✅ **Access Control** - Role-based permissions
- ✅ **Input Sanitization** - All user inputs sanitized

### Security Best Practices
```php
// Password hashing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// SQL injection prevention
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);

// XSS prevention
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

### How to Contribute
1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/AmazingFeature
   ```
3. **Commit your changes**
   ```bash
   git commit -m 'Add some AmazingFeature'
   ```
4. **Push to the branch**
   ```bash
   git push origin feature/AmazingFeature
   ```
5. **Open a Pull Request**

### Contribution Guidelines
- Follow PSR-12 coding standards for PHP
- Write clear commit messages
- Add comments for complex logic
- Test thoroughly before submitting
- Update documentation if needed

---

## 🐛 Known Issues

- Database tablespace errors on some MySQL configurations (fixed in v1.1)
- Mobile dropdown menu requires double-tap on some devices
- Large image uploads may timeout on slow connections

### Reporting Issues
Found a bug? Please open an issue on GitHub with:
- Detailed description
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)
- System information

---

## 🔄 Version History

### v1.0.0 (Current)
- Initial release
- Core event management features
- User authentication system
- Responsive design
- Admin dashboard

### Upcoming Features (v1.1)
- [ ] Email notifications
- [ ] PDF certificate generation
- [ ] Advanced analytics
- [ ] Payment gateway integration
- [ ] Mobile app API
- [ ] Multi-language support

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 IT Club Management System

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 📞 Contact

### Project Maintainer
**Sangameshwar College IT Club**

- 📧 Email: aditya.anmol.patil@gmail.com
- 🌐 Website: [https://itclub.sanmcs.com/user/index.php](https://itclub.sanmcs.com/user/index.php)

### Support
For support and queries:
- Open an issue on GitHub
- Email: aditya.anmol.patil@gmail.com
---

## 🙏 Acknowledgments

- **Bootstrap Team** - For the amazing CSS framework
- **Font Awesome** - For beautiful icons
- **AOS Library** - For smooth animations
- **PHP Community** - For excellent documentation
- **All Contributors** - For making this project better

---
<div align="center">

**Made with ❤️ by Sangameshwar College IT Club**

[⬆ Back to Top](#-it-club-management-system)

</div>
