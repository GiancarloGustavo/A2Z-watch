<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.gc_maxlifetime', 10); // Durée max des sessions à 10 secondes
session_set_cookie_params(10);        // Durée de vie du cookie à 10 secondes
session_start();

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "root", "azia-watch");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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
    <link rel="stylesheet" href="styles/style.css">
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
                <a href="user_profile.php">
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

    <main>
        <aside>

        </aside>
    <section class="principale">
        <!-- <h2>Vidéos récentes</h2> -->
        <div class="videos">
            <?php while ($video = $result->fetch_assoc()): ?>
                <a href="video_details.php?id=<?php echo $video['id']; ?>">
                    <div class="image" style="background-image: url('<?php echo "uploads/photos/" . htmlspecialchars($video['thumbnail_url']); ?>');"></div>
                    <div class="title"><h3><?php echo htmlspecialchars($video['title']); ?></h3></div>
                </a>
            <?php endwhile; ?>
        </div>
    </section>
    </main>

    <footer>

    </footer>
</body>
</html>
