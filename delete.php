<?php
require_once "csrf.php";
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	verifyCsrfToken();
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid item ID.");
}

$sql = "DELETE FROM library_items WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id]);

header("Location: index.php");
exit;
?>