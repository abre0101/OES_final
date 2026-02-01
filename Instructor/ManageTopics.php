<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

// Create topics table if not exists
$con->query("CREATE TABLE IF NOT EXISTS `question_topics` (
    `topic_id` INT AUTO_INCREMENT PRIMARY KEY,
    `topic_name` VARCHAR(100) NOT NULL,
    `topic_description` TEXT,
    `course_name` VARCHAR(100),
    `chapter_number` INT,
    `created_by` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_topic` (`topic_name`, `course_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add columns to question tables safely - only if tables exist
$tableCheck = $con->query("SHOW TABLES LIKE 'question_page'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $checkColumn = $con->query("SHOW COLUMNS FROM question_page LIKE 'topic_id'");
    if($checkColumn && $checkColumn->num_rows == 0) {
        $con->query("ALTER TABLE question_page ADD COLUMN topic_id INT AFTER course_name");
        $con->query("ALTER TABLE question_page ADD COLUMN topic_name VARCHAR(100) AFTER topic_id");
    }
}

$tableCheck = $con->query("SHOW TABLES LIKE 'truefalse_question'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $checkColumn = $con->query("SHOW COLUMNS FROM truefalse_question LIKE 'topic_id'");
    if($checkColumn && $checkColumn->num_rows == 0) {
        $con->query("ALTER TABLE truefalse_question ADD COLUMN topic_id INT AFTER Course_name");
        $con->query("ALTER TABLE truefalse_question ADD COLUMN topic_name VARCHAR(100) AFTER topic_id");
    }
}

$message = '';
$messageType = '';

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_topic'])) {
        $topicName = $_POST['topic_name'];
        $topicDesc = $_POST['topic_description'];
        $courseName = $_POST['course_name'];
        $chapterNum = $_POST['chapter_number'];
        $createdBy = $_SESSION['Name'];
        
        $stmt = $con->prepare("INSERT INTO question_topics (topic_name, topic_description, course_name, chapter_number, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $topicName, $topicDesc, $courseName, $chapterNum, $createdBy);
        
        if($stmt->execute()) {
            $message = 'Topic added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error: ' . $stmt->error;
            $messageType = 'danger';
        }
    }
    
    if(isset($_POST['delete_topic'])) {
        $topicId = $_POST['topic_id'];
        // Check if table exists before deleting
        $tableCheck = $con->query("SHOW TABLES LIKE 'question_topics'");
        if($tableCheck && $tableCheck->num_rows > 0) {
            $con->query("DELETE FROM question_topics WHERE topic_id = $topicId");
            $message = 'Topic deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Topics feature not yet configured';
            $messageType = 'error';
        }
    }
}

