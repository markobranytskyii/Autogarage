<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
vereisPost();
controleerCsrf();

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    stuurTerug('diensten_index.php', 'ongeldig');
}

require 'database.php';

$id = $_POST['id'];

try {
    $stmt = $conn->prepare("UPDATE diensten SET deleted_at = NOW() WHERE dienst_id = :id");
    $stmt->execute(['id' => $id]);

    logWijziging($conn, 'Dienst uitgezet', 'diensten', "Dienst #$id is naar de prullenbak verplaatst");
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

stuurTerug('diensten_index.php', 'uitgezet');
