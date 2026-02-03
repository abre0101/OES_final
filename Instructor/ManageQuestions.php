<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Manage Questions";
$instructor_id = $_SESSION['ID'];

// Get filter parameters
$course_filter = $_GET['course'] ?? '';
$topic_filter = $_GET['topic'] ?? '';
$search = $_GET['search'] ?? '';

// Get instructor's courses
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ?
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get topics
$topicsQuery = $con->query("SELECT * FROM question_topics ORDER BY topic_name");

// Build questions query
$query = "SELECT q.*, qt.topic_name, c.course_name, c.course_code
    FROM questions q
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    LEFT JOIN courses c ON q.course_id = c.course_id
    WHERE q.created_by = ?";

$params = [$instructor_id];
$types = "i";

if($course_filter) {
    $query .= " AND q.course_id = ?";
    $params[] = $course_filter;
    $types .= "i";
}

if($topic_filter) {
    $query .= " AND q.topic_id = ?";
    $params[] = $topic_filter;
    $types .= "i";
}

if($search) {
    $query .= " AND q.question_text LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

$query .= " ORDER BY q.created_at DESC";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$questions = $stmt->get_result();

// Get statistics
$statsQuery = $con->prepare("SELECT 
    COUNT(*) as total_questions,
    COUNT(DISTINCT course_id) as total_courses,
    COUNT(DISTINCT topic_id) as total_topics
    FROM questions
    WHERE created_by = ?");
$statsQuery->bind_param("i", $instructor_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();
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
        
        .page-header-modern {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); }
        .stat-card.primary { border-top-color: #003366; }
        .stat-card.success { border-top-color: #28a745; }
        .stat-card.info { border-top-color: #17a2b8; }
        
        .stat-icon { font-size: 3rem; margin-bottom: 1rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .filter-card h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #003366;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #003366;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
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
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-sm { padding: 0.6rem 1.2rem; font-size: 0.875rem; }
        
        .question-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #003366;
            transition: all 0.3s ease;
        }
        
        .question-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        
        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #003366;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .question-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .meta-badge {
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .meta-badge.course { background: #e7f3ff; color: #004085; }
        .meta-badge.topic { background: #d4edda; color: #155724; }
        .meta-badge.points { background: #fff3cd; color: #856404; }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .option-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            font-size: 0.9rem;
        }
        
        .option-item.correct {
            background: #d4edda;
            border-color: #28a745;
            font-weight: 600;
        }
        
        .question-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            color: #495057;
            margin-bottom: 0.75rem;
        }
        
        .empty-state p {
            margin-bottom: 1.5rem;
        }
        
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        @media (max-width: 768px) {
            .page-header-modern { flex-direction: column; align-items: flex-start; }
            .stats-grid { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr; }
            .options-grid { grid-template-columns: 1fr; }
            .question-header { flex-direction: column; }
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
                <div>
                    <h1>📝 Question Bank</h1>
                    <p>Create and manage questions for your courses</p>
                </div>
                <a href="AddQuestion.php" class="btn btn-primary" style="background: white; color: #003366;">
                    <span>➕</span> Create New Question
                </a>
            </div>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <span style="font-size: 1.5rem;">✅</span>
                <span>
                    <?php 
                    if($_GET['success'] == 'created') echo 'Question created successfully!';
                    elseif($_GET['success'] == 'updated') echo 'Question updated successfully!';
                    elseif($_GET['success'] == 'deleted') echo 'Question deleted successfully!';
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <span style="font-size: 1.5rem;">❌</span>
                <span>An error occurred. Please try again.</span>
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_questions']); ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo number_format($stats['total_courses']); ?></div>
                    <div class="stat-label">Courses</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">🗂️</div>
                    <div class="stat-value"><?php echo number_format($stats['total_topics']); ?></div>
                    <div class="stat-label">Topics</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <h3>🔍 Filter Questions</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course">
                                <option value="">All Courses</option>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Topic</label>
                            <select name="topic">
                                <option value="">All Topics</option>
                                <?php while($topic = $topicsQuery->fetch_assoc()): ?>
                                <option value="<?php echo $topic['topic_id']; ?>" <?php echo $topic_filter == $topic['topic_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($topic['topic_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" name="search" placeholder="Search question text..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <span>🔍</span> Apply Filters
                        </button>
                        <?php if($course_filter || $topic_filter || $search): ?>
                        <a href="ManageQuestions.php" class="btn btn-secondary">
                            <span>🔄</span> Clear Filters
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Questions List -->
            <div style="margin-bottom: 1rem;">
                <h3 style="color: #003366; font-size: 1.3rem; font-weight: 700;">
                    📋 Questions (<?php echo $questions->num_rows; ?>)
                </h3>
            </div>

            <?php if($questions->num_rows > 0): ?>
                <?php while($q = $questions->fetch_assoc()): ?>
                <div class="question-card">
                    <div class="question-header">
                        <div style="flex: 1;">
                            <div class="question-text">
                                <?php echo htmlspecialchars($q['question_text']); ?>
                            </div>
                            <div class="question-meta">
                                <?php if($q['course_name']): ?>
                                <span class="meta-badge course">
                                    <strong>📚</strong> <?php echo htmlspecialchars($q['course_code']); ?>
                                </span>
                                <?php endif; ?>
                                <?php if($q['topic_name']): ?>
                                <span class="meta-badge topic">
                                    <strong>🗂️</strong> <?php echo htmlspecialchars($q['topic_name']); ?>
                                </span>
                                <?php endif; ?>
                                <span class="meta-badge points">
                                    <strong>⭐</strong> <?php echo $q['point_value']; ?> point<?php echo $q['point_value'] != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="options-grid">
                        <div class="option-item <?php echo $q['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                            <strong>A)</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                            <?php if($q['correct_answer'] == 'A'): ?>
                            <span style="float: right; color: #28a745;">✓</span>
                            <?php endif; ?>
                        </div>
                        <div class="option-item <?php echo $q['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                            <strong>B)</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                            <?php if($q['correct_answer'] == 'B'): ?>
                            <span style="float: right; color: #28a745;">✓</span>
                            <?php endif; ?>
                        </div>
                        <?php if($q['option_c']): ?>
                        <div class="option-item <?php echo $q['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                            <strong>C)</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                            <?php if($q['correct_answer'] == 'C'): ?>
                            <span style="float: right; color: #28a745;">✓</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if($q['option_d']): ?>
                        <div class="option-item <?php echo $q['correct_answer'] == 'D' ? 'correct' : ''; ?>">
                            <strong>D)</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                            <?php if($q['correct_answer'] == 'D'): ?>
                            <span style="float: right; color: #28a745;">✓</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if($q['explanation']): ?>
                    <div style="padding: 1rem; background: #e7f3ff; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        <strong style="color: #004085;">💡 Explanation:</strong>
                        <div style="margin-top: 0.5rem; color: #004085;">
                            <?php echo nl2br(htmlspecialchars($q['explanation'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="question-actions">
                        <a href="EditQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn btn-warning btn-sm">
                            <span>✏️</span> Edit
                        </a>
                        <a href="DeleteQuestion.php?id=<?php echo $q['question_id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this question?')">
                            <span>🗑️</span> Delete
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h3>No Questions Found</h3>
                    <p>
                        <?php if($course_filter || $topic_filter || $search): ?>
                            No questions match your filter criteria. Try adjusting the filters.
                        <?php else: ?>
                            You haven't created any questions yet. Start building your question bank!
                        <?php endif; ?>
                    </p>
                    <a href="AddQuestion.php" class="btn btn-primary">
                        <span>➕</span> Create Your First Question
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
