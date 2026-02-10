# Debre Markos University Health Campus - Online Examination System

A comprehensive web-based examination management system designed for Debre Markos University Health Campus to facilitate secure online examinations, student assessments, and academic administration.

## 🎓 Overview

The Online Examination System (OES) is a full-featured platform that enables:
- **Students** to take exams online, practice with sample questions, and view results
- **Instructors** to create exams, manage question banks, and monitor student performance
- **Department Heads** to approve exams and oversee departmental academic activities
- **Administrators** to manage users, courses, departments, and system settings

## ✨ Key Features

### For Students
- 📝 Take online examinations with timer and auto-submit functionality
- 🎯 Practice mode with sample questions
- 📊 View exam results and performance history
- 👤 Profile management and password reset
- ⚠️ Report technical issues during exams

### For Instructors
- ➕ Create and manage exams with multiple question types
- 📚 Build and organize question banks by course
- 👥 View enrolled students and their performance
- 📈 Track exam statistics and analytics
- ✏️ Edit and update exam content before approval

### For Department Heads
- ✅ Approve or reject exam submissions
- 📋 Review exam content and quality
- 🏛️ Monitor departmental exam schedules
- 📜 Access approval history and audit trails
- ⏳ Track pending approvals and deadlines

### For Administrators
- 👨‍🎓 Manage students, instructors, and department heads
- 🏢 Configure departments, faculties, and courses
- 📥 Bulk import users and data
- 🔒 System security and access control
- 📊 Generate comprehensive reports
- 🗄️ Database backup and maintenance
- 🔍 Global search across all entities

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB 10.4+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: Apache with mod_rewrite
- **Architecture**: MVC-inspired structure with session management

## 📋 System Requirements

### Server Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache 2.4+ with mod_rewrite enabled
- 512MB RAM minimum (1GB+ recommended)
- 500MB disk space minimum

### PHP Extensions Required
- mysqli
- json
- session
- mbstring
- openssl

## 🚀 Installation

### 1. Clone or Download
```bash
git clone <repository-url>
cd online-examination-system
```

### 2. Database Setup
```bash
# Import the database schema
mysql -u root -p < database/oes_professional.sql

# Or use phpMyAdmin to import the SQL file
```

### 3. Configure Database Connection
Edit `Connections/OES.php`:
```php
$hostname_OES = 'localhost';
$database_OES = 'oes_professional';
$username_OES = 'root';
$password_OES = 'your_password';
$port_OES = 3306;
```

### 4. Set Permissions
```bash
# Make sure these directories are writable
chmod 755 backups/
chmod 755 images/
```

### 5. Configure Apache
Ensure `.htaccess` is enabled and mod_rewrite is active:
```apache
<Directory /path/to/oes>
    AllowOverride All
    Require all granted
</Directory>
```

### 6. Access the System
Navigate to: `http://localhost/online-examination-system/`

## 👥 Default Login Credentials

### Administrator
- **Username**: `admin`
- **Password**: `password` (change immediately after first login)

### Test Accounts
Sample accounts are created during database import. Check `database/insert_sample_data.sql` for details.

## 📁 Project Structure

```
online-examination-system/
├── Admin/                  # Administrator dashboard and management
│   ├── index.php          # Admin dashboard
│   ├── Student.php        # Student management
│   ├── Instructor.php     # Instructor management
│   ├── Course.php         # Course management
│   ├── Department.php     # Department management
│   └── ...
├── Student/               # Student portal
│   ├── index.php         # Student dashboard
│   ├── StartExam.php     # Exam interface
│   ├── Result.php        # Results viewing
│   └── ...
├── Instructor/            # Instructor portal
│   ├── index.php         # Instructor dashboard
│   ├── CreateExam.php    # Exam creation
│   ├── ManageQuestions.php
│   └── ...
├── DepartmentHead/        # Department head portal
│   ├── index.php         # Department head dashboard
│   ├── PendingApprovals.php
│   └── ...
├── auth/                  # Authentication system
│   ├── student-login.php
│   ├── staff-login.php
│   └── ...
├── assets/                # Static resources
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Image assets
├── Connections/           # Database connections
│   └── OES.php
├── database/              # Database files
│   ├── oes_professional.sql
│   ├── migrations/
│   └── ...
├── utils/                 # Utility functions
│   ├── session_manager.php
│   └── ...
├── index.php             # Landing page
└── README.md             # This file
```

