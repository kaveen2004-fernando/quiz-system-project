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
        WHERE q.id = ?
    ");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        redirect('dashboard.php');
    }

    // Get attempt details
    $stmt = $pdo->prepare("
        SELECT * FROM quiz_attempts 
        WHERE quiz_id = ? AND user_id = ? 
        ORDER BY attempt_date DESC LIMIT 1
    ");
    $stmt->execute([$quiz_id, $_SESSION['user_id']]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        redirect('take_quiz.php?id=' . $quiz_id);
    }

    // Get questions and user answers
    $stmt = $pdo->prepare("
        SELECT qq.*, qa.answer as user_answer
        FROM quiz_questions qq
        LEFT JOIN quiz_answers qa ON qq.id = qa.question_id AND qa.attempt_id = ?
        WHERE qq.quiz_id = ?
        ORDER BY qq.id
    ");
    $stmt->execute([$attempt['id'], $quiz_id]);
    $questions = $stmt->fetchAll();

    // Calculate detailed statistics
    $total_questions = count($questions);
    $correct_answers = 0;
    $incorrect_answers = 0;
    $unanswered = 0;

    foreach ($questions as &$question) {
        $user_answer = trim($question['user_answer'] ?? '');
        $correct_answer = trim($question['correct_answer']);

        $is_correct = false;

        if ($user_answer !== '') {
            if ($question['question_type'] == 'multiple_choice') {
                $is_correct = (strtoupper($user_answer) === strtoupper($correct_answer));
            } elseif ($question['question_type'] == 'true_false') {
                $is_correct = (strtolower($user_answer) === strtolower($correct_answer));
            } else {
                $is_correct = (strcasecmp($user_answer, $correct_answer) === 0);
            }
        }

        $question['is_correct'] = $is_correct;

        if ($user_answer === '') {
            $unanswered++;
        } elseif ($is_correct) {
            $correct_answers++;
        } else {
            $incorrect_answers++;
        }
    }

    // ✅ Calculate percentage
    $percentage = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100, 2) : 0;

} catch (Exception $e) {
    $error = "Error loading quiz attempt: " . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p class="text-muted mb-0">
                    Subject: <?php echo htmlspecialchars($quiz['subject_name']); ?><br>
                    Attempted on: <?php echo date('M d, Y H:i', strtotime($attempt['attempt_date'])); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <div class="score-circle mx-auto mb-3">
                            <?php echo $percentage; ?>%
                        </div>
                        <h5 class="mb-1">Your Score</h5>
                        <p class="mb-0"><?php echo $correct_answers; ?> / <?php echo $total_questions; ?> correct</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Performance Statistics</h5>
                        <div class="d-flex justify-content-center mt-3">
                            <div class="mx-3">
                                <div class="text-success fw-bold"><?php echo $correct_answers; ?></div>
                                <small>Correct</small>
                            </div>
                            <div class="mx-3">
                                <div class="text-danger fw-bold"><?php echo $incorrect_answers; ?></div>
                                <small>Incorrect</small>
                            </div>
                            <div class="mx-3">
                                <div class="text-secondary fw-bold"><?php echo $unanswered; ?></div>
                                <small>Unanswered</small>
                            </div>
                            <div class="mx-3">
                                <div class="text-primary fw-bold"><?php echo $total_questions; ?></div>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Review -->
        <div class="row">
            <div class="col-md-12">
                <h4>Question Review</h4>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-3 <?php echo $question['is_correct'] ? 'border-success' : 'border-danger'; ?>">
                        <div class="card-body">
                            <h5 class="card-title">
                                Q<?php echo $index + 1; ?>. <?php echo htmlspecialchars($question['question_text']); ?>
                            </h5>
                            <p><strong>Your Answer:</strong>
                                <?php echo $question['user_answer'] !== '' 
                                    ? htmlspecialchars($question['user_answer']) 
                                    : '<span class="text-muted">Unanswered</span>'; ?>
                            </p>
                            <p><strong>Correct Answer:</strong> <?php echo htmlspecialchars($question['correct_answer']); ?></p>
                            <?php if (!$question['is_correct']): ?>
                                <div class="alert alert-warning mb-0">
                                    <?php echo $question['user_answer'] === '' 
                                        ? 'You did not answer this question.' 
                                        : 'Your answer was incorrect.'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<style>
.score-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 5px solid #007bff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}
</style>

<?php include 'includes/footer.php'; ?>
