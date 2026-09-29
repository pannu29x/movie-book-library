<?php
require_once "csrf.php";
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrfToken();

    

    $title = trim($_POST["title"] ?? "");
    $type = $_POST["item_type"] ?? "";
    $genre = trim($_POST["genre"] ?? "");
    $rating = $_POST["rating"] ?? "";
    $year = $_POST["release_year"] ?? "";

    if (
        $title === "" ||
        $genre === "" ||
        !in_array($type, ["movie", "book"], true) ||
        !is_numeric($rating) ||
        $rating < 0 ||
        $rating > 10 ||
        ($year !== "" &&
            (!ctype_digit($year) ||
             (int)$year < 1 ||
             (int)$year > 9999))
    ) {
        die("Invalid input. Please go back and check your form.");
    }

    $year = ($year === "") ? null : (int)$year;

    $sql = "INSERT INTO library_items
            (title, item_type, genre, rating, release_year)
            VALUES (:title, :type, :genre, :rating, :year)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":title" => $title,
        ":type" => $type,
        ":genre" => $genre,
        ":rating" => $rating,
        ":year" => $year
    ]);

    header("Location: index.php");
    exit;
}

header("Location: index.php");
exit;
?>