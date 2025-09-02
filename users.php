<?php
require_once '../config.php';
requireAdmin();

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $error = 'Username or email already exists.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password, $role]);
                    $success = 'User added successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_user'])) {
        // Edit user
        $id = $_POST['id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($username) || empty($email)) {
            $error = 'Username and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                // Check if username or email already exists (excluding current user)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $id]);
                if ($stmt->fetch()) {
                    $error = 'Username or email already exists.';
                } else {
                    if (!empty($password)) {
                        if ($password !== $confirm_password) {
                            $error = 'Passwords do not match.';
                        } elseif (strlen($password) < 6) {
                            $error = 'Password must be at least 6 characters long.';
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                            $stmt->execute([$username, $email, $hashed_password, $role, $id]);
                            $success = 'User updated successfully.';
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $role, $id]);
                        $success = 'User updated successfully.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        // Delete user (cannot delete self)
        $id = $_POST['id'];
        
        if ($id == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            try {
                // Check if user has quiz attempts
                $stmt = $pdo->prepare("SELECT COUNT(*) as attempt_count FROM quiz_attempts WHERE user_id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                
                if ($user['attempt_count'] > 0) {
                    $error = 'Cannot delete user that has quiz attempts.';
                } else {
                    $pdo->beginTransaction();
                    
                    // Delete user answers
                    $stmt = $pdo->prepare("DELETE FROM user_answers WHERE user_id = ?");
                    $stmt->execute([$id]);
                    
                    // Delete user
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $pdo->commit();
                    $success = 'User deleted successfully.';
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get all users with their stats
try {
    $stmt = $pdo->query("SELECT u.*, 
                         (SELECT COUNT(*) FROM quiz_attempts WHERE user_id = u.id) as attempt_count,
                         (SELECT AVG(score) FROM quiz_attempts WHERE user_id = u.id) as avg_score
                         FROM users u ORDER BY u.created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-card {
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ .'/../admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ .'/../admin_sidebar.php'; ?>
            
            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-users me-2"></i>Manage Users</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add User Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Add New User</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_user" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add User
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Users List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">All Users (<?php echo count($users); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($users)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No users found.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Quizzes</th>
                                                    <th>Avg Score</th>
                                                    <th>Joined</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($users as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                                <span class="badge bg-info ms-1">You</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'secondary'; ?>">
                                                                <?php echo ucfirst($user['role']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $user['attempt_count']; ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if ($user['attempt_count'] > 0): ?>
                                                                <span class="badge bg-<?php 
                                                                    echo $user['avg_score'] >= 80 ? 'success' : 
                                                                        ($user['avg_score'] >= 60 ? 'warning' : 'danger'); 
                                                                ?>">
                                                                    <?php echo round($user['avg_score']); ?>%
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-dark">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                        data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $user['id']; ?>">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit User Modal -->
                                                    <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit User</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label for="username<?php echo $user['id']; ?>" class="form-label">Username</label>
                                                                            <input type="text" class="form-control" id="username<?php echo $user['id']; ?>" 
                                                                                   name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="email<?php echo $user['id']; ?>" class="form-label">Email</label>
                                                                            <input type="email" class="form-control" id="email<?php echo $user['id']; ?>" 
                                                                                   name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="role<?php echo $user['id']; ?>" class="form-label">Role</label>
                                                                            <select class="form-select" id="role<?php echo $user['id']; ?>" name="role" required>
                                                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="password<?php echo $user['id']; ?>" class="form-label">New Password (leave blank to keep current)</label>
                                                                            <input type="password" class="form-control" id="password<?php echo $user['id']; ?>" name="password">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="confirm_password<?php echo $user['id']; ?>" class="form-label">Confirm New Password</label>
                                                                            <input type="password" class="form-control" id="confirm_password<?php echo $user['id']; ?>" name="confirm_password">
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete User Modal -->
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <div class="modal fade" id="deleteUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form method="POST">
                                                                        <div class="modal-body">
                                                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                                            <p>Are you sure you want to delete the user "<?php echo htmlspecialchars($user['username']); ?>"?</p>
                                                                            <?php if ($user['attempt_count'] > 0): ?>
                                                                                <div class="alert alert-warning">
                                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                                    This user has <?php echo $user['attempt_count']; ?> quiz attempt(s). 
                                                                                    Deleting the user will also delete all their quiz attempts.
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
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