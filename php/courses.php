<?php
session_start();
include 'db.php';
$message = '';
$message_type = 'success';
// Admin: delete course
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $cid = (int)$_GET['delete'];
    $conn->query("DELETE FROM courses WHERE id = $cid");
    $message = 'Cours supprimé.';
}
// Admin: add course
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $titre = $conn->real_escape_string(trim($_POST['titre'] ?? ''));
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $public_cible = $conn->real_escape_string(trim($_POST['public_cible'] ?? ''));
    $cle_inscription = $conn->real_escape_string(trim($_POST['cle_inscription'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $status = in_array($_POST['status'] ?? '', ['Active','Completed','Upcoming']) ? $_POST['status'] : 'Active';
    if ($titre && $teacher_id && $public_cible && $cle_inscription) {
        // Vérifier si la clé d'inscription existe déjà
        $check = $conn->query("SELECT id FROM courses WHERE cle_inscription = '$cle_inscription' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $message = 'Cette clé d\'inscription est déjà utilisée. Choisissez une autre clé.';
            $message_type = 'error';
        } else {
            try {
                if ($conn->query("INSERT INTO courses (titre, teacher_id, public_cible, cle_inscription, description, status) VALUES ('$titre', $teacher_id, '$public_cible', '$cle_inscription', '$description', '$status')")) {
                    $message = 'Cours ajouté.';
                } else {
                    $message = 'Erreur lors de l\'ajout du cours.';
                    $message_type = 'error';
                }
            } catch (mysqli_sql_exception $e) {
                $message = (isset($conn->errno) && $conn->errno === 1062) ? 'Cette clé d\'inscription est déjà utilisée. Choisissez une autre clé.' : 'Erreur base de données.';
                $message_type = 'error';
            }
        }
    } else {
        $message = 'Titre, enseignant, public ciblé et clé requis.';
        $message_type = 'error';
    }
}
$specialty = isset($_GET['specialty']) ? trim($_GET['specialty']) : '';
$sql = "SELECT c.id, c.titre, c.public_cible, c.cle_inscription, c.description, c.status, t.nom AS t_nom, t.prenom AS t_prenom, t.domaine FROM courses c JOIN teachers t ON c.teacher_id = t.id WHERE 1=1";
$params = [];
$types = '';
if ($specialty !== '') {
    $sql .= " AND c.public_cible = ?";
    $params[] = $specialty;
    $types .= 's';
}
$sql .= " ORDER BY c.titre";
if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
$courses_list = [];
while ($row = $result->fetch_assoc()) $courses_list[] = $row;
$specialties = [];
$r = $conn->query("SELECT DISTINCT public_cible FROM courses ORDER BY public_cible");
if ($r) while ($row = $r->fetch_assoc()) $specialties[] = $row['public_cible'];
$teachers = $conn->query("SELECT id, nom, prenom FROM teachers ORDER BY nom");
$is_php_folder = true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cours par spécialité - E-Learning</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
        .course-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        .course-card h3 { margin-bottom: 0.5rem; }
        .course-card a { color: #2b6cb0; text-decoration: none; font-weight: 600; }
        .filter-form { margin-bottom: 1.5rem; display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
        .filter-form select { max-width: 250px; }
        .status-badge { font-size: 0.85rem; padding: 0.25rem 0.5rem; border-radius: 6px; }
        .status-active { background: #c6f6d5; color: #2f855a; }
        .status-completed { background: #bee3f8; color: #2c5282; }
        .status-upcoming { background: #fefcbf; color: #975a16; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
    <h2>Cours disponibles par spécialité</h2>
    <?php if ($message): ?><div class="<?php echo $message_type; ?>-message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <section class="admin-add-course" style="margin-bottom: 2rem; padding: 1.5rem; background: #f0fff4; border-radius: 12px;">
        <h3>Ajouter un cours (admin)</h3>
        <form method="POST">
            <input type="hidden" name="add_course" value="1">
            <div class="form-group"><label>Titre du cours</label><input type="text" name="titre" required></div>
            <div class="form-group"><label>Enseignant responsable</label>
                <select name="teacher_id" required>
                    <option value="">Choisir...</option>
                    <?php while ($t = $teachers->fetch_assoc()): ?>
                        <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['prenom'] . ' ' . $t['nom']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Public ciblé</label><input type="text" name="public_cible" required placeholder="Ex: L3 Informatique"></div>
            <div class="form-group"><label>Clé d'inscription</label><input type="text" name="cle_inscription" required placeholder="Ex: DB2024"></div>
            <div class="form-group"><label>Information sur le cours</label><textarea name="description" rows="3"></textarea></div>
            <div class="form-group"><label>Statut</label><select name="status"><option value="Active">Active</option><option value="Upcoming">Upcoming</option><option value="Completed">Completed</option></select></div>
            <button type="submit" class="button">Ajouter le cours</button>
        </form>
    </section>
    <?php endif; ?>
    <form class="filter-form" method="GET">
        <label for="specialty">Spécialité / Public ciblé :</label>
        <select name="specialty" id="specialty" onchange="this.form.submit()">
            <option value="">Toutes</option>
            <?php foreach ($specialties as $s): ?>
                <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $specialty === $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <div class="courses-grid">
        <?php foreach ($courses_list as $row): ?>
        <div class="course-card">
            <h3><a href="course_detail.php?id=<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['titre']); ?></a></h3>
            <p><strong>Public ciblé :</strong> <?php echo htmlspecialchars($row['public_cible']); ?></p>
            <p><strong>Enseignant :</strong> <?php echo htmlspecialchars($row['prenom'] . ' ' . $row['nom']); ?></p>
            <p><strong>Domaine :</strong> <?php echo htmlspecialchars($row['domaine']); ?></p>
            <?php if ($row['description']): ?><p><?php echo htmlspecialchars(mb_substr($row['description'], 0, 120)); ?>…</p><?php endif; ?>
            <span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <p style="margin-top: 0.75rem;">
                <a href="admin_course_edit.php?id=<?php echo (int)$row['id']; ?>" class="button">Modifier</a>
                <a href="courses.php?delete=<?php echo (int)$row['id']; ?>" class="button" onclick="return confirm('Supprimer ce cours ?');">Supprimer</a>
            </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (empty($courses_list)): ?><p>Aucun cours trouvé.</p><?php endif; ?>
</main>
<footer><div class="footer-container"><p>&copy; <?php echo date('Y'); ?> E-Learning.</p></div></footer>
</body>
</html>
