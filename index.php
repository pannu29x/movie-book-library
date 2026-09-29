<?php
require_once "csrf.php";
require_once "db.php";

// Get search and filter values
$search = trim($_GET['search'] ?? '');
$type = $_GET['type'] ?? '';
$genre = $_GET['genre'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Pagination settings
$limit = 6;
$page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT
);

$page = ($page && $page > 0) ? $page : 1;

// Build WHERE conditions
$where = " WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND title ILIKE :search";
    $params[':search'] = '%' . $search . '%';
}

if (in_array($type, ['movie', 'book'], true)) {
    $where .= " AND item_type = :type";
    $params[':type'] = $type;
}

if ($genre !== '') {
    $where .= " AND genre = :genre";
    $params[':genre'] = $genre;
}




// Count all matching records
$countSql = "SELECT COUNT(*) FROM library_items"
          . $where;

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalItems = (int) $countStmt->fetchColumn();

// Calculate total pages
$totalPages = max(
    1,
    (int) ceil($totalItems / $limit)
);

// Keep page within valid range
$page = min($page, $totalPages);

// Calculate offset
$offset = ($page - 1) * $limit;

// Sorting
switch ($sort) {
    case 'rating':
        $orderBy = "rating DESC NULLS LAST";
        break;

    case 'title':
        $orderBy = "title ASC";
        break;

    default:
        $orderBy = "id DESC";
}

// Fetch current page
$sql = "SELECT * FROM library_items"
     . $where
     . " ORDER BY " . $orderBy
     . " LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bind search/filter parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination values as integers
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get genres for dropdown
$genreStmt = $pdo->query(
    "SELECT DISTINCT genre
     FROM library_items
     ORDER BY genre ASC"
);

$genres = $genreStmt->fetchAll(PDO::FETCH_COLUMN);



// Statistics for the current page
$movieCount = count(array_filter(
    $items,
    fn($item) => $item['item_type'] === 'movie'
));

$bookCount = count(array_filter(
    $items,
    fn($item) => $item['item_type'] === 'book'
));


// Overall library statistics
$statsSql = "
    SELECT
        COUNT(*) AS total,
        COUNT(*) FILTER (
            WHERE item_type = 'movie'
        ) AS movies,
        COUNT(*) FILTER (
            WHERE item_type = 'book'
        ) AS books
    FROM library_items
";

$stats = $pdo->query($statsSql)->fetch(
    PDO::FETCH_ASSOC
);

$allTotal = $stats['total'];
$allMovies = $stats['movies'];
$allBooks = $stats['books'];


// Build URLs while preserving filters
function pageUrl($pageNumber) {
    $params = $_GET;
    $params['page'] = $pageNumber;

    return 'index.php?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Library</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="navbar">
    <h2>My<span>Library</span></h2>
    <a href="#add-form" class="btn">+ Add Item</a>
</header>

<main class="container">

    <section class="hero">
        <p class="tagline">YOUR PERSONAL COLLECTION</p>
        <h1>Movie & Book Library</h1>
        <p>Discover, organize and manage your
           favorite movies and books.</p>
    </section>

   <section class="stats">

    <div class="stat-card">
        <h3>
            <?php echo $allTotal; ?>
        </h3>
        <p>Total Items</p>
    </div>

    <div class="stat-card">
        <h3>
            <?php echo $allMovies; ?>
        </h3>
        <p>Movies</p>
    </div>

    <div class="stat-card">
        <h3>
            <?php echo $allBooks; ?>
        </h3>
        <p>Books</p>
    </div>

</section>

    <section class="form-section" id="add-form">

        <h2>Add to Your Library</h2>

        <form action="add.php" method="POST"
              class="add-form">

            <input type="hidden"
       name="csrf_token"
       value="<?php echo htmlspecialchars(
           $_SESSION['csrf_token'],
           ENT_QUOTES,
           'UTF-8'
       ); ?>">

		<input type="text" name="title"
                	   placeholder="Enter title"
                   	maxlength="255" required>

            <select name="item_type" required>
                <option value="">Select Type</option>
                <option value="movie">Movie</option>
                <option value="book">Book</option>
            </select>

            <input type="text" name="genre"
                   placeholder="Genre"
                   maxlength="100" required>

            <input type="number" name="rating"
                   placeholder="Rating (0-10)"
                   min="0" max="10" step="0.1"
                   required>

            <input type="number" name="release_year"
                   placeholder="Year"
                   min="1" max="9999">

            <button type="submit" class="btn">
                Add Item
            </button>

        </form>

    </section>

    <section class="library-section">
		<form method="GET" action="index.php"
      class="filter-form">

    <input
        type="text"
        name="search"
        placeholder="Search by title..."
        value="<?php echo htmlspecialchars(
            $search, ENT_QUOTES, 'UTF-8'
        ); ?>"
    >

    <select name="type">
        <option value="">All Types</option>

        <option value="movie"
            <?php if ($type === 'movie')
                echo 'selected'; ?>>
            Movies
        </option>

        <option value="book"
            <?php if ($type === 'book')
                echo 'selected'; ?>>
            Books
        </option>
    </select>

    <select name="genre">
        <option value="">All Genres</option>

        <?php foreach ($genres as $g): ?>
            <option
                value="<?php echo htmlspecialchars(
                    $g, ENT_QUOTES, 'UTF-8'
                ); ?>"
                <?php if ($genre === $g)
                    echo 'selected'; ?>
            >
                <?php echo htmlspecialchars($g); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort">
        <option value="newest"
            <?php if ($sort === 'newest')
                echo 'selected'; ?>>
            Recently Added
        </option>

        <option value="rating"
            <?php if ($sort === 'rating')
                echo 'selected'; ?>>
            Highest Rated
        </option>

        <option value="title"
            <?php if ($sort === 'title')
                echo 'selected'; ?>>
            Title A-Z
        </option>
    </select>

    <button type="submit" class="btn">
        Search / Filter
    </button>

    <a href="index.php" class="reset-btn">
        Reset
    </a>

</form>

      <div class="section-heading">
    <h2>Your Collection</h2>

    <p>
        <?php echo $totalItems; ?> items found
        | Page <?php echo $page; ?>
        of <?php echo $totalPages; ?>
    </p>
</div>

        <div class="library-grid">

            <?php if (count($items) > 0): ?>

                <?php foreach ($items as $item): ?>

                    <div class="item-card">

                        <div class="item-top">
                            <span class="type-badge">
                                <?php echo htmlspecialchars(
                                    ucfirst($item['item_type'])
                                ); ?>
                            </span>

                            <span class="rating">
                                ★ <?php echo htmlspecialchars(
                                    $item['rating'] ?? 'N/A'
                                ); ?>
                            </span>
                        </div>

                        <h3>
                            <?php echo htmlspecialchars(
                                $item['title']
                            ); ?>
                        </h3>

                        <p class="genre">
                            <?php echo htmlspecialchars(
                                $item['genre']
                            ); ?>
                        </p>

                        <p class="year">
                            Year:
                            <?php echo htmlspecialchars(
                                $item['release_year'] ?? 'N/A'
                            ); ?>
                        </p>

                        <div class="card-actions">

                            <a class="edit-btn"
                               href="edit.php?id=<?php
                               echo $item['id']; ?>">
                                Edit
                            </a>

                            <form action="delete.php"
                                  method="POST"
                                  onsubmit="return confirm(
                                  'Delete this item?');">

				<input type="hidden"
       name="csrf_token"
       value="<?php echo htmlspecialchars(
           $_SESSION['csrf_token'],
           ENT_QUOTES,
           'UTF-8'
       ); ?>">

                                <input type="hidden"
                                       name="id"
                                       value="<?php
                                       echo $item['id']; ?>">

                                <button type="submit"
                                        class="delete-btn">
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p>No items found. Add your first item!</p>

            <?php endif; ?>

        </div>

		<!-- Pagination -->

<?php if ($totalPages > 1): ?>

    <div class="pagination">

        <!-- Previous Button -->

        <?php if ($page > 1): ?>

            <a href="<?php echo htmlspecialchars(
                pageUrl($page - 1),
                ENT_QUOTES,
                'UTF-8'
            ); ?>" class="page-btn">
                &laquo; Previous
            </a>

        <?php else: ?>

            <span class="page-btn disabled">
                &laquo; Previous
            </span>

        <?php endif; ?>


        <!-- Numbered Pages -->

        <?php for (
            $i = 1;
            $i <= $totalPages;
            $i++
        ): ?>

            <a href="<?php echo htmlspecialchars(
                pageUrl($i),
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            class="page-btn
            <?php echo ($i === $page)
                ? 'active'
                : ''; ?>">

                <?php echo $i; ?>

            </a>

        <?php endfor; ?>


        <!-- Next Button -->

        <?php if ($page < $totalPages): ?>

            <a href="<?php echo htmlspecialchars(
                pageUrl($page + 1),
                ENT_QUOTES,
                'UTF-8'
            ); ?>" class="page-btn">
                Next &raquo;
            </a>

        <?php else: ?>

            <span class="page-btn disabled">
                Next &raquo;
            </span>

        <?php endif; ?>

    </div>

<?php endif; ?>

    </section>

</main>

<footer>
    <p>MyLibrary &copy; <?php echo date("Y"); ?></p>
</footer>

</body>
</html>