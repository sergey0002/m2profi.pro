<!DOCTYPE html>
<html>
<head>
    <title>Update Schema</title>
</head>
<body>
    <h1>Database Schema Update</h1>
    <?php
    $connection = mysqli_connect('localhost', 'root', 'root', 'm2profi_doc') or die(mysqli_error());

    echo "<h2>Altering users_group_rules table...</h2>";

    // Alter ctr and act to VARCHAR
    $q1 = "ALTER TABLE users_group_rules MODIFY ctr VARCHAR(50) DEFAULT NULL";
    $q2 = "ALTER TABLE users_group_rules MODIFY act VARCHAR(50) DEFAULT NULL";

    if ($connection->query($q1)) {
        echo "<p style='color: green;'>✓ Modified ctr column to VARCHAR(50)</p>";
    } else {
        echo "<p style='color: red;'>✗ Error modifying ctr: " . $connection->error . "</p>";
    }

    if ($connection->query($q2)) {
        echo "<p style='color: green;'>✓ Modified act column to VARCHAR(50)</p>";
    } else {
        echo "<p style='color: red;'>✗ Error modifying act: " . $connection->error . "</p>";
    }

    echo "<h2>Done!</h2>";
    echo "<p><a href='ctrind.php?ctr=permissions&act=index'>Go to Permissions</a></p>";
    ?>
</body>
</html>
