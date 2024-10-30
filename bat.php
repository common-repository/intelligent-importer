<?php
if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mac')!==FALSE)
{
	header("Content-type: application/x-sh"); 
	header("Content-disposition: attachment; filename=\"LAUNCH.sh\"");
}
else
{
	header("Content-type: application/bat"); 
	header("Content-disposition: attachment; filename=\"LAUNCH.bat\"");
}

$testExtraction='';
if($_GET['testExtraction']) $testExtraction=' '.str_replace('---PLUS----','+',$_GET['testExtraction']);
$commande=stripslashes($_GET['site_url']).$testExtraction;
if($commande)
{
	$user_agent = getenv("HTTP_USER_AGENT");
	
	if(strpos($user_agent, "Mac") === FALSE)
	{
		
		$commande=str_replace('^','^^',$commande);
		$commande=str_replace('&','^&',$commande);
		$commande=str_replace('?','^?',$commande);
		$commande=str_replace('<','^<',$commande);
		$commande=str_replace('>','^>',$commande);
		$commande=str_replace('|','^|',$commande);
		$commande=str_replace('`','^`',$commande);
		$commande=str_replace('%','%%',$commande);
		$commande=str_replace(',','^,',$commande);
		$commande=str_replace(';','^;',$commande);
		$commande=str_replace('=','^=',$commande);
		$commande=str_replace('(','^(',$commande);
		$commande=str_replace(')','^)',$commande);
		//$commande=str_replace('!','^^!',$commande);
		$commande=str_replace('"','\"',$commande);
		//$commande=str_replace('\\','\\\\',$commande); //les backslashes posent encore problème
		//$commande=str_replace('[','\[',$commande);
		//$commande=str_replace(']','\]',$commande);
		//$commande=str_replace('?','\"',$commande);
		//$commande=str_replace('.','\.',$commande);
		//$commande=str_replace('*','\*',$commande);
		//$commande=str_replace('?','\?',$commande);
	}
}

echo 'java -jar run.jar '.$commande.'
pause'; // -Xmx3024k

die;