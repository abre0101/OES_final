# Online Examination System (OES) — Complete System Overview
## Debre Markos University Health Campus

---

## 1. System Summary

The OES is a PHP/MySQL web application for conducting online examinations at Debre Markos University Health Campus. It supports four distinct user roles — Administrator, Instructor, Department Head, and Student — each with isolated sessions, dedicated dashboards, and role-specific workflows. The system enforces a multi-stage exam approval pipeline, a proctored exam interface with anti-cheat measures, automatic partial answer capture on forced submission, comprehensive audit logging, and an in-app notification system.

---

## 2. Technology Stack

- Backend: PHP (procedural + OOP utilities)
- Database: MySQL (via MySQLi)
- Frontend: HTML5, CSS3, JavaScript (vanilla)
- Session isolation: Named PHP sessions per role (OES_ADMIN_SESSION, OES_INSTRUCTOR_SESSION, OES_DEPT_HEAD_SESSION, OES_STUDENT_SESSION)


---

## 3. Authentication & Session Logic

### Staff Login Flow (`auth/staff-login-process.php`)
The system checks tables in strict order. The first match wins:

```
1. administrators table  → sets UserType = 'Administrator'
2. instructors table     → must have is_active = 1 → sets UserType = 'Instructor'
3. department_heads table → must have is_active = 1 → sets UserType = 'DepartmentHead'
```

On success, the matching session is started via `SessionManager::startSession($userType)`, session variables are populated (ID, Name, Dept, DeptId, Email, UserType, login_time), and the user is redirected to their role dashboard. Failed attempts are logged to `audit_logs`.

### Student Login Flow (`auth/student-login.php`)
Checks the `students` table with `is_active = 1`. On success, starts `OES_STUDENT_SESSION` and redirects to `Student/index.php`.

### Session Isolation
Each role uses a separate named session cookie. This allows a user to be simultaneously logged in as different roles in different browser tabs without conflict.

### Access Validation (every protected page)
Every page calls `SessionManager::startSession($role)` then checks:
- `$_SESSION['UserType'] === $requiredRole`
- `$_SESSION['ID']` and `$_SESSION['Name']` are set

Failure destroys the session and redirects to the appropriate login page. The attempt is logged as an unauthorized access event.

---

## 4. User Roles & Complete Task Lists

### 4.1 Administrator

Dashboard stats: total students, active students, total instructors, active instructors, total courses, departments, pending exams, total exams. Recent registrations, recent exam creations, and recent approvals/rejections are shown on the dashboard.

**User Management**
- Create, view, edit, delete students (with unique auto-generated student code, e.g., STU001)
- Activate / deactivate student accounts
- Create, view, edit, delete instructors
- Activate / deactivate instructor accounts
- Create, view, edit, delete department heads
- Reset passwords for any user (logged as password reset by administrator)
- Bulk import students via CSV/Excel (`Admin/BulkImport.php`)

**Academic Structure Management**
- Create, edit, delete faculties
- Create, edit, delete departments (linked to faculties)
- Create, edit, delete courses (linked to departments)
- Create, edit, delete department head assignments

**Monitoring & Reporting**
- View system-wide reports: Academic Analytics (department performance, instructor effectiveness, program success rates), Operational Reports (system usage, exam completion rates), Compliance Reports (audit trails, security logs)
- Export reports to Excel, print reports
- View security logs and audit trails — filterable by user, action type, date
- Perform database backups (`Admin/DatabaseBackup.php`)
- Use global search across all tables (`Admin/GlobalSearch.php`)
- View and manage technical issues reported by students (`Admin/TechnicalIssues.php`, `Admin/ViewIssueDetails.php`)
- View system settings (`Admin/SystemSettings.php`, `Admin/Settings.php`)

**Own Profile**
- Edit own administrator profile (`Admin/EditProfile.php`, `Admin/UpdateProfile.php`)

---

### 4.2 Instructor

Dashboard stats: total courses assigned, total enrolled students, total questions in bank, total exams (with breakdown: draft / pending / approved).

**Question Bank Management**
- Add MCQ and True/False questions with point value, topic, and explanation (`Instructor/AddQuestion.php`, `Instructor/InsertQuestion.php`, `Instructor/InsertQuestionWithPoints.php`)
- Edit and delete questions (`Instructor/EditQuestion.php`, `Instructor/deletequestion.php`)
- Organize questions into topics (`Instructor/ManageTopics.php`, `Instructor/GetCourseTopics.php`)
- View questions by topic (`Instructor/ViewTopicQuestions.php`)
- View question status (`Instructor/ViewStatusQuestions.php`)
- Manage practice questions separately (`Instructor/ManagePracticeQuestions.php`, `Instructor/AddPracticeQuestion.php`, `Instructor/EditPracticeQuestion.php`, `Instructor/DeletePracticeQuestion.php`)

