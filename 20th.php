<html>
<head>
<title>Multiplication table</title>
</head>
<body>
    <h2>multiplication table</h2>
    <form action="" method="post">
        <label>Number:</label>
        <input type="number" name="number"><br><br>
        <input type="submit" name="submit" value="submit">
    </form>
</body>
</html>
<?php
if(isset($_POST['submit'])){
    $number = $_POST['number'];
    echo "<p><i><center>Multiplication table of $number</center></i></p>";
    for($i=1;$i<=10;$i++){
        $result =  $number*$i;
        echo "$number * $i= ".$result;
        echo "<br>";
    }
}
?>