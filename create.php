<?php

include 'Database.php';
if(isset($_POST['submit'])){
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];


$sql = "insert into students(firstname, lastname,email,gender,password)
VALUES('$firstname','$lastname','$email','$gender','$password')";

$result = $conn->query($sql);

if(!$result){
    echo "Failed to add new user";
} else {
    echo "New user added successfully";
}

$conn->close;

}

?>

<html>

<a href="Signup.html">Back</a>
<a href="read.php">View record from database</a>

</html>



