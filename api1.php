<?php

$conn = mysqli_connect("localhost","root","2010","crud_api");

$method = $_SERVER['REQUEST_METHOD'];

// =============================
// READ DATA
// =============================

if($method == "GET"){
    $query = "SELECT * FROM students";
    $result = mysqli_query($conn,$query);

    $students = [];

    while($row = mysqli_fetch_assoc($result)){
        $students[] = $row;
    }

    echo json_encode($students);
}

// ==================================
// CREATE DATA
// ==================================

elseif($method == "POST"){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $course = $_POST['course'];

    $query = "INSERT INTO students(name,email,course)
             VALUES ('$name','$email','$course');"

    if(mysqli_query($conn,$query)){
        echo json_encode([
            "message" => "Student added"
        ]);
    } else {
        "message" => "Failed"
    }
}

// =============================
// UPDATE DATA 
// =============================

if($method == 'PUT'){
    parse_str(file_get_contents("php://input"), $_PUT);

    $id = $_PUT['id'];
    $name = $_PUT['name'];
    $email = $_PUT['email'];
    $course = $_PUT['course'];

    $query = "UPDATE students
             SET name = '$name',
                 email = '$email',
                 course = '$course'
            WHERE id = $id;"

            if(mysqli_query($conn,$query)){
                echo json_encode([
                    "message" => "Student Updated"
                ]);
            } else {
                echo json_encode([
                    "message" => "Failed"
                ]);
            }
}

// ===========================
// DELETE DATA
/ ============================
if($method == 'DELETE'){
    parse_str(file_get_contents('php://input'), $_DELETE);

    $id = $_DELETE['id'];

    $query = 'DELETE FROM students WHERE id = $id';
    if(mysqli($conn,$query)){
        echo json_encode([
            "message" => "Student deleted";
        ]);
    } else {
        echo json_encode([
            "message" => "Failed"
        ]);
    }
}

?>