<?php

// Реализовать функцию findSimple ($a, $b). $a и $b – целые положительные числа. Результат ее выполнения: массив простых чисел от $a до $b.

function findSimple($a, $b) {

    $result = array();

    for ($a; $a <= $b; $a++) {
        for ($i = 2; $i <= $a; $i++) {
            
            if ($i > (sqrt($a))) {
                array_push($result, $a);
                break;
            };

            if ($a % $i == 0) break;
        }
    }

    return $result;
}

findSimple(1, 100);

/* Реализовать функцию createTrapeze($a).
$a – массив положительных чисел, количество элементов кратно 3.
Результат ее выполнения: двумерный массив (массив состоящий из ассоциативных массивов с ключами a, b, c).
Пример для входных массива [1, 2, 3, 4, 5, 6] результат [[‘a’=>1,’b’=>2,’с’=>3],[‘a’=>4,’b’=>5 ,’c’=>6]]. */

function createTrapeze($a) {

    $result = [];

    for ($i = 0; $i < count($a); $i += 3) {
        array_push($result, array(
            'a' => $a[$i],
            'b' => $a[$i + 1],
            'c' => $a[$i + 2]
        ));
    }
    return $result;
}

$a = [1, 2, 3, 4, 5, 6, 7, 8, 9];
createTrapeze($a);

/* Реализовать функцию squareTrapeze($a). $a – массив результата выполнения функции createTrapeze().
Результат ее выполнения: в исходный массив для каждой тройки чисел добавляется дополнительный ключ s,
содержащий результат расчета площади трапеции со сторонами a и b, и высотой c. */

function squareTrapeze(& $a) {
    for ($i = 0; $i < count($a); $i++) {
        $a[$i] += ['s' => ($a[$i]['a'] + $a[$i]['b']) / 2 * $a[$i]['c']];
    }
    return $a;
}

/* Реализовать функцию getSizeForLimit($a, $b). $a – массив результата выполнения функции squareTrapeze(),$b – максимальная площадь. 
Результат ее выполнения: массив размеров трапеции с максимальной площадью, но меньше или равной $b */

function getSizeForLimit($a, $b) {
    $currentMaxSquare = $a[0]['s'];
    for ($i = 0; $i < count($a); $i++) {
        if ($a[$i]['s'] > $currentMaxSquare && $a[$i]['s'] <= $b) {
            $maxSquare = $a[$i];
        }
    }
    return $maxSquare;
}

$resultGetSizeForLimit = getSizeForLimit($resultSquareTrapeze, 30);

// Реализовать функцию getMin($a). $a – массив чисел. Результат ее выполнения: минимальное число в массиве (не используя функцию min, ключи массива могут быть ассоциативными).

function getMin($a) {
    $keys = array_keys($a);
    $currentMinValue = $a[$keys[0]];
    
    foreach ($a as $key) {
        if ($key < $currentMinValue) {
            $currentMinValue = $key;
        }
    }
    return $currentMinValue;
}

getMin($resultGetSizeForLimit);

/* Реализовать функцию printTrapeze($a). $a – массив результата выполнения функции squareTrapeze().
Результат ее выполнения: вывод таблицы с размерами трапеций, строки с нечетной площадью трапеции отметить любым способом. */

function printTrapeze($a) {
    echo "<table border = \"3\"><tr>";
    foreach ($a as $trapeze) {
        foreach ($trapeze as $key) {
            if ($key == $trapeze['s']) {
                if ($key % 2 != 0) {
                    echo "<td bgcolor = \"red\">", $key, "</td>";
                } else echo "<td>", $key, "</td>";
            } else echo "<td>", $key, "</td>";
        } 
        echo "<tr>";
    }
}

printTrapeze($resultSquareTrapeze);

/* Реализовать абстрактный класс BaseMath содержащий 3 метода: exp1($a, $b, $c) и exp2($a, $b, $c),getValue().
Метод exp1 реализует расчет по формуле a*(b^c).
Метод exp2 реализует расчет по формуле (a/b)^c.
Метод getValue() возвращает результат расчета класса наследника. */

abstract class BaseMath {
    public function exp1($a, $b, $c) {
        return $a * pow($b, $c);
    }
    public function exp2($a, $b, $c) {
        return pow($a / $b, $c);
    }
    abstract function getValue();
}

/* Реализовать класс F1 наследующий методы BaseMath, содержащий конструктор с параметрами ($a, $b, $c)
и метод getValue(). Класс реализует расчет по формуле f=(a*(b^c)+(((a/c)^b)%3)^min(a,b,c)). */

class F1 extends BaseMath {
    public $f;
    public function __construct($a, $b, $c) {
        $this->f = $this->exp1($a, $b, $c) + pow(($this->exp2($a, $b, $c)) % 3, min($a, $b, $c));
    }
    public function getValue() {
        $this->f;
    }
}

?>