**Exam Management**
- Create exams (draft): select course and category; exam name auto-generated as `{CourseName} - {CategoryName}` (Quizzes get a numeric suffix); system prevents duplicate Final/Midterm per course (`Instructor/CreateExam.php`)
- Add/remove questions to/from an exam; total marks auto-calculated as sum of question point values (`Instructor/ManageExamQuestions.php`, `Instructor/UpdateExamTotalMarks.php`)
- Edit draft exams (`Instructor/EditExam.php`)
- Delete draft exams (`Instructor/DeleteExam.php`)
- View all own exams and their statuses (`Instructor/MyExams.php`, `Instructor/ManageExams.php`, `Instructor/ViewExam.php`)
- Submit exam for approval (draft → pending) with optional comments (`Instructor/SubmitExamForApproval.php`)
- Resubmit exams after revision requests (status returns to pending, revision_count increments)
- Get exam question count (`Instructor/GetExamCount.php`)

**Scheduling**
- Create, edit, delete exam schedules (date, time, room) after approval (`Instructor/CreateSchedule.php`, `Instructor/EditSchedule.php`, `Instructor/DeleteSchedule.php`, `Instructor/InsertSchedule.php`, `Instructor/SaveSchedule.php`, `Instructor/ViewSchedule.php`)

**Results & Students**
- View student results for own courses (`Instructor/SeeResults.php`, `Instructor/ViewStudentResult.php`, `Instructor/ResultsOverview.php`)
- View enrolled students per course (`Instructor/ViewStudents.php`, `Instructor/MyCourses.php`)
- View analytics (`Instructor/Analytics.php`)
- Generate own reports (`Instructor/Reports.php`)

**Notifications & Issues**
- View notifications about approval decisions (`Instructor/Notifications.php`)
- View reported technical issues (`Instructor/ViewIssues.php`)

**Own Profile**
- Edit profile (`Instructor/EditProfile.php`, `Instructor/UpdateProfile.php`)
- Change password (`Instructor/ChangePassword.php`)
- Use global search (`Instructor/GlobalSearch.php`)

---

### 4.3 Department Head

Dashboard stats: pending exams count, approved today, approved this month, exams needing revision. Shows urgent alert when pending exams exist with direct link to review. Shows recent pending approvals, recent activity, and upcoming approved exams.

**Exam Approval Workflow**
- View all pending exams for the department (`DepartmentHead/PendingApprovals.php`)
- View full exam details including all questions before making a decision (`DepartmentHead/ViewExamDetails.php`, `DepartmentHead/ViewQuestion.php`, `DepartmentHead/CheckQuestions.php`)
- Process approval decisions (`DepartmentHead/ProcessApproval.php`):
  - Approve → status = `approved`, records `approved_by`, `approved_at`, logs to `exam_approval_history`
  - Request revision → status = `revision`, increments `revision_count`, requires comments, logs to history
  - Reject → status = `rejected`, requires reason, logs to history
- View approval history for each exam (`DepartmentHead/ApprovalHistory.php`)
- View approved exams (`DepartmentHead/ApprovedExams.php`)
- View all department exams (`DepartmentHead/DepartmentExams.php`)
- Check approval data (`DepartmentHead/check_approval_data.php`)

**Exam Scheduling & Publishing**
- Schedule approved exams: set date, start time, duration (minutes), room/lab; end time auto-calculated (`DepartmentHead/ScheduleExam.php`)
- Edit existing schedules
- Publish / unpublish scheduled exams (`is_active` flag) to control student visibility
- View past exams (read-only)

**Student & Course Management**
- View students in the department (`DepartmentHead/Students.php`, `DepartmentHead/ViewStudent.php`)
- Register new students (`DepartmentHead/RegisterStudent.php`)
- Edit student records (`DepartmentHead/EditStudent.php`)
- Bulk import students (`DepartmentHead/BulkImportStudents.php`)
- Auto-enroll students in courses (`DepartmentHead/AutoEnrollStudent.php`)
- Assign instructors to courses (`DepartmentHead/AssignInstructor.php`)
- View and manage courses (`DepartmentHead/Courses.php`, `DepartmentHead/ViewCourse.php`, `DepartmentHead/EditCourse.php`, `DepartmentHead/RegisterCourse.php`)

