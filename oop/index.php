<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>物件導向</title>
</head>
<body>
    <?php
    class Person {
        // 成員,屬性
        public $name;
        public $age;
        // 方法,行為,建構函式
        public function __construct($name, $age) {
            $this->name = $name;
            $this->age = $age;
        }
        // 方法
        public function greet() {
            echo "Hello, my name is {$this->name} and I am {$this->age} years old.";
        }
    }
    $jason = new Person("Jason", 25);
    echo $jason->name;
    echo "<br>";
    echo $jason->age;
    echo "<br>";
    $jason->greet();
    echo "<br>";
    ?>
</body>
</html>