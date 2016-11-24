
<?php
	echo "<html><title>My parser</title><body>";
	echo "<h1 style='color:blue'>Скачиваем ссылки</h1>";
	require_once 'simple_html_dom.php';
	set_time_limit(600); 
	 /*Подключение к БД*/
	$link = mysql_connect('127.0.0.1', 'root', '') or die('Could not connect: ' . mysql_error());
	$db_selected = mysql_select_db('Parserr', $link);
		if (!$db_selected) {
			mysql_query('CREATE DATABASE Parserr') or die('Could not make database');
			$db_selected = mysql_select_db('Parserr', $link);
		}
		else{
			$clear_db = mysql_query('drop database Parserr');
			$clear_db = mysql_query('create database Parserr');
			$db_selected = mysql_select_db('Parserr', $link);
			}
	
	$arr = array( 	"Производитель",
						"Артикул", 
						"Диагональ экрана",
						"Тип дисплея",
						"Разрешение экрана", 
						"Процессор", 
						"Емкость аккумулятора", 
						"Сообщения", 
						"Цвет", 
						"Беспроводные подключения" 
													);
	
	
	$res_table = mysql_query('CREATE TABLE IF NOT EXISTS items ( 
		id int NOT NULL PRIMARY KEY auto_increment , 
		Seller varchar(50),
		link varchar(100)
		)');
	
	$properties = mysql_query('CREATE TABLE IF NOT EXISTS properties
	(
		id_item int NOT NULL auto_increment ,
		Article varchar(50),
		Screen_density varchar(50),
		Display_type varchar(50),
		Screen_razrewenie varchar(50),
		CPU varchar(50),
		Accum_capacity varchar(50),
		Messages varchar(50),
		Color varchar(50),
		Wireless_cons varchar(50),
		FOREIGN KEY (id_item) REFERENCES items(id) 
				
	)
	');

	if($res_table){echo "\nТаблица res_table создана" ;}
	else {echo"<br>\nerror Таблица res_table не создана\n<br>" . mysql_error();}
	if($properties){echo "\n<br>Таблица properties создана\n" ;}
	else {echo"<br>\nerror Таблица properties не создана\n<br>" . mysql_error();}

	
	
	$it = 0;
	for($j = 1;$j <2; $j++){
		echo "<br> <h2> Page ".$j." </h2> ";
		$url ='http://ultrapc.com.ua/catalog/phone/mobile/ultra-filter/sort-price/order-asc/view-compact/word-0/from-0/to-0/page-'.$j;
		$html1 = file_get_html($url);
		if(empty($html1)){
			echo "No HTML!";
			continue;
		}
		for( $i = 0; $i<10; $i++ ){
			
			$a_links1[$it] = $html1->find('div.good-description a',$i); 
			if(isset($a_links1[$it])){
				echo '<br>'.$it.'<br>';
				$a_links[$it] = $a_links1[$it]->getAttribute( 'href' );
				echo "http://ultrapc.com.ua/" . $a_links[$it];
				$it++;
			}
			if($it==100){
				break;
			}
		}
		$html1->clear();
		unset($html1);
	}
	
	
	//    Товары 
	echo "<br><h1 style='color:blue'>Информация о товарах</h1><br>";
	
	//unlink("/home/fancy/testfile.txt");
	$myfile = fopen("/home/fancy/parser/testfile.txt", "w") ;
	fwrite($myfile,"");
	for($i=0; $i < count($arr); $i++){
		$arri[$i] = mb_convert_encoding($arr[$i], "UTF-8");
	}
	
	
	foreach($a_links as $key=>$link){
		
		
		$src = "http://ultrapc.com.ua/" . $link;
		
		$html2 = file_get_html($src);
		if(empty($html2)){
			echo "No HTML! Take another!";
			continue;
		}
		$title1 = $html2->find('div.good-title h1',0);
		strip_tags($title1,'<h1></h1>');
		echo "\n <br><br><h4>" . $title1->plaintext . "</h4> ";
		echo  "<b>(" . $key . ")</b><br>";
		echo  "<a href=\">" . $src . "\">" . $src . "</a>";
		$title = trim($title1);
		//echo "<br>Производитель : ". $title->plaintext. "<br>";
		
		$tables1=$html2->find('div#good_tabs_descr_features table',0);
		$tables1 = mb_convert_encoding($tables1, "UTF-8");

		
		for( $i=0; $i< count($arr); $i++ )
		{
			$arr_vars[$i] = "0";
		if(preg_match("/" . $arr[$i] . "[\s:]*((\<\/th\>)|(\<th\>)|(\<td\>)|(\<\/td\>)|(\<p\>)|(\<\/p\>)|(\<tr\>)|(\<\/tr\>))*(([A-Za-zА-Яа-я0-9_-]*[\s]*[xх]?)|(\d\\.\d))*/",$tables1,$found))
			if(preg_match("/<p>([A-Za-zА-Яа-я0-9_-]*[\s]*)*$/",$found[0],$found1))
				{
					
					$arr_vars[$i] = substr($found1[0], 3, strlen($found1[0])-1);
					echo "<br> <b>" . $arr[$i] . "</b>  = " . $arr_vars[$i] . "\n";
					
				}
		}
		
		
			fputcsv($myfile, $arr_vars);

		$to_pars = mysql_query("INSERT INTO items (Seller, link) VALUES ('$arr_vars[0]','$src')");	
		if(!$to_pars)
			{echo"NO Result! pars";}
		$to_db = mysql_query("INSERT INTO properties (Article,Screen_density,Display_type,Screen_razrewenie,CPU,Accum_capacity,Messages,Color,Wireless_cons) VALUES 
		('$arr_vars[1]','$arr_vars[2]','$arr_vars[3]','$arr_vars[4]','$arr_vars[5]','$arr_vars[6]','$arr_vars[7]','$arr_vars[8]','$arr_vars[9]') ");
		if(!$to_db)
			{echo"NO Result! db";}

		
		$html2->clear();
		unset($html2);
	}
	
	mysql_close();
	fclose($myfile);
	echo "</body></html>";

?>
