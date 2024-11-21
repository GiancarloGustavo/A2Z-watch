<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$conn = new mysqli("localhost", "root", "root", "azia-watch");

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérification de l'utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Gestion de la suppression de vidéo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id'])) {
    $videoId = $_POST['video_id'];

    // Récupérer l'URL de la vidéo pour la supprimer du serveur
    $sql = "SELECT video_url FROM videos WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $videoId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $video = $result->fetch_assoc();

    if ($video) {
        // Construire le chemin complet du fichier vidéo
        $videos_path = 'uploads/videos/'; // Dossier des vidéos
        $video_file = $videos_path . $video['video_url'];

        // Vérifier si le fichier existe et le supprimer
        if (file_exists($video_file)) {
            if (unlink($video_file)) {
                // Supprimer l'entrée de la vidéo dans la base de données
                $sql = "DELETE FROM videos WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $videoId);

                if ($stmt->execute()) {
                    header("Location: user_profile.php");
                    exit();
                } else {
                    echo "Erreur lors de la suppression de la vidéo dans la base de données : " . $stmt->error;
                }
            } else {
                echo "Erreur lors de la suppression du fichier vidéo.";
            }
        } else {
            echo "Le fichier vidéo n'existe pas : " . htmlspecialchars($video_file);
        }
    } else {
        echo "Vidéo introuvable.";
    }
} else {
    echo "Requête invalide.";
}
?>