**Reports & Monitoring**
- Monitor live exams (`DepartmentHead/MonitorExams.php`)
- View exam results and performance reports (`DepartmentHead/ExamResults.php`, `DepartmentHead/PerformanceReports.php`)
- Department-level reports:
  - Student management report (`DepartmentHead/ReportStudentManagement.php`)
  - Course performance report (`DepartmentHead/ReportCoursePerformance.php`)
  - Exam participation report (`DepartmentHead/ReportExamParticipation.php`)
  - Instructor compliance report (`DepartmentHead/ReportInstructorCompliance.php`)
  - Examination schedule report (`DepartmentHead/ReportExaminationSchedule.php`)
  - Question bank quality report (`DepartmentHead/ReportQuestionBankQuality.php`)
  - General reports (`DepartmentHead/Reports.php`)
- Export reports to Excel (individual and all-in-one):
  - `ExportStudentReport.php`, `ExportCoursePerformance.php`, `ExportExams.php`, `ExportInstructorCompliance.php`, `ExportParticipationReport.php`, `ExportQuestionBankQuality.php`, `ExportScheduleReport.php`, `ExportAllReports.php`, `ExportToExcel.php`

**Issues & Notifications**
- Report issues (`DepartmentHead/ReportIssue.php`)
- View reported issues (`DepartmentHead/ViewIssues.php`)

**Own Profile**
- Edit profile (`DepartmentHead/EditProfile.php`, `DepartmentHead/UpdateProfile.php`)
- Change password (`DepartmentHead/ChangePassword.php`)
- Use global search (`DepartmentHead/GlobalSearch.php`)

---

### 4.4 Student

Dashboard stats: available exams (active + approved, filtered by semester), completed exams, average score percentage, department name. Shows student information card (ID, name, department, year level, semester) and announcements.

**Exam Taking**
- View available exams (published, approved, within entry window) (`Student/StartExam.php`)
- View exam schedule (`Student/Shedule.php`)
- Read exam instructions before starting (`Student/exam-instructions.php`)
- Take exam in fullscreen proctored interface (`Student/exam-interface.php`)
- Navigate questions, flag for review, confirm answers
- Report technical issues during exam without triggering blur detection (`Student/QuickReportIssue.php`, `Student/exam-issue-reporter.js`)
- Exam auto-submits on timer expiry or 2 tab/window blur violations
- Submit exam and save result (`Student/save-exam-result.php`)

**Results**
- View exam results after submission (`Student/exam-result.php`)
- View all past results (`Student/Result.php`)
- Review answers in detail: each question, selected answer, correct answer, points earned (`Student/review-answers.php`)

**Practice Mode**
- Select a course for practice (`Student/practice-selection.php`)
- Take 10 random practice questions with immediate feedback and explanations (`Student/practice.php`)
- No score saved, no timer, no anti-cheat enforcement

**Issues & Notifications**
- Report technical issues (with exam selection, issue type, description; browser/OS info auto-captured) (`Student/ReportIssue.php`, `Student/submit-technical-issue.php`)
- View own reported issues and their status (`Student/MyReportedIssues.php`)
- View notifications (`Student/Notifications.php`)

**Own Profile**
- View profile (`Student/Profile.php`)
- Edit profile and account settings (`Student/EditProfile.php`, `Student/UpdateProfile.php`)
- Logout (`Student/Logout.php`)

---

## 5. Exam Creation Workflow (Instructor)

```
1. Create exam
   - Select course and category (Final, Midterm, Quiz, Makeup)
   - Exam name auto-generated: {CourseName} - {CategoryName}
   - Quizzes get a numeric suffix (e.g., Quiz 1, Quiz 2)
   - Only one Final or Midterm allowed per course; multiple Quizzes/Makeups allowed
   - New exam status = 'draft', total_marks = 0, pass_marks = 0

2. Add questions
   - Instructor selects questions from the question bank for this exam
   - Each question has a point_value
   - total_marks = SUM(point_value) of all questions in the exam
   - pass_marks = total_marks × 0.5 (auto-calculated)

3. Submit for approval
   - Instructor optionally adds comments
   - Exam status: draft → pending
   - submitted_at timestamp recorded
   - Action logged to exam_approval_history
   - All active department heads receive an in-app notification

4. Department Head reviews (see Section 6)

5. Scheduling (after approval)
   - Department Head sets: exam_date, start_time, duration_minutes, room/lab
   - end_time = start_time + duration_minutes (auto-calculated)
   - Exam remains inactive (is_active = 0) until explicitly published

6. Publishing
   - Department Head sets is_active = 1
   - Exam becomes visible to enrolled students on the scheduled date
```

