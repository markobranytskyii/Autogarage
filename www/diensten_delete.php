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
    $conn->beginTransaction();

    $stmt = $conn->prepare("DELETE FROM afspraken WHERE dienst_id = :id");
    $stmt->execute(['id' => $id]);

    $stmt = $conn->prepare("DELETE FROM diensten WHERE dienst_id = :id");
    $stmt->execute(['id' => $id]);

    logWijziging($conn, 'Dienst definitief verwijderd', 'diensten', "Dienst #$id en de bijbehorende afspraken zijn verwijderd");

    $conn->commit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

stuurTerug('diensten_softdelete_index.php', 'verwijderd');
