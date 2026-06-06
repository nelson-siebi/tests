<?php 
include 'config/init.php';

// Si c'est une page qui nécessite le layout complet
if ($show_layout) {
    include 'includes/header.php';
    include 'includes/header_mobile.php';
?>

<main class="container mx-auto px-4 py-6 md:py-8">
    <?php include "pages/{$page}.php"; ?>
</main>

<?php 
    include 'includes/nav_bottom.php';
    include 'includes/footer.php';
} else {
    // Pour les pages sans layout (login, register)
    include "pages/{$page}.php";
}
?>