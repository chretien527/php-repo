<?php

$host = "localhost";
$user = "root";
$password = "2010";
$database = "crud_db";

$conn = mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die("Connection failed: ".$conn->connect_error());
}

?>