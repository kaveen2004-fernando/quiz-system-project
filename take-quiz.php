<?php
require_once 'config.php';
requireLogin();

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$quiz = null;
$questions = [];

if ($quiz_id <= 0) {
    redirect('dashboard.php');
}

try {
    // Get quiz details
    $stmt = $pdo->prepare("
        SELECT q.*, s.name as subject_name 
        FROM quizzes q 
        JOIN subjects s ON q.subject_id = s.id 
        WHERE q.id = ? AND q.status = 'active'
    ");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        redirect('dashboard.php');
    }

    // Get quiz questions
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $answers = $_POST['answers'] ?? [];
        $score = 0;
        $total_questions = count($questions);
        
        // Calculate score
        foreach ($questions as $question) {
            $user_answer = $answers[$question['id']] ?? '';
            if (strtolower(trim($user_answer)) === strtolower(trim($question['correct_answer']))) {
                $score++;
            }
        }
        
        $percentage = $total_questions > 0 ? round(($score / $total_questions) * 100, 2) : 0;
        
        // Save quiz attempt
        $stmt = $pdo->prepare("
            INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, answers, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_SESSION['user_id'], 
            $quiz_id, 
            $percentage, 
            $total_questions, 
            json_encode($answers)
        ]);
        
        // Redirect to results
        redirect('quiz-result.php?attempt_id=' . $pdo->lastInsertId());
    }

} catch (PDOException $e) {
    $error = 'Database error occurred: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .question-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .question-card.active {
            border-left: 5px solid #667eea;
        }
        .timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            font-weight: bold;
        }
        .timer.warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .timer.danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .progress-container {
            position: sticky;
            top: 0;
            background: white;
            z-index: 999;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .btn-submit {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-submit:hover {
            opacity: 0.9;
            color: white;
            transform: translateY(-2px);
        }
        .question-number {
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        .navigation-buttons {
            position: sticky;
            bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .option-label {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #dee2e6;
        }
        .option-label:hover {
            background-color: #f8f9fa;
            border-color: #667eea;
        }
        input[type="radio"]:checked + .option-label {
            background-color: #e8f0fe;
            border-color: #667eea;
            color: #667eea;
            font-weight: bold;
        }
        .error-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Quiz Header -->
    <div class="quiz-header text-center">
        <div class="container">
            <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
            <p class="mb-2"><?php echo htmlspecialchars($quiz['subject_name']); ?></p>
            <p class="mb-0">
                <i class="fas fa-clock me-2"></i>Time Limit: <?php echo $quiz['time_limit']; ?> minutes
                <span class="mx-3">|</span>
                <i class="fas fa-question-circle me-2"></i>Questions: <?php echo count($questions); ?>
            </p>
        </div>
    </div>

    <!-- Timer -->
    <div class="timer" id="timer">
        <i class="fas fa-stopwatch me-2"></i>
        <span id="time-display"><?php echo $quiz['time_limit']; ?>:00</span>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Progress</h6>
                <span id="progress-text">0 of <?php echo count($questions); ?> answered</span>
            </div>
            <div class="progress mt-2" style="height: 8px;">
                <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Quiz Content -->
    <div class="container py-4">
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="quizForm">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card card" id="question-<?php echo $index + 1; ?>">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="question-number me-3">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-3"><?php echo htmlspecialchars($question['question_text']); ?></h5>
                                
                                <?php if ($question['question_type'] === 'multiple_choice'): ?>
                                    <?php 
                                    $options = [];
                                    if (!empty($question['option_a'])) $options['A'] = $question['option_a'];
                                    if (!empty($question['option_b'])) $options['B'] = $question['option_b'];
                                    if (!empty($question['option_c'])) $options['C'] = $question['option_c'];
                                    if (!empty($question['option_d'])) $options['D'] = $question['option_d'];
                                    ?>
                                    
                                    <div class="options-container">
                                        <?php foreach ($options as $key => $option): ?>
                                            <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                                   value="<?php echo $key; ?>" 
                                                   id="q<?php echo $question['id']; ?>_<?php echo $key; ?>" 
                                                   class="d-none" onchange="updateProgress()">
                                            <label for="q<?php echo $question['id']; ?>_<?php echo $key; ?>" class="option-label d-block">
                                                <?php echo htmlspecialchars($option); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                <?php elseif ($question['question_type'] === 'true_false'): ?>
                                    <div class="options-container">
                                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                               value="True" id="q<?php echo $question['id']; ?>_true" 
                                               class="d-none" onchange="updateProgress()">
                                        <label for="q<?php echo $question['id']; ?>_true" class="option-label d-block">
                                            True
                                        </label>
                                        
                                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                               value="False" id="q<?php echo $question['id']; ?>_false" 
                                               class="d-none" onchange="updateProgress()">
                                        <label for="q<?php echo $question['id']; ?>_false" class="option-label d-block">
                                            False
                                        </label>
                                    </div>
                                    
                                <?php else: // text input ?>
                                    <input type="text" class="form-control" 
                                           name="answers[<?php echo $question['id']; ?>]" 
                                           placeholder="Enter your answer" onchange="updateProgress()">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="navigation-buttons text-center">
                <button type="button" class="btn btn-outline-secondary me-3" onclick="window.history.back()">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </button>
                <button type="button" class="btn btn-submit" onclick="confirmSubmit()">
                    <i class="fas fa-check me-2"></i>Submit Quiz
                </button>
            </div>
        </form>
    </div>

    <!-- Submit Confirmation Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to submit your quiz?</p>
                    <p class="text-muted small">You have answered <span id="answered-count">0</span> out of <?php echo count($questions); ?> questions.</p>
                    <p class="text-warning small"><i class="fas fa-exclamation-triangle me-2"></i>Once submitted, you cannot change your answers.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Quiz</button>
                    <button type="button" class="btn btn-success" onclick="document.getElementById('quizForm').submit()">Submit Quiz</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script>
    // Timer functionality
    let timeLimit = <?php echo $quiz['time_limit'] * 60; ?>; // Convert to seconds
    let timeLeft = timeLimit;
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        document.getElementById('time-display').textContent = display;
        
        const timer = document.getElementById('timer');
        if (timeLeft <= 60) { // Last minute
            timer.classList.add('danger');
            timer.classList.remove('warning');
        } else if (timeLeft <= 300) { // Last 5 minutes
            timer.classList.add('warning');
            timer.classList.remove('danger');
        } else {
            timer.classList.remove('warning', 'danger');
        }
        
        if (timeLeft <= 0) {
            alert('Time is up! Your quiz will be submitted automatically.');
            document.getElementById('quizForm').submit();
            return;
        }
        
        timeLeft--;
        setTimeout(updateTimer, 1000);
    }
    
    // Start timer
    updateTimer();
    
    // Progress tracking
    function updateProgress() {
        const totalQuestions = <?php echo count($questions); ?>;
        let answeredQuestions = 0;
        
        // Count answered questions
        for (let i = 0; i < totalQuestions; i++) {
            const questionId = <?php echo isset($questions[0]['id']) ? $questions[0]['id'] : 0; ?> + i;
            const selectedOption = document.querySelector(`input[name="answers[${questionId}]"]:checked`);
            const textAnswer = document.querySelector(`input[name="answers[${questionId}]"][type="text"]`);
            
            if (selectedOption || (textAnswer && textAnswer.value.trim() !== '')) {
                answeredQuestions++;
            }
        }
        
        const percentage = (answeredQuestions / totalQuestions) * 100;
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-text').textContent = `${answeredQuestions} of ${totalQuestions} answered`;
        
        // Update modal
        document.getElementById('answered-count').textContent = answeredQuestions;
    }
    
    // Confirm submission
    function confirmSubmit() {
        updateProgress();
        const modal = new bootstrap.Modal(document.getElementById('submitModal'));
        modal.show();
    }
    
    // Prevent accidental page reload
    window.addEventListener('beforeunload', function(e) {
        // Only show warning if there are answered questions
        let hasAnswers = false;
        const totalQuestions = <?php echo count($questions); ?>;
        
        for (let i = 0; i < totalQuestions; i++) {
            const questionId = <?php echo isset($questions[0]['id']) ? $questions[0]['id'] : 0; ?> + i;
            const selectedOption = document.querySelector(`input[name="answers[${questionId}]"]:checked`);
            const textAnswer = document.querySelector(`input[name="answers[${questionId}]"][type="text"]`);
            
            if (selectedOption || (textAnswer && textAnswer.value.trim() !== '')) {
                hasAnswers = true;
                break;
            }
        }
        
        if (hasAnswers) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
        }
    });
    
    // Add event listeners to all input elements to update progress
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[type="radio"], input[type="text"]');
        inputs.forEach(input => {
            input.addEventListener('change', updateProgress);
            input.addEventListener('input', updateProgress);
        });
        
        // Initialize progress
        updateProgress();
    });
</script>
</body>
</html>