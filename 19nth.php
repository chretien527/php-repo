<?php
$display = [10,20,4,45,99,99,77];
function secondLargesta($array){
    sort($array);
    return $array[count($array) - 2];
}
$secondLargest = secondLargesta($display);
echo "The second largest element in the array is:".$secondLargest;
?>