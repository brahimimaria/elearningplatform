<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>E-Learning Platform</title>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>E-Learning Platform</h1>
            <nav>
                <ul>
                    <li><a href="php/resources.php" class="button">Resource Library</a></li>
                    <li><a href="php/courses.php" class="button">Courses</a></li>
                    <li><a href="php/schedule.php" class="button">Class Schedule</a></li>
                    <li><a href="php/roles.php" class="button">User Management</a></li>
                    <?php if (isset($_SESSION['username'])) { ?>
                        <li><a href="php/logout.php" class="button">Logout</a></li>
                    <?php } else { ?>
                        <li><a href="php/login.php" class="button">Login</a></li>
                        <li><a href="php/register.php" class="button">Register</a></li> <!-- Register Link -->
                    <?php } ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="welcome-section">
            <h2>Welcome to the E-Learning Platform</h2>
            <p>Empower your learning journey with our comprehensive educational tools. Our platform provides:</p>
            <ul>
                <li>Extensive resource library with books, videos, and PDFs</li>
                <li>Interactive course management for instructors and students</li>
                <li>Dynamic class scheduling and calendar</li>
                <li>Flexible user role management</li>
                <li>Intuitive and accessible interface</li>
            </ul>
        </section>

        <section class="features-section">
            <h2>Platform Features</h2>
            <div class="features">
                <div class="feature">
                    <h3>Resource Library</h3>
                    <p>Access a wide range of educational materials including videos, documents, and books.</p>
                </div>
                <div class="feature">
                    <h3>Course Management</h3>
                    <p>Enroll in courses, track progress, and manage class assignments efficiently.</p>
                </div>
                <div class="feature">
                    <h3>Class Schedule</h3>
                    <p>View upcoming classes, manage timetables, and stay organized with our calendar.</p>
                </div>
                <div class="feature">
                    <h3>User Management</h3>
                    <p>Administer user roles and permissions for admins, instructors, and students.</p>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="footer-container">
            <p>&copy; 2026 E-Learning Platform. All rights reserved.</p>
            <p id="role-display">
                <?php
                session_start();
                if (isset($_SESSION['role'])) {
                    echo "Logged in as: " . htmlspecialchars($_SESSION['role']);
                    echo " | <a href='php/logout.php'>Logout</a>";
                } else {
                    echo "Not logged in.";
                }
                ?>
            </p>
        </div>
    </footer>
    <script src="js/scripts.js"></script>
</body>
</html>
