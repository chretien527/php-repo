<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            border-collapse : collapse;
            width :270px;
        } td {
            width: 30px;
            height: 30px;
        }
        .black {
            background-color: black;
        }
        .white {
            background-color: white;
        }
    </style>
</head>
<body>
    <?php
    echo "<table>";
    for($i=1;$i<=8;$i++){
        echo "<tr>";
        for($j=1;$j<=8;$j++){
            $bgcolor = (($sum%2) == 0)?'white':'black';
            $sum = $i + $j;
            echo"<td class='$bgcolor'></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    ?>
</body>
</html>-->
<!DOCTYPE html>
<html>
<head>
    <title>Chess Board</title>
    <style>
        table {
            border-collapse: collapse;
            width: 270px;
        }
        td {
            width: 30px;
            height: 30px;
        }
        .black {
            background-color: black;
        }
        .white {
            background-color: white;
        }
    </style>
</head>
<body>
    <?php
    echo "<table border='1'>";
    for ($row = 1; $row <= 8; $row++) {
        echo "<tr>";
        for ($col = 1; $col <= 8; $col++) {
            // Alternate colors: if sum of row+col is even → white, else black
            $colorClass = (($row + $col) % 2 == 0) ? "white" : "black";
            echo "<td class='$colorClass'></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    ?>
</body>
</html>
