<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Add Question";
$instructor_id = $_SESSION['ID'];

// Check for preselected exam or course
$preselect_exam_id = $_GET['exam_id'] ?? null;
$preselect_course_id = $_GET['course_id'] ?? null;

$preselectedExam = null;
if($preselect_exam_id) {
    $examQuery = $con->prepare("SELECT es.*, c.course_name, c.course_code, ec.category_name
        FROM exams es
        INNER JOIN courses c ON es.course_id = c.course_id
        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
        WHERE es.exam_id = ?");
    $examQuery->bind_param("i", $preselect_exam_id);
    $examQuery->execute();
    $preselectedExam = $examQuery->get_result()->fetch_assoc();
    
    if($preselectedExam && !$preselect_course_id) {
        $preselect_course_id = $preselectedExam['course_id'];
    }
}

// Get instructor's courses
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_code, c.course_name, c.semester
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ?
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get topics
$topicsQuery = $con->query("SELECT topic_id, topic_name FROM question_topics ORDER BY topic_name");
$topics = $topicsQuery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        
        .page-header-modern {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
            margin-bottom: 2rem;
        }
        
        .page-header-modern h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .form-section {
            margin-bottom: 2.5rem;
        }
        
        .form-section:last-child {
            margin-bottom: 0;
        }
        
        .form-section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #003366;
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #003366;
            font-size: 0.95rem;
        }
        
        .form-group label .required {
            color: #dc3545;
            margin-left: 0.25rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .question-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .type-option {
            padding: 1.5rem;
            border: 3px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .type-option:hover {
            border-color: #003366;
            background: rgba(0, 51, 102, 0.05);
        }
        
        .type-option.active {
            border-color: #003366;
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.1), rgba(0, 85, 170, 0.1));
        }
        
        .type-option input[type="radio"] {
            display: none;
        }
        
        .type-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
        }
        
        .type-label {
            font-weight: 700;
            color: #003366;
            font-size: 1.1rem;
        }
        
        .options-container {
            display: grid;
            gap: 1rem;
        }
        
        .option-row {
            display: grid;
            grid-template-columns: 60px 1fr 100px;
            gap: 1rem;
            align-items: center;
        }
        
        .option-label {
            font-weight: 700;
            color: #003366;
            font-size: 1.1rem;
            text-align: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .option-input {
            flex: 1;
        }
        
        .correct-radio {
            text-align: center;
        }
        
        .correct-radio input[type="radio"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }
        
        .btn {
            padding: 0.85rem 1.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e0e0e0;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 1.25rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .info-box h4 {
            margin: 0 0 0.5rem 0;
            color: #003366;
            font-size: 1rem;
        }
        
        .info-box p {
            margin: 0;
            color: #004085;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
            .option-row { grid-template-columns: 50px 1fr 80px; }
            .question-type-selector { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-modern">
                <h1>➕ Add New Question</h1>
                <p>Create a new question for your question bank</p>
            </div>

            <?php if($preselectedExam): ?>
            <div class="info-box">
                <h4>📝 Adding Question to Exam</h4>
                <p>
                    <strong><?php echo htmlspecialchars($preselectedExam['exam_name']); ?></strong> - 
                    <?php echo htmlspecialchars($preselectedExam['course_name']); ?> (<?php echo $preselectedExam['course_code']; ?>)
                </p>
            </div>
            <?php endif; ?>

            <form method="POST" action="InsertQuestion.php" id="questionForm">
                <input type="hidden" name="instructor_id" value="<?php echo $instructor_id; ?>">
                <?php if($preselect_exam_id): ?>
                <input type="hidden" name="exam_id" value="<?php echo $preselect_exam_id; ?>">
                <?php endif; ?>

                <div class="form-card">
                    <!-- Question Type Selection -->
                    <div class="form-section">
                        <h3 class="form-section-title">Question Type</h3>
                        <div class="question-type-selector">
                            <label class="type-option active" id="mcqOption">
                                <input type="radio" name="question_type" value="mcq" checked onchange="toggleQuestionType()">
                                <div class="type-icon">📝</div>
                                <div class="type-label">Multiple Choice</div>
                            </label>
                            <label class="type-option" id="tfOption">
                                <input type="radio" name="question_type" value="tf" onchange="toggleQuestionType()">
                                <div class="type-icon">✓✗</div>
                                <div class="type-label">True / False</div>
                            </label>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="form-section-title">Basic Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Course <span class="required">*</span></label>
                                <select name="course_id" class="form-control" required <?php echo $preselect_course_id ? 'disabled' : ''; ?>>
                                    <option value="">Select Course</option>
                                    <?php 
                                    $courses->data_seek(0);
                                    while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_id']; ?>" 
                                        <?php echo ($preselect_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <?php if($preselect_course_id): ?>
                                <input type="hidden" name="course_id" value="<?php echo $preselect_course_id; ?>">
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label>Topic (Optional)</label>
                                <select name="topic_id" class="form-control">
                                    <option value="">Select Topic</option>
                                    <?php while($topic = $topics->fetch_assoc()): ?>
                                    <option value="<?php echo $topic['topic_id']; ?>">
                                        <?php echo htmlspecialchars($topic['topic_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Point Value <span class="required">*</span></label>
                                <input type="number" name="point_value" class="form-control" value="1" min="1" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Question Text <span class="required">*</span></label>
                            <textarea name="question_text" class="form-control" placeholder="Enter your question here..." required></textarea>
                        </div>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div class="form-section" id="mcqSection">
                        <h3 class="form-section-title">Answer Options</h3>
                        
                        <div class="options-container">
                            <div class="option-row">
                                <div class="option-label">A</div>
                                <div class="option-input">
                                    <input type="text" name="option_a" class="form-control" placeholder="Enter option A" required>
                                </div>
                                <div class="correct-radio">
                                    <input type="radio" name="correct_answer" value="A" required>
                                    <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem;">Correct</div>
                                </div>
                            </div>

                            <div class="option-row">
                                <div class="option-label">B</div>
                                <div class="option-input">
                                    <input type="text" name="option_b" class="form-control" placeholder="Enter option B" required>
                                </div>
                                <div class="correct-radio">
                                    <input type="radio" name="correct_answer" value="B">
                                    <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem;">Correct</div>
                                </div>
                            </div>

                            <div class="option-row">
                                <div class="option-label">C</div>
                                <div class="option-input">
                                    <input type="text" name="option_c" class="form-control" placeholder="Enter option C (optional)">
                                </div>
                                <div class="correct-radio">
                                    <input type="radio" name="correct_answer" value="C">
                                    <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem;">Correct</div>
                                </div>
                            </div>

                            <div class="option-row">
                                <div class="option-label">D</div>
                                <div class="option-input">
                                    <input type="text" name="option_d" class="form-control" placeholder="Enter option D (optional)">
                                </div>
                                <div class="correct-radio">
                                    <input type="radio" name="correct_answer" value="D">
                                    <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem;">Correct</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- True/False Options -->
                    <div class="form-section" id="tfSection" style="display: none;">
                        <h3 class="form-section-title">Select Correct Answer</h3>
                        
                        <div class="question-type-selector">
                            <label class="type-option">
                                <input type="radio" name="correct_answer_tf" value="True">
                                <div class="type-icon">✓</div>
                                <div class="type-label">True</div>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="correct_answer_tf" value="False">
                                <div class="type-icon">✗</div>
                                <div class="type-label">False</div>
                            </label>
                        </div>
                    </div>

                    <!-- Explanation (Optional) -->
                    <div class="form-section">
                        <h3 class="form-section-title">Explanation (Optional)</h3>
                        <div class="form-group">
                            <label>Provide an explanation for the correct answer</label>
                            <textarea name="explanation" class="form-control" placeholder="Explain why this is the correct answer..." rows="4"></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>💾</span> Save Question
                        </button>
                        <button type="submit" name="save_and_add_another" value="1" class="btn btn-success">
                            <span>➕</span> Save & Add Another
                        </button>
                        <a href="ManageQuestions.php" class="btn btn-secondary">
                            <span>❌</span> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function toggleQuestionType() {
            const questionType = document.querySelector('input[name="question_type"]:checked').value;
            const mcqSection = document.getElementById('mcqSection');
            const tfSection = document.getElementById('tfSection');
            const mcqOption = document.getElementById('mcqOption');
            const tfOption = document.getElementById('tfOption');
            
            if (questionType === 'mcq') {
                mcqSection.style.display = 'block';
                tfSection.style.display = 'none';
                mcqOption.classList.add('active');
                tfOption.classList.remove('active');
                
                // Enable MCQ fields
                document.querySelectorAll('#mcqSection input').forEach(input => {
                    if(input.name === 'option_a' || input.name === 'option_b') {
                        input.required = true;
                    }
                });
                
                // Disable TF fields
                document.querySelectorAll('#tfSection input').forEach(input => {
                    input.required = false;
                });
            } else {
                mcqSection.style.display = 'none';
                tfSection.style.display = 'block';
                mcqOption.classList.remove('active');
                tfOption.classList.add('active');
                
                // Disable MCQ fields
                document.querySelectorAll('#mcqSection input').forEach(input => {
                    input.required = false;
                });
                
                // Enable TF fields
                document.querySelectorAll('#tfSection input[type="radio"]').forEach(input => {
                    input.required = true;
                });
            }
        }
        
        // Handle form submission for True/False
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const questionType = document.querySelector('input[name="question_type"]:checked').value;
            
            if (questionType === 'tf') {
                const tfAnswer = document.querySelector('input[name="correct_answer_tf"]:checked');
                
                if (!tfAnswer) {
                    e.preventDefault();
                    alert('Please select True or False as the correct answer');
                    return;
                }
                
                // Set options for True/False
                document.querySelector('input[name="option_a"]').value = 'True';
                document.querySelector('input[name="option_b"]').value = 'False';
                document.querySelector('input[name="option_c"]').value = '';
                document.querySelector('input[name="option_d"]').value = '';
                
                // Set correct answer
                if (tfAnswer.value === 'True') {
                    document.querySelector('input[name="correct_answer"][value="A"]').checked = true;
                } else {
                    document.querySelector('input[name="correct_answer"][value="B"]').checked = true;
                }
            }
        });
    </script>
</body>
</html>
<?php $con->close(); ?>
