<?php
session_start();
include 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: courses.php'); exit; }
$course = $conn->query("SELECT * FROM courses WHERE id = $id")->fetch_assoc();
if (!$course) { header('Location: courses.php'); exit; }
$message = '';
$message_type = 'success';
// Update course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $titre = $conn->real_escape_string(trim($_POST['titre'] ?? ''));
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $public_cible = $conn->real_escape_string(trim($_POST['public_cible'] ?? ''));
    $cle_inscription = $conn->real_escape_string(trim($_POST['cle_inscription'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $status = in_array($_POST['status'] ?? '', ['Active','Completed','Upcoming']) ? $_POST['status'] : 'Active';
    if ($titre && $teacher_id && $public_cible && $cle_inscription && $conn->query("UPDATE courses SET titre='$titre', teacher_id=$teacher_id, public_cible='$public_cible', cle_inscription='$cle_inscription', description='$description', status='$status' WHERE id = $id")) {
        $message = 'Cours mis à jour.';
        $course = $conn->query("SELECT * FROM courses WHERE id = $id")->fetch_assoc();
    } else {
        $message = 'Erreur mise à jour.';
        $message_type = 'error';
    }
}
// Add resource (support)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $title = $conn->real_escape_string(trim($_POST['res_title'] ?? ''));
    $type = in_array($_POST['res_type'] ?? '', ['video','document']) ? $_POST['res_type'] : 'document';
    $url = $conn->real_escape_string(trim($_POST['res_url'] ?? ''));
    $d = date('Y-m-d');
    if ($title && $conn->query("INSERT INTO course_resources (course_id, title, type, url_or_path, upload_date) VALUES ($id, '$title', '$type', '$url', '$d')")) {
        $message = 'Support ajouté.';
    } else {
        $message = 'Erreur ajout support.';
        $message_type = 'error';
    }
}
// Delete resource
if (isset($_GET['del_res']) && (int)$_GET['del_res'] > 0) {
    $rid = (int)$_GET['del_res'];
    $conn->query("DELETE FROM course_resources WHERE id = $rid AND course_id = $id");
    $message = 'Support supprimé.';
}
// Add evaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_eval'])) {
    $title = $conn->real_escape_string(trim($_POST['eval_title'] ?? ''));
    $type = in_array($_POST['eval_type'] ?? '', ['quiz','devoir','examen']) ? $_POST['eval_type'] : 'devoir';
    $desc = $conn->real_escape_string(trim($_POST['eval_desc'] ?? ''));
    $due = !empty($_POST['eval_due']) ? $conn->real_escape_string($_POST['eval_due']) : 'NULL';
    $max = (int)($_POST['eval_max'] ?? 100);
    if ($title) {
        $due_sql = $due === 'NULL' ? 'NULL' : "'$due'";
        if ($conn->query("INSERT INTO evaluations (course_id, type, title, description, due_date, max_score) VALUES ($id, '$type', '$title', '$desc', $due_sql, $max)")) {
            $message = 'Évaluation ajoutée.';
        } else {
            $message = 'Erreur ajout évaluation.';
            $message_type = 'error';
        }
    }
}
// Delete evaluation
if (isset($_GET['del_eval']) && (int)$_GET['del_eval'] > 0) {
    $eid = (int)$_GET['del_eval'];
    $conn->query("DELETE FROM evaluations WHERE id = $eid AND course_id = $id");
    $message = 'Évaluation supprimée.';
}
$teachers = $conn->query("SELECT id, nom, prenom FROM teachers ORDER BY nom");
$resources = $conn->query("SELECT * FROM course_resources WHERE course_id = $id ORDER BY type, title");
$evals = $conn->query("SELECT * FROM evaluations WHERE course_id = $id ORDER BY due_date");
$is_php_folder = true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le cours - E-Learning</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .edit-section { margin: 2rem 0; padding: 1.5rem; background: #f8fafc; border-radius: 12px; }
        .edit-section h3 { margin-bottom: 1rem; }
        .inline-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
    <h2>Modifier le cours : <?php echo htmlspecialchars($course['titre']); ?></h2>
    <?php if ($message): ?><div class="<?php echo $message_type; ?>-message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <div class="edit-section">
        <h3>Informations du cours</h3>
        <form method="POST">
            <input type="hidden" name="update_course" value="1">
            <div class="form-group"><label>Titre</label><input type="text" name="titre" required value="<?php echo htmlspecialchars($course['titre']); ?>"></div>
            <div class="form-group"><label>Enseignant</label>
                <select name="teacher_id" required>
                    <?php while ($t = $teachers->fetch_assoc()): ?>
                        <option value="<?php echo (int)$t['id']; ?>" <?php echo (int)$course['teacher_id'] === (int)$t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['prenom'] . ' ' . $t['nom']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Public ciblé</label><input type="text" name="public_cible" required value="<?php echo htmlspecialchars($course['public_cible']); ?>"></div>
            <div class="form-group"><label>Clé d'inscription</label><input type="text" name="cle_inscription" required value="<?php echo htmlspecialchars($course['cle_inscription']); ?>"></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea></div>
            <div class="form-group"><label>Statut</label><select name="status"><?php foreach (['Active','Upcoming','Completed'] as $s): ?><option value="<?php echo $s; ?>" <?php echo ($course['status'] ?? '') === $s ? 'selected' : ''; ?>><?php echo $s; ?></option><?php endforeach; ?></select></div>
            <button type="submit" class="button">Enregistrer</button>
        </form>
    </div>
    <div class="edit-section">
        <h3>Supports (vidéo, documents)</h3>
        <form method="POST">
            <input type="hidden" name="add_resource" value="1">
            <div class="form-group"><label>Titre</label><input type="text" name="res_title" required></div>
            <div class="form-group"><label>Type</label><select name="res_type"><option value="video">Vidéo</option><option value="document">Document</option></select></div>
            <div class="form-group"><label>URL ou chemin</label><input type="text" name="res_url" placeholder="https://..."></div>
            <button type="submit" class="button">Ajouter</button>
        </form>
        <ul style="list-style: none; padding: 0; margin-top: 1rem;">
            <?php while ($r = $resources->fetch_assoc()): ?>
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                <?php echo htmlspecialchars($r['title']); ?> (<?php echo $r['type']; ?>)
                <a href="admin_course_edit.php?id=<?php echo $id; ?>&del_res=<?php echo $r['id']; ?>" onclick="return confirm('Supprimer ?');">Supprimer</a>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <div class="edit-section">
        <h3>Évaluations (Quiz, devoirs, examens)</h3>
        <form method="POST">
            <input type="hidden" name="add_eval" value="1">
            <div class="form-group"><label>Titre</label><input type="text" name="eval_title" required></div>
            <div class="form-group"><label>Type</label><select name="eval_type"><option value="quiz">Quiz</option><option value="devoir">Devoir</option><option value="examen">Examen</option></select></div>
            <div class="form-group"><label>Description</label><textarea name="eval_desc" rows="2"></textarea></div>
            <div class="form-group"><label>Date limite</label><input type="date" name="eval_due"></div>
            <div class="form-group"><label>Note max</label><input type="number" name="eval_max" value="100"></div>
            <button type="submit" class="button">Ajouter</button>
        </form>
        <ul style="list-style: none; padding: 0; margin-top: 1rem;">
            <?php while ($e = $evals->fetch_assoc()): ?>
            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                <?php echo htmlspecialchars($e['title']); ?> (<?php echo $e['type']; ?>) <?php if ($e['due_date']): ?> — <?php echo date('d/m/Y', strtotime($e['due_date'])); endif; ?>
                <a href="admin_course_edit.php?id=<?php echo $id; ?>&del_eval=<?php echo $e['id']; ?>" onclick="return confirm('Supprimer ?');">Supprimer</a>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <p><a href="courses.php" class="button">← Retour aux cours</a></p>
</main>
<footer><div class="footer-container"><p>Admin</p></div></footer>
</body>
</html>
