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

/* Реализовать функцию exportXml($a, $b). $a – путь к xml файлу вида (структура файла приведена ниже), $b – код рубрики.
Результат ее выполнения: выбрать из БД товары (и их характеристики, необходимые для формирования файла)
входящие в рубрику $b или в любую из всех вложенных в нее рубрик, сохранить результат в файл $a. */

function exportXml($a, $b) {

    $conn = mysqli_connect("localhost", "root", "", "test_samson");
    if ($conn->connect_error) {
        die ("Ошибка подключения " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT a_product.code, a_product.name
                            FROM a_product 
                            JOIN a_category ON a_product.id = a_category.id
                            WHERE a_category.name = ?");    // Выбираю товары по названию рубрики
    $stmt->bind_param('s', $b);
    $stmt->execute();

    $dom = new domDocument("1.0", "utf-8");
    $products = $dom->appendChild($dom->createElement('Товары'));

    while ($result = $stmt->get_result()) {
        foreach ($result as $item) {

            // Создание тегов "Товар"
            $product = $products->appendChild($dom->createElement('Товар'));
            $product->setAttribute('Код', $item['code']);
            $product->setAttribute('Название', $item['name']);

            // Получение Цен
            $stmt = $conn->prepare("SELECT a_price.price_type, a_price.price
                           FROM a_price WHERE a_price.product = ?");
            $stmt->bind_param('s', $item['name']);
            $stmt->execute();

            // Создание тегов "Цена"
            while ($values = $stmt->get_result()) {
                foreach ($values as $value) {
                    $price = $dom->createElement('Цена', $value['price']);
                    $price->setAttribute('Тип', $value['price_type']);
                    $product->appendChild($price);
                }
            }

            // Получение свойств
            $stmt = $conn->prepare("SELECT a_property.property
                                    FROM a_property WHERE a_property.product = ?");
            $stmt->bind_param('s', $item['name']);
            $stmt->execute();
            $properties = $product->appendChild($dom->createElement('Свойства'));

            // Создание тегов "Свойства"
            while ($values = $stmt->get_result()) {
                foreach ($values as $value) {
                    $propertyName = stristr($value['property'], ' ', true); // Разделяю свойство на его название
                    $propertyValue = stristr($value['property'], ' ');      // и значение
                    $propertyValue = trim($propertyValue, ' ');
                    if (mb_substr($propertyValue, -1) === '%') {            // Проверяет наличие единицы измерения в конце строки
                        $property = $dom->createElement($propertyName, substr($propertyValue, 0, -1));
                        $property->setAttribute('ЕдИзм', mb_substr($propertyValue, -1));
                    } else {
                        $property = $dom->createElement($propertyName, $propertyValue);
                    }
                    $properties->appendChild($property);
                }
            }
            // Получение категорий
            $stmt = $conn->prepare("SELECT a_category.name
                                  FROM a_category
                                  JOIN a_product ON a_product.id = a_category.id
                                  WHERE a_product.name = ?");
            $stmt->bind_param('s', $item['name']);
            $stmt->execute();
            $categories = $product->appendChild(($dom->createElement('Разделы')));

            // Создание тегов "Категории"
            while ($values = $stmt->get_result()) {
                foreach ($values as $value) {
                    $category = $dom->createElement('Раздел', $value['name']);
                    $categories->appendChild($category);
                }
            }
        }
    }

    $dom->save($a);
    $conn->close();
}

exportXml('export.xml', 'Бумага');

?>