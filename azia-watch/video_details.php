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

$video_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer la vidéo principale
$video_sql = "SELECT title, description, video_url, thumbnail_url FROM videos WHERE id = ?";
$stmt = $conn->prepare($video_sql);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();

// Vérifier si la vidéo existe
if (!$video) {
    echo "Vidéo introuvable.";
    exit;
}

// Récupérer les vidéos associées
$related_sql = "SELECT id, title, thumbnail_url FROM videos WHERE id != ? LIMIT 100";
$stmt = $conn->prepare($related_sql);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$related = $stmt->get_result();

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
    <section style="margin-top: 80px; padding:20px;">
        <video controls style="width:300px;">
            <!-- Afficher la vidéo en utilisant l'URL correcte du dossier 'uploads/videos/' -->
            <source src="<?php echo 'uploads/videos/' . htmlspecialchars($video['video_url']); ?>" type="video/mp4" >
            Votre navigateur ne supporte pas la vidéo.
        </video>
        <h2><?php echo htmlspecialchars($video['title']); ?></h2>
        <p> <h4>Description</h4><br /><?php echo htmlspecialchars($video['description']); ?></p>
    </section>
    <hr />
    <section style="padding:20px;">
        <h2>Vidéos associées</h2>
        <div class="videos">
            <?php while ($related_video = $related->fetch_assoc()): ?>
                <a href="video_details.php?id=<?php echo $related_video['id']; ?>">
                <div class="image" style="background-image: url('<?php echo "uploads/photos/" . htmlspecialchars($video['thumbnail_url']); ?>');"></div>
                    <p><?php echo htmlspecialchars($related_video['title']); ?></p>
                </a>
            <?php endwhile; ?>
        </div>
    </section>
</body>
</html>
