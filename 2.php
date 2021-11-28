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

?>