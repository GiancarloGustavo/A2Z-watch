<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
$conn = new mysqli("localhost", "root", "root", "azia-watch");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

require_once 'functions.php'; // Inclure la fonction

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];

    // Gestion de l'upload de la photo de profil
    $profile_pic_name = generateUniqueFileName($_FILES['profile_picture']);
    $profile_pic_path = 'uploads/profile_pics/' . $profile_pic_name;
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_pic_path);

    // Mettre à jour la base de données
    $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $profile_pic_path, $user_id);
    $stmt->execute();

    header("Location: user_profile.php");
    exit;

}

// Récupération des informations utilisateur
$user_sql = "SELECT username, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_pic = $user['profile_picture'] ?: 'images/default_images.jpeg';

// Récupération de l'historique des vidéos regardées
$history_sql = "SELECT v.id, v.title, v.thumbnail_url FROM video_history vh 
                JOIN videos v ON vh.video_id = v.id 
                WHERE vh.user_id = ? ORDER BY vh.watched_at DESC";
$stmt = $conn->prepare($history_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

// Récupération des vidéos publiées
$published_sql = "SELECT id, title, thumbnail_url FROM videos WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($published_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$published = $stmt->get_result();
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

    <section class="info_perso" style="margin-left: 20px;">
    <h1>Profil de : <?php echo htmlspecialchars($user['username']); ?></h1>
        <h2>Ajouter une vidéo</h2>
        <p><a href="upload_video.php">Ajouter</a></p><br /><br />
        <form method="POST" enctype="multipart/form-data"  style="background-color: aquamarine; border-radius:6px; padding:23px; width:220px;">
            <label for="profile_picture">Changer la photo de profil :</label><br /><br />
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required><br /><br />
            <button type="submit">Mettre à jour</button>
        </form>
    </section>
    <section class="video_publiées" style="padding: 20px;">
        <h2>Historique des vidéos regardées</h2>
        <div class="videos">
            <?php while ($video = $history->fetch_assoc()): ?>
                <a href="video_details.php?id=<?php echo $video['id']; ?>">
                    <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                    <p><?php echo htmlspecialchars($video['title']); ?></p>
                </a>
            <?php endwhile; ?>
        </div>
        <h2>Vidéos publiées</h2>
        <div class="videos">
            <?php while ($video = $published->fetch_assoc()): ?>
                <a href="video_details.php?id=<?php echo $video['id']; ?>">
                <div class="image" style="background-image: url('<?php echo "uploads/photos/" . htmlspecialchars($video['thumbnail_url']); ?>');"></div>
                    <p><?php echo htmlspecialchars($video['title']); ?></p>
                </a>
                                <!-- Ajouter le formulaire pour supprimer la vidéo -->
                <form action="delete_video.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?');" class="delete_video">
                    <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                    <button type="submit" style="margin-bottom: 5px; margin-right: 3px;">Supprimer</button>
                </form>
            <?php endwhile; ?>
        </div>
    </section>
</body>
</html>
