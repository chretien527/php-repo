<?php

$DB_NAME = "student_db";
$USERNAME = "root";
$PASSWORD = 2010;
$USER = "localhost";

$conn = new mysqli($USER, $USERNAME,$PASSWORD,$DB_NAME);

if($conn->connect_error){
    echo "Connection failed: ".$conn->connect_error;
} else {
    echo "Connected successfully";
}

mysqli_close($conn);

?>