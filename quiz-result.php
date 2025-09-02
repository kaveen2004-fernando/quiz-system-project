<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['attempt_id'])) {
    header('Location: results.php');
    exit();
}

$attempt_id = $_GET['attempt_id'];

// Get quiz attempt details
try {
    $stmt = $pdo->prepare("
        SELECT qa.*, q.title as quiz_title, q.description as quiz_description, 
               s.name as subject_name, u.username, u.email
        FROM quiz_attempts qa 
        JOIN quizzes q ON qa.quiz_id = q.id 
        JOIN subjects s ON q.subject_id = s.id 
        JOIN users u ON qa.user_id = u.id
        WHERE qa.id = ? AND qa.user_id = ?
    ");
    $stmt->execute([$attempt_id, $_SESSION['user_id']]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        header('Location: results.php');
        exit();
    }

    // Get quiz questions with user answers
    $stmt = $pdo->prepare("
        SELECT q.*, ua.answer as user_answer
        FROM questions q 
        LEFT JOIN user_answers ua ON q.id = ua.question_id AND ua.attempt_id = ?
        WHERE q.quiz_id = ?
        ORDER BY q.id
    ");
    $stmt->execute([$attempt_id, $attempt['quiz_id']]);
    $questions = $stmt->fetchAll();

    // Calculate detailed statistics
    $total_questions = count($questions);
    $correct_answers = 0;
    
    foreach ($questions as &$question) {
        $user_answer = trim($question['user_answer'] ?? '');
        $correct_answer = trim($question['correct_answer']);
        
        // Determine if answer is correct
        $is_correct = false;
        if (!empty($user_answer)) {
            if ($question['question_type'] == 'multiple_choice') {
                $is_correct = (strtoupper($user_answer) === strtoupper($correct_answer));
            } else if ($question['question_type'] == 'true_false') {
                $is_correct = (strtolower($user_answer) === strtolower($correct_answer));
            } else {
                $is_correct = (strcasecmp($user_answer, $correct_answer) === 0);
            }
        }
        
        $question['is_correct'] = $is_correct;
        
        if ($is_correct) {
            $correct_answers++;
        }
    }

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .stats-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .question-card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .score-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .correct-radio {
            border-color: #28a745 !important;
            background-color: #28a745 !important;
        }
        .correct-option {
            background-color: #d4edda !important;
            border-color: #28a745 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="card header-card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">Quiz Results</h2>
                                <p class="mb-0">Review your quiz performance</p>
                            </div>
                            <a href="results.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Results
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quiz Information -->
                <div class="card stats-card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Quiz Information</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Quiz Title:</th>
                                        <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Subject:</th>
                                        <td><?php echo htmlspecialchars($attempt['subject_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date Taken:</th>
                                        <td><?php echo date('M j, Y g:i A', strtotime($attempt['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="score-circle mx-auto mb-3">
                                    <?php echo $attempt['score']; ?>%
                                </div>
                                <h5>Your Score</h5>
                                <p class="text-muted"><?php echo $total_questions; ?> questions total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Questions Review -->
                <div class="card stats-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list-check me-2"></i>
                            Question Review
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($questions)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No questions found for this quiz attempt.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="question-card card mb-4">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <span class="badge bg-primary me-2">
                                                <?php echo $index + 1; ?>
                                            </span>
                                            <h6 class="mb-0 flex-grow-1">
                                                <?php echo htmlspecialchars($question['question_text']); ?>
                                            </h6>
                                        </div>

                                        <?php if ($question['question_type'] == 'multiple_choice'): ?>
                                            <div class="options mt-3">
                                                <?php 
                                                $options = [
                                                    'A' => $question['option_a'] ?? '',
                                                    'B' => $question['option_b'] ?? '',
                                                    'C' => $question['option_c'] ?? '',
                                                    'D' => $question['option_d'] ?? ''
                                                ];
                                                $correct_option = strtoupper(trim($question['correct_answer']));
                                                ?>
                                                
                                                <?php foreach ($options as $letter => $option_text): ?>
                                                    <?php if (!empty(trim($option_text))): ?>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="radio" disabled>
                                                            <label class="form-check-label <?php echo ($correct_option == $letter) ? 'correct-option p-2 rounded' : ''; ?>">
                                                                <strong><?php echo $letter; ?>.</strong> 
                                                                <?php echo htmlspecialchars($option_text); ?>
                                                                
                                                                <?php if ($correct_option == $letter): ?>
                                                                    <span class="badge bg-success ms-2">Correct Answer</span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>

                                        <?php elseif ($question['question_type'] == 'true_false'): ?>
                                            <div class="options mt-3">
                                                <?php 
                                                $correct_answer = strtoupper(trim($question['correct_answer']));
                                                ?>
                                                
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" disabled>
                                                    <label class="form-check-label <?php echo ($correct_answer == 'TRUE') ? 'correct-option p-2 rounded' : ''; ?>">
                                                        True
                                                        <?php if ($correct_answer == 'TRUE'): ?>
                                                            <span class="badge bg-success ms-2">Correct Answer</span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                                
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" disabled>
                                                    <label class="form-check-label <?php echo ($correct_answer == 'FALSE') ? 'correct-option p-2 rounded' : ''; ?>">
                                                        False
                                                        <?php if ($correct_answer == 'FALSE'): ?>
                                                            <span class="badge bg-success ms-2">Correct Answer</span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            </div>

                                        <?php else: ?>
                                            <div class="mt-3 p-3 bg-light rounded">
                                                <div>
                                                    <strong>Correct answer:</strong> 
                                                    <span class="text-success"><?php echo htmlspecialchars($question['correct_answer']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($question['explanation'])): ?>
                                            <div class="explanation mt-3 p-3 bg-light rounded">
                                                <strong>Explanation:</strong> <?php echo htmlspecialchars($question['explanation']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>