---

## 6. Exam Approval & Revision Loop

```
Instructor submits exam (status: pending)
            ↓
Department Head reviews exam + all questions
            ↓
   ┌─────────────┬──────────────┬──────────────┐
   ↓             ↓              ↓
Approve      Revision       Reject
   ↓             ↓              ↓
status:       status:        status:
'approved'   'revision'     'rejected'
              ↓
   revision_count increments
   Comments required
   Instructor notified
              ↓
   Instructor edits exam/questions
              ↓
   Resubmits → status returns to 'pending'
   revision_count increments again
              ↓
   (Loop continues until approved or rejected)
```

Every state transition is recorded in `exam_approval_history` with: exam_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at.

---

## 7. Exam State Machine

```
draft
  │
  └─(instructor submits)──────────────────► pending
                                               │
                          ┌────────────────────┼────────────────────┐
                          ↓                    ↓                    ↓
                       approved             revision             rejected
                          │                    │
                          ↓                    └──(instructor edits & resubmits)──► pending
               (scheduled & published)
                          │
                          ↓
                    is_active = 1
                 (students can take exam)
```

---

## 8. Exam Entry Window Logic

```php
if (exam.is_active == 1 AND exam.approval_status == 'approved') {
    current_time = now();
    window_start = exam.start_time;
    window_end   = exam.start_time + 10 minutes;

    if (student already completed this exam)     → show "Completed"
    if (current_time >= window_start AND <= window_end) → allow_entry()
    if (current_time < window_start)             → show "Upcoming"
    if (current_time > window_end)               → show "Entry Closed"
} else {
    show "Not Available"
}
```

---

## 9. Student Exam Taking Logic

### Exam Interface (`Student/exam-interface.php`)

**Fullscreen enforcement**
The exam will not start without entering fullscreen mode. Exiting fullscreen is treated as a blur event.

**Anti-cheat measures**
- Tab switching / window blur detection: each blur event increments `blur_warning_count`
- After 1 warning: overlay shown — "Please stay in exam tab. One more violation will auto submit."
- After 2 warnings: auto-submit triggered
- Right-click disabled
- Keyboard shortcuts blocked: F12, Ctrl+Shift+I, Ctrl+U, Ctrl+C, Ctrl+X, Ctrl+V

**Navigation**
- Sidebar shows question status: answered, skipped, flagged for review
- Student can flag questions to return to later
- "Confirm Answer & Next" saves the answer and advances

**Auto-submit on blur/tab switch**
```
Initialize blur_warning_count = 0

On window blur or tab switch:
  if fullscreen is active:
    blur_warning_count++
    if blur_warning_count == 1 → show warning overlay
    if blur_warning_count == 2 → trigger auto-submit

Auto-submit steps:
  1. Capture any in-progress radio/checkbox selection on the current question
  2. Add it to the answers JSON object (alongside all confirmed answers)
  3. POST full payload to save-exam-result.php
  4. Server saves all answers and calculates score
```

**Partial answer capture (critical fix)**
Before auto-submit, JavaScript reads the currently selected radio button value on the active question. That answer is merged into the serialized answers object before sending to the server. This ensures no in-progress answer is ever lost, even if the student had not yet clicked "Confirm Answer & Next".

---

## 10. Exam Submission & Scoring (`Student/save-exam-result.php`)

**Duplicate prevention**
Before inserting, the server checks `exam_results` for an existing row with the same `student_id` and `exam_id`. If found, the submission is rejected and the student is redirected to their existing result.

**Score calculation**
```
total_points_earned   = SUM(point_value) for all correct answers
total_points_possible = SUM(point_value) of all questions in the exam
percentage_score      = (total_points_earned / total_points_possible) × 100
pass_status           = 'Pass' if percentage_score >= 50, else 'Fail'
```

**Storage**
1. Insert one row into `exam_results` (student_id, exam_id, total_questions, correct_answers, wrong_answers, unanswered, total_points_earned, total_points_possible, percentage_score, pass_status, timestamps)
2. For each question in the exam, insert one row into `student_answers` (result_id, question_id, selected_answer, is_correct, points_earned)

**Result review**
After submission, the student can view a detailed result page showing each question, their selected answer, the correct answer, and points earned per question.

---

## 11. Grading Scheme (`utils/GradingHelper.php`)

