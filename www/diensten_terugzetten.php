<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
vereisPost();
controleerCsrf();

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    stuurTerug('diensten_softdelete_index.php', 'ongeldig');
}

require 'database.php';

$id = $_POST['id'];

try {
    $stmt = $conn->prepare("UPDATE diensten SET deleted_at = NULL WHERE dienst_id = :id");
    $stmt->execute(['id' => $id]);

    logWijziging($conn, 'Dienst teruggezet', 'diensten', "Dienst #$id is teruggezet uit de prullenbak");
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

stuurTerug('diensten_index.php', 'teruggezet');
