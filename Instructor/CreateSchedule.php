<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Create Exam Schedule";
$instructor_id = $_SESSION['ID'];

// Get instructor's courses
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code, c.semester
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get exam categories
$categoriesQuery = $con->query("SELECT * FROM exam_categories ORDER BY category_name");

// Get existing exams to check limits
$existingExamsQuery = $con->prepare("SELECT 
    es.course_id,
    c.semester,
    ec.category_name,
    COUNT(*) as exam_count
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    AND ec.category_name IN ('Midterm', 'Final')
    GROUP BY es.course_id, c.semester, ec.category_name");
$existingExamsQuery->bind_param("i", $instructor_id);
$existingExamsQuery->execute();
$existingExams = $existingExamsQuery->get_result();

// Build array of existing exams for validation
$examLimits = [];
while($exam = $existingExams->fetch_assoc()) {
    $key = $exam['course_id'] . '_' . $exam['semester'] . '_' . $exam['category_name'];
    $examLimits[$key] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam Schedule - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        
        .form-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #003366; box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        
        .btn-modern { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .btn-secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1>📅 Create Exam Schedule</h1>
                <p>Schedule a new exam for your course</p>
            </div>

            <div class="form-card">
                <?php if(isset($_SESSION['error'])): ?>
                <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <strong style="color: #dc3545;">Error:</strong>
                    <p style="margin: 0.5rem 0 0 0; color: #721c24;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
                <?php endif; ?>

                <div style="background: rgba(0, 123, 255, 0.1); border-left: 4px solid #007bff; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <strong style="color: #004085;">📋 Exam Scheduling Rules:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; color: #004085;">
                        <li><strong>Midterm Exam:</strong> Only 1 per course per semester</li>
                        <li><strong>Final Exam:</strong> Only 1 per course per semester</li>
                        <li><strong>Quizzes:</strong> Unlimited - schedule as many as needed</li>
                        <li><strong>Minimum Questions:</strong> At least 5 questions required before submission to ensure exam validity</li>
                    </ul>
                </div>

                <form action="SaveSchedule.php" method="POST" id="scheduleForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course_id">Course *</label>
                            <select id="course_id" name="course_id" required onchange="onCourseChange()">
                                <option value="">Select Course</option>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $course['course_id']; ?>" data-semester="<?php echo $course['semester']; ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?> (<?php echo $course['course_code']; ?>) - Semester <?php echo $course['semester']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="exam_category_id">Exam Category *</label>
                            <select id="exam_category_id" name="exam_category_id" required onchange="updateExamName(); checkExamLimits();">
                                <option value="">Select Category</option>
                                <?php 
                                $categoriesQuery->data_seek(0);
                                while($category = $categoriesQuery->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $category['exam_category_id']; ?>" data-category="<?php echo $category['category_name']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                            <div id="categoryWarning" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: rgba(220, 53, 69, 0.1); border-radius: 4px; color: #dc3545; font-size: 0.9rem;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="exam_name">Exam Name *</label>
                        <select id="exam_name" name="exam_name" required disabled>
                            <option value="">Select exam category first</option>
                        </select>
                        <small style="color: #6c757d; display: block; margin-top: 0.25rem;">
                            Midterm & Final are auto-named. Select a number for Quiz, Assignment, or Test.
                        </small>
                    </div>

                    <!-- Topic Selection for Quizzes -->
                    <div class="form-group" id="topicSelectionGroup" style="display: none;">
                        <label for="topic_id">Select Topic for Quiz (Optional)</label>
                        <select id="topic_id" name="topic_id" onchange="loadQuestionsByTopic()">
                            <option value="">All Topics</option>
                        </select>
                        <small style="color: #6c757d; display: block; margin-top: 0.25rem;">
                            📚 Select a specific topic/chapter to include questions from that topic only, or leave as "All Topics"
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="exam_date">Exam Date *</label>
                            <input type="date" id="exam_date" name="exam_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="start_time">Start Time *</label>
                            <input type="time" id="start_time" name="start_time" required>
                        </div>

                        <div class="form-group">
                            <label for="end_time">End Time *</label>
                            <input type="time" id="end_time" name="end_time" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="duration_minutes">Duration (minutes) *</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" required min="1" placeholder="e.g., 60">
                        </div>

                        <div class="form-group">
                            <label for="total_marks">Total Marks</label>
                            <input type="number" id="total_marks" name="total_marks" min="0" value="0" placeholder="Auto-calculated from questions">
                            <small style="color: #6c757d; display: block; margin-top: 0.25rem;">
                                ℹ️ Will be automatically calculated based on question points when you add questions
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="pass_marks">Pass Percentage *</label>
                            <input type="number" id="pass_marks" name="pass_marks" required min="1" max="100" value="50" placeholder="e.g., 50">
                            <small style="color: #6c757d; display: block; margin-top: 0.25rem;">
                                Enter percentage (e.g., 50 for 50%). Pass marks will be calculated automatically.
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="instructions">Instructions</label>
                        <textarea id="instructions" name="instructions" placeholder="Enter exam instructions for students..."></textarea>
                    </div>

                    <div id="questionPreview" style="display: none; margin-top: 1.5rem; padding: 1.5rem; background: rgba(0, 123, 255, 0.05); border: 2px solid #007bff; border-radius: 8px;">
                        <h4 style="margin: 0 0 1rem 0; color: #004085;">📝 Questions to be Added</h4>
                        <div id="questionList"></div>
                        <p style="margin: 1rem 0 0 0; color: #004085; font-weight: 600;">
                            Total: <span id="previewTotal">0</span> questions | <span id="previewMarks">0</span> marks
                        </p>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn-modern btn-primary">
                            ✅ Create Schedule
                        </button>
                        <a href="ManageSchedules.php" class="btn-modern btn-secondary">
                            ← Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        // Exam limits data from PHP
        const examLimits = <?php echo json_encode($examLimits); ?>;
        
        // Exam name options based on category
        const examNameOptions = {
            'Midterm': ['Midterm Exam'],
            'Final': ['Final Exam'],
            'Quiz': ['Quiz 1', 'Quiz 2', 'Quiz 3', 'Quiz 4', 'Quiz 5', 'Quiz 6', 'Quiz 7', 'Quiz 8', 'Quiz 9', 'Quiz 10'],
            'Assignment': ['Assignment 1', 'Assignment 2', 'Assignment 3', 'Assignment 4', 'Assignment 5', 'Assignment 6', 'Assignment 7', 'Assignment 8', 'Assignment 9', 'Assignment 10'],
            'Test': ['Test 1', 'Test 2', 'Test 3', 'Test 4', 'Test 5', 'Test 6', 'Test 7', 'Test 8', 'Test 9', 'Test 10']
        };
        
        // Handle course selection change
        function onCourseChange() {
            // Check exam limits
            checkExamLimits();
            // Load question preview
            loadQuestionPreview();
            
            // If Quiz is already selected, load topics
            const categorySelect = document.getElementById('exam_category_id');
            if(categorySelect.value) {
                const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
                const categoryName = selectedCategory.getAttribute('data-category');
                if(categoryName === 'Quiz') {
                    loadTopicsForCourse();
                }
            }
        }
        
        // Update exam name dropdown based on category
        function updateExamName() {
            const categorySelect = document.getElementById('exam_category_id');
            const examNameSelect = document.getElementById('exam_name');
            const topicGroup = document.getElementById('topicSelectionGroup');
            
            if(!categorySelect.value) {
                examNameSelect.disabled = true;
                examNameSelect.innerHTML = '<option value="">Select exam category first</option>';
                topicGroup.style.display = 'none';
                return;
            }
            
            const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
            const categoryName = selectedCategory.getAttribute('data-category');
            
            // Show topic selection only for Quiz
            if(categoryName === 'Quiz') {
                topicGroup.style.display = 'block';
                // Load topics only if course is selected
                const courseId = document.getElementById('course_id').value;
                if(courseId) {
                    loadTopicsForCourse();
                }
            } else {
                topicGroup.style.display = 'none';
                document.getElementById('topic_id').value = '';
            }
            
            // Get options for this category
            const options = examNameOptions[categoryName] || [categoryName + ' Exam'];
            
            // For Midterm and Final, auto-select the only option
            if(categoryName === 'Midterm' || categoryName === 'Final') {
                examNameSelect.innerHTML = `<option value="${options[0]}" selected>${options[0]}</option>`;
                examNameSelect.disabled = false; // Keep enabled so value submits
                examNameSelect.style.backgroundColor = '#f8f9fa';
                examNameSelect.style.cursor = 'not-allowed';
            } else {
                // For Quiz, Assignment, Test - show dropdown
                examNameSelect.innerHTML = '<option value="">Select ' + categoryName.toLowerCase() + ' number</option>';
                options.forEach(option => {
                    examNameSelect.innerHTML += `<option value="${option}">${option}</option>`;
                });
                examNameSelect.disabled = false;
                examNameSelect.style.backgroundColor = 'white';
                examNameSelect.style.cursor = 'pointer';
            }
        }
        
        // Load topics for selected course
        function loadTopicsForCourse() {
            const courseId = document.getElementById('course_id').value;
            const topicSelect = document.getElementById('topic_id');
            
            if(!courseId) {
                topicSelect.innerHTML = '<option value="">Select course first</option>';
                return;
            }
            
            // Show loading state
            topicSelect.innerHTML = '<option value="">Loading topics...</option>';
            topicSelect.disabled = true;
            
            console.log('Loading topics for course:', courseId);
            
            // Fetch topics via AJAX
            fetch('GetCourseTopics.php?course_id=' + courseId)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.text(); // Get as text first to see what we're getting
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed data:', data);
                        
                        topicSelect.disabled = false;
                        topicSelect.innerHTML = '<option value="">All Topics</option>';
                        
                        if(data.error) {
                            console.error('Error from server:', data.error);
                            topicSelect.innerHTML += '<option value="" disabled>⚠️ ' + data.error + '</option>';
                            return;
                        }
                        
                        if(data.topics && data.topics.length > 0) {
                            data.topics.forEach(topic => {
                                const chapterText = topic.chapter_number ? `Ch.${topic.chapter_number}: ` : '';
                                topicSelect.innerHTML += `<option value="${topic.topic_id}">${chapterText}${topic.topic_name}</option>`;
                            });
                            console.log('Loaded', data.topics.length, 'topics');
                        } else {
                            // No topics available - this is OK, user can still create quiz with all questions
                            topicSelect.innerHTML += '<option value="" disabled style="color: #6c757d;">ℹ️ No topics defined for this course</option>';
                            console.log('No topics found for this course');
                        }
                    } catch(e) {
                        console.error('JSON parse error:', e);
                        topicSelect.disabled = false;
                        topicSelect.innerHTML = '<option value="">All Topics</option>';
                        topicSelect.innerHTML += '<option value="" disabled style="color: #dc3545;">⚠️ Invalid response from server</option>';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    topicSelect.disabled = false;
                    topicSelect.innerHTML = '<option value="">All Topics</option>';
                    topicSelect.innerHTML += '<option value="" disabled style="color: #dc3545;">⚠️ Could not load topics</option>';
                });
        }
        
        // Load questions by topic
        function loadQuestionsByTopic() {
            const courseId = document.getElementById('course_id').value;
            const topicId = document.getElementById('topic_id').value;
            const previewDiv = document.getElementById('questionPreview');
            const questionList = document.getElementById('questionList');
            const previewTotal = document.getElementById('previewTotal');
            const previewMarks = document.getElementById('previewMarks');
            
            if(!courseId) {
                previewDiv.style.display = 'none';
                return;
            }
            
            // Build URL with optional topic filter
            let url = 'GetCourseQuestions.php?course_id=' + courseId;
            if(topicId) {
                url += '&topic_id=' + topicId;
            }
            
            // Fetch questions
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if(data.questions && data.questions.length > 0) {
                        questionList.innerHTML = '';
                        let totalMarks = 0;
                        
                        data.questions.forEach((q, index) => {
                            totalMarks += parseInt(q.point_value);
                            const topicBadge = q.topic_name ? `<span style="background: #e3f2fd; color: #1976d2; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; margin-left: 0.5rem;">${q.topic_name}</span>` : '';
                            questionList.innerHTML += `
                                <div style="padding: 0.5rem; margin-bottom: 0.5rem; background: white; border-radius: 4px; border-left: 3px solid #28a745;">
                                    <strong>${index + 1}.</strong> ${q.question_text.substring(0, 80)}... ${topicBadge}
                                    <span style="float: right; color: #28a745; font-weight: 600;">${q.point_value} pts</span>
                                </div>
                            `;
                        });
                        
                        previewTotal.textContent = data.questions.length;
                        previewMarks.textContent = totalMarks;
                        previewDiv.style.display = 'block';
                        
                        // Auto-fill total marks
                        document.getElementById('total_marks').value = totalMarks;
                    } else {
                        const topicText = topicId ? 'this topic' : 'this course';
                        questionList.innerHTML = `<p style="color: #856404; background: rgba(255, 193, 7, 0.1); padding: 1rem; border-radius: 4px;">⚠️ No questions found for ${topicText}. You can add questions after creating the schedule.</p>`;
                        previewTotal.textContent = 0;
                        previewMarks.textContent = 0;
                        previewDiv.style.display = 'block';
                        document.getElementById('total_marks').value = 0;
                    }
                })
                .catch(error => {
                    console.error('Error loading questions:', error);
                    previewDiv.style.display = 'none';
                });
        }
        
        // Auto-calculate duration when times are selected
        document.getElementById('start_time').addEventListener('change', calculateDuration);
        document.getElementById('end_time').addEventListener('change', calculateDuration);

        function calculateDuration() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if(startTime && endTime) {
                const start = new Date('2000-01-01 ' + startTime);
                const end = new Date('2000-01-01 ' + endTime);
                const diff = (end - start) / 1000 / 60; // minutes
                
                if(diff > 0) {
                    document.getElementById('duration_minutes').value = diff;
                }
            }
        }
        
        // Check exam limits
        function checkExamLimits() {
            const courseSelect = document.getElementById('course_id');
            const categorySelect = document.getElementById('exam_category_id');
            const warningDiv = document.getElementById('categoryWarning');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if(!courseSelect.value || !categorySelect.value) {
                warningDiv.style.display = 'none';
                submitBtn.disabled = false;
                return;
            }
            
            const selectedCourse = courseSelect.options[courseSelect.selectedIndex];
            const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
            
            const courseId = courseSelect.value;
            const semester = selectedCourse.getAttribute('data-semester');
            const categoryName = selectedCategory.getAttribute('data-category');
            
            // Check if this is a limited exam type (Midterm or Final)
            if(categoryName === 'Midterm' || categoryName === 'Final') {
                const key = courseId + '_' + semester + '_' + categoryName;
                
                if(examLimits[key]) {
                    warningDiv.innerHTML = '⚠️ <strong>Limit Reached:</strong> This course already has a ' + categoryName + ' exam scheduled for Semester ' + semester + '. You can only have one ' + categoryName + ' exam per course per semester.';
                    warningDiv.style.display = 'block';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                } else {
                    warningDiv.style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                }
            } else {
                // Quiz or other category - no limit
                warningDiv.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
        }
        
        // Validate form before submission
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('button[type="submit"]');
            if(submitBtn.disabled) {
                e.preventDefault();
                alert('Cannot create this exam schedule. Please check the warning message.');
                return false;
            }
            
            // Check required fields
            const requiredFields = [
                { id: 'course_id', name: 'Course' },
                { id: 'exam_category_id', name: 'Exam Category' },
                { id: 'exam_name', name: 'Exam Name' },
                { id: 'exam_date', name: 'Exam Date' },
                { id: 'start_time', name: 'Start Time' },
                { id: 'end_time', name: 'End Time' },
                { id: 'duration_minutes', name: 'Duration' },
                { id: 'pass_marks', name: 'Pass Percentage' }
            ];
            
            const missingFields = [];
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if(!element.value || element.value == '0' || element.value == '') {
                    missingFields.push(field.name);
                    element.style.borderColor = '#dc3545';
                    element.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                } else {
                    element.style.borderColor = '#e0e0e0';
                    element.style.boxShadow = 'none';
                }
            });
            
            if(missingFields.length > 0) {
                e.preventDefault();
                alert('⚠️ Missing required fields:\n\n• ' + missingFields.join('\n• '));
                // Scroll to first missing field
                const firstMissing = document.getElementById(requiredFields.find(f => missingFields.includes(f.name)).id);
                firstMissing.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstMissing.focus();
                return false;
            }
        });
        
        // Preview questions when course is selected
        document.getElementById('course_id').addEventListener('change', loadQuestionPreview);
        
        function loadQuestionPreview() {
            const courseId = document.getElementById('course_id').value;
            const previewDiv = document.getElementById('questionPreview');
            const questionList = document.getElementById('questionList');
            const previewTotal = document.getElementById('previewTotal');
            const previewMarks = document.getElementById('previewMarks');
            
            if(!courseId) {
                previewDiv.style.display = 'none';
                return;
            }
            
            // Fetch questions for this course via AJAX
            fetch('GetCourseQuestions.php?course_id=' + courseId)
                .then(response => response.json())
                .then(data => {
                    if(data.questions && data.questions.length > 0) {
                        questionList.innerHTML = '';
                        let totalMarks = 0;
                        
                        data.questions.forEach((q, index) => {
                            totalMarks += parseInt(q.point_value);
                            questionList.innerHTML += `
                                <div style="padding: 0.5rem; margin-bottom: 0.5rem; background: white; border-radius: 4px; border-left: 3px solid #28a745;">
                                    <strong>${index + 1}.</strong> ${q.question_text.substring(0, 80)}... 
                                    <span style="float: right; color: #28a745; font-weight: 600;">${q.point_value} pts</span>
                                </div>
                            `;
                        });
                        
                        previewTotal.textContent = data.questions.length;
                        previewMarks.textContent = totalMarks;
                        previewDiv.style.display = 'block';
                        
                        // Auto-fill total marks
                        document.getElementById('total_marks').value = totalMarks;
                    } else {
                        questionList.innerHTML = '<p style="color: #856404; background: rgba(255, 193, 7, 0.1); padding: 1rem; border-radius: 4px;">⚠️ No approved questions found for this course. You can add questions after creating the schedule.</p>';
                        previewTotal.textContent = 0;
                        previewMarks.textContent = 0;
                        previewDiv.style.display = 'block';
                        document.getElementById('total_marks').value = 0;
                    }
                })
                .catch(error => {
                    console.error('Error loading questions:', error);
                    previewDiv.style.display = 'none';
                });
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
