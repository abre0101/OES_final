# Exam Committee Dashboard Setup Instructions

## Database Migration Required

The Exam Committee dashboard requires additional database columns. Follow these steps:

### Step 1: Run the Migration SQL

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `oes` database
3. Click on the "SQL" tab
4. Copy and paste the contents of `exam_committee_migration.sql`
5. Click "Go" to execute

### Step 2: What the Migration Does

The migration adds the following:

**To `exam_committee` table:**
- `last_password_change` - Tracks when password was last changed

**To `question_page` table:**
- `status` - Question approval status (Pending/Approved/Revision)
- `submission_date` - When question was submitted
- `approval_date` - When question was approved
- `approved_by` - Who approved the question
- `review_date` - When question was reviewed
- `dept_name` - Department name
- `course_id` - Course ID
- `instructor_id` - Instructor ID
- `question_text` - Question content
- `question_count` - Number of questions
- `exam_status` - Exam status (Live/Scheduled/Archived)
- `dept_id` - Department ID

**To `truefalse_question` table:**
- `status` - Question status
- `submission_date` - Submission date
- `dept_name` - Department name

**Creates `exam` table:**
- For tracking exam schedules and deadlines

### Step 3: Verify Installation

After running the migration:
1. Visit http://localhost:8000/ExamCommittee/index.php
2. Login with an exam committee account
3. You should see the dashboard without errors

### Troubleshooting

If you still see errors:
1. Make sure you ran the migration SQL
2. Check that your database name is `oes`
3. Verify the tables exist: `exam_committee`, `question_page`, `truefalse_question`
4. Check PHP error logs for specific issues

### Alternative: Use Existing Structure

If you prefer not to modify the database, the pages can be simplified to work with the existing structure. Contact support for a simplified version.
