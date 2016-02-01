<?php
	
	require 'indexing.php';
	
	$db=mysql_connect("HOST", "DB",  "PASSWORD") or die ("Lo sentimos pero estamos experimentando problemas con nuestra base de datos. Favor de intentar de nuevo en unos minutos");
	mysql_set_charset('utf8',$db);
	//-select  the database to use
	$mydb=mysql_select_db("DB");
	
	$offset_result = mysql_query( " SELECT FLOOR(RAND() * COUNT(*)) AS `offset` FROM `pages` ");
	$offset_row = mysql_fetch_object( $offset_result ); 
	$offset = $offset_row->offset;
	$result = mysql_query( " SELECT * FROM `pages` LIMIT $offset, 1 " );
	$list = mysql_fetch_assoc($result);
	$url = $list['url'];
	mysql_close($db);
	return $url;

?>