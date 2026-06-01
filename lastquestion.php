<?php
$result = "";

if(isset($_POST['operation'])){
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];
    $operation = $_POST['operation'];

    switch($operation){
        case "add":
            $result = $num1 + $num2;
            break;
        case "sub":
            $result = $num1 - $num2;
            break;
        case "mul":
            $result = $num1 * $num2;
            break;
        case "div":
            if($num2 != 0){
                $result = $num1 / $num2;
            } else{
                $result = "Error";
            }

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Simple Calculator Operation</h2>
<form method="post">
    First Number:
    <input type="number" name="num1"><br><br>

    Second Number:
    <input type="number" name="num2"><br><br>

    Result:
    <input type="text" value="<?php echo $result; ?>" readonly><br><br>

    <button type="submit" name="operation" value="add">+</button>
    <button type="submit" name="operation" value="sub">-</button>
    <button type="submit" name="operation" value="mul">*</button>
    <button type="submit" name="operation" value="div">/</button>
</form>

</body>
</html>

