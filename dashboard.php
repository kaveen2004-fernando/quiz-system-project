<?php
require_once '../config.php';
requireLogin();
requireAdmin();

// Get admin statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $total_users = $stmt->fetch()['total'];

    // Total quizzes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
    $total_quizzes = $stmt->fetch()['total'];

    // Total subjects
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM subjects");
    $total_subjects = $stmt->fetch()['total'];

    // Total quiz attempts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quiz_attempts");
    $total_attempts = $stmt->fetch()['total'];

    // Recent quiz attempts
    $stmt = $pdo->query("
        SELECT qa.*, u.username, q.title as quiz_title, s.name as subject_name
        FROM quiz_attempts qa
        JOIN users u ON qa.user_id = u.id
        JOIN quizzes q ON qa.quiz_id = q.id
        JOIN subjects s ON q.subject_id = s.id
        ORDER BY qa.created_at DESC
        LIMIT 10
    ");
    $recent_attempts = $stmt->fetchAll();

    // Top performing users
    $stmt = $pdo->query("
        SELECT u.username, AVG(qa.score) as avg_score, COUNT(qa.id) as quiz_count
        FROM users u
        JOIN quiz_attempts qa ON u.id = qa.user_id
        WHERE u.role = 'user'
        GROUP BY u.id, u.username
        HAVING quiz_count >= 3
        ORDER BY avg_score DESC
        LIMIT 5
    ");
    $top_users = $stmt->fetchAll();

    // Quiz statistics
    $stmt = $pdo->query("
        SELECT q.title, COUNT(qa.id) as attempt_count, AVG(qa.score) as avg_score
        FROM quizzes q
        LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
        GROUP BY q.id, q.title
        ORDER BY attempt_count DESC
        LIMIT 5
    ");
    $quiz_stats = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Database error occurred';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-0">
                <div class="position-sticky pt-3">
                    <div class="px-3 mb-4">
                        <h5 class="text-white"><i class="fas fa-user-shield me-2"></i>Admin Panel</h5>
                        <p class="small mb-0 opacity-75">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="subjects.php">
                                <i class="fas fa-book me-2"></i>Subjects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="quizzes.php">
                                <i class="fas fa-clipboard-list me-2"></i>Quizzes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="questions.php">
                                <i class="fas fa-question-circle me-2"></i>Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                      
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="../dashboard.php">
                                <i class="fas fa-eye me-2"></i>View Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card text-center p-4">
                            <div class="card-body">
                                <i class="fas fa-users stat-icon text-primary mb-3"></i>
                                <h3 class="mb-1"><?php echo number_format($total_users); ?></h3>
                                <p class="text-muted mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card text-center p-4">
                            <div class="card-body">
                                <i class="fas fa-clipboard-list stat-icon text-success mb-3"></i>
                                <h3 class="mb-1"><?php echo number_format($total_quizzes); ?></h3>
                                <p class="text-muted mb-0">Total Quizzes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card text-center p-4">
                            <div class="card-body">
                                <i class="fas fa-book stat-icon text-info mb-3"></i>
                                <h3 class="mb-1"><?php echo number_format($total_subjects); ?></h3>
                                <p class="text-muted mb-0">Subjects</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card text-center p-4">
                            <div class="card-body">
                                <i class="fas fa-chart-line stat-icon text-warning mb-3"></i>
                                <h3 class="mb-1"><?php echo number_format($total_attempts); ?></h3>
                                <p class="text-muted mb-0">Quiz Attempts</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activity -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Quiz Attempts</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="recent-activity">
                                    <?php if (empty($recent_attempts)): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No quiz attempts yet</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>User</th>
                                                        <th>Quiz</th>
                                                        <th>Subject</th>
                                                        <th>Score</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_attempts as $attempt): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                                         style="width: 35px; height: 35px;">
                                                                        <span class="text-white small fw-bold">
                                                                            <?php echo strtoupper(substr($attempt['username'], 0, 1)); ?>
                                                                        </span>
                                                                    </div>
                                                                    <?php echo htmlspecialchars($attempt['username']); ?>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($attempt['subject_name']); ?></span></td>
                                                            <td>
                                                                <span class="badge <?php echo $attempt['score'] >= 80 ? 'bg-success' : ($attempt['score'] >= 60 ? 'bg-warning' : 'bg-danger'); ?>">
                                                                    <?php echo $attempt['score']; ?>%
                                                                </span>
                                                            </td>
                                                            <td class="text-muted small">
                                                                <?php echo date('M j, Y g:i A', strtotime($attempt['created_at'])); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Users & Quick Actions -->
                    <div class="col-lg-4">
                        <!-- Top Users -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performers</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_users)): ?>
                                    <p class="text-muted text-center">No data available</p>
                                <?php else: ?>
                                    <?php foreach ($top_users as $index => $user): ?>
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 30px; height: 30px;">
                                                    <span class="text-dark small fw-bold"><?php echo $index + 1; ?></span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h6>
                                                    <small class="text-muted"><?php echo $user['quiz_count']; ?> quizzes</small>
                                                </div>
                                            </div>
                                            <span class="badge bg-success"><?php echo round($user['avg_score'], 1); ?>%</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="subjects.php?action=add" class="btn btn-outline-primary">
                                        <i class="fas fa-plus me-2"></i>Add Subject
                                    </a>
                                    <a href="quizzes.php?action=add" class="btn btn-outline-success">
                                        <i class="fas fa-plus me-2"></i>Create Quiz
                                    </a>
                                    <a href="users.php" class="btn btn-outline-info">
                                        <i class="fas fa-users me-2"></i>Manage Users
                                    </a>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Statistics -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quiz Performance</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($quiz_stats)): ?>
                                    <p class="text-muted text-center py-4">No quiz data available</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Quiz Title</th>
                                                    <th>Attempts</th>
                                                    <th>Average Score</th>
                                                    <th>Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quiz_stats as $stat): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($stat['title']); ?></td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $stat['attempt_count'] ?: 0; ?></span>
                                                        </td>
                                                        <td><?php echo $stat['avg_score'] ? round($stat['avg_score'], 1) . '%' : 'N/A'; ?></td>
                                                        <td>
                                                            <?php if ($stat['avg_score']): ?>
                                                                <div class="progress" style="height: 8px;">
                                                                    <div class="progress-bar <?php echo $stat['avg_score'] >= 80 ? 'bg-success' : ($stat['avg_score'] >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                                         style="width: <?php echo $stat['avg_score']; ?>%"></div>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted">No attempts</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
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
    <script>
        // Auto refresh stats every 30 seconds
        setInterval(function() {
            // You can add AJAX calls here to refresh statistics
        }, 30000);
    </script>
</body>
</html>