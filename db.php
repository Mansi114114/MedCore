<?php
$conn = mysqli_connect("localhost", "root", "", "Hospital_db", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>