| Grade | Min % | Max % | GPA  | Status       |
|-------|-------|-------|------|--------------|
| A+    | 90    | 100   | 4.0  | Excellent    |
| A     | 85    | 89.99 | 3.75 | Excellent    |
| A-    | 80    | 84.99 | 3.5  | Excellent    |
| B+    | 75    | 79.99 | 3.0  | Good         |
| B     | 70    | 74.99 | 2.75 | Good         |
| B-    | 65    | 69.99 | 2.5  | Good         |
| C+    | 60    | 64.99 | 2.0  | Satisfactory |
| C     | 55    | 59.99 | 1.75 | Satisfactory |
| C-    | 50    | 54.99 | 1.5  | Satisfactory |
| D     | 45    | 49.99 | 1.0  | Pass         |
| F     | 0     | 44.99 | 0.0  | Fail         |

Pass threshold = 50%. Letter grades and GPA are calculated but pass/fail is determined solely by the 50% threshold.

---

## 12. Practice Mode Logic (`Student/practice.php`)

- Student selects a course on `practice-selection.php`
- System loads 10 random questions from the `practice_questions` table (separate from the main question bank)
- No timer, no fullscreen enforcement, no anti-cheat
- For each question, student selects an answer and clicks "Check Answer"
- System immediately shows correct/incorrect, displays the correct answer and explanation
- Running score is updated (correct count, wrong count, percentage)
- No result is saved to the database

---

## 13. Student Registration & Enrollment Logic

```
1. Administrator creates student account
   - Unique student code auto-generated (e.g., STU001)
   - Student assigned to department, academic year, semester

2. Department Head enrolls student in courses
   - Many-to-many relationship via student_courses table
   - Enrollment is required for the student to see exams for those courses

3. Student can now view and take exams for all enrolled courses
   that are published (is_active = 1) and within the entry window
```

---

## 14. Notification Logic (`utils/NotificationSystem.php`)

Notifications are stored in the `notifications` table with: user_id, user_type, title, message, type (info/success/warning/danger), related_type (exam/result/announcement/approval), related_id, is_read, created_at, read_at.

**Triggers**
- Instructor submits exam for approval → all active department heads receive a notification (type: info, related_type: approval)
- Department head approves exam → instructor receives a success notification
- Department head requests revision → instructor receives a warning notification with comments
- Department head rejects exam → instructor receives a danger notification with reason

**User actions**
- View unread notifications
- Mark a single notification as read
- Mark all notifications as read
- View unread count (shown in header)

---

## 15. Audit Logging (`utils/audit_logger.php`)

Every significant action is logged to the `audit_logs` table.

**Logged fields**
- user_id, user_type (admin/instructor/student/department_head)
- action (description string)
- table_name, record_id
- old_value, new_value
- changed_fields (JSON array of field names)
- ip_address (detected via HTTP_CLIENT_IP → HTTP_X_FORWARDED_FOR → REMOTE_ADDR)
- user_agent
- metadata (JSON with event_type, operation, details)
- created_at

**Logged events**
- Login (success and failure)
- Logout
- Create record
- Update record (with changed fields)
- Delete record
- Password change (by user or by admin)
- Unauthorized access attempt

---

## 16. Security & Access Control

- Role-based session isolation: each role has a separate named session cookie
- Every protected page validates session role before rendering
- Unauthorized access destroys the session, redirects to login, and logs the attempt
- Passwords stored as hashed values (verified via `utils/password_helper.php`)
- SQL injection prevention: all queries use prepared statements with bound parameters
- XSS prevention: all output uses `htmlspecialchars()`
- Anti-cheat: fullscreen enforcement, blur detection, keyboard shortcut blocking, right-click disabled

---

## 17. File Structure Summary

```
/
├── index.php                  — Public landing page
├── login.php                  — Redirect to staff login
├── staff-login.php            — Staff login page
├── student-login.php          — Student login page
├── forgot-password-request.php
├── update-password.php
│
├── auth/                      — Authentication processing
│   ├── staff-login-process.php
│   ├── student-login.php
│   ├── forgot-password-process.php
│   └── Logout.php
│
├── Admin/                     — Administrator panel
├── Instructor/                — Instructor panel
├── DepartmentHead/            — Department Head panel
├── Student/                   — Student portal
│
├── utils/                     — Shared utilities
│   ├── session_manager.php    — Session isolation
│   ├── session_validator.php  — Role validation helpers
│   ├── audit_logger.php       — Audit logging
│   ├── NotificationSystem.php — In-app notifications
│   ├── GradingHelper.php      — Grade/GPA calculations
│   ├── password_helper.php    — Password hashing/verification
│   └── get_user_type.php
│
├── Connections/
│   └── OES.php                — Database connection
│
├── database/
│   └── oes_professional.sql   — Full database schema
│
└── assets/                    — CSS, JS, templates
```
