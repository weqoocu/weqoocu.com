<?php

if(file_exists("index.html"))

{

unlink("index.html");

}

$baseCmsUrl = "https://www.weqoocu.com";   //这里改为你网站的根目录

$dmPageName = "index.php";

$stPageName = "index.html";

$tureStFile = dirname(__FILE__).'/'.$stPageName;

{

$body = file_get_contents($baseCmsUrl.'/'.$dmPageName);

$fp = fopen($tureStFile, 'w');

fwrite($fp, $body);

fclose($fp);

}

header("Location:$baseCmsUrl/index.html");

?>