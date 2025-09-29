# Complete Database Setup for Exam Management System

This directory contains a comprehensive database setup for the Exam Management System with all necessary tables, sample data, views, and stored procedures.

## 📋 Overview

The database setup includes:
- **11 Core Tables** with proper relationships and constraints
- **Sample Data** for testing and development
- **3 Database Views** for common queries
- **2 Stored Procedures** for statistics and reporting
- **System Configuration** settings
- **Audit Logging** system

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

Run the automated setup script:

```bash
php setup_complete_database.php
```

This will:
- Create all database tables
- Insert sample data
- Create views and stored procedures
- Verify the setup
- Provide a detailed report

### Option 2: Manual Setup

If you prefer manual setup:

```bash
# Import the SQL file directly
mysql -u root -p pokenginang < complete_database_setup.sql

# Or use phpMyAdmin to import the SQL file
```

### Option 3: Verify Existing Setup

To check if your database is properly configured:

```bash
php verify_database.php
```

## 📊 Database Structure

### Core Tables

| Table | Purpose | Records |
|-------|---------|---------|
| `users` | User management (admin, faculty, students) | 10 sample users |
| `subjects` | Course and subject information | 8 sample subjects |
| `subject_assignments` | Faculty-subject assignments | 8 assignments |
| `student_enrollments` | Student course enrollments | 14 enrollments |
| `exams` | Exam configurations and settings | 5 sample exams |
| `questions` | Exam questions and content | Linked to exams |
| `question_options` | Multiple choice options | For MC questions |
| `exam_attempts` | Student exam attempts tracking | Performance data |
| `student_answers` | Individual question responses | Detailed answers |
| `system_settings` | Application configuration | 10 settings |
| `audit_logs` | System activity logging | Activity tracking |

### Database Views

| View | Purpose |
|------|---------|
| `active_exams_view` | Active exams with faculty and subject info |
| `student_results_view` | Student exam results and grades |
| `faculty_exam_stats` | Faculty examination statistics |

### Stored Procedures

| Procedure | Purpose |
|-----------|---------|
| `GetExamStatistics(exam_id)` | Calculate comprehensive exam statistics |
| `GetStudentExamHistory(student_id)` | Retrieve student exam history with grades |

## 🔐 Default Login Credentials

After setup, you can use these default credentials:

| Role | Username | Password | Description |
|------|----------|----------|-------------|
| Admin | ADMIN001 | password | System administrator |
| Faculty | FAC001 | password | Dr. John Smith |
| Faculty | FAC002 | password | Dr. Jane Doe |
| Student | 2022-001 | password | Eve Wilson (1st Year) |
| Student | 2020-001 | password | Alice Johnson (2nd Year) |

**⚠️ Important:** Change these default passwords immediately after setup for security!

## 📚 Sample Data Included

### Users
- 1 Administrator
- 3 Faculty members
- 6 Students (across different year levels)

### Subjects
- CS101: Introduction to Computer Science
- CS102: Programming Fundamentals
- MATH101: College Algebra
- MATH102: Calculus I
- ENG101: English Communication
- CS201: Data Structures and Algorithms
- CS202: Object-Oriented Programming
- DB101: Database Management Systems

### Exams
- CS101 Midterm Examination
- CS101 Final Examination
- Programming Quiz 1
- Math 101 Quiz - Algebra Basics
- Data Structures Midterm

## 🔧 Configuration

### Database Connection

The system uses the configuration in `src/App/Config/Database.php`:

```php
private $host = '127.0.0.1';
private $database = 'pokenginang';
private $username = 'root';
private $password = '';
```

### System Settings

Key system settings that can be configured:

| Setting | Default | Description |
|---------|---------|-------------|
| `default_exam_time_limit` | 60 | Default time limit in minutes |
| `max_exam_attempts` | 3 | Maximum attempts per exam |
| `auto_grade_multiple_choice` | 1 | Auto-grade MC questions |
| `show_results_immediately` | 0 | Show results after completion |
| `allow_exam_retakes` | 1 | Allow exam retakes |
| `academic_year` | 2024-2025 | Current academic year |
| `current_semester` | 1st Semester | Current semester |

## 🔍 Verification and Health Checks

### Run Health Check

```bash
php verify_database.php
```

This will check:
- ✅ Database connection
- 📋 Table existence and structure
- 👁️ Database views
- ⚙️ Stored procedures
- 🔍 Data integrity
- ⚡ Performance indexes