// Get all topics - check if table exists first
$topics = null;
$tableCheck = $con->query("SHOW TABLES LIKE 'question_topics'");
if($tableCheck && $tableCheck->num_rows > 0) {
    // Check if question_page table exists for the JOIN
    $qpTableCheck = $con->query("SHOW TABLES LIKE 'question_page'");
    if($qpTableCheck && $qpTableCheck->num_rows > 0) {
        $topics = $con->query("SELECT qt.*, COUNT(qp.question_id) as question_count 
            FROM question_topics qt 
            LEFT JOIN question_page qp ON qt.topic_id = qp.topic_id 
            GROUP BY qt.topic_id 
            ORDER BY qt.topic_name");
    } else {
        // If question_page doesn't exist, just get topics without count
        $topics = $con->query("SELECT *, 0 as question_count 
            FROM question_topics 
            ORDER BY topic_name");
    }
}

// Get only courses assigned to this instructor
$instructor_id = $_SESSION['ID'];
$courses = null;
$tableCheck = $con->query("SHOW TABLES LIKE 'courses'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $icTableCheck = $con->query("SHOW TABLES LIKE 'instructor_courses'");
    if($icTableCheck && $icTableCheck->num_rows > 0) {
        $courses = $con->query("SELECT DISTINCT c.course_name FROM courses c 
            INNER JOIN instructor_courses ic ON c.course_id = ic.course_id 
            WHERE ic.instructor_id = $instructor_id AND ic.is_active = 1
            ORDER BY c.course_name");
    } else {
        $courses = $con->query("SELECT DISTINCT course_name FROM courses ORDER BY course_name");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Topics - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4F46E5;
            --primary-dark: #4338CA;
            --primary-light: #818CF8;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --text-primary: #1F2937;
            --text-secondary: #6B7280;
            --bg-light: #F9FAFB;
            --border-color: #E5E7EB;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .topic-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .topic-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .chapter-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .stat-card {
            padding: 1rem;
            background: var(--bg-light);
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: white;
            box-shadow: var(--shadow-md);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .hero-stat {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .hero-stat-value {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .course-section-header {
            margin: 2rem 0 1rem 0;
            color: var(--primary-color);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .info-box {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(79, 70, 229, 0.05);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary-color);
        }

        .info-box strong {
            color: var(--primary-color);
        }

        .topic-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .btn-delete {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border: none;
            background: var(--danger-color);
            color: white;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-delete:hover {
            background: #DC2626;
            transform: scale(1.05);
        }

        .grid-topics {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .how-to-section {
            padding: 2rem;
        }

        .how-to-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .how-to-item h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .how-to-item ul {
            color: var(--text-secondary);
            line-height: 1.8;
            padding-left: 1.5rem;
        }

        .how-to-item li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Manage Topics';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>📚 Manage Question Topics</h1>
                <p>Organize questions by chapters and topics</p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1.25rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); animation: slideDown 0.3s ease;">
                <strong><?php echo $messageType == 'success' ? '✓' : '✗'; ?></strong> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <style>
                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>

            <div class="grid grid-2">
                <!-- Add New Topic -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">➕ Add New Topic</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <form method="POST">
                            <div class="form-group">
                                <label>Topic Name *</label>
                                <input type="text" name="topic_name" class="form-control" required placeholder="e.g., Data Structures">
                            </div>

                            <div class="form-group">
                                <label>Topic Description</label>
                                <textarea name="topic_description" class="form-control" rows="3" placeholder="Brief description of the topic..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Course *</label>
                                <select name="course_name" class="form-control" required>
                                    <option value="">-- Select Course --</option>
                                    <?php 
                                    if($courses && $courses->num_rows > 0) {
                                        while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_name']; ?>">
                                        <?php echo $course['course_name']; ?>
                                    </option>
                                    <?php 
                                        endwhile;
                                    } else {
                                        echo '<option value="">No courses available</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Chapter Number *</label>
                                <input type="number" name="chapter_number" class="form-control" min="1" max="50" required placeholder="1">
                                <small style="color: var(--text-secondary);">Chapter or unit number (1-50)</small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="add_topic" class="btn btn-primary">
                                    ➕ Add Topic
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Topic Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📊 Topic Statistics</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <?php
                        // Get statistics safely
                        $totalTopics = 0;
                        $topicsWithQuestions = 0;
                        $questionsWithTopics = 0;
                        $totalQuestions = 0;
                        
                        $tableCheck = $con->query("SHOW TABLES LIKE 'question_topics'");
                        if($tableCheck && $tableCheck->num_rows > 0) {
                            $result = $con->query("SELECT COUNT(*) as count FROM question_topics");
                            if($result) $totalTopics = $result->fetch_assoc()['count'];
                        }
                        
                        $tableCheck = $con->query("SHOW TABLES LIKE 'question_page'");
                        if($tableCheck && $tableCheck->num_rows > 0) {
                            $result = $con->query("SELECT COUNT(DISTINCT topic_id) as count FROM question_page WHERE topic_id IS NOT NULL");
                            if($result) $topicsWithQuestions = $result->fetch_assoc()['count'];
                            
                            $result = $con->query("SELECT COUNT(*) as count FROM question_page WHERE topic_id IS NOT NULL");
                            if($result) $questionsWithTopics = $result->fetch_assoc()['count'];
                            
                            $result = $con->query("SELECT COUNT(*) as count FROM question_page");
                            if($result) $totalQuestions = $result->fetch_assoc()['count'];
                        }
                        ?>

                        <div class="hero-stat">
                            <div class="hero-stat-value">
                                <?php echo $totalTopics; ?>
                            </div>
                            <div style="font-size: 1.1rem; opacity: 0.9;">
                                Total Topics Created
                            </div>
                        </div>

                        <div class="stat-card" style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Topics with Questions</span>
                                <strong class="stat-value" style="color: var(--success-color);"><?php echo $topicsWithQuestions; ?></strong>
                            </div>
                        </div>

                        <div class="stat-card" style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Questions Categorized</span>
                                <strong class="stat-value"><?php echo $questionsWithTopics; ?></strong>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Coverage</span>
                                <strong class="stat-value" style="color: var(--warning-color);">
                                    <?php echo $totalQuestions > 0 ? round(($questionsWithTopics / $totalQuestions) * 100, 1) : 0; ?>%
                                </strong>
                            </div>
                        </div>

                        <div class="info-box">
                            <strong>💡 Tip:</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-secondary);">
                                Organize questions by topics to create balanced exams and track student performance by chapter.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Topics List -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📋 All Topics</h3>
                </div>
                <div style="padding: 2rem;">
                    <?php if($topics && $topics->num_rows > 0): ?>
                    <?php 
                    $currentCourse = '';
                    while($topic = $topics->fetch_assoc()): 
                        if($currentCourse != $topic['course_name']):
                            if($currentCourse != '') echo '</div>';
                            $currentCourse = $topic['course_name'];
                    ?>
                    <h3 class="course-section-header">
                        📖 <?php echo $currentCourse; ?>
                    </h3>
                    <div class="grid-topics">
                    <?php endif; ?>

                    <div class="topic-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                            <div>
                                <span class="chapter-badge">Chapter <?php echo $topic['chapter_number']; ?></span>
                            </div>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this topic? Questions will not be deleted.')">
                                <input type="hidden" name="topic_id" value="<?php echo $topic['topic_id']; ?>">
                                <button type="submit" name="delete_topic" class="btn-delete">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>

                        <h4 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">
                            <?php echo $topic['topic_name']; ?>
                        </h4>

                        <?php if($topic['topic_description']): ?>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
                            <?php echo substr($topic['topic_description'], 0, 100); ?><?php echo strlen($topic['topic_description']) > 100 ? '...' : ''; ?>
                        </p>
                        <?php endif; ?>

                        <div class="topic-meta">
                            <div>
                                <strong><?php echo $topic['question_count']; ?></strong> questions
                            </div>
                            <div style="font-size: 0.75rem;">
                                By: <?php echo $topic['created_by']; ?>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <p>No topics created yet. Add your first topic above.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- How to Use Topics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📖 How to Use Topics</h3>
                </div>
                <div class="how-to-section">
                    <div class="how-to-grid">
                        <div class="how-to-item">
                            <h4>1️⃣ Create Topics</h4>
                            <ul>
                                <li>Add topics for each chapter/unit</li>
                                <li>Organize by course</li>
                                <li>Use descriptive names</li>
                                <li>Number chapters sequentially</li>
                            </ul>
                        </div>
                        <div class="how-to-item">
                            <h4>2️⃣ Assign to Questions</h4>
                            <ul>
                                <li>When creating questions, select topic</li>
                                <li>Edit existing questions to add topics</li>
                                <li>One topic per question</li>
                                <li>Helps organize question bank</li>
                            </ul>
                        </div>
                        <div class="how-to-item">
                            <h4>3️⃣ Benefits</h4>
                            <ul>
                                <li>Create balanced exams</li>
                                <li>Track performance by topic</li>
                                <li>Filter questions easily</li>
                                <li>Identify weak areas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
