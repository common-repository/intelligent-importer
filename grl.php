<?php
header('Content-Description: File Transfer');
header("Content-type: text/grl"); 
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header("Content-disposition: attachment; filename=\"".($testExtraction?'Test on ':'Launch scripts of ')." ".preg_replace("#[^0-9A-Za-z]#isU",'_',(@$_GET['nom_concurrent']?@$_GET['nom_concurrent']:@$_GET['nom_wordpress'])).".grl\"");
	

$is_mac=false;
if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mac')!==FALSE)
{
	$is_mac=true;
}



echo stripslashes($_GET['site_url'])."\n".
"identity=intellient_importer,".@$_GET['testExtraction']
."\n\n".@$_GET['nom_wordpress'];

die;

