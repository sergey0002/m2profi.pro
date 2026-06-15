<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$config = require_once 'config.php';
require_once 'access/template/header.php';

$deal_columns = [];
$customer_profile_columns = [];
$in_customer_profile = false;

foreach ($config['table_captions'] as $field => $title) {
    if ($field === '---') {
        $in_customer_profile = true;
        continue;
    }
    
    $column_definition = ['title' => $title, 'field' => $field, 'editor' => 'input'];

    if ($in_customer_profile) {
        $customer_profile_columns[] = $column_definition;
    } else {
        $deal_columns[] = $column_definition;
    }
}

$columns = array_merge(
    [
        ['title' => 'ID', 'field' => 'id', 'width' => 50]
    ],
    $deal_columns,
    $customer_profile_columns
);
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<style>
    #global-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background-color: #3498db;
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s, opacity 0.3s;
        z-index: 9999;
        opacity: 0;
    }
    #global-progress-bar.active {
        transform: scaleX(1);
        opacity: 1;
    }
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    #caller-table {
        min-width: 1000px;
    }
    .tabulator .tabulator-cell {
        font-size: 14px;
        width: 100%;
    }
    .event-item {
        margin: 5px 0;
        padding: 5px;
        border-bottom: 1px solid #eee;
    }
    .event-item:last-child {
        border-bottom: none;
    }
    .tabulator-row-expanded {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        opacity: 0.5; /* Start semi-transparent */
    }
    .tabulator-row-expanded.show {
        max-height: 1000px;
        opacity: 1; /* Become fully visible */
        transition: max-height 0.3s ease-out, opacity 0.5s ease-in-out;
    }
    .bordered-row {
        border-top: 2px solid black !important;
        border-left: 2px solid black !important;
        border-right: 2px solid black !important;
    }
    .bordered-expanded-content {
        border-bottom: 2px solid black !important;
        border-left: 2px solid black !important;
        border-right: 2px solid black !important;
    }
    .loading-indicator {
        display: none;
        text-align: center;
        padding: 20px;
    }
    .loading-indicator.show {
        display: block;
    }
    .loading-spinner {
        display: inline-block;
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-radius: 50%;
        border-top: 3px solid #3498db;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    select option {
        padding: 5px;
    }
    .color-select {
        padding: 5px;
        min-width: 150px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .event-container {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
        transition: opacity 0.25s ease-in-out;
    }
    .comment-form {
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px;
    }
    #new-comment {
        width: 100%;
        margin-bottom: 10px;
        padding: 5px;
    }
</style>

<script>
    const rowcolors = <?php echo json_encode($config['rowcolors']); ?>;
    <?php
        $all_columns_js = [];
        foreach ($deal_columns as $col) {
            $all_columns_js[] = "{title: '" . htmlspecialchars($col['title']) . "', field: '" . htmlspecialchars($col['field']) . "', headerFilter: 'input', resizable: true}";
        }
        foreach ($customer_profile_columns as $col) {
            $all_columns_js[] = "{title: '" . htmlspecialchars($col['title']) . "', field: '" . htmlspecialchars($col['field']) . "', headerFilter: 'input', resizable: true}";
        }
        echo "const dynamic_columns = [\n" . implode(",\n", $all_columns_js) . "\n];";
    ?>
</script>
<script src="js/dashboard.js" defer></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<div id="global-progress-bar"></div>
<div class="table-container">
    <div id="caller-table"></div>
</div>

<?php
require_once 'access/template/footer.php';
?>