<?php
require_once "csrf.php";
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	verifyCsrfToken();

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $title = trim($_POST["title"] ?? "");
    $type = $_POST["item_type"] ?? "";
    $genre = trim($_POST["genre"] ?? "");
    $rating = $_POST["rating"] ?? "";
    $year = $_POST["release_year"] ?? "";

    if (
        !$id ||
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
        die("Invalid input.");
    }

    $year = ($year === "") ? null : (int)$year;

    $sql = "UPDATE library_items
            SET title = :title,
                item_type = :type,
                genre = :genre,
                rating = :rating,
                release_year = :year
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":title" => $title,
        ":type" => $type,
        ":genre" => $genre,
        ":rating" => $rating,
        ":year" => $year,
        ":id" => $id
    ]);

    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid item ID.");
}

$stmt = $pdo->prepare(
    "SELECT * FROM library_items WHERE id = :id"
);
$stmt->execute([":id" => $id]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<main class="container">

    <section class="form-section" style="margin-top:50px">

        <h2>Edit Library Item</h2>

        <form method="POST" action="edit.php"
              class="add-form">

		<input type="hidden"
       name="csrf_token"
       value="<?php echo htmlspecialchars(
           $_SESSION['csrf_token'],
           ENT_QUOTES,
           'UTF-8'
       ); ?>">

            <input type="hidden" name="id"
                   value="<?php echo $item['id']; ?>">

            <input type="text" name="title"
                   maxlength="255" required
                   value="<?php echo htmlspecialchars(
                       $item['title'],
                       ENT_QUOTES, 'UTF-8'
                   ); ?>">

            <select name="item_type" required>
                <option value="movie"
                    <?php if ($item['item_type'] === 'movie')
                        echo 'selected'; ?>>
                    Movie
                </option>

                <option value="book"
                    <?php if ($item['item_type'] === 'book')
                        echo 'selected'; ?>>
                    Book
                </option>
            </select>

            <input type="text" name="genre"
                   maxlength="100" required
                   value="<?php echo htmlspecialchars(
                       $item['genre'],
                       ENT_QUOTES, 'UTF-8'
                   ); ?>">

            <input type="number" name="rating"
                   min="0" max="10" step="0.1"
                   required
                   value="<?php echo $item['rating']; ?>">

            <input type="number" name="release_year"
                   min="1" max="9999"
                   value="<?php echo $item['release_year']; ?>">

            <button type="submit" class="btn">
                Save Changes
            </button>

        </form>

        <br>
        <a href="index.php" class="edit-btn">Back to Library</a>

    </section>

</main>

</body>
</html>