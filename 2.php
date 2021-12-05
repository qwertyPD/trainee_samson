<?php

/* convertString($a, $b). Результат ее выполнение: если в строке $a содержится 2 и более подстроки $b,
то во втором месте заменить подстроку $b на инвертированную подстроку. */

function convertString($a, $b) {
    $counter = 0;   // счетчик повторений подстроки $b в строке $a
    for ($i = 0; $i <= strlen($a) - strlen($b); $i++) {
        $subStr = substr($a, $i, strlen($b));
        if ($subStr == $b) {
            $counter++;
            if ($counter == 2) {
                $a = substr_replace($a, strrev($b), $i, strlen($b));
            }
        }
    } return $a;
}

/* mySortForKey($a, $b). $a – двумерный массив вида [['a'=>2,'b'=>1],['a'=>1,'b'=>3]], $b – ключ вложенного массива. 
Результат ее выполнения: двумерном массива $a отсортированный по возрастанию значений для ключа $b. 
В случае отсутствия ключа $b в одном из вложенных массивов, выбросить ошибку класса Exception с индексом неправильного массива. */

function mySortForKey($a, $b) {
    for ($j = 0; $j < count($a); $j++) {
        for ($i = 0; $i < count($a); $i++) {
            if (!array_key_exists($b, $a[$i])) {
                throw new Exception("Ключ \"$b\" отсутствует. Индекс вложенного массива \"[$i]\"");
            }
            if ($a[$i][$b] > $a[$j][$b]) {
                $buffer = $a[$i];
                $a[$i] = $a[$j];
                $a[$j] = $buffer;
            }
        }
    }
    return $a;
}

?>