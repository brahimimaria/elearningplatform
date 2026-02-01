<?php
include 'db.php';

// Only allow logged-in users with the 'admin' role to access the page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (registerUser($username, $password, $role)) {
        $message = "User registered successfully!";
        $message_type = "success";
    } else {
        $message = "Username already exists or registration failed. Please try again.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New User - Dental Office Management</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .register-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h2 {
            font-size: 2rem;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .register-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .form-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .form-section h3 {
            color: #2d3748;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .role-select {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .role-select select {
            appearance: none;
            width: 100%;
            padding: 0.8rem 1rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
        }

        .role-select::after {
            content: '▼';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #4a5568;
            pointer-events: none;
        }

        .password-requirements {
            margin-top: 1rem;
            padding: 1rem;
            background: #ebf8ff;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #2c5282;
        }

        .password-requirements ul {
            margin: 0.5rem 0 0 1.2rem;
            padding: 0;
        }

        .password-requirements li {
            margin-bottom: 0.3rem;
        }

        .register-button {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .role-info {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .role-card {
            flex: 1;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .role-card h4 {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .role-card p {
            color: #4a5568;
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>User Registration</h1>
            <nav>
                <ul>
                    <li><a href="../index.php" class="button">Return to Main Page</a></li>
                    <li><a href="logout.php" class="button">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="register-container">
            <div class="register-header">
                <h2>Create New User Account</h2>
                <p>Add a new user to the dental office management system</p>
            </div>

            <?php if (isset($message)): ?>
                <div class="<?php echo $message_type; ?>-message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="register-form">
                <div class="form-section">
                    <h3>User Information</h3>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required
                               placeholder="Enter username" minlength="4">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required
                               placeholder="Enter password" minlength="8">
                        <div class="password-requirements">
                            <strong>Password Requirements:</strong>
                            <ul>
                                <li>At least 8 characters long</li>
                                <li>Include numbers and letters</li>
                                <li>Include special characters</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Role Assignment</h3>
                    <div class="role-select">
                        <label for="role">Select Role</label>
                        <select name="role" id="role" required>
                            <option value="">Choose a role...</option>
                            <option value="admin">Administrator</option>
                            <option value="instructor">Instructor</option>
                            <option value="student">Student</option>
                        </select>
                    </div>

                    <div class="role-info">
                        <div class="role-card">
                            <h4>Administrator</h4>
                            <p>Full system access and user management</p>
                        </div>
                        <div class="role-card">
                            <h4>Instructor</h4>
                            <p>Create courses and schedule classes</p>
                        </div>
                        <div class="role-card">
                            <h4>Student</h4>
                            <p>View resources and enroll in courses</p>
                        </div>
                    </div>

                    <button type="submit" class="register-button">Create Account</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
        </div>
    </footer>
</body>
</html>
