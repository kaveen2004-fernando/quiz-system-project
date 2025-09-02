<?php
require_once '../config.php';
requireAdmin();

$error = '';
$success = '';

// Check if quiz_id is provided
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id === 0) {
    header('Location: quizzes.php');
    exit();
}

// Get quiz information
try {
    $stmt = $pdo->prepare("SELECT q.*, s.name as subject_name 
                          FROM quizzes q 
                          JOIN subjects s ON q.subject_id = s.id 
                          WHERE q.id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();
    
    if (!$quiz) {
        header('Location: quizzes.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_question'])) {
        // Add new question
        $question_text = trim($_POST['question_text']);
        $question_type = $_POST['question_type'];
        $option_a = trim($_POST['option_a']);
        $option_b = trim($_POST['option_b']);
        $option_c = trim($_POST['option_c']);
        $option_d = trim($_POST['option_d']);
        $correct_answer = $_POST['correct_answer'];
        $explanation = trim($_POST['explanation']);
        $points = $_POST['points'];
        
        if (empty($question_text) || empty($correct_answer)) {
            $error = 'Question text and correct answer are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, question_type, option_a, option_b, option_c, option_d, correct_answer, explanation, points) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$quiz_id, $question_text, $question_type, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $points]);
                $success = 'Question added successfully.';
                
                // Clear form values
                $_POST = [];
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
 elseif (isset($_POST['edit_question'])) {
        // Edit question
        $id = $_POST['id'];
        $question_text = trim($_POST['question_text']);
        $question_type = $_POST['question_type'];
        $option_a = trim($_POST['option_a']);
        $option_b = trim($_POST['option_b']);
        $option_c = trim($_POST['option_c']);
        $option_d = trim($_POST['option_d']);
        $correct_answer = $_POST['correct_answer'];
        $explanation = trim($_POST['explanation']);
        $points = $_POST['points'];
        
        if (empty($question_text) || empty($correct_answer)) {
            $error = 'Question text and correct answer are required.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, question_type = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ?, explanation = ?, points = ? WHERE id = ?");
                $stmt->execute([$question_text, $question_type, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $points, $id]);
                $success = 'Question updated successfully.';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_question'])) {
        // Delete question
        $id = $_POST['id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Question deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }


// Get existing questions
try {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<script>
    // Function to handle question type changes
    function handleQuestionTypeChange() {
        const type = document.getElementById('question_type').value;
        
        // Hide all sections first
        document.getElementById('multipleChoiceOptions').style.display = 'none';
        document.getElementById('trueFalseOptions').style.display = 'none';
        document.getElementById('shortAnswerOptions').style.display = 'none';
        
        // Show the appropriate section
        if (type === 'multiple_choice') {
            document.getElementById('multipleChoiceOptions').style.display = 'block';
            document.getElementById('correct_answer_text').removeAttribute('required');
            document.querySelectorAll('#multipleChoiceOptions input[type="text"]').forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else if (type === 'true_false') {
            document.getElementById('trueFalseOptions').style.display = 'block';
            document.getElementById('correct_answer_text').removeAttribute('required');
            document.querySelectorAll('#multipleChoiceOptions input[type="text"]').forEach(input => {
                input.removeAttribute('required');
            });
        } else if (type === 'short_answer') {
            document.getElementById('shortAnswerOptions').style.display = 'block';
            document.getElementById('correct_answer_text').setAttribute('required', 'required');
            document.querySelectorAll('#multipleChoiceOptions input[type="text"]').forEach(input => {
                input.removeAttribute('required');
            });
        }
    }

    // Set initial display when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Remove all inline styles first
        document.getElementById('multipleChoiceOptions').removeAttribute('style');
        document.getElementById('trueFalseOptions').removeAttribute('style');
        document.getElementById('shortAnswerOptions').removeAttribute('style');
        
        // Now call the handler
        handleQuestionTypeChange();
        
        // Add event listener for future changes
        document.getElementById('question_type').addEventListener('change', handleQuestionTypeChange);
    });

    // Handle edit modal question type changes
    function initEditModals() {
        document.querySelectorAll('[id^="question_type_edit"]').forEach(select => {
            select.addEventListener('change', function() {
                const id = this.id.replace('question_type_edit', '');
                const type = this.value;
                
                document.getElementById('multipleChoiceOptionsEdit' + id).style.display = type === 'multiple_choice' ? 'block' : 'none';
                document.getElementById('trueFalseOptionsEdit' + id).style.display = type === 'true_false' ? 'block' : 'none';
                document.getElementById('shortAnswerOptionsEdit' + id).style.display = type === 'short_answer' ? 'block' : 'none';
            });
        });
    }

    // Initialize edit modals when DOM is loaded
    document.addEventListener('DOMContentLoaded', initEditModals);
</script>
</body>
</html>
<script>
    console.log('DOM loaded');
    console.log('Question type:', document.getElementById('question_type').value);
    console.log('MC style:', document.getElementById('multipleChoiceOptions').style.display);
    console.log('TF style:', document.getElementById('trueFalseOptions').style.display);
    console.log('SA style:', document.getElementById('shortAnswerOptions').style.display);
</script>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Questions - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        textarea, input, select {
            border-radius: .5rem !important;
        }
    </style>
</head>
<body class="container py-4">

    <h1 class="mb-4">
        <i class="fa-solid fa-pen-to-square text-primary"></i>
        Manage Questions for <span class="text-success"><?php echo htmlspecialchars($quiz['title']); ?></span>
        <small class="text-muted">(<?php echo htmlspecialchars($quiz['subject_name']); ?>)</small>
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa fa-triangle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa fa-circle-check"></i> <?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Add Question Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-plus-circle"></i> Add New Question
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="question_text" required><?php echo isset($_POST['question_text']) ? htmlspecialchars($_POST['question_text']) : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Question Type</label>
                    <select class="form-select" name="question_type" id="question_type">
                        <option value="multiple_choice" <?php if(isset($_POST['question_type']) && $_POST['question_type']=="multiple_choice") echo "selected"; ?>>Multiple Choice</option>
                        <option value="true_false" <?php if(isset($_POST['question_type']) && $_POST['question_type']=="true_false") echo "selected"; ?>>True/False</option>
                    </select>
                </div>

                <!-- Multiple Choice Options -->
                <div id="multipleChoiceOptions" class="mb-3">
                    <label class="form-label">Options</label>
                    <input type="text" class="form-control mb-2" name="option_a" placeholder="Option A" value="<?php echo isset($_POST['option_a']) ? htmlspecialchars($_POST['option_a']) : ''; ?>">
                    <input type="text" class="form-control mb-2" name="option_b" placeholder="Option B" value="<?php echo isset($_POST['option_b']) ? htmlspecialchars($_POST['option_b']) : ''; ?>">
                    <input type="text" class="form-control mb-2" name="option_c" placeholder="Option C" value="<?php echo isset($_POST['option_c']) ? htmlspecialchars($_POST['option_c']) : ''; ?>">
                    <input type="text" class="form-control mb-2" name="option_d" placeholder="Option D" value="<?php echo isset($_POST['option_d']) ? htmlspecialchars($_POST['option_d']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Correct Answer</label>
                    <select class="form-select" name="correct_answer" required>
                        <option value="">--Select--</option>
                        <option value="A" <?php if(isset($_POST['correct_answer']) && $_POST['correct_answer']=="A") echo "selected"; ?>>Option A</option>
                        <option value="B" <?php if(isset($_POST['correct_answer']) && $_POST['correct_answer']=="B") echo "selected"; ?>>Option B</option>
                        <option value="C" <?php if(isset($_POST['correct_answer']) && $_POST['correct_answer']=="C") echo "selected"; ?>>Option C</option>
                        <option value="D" <?php if(isset($_POST['correct_answer']) && $_POST['correct_answer']=="D") echo "selected"; ?>>Option D</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Explanation (optional)</label>
                    <textarea class="form-control" name="explanation"><?php echo isset($_POST['explanation']) ? htmlspecialchars($_POST['explanation']) : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Points</label>
                    <input type="number" class="form-control" name="points" value="<?php echo isset($_POST['points']) ? htmlspecialchars($_POST['points']) : '1'; ?>" min="1">
                </div>

                <button type="submit" name="add_question" class="btn btn-success">
                    <i class="fa fa-plus"></i> Add Question
                </button>
            </form>
        </div>
    </div>

    <!-- Existing Questions -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <i class="fa fa-list"></i> Existing Questions
        </div>
        <div class="card-body">
            <?php if ($questions): ?>
                <ol class="list-group list-group-numbered">
                    <?php foreach ($questions as $q): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo htmlspecialchars($q['question_text']); ?> 
                                <span class="badge bg-info">Correct: <?php echo htmlspecialchars($q['correct_answer']); ?></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="text-muted">No questions added yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <p class="mt-3">
        <a href="quizzes.php" class="btn btn-outline-dark">
            <i class="fa fa-arrow-left"></i> Back to Quizzes
        </a>
    </p>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>


