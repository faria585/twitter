<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['followed_id'];

    $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
    $stmt->execute([$follower_id, $followed_id]);
    header('Location: profile.php?id=' . $followed_id);
}
?>
