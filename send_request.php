<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) header("Location: index.php");

$from = $_SESSION['user']['id'];
$to = $_POST['to'];

$stmt = $db->prepare("SELECT * FROM team_requests WHERE sender_id=? AND receiver_id=?");
$stmt->execute([$from, $to]);

if ($stmt->rowCount() == 0) {
    $db->prepare("INSERT INTO team_requests (sender_id, receiver_id) VALUES (?, ?)")->execute([$from, $to]);
    $msg = "Request sent!";
} else {
    $msg = "You already sent a request.";
}

header("Location: dashboard.php?msg=" . urlencode($msg));
exit;
