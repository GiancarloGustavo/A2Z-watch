<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$conn = new mysqli("localhost", "root", "root", "azia-watch");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $default_profile_pic = 'images/default_images.jpeg';

    // Vérifier si l'email existe déjà
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "Cet email est déjà utilisé.";
        exit;
    }
    $stmt->close();

    // Insertion dans la base de données
    $sql = "INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password, $default_profile_pic);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['profile_picture'] = $default_profile_pic;

        header("Location: user_profile.php");
        exit;
    } else {
        echo "Erreur lors de l'inscription.";
    }
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Inscription</h1>
    <form method="POST">
        <label>Nom d'utilisateur :</label>
        <input type="text" name="username" required><br>
        <label>Email :</label>
        <input type="email" name="email" required><br>
        <label>Mot de passe :</label>
        <input type="password" name="password" required><br>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
