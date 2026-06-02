<!DOCTYPE html>
<html>
<head>
    <title>Multiplication Table</title>
    <style>
        table {
            border-collapse: collapse;
            margin: 20px;
        }
        td, th {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php
    echo "<table>";
    
    // Table body
    for ($row = 1; $row <= 10; $row++) {
        for ($col = 1; $col <= 10; $col++) {
            echo "<td>" . ($row * $col) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    ?>
</body>
</html>
