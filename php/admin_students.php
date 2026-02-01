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
    $row = $conn->query("SELECT user_id FROM students WHERE id = $id")->fetch_assoc();
    if ($row) {
        $uid = (int)$row['user_id'];
        $conn->query("DELETE FROM students WHERE id = $id");
        $conn->query("DELETE FROM users WHERE id = $uid");
        $message = 'Étudiant supprimé.';
    } else {
        $message = 'Erreur suppression.';
        $message_type = 'error';
    }
}
// Add: create user + student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $username = $conn->real_escape_string(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $numero_carte = $conn->real_escape_string(trim($_POST['numero_carte'] ?? ''));
    $nom = $conn->real_escape_string(trim($_POST['nom'] ?? ''));
    $prenom = $conn->real_escape_string(trim($_POST['prenom'] ?? ''));
    $annee = (int)($_POST['annee'] ?? 1);
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    if (!$username || !$password || !$numero_carte || !$nom || !$prenom || !$email) {
        $message = 'Tous les champs sont requis.';
        $message_type = 'error';
    } else {
        $r = registerStudent($username, $password, $numero_carte, $nom, $prenom, $annee, $email);
        $message = $r['ok'] ? 'Étudiant ajouté.' : $r['msg'];
        if (!$r['ok']) $message_type = 'error';
    }
}
// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && (int)$_POST['edit_id'] > 0) {
    $eid = (int)$_POST['edit_id'];
    $numero_carte = $conn->real_escape_string(trim($_POST['numero_carte'] ?? ''));
    $nom = $conn->real_escape_string(trim($_POST['nom'] ?? ''));
    $prenom = $conn->real_escape_string(trim($_POST['prenom'] ?? ''));
    $annee = (int)($_POST['annee'] ?? 1);
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    if ($conn->query("UPDATE students SET numero_carte='$numero_carte', nom='$nom', prenom='$prenom', annee=$annee, email='$email' WHERE id = $eid")) {
        $message = 'Étudiant modifié.';
    } else {
        $message = 'Erreur modification.';
        $message_type = 'error';
    }
}
$students = $conn->query("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id ORDER BY s.nom, s.prenom");
$is_php_folder = true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des étudiants - E-Learning</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
    <h2>Gestion des étudiants</h2>
    <p>L'administrateur peut ajouter, modifier et supprimer les étudiants (Numéro de la carte, Nom, Prénom, Année, Adresse Email).</p>
    <?php if ($message): ?><div class="<?php echo $message_type; ?>-message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <section style="margin-bottom: 2rem;">
        <h3><?php echo isset($_GET['edit']) ? 'Modifier' : 'Ajouter'; ?> un étudiant</h3>
        <?php
        $edit = null;
        if (isset($_GET['edit'])) {
            $eid = (int)$_GET['edit'];
            $r = $conn->query("SELECT * FROM students WHERE id = $eid");
            if ($r && $r->num_rows) $edit = $r->fetch_assoc();
        }
        ?>
        <form method="POST">
            <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?php echo $edit['id']; ?>"><?php endif; ?>
            <div class="form-group"><label>Numéro de la carte</label><input type="text" name="numero_carte" required value="<?php echo $edit ? htmlspecialchars($edit['numero_carte']) : ''; ?>"></div>
            <div class="form-group"><label>Nom</label><input type="text" name="nom" required value="<?php echo $edit ? htmlspecialchars($edit['nom']) : ''; ?>"></div>
            <div class="form-group"><label>Prénom</label><input type="text" name="prenom" required value="<?php echo $edit ? htmlspecialchars($edit['prenom']) : ''; ?>"></div>
            <div class="form-group"><label>Année</label><input type="number" name="annee" min="1" max="5" required value="<?php echo $edit ? (int)$edit['annee'] : 1; ?>"></div>
            <div class="form-group"><label>Adresse Email</label><input type="email" name="email" required value="<?php echo $edit ? htmlspecialchars($edit['email']) : ''; ?>"></div>
            <?php if (!$edit): ?>
            <div class="form-group"><label>Identifiant (username)</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required minlength="6"></div>
            <?php endif; ?>
            <button type="submit" class="button"><?php echo $edit ? 'Enregistrer' : 'Ajouter'; ?></button>
            <?php if ($edit): ?><a href="admin_students.php" class="button">Annuler</a><?php endif; ?>
        </form>
    </section>
    <section>
        <h3>Liste des étudiants</h3>
        <table>
            <thead><tr><th>Numéro carte</th><th>Nom</th><th>Prénom</th><th>Année</th><th>Email</th><th>Username</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while ($s = $students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['numero_carte']); ?></td>
                    <td><?php echo htmlspecialchars($s['nom']); ?></td>
                    <td><?php echo htmlspecialchars($s['prenom']); ?></td>
                    <td><?php echo (int)$s['annee']; ?></td>
                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                    <td><?php echo htmlspecialchars($s['username']); ?></td>
                    <td>
                        <a href="admin_students.php?edit=<?php echo $s['id']; ?>" class="button">Modifier</a>
                        <a href="admin_students.php?delete=<?php echo $s['id']; ?>" class="button" onclick="return confirm('Supprimer ?');">Supprimer</a>
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
