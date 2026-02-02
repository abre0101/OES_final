# Practice Questions System

## Overview
The Practice Questions System is separate from the Exam Questions System to ensure:
- Students can practice freely without seeing actual exam questions
- Practice questions don't require approval
- Instructors can add unlimited practice questions
- Better learning experience with explanations and difficulty levels

## Database Structure

### Tables

#### 1. `practice_questions`
Stores all practice questions for students.

**Key Fields:**
- `practice_question_id` - Unique identifier
- `course_id` - Links to courses table
- `question_text` - The question
- `question_type` - multiple_choice, true_false, short_answer
- `option_a, option_b, option_c, option_d` - Answer choices
- `correct_answer` - The correct answer
- `explanation` - Explanation for the answer (helps learning)
- `difficulty_level` - easy, medium, hard
- `topic` - Question topic/category
- `points` - Point value
- `is_active` - Enable/disable questions

#### 2. `practice_results`
Tracks student practice attempts.

**Key Fields:**
- `practice_result_id` - Unique identifier
- `student_id` - Student who took the practice
- `course_name` - Course practiced
- `total_questions` - Number of questions
- `correct_answers` - Correct count
- `wrong_answers` - Wrong count
- `percentage_score` - Score percentage
- `time_taken_seconds` - Time spent
- `completed_at` - When completed

#### 3. `practice_answers`
Stores individual answers for review.

**Key Fields:**
- `practice_answer_id` - Unique identifier
- `practice_result_id` - Links to practice_results
- `practice_question_id` - Question answered
- `student_answer` - Student's answer
- `is_correct` - Whether answer was correct

## Migration

### Run Migration
```bash
php database/migrate_to_practice_questions.php
```

This will:
1. Create the three new tables
2. Optionally copy existing questions to practice_questions
3. Show summary of migration

### Manual SQL Execution
Alternatively, run the SQL file directly:
```bash
mysql -u root -p oes < database/create_practice_questions_table.sql
```

## Usage

### For Students
1. Go to Practice page
2. Select a course
3. Answer random questions
4. Get immediate feedback with explanations
5. See results and review answers

### For Instructors
Instructors can add practice questions through:
1. Instructor dashboard
2. Manage Practice Questions page
3. Bulk import from CSV

### For Administrators
- Monitor practice usage
- View practice statistics
- Manage practice questions across all courses

## Key Differences: Practice vs Exam Questions

| Feature | Practice Questions | Exam Questions |
|---------|-------------------|----------------|
| **Approval** | Not required | Required by exam committee |
| **Visibility** | Always visible | Only during exam schedule |
| **Feedback** | Immediate with explanations | After exam completion |
| **Attempts** | Unlimited | One attempt per exam |
| **Time Limit** | None | Strict time limits |
| **Scoring** | For learning only | Counts toward grades |
| **Randomization** | Random selection | Fixed exam set |

## Benefits

### For Students
- ✅ Practice anytime without pressure
- ✅ Learn from explanations
- ✅ Track progress over time
- ✅ No fear of failing
- ✅ Unlimited attempts

### For Instructors
- ✅ Easy to add practice content
- ✅ No approval process needed
- ✅ See what students struggle with
- ✅ Improve teaching based on practice data

### For System
- ✅ Exam security maintained
- ✅ Better database organization
- ✅ Scalable architecture
- ✅ Clear separation of concerns

## API Endpoints (Future)

### Get Practice Questions
```php
GET /api/practice/questions?course_id=1&limit=10
```

### Submit Practice Answers
```php
POST /api/practice/submit
{
  "student_id": 123,
  "course_name": "Fundamentals of Nursing",
  "answers": [...]
}
```

### Get Practice History
```php
GET /api/practice/history?student_id=123
```

## Future Enhancements

1. **Adaptive Learning** - Adjust difficulty based on performance
2. **Spaced Repetition** - Show questions at optimal intervals
3. **Topic Mastery** - Track mastery by topic
4. **Leaderboards** - Gamification for motivation
5. **Study Plans** - Personalized practice schedules
6. **Question Bank** - Community-contributed questions
7. **Analytics Dashboard** - Detailed practice analytics
8. **Mobile App** - Practice on the go

## Maintenance

### Adding Practice Questions
```sql
INSERT INTO practice_questions 
(course_id, question_text, question_type, option_a, option_b, option_c, option_d, 
 correct_answer, explanation, difficulty_level, topic, points, created_by)
VALUES 
(1, 'What is the normal heart rate?', 'multiple_choice', 
 '60-100 bpm', '40-60 bpm', '100-120 bpm', '120-140 bpm',
 '60-100 bpm', 'Normal adult resting heart rate is 60-100 beats per minute.',
 'easy', 'Vital Signs', 1, 1);
```

### Cleaning Old Practice Results
```sql
-- Delete practice results older than 1 year
DELETE FROM practice_results 
WHERE completed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Backup Practice Questions
```bash
mysqldump -u root -p oes practice_questions practice_results practice_answers > practice_backup.sql
```

## Support

For issues or questions:
- Check the Help page
- Contact IT support
- Email: support@dmu.edu.et
