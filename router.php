<?php
ini_set('memory_limit', '2G'); 
date_default_timezone_set('UTC');
$pages_controllers = scandir('controllers/');

$nombre = count($pages_controllers);
//echo $nombre;

for ($i=2; $i < $nombre; $i++) { 

	$page = $pages_controllers[$i];
	
        include 'controllers/'.$page;   
        
}



if (isset($_GET['c']) && isset($_GET['a'])) 
{
	$controller = $_GET['c'];
	$action     = $_GET['a'];
	
if (class_exists($controller, true) and method_exists($controller,$action)) 
{
	$controller = new $controller();
    $controller->$action();
} 
else 
{
	//include "erreur.php";
	       echo '404';
}

} 
else
{
	//include "erreur.php";
	         echo '404';
}






?>