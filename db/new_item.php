<!-- ФАЙЛ ДОБАВЛЕНИЯ НОВОСТИ В БАЗУ ДАННЫХ -->



<!-- В самом начале проверка на сессию, так как админ панель полностью работет на сессии -->
<?php 
session_start();
if(!isset($_SESSION["session_username"])) {
  header("location:login.php");
} else {
?>

<?php

include "partials/left_bar.php";

?>


<?php

require_once("connection.php");



// Код проверки файла ниже раньше инклудился, чтобы не занимать много места в файле, сейчас все вместе для того, чтобы продемонстрировать вам


$input_name = 'file';

$allow = array();

$deny = array(
    'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
    'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
    'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi'
);

$path =  '../uploads/';

if (isset($_FILES[$input_name])) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    $files = array();
    $diff = count($_FILES[$input_name]) - count($_FILES[$input_name], COUNT_RECURSIVE);
    if ($diff == 0) {
        $files = array($_FILES[$input_name]);
    } else {
        foreach($_FILES[$input_name] as $k => $l) {
            foreach($l as $i => $v) {
                $files[$i][$k] = $v;
            }
        }        
    }    
    
    foreach ($files as $file) {
        $error = $success = '';

        // Проверим на ошибки загрузки.
        if (!empty($file['error']) || empty($file['tmp_name'])) {
            switch (@$file['error']) {
                case 1:
                case 2: $error = 'Превышен размер загружаемого файла.'; break;
                case 3: $error = 'Файл был получен только частично.'; break;
                case 4: $error = 'Файл не был загружен.'; break;
                case 6: $error = 'Файл не загружен - отсутствует временная директория.'; break;
                case 7: $error = 'Не удалось записать файл на диск.'; break;
                case 8: $error = 'PHP-расширение остановило загрузку файла.'; break;
                case 9: $error = 'Файл не был загружен - директория не существует.'; break;
                case 10: $error = 'Превышен максимально допустимый размер файла.'; break;
                case 11: $error = 'Данный тип файла запрещен.'; break;
                case 12: $error = 'Ошибка при копировании файла.'; break;
                default: $error = 'Файл не был загружен - неизвестная ошибка.'; break;
            }
        } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
            $error = 'Не удалось загрузить файл.';
        } else {
            $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
            $name = mb_eregi_replace($pattern, '-', $file['name']);
            $name = mb_ereg_replace('[-]+', '-', $name);
            
            $converter = array(
                'а' => 'a',   'б' => 'b',   'в' => 'v',    'г' => 'g',   'д' => 'd',   'е' => 'e',
                'ё' => 'e',   'ж' => 'zh',  'з' => 'z',    'и' => 'i',   'й' => 'y',   'к' => 'k',
                'л' => 'l',   'м' => 'm',   'н' => 'n',    'о' => 'o',   'п' => 'p',   'р' => 'r',
                'с' => 's',   'т' => 't',   'у' => 'u',    'ф' => 'f',   'х' => 'h',   'ц' => 'c',
                'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',  'ь' => '',    'ы' => 'y',   'ъ' => '',
                'э' => 'e',   'ю' => 'yu',  'я' => 'ya', 
            
                'А' => 'A',   'Б' => 'B',   'В' => 'V',    'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
                'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',    'И' => 'I',   'Й' => 'Y',   'К' => 'K',
                'Л' => 'L',   'М' => 'M',   'Н' => 'N',    'О' => 'O',   'П' => 'P',   'Р' => 'R',
                'С' => 'S',   'Т' => 'T',   'У' => 'U',    'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
                'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',  'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
                'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
            );

            $name = strtr($name, $converter);
            $parts = pathinfo($name);

            if (empty($name) || empty($parts['extension'])) {
                $error = 'Недопустимое тип файла';
            } elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
                $error = 'Недопустимый тип файла';
            } elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) {
                $error = 'Недопустимый тип файла';
            } else {
                $i = 0;
                $prefix = '';
                while (is_file($path . $parts['filename'] . $prefix . '.' . $parts['extension'])) {
                      $prefix = '(' . ++$i . ')';
                }
                $name = $parts['filename'] . $prefix . '.' . $parts['extension'];

                // Перемещаем файл в директорию.
                if (move_uploaded_file($file['tmp_name'], $path . $name)) {
                    $image_path_db = "uploads/".$name."";
                    $success = 'Файл «' . $name . '» успешно загружен.';
                } else {
                    $error = 'Не удалось загрузить файл.';
                }
            }
        }
        
        if (!empty($success)) {
            echo '<p>' . $success . '</p>';        
        } else {
            echo '<p>' . $error . '</p>';
        }
    }
}




if(isset($_POST["submit"])){


 
 	if(!empty($_POST['new_name']) && !empty($_POST['area1'])) {

    $new_name = $_POST['new_name'];
    $new_text = $_POST['new_text'];
    $new_date = $_POST['new_date'];
    $new_writer = $_POST['new_writer'];
    $new_type = $_POST['new_type'];
    $new_area = $_POST['area1'];

	
	

	$result=mysqli_query($db,"INSERT INTO news (new_name, new_text, new_img, new_date, new_writer, new_type, new_area)  VALUES('$new_name', '$new_text', '$image_path_db', '$new_date', '$new_writer', '$new_type', '$new_area')");

	


	if($result){
	 $message = "Item Successfully Created";
     header('location: orders.php');
	} else {
	 $message = "Failed to insert data information!";
	}

	} else {
	 $message = "All fields are required!";
}

}

 
 





?>


<!-- Основные функции старницы выше -->
<div style="width: 100%; 
                min-height: 100vh; 
                padding: 30px 0; 
                display: flex; 
                background-color: #f1f1f1;
                align-items: center; 
                justify-content: center;
                " class="container mlogin">


<form enctype="multipart/form-data" method="post">
	<h1>Новая статья (новость)</h1>
	<p><input class="input-admin-new" type="file" name="file[]" multiple></p>

   <p><input class="input-admin-new" type="text" name="new_name" size="50"  placeholder="Название статьи">
  </p>

  <p><input class="input-admin-new" type="text" name="new_text" size="10000" placeholder="Анонс статьи"></p>

<div id="sample">
  <script type="text/javascript" src="http://js.nicedit.com/nicEdit-latest.js"></script> <script type="text/javascript">
//<![CDATA[
        bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
  //]]>
  </script>
  <h4>
    Текст статьи
  </h4>
  <textarea style="background-color: #fff;" name="area1" cols="80">
</textarea><br />

</div>








   
   <p><input class="input-admin-new" type="text" name="new_writer" size="50"  placeholder="Имя Фамилия корреспондента">
  </p>
  <h4>
    Дата формата (гггг-мм-дд)
  </h4>
  <p><input class="input-admin-new" type="text" name="new_date" size="50"  placeholder="Пример (2020-01-01)">
  </p>

   <p><select  class="select-admin-new" name="new_type">
    <option disabled>Выберите тип новости</option>

    <option value="1">Событие</option>
    <option value="2">День рождения</option>
    <option value="3">Школа</option>
    <option value="4">Интервью</option>
    <option value="5">Обзор</option>
    <option value="6">Анонс</option>

   </select></p>

  <p><input class="input-admin-new" name="submit" type="submit" value="Продолжить"></p>
 </form>
</div>


</div>

<?php
}
?>