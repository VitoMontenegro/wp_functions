<?php


require_once 'phpQuery-onefile.php';
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );

//функция вывода контента для парсинга 
function parse_res($url, $content) {
	$file = file_get_contents($url); // добавляем контент в переменную
	$doc = phpQuery::newDocument($file); //преобразуем контент в объект
	$res = $doc->find($content); // выбираем из объекта контиент
	return $res;
}

// проверяем ссылки на наличие в базе (уже посещенных)
function check_link($file_links, $lnk) {

	$skiplinks = $file_links; //файл со стоп-ссылками
	$all_text = $lnk . ' '; // строка с проверяемой ссылкой
		//открываем файл со стоп-ссылками в режиме чтения
	$i = 0;
		$fp = fopen($skiplinks, "r"); 
		if($fp) {
			while (!feof($fp)) {
				$words= fgets($fp); // массив сo стоп-ссылками
				if (strcasecmp($all_text, $words) == 22) {
					$i++;
				} 				
			}
		}
		if ($i>0) {
			return false;
		} else {
			return true;
		}
		
}


if (!empty($_POST['url'])) {

	$parse_url = $_POST['url']; //адрес сайта
	$parse_link = $_POST['link']; // ссылки для парсинга
	$parse_content = $_POST['text']; // содержимое сайта
	$parse_title = $_POST['title']; // заголовок сайта
	$no_content = $_POST['no_text' ];// исключить из страницы (теги, атрибуты и пр)
	$category = $_POST['category' ];
	$file_links = 'visits.txt';



	//СОБИРАЕМ ССЫЛКИ В МАССИВ
	$link = parse_res($parse_url, $parse_link);

	foreach ($link as $link_value) { //цикл по ссылкам
		$link_value = pq($link_value); //переводим ссылки в query объект 
		$my_link = $link_value->attr('href'); //загнали в массив все ссылки

		if (check_link($file_links, $my_link) == true) { //если ссылку нe парсили, парсим
			// добавляем ссылку в файл
			$f = fopen($file_links, 'a+');
			fwrite($f, $my_link . "\n");
			fclose($f);

			$result = parse_res($my_link, 'body'); // получаем содержимое каждой отдельной страницы
			$result[] = $result->remove($no_content);

			foreach ($result as $page) { //перебираем каждую страницу
				$page = pq($page);
				$site_title = $page->find('h1')->text(); // получаем заголовок
				$site_content = $page->find($parse_content); //получаем контент
				$cont_img = $page->find('p img'); //получили объект с картинками

				foreach ($cont_img as $img) {
					$img = pq($img); //переводим ссылки в query объект 
					$img->removeAttr('srcset'); //удаляем ссылки на фото
					$desc = $img->attr('title'); // тайтл фото
					$url = $img->attr('src'); // адрес фото

					// Установим данные файла
					$file_array = array();
					$tmp = download_url( $url );
					preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches );
					$file_array['name'] = basename( $matches[0] );
					$file_array['tmp_name'] = $tmp;

					// загружаем файл
					$id = media_handle_sideload( $file_array, $post_id, $desc );

					// если ошибка
					if( is_wp_error( $id ) ) {
						@unlink($file_array['tmp_name']);
						return $id->get_error_messages();
					}

					// удалим временный файл
					@unlink( $file_array['tmp_name'] );

					// все, файл загружен и должен появится в админке в медиафайлах

					$src = wp_get_attachment_url( $id); //получаем обновленный путь до фото
					$img->attr('src', $src); //обновляем абсолютные пути до фото в тексте, перед тем, как его вставить 

				}

				$site_content->find($no_content)->remove(); //удаляем ненужный контент
				$site_content = preg_replace('#<a.*>.*</a>#USi', '', $site_content);
				//$site_content = preg_replace('/<a[^>]*>(.*)<\/a>/Ui', '\\1', $site_content); //удаляем ссылки, если была перелинковка страниц
					// загружаем контент на сайт
				$my_postarr = array(
								'post_title'     => $site_title,
								'post_content'  => $site_content, // контент
								'post_status'   => 'publish' // опубликованный пост
								  );

						// добавляем пост и получаем его ID 
				$my_post_id = wp_insert_post( $my_postarr );

						// присваиваем рубрику к посту 
				wp_set_object_terms( $my_post_id, $category, 'category' );

			}
			
		} 



		



	}

}


get_header(); ?>


<form method="post" name="qwe"><h2>Парсинг сайта</h2>
	<strong>адрес рубрики сайта<font color="red">*</font></strong>
	<input type="text" name="url" ></input><br>
	<br>
	<strong>Какие ссылки брать(расположение ссылок новостей)<font color="red">*</font></strong>
	<input type="text" name="link" ></input><br>
	<br>
	<strong>Заголовок (расположение заголовка)<font color="red">*</font></strong>
	<input type="text" name="title" ></input><br>
	<br>
	<strong>Что парсить (расположение поста)<font color="red">*</font></strong>
	<input type="text" name="text" ></input><br>
	<br>
	<strong>Что исключить (исключить лишнее содержимое в посте поста)<font color="red">*</font></strong>
	<input type="text" name="no_text" ></input><br>
	<br>
	<strong>Категория<font color="red">*</font></strong>
	<input type="text" name="category" ></input><br>
	<br>
	<input type="submit" value="Загрузить файл!" />

</div>
</form>


<?php 

get_footer();







