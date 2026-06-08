<!DOCTYPE html>
<html>
<body>
    <?php
    
    $products = [
        "Electronics" => [
            "phones" => ['Ipone','samsung','vivo'],
            "computers" => ['Hp','lenovo','macbook']
        ],
        "Furnitures" => [
            "chairs" => ['sofa','raw','plastic'],
            "Tables" => ['plastic','glass','wooden']
        ]
    ];

    foreach($products as $category => $subcategories){
        echo "Product: " .$category."<br>";
        foreach($subcategories as $subcategory => $items){
            echo "Subcategory: ".$subcategory . "<br>";
            foreach($items as $item){
                echo "Item: ".$item."<br>";
            }
        }
    }
    
    ?>
</body>
</html>