<?php
// Admin sidebar component
?>
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : ''; ?>" href="subjects.php">
                    <i class="fas fa-book me-2"></i>
                    Subjects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'quizzes.php' ? 'active' : ''; ?>" href="quizzes.php">
                    <i class="fas fa-tasks me-2"></i>
                    Quizzes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users me-2"></i>
                    Users
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Quick Stats</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <?php
            try {
                // Get quick stats
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                $user_count = $stmt->fetch()['total'];
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
                $quiz_count = $stmt->fetch()['total'];
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM quiz_attempts");
                $attempt_count = $stmt->fetch()['total'];
            } catch (PDOException $e) {
                $user_count = $quiz_count = $attempt_count = 0;
            }
            ?>
            <li class="nav-item px-3 py-1">
                <small class="text-muted">Users: <strong><?php echo $user_count; ?></strong></small>
            </li>
            <li class="nav-item px-3 py-1">
                <small class="text-muted">Quizzes: <strong><?php echo $quiz_count; ?></strong></small>
            </li>
            <li class="nav-item px-3 py-1">
                <small class="text-muted">Attempts: <strong><?php echo $attempt_count; ?></strong></small>
            </li>
        </ul>
    </div>
</div>