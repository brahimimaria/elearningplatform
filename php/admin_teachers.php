<?php
session_start();
include 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$message = '';
$message_type = 'success';
// Delete
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM teachers WHERE id = $id")) {
        $message = 'Enseignant supprimé.';
    } else {
        $message = 'Erreur suppression.';
        $message_type = 'error';
    }
}
// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $conn->real_escape_string(trim($_POST['nom'] ?? ''));
    $prenom = $conn->real_escape_string(trim($_POST['prenom'] ?? ''));
    $domaine = $conn->real_escape_string(trim($_POST['domaine'] ?? ''));
    $grade = $conn->real_escape_string(trim($_POST['grade'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    if (!$nom || !$prenom || !$domaine || !$grade || !$email) {
        $message = 'Tous les champs sont requis.';
        $message_type = 'error';
    } elseif ($edit_id > 0) {
        if ($conn->query("UPDATE teachers SET nom='$nom', prenom='$prenom', domaine='$domaine', grade='$grade', email='$email' WHERE id = $edit_id")) {
            $message = 'Enseignant modifié.';
        } else {
            $message = 'Erreur modification.';
            $message_type = 'error';
        }
    } else {
        if ($conn->query("INSERT INTO teachers (nom, prenom, domaine, grade, email) VALUES ('$nom','$prenom','$domaine','$grade','$email')")) {
            $message = 'Enseignant ajouté.';
        } else {
            $message = 'Erreur ajout.';
            $message_type = 'error';
        }
    }
}
$teachers = $conn->query("SELECT * FROM teachers ORDER BY nom, prenom");
$is_php_folder = true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des enseignants - E-Learning</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
    <h2>Gestion des enseignants</h2>
    <p>L'administrateur peut ajouter, modifier et supprimer les enseignants (Nom, Prénom, Domaine, Grade, Adresse Email).</p>
    <?php if ($message): ?><div class="<?php echo $message_type; ?>-message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <section style="margin-bottom: 2rem;">
        <h3><?php echo isset($_GET['edit']) ? 'Modifier' : 'Ajouter'; ?> un enseignant</h3>
        <?php
        $edit = null;
        if (isset($_GET['edit'])) {
            $eid = (int)$_GET['edit'];
            $r = $conn->query("SELECT * FROM teachers WHERE id = $eid");
            if ($r && $r->num_rows) $edit = $r->fetch_assoc();
        }
        ?>
        <form method="POST">
            <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?php echo $edit['id']; ?>"><?php endif; ?>
            <div class="form-group"><label>Nom</label><input type="text" name="nom" required value="<?php echo $edit ? htmlspecialchars($edit['nom']) : ''; ?>"></div>
            <div class="form-group"><label>Prénom</label><input type="text" name="prenom" required value="<?php echo $edit ? htmlspecialchars($edit['prenom']) : ''; ?>"></div>
            <div class="form-group"><label>Domaine</label><input type="text" name="domaine" required value="<?php echo $edit ? htmlspecialchars($edit['domaine']) : ''; ?>"></div>
            <div class="form-group"><label>Grade</label><input type="text" name="grade" required value="<?php echo $edit ? htmlspecialchars($edit['grade']) : ''; ?>"></div>
            <div class="form-group"><label>Adresse Email</label><input type="email" name="email" required value="<?php echo $edit ? htmlspecialchars($edit['email']) : ''; ?>"></div>
            <button type="submit" class="button"><?php echo $edit ? 'Enregistrer' : 'Ajouter'; ?></button>
            <?php if ($edit): ?><a href="admin_teachers.php" class="button">Annuler</a><?php endif; ?>
        </form>
    </section>
    <section>
        <h3>Liste des enseignants</h3>
        <table>
            <thead><tr><th>Nom</th><th>Prénom</th><th>Domaine</th><th>Grade</th><th>Email</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while ($t = $teachers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['nom']); ?></td>
                    <td><?php echo htmlspecialchars($t['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($t['domaine']); ?></td>
                    <td><?php echo htmlspecialchars($t['grade']); ?></td>
                    <td><?php echo htmlspecialchars($t['email']); ?></td>
                    <td>
                        <a href="admin_teachers.php?edit=<?php echo $t['id']; ?>" class="button">Modifier</a>
                        <a href="admin_teachers.php?delete=<?php echo $t['id']; ?>" class="button" onclick="return confirm('Supprimer ?');">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>
<footer><div class="footer-container"><p>Admin</p></div></footer>
</body>
</html>
