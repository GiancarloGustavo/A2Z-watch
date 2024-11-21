<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$conn = new mysqli("localhost", "root", "root", "azia-watch");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

require_once 'functions.php'; // Inclure la fonction

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Vérifier que les fichiers sont bien envoyés
    if (!isset($_FILES['video']) || !isset($_FILES['thumbnail'])) {
        die("Les fichiers vidéo ou miniature sont manquants.");
    }

    // Gestion de l'upload de vidéo
    $video_name = generateUniqueFileName($_FILES['video']);
    $video_path = 'uploads/videos/' . $video_name;

    if (!move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
        die("Erreur lors du téléchargement de la vidéo.");
    }

    // Gestion de l'upload de la miniature
    $thumbnail_name = generateUniqueFileName($_FILES['thumbnail']);
    $thumbnail_path = 'uploads/photos/' . $thumbnail_name;

    if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
        die("Erreur lors du téléchargement de la miniature.");
    }

    // Enregistrement dans la base de données
    $sql = "INSERT INTO videos (user_id, title, description, video_url, thumbnail_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $title, $description, $video_name, $thumbnail_name);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        die("Erreur lors de l'enregistrement dans la base de données.");
    }
}


// Récupération des vidéos
$sql = "SELECT id, title, thumbnail_url FROM videos ORDER BY created_at DESC";
$result = $conn->query($sql);

// Récupération de la photo de profil de l'utilisateur
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_pic = $user['profile_picture'] ?: 'images/default_images.jpeg';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/style2.css">
    <title>Azia-Watch | Bienvenue</title>

    <style>
    .image{
        width: 327px ;
        height: 260px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 6px;
    }
    </style>
</head>
<body>
    <header>
        <!-- <div class="navbar"> -->
            <div class="logo">
                <a href="index.php">A2Z-Watch</a>
            </div>
            <!-- <div class="search">
                <span><input type="search" name="search" id=""  class="search_input"/><input type="submit" name="submit" value="search"></span>
            </div> -->
            <div class="deconnexion">
                <!-- <a href="logout.php" class="logout"><i>Déconnexion</i></a> -->
                <a href="<?php echo htmlspecialchars($profile_pic); ?>">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Photo de profil" class="profile-pic">
                </a>
            </div>
        <!-- </div> -->
        <!-- <div class="important">
           <div class="search">
                <span><input type="search" name="search" id=""  class="search_input"/><input type="submit" name="submit" value="search"></span>
            </div>
        </div> -->

       
    </header>
<body>
    <form method="POST" enctype="multipart/form-data" style="padding: 30px; margin-top:70px;">
        <label for="title">Titre de la vidéo</label><br />
        <input type="text" name="title" placeholder="Titre" required id="title" style="padding:3px; border-radius:6px;"><br /><br />
        <label for="description">Description de la vidéo</label><br />
        <textarea name="description" placeholder="Description" id="de<br />scription" style="padding:20px; border-radius:6px;"></textarea><br /><br />
        <label for="video">Choisir la vidéo</label><br />
        <input type="file" name="video" accept="video/*" required id="video" style="padding:3px; border-radius:6px;"><br /><br />
        <label for="thumbnail"> Choisir la miniature de la vidéo</label><br /><br />
        <input type="file" name="thumbnail" accept="image/*" required id="thumbnail" style="padding:3px; border-radius:6px;"><br /><br />
        <button type="submit">Publier</button>
    </form>
</body>
</html>