### Expected Output

```
🔍 Database Health Check for Exam Management System
==================================================

📡 Checking database connection...
✅ Database connection successful
   Database: pokenginang
   MySQL Version: 8.0.x

📋 Checking database tables...
✅ users (10 records) - User management and authentication
✅ subjects (8 records) - Course and subject information
...

📋 HEALTH CHECK SUMMARY
======================

✅ Database Connection: OK
📋 Tables: 11/11 present
👁️ Views: 3/3 present
⚙️ Procedures: 2/2 present
✅ Data Integrity: No issues found

🎉 OVERALL STATUS: HEALTHY ✅
```

## 🛠️ Troubleshooting

### Common Issues

1. **Connection Failed**
   ```
   Error: Database connection failed
   ```
   - Check MySQL server is running
   - Verify database credentials in `Database.php`
   - Ensure database `pokenginang` exists

2. **Permission Denied**
   ```
   Error: Access denied for user
   ```
   - Check MySQL user permissions
   - Grant necessary privileges to the database user

3. **Table Already Exists**
   ```
   Warning: Table 'users' already exists
   ```
   - This is normal if re-running setup
   - The script uses `CREATE TABLE IF NOT EXISTS`

4. **Foreign Key Constraints**
   ```
   Error: Cannot add foreign key constraint
   ```
   - Ensure tables are created in correct order
   - Check that referenced tables exist

### Manual Fixes

If automated setup fails, you can:

1. **Drop and recreate database:**
   ```sql
   DROP DATABASE IF EXISTS pokenginang;
   CREATE DATABASE pokenginang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Reset specific table:**
   ```sql
   DROP TABLE IF EXISTS table_name;
   -- Then re-run the CREATE TABLE statement
   ```

3. **Check table structure:**
   ```sql
   DESCRIBE table_name;
   SHOW CREATE TABLE table_name;
   ```

## 📈 Performance Optimization

### Recommended Indexes

The setup includes optimized indexes for:
- User lookups by role and school_id
- Exam queries by faculty, subject, and dates
- Student attempt tracking
- Question and answer relationships

### Query Optimization

Use the provided views for common queries:
- `active_exams_view` for exam listings
- `student_results_view` for grade reports
- `faculty_exam_stats` for analytics

## 🔒 Security Considerations

1. **Change Default Passwords**
   - All default passwords are 'password'
   - Change immediately after setup

2. **Database User Privileges**
   - Create dedicated database user
   - Grant only necessary privileges

3. **Backup Strategy**
   - Regular database backups
   - Test restore procedures

4. **Audit Logging**
   - Monitor `audit_logs` table
   - Track important system changes

## 📝 Usage Examples

### Using Stored Procedures

```sql
-- Get exam statistics
CALL GetExamStatistics(1);

-- Get student exam history
CALL GetStudentExamHistory(5);
```

### Using Views

```sql
-- List all active exams
SELECT * FROM active_exams_view WHERE year_level = '1st Year';

-- Get student results
SELECT * FROM student_results_view WHERE school_id = '2022-001';

-- Faculty statistics
SELECT * FROM faculty_exam_stats WHERE faculty_name LIKE '%Smith%';
```

### Common Queries

```sql
-- Find students enrolled in a subject
SELECT u.full_name, u.school_id 
FROM users u 
JOIN student_enrollments se ON u.user_id = se.student_id 
WHERE se.subject_id = 1 AND se.status = 'enrolled';

-- Get exam results for a specific exam
SELECT u.full_name, ea.score, ea.percentage, ea.status
FROM exam_attempts ea
JOIN users u ON ea.student_id = u.user_id
WHERE ea.exam_id = 1;
```

## 🆘 Support

If you encounter issues:

1. Check the setup log files (generated with timestamps)
2. Run the verification script: `php verify_database.php`
3. Review the health check report
4. Check MySQL error logs
5. Ensure all dependencies are installed via Composer

## 📄 Files Included

- `complete_database_setup.sql` - Complete SQL schema and data
- `setup_complete_database.php` - Automated setup script
- `verify_database.php` - Health check and verification
- `DATABASE_SETUP_README.md` - This documentation
- `database_exam_schema.sql` - Original exam tables only
- `databse1.sql` - Extended schema with relationships

Choose the setup method that best fits your needs!
