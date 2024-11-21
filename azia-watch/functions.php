
<?php 

function generateUniqueFileName($file) {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION); // Récupérer l'extension du fichier
    return uniqid("file_", true) . '.' . $extension; // Créer un nom unique avec l'extension
}


?>