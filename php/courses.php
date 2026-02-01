<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Check if user is authorized to add courses (Admin or Instructor)
$canAddCourse = isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor');

// Handle form submission for adding courses
if ($canAddCourse && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $instructor_name = $_POST['instructor_name'];
    $status = $_POST['status'];

    $sql = "INSERT INTO courses (course_name, instructor_name, status) 
            VALUES ('$course_name', '$instructor_name', '$status')";

    if ($conn->query($sql) === TRUE) {
        $message = "Course added successfully!";
        $message_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $message_type = "error";
    }
}

// Fetch all courses data
$sql = "SELECT * FROM courses ORDER BY status, course_name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - E-Learning Platform</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .add-course-form {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .courses-list {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .status-active {
            background-color: #c6f6d5;
            color: #2f855a;
        }

        .status-completed {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .status-upcoming {
            background-color: #fefcbf;
            color: #975a16;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Course Management</h1>
            <nav>
                <ul>
                    <li><a href="../index.php" class="button">Return to Main Page</a></li>
                    <li><a href="logout.php" class="button">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <?php if (isset($message)): ?>
            <div class="<?php echo $message_type; ?>-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="courses-grid">
            <?php if ($canAddCourse): ?>
            <div class="add-course-form">
                <h2>Add New Course</h2>
                <form method="POST" class="form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="course_name">Course Name</label>
                            <input type="text" name="course_name" id="course_name" required
                                   placeholder="Enter course name">
                        </div>

                        <div class="form-group">
                            <label for="instructor_name">Instructor Name</label>
                            <input type="text" name="instructor_name" id="instructor_name" required
                                   placeholder="Enter instructor name">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" required>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Upcoming">Upcoming</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="button">Add Course</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="courses-list">
                <h2>All Courses</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Instructor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['instructor_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
        </div>
    </footer>
</body>
</html>
