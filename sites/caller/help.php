<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'access/template/header.php';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Справка</h1>
<div class="bg-white p-8 rounded-lg shadow-md">
    <p>Этот раздел находится в разработке.</p>
</div>

<?php
require_once 'access/template/footer.php';
?>