<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}
$canAddResource = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor');

// Handle form submission for adding new resources
if ($canAddResource && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $title = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $type = $conn->real_escape_string(trim($_POST['type'] ?? ''));
    $upload_date = $conn->real_escape_string(trim($_POST['upload_date'] ?? date('Y-m-d')));
    $author = $conn->real_escape_string(trim($_POST['author'] ?? ''));

    $sql = "INSERT INTO resources (title, type, upload_date, author) 
            VALUES ('$title', '$type', '$upload_date', '$author')";

    if ($conn->query($sql) === TRUE) {
        $message = "New resource added successfully!";
        $message_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $message_type = "error";
    }
}

// Fetch resources data
$sql = "SELECT * FROM resources ORDER BY upload_date DESC";
$result = $conn->query($sql);
$is_php_folder = true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ressources - E-Learning</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .add-resource-form {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .resources-list {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .type-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 80px;
            background-color: #e2e8f0;
            color: #4a5568;
        }

        .date-cell {
            color: #4a5568;
            font-size: 0.9rem;
        }

        .author-cell {
            color: #2d3748;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .resources-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
    <main>
        <?php if (isset($message)): ?>
            <div class="<?php echo $message_type; ?>-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="resources-grid">
            <?php if ($canAddResource): ?>
            <div class="add-resource-form">
                <h2>Add New Resource</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" required
                                   placeholder="Enter resource title">
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" required>
                                <option value="Video">Video</option>
                                <option value="PDF">PDF</option>
                                <option value="Book">Book</option>
                                <option value="Article">Article</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="upload_date">Date</label>
                            <input type="date" name="upload_date" id="upload_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="author">Author/Source</label>
                            <input type="text" name="author" id="author" required
                                   placeholder="Enter author name">
                        </div>

                        <div class="form-group">
                            <button type="submit" name="add_resource" class="button">Add Resource</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="resources-list">
                <h2>Library Collection</h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Date Added</th>
                            <th>Author</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <span class="type-badge">
                                        <?php echo htmlspecialchars($row['type']); ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?php echo date('F j, Y', strtotime($row['upload_date'])); ?>
                                </td>
                                <td class="author-cell"><?php echo htmlspecialchars($row['author']); ?></td>
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
