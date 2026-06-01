<?php
echo "<table width='270' border='1' cellpadding='3' cellspacing='0'>";
for($i=1;$i<=10;$i++){
    echo "<tr>";
    for($j=1;$j<=10;$j++){
        $result = $i * $j;
        echo "<td>$result</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>