<?php
require_once '../config.php';
requireAdmin();

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_quiz'])) {
        // Add new quiz
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $subject_id = $_POST['subject_id'];
        $time_limit = $_POST['time_limit'];
        $passing_score = $_POST['passing_score'];
        $status = $_POST['status'];
        
        if (empty($title) || empty($subject_id)) {
            $error = 'Quiz title and subject are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, subject_id, time_limit, passing_score, status) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $subject_id, $time_limit, $passing_score, $status]);
                $quiz_id = $pdo->lastInsertId();
                $success = 'Quiz added successfully. <a href="questions.php?quiz_id=' . $quiz_id . '">Add questions now</a>';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_quiz'])) {
        // Edit quiz
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $subject_id = $_POST['subject_id'];
        $time_limit = $_POST['time_limit'];
        $passing_score = $_POST['passing_score'];
        $status = $_POST['status'];
        
        if (empty($title) || empty($subject_id)) {
            $error = 'Quiz title and subject are required.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, description = ?, subject_id = ?, 
                                      time_limit = ?, passing_score = ?, status = ? WHERE id = ?");
                $stmt->execute([$title, $description, $subject_id, $time_limit, $passing_score, $status, $id]);
                $success = 'Quiz updated successfully.';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_quiz'])) {
        // Delete quiz
        $id = $_POST['id'];
        
        try {
            // Check if quiz has attempts
            $stmt = $pdo->prepare("SELECT COUNT(*) as attempt_count FROM quiz_attempts WHERE quiz_id = ?");
            $stmt->execute([$id]);
            $quiz = $stmt->fetch();
            
            if ($quiz['attempt_count'] > 0) {
                $error = 'Cannot delete quiz that has attempts.';
            } else {
                $pdo->beginTransaction();
                
                // Delete questions
                $stmt = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?");
                $stmt->execute([$id]);
                
                // Delete quiz
                $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
                $stmt->execute([$id]);
                
                $pdo->commit();
                $success = 'Quiz deleted successfully.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all quizzes with subject information
try {
    $stmt = $pdo->query("SELECT q.*, s.name as subject_name, 
                         (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
                         (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count
                         FROM quizzes q 
                         JOIN subjects s ON q.subject_id = s.id 
                         ORDER BY q.created_at DESC");
    $quizzes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Get all subjects for dropdown
try {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .quiz-card {
            transition: transform 0.2s;
        }
        .quiz-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ .  '/../admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../admin_sidebar.php'; ?>
            
            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-tasks me-2"></i>Manage Quizzes</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Quiz Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Add New Quiz</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Quiz Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="subject_id" class="form-label">Subject</label>
                                        <select class="form-select" id="subject_id" name="subject_id" required>
                                            <option value="">Select Subject</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                            <input type="number" class="form-control" id="time_limit" name="time_limit" value="10" min="1" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="passing_score" class="form-label">Passing Score (%)</label>
                                            <input type="number" class="form-control" id="passing_score" name="passing_score" value="60" min="0" max="100" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_quiz" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Quiz
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Quizzes List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">All Quizzes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($quizzes)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No quizzes found.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Subject</th>
                                                    <th>Questions</th>
                                                    <th>Attempts</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quizzes as $quiz): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($quiz['title']); ?></strong>
                                                            <?php if (!empty($quiz['description'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($quiz['description']); ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($quiz['subject_name']); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo $quiz['question_count']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $quiz['attempt_count']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $quiz['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($quiz['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="questions.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                                               class="btn btn-sm btn-outline-info" title="Manage Questions">
                                                                <i class="fas fa-question-circle"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    data-bs-toggle="modal" data-bs-target="#editQuizModal<?php echo $quiz['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    data-bs-toggle="modal" data-bs-target="#deleteQuizModal<?php echo $quiz['id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Quiz Modal -->
                                                    <div class="modal fade" id="editQuizModal<?php echo $quiz['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Quiz</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label for="title<?php echo $quiz['id']; ?>" class="form-label">Quiz Title</label>
                                                                            <input type="text" class="form-control" id="title<?php echo $quiz['id']; ?>" 
                                                                                   name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="description<?php echo $quiz['id']; ?>" class="form-label">Description</label>
                                                                            <textarea class="form-control" id="description<?php echo $quiz['id']; ?>" 
                                                                                      name="description" rows="2"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="subject_id<?php echo $quiz['id']; ?>" class="form-label">Subject</label>
                                                                            <select class="form-select" id="subject_id<?php echo $quiz['id']; ?>" name="subject_id" required>
                                                                                <?php foreach ($subjects as $subject): ?>
                                                                                    <option value="<?php echo $subject['id']; ?>" 
                                                                                        <?php echo $subject['id'] == $quiz['subject_id'] ? 'selected' : ''; ?>>
                                                                                        <?php echo htmlspecialchars($subject['name']); ?>
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-6">
                                                                                <label for="time_limit<?php echo $quiz['id']; ?>" class="form-label">Time Limit (minutes)</label>
                                                                                <input type="number" class="form-control" id="time_limit<?php echo $quiz['id']; ?>" 
                                                                                       name="time_limit" value="<?php echo $quiz['time_limit']; ?>" min="1" required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label for="passing_score<?php echo $quiz['id']; ?>" class="form-label">Passing Score (%)</label>
                                                                                <input type="number" class="form-control" id="passing_score<?php echo $quiz['id']; ?>" 
                                                                                       name="passing_score" value="<?php echo $quiz['passing_score']; ?>" min="0" max="100" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="status<?php echo $quiz['id']; ?>" class="form-label">Status</label>
                                                                            <select class="form-select" id="status<?php echo $quiz['id']; ?>" name="status">
                                                                                <option value="active" <?php echo $quiz['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                                <option value="inactive" <?php echo $quiz['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="edit_quiz" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete Quiz Modal -->
                                                    <div class="modal fade" id="deleteQuizModal<?php echo $quiz['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                                                        <p>Are you sure you want to delete the quiz "<?php echo htmlspecialchars($quiz['title']); ?>"?</p>
                                                                        <?php if ($quiz['attempt_count'] > 0): ?>
                                                                            <div class="alert alert-warning">
                                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                                This quiz has <?php echo $quiz['attempt_count']; ?> attempt(s). 
                                                                                It cannot be deleted until all attempts are removed.
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <?php if ($quiz['attempt_count'] == 0): ?>
                                                                            <button type="submit" name="delete_quiz" class="btn btn-danger">Delete</button>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>