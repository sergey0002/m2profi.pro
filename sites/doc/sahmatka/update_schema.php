<?php
$connection = mysqli_connect('localhost', 'root', 'root', 'm2profi_doc') or die(mysqli_error());

// Alter ctr and act to VARCHAR
$q1 = "ALTER TABLE users_group_rules MODIFY ctr VARCHAR(50) DEFAULT NULL";
$q2 = "ALTER TABLE users_group_rules MODIFY act VARCHAR(50) DEFAULT NULL";

if ($connection->query($q1)) {
    echo "Modified ctr column.\n";
} else {
    echo "Error modifying ctr: " . $connection->error . "\n";
}

if ($connection->query($q2)) {
    echo "Modified act column.\n";
} else {
    echo "Error modifying act: " . $connection->error . "\n";
}
