<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

// Get course from URL parameter
$selectedCourse = isset($_GET['course']) ? $_GET['course'] : '';

if (empty($selectedCourse)) {
    header("Location: practice-selection.php");
    exit();
}

// Get practice questions from database for selected course
require_once(__DIR__ . "/../Connections/OES.php");
$stmt = $con->prepare("SELECT pq.*, c.course_name 
    FROM practice_questions pq
    INNER JOIN courses c ON pq.course_id = c.course_id 
    WHERE c.course_name = ? AND pq.is_active = 1
    ORDER BY RAND() LIMIT 10");
$stmt->bind_param("s", $selectedCourse);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];
while($row = mysqli_fetch_array($result)) {
    $questions[] = $row;
}
$totalQuestions = count($questions);
$stmt->close();
mysqli_close($con);

if ($totalQuestions == 0) {
    header("Location: practice-selection.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice Mode - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/exam-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .practice-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }
        
        .feedback-correct {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid var(--success-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-top: 1rem;
            color: var(--success-color);
            font-weight: 600;
        }
        
        .feedback-wrong {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid var(--danger-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-top: 1rem;
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .option-label.correct-answer {
            border-color: var(--success-color);
            background: rgba(40, 167, 69, 0.1);
        }
        
        .option-label.wrong-answer {
            border-color: var(--danger-color);
            background: rgba(220, 53, 69, 0.1);
        }
        
        .practice-badge {
            background: #6366f1;
            color: white;
        }
        
        .score-display {
            background: white;
            padding: 1rem 2rem;
            border-radius: var(--radius-lg);
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .score-item {
            text-align: center;
        }
        
        .score-item .label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .score-item .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>
<body class="exam-body">
    <!-- Practice Header -->
    <div class="exam-header practice-header">
        <div class="exam-header-left">
            <img src="../images/logo1.png" alt="Logo" class="exam-logo">
            <div>
                <h2>Practice Mode</h2>
                <p><?php echo $_SESSION['Name']; ?> (<?php echo $_SESSION['ID']; ?>)</p>
            </div>
        </div>
        <div class="exam-header-center">
            <div class="practice-badge course-badge"><?php echo htmlspecialchars($selectedCourse); ?></div>
            <div class="score-display">
                <div class="score-item">
                    <div class="label">Correct</div>
                    <div class="value" id="correctCount">0</div>
                </div>
                <div class="score-item">
                    <div class="label">Wrong</div>
                    <div class="value" id="wrongCount">0</div>
                </div>
                <div class="score-item">
                    <div class="label">Score</div>
                    <div class="value" id="scoreDisplay">0%</div>
                </div>
            </div>
        </div>
        <div class="exam-header-right">
            <a href="practice-selection.php" class="btn btn-warning">
                🔄 Change Course
            </a>
        </div>
    </div>

    <!-- Main Practice Container -->
    <div class="exam-container">
        <!-- Question Area -->
        <div class="question-area">
            <div class="question-card" id="questionCard">
                <!-- Questions will be loaded here by JavaScript -->
            </div>

            <div id="feedbackArea"></div>

            <div class="question-navigation">
                <button class="btn btn-primary" id="checkBtn" onclick="checkAnswer()">
                    Check Answer
                </button>
                <button class="btn btn-secondary" id="nextBtn" onclick="nextQuestion()" style="display:none;">
                    Next Question →
                </button>
                <button class="btn btn-success" id="finishBtn" onclick="finishPractice()" style="display:none;">
                    Finish Practice
                </button>
            </div>
        </div>

        <!-- Question Panel Sidebar -->
        <div class="question-panel">
            <div class="panel-header">
                <h3>📊 Question Panel</h3>
                <p>Total: <?php echo $totalQuestions; ?> Questions</p>
            </div>

            <div class="panel-legend">
                <div class="legend-item">
                    <span class="legend-box answered"></span>
                    <span>Correct</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box skipped"></span>
                    <span>Wrong</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box" style="background: #e5e7eb;"></span>
                    <span>Not Attempted</span>
                </div>
            </div>

            <div class="panel-grid" id="questionPanel">
                <!-- Question numbers will be loaded here -->
            </div>

            <div class="panel-summary">
                <div class="summary-item">
                    <span>Attempted:</span>
                    <strong id="attemptedCount">0</strong>
                </div>
                <div class="summary-item">
                    <span>Remaining:</span>
                    <strong id="remainingCount"><?php echo $totalQuestions; ?></strong>
                </div>
            </div>

            <div class="panel-submit">
                <button class="btn btn-success btn-block" onclick="finishPractice()">
                    Finish Practice
                </button>
            </div>
        </div>
    </div>

    <script>
        // Questions data from PHP
        const questions = <?php echo json_encode($questions); ?>;
        const totalQuestions = questions.length;
        let currentQuestion = 0;
        let answers = {};
        let questionStatus = {}; // 'correct', 'wrong', 'unattempted'
        let correctCount = 0;
        let wrongCount = 0;
        let hasChecked = false;

        // Initialize practice
        function initPractice() {
            createQuestionPanel();
            loadQuestion(0);
        }

        // Load question
        function loadQuestion(index) {
            currentQuestion = index;
            hasChecked = false;
            const q = questions[index];
            
            const questionHTML = `
                <div class="question-number">Question No. ${index + 1}</div>
                <div class="question-text">${q.question || 'Question text here'}</div>
                <div class="options-container">
                    <label class="option-label">
                        <input type="radio" name="answer" value="A">
                        <span class="option-text">(A) ${q.Option1 || 'Option A'}</span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="answer" value="B">
                        <span class="option-text">(B) ${q.Option2 || 'Option B'}</span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="answer" value="C">
                        <span class="option-text">(C) ${q.Option3 || 'Option C'}</span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="answer" value="D">
                        <span class="option-text">(D) ${q.Option4 || 'Option D'}</span>
                    </label>
                </div>
            `;
            
            document.getElementById('questionCard').innerHTML = questionHTML;
            document.getElementById('feedbackArea').innerHTML = '';
            
            // Add event listeners to options
            document.querySelectorAll('input[name="answer"]').forEach(input => {
                input.addEventListener('change', function() {
                    document.querySelectorAll('.option-label').forEach(label => {
                        label.classList.remove('selected');
                    });
                    this.parentElement.classList.add('selected');
                });
            });
            
            updateNavigationButtons();
            updateQuestionPanel();
        }

        // Check answer
        function checkAnswer() {
            const selectedAnswer = document.querySelector('input[name="answer"]:checked');
            if (!selectedAnswer) {
                alert('Please select an answer first!');
                return;
            }

            hasChecked = true;
            const userAnswer = selectedAnswer.value;
            const correctAnswer = questions[currentQuestion].Answer;
            const isCorrect = userAnswer === correctAnswer;

            // Save answer
            answers[currentQuestion] = userAnswer;
            
            // Update status
            if (questionStatus[currentQuestion] === undefined) {
                if (isCorrect) {
                    correctCount++;
                    questionStatus[currentQuestion] = 'correct';
                } else {
                    wrongCount++;
                    questionStatus[currentQuestion] = 'wrong';
                }
                updateScore();
            }

            // Disable all options
            document.querySelectorAll('input[name="answer"]').forEach(input => {
                input.disabled = true;
            });

            // Highlight correct and wrong answers
            document.querySelectorAll('.option-label').forEach(label => {
                const input = label.querySelector('input');
                if (input.value === correctAnswer) {
                    label.classList.add('correct-answer');
                }
                if (input.value === userAnswer && !isCorrect) {
                    label.classList.add('wrong-answer');
                }
            });

            // Show feedback
            const feedbackHTML = isCorrect 
                ? `<div class="feedback-correct">✓ Correct! Well done!</div>`
                : `<div class="feedback-wrong">✗ Wrong! The correct answer is (${correctAnswer})</div>`;
            
            document.getElementById('feedbackArea').innerHTML = feedbackHTML;

            // Update buttons
            document.getElementById('checkBtn').style.display = 'none';
            if (currentQuestion < totalQuestions - 1) {
                document.getElementById('nextBtn').style.display = 'inline-block';
            } else {
                document.getElementById('finishBtn').style.display = 'inline-block';
            }

            updateQuestionPanel();
        }

        // Next question
        function nextQuestion() {
            if (currentQuestion < totalQuestions - 1) {
                loadQuestion(currentQuestion + 1);
            }
        }

        // Jump to question
        function jumpToQuestion(index) {
            loadQuestion(index);
        }

        // Update navigation buttons
        function updateNavigationButtons() {
            document.getElementById('checkBtn').style.display = hasChecked ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('finishBtn').style.display = 'none';
        }

        // Question Panel
        function createQuestionPanel() {
            const panel = document.getElementById('questionPanel');
            let html = '';
            for (let i = 0; i < totalQuestions; i++) {
                html += `<button class="panel-question" onclick="jumpToQuestion(${i})" id="panelQ${i}">${i + 1}</button>`;
            }
            panel.innerHTML = html;
        }

        function updateQuestionPanel() {
            for (let i = 0; i < totalQuestions; i++) {
                const btn = document.getElementById(`panelQ${i}`);
                btn.classList.remove('answered', 'skipped', 'current');
                
                if (questionStatus[i] === 'correct') {
                    btn.classList.add('answered');
                } else if (questionStatus[i] === 'wrong') {
                    btn.classList.add('skipped');
                }
                
                if (i === currentQuestion) {
                    btn.classList.add('current');
                }
            }
            
            updateSummary();
        }

        function updateSummary() {
            const attemptedCount = Object.keys(questionStatus).length;
            document.getElementById('attemptedCount').textContent = attemptedCount;
            document.getElementById('remainingCount').textContent = totalQuestions - attemptedCount;
        }

        function updateScore() {
            document.getElementById('correctCount').textContent = correctCount;
            document.getElementById('wrongCount').textContent = wrongCount;
            
            const attempted = correctCount + wrongCount;
            const percentage = attempted > 0 ? Math.round((correctCount / attempted) * 100) : 0;
            document.getElementById('scoreDisplay').textContent = percentage + '%';
        }

        // Finish practice
        function finishPractice() {
            const attempted = Object.keys(questionStatus).length;
            const percentage = attempted > 0 ? Math.round((correctCount / attempted) * 100) : 0;
            
            let message = 'Practice Session Summary\n\n';
            message += `Total Questions: ${totalQuestions}\n`;
            message += `Attempted: ${attempted}\n`;
            message += `Correct: ${correctCount}\n`;
            message += `Wrong: ${wrongCount}\n`;
            message += `Score: ${percentage}%\n\n`;
            message += 'Would you like to start a new practice session?';
            
            if (confirm(message)) {
                window.location.reload();
            } else {
                window.location.href = 'index.php';
            }
        }

        // Initialize on load
        window.onload = initPractice;
    </script>
</body>
</html>
