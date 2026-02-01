<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    echo "<script>window.close();</script>";
    exit();
}

// Get questions and course info from database
$con = require_once(__DIR__ . "/../Connections/OES.php"); $con;
$sql = "SELECT * FROM question_page ORDER BY question_id LIMIT 20";
$result = mysqli_query($con,$sql);
$questions = [];
$courseName = "General Exam"; // Default
while($row = mysqli_fetch_array($result)) {
    $questions[] = $row;
    if(!empty($row['course_name'])) {
        $courseName = $row['course_name'];
    }
}
$totalQuestions = count($questions);
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Examination - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/exam-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Full-screen exam mode */
        body.exam-body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        /* Prevent text selection during exam */
        .exam-container * {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Fullscreen request modal */
        .fullscreen-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(26, 43, 74, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10001;
            color: white;
        }

        .fullscreen-modal.hidden {
            display: none;
        }

        .fullscreen-modal-content {
            text-align: center;
            padding: 3rem;
            max-width: 600px;
        }

        .modal-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .modal-message {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            line-height: 1.6;
            opacity: 0.95;
        }

        .btn-large {
            font-size: 1.25rem;
            padding: 1.25rem 2.5rem;
        }

        /* Warning overlay for tab switching */
        .warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(220, 53, 69, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            flex-direction: column;
            color: white;
        }

        .warning-overlay.show {
            display: flex;
        }

        .warning-content {
            text-align: center;
            padding: 3rem;
            max-width: 600px;
        }

        .warning-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .warning-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .warning-message {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .warning-count {
            font-size: 3rem;
            font-weight: 800;
            margin: 1rem 0;
        }

        /* Fullscreen button */
        .fullscreen-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-lg);
        }

        .fullscreen-btn:hover {
            background: var(--primary-dark);
        }

        /* Exam locked message */
        .exam-locked {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-color);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
        }

        .exam-locked.show {
            display: flex;
        }

        .locked-content {
            text-align: center;
            padding: 3rem;
        }

        .locked-icon {
            font-size: 6rem;
            margin-bottom: 1.5rem;
        }

        .locked-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .locked-message {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="exam-body">
    <!-- Fullscreen Request Modal -->
    <div class="fullscreen-modal" id="fullscreenModal">
        <div class="fullscreen-modal-content">
            <div class="modal-icon">🖥️</div>
            <div class="modal-title">Fullscreen Mode Required</div>
            <div class="modal-message">
                For the best exam experience and security,<br>
                this exam must be taken in fullscreen mode.
            </div>
            <button class="btn btn-success btn-lg" onclick="startExamFullscreen()">
                🚀 Enter Fullscreen & Start Exam
            </button>
        </div>
    </div>

    <!-- Warning Overlay for Tab Switching -->
    <div class="warning-overlay" id="warningOverlay">
        <div class="warning-content">
            <div class="warning-icon">⚠️</div>
            <div class="warning-title">WARNING!</div>
            <div class="warning-message">
                You switched tabs or minimized the exam window!<br>
                This action has been recorded.
            </div>
            <div class="warning-count" id="warningCount">Warnings: 0/2</div>
            <div style="font-size: 1rem; opacity: 0.9;">
                After 2 warnings, your exam will be automatically submitted.
            </div>
            <button class="btn btn-light" onclick="returnToExam()" style="margin-top: 2rem; font-size: 1.25rem; padding: 1rem 2rem;">
                Return to Exam
            </button>
        </div>
    </div>

    <!-- Exam Locked Overlay -->
    <div class="exam-locked" id="examLocked">
        <div class="locked-content">
            <div class="locked-icon">🔒</div>
            <div class="locked-title">Exam Locked</div>
            <div class="locked-message">
                You have exceeded the maximum number of warnings.<br>
                Your exam has been automatically submitted.
            </div>
            <div style="font-size: 1rem; opacity: 0.9; margin-top: 1rem;">
                Redirecting to results page...
            </div>
        </div>
    </div>

    <!-- Fullscreen Button -->
    <button class="fullscreen-btn" id="fullscreenBtn" onclick="enterFullscreen()">
        <span>🖥️</span>
        <span>Enter Fullscreen</span>
    </button>

    <!-- Exam Header -->
    <div class="exam-header">
        <div class="exam-header-left">
            <img src="../images/logo1.png" alt="Logo" class="exam-logo">
            <div>
                <h2>Online Examination</h2>
                <p><?php echo $_SESSION['Name']; ?> (<?php echo $_SESSION['ID']; ?>)</p>
            </div>
        </div>
        <div class="exam-header-center">
            <div class="course-badge"><?php echo $courseName; ?></div>
            <div class="timer-container">
                <div class="timer-icon">⏱️</div>
                <div>
                    <div class="timer-label">Time Left:</div>
                    <div class="timer-display" id="timer">20:00</div>
                </div>
            </div>
            <div class="auto-submit-notice">
                ⚠️ Exam will auto-submit when timer ends
            </div>
        </div>
        <div class="exam-header-right">
            <div class="marking-info">
                <div class="marking-badge positive">Right: +2</div>
                <div class="marking-badge negative">Wrong: -1</div>
            </div>
        </div>
    </div>

    <!-- Main Exam Container -->
    <div class="exam-container">
        <!-- Question Area -->
        <div class="question-area">
            <div class="question-card" id="questionCard">
                <!-- Questions will be loaded here by JavaScript -->
            </div>

            <div class="question-navigation">
                <button class="btn btn-success" id="confirmBtn" onclick="confirmAndNext()">
                    Confirm Answer & Next Question
                </button>
                <button class="btn btn-warning" id="skipBtn" onclick="skipAndNext()">
                    Skip
                </button>
                <button class="btn btn-secondary" id="flagBtn" onclick="toggleFlag()">
                    🚩 Flag for Review
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
                    <span>Answered</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box skipped"></span>
                    <span>Skipped</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box flagged"></span>
                    <span>Flagged</span>
                </div>
            </div>

            <div class="panel-grid" id="questionPanel">
                <!-- Question numbers will be loaded here -->
            </div>

            <div class="panel-summary">
                <div class="summary-item">
                    <span>Answered:</span>
                    <strong id="answeredCount">0</strong>
                </div>
                <div class="summary-item">
                    <span>Remaining:</span>
                    <strong id="remainingCount"><?php echo $totalQuestions; ?></strong>
                </div>
            </div>

            <div class="panel-submit">
                <button class="btn btn-success btn-block" id="panelSubmitBtn" onclick="submitExam()">
                    Submit Exam
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
        let questionStatus = {}; // 'answered', 'skipped', 'flagged'
        let flaggedQuestions = new Set(); // Track flagged questions
        let timeLeft = 1200; // 20 minutes in seconds
        let tabSwitchCount = 0;
        const maxTabSwitches = 2;
        let examLocked = false;
        let monitoringStarted = false; // Only start monitoring after initial alert

        // Initialize exam
        function initExam() {
            createQuestionPanel();
            loadQuestion(0);
            preventNavigation();
            setupAntiCheat();
            // Show fullscreen modal instead of starting immediately
            showFullscreenModal();
        }

        // Show fullscreen modal
        function showFullscreenModal() {
            document.getElementById('fullscreenModal').classList.remove('hidden');
        }

        // Start exam in fullscreen (triggered by button click)
        function startExamFullscreen() {
            enterFullscreen();
            document.getElementById('fullscreenModal').classList.add('hidden');
            // Start timer and monitoring after entering fullscreen
            startTimer();
            setTimeout(() => {
                monitoringStarted = true;
            }, 2000);
        }

        // Anti-cheat measures
        function setupAntiCheat() {
            // Detect tab switching / window blur
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && !examLocked && monitoringStarted) {
                    handleTabSwitch();
                }
            });

            window.addEventListener('blur', function() {
                if (!examLocked && monitoringStarted) {
                    handleTabSwitch();
                }
            });

            // Prevent right-click
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });

            // Prevent common keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Prevent F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
                if (e.keyCode === 123 || 
                    (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) ||
                    (e.ctrlKey && e.keyCode === 85)) {
                    e.preventDefault();
                    return false;
                }

                // Prevent Ctrl+C, Ctrl+V, Ctrl+X
                if (e.ctrlKey && (e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88)) {
                    e.preventDefault();
                    return false;
                }

                // Prevent Alt+Tab (can't fully prevent but can detect)
                if (e.altKey && e.keyCode === 9) {
                    e.preventDefault();
                    return false;
                }
            });

            // Detect fullscreen exit
            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement && !examLocked && monitoringStarted) {
                    setTimeout(() => {
                        if (!document.fullscreenElement) {
                            handleTabSwitch();
                        }
                    }, 1000);
                }
            });
        }

        // Handle tab switching
        function handleTabSwitch() {
            tabSwitchCount++;
            document.getElementById('warningCount').textContent = `Warnings: ${tabSwitchCount}/${maxTabSwitches}`;
            document.getElementById('warningOverlay').classList.add('show');

            if (tabSwitchCount >= maxTabSwitches) {
                lockExam();
            }
        }

        // Return to exam
        function returnToExam() {
            document.getElementById('warningOverlay').classList.remove('show');
            enterFullscreen();
        }

        // Lock exam after too many warnings
        function lockExam() {
            examLocked = true;
            document.getElementById('warningOverlay').classList.remove('show');
            document.getElementById('examLocked').classList.add('show');
            
            // Auto-submit after 3 seconds
            setTimeout(() => {
                submitExam(true);
            }, 3000);
        }

        // Fullscreen functions
        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
            document.getElementById('fullscreenBtn').style.display = 'none';
        }

        // Load question
        function loadQuestion(index) {
            currentQuestion = index;
            const q = questions[index];
            
            const questionHTML = `
                <div class="question-number">Question No. ${index + 1}</div>
                <div class="question-text">${q.question || 'Question text here'}</div>
                <div class="options-container">
                    <label class="option-label ${answers[index] === 'A' ? 'selected' : ''}">
                        <input type="radio" name="answer" value="A" ${answers[index] === 'A' ? 'checked' : ''}>
                        <span class="option-text">(A) ${q.Option1 || 'Option A'}</span>
                    </label>
                    <label class="option-label ${answers[index] === 'B' ? 'selected' : ''}">
                        <input type="radio" name="answer" value="B" ${answers[index] === 'B' ? 'checked' : ''}>
                        <span class="option-text">(B) ${q.Option2 || 'Option B'}</span>
                    </label>
                    <label class="option-label ${answers[index] === 'C' ? 'selected' : ''}">
                        <input type="radio" name="answer" value="C" ${answers[index] === 'C' ? 'checked' : ''}>
                        <span class="option-text">(C) ${q.Option3 || 'Option C'}</span>
                    </label>
                    <label class="option-label ${answers[index] === 'D' ? 'selected' : ''}">
                        <input type="radio" name="answer" value="D" ${answers[index] === 'D' ? 'checked' : ''}>
                        <span class="option-text">(D) ${q.Option4 || 'Option D'}</span>
                    </label>
                </div>
            `;
            
            document.getElementById('questionCard').innerHTML = questionHTML;
            
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
            
            // Update flag button state
            const flagBtn = document.getElementById('flagBtn');
            if (flaggedQuestions.has(index)) {
                flagBtn.innerHTML = '🏴 Unflag';
                flagBtn.classList.remove('btn-secondary');
                flagBtn.classList.add('btn-danger');
            } else {
                flagBtn.innerHTML = '🚩 Flag for Review';
                flagBtn.classList.remove('btn-danger');
                flagBtn.classList.add('btn-secondary');
            }
        }

        // Confirm and next
        function confirmAndNext() {
            if (examLocked) return;
            
            const selectedAnswer = document.querySelector('input[name="answer"]:checked');
            if (selectedAnswer) {
                answers[currentQuestion] = selectedAnswer.value;
                questionStatus[currentQuestion] = 'answered';
                updateSummary();
                updateQuestionPanel();
            } else {
                alert('Please select an answer before confirming.');
                return;
            }
            
            if (currentQuestion < totalQuestions - 1) {
                loadQuestion(currentQuestion + 1);
            } else {
                showNoMoreQuestions();
            }
        }

        // Skip and next
        function skipAndNext() {
            if (examLocked) return;
            
            questionStatus[currentQuestion] = 'skipped';
            updateQuestionPanel();
            
            if (currentQuestion < totalQuestions - 1) {
                loadQuestion(currentQuestion + 1);
            } else {
                showNoMoreQuestions();
            }
        }

        // Show "No More Question" message
        function showNoMoreQuestions() {
            const questionHTML = `
                <div style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                    <h2 style="color: var(--primary-color); margin-bottom: 1rem;">No More Questions</h2>
                    <p style="font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 2rem;">
                        You have reached the end of the exam.
                    </p>
                    <p style="font-size: 1.1rem; color: var(--text-primary);">
                        Please review your answers using the Question Panel on the right,<br>
                        then click the <strong>"Submit Exam"</strong> button when you're ready.
                    </p>
                </div>
            `;
            document.getElementById('questionCard').innerHTML = questionHTML;
        }

        // Navigation
        function jumpToQuestion(index) {
            if (examLocked) return;
            loadQuestion(index);
        }

        function updateNavigationButtons() {
            // Buttons stay the same
        }

        // Question Panel
        function createQuestionPanel() {
            const panel = document.getElementById('questionPanel');
            if (!panel) return;
            
            let html = '';
            for (let i = 0; i < totalQuestions; i++) {
                html += `<button class="panel-question" onclick="jumpToQuestion(${i})" id="panelQ${i}">${i + 1}</button>`;
            }
            panel.innerHTML = html;
        }

        function updateQuestionPanel() {
            for (let i = 0; i < totalQuestions; i++) {
                const btn = document.getElementById(`panelQ${i}`);
                if (!btn) continue;
                
                btn.classList.remove('answered', 'skipped', 'current');
                
                if (questionStatus[i] === 'answered') {
                    btn.classList.add('answered');
                } else if (questionStatus[i] === 'skipped') {
                    btn.classList.add('skipped');
                }
                
                // Add flagged class if question is flagged
                if (flaggedQuestions.has(i)) {
                    btn.classList.add('flagged');
                }
                
                if (i === currentQuestion) {
                    btn.classList.add('current');
                }
            }
        }

        function updateSummary() {
            const answeredCount = Object.keys(answers).length;
            document.getElementById('answeredCount').textContent = answeredCount;
            document.getElementById('remainingCount').textContent = totalQuestions - answeredCount;
        }

        // Timer
        function startTimer() {
            const timerDisplay = document.getElementById('timer');
            
            const timerInterval = setInterval(() => {
                if (examLocked) {
                    clearInterval(timerInterval);
                    return;
                }
                
                timeLeft--;
                
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 60) {
                    timerDisplay.style.color = '#ef4444';
                }
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    submitExam(true);
                }
            }, 1000);
        }

        // Submit exam
        function submitExam(autoSubmit = false) {
            if (examLocked && !autoSubmit) return;
            
            const answeredCount = Object.keys(answers).length;
            const unansweredCount = totalQuestions - answeredCount;
            
            if (!autoSubmit) {
                let message = 'Final Submission of Examination\n\n';
                message += `Total Questions: ${totalQuestions}\n`;
                message += `Answered: ${answeredCount}\n`;
                message += `Unanswered/Skipped: ${unansweredCount}\n`;
                message += '\nNote: Unanswered questions will be marked as wrong.\n\n';
                message += 'Are you sure you want to submit your exam?';
                
                if (!confirm(message)) {
                    return;
                }
            }
            
            // Calculate results
            let correct = 0;
            let wrong = 0;
            
            questions.forEach((q, index) => {
                if (answers[index]) {
                    if (answers[index] === q.Answer) {
                        correct++;
                    } else {
                        wrong++;
                    }
                } else {
                    wrong++;
                }
            });
            
            const score = correct * 2;
            
            // Create form to submit results
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'save-exam-result.php';
            
            const fields = {
                'correct': correct,
                'wrong': wrong,
                'total': totalQuestions,
                'score': score,
                'answers': JSON.stringify(answers),
                'tab_switches': tabSwitchCount
            };
            
            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        // Prevent navigation
        function preventNavigation() {
            window.addEventListener('beforeunload', function (e) {
                if (!examLocked) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        }

        // Initialize on load
        window.onload = initExam;
        
        // Toggle flag for review
        function toggleFlag() {
            if (examLocked) return;
            
            const flagBtn = document.getElementById('flagBtn');
            
            if (flaggedQuestions.has(currentQuestion)) {
                flaggedQuestions.delete(currentQuestion);
                flagBtn.innerHTML = '🚩 Flag for Review';
                flagBtn.classList.remove('btn-danger');
                flagBtn.classList.add('btn-secondary');
            } else {
                flaggedQuestions.add(currentQuestion);
                flagBtn.innerHTML = '🏴 Unflag';
                flagBtn.classList.remove('btn-secondary');
                flagBtn.classList.add('btn-danger');
            }
            
            updateQuestionPanel();
        }
    </script>
</body>
</html>
