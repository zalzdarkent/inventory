<?php
include('session.php');
require_auth();
$current_page = resolve_route();
?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <?php
    include('ui/head.php');

    ?>
</head>

<body>
    <?php
    include('ui/Navbar/navbar.php');

    ?>
    <?php
    include('ui/Header/header.php');

    ?>
    <main class="nxl-container">
        <div class="nxl-content">
            <?php
            if (file_exists($GLOBALS['content_file'])) {
                include($GLOBALS['content_file']);
            } else {
                echo "<p>Halaman tidak ditemukan.</p>";
            }
            ?>
        </div>
        <?php
        // include('ui/Footer/footer.php');

        ?>
    </main>
    <?php
    $scripts = isset($GLOBALS['scripts_file']) ? $GLOBALS['scripts_file'] : 'ui/scripts.php';
    include_once $scripts;
    ?>
</body>

</html>