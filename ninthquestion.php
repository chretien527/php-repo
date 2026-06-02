<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Enter Student Marks</h2>
    <form method="post">
        Percentage: <input type="number" name="percentage" required><br><br>
        <input type="submit" value="Display Grade" name="display grade">
    </form>
    <?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $percentage = $_POST['percentage'];
        
        $grade = "";
        switch(intdiv($percentage, 10)){
            case 10:
                $grade = "A";
                break;
            case 9:
                $grade = "A";
                break;
            case 8:
                $grade = "B";
                break;
            case 7:
                $grade = "C";
                break;
            case 6:
                $grade = "D";
                break;
            case 5:
                $grade = "E";
                break;
            case 4:
                $grade = "F";
                break;
            case 3:
                 $grade = "S";
                 break;
            case 2:
                $grade = "U";
                break;
        }
        echo "<p>Percentage: $percentage%</p>";
        echo "<p>grade: $grade</p>";
    }
    ?>
</body>
</html>