<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$pageTitle = "Help & Support";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .faq-item {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .faq-answer {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border-color);
            color: var(--text-primary);
            line-height: 1.8;
        }
        
        .faq-item.active .faq-answer {
            display: block;
        }
        
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }
        
        .faq-icon {
            transition: transform 0.3s ease;
        }
        
        .contact-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
        }
        
        .shortcut-key {
            background: var(--bg-light);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-family: monospace;
            font-weight: 700;
            border: 2px solid var(--border-color);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>❓ Help & Support</h1>
                <p>Get assistance and learn how to use the system</p>
            </div>

            <!-- Quick Contact -->
            <div class="contact-card">
                <h2 style="margin: 0 0 1rem 0;">📞 Need Immediate Help?</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <strong style="display: block; margin-bottom: 0.5rem;">📧 Email Support</strong>
                        <a href="mailto:support@dmu.edu" style="color: var(--secondary-color); font-weight: 600;">support@dmu.edu</a>
                    </div>
                    <div>
                        <strong style="display: block; margin-bottom: 0.5rem;">📱 IT Support Phone</strong>
                        <a href="tel:+251911234567" style="color: var(--secondary-color); font-weight: 600;">+251 911 234 567</a>
                    </div>
                    <div>
                        <strong style="display: block; margin-bottom: 0.5rem;">👤 Admin Contact</strong>
                        <a href="mailto:admin@dmu.edu" style="color: var(--secondary-color); font-weight: 600;">admin@dmu.edu</a>
                    </div>
                </div>
                <button class="btn btn-light" style="margin-top: 1.5rem; background: white; color: var(--primary-color);" onclick="openContactForm()">
                    📝 Submit Support Ticket
                </button>
            </div>

            <!-- FAQ Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">❓ Frequently Asked Questions</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>How do I approve an exam?</span>
                            <span class="faq-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <ol>
                                <li>Navigate to "Check Questions" or "Pending Approvals"</li>
                                <li>Click "View Details" to review the full exam</li>
                                <li>Verify content accuracy, clarity, and alignment with course objectives</li>
                                <li>Click the "✓ Approve" button</li>
                                <li>Confirm your approval in the dialog box</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>What criteria should I check when reviewing questions?</span>
                            <span class="faq-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <ul>
                                <li><strong>Content Accuracy:</strong> Ensure questions and answers are factually correct</li>
                                <li><strong>Clarity:</strong> Questions should be clear and unambiguous</li>
                                <li><strong>Relevance:</strong> Questions must align with course objectives and syllabus</li>
                                <li><strong>Difficulty Level:</strong> Appropriate for the course level</li>
                                <li><strong>Grammar & Formatting:</strong> Check for spelling and formatting errors</li>
                                <li><strong>Answer Correctness:</strong> Verify that correct answers are properly marked</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>How do I request revisions on a question?</span>
                            <span class="faq-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <ol>
                                <li>Open the question you want to revise</li>
                                <li>Click "✏️ Request Revision" button</li>
                                <li>Enter detailed comments explaining what needs to be changed</li>
                                <li>Submit the revision request</li>
                                <li>The instructor will be notified and can make corrections</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>What are deadline policies?</span>
                            <span class="faq-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p><strong>Review Timeline:</strong></p>
                            <ul>
                                <li>Questions should be reviewed within 3-5 business days</li>
                                <li>Urgent requests (marked as high priority) should be reviewed within 24 hours</li>
                                <li>Questions pending for more than 7 days are automatically flagged</li>
                            </ul>
                            <p><strong>Exam Scheduling:</strong></p>
                            <ul>
                                <li>Exams must be approved at least 48 hours before the scheduled date</li>
                                <li>Last-minute changes require special approval from the department head</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Can I edit questions directly?</span>
                            <span class="faq-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p><strong>No.</strong> As a Department Head, you can only review and approve questions. You cannot edit them directly.</p>
                            <p>If changes are needed, you must request revisions from the instructor who created the questions. This maintains accountability and ensures instructors are aware of all changes to their exams.</p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>How do I change my password?</span>
                            <span class="faq-icon">▼</span>
                        </div>
                        <div class="faq-answer">
                            <ol>
                                <li>Click on "Change Password" in the sidebar</li>
                                <li>Enter your current password</li>
                                <li>Enter your new password (must meet security requirements)</li>
                                <li>Confirm your new password</li>
                                <li>Click "Update Password"</li>
                            </ol>
                            <p><strong>Security Tip:</strong> Change your password every 3-6 months for better security.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Guides -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📚 Quick Guides & Resources</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <a href="#" class="action-card" style="text-decoration: none;">
                            <div class="action-icon">📄</div>
                            <div class="action-title">User Manual</div>
                            <div class="action-desc">Download PDF guide</div>
                        </a>
                        <a href="#" class="action-card" style="text-decoration: none;">
                            <div class="action-icon">🎥</div>
                            <div class="action-title">Video Tutorials</div>
                            <div class="action-desc">Watch how-to videos</div>
                        </a>
                        <a href="#" class="action-card" style="text-decoration: none;">
                            <div class="action-icon">📖</div>
                            <div class="action-title">Documentation</div>
                            <div class="action-desc">System documentation</div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Keyboard Shortcuts -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">⌨️ Keyboard Shortcuts</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="overview-list">
                        <div class="overview-item">
                            <span>Search</span>
                            <span><span class="shortcut-key">Ctrl</span> + <span class="shortcut-key">K</span></span>
                        </div>
                        <div class="overview-item">
                            <span>Go to Dashboard</span>
                            <span><span class="shortcut-key">Ctrl</span> + <span class="shortcut-key">H</span></span>
                        </div>
                        <div class="overview-item">
                            <span>Check Questions</span>
                            <span><span class="shortcut-key">Ctrl</span> + <span class="shortcut-key">Q</span></span>
                        </div>
                        <div class="overview-item">
                            <span>Pending Approvals</span>
                            <span><span class="shortcut-key">Ctrl</span> + <span class="shortcut-key">P</span></span>
                        </div>
                        <div class="overview-item">
                            <span>Help</span>
                            <span><span class="shortcut-key">F1</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Issue -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">🐛 Report an Issue</h3>
                </div>
                <div style="padding: 2rem;">
                    <p style="margin-bottom: 1.5rem;">Found a bug or have a suggestion? Let us know!</p>
                    <form id="issueForm">
                        <div class="form-group">
                            <label>Issue Type</label>
                            <select class="form-control" required>
                                <option value="">Select type...</option>
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Request</option>
                                <option value="question">Question</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" rows="5" placeholder="Describe the issue..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Issue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function toggleFAQ(element) {
            element.classList.toggle('active');
        }
        
        function openContactForm() {
            alert('Contact form will open here');
        }
        
        document.getElementById('issueForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you! Your issue has been submitted.');
            this.reset();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if(e.ctrlKey) {
                switch(e.key) {
                    case 'h':
                        e.preventDefault();
                        window.location.href = 'index.php';
                        break;
                    case 'q':
                        e.preventDefault();
                        window.location.href = 'CheckQuestions.php';
                        break;
                    case 'p':
                        e.preventDefault();
                        window.location.href = 'PendingApprovals.php';
                        break;
                }
            } else if(e.key === 'F1') {
                e.preventDefault();
                window.location.href = 'Help.php';
            }
        });
    </script>
</body>
</html>