## 🔐 Security Features

- **Password Hashing**: bcrypt with cost factor 10
- **Session Management**: Secure session handling with role-based access
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Token-based form validation
- **Audit Logging**: Comprehensive activity tracking
- **Role-Based Access Control**: Strict permission enforcement

## 📊 Database Schema

### Core Tables
- `students` - Student information and credentials
- `instructors` - Instructor profiles and assignments
- `department_heads` - Department head accounts
- `administrators` - System administrator accounts
- `courses` - Course catalog
- `departments` - Academic departments
- `faculties` - Faculty/college structure

### Exam Management
- `exams` - Exam definitions and metadata
- `exam_questions` - Questions assigned to exams
- `questions` - Question bank
- `exam_results` - Student exam submissions and scores
- `exam_answers` - Individual answer records

### System Tables
- `audit_logs` - System activity tracking
- `technical_issues` - Issue reporting and tracking
- `password_reset_tokens` - Password recovery tokens

## 🎯 User Roles & Permissions

### Student
- Take assigned exams
- View personal results
- Practice with sample questions
- Update profile information
- Report technical issues

### Instructor
- Create and manage exams
- Build question banks
- View student performance
- Submit exams for approval
- Manage course content

### Department Head
- Approve/reject exam submissions
- Review exam quality
- Monitor department activities
- Access approval history
- Manage exam schedules

### Administrator
- Full system access
- User management (CRUD operations)
- Course and department management
- System configuration
- Database backup/restore
- Security and audit logs
- Bulk data import

## 🔄 Exam Workflow

1. **Instructor** creates exam and adds questions
2. **Instructor** submits exam for approval
3. **Department Head** reviews and approves/rejects
4. **Students** take approved exams during scheduled time
5. **System** auto-grades and stores results
6. **Students** view results after exam completion
7. **Instructors** analyze performance metrics

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers (1920x1080 and above)
- Laptops (1366x768 and above)
- Tablets (768px and above)
- Mobile devices (320px and above)

## 🧪 Testing

### Manual Testing Checklist
- [ ] Student login and exam taking
- [ ] Instructor exam creation
- [ ] Department head approval workflow
- [ ] Admin user management
- [ ] Password reset functionality
- [ ] Bulk import operations
- [ ] Report generation
- [ ] Database backup/restore

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
```
Solution: Check Connections/OES.php credentials and ensure MySQL is running
```

**Session Issues**
```
Solution: Ensure session.save_path is writable and session cookies are enabled
```

**Permission Denied**
```
Solution: Check file permissions on backups/ and images/ directories
```

**Blank Page After Login**
```
Solution: Enable PHP error reporting and check error logs
```

## 📈 Future Enhancements

- [ ] Real-time exam monitoring dashboard
- [ ] Advanced analytics and reporting
- [ ] Email notifications for exam schedules
- [ ] Mobile application (iOS/Android)
- [ ] Integration with Learning Management Systems
- [ ] AI-powered question generation
- [ ] Video proctoring capabilities
- [ ] Multi-language support

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:
1. Fork the repository
2. Create a feature branch
3. Commit your changes with clear messages
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software developed for Debre Markos University Health Campus.
All rights reserved © 2026 Debre Markos University.

## 👨‍💻 Development Team

Developed for Debre Markos University Health Campus
Contact: admin@dmu.edu.et

## 📞 Support

For technical support or questions:
- **Email**: support@dmu.edu.et
- **Phone**: +251-911-000-001
- **Help Page**: Available within the system at `/Help.php`

## 🔄 Version History

### Version 1.0.0 (February 2026)
- Initial release
- Core examination functionality
- User management system
- Approval workflow
- Reporting capabilities
- Security features

---

**Note**: This system handles sensitive academic data. Ensure proper security measures, regular backups, and compliance with institutional policies.
