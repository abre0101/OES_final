<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Manage Questions";
$instructor_id = $_SESSION['ID'];

// Get instructor's courses for filtering
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$instructorCourses = $coursesQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .action-buttons { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .btn-modern { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); }
        .btn-secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.875rem; }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
        
        .exam-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 4px solid #003366; transition: all 0.3s ease; }
        .exam-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12); }
        .exam-card h3 { margin: 0 0 0.5rem 0; color: #003366; font-size: 1.3rem; font-weight: 700; }
        
        .tabs-container { background: white; border-radius: 12px; padding: 0; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); overflow: hidden; }
        .tabs-header { display: flex; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; }
        .tab-btn { flex: 1; padding: 1rem 1.5rem; border: none; background: transparent; font-weight: 600; font-size: 0.95rem; color: #6c757d; cursor: pointer; transition: all 0.3s ease; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #003366; border-bottom-color: #003366; background: white; }
        .tab-btn:hover { background: rgba(0, 51, 102, 0.05); }
        .tab-content { display: none; padding: 2rem; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📝</span> Question Bank</h1>
                <p>Create, edit, and organize your questions by course and topic</p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="AddQuestion.php" class="btn-modern btn-primary">
                    ➕ Create New Question
                </a>
                <a href="ManageTopics.php" class="btn-modern btn-success">
                    🗂️ Manage Topics
                </a>
                <a href="ManageExams.php" class="btn-modern btn-secondary">
                    📋 View Exam Schedules
                </a>
            </div>

            <!-- Questions List -->
            <div class="tabs-container">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="switchTab(0)">By Exam</button>
                    <button class="tab-btn" onclick="switchTab(1)">By Course</button>
                    <button class="tab-btn" onclick="switchTab(2)">By Topic</button>
                </div>

                <!-- By Exam Tab -->
                <div class="tab-content active">
                    <?php
                    // Get exams for instructor's courses with question counts
                    $examsQuery = $con->prepare("SELECT 
                        es.schedule_id,
                        es.exam_name,
                        c.course_name,
                        c.course_code,
                        ec.category_name,
                        COUNT(DISTINCT eq.question_id) as question_count
                        FROM exam_schedules es
                        INNER JOIN courses c ON es.course_id = c.course_id
                        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                        INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
                        LEFT JOIN exam_questions eq ON es.schedule_id = eq.schedule_id
                        WHERE ic.instructor_id = ? AND ic.is_active = TRUE
                        GROUP BY es.schedule_id
                        ORDER BY es.exam_date DESC");
                    $examsQuery->bind_param("i", $instructor_id);
                    $examsQuery->execute();
                    $exams = $examsQuery->get_result();
                    
                    if($exams->num_rows > 0):
                        while($exam = $exams->fetch_assoc()):
                    ?>
                    <div class="exam-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                <p style="margin: 0.5rem 0 0 0; color: #6c757d;">
                                    <?php echo htmlspecialchars($exam['course_name']); ?> (<?php echo $exam['course_code']; ?>) - 
                                    <?php echo htmlspecialchars($exam['category_name']); ?>
                                </p>
                                <p style="margin: 0.5rem 0 0 0; color: #6c757d;">
                                    <strong><?php echo $exam['question_count']; ?></strong> questions
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="ViewExam.php?id=<?php echo $exam['schedule_id']; ?>" class="btn-modern btn-primary btn-sm">
                                    👁️ View All
                                </a>
                                <a href="AddQuestion.php?schedule_id=<?php echo $exam['schedule_id']; ?>" class="btn-modern btn-success btn-sm">
                                    ➕ Add Question
                                </a>
                            </div>
                        </div>
                        
                        <?php
                        // Get recent questions for this exam
                        $questionsQuery = $con->prepare("SELECT q.question_id, q.question_text
                            FROM exam_questions eq
                            INNER JOIN questions q ON eq.question_id = q.question_id
                            WHERE eq.schedule_id = ?
                            ORDER BY eq.question_order
                            LIMIT 3");
                        $questionsQuery->bind_param("i", $exam['schedule_id']);
                        $questionsQuery->execute();
                        $examQuestions = $questionsQuery->get_result();
                        
                        if($examQuestions->num_rows > 0):
                        ?>
                        <div style="border-top: 2px solid #e0e0e0; padding-top: 1rem; margin-top: 1rem;">
                            <strong style="color: #6c757d; font-size: 0.9rem;">Recent Questions:</strong>
                            <?php while($q = $examQuestions->fetch_assoc()): ?>
                            <div style="background: #f8f9fa; padding: 1rem; margin-top: 0.75rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <p style="margin: 0; color: #212529;">
                                        <?php echo substr(htmlspecialchars($q['question_text']), 0, 100); ?>...
                                    </p>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="EditQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn-modern btn-primary btn-sm">✏️</a>
                                    <button class="btn-modern btn-danger btn-sm" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)">🗑️</button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div style="text-align: center; padding: 1rem; color: #6c757d; border-top: 2px solid #e0e0e0; margin-top: 1rem;">
                            No questions yet. <a href="AddQuestion.php?schedule_id=<?php echo $exam['schedule_id']; ?>">Add your first question</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <h3 style="color: #6c757d;">No exams scheduled yet</h3>
                            <p>Schedule an exam for your courses first, then add questions to it</p>
                            <a href="ManageExams.php" class="btn-modern btn-primary" style="margin-top: 1rem;">View Exams</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- By Course Tab -->
                <div class="tab-content">
                    <?php
                    // Check if viewing all questions for a specific course
                    $viewAllCourse = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;
                    
                    // Get questions grouped by course for this instructor
                    $instructorCourses->data_seek(0);
                    $hasCourses = false;
                    while($course = $instructorCourses->fetch_assoc()):
                        // Skip if viewing all for a different course
                        if($viewAllCourse && $viewAllCourse != $course['course_id']) continue;
                        
                        $courseQuestionsQuery = $con->prepare("SELECT COUNT(*) as count 
                            FROM questions 
                            WHERE course_id = ? AND instructor_id = ?");
                        $courseQuestionsQuery->bind_param("ii", $course['course_id'], $instructor_id);
                        $courseQuestionsQuery->execute();
                        $count = $courseQuestionsQuery->get_result()->fetch_assoc()['count'];
                        
                        if($count > 0):
                            $hasCourses = true;
                    ?>
                    <div class="exam-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3>📚 <?php echo htmlspecialchars($course['course_name']); ?></h3>
                                <p style="margin: 0.5rem 0 0 0; color: #6c757d;">
                                    <?php echo $course['course_code']; ?>
                                </p>
                                <p style="margin: 0.5rem 0 0 0; color: #6c757d;">
                                    <strong><?php echo $count; ?></strong> questions
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <?php if(!$viewAllCourse): ?>
                                <a href="?course_id=<?php echo $course['course_id']; ?>" class="btn-modern btn-primary btn-sm" onclick="switchTab(1)">
                                    👁️ View All
                                </a>
                                <?php endif; ?>
                                <a href="AddQuestion.php?course_id=<?php echo $course['course_id']; ?>" class="btn-modern btn-success btn-sm">
                                    ➕ Add Question
                                </a>
                            </div>
                        </div>
                        
                        <?php
                        // Show limited or all questions based on view mode
                        $limit = $viewAllCourse ? "" : "LIMIT 3";
                        $questionsQuery = $con->prepare("SELECT q.*, qt.topic_name
                            FROM questions q
                            LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
                            WHERE q.course_id = ? AND q.instructor_id = ?
                            ORDER BY q.created_at DESC
                            $limit");
                        $questionsQuery->bind_param("ii", $course['course_id'], $instructor_id);
                        $questionsQuery->execute();
                        $questions = $questionsQuery->get_result();
                        
                        if($questions->num_rows > 0):
                        ?>
                        <div style="border-top: 2px solid #e0e0e0; padding-top: 1rem; margin-top: 1rem;">
                            <strong style="color: #6c757d; font-size: 0.9rem;">
                                <?php echo $viewAllCourse ? 'All Questions:' : 'Recent Questions:'; ?>
                            </strong>
                            <?php 
                            $qnum = 1;
                            while($q = $questions->fetch_assoc()): 
                                if($viewAllCourse):
                                    // Full question display with options
                            ?>
                            <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-top: 1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); border-left: 4px solid #003366;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <h4 style="margin: 0; color: #003366;">Question <?php echo $qnum++; ?></h4>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if($q['topic_name']): ?>
                                        <span style="background: #f8f9fa; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($q['topic_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                        <span style="background: #f8f9fa; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                            <?php echo $q['point_value']; ?> pts
                                        </span>
                                    </div>
                                </div>
                                
                                <p style="font-size: 1.1rem; line-height: 1.6; margin: 1rem 0; color: #212529;">
                                    <?php echo htmlspecialchars($q['question_text']); ?>
                                </p>
                                
                                <div style="margin-top: 1rem;">
                                    <strong style="display: block; margin-bottom: 0.75rem; color: #003366;">Options:</strong>
                                    
                                    <div style="padding: 0.75rem; margin: 0.5rem 0; background: #f8f9fa; border-radius: 8px; border-left: 3px solid <?php echo ($q['correct_answer'] == 'A') ? '#28a745' : '#e0e0e0'; ?>; <?php echo ($q['correct_answer'] == 'A') ? 'background: rgba(40, 167, 69, 0.1);' : ''; ?>">
                                        <strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                                        <?php if($q['correct_answer'] == 'A'): ?>
                                            <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="padding: 0.75rem; margin: 0.5rem 0; background: #f8f9fa; border-radius: 8px; border-left: 3px solid <?php echo ($q['correct_answer'] == 'B') ? '#28a745' : '#e0e0e0'; ?>; <?php echo ($q['correct_answer'] == 'B') ? 'background: rgba(40, 167, 69, 0.1);' : ''; ?>">
                                        <strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                                        <?php if($q['correct_answer'] == 'B'): ?>
                                            <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if($q['option_c']): ?>
                                    <div style="padding: 0.75rem; margin: 0.5rem 0; background: #f8f9fa; border-radius: 8px; border-left: 3px solid <?php echo ($q['correct_answer'] == 'C') ? '#28a745' : '#e0e0e0'; ?>; <?php echo ($q['correct_answer'] == 'C') ? 'background: rgba(40, 167, 69, 0.1);' : ''; ?>">
                                        <strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                                        <?php if($q['correct_answer'] == 'C'): ?>
                                            <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if($q['option_d']): ?>
                                    <div style="padding: 0.75rem; margin: 0.5rem 0; background: #f8f9fa; border-radius: 8px; border-left: 3px solid <?php echo ($q['correct_answer'] == 'D') ? '#28a745' : '#e0e0e0'; ?>; <?php echo ($q['correct_answer'] == 'D') ? 'background: rgba(40, 167, 69, 0.1);' : ''; ?>">
                                        <strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                                        <?php if($q['correct_answer'] == 'D'): ?>
                                            <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($q['revision_comments']): ?>
                                <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(255,193,7,0.1); border-left: 3px solid #ffc107; border-radius: 4px;">
                                    <strong style="font-size: 0.85rem; color: #f57c00;">Revision Comments:</strong>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #6c757d;">
                                        <?php echo htmlspecialchars($q['revision_comments']); ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e0e0e0;">
                                    <a href="EditQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn-modern btn-primary btn-sm">
                                        ✏️ Edit Question
                                    </a>
                                    <button class="btn-modern btn-danger btn-sm" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </div>
                            <?php else: 
                                // Compact display for recent questions
                            ?>
                            <div style="background: #f8f9fa; padding: 1rem; margin-top: 0.75rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <p style="margin: 0; color: #212529;">
                                        <?php echo substr(htmlspecialchars($q['question_text']), 0, 100); ?>...
                                    </p>
                                    <div style="font-size: 0.85rem; color: #6c757d; margin-top: 0.25rem;">
                                        <?php if($q['topic_name']): ?>
                                        📖 <?php echo htmlspecialchars($q['topic_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="EditQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn-modern btn-primary btn-sm">✏️</a>
                                    <button class="btn-modern btn-danger btn-sm" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)">🗑️</button>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endwhile; ?>
                            
                            <?php if($viewAllCourse): ?>
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <a href="ManageQuestions.php" class="btn-modern btn-secondary btn-sm">
                                    ← Back to Overview
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endif;
                    endwhile;
                    
                    if(!$hasCourses):
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
                            <h3 style="color: #6c757d;">No Questions Yet</h3>
                            <p>Start building your question bank by creating your first question</p>
                            <a href="AddQuestion.php" class="btn-modern btn-primary" style="margin-top: 1rem;">Create First Question</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- By Topic Tab -->
                <div class="tab-content">
                    <?php
                    // Get all topics with question counts
                    $topicsWithQuestionsQuery = $con->prepare("SELECT 
                        qt.topic_id,
                        qt.topic_name,
                        qt.chapter_number,
                        c.course_name,
                        c.course_code,
                        c.course_id,
                        COUNT(q.question_id) as question_count
                        FROM question_topics qt
                        INNER JOIN courses c ON qt.course_id = c.course_id
                        INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
                        LEFT JOIN questions q ON qt.topic_id = q.topic_id AND q.instructor_id = ?
                        WHERE ic.instructor_id = ? AND ic.is_active = TRUE
                        GROUP BY qt.topic_id
                        HAVING question_count > 0
                        ORDER BY c.course_name, qt.chapter_number, qt.topic_name");
                    $topicsWithQuestionsQuery->bind_param("ii", $instructor_id, $instructor_id);
                    $topicsWithQuestionsQuery->execute();
                    $topicsWithQuestions = $topicsWithQuestionsQuery->get_result();
                    
                    if($topicsWithQuestions->num_rows > 0):
                        while($topic = $topicsWithQuestions->fetch_assoc()):
                    ?>
                    <div class="exam-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3>📖 <?php echo htmlspecialchars($topic['topic_name']); ?></h3>
                                <p style="margin: 0.5rem 0 0 0; color: #6c757d;">
                                    <?php echo htmlspecialchars($topic['course_name']); ?> (<?php echo $topic['course_code']; ?>)
                                    <?php if($topic['chapter_number']): ?>
                                    - Chapter <?php echo $topic['chapter_number']; ?>
                                    <?php endif; ?>
                                </p>
                                <p style="margin: 0.5rem 0 0 0; color: #6c757d;">
                                    <strong><?php echo $topic['question_count']; ?></strong> questions
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="ViewTopicQuestions.php?topic_id=<?php echo $topic['topic_id']; ?>" class="btn-modern btn-primary btn-sm">
                                    👁️ View All
                                </a>
                                <a href="AddQuestion.php?topic_id=<?php echo $topic['topic_id']; ?>" class="btn-modern btn-success btn-sm">
                                    ➕ Add Question
                                </a>
                            </div>
                        </div>
                        
                        <?php
                        $topicQuestionsQuery = $con->prepare("SELECT * FROM questions 
                            WHERE topic_id = ? AND instructor_id = ?
                            ORDER BY created_at DESC
                            LIMIT 3");
                        $topicQuestionsQuery->bind_param("ii", $topic['topic_id'], $instructor_id);
                        $topicQuestionsQuery->execute();
                        $topicQuestions = $topicQuestionsQuery->get_result();
                        
                        if($topicQuestions->num_rows > 0):
                        ?>
                        <div style="border-top: 2px solid #e0e0e0; padding-top: 1rem; margin-top: 1rem;">
                            <strong style="color: #6c757d; font-size: 0.9rem;">Recent Questions:</strong>
                            <?php while($q = $topicQuestions->fetch_assoc()): ?>
                            <div style="background: #f8f9fa; padding: 1rem; margin-top: 0.75rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <p style="margin: 0; color: #212529;">
                                        <?php echo substr(htmlspecialchars($q['question_text']), 0, 100); ?>...
                                    </p>
                                    <div style="font-size: 0.85rem; color: #6c757d; margin-top: 0.25rem;">
                                        ⚡ <?php echo $q['difficulty_level']; ?> | 
                                        💯 <?php echo $q['point_value']; ?> pts
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="EditQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn-modern btn-primary btn-sm">✏️</a>
                                    <button class="btn-modern btn-danger btn-sm" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)">🗑️</button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <h3 style="color: #6c757d;">No Topics with Questions</h3>
                            <p>Organize your questions by creating topics first</p>
                            <a href="ManageTopics.php" class="btn-modern btn-primary" style="margin-top: 1rem;">Manage Topics</a>
                        </div>
                    <?php endif; ?>
                </div>

                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function switchTab(index) {
            const tabs = document.querySelectorAll('.tab-content');
            const buttons = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            buttons.forEach(btn => btn.classList.remove('active'));
            
            tabs[index].classList.add('active');
            buttons[index].classList.add('active');
        }
        
        function deleteQuestion(id) {
            if(confirm('Are you sure you want to delete this question?')) {
                window.location.href = 'DeleteQuestion.php?id=' + id;
            }
        }
        
        // Auto-switch to course tab if course_id is in URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('course_id')) {
                switchTab(1);
            }
        });
    </script>
</body>
</html>
<?php $con->close(); ?>
