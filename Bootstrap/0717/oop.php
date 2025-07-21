<?php
$apple = '我是一個紅色的蘋果';
$mongo = '我是一個黃色的芒果';
//  ..... more ......

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

// 水果 class
class Fruit
{
    //properties
    public $name;
    public $color;
    // private $color;


    // methods
    function fall()
    {
        $text = "$this->color 的 $this->name 掉下來了<br>";
        echo $text;
    }
}

// 具象化
// object
$apple = new Fruit();

// apple.name
// apple->name
// apple['name']
dd($apple);

$apple->name = "蘋果";
$apple->color = "紅色";
$apple->fall();


$mongo = new Fruit();
$mongo->name = "芒果";
$mongo->color = "黃色";
$mongo->fall();


