<?php
/**
 * Crée le compte enseignant : username "teacher", mot de passe "teacher123"
 * À exécuter une fois : http://localhost/elearning/php/create_teacher.php
 */

include 'db.php';

$login_username = 'teacher';
$login_password = 'teacher123';
$hash = $conn->real_escape_string(password_hash($login_password, PASSWORD_BCRYPT));
$u = $conn->real_escape_string($login_username);

// Vérifier si l'utilisateur existe déjà
$check = $conn->query("SELECT id FROM users WHERE username = '$u' LIMIT 1");
if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $user_id = (int)$row['id'];
    // Mettre à jour le mot de passe au cas où
    $conn->query("UPDATE users SET password = '$hash', role = 'instructor' WHERE id = $user_id");
    // Vérifier si un enseignant existe déjà pour ce user_id
    $t = $conn->query("SELECT id FROM teachers WHERE user_id = $user_id LIMIT 1");
    if (!$t || $t->num_rows === 0) {
        $conn->query("INSERT INTO teachers (user_id, nom, prenom, domaine, grade, email) VALUES ($user_id, 'Enseignant', 'Teacher', 'Informatique', 'Professeur', 'teacher@elearning.edu')");
    }
    $msg = "Compte enseignant '$login_username' existait déjà. Mot de passe et rôle mis à jour.";
} else {
    $conn->query("INSERT INTO users (username, password, role) VALUES ('$u', '$hash', 'instructor')");
    $user_id = $conn->insert_id;
    $conn->query("INSERT INTO teachers (user_id, nom, prenom, domaine, grade, email) VALUES ($user_id, 'Enseignant', 'Teacher', 'Informatique', 'Professeur', 'teacher@elearning.edu')");
    $msg = "Compte enseignant créé : username <strong>$login_username</strong>, mot de passe <strong>$login_password</strong>.";
}

$conn->close();

if (php_sapi_name() === 'cli') {
    echo $msg . "\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Enseignant créé</title></head><body>';
    echo '<h1>Compte enseignant</h1><p>' . $msg . '</p>';
    echo '<p><a href="../index.php">Retour à l\'accueil</a> | <a href="login.php">Connexion</a></p>';
    echo '</body></html>';
}
