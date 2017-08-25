<?php 
if (isset($_POST['textt'])) { //проверяем если отправил сообщение


	function testmessage($ob_desc) { //проверка на стоп-слова
		$stopwords =  get_template_directory_uri() . '/stopworlds.txt'; //путь до файла со стоп-словами
		$all_text = $_POST['textt']; // передаем сообщение в переменную  (строковый массив) 
		$i = 0;
		//открываем файл со стоп-словами в режиме чтения
		$fp = fopen($stopwords, "r"); 
		if($fp) {
			while (!feof($fp)) {
				$words= fgets($fp, 10000);       
				// проверяем на совпадения сообщение со списком стоп-слов
				$pos = strpos($all_text, trim($words)); 
				if ($pos !== false)
					$i++; //если "поймали" стоп-слово, увеличиваем счетчик
			}
		}

		if($i>0) { // если стоп слов больше чем "0"
			return false; // проверка выдаст "ЛОЖЬ"
		} else {
			return true; //проверка выдаст "ПРАВДА"
		}
	}  

	$result = $_POST['titlee']; // Записываем в переменную имя, фамилию
	$textt = $_POST['textt']; // Записываем в переменную текст
	$yearss = $_POST['yearss']; // кладем в переменную значение поля "возраст"

	if ($result <> '')  { // если значение поля "Имя" не пустое
		if (testmessage($textt) == 'true') { // если проверка выдала "ПРАВДА!", то есть стоп слов нет
			$status_msg = 'publish'; // статус "опубликовано"
		} else {
			$status_msg = 'pending'; // статус "на утверждение"
		}


		
		 // Создаем массив
		$post_data = array(
			'post_title'    => wp_strip_all_tags($result), 
			'post_content'  => wp_strip_all_tags($textt),
			'post_yearss' => wp_strip_all_tags($yearss),
			'post_status'   => $status_msg,
			'post_author'   => 1,
			'post_category' => array(1) //категория "Письма"
			);
			// Вставляем данные в БД
		$post_id = wp_insert_post( wp_slash($post_data) ); // заголовок (имя) сообщение
		update_post_meta( $post_id, 'ads_phone', $yearss ); // дополнительные поля - возраст
		//echo "<script language=\"JavaScript\">alert('Вы не ввели Ваше имя. Представьтесь пожалуйста.');</script>";
		header ('Location: '.$_SERVER['REQUEST_URI']);
		exit();
	} else { // если значение "имя" пустое
			echo "<script language=\"JavaScript\">alert('Вы не ввели Ваше имя. Представьтесь пожалуйста.');</script>";
	}
} 
?>
