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
    //成員,屬性
    /**
     * public: 公開的屬性，可以在任何地方訪問。
     * private: 私有的屬性，只能在類(物件)內部訪問。
     * protected: 受保護的屬性，只能在類(物入)內部或子類中訪問。
     * 
     */
     protected $name;
     protected $age;

    //方法,行為,建構函式
     public function __construct($name, $age) {
         $this->name = $name;
         $this->age = $age;
     }

    //方法
     public function greet() {
         echo "Hello, my name is {$this->name} and I am {$this->age} years old.<br>";
     }

     public function getName(){
            return $this->name;
     }
    public function getAge(){
                return $this->age;
    }
    public function setName($name){
                $this->name = $name;
    }
    public function setAge($age){
                $this->age = $age;
    }
 }


$jason = new Person('jason', 18);
echo $jason->getName();
echo "<br>";
echo $jason->getAge();
echo "<br>";
$jason->greet();;

echo "<hr>";
$jason->setName("Mary");
//$jason->age=20;
echo $jason->getName();
echo "<br>";
echo $jason->getAge();   
echo "<br>";
$jason->greet();

?>
<hr>
<h2>繼承</h2>
<?php


Interface PersonInterface {
    public function getGender();
    public function say();

}

class Man extends Person implements PersonInterface {
    private $gender='男性';
    public static  $skin='yellow';

    function getGender(){
        return $this->gender;
    }
    function say(){

    }

    static function getSkin(){
        return self::$skin;
    }   
}
class WoMan extends Person implements PersonInterface {
    private $gender='女性';
    public $skin='white';

    function getGender(){
        return $this->gender;
    }
        function say(){
        
    }
    static function getSkin(){
        return self::$skin;
    }  
}

/* $man=new Man('John', 25);
echo $man->getName();
echo "<br>";
echo $man->getGender();
echo "<br>";
$man->greet(); */
echo Man::$skin;
echo Man::getSkin();
echo "<hr>";
$woman=new Woman('Jane', 22);
echo $woman->getName();
echo "<br>";
echo $woman->getGender();
echo "<br>";
$woman->greet();

?>
</body>
</html>