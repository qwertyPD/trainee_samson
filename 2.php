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

/* Реализовать функцию importXml($a). $a – путь к xml файлу.
Результат ее выполнения: прочитать файл $a и импортировать его в созданную БД. */


function importXml($a) {

    $conn = mysqli_connect("localhost", "root", "", "test_samson");
    $xml = simplexml_load_file($a) or die ("Невозможно загрзить xml");
    $categories = array(); // Список добавленных в базу данных категорий
    $category_id = 0; // ID последней добавленной в базу данных категории

    $sqlLoad = function($table, $column, $data, $conn, $return_id = FALSE) { // $return_id = TRUE если нужен ID добавленной в базу данных категории
        if (is_array($data)) {
            $data = implode('\', \'', $data);
            $column = implode(', ', $column);
        }

        $sqlRequest = "INSERT INTO test_samson.$table ($column) VALUES ('$data')";

        if ($conn->query($sqlRequest) === TRUE) {
            $category_id = $conn->insert_id;
            if ($return_id === TRUE) {
                return $category_id;
            }
        } else {
            echo "Ошибка: " . $conn->error;
        }
    };

    foreach ($xml as $tag) {    
        $category = $tag->Разделы->children();

        foreach ($category as $item) {
            $value = (string) $item;

            if (!in_array($item, $categories)) {    //Загрузка категорий
                $category_id = $sqlLoad('a_category','name', $value, $conn, TRUE);
                $categories += array($category_id => $value);
                //Загрузка товаров
                $values = array($category_id, (integer) $tag['Код'], (string) $tag['Название']);
                $category_id = $sqlLoad('a_product', array('id', 'code', 'name'), $values, $conn, TRUE);  
            } else {
                $values = array(array_search($value, $categories), (integer) $tag['Код'], (string) $tag['Название']);
                $category_id = $sqlLoad('a_product', array('id', 'code', 'name'), $values, $conn, TRUE); 
            }
        }
        foreach ($tag->Цена as $price) {    //Загрузка цены
            $values = array((float) $price, (string) $price['Тип'], $tag['Название']);
            $sqlLoad('a_price', array('price', 'price_type', 'product'), $values, $conn);
        } 
        foreach ($tag->Свойства as $properties) {   //Загрузка свойств
            foreach ($properties as $property) {
                $values = array($tag['Название'], $property->getName(). ' ' . $property . $property->attributes());
                $sqlLoad('a_property', array('product', 'property'), $values, $conn);
            }
        }
    }
    mysqli_close($conn);
}

?>