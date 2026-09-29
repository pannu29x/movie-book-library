<?php
echo "<h2>PHP PostgreSQL Extension Check</h2>";

if (extension_loaded('pdo_pgsql')) {
    echo "PDO PostgreSQL: Enabled<br>";
} else {
    echo "PDO PostgreSQL: Not enabled<br>";
}

if (extension_loaded('pgsql')) {
    echo "PostgreSQL: Enabled<br>";
} else {
    echo "PostgreSQL: Not enabled<br>";
}
?>