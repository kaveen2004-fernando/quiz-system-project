<?php
require_once __DIR__ . '/../config.php';

requireAdmin();

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        // Add new subject
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $error = 'Subject name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO subjects (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                $success = 'Subject added successfully.';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_subject'])) {
        // Edit subject
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $error = 'Subject name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE subjects SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $description, $id]);
                $success = 'Subject updated successfully.';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_subject'])) {
        // Delete subject
        $id = $_POST['id'];
        
        try {
            // Check if subject has quizzes
            $stmt = $pdo->prepare("SELECT COUNT(*) as quiz_count FROM quizzes WHERE subject_id = ?");
            $stmt->execute([$id]);
            $subject = $stmt->fetch();
            
            if ($subject['quiz_count'] > 0) {
                $error = 'Cannot delete subject that has quizzes. Please delete the quizzes first.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Subject deleted successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all subjects
try {
    $stmt = $pdo->query("SELECT s.*, 
                         (SELECT COUNT(*) FROM quizzes WHERE subject_id = s.id) as quiz_count 
                         FROM subjects s ORDER BY s.name");
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
    <title>Manage Subjects - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .subject-card {
            transition: transform 0.2s;
        }
        .subject-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../admin_sidebar.php'; ?>
            
            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-book me-2"></i>Manage Subjects</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Subject Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Add New Subject</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Subject Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    <button type="submit" name="add_subject" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Subject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">All Subjects</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($subjects)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No subjects found.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th>Quizzes</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($subject['description']); ?></td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $subject['quiz_count']; ?></span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    data-bs-toggle="modal" data-bs-target="#editSubjectModal<?php echo $subject['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    data-bs-toggle="modal" data-bs-target="#deleteSubjectModal<?php echo $subject['id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Subject Modal -->
                                                    <div class="modal fade" id="editSubjectModal<?php echo $subject['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Subject</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label for="name<?php echo $subject['id']; ?>" class="form-label">Subject Name</label>
                                                                            <input type="text" class="form-control" id="name<?php echo $subject['id']; ?>" 
                                                                                   name="name" value="<?php echo htmlspecialchars($subject['name']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="description<?php echo $subject['id']; ?>" class="form-label">Description</label>
                                                                            <textarea class="form-control" id="description<?php echo $subject['id']; ?>" 
                                                                                      name="description" rows="3"><?php echo htmlspecialchars($subject['description']); ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="edit_subject" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete Subject Modal -->
                                                    <div class="modal fade" id="deleteSubjectModal<?php echo $subject['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                                                        <p>Are you sure you want to delete the subject "<?php echo htmlspecialchars($subject['name']); ?>"?</p>
                                                                        <?php if ($subject['quiz_count'] > 0): ?>
                                                                            <div class="alert alert-warning">
                                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                                This subject has <?php echo $subject['quiz_count']; ?> quiz(es). 
                                                                                Deleting it will also delete all associated quizzes and questions.
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_subject" class="btn btn-danger">Delete</button>
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