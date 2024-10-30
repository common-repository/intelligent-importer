<?php

$no_notice=true;
$no_error_display=false;
$si_pas_chemin_pas_categorie=false;
$default_max_time_execution=1000; //seconds
$utf8_decode=false;


@header('Content-type: text/plain; charset=utf-8');

@set_time_limit(0);
@ini_set('display_errors', 'on');
@ini_set('post_max_size', '32M');
@ini_set('post_max_size', '32M');
@ini_set("memory_limit","512M");
error_reporting(0);

if(get_option('megaimporter_clefacces')!=@$_GET['clef']) die('Erreur k1');

function utf8_decode2($string)
{
	global $utf8_decode;
	if($utf8_decode) return utf8_decode($string);
	return $string;
}

function utf8_encode2($string)
{
	
	global $utf8_decode;
	if($utf8_decode) return utf8_encode($string);
	return $string;
}

function findDebut($txt,$aTrouver)
{
	if(  preg_match("#^".$aTrouver."([0-9]+)$#isU",$txt) )
	{
		return true;
	}
	return false;
}
function escapeCode($variable)
{
	$codeGroovy=@$_POST[$variable];
	if (true) $codeGroovy=stripslashes($codeGroovy);
	$codeGroovy=str_replace('\\','\\\\',$codeGroovy);
	$codeGroovy=str_replace("'","\'",$codeGroovy);
	
	return $codeGroovy;
}



if(@$_POST['getConcurrents']) //avoir les concurrents
{
	$concurrents = $wpdb->get_results('
	SELECT *
	FROM `'.$wpdb->prefix.'megaimporter_concurrents`
	WHERE 1=1'.(@$_POST['actifs']?' AND actif=1':'').' ORDER BY ordre' ); // restriction Silver
	
	foreach($concurrents as $c)
	{
		/*if($c['reference_developpeur'])
		{
			$script_externe=unserialize(base64_decode(file_get_contents('https://www.storeinterfacer.com/script_icim.php?ref='.$c['reference_developpeur'])));
			if($script_externe)
			{
				$c['codeGroovy']=$script_externe['codeGroovy'];
				$c['codeLiens']=$script_externe['codeLiens'];
				$c['codeFinal']=$script_externe['codeFinal'];
				if(!$c['regexUrlBloquer']) $c['regexUrlBloquer']=$script_externe['regexUrlBloquer'];
			}
		}*/
		
		echo utf8_decode2($c->id_concurrents."#t-#".$c->url."#t-#".$c->httpAgent."#t-#".$c->profondeur."#t-#".$c->delai."#t-#".$c->maxUrl."#t-#".$c->regexUrlBloquer."#t-#". str_replace("\r",'',str_replace("\n",'#$n#',$c->codeGroovy)) ."#t-#". str_replace("\r",'',str_replace("\n",'#$n#',$c->codeLiens))  ."#t-#". str_replace("\r",'',str_replace("\n",'#$n#',$c->codeFinal)) ."#t-#".$c->nom ."#t-#".$c->ordre ."#t-#".$c->actif ."#t-#".$c->nb_taches."#t-#".$c->suivi_cookies."#t-#".$c->urls_sav_progression ."#t-#".$c->id_unique
		."#t-#".".\n");
	}
}
elseif(@$_POST['getFonctionsDoc']) //avoir les concurrents
{
	$codeFonctions=file_get_contents(dirname(__FILE__).'/fonctions.php');
	$codeFonctions=substr($codeFonctions,6);
	
	
	preg_match_all("#(/\\*([^*]+?)\\*/\\s*)?function\\s+([a-zA-Z0-9_]+)\\s*\\(([^\\)]*)\\)#isU",$codeFonctions,$matches);
	$nb_fonctions=0;
	if($matches[0]) $nb_fonctions=count($matches[0]);
	for($i=0;$i<$nb_fonctions;$i++)
	{
		$explication=$matches[2][$i];
		$fonction=$matches[3][$i];
		$arguments=$matches[4][$i];
		$arguments=preg_replace("#(\\$[a-zA-Z0-9_]+)\\s*=[^,]+(,|$)#isU","_$1$2",trim($arguments));
		$arguments=str_replace('$','',$arguments);
		
		echo utf8_decode2($fonction ."#t-#". str_replace("\r",'',str_replace("\n",'',$arguments))  ."#t-#". str_replace("\r",'',str_replace("\n",'#$n#',$explication)) 
		."#t-#"."\n");
	}
}
elseif(@$_POST['setRegexUrlBloquer'] && @$_POST['id_concurrents'])
{
	$wpdb->query(
		'UPDATE '.$wpdb->prefix.'megaimporter_concurrents SET regexUrlBloquer=\''.$escapeCode('regexUrlBloquer').'\' WHERE id_concurrents = '.(int)@$_POST['id_concurrents']
	);
}
elseif(@$_POST['supprScript'] && @$_POST['id_concurrents'])
{
	$wpdb->query(
		'DELETE FROM '.$wpdb->prefix.'megaimporter_concurrents WHERE id_concurrents = '.(int)@$_POST['id_concurrents']
	);
}
elseif(@$_POST['getIDProduct'] && isset($_POST['id_product']))
{
	$id_product=false;
	if(preg_match("#^[0-9\s]+$#isU",@$_POST['id_product']))
	{
		 $product = wc_get_product_object_type( 'product' );
		 if($product) $id_product=$product->get_id();
	}
	
	if(!$id_product)
	{
		$id_product = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", @$_POST['id_product'] ) ); 
	}
	echo $id_product;
}
elseif(@$_POST['supprBDD'])
{
	$wpdb->query(
		'DROP TABLE '.$wpdb->prefix.'megaimporter_concurrents '
	);
}

elseif(@$_POST['setScript'] && isset($_POST['id_concurrents']))
{
	if(!(int)@$_POST['id_concurrents']) $is_existe=false;
	else $is_existe=$wpdb->get_var('SELECT id_concurrents FROM '.$wpdb->prefix.'megaimporter_concurrents  WHERE id_concurrents = '.(int)@$_POST['id_concurrents']);
	
	if(!$is_existe)
	{
		$wpdb->query('INSERT INTO `'.$wpdb->prefix.'megaimporter_concurrents`
			(url,httpAgent,profondeur,maxUrl,delai,regexUrlBloquer,nom,codeGroovy,codeLiens,codeFinal,edition_libre,ordre,actif,nb_taches,suivi_cookies) VALUES (\''.escapeCode('url').'\',\''.escapeCode('httpAgent').'\',\''.esc_sql(@$_POST['profondeur']).'\',\''.esc_sql(@$_POST['maxUrl']).'\',\''.esc_sql(@$_POST['delai']).'\',\''.escapeCode('regexUrlBloquer').'\',\''.escapeCode('nom').'\',\''.escapeCode('scriptGroovy').'\',\''.escapeCode('scriptLiens').'\',\''.escapeCode('scriptFinal').'\',1,\''.esc_sql(@$_POST['ordre']).'\',\''.esc_sql(@$_POST['actif']).'\',\''.esc_sql(@$_POST['nb_taches']).'\',\''.esc_sql(@$_POST['suivi_cookies']).'\')');
		echo $wpdb->insert_id;
	}
	else
	{
		$wpdb->query(
			'UPDATE '.$wpdb->prefix.'megaimporter_concurrents SET url=\''.escapeCode('url').'\',httpAgent=\''.escapeCode('httpAgent').'\',profondeur=\''.esc_sql(@$_POST['profondeur']).'\',maxUrl=\''.esc_sql(@$_POST['maxUrl']).'\',ordre=\''.esc_sql(@$_POST['ordre']).'\',delai=\''.esc_sql(@$_POST['delai']).'\',regexUrlBloquer=\''.escapeCode('regexUrlBloquer').'\',nom=\''.escapeCode('nom').'\',codeGroovy=\''.escapeCode('scriptGroovy').'\',codeLiens=\''.escapeCode('scriptLiens').'\',codeFinal=\''.escapeCode('scriptFinal').'\',actif=\''.esc_sql(@$_POST['actif']).'\',nb_taches=\''.esc_sql(@$_POST['nb_taches']).'\',suivi_cookies=\''.esc_sql(@$_POST['suivi_cookies']).'\' WHERE id_concurrents = '.(int)@$_POST['id_concurrents']
		);
	}
}
elseif(@$_POST['getStoreConfig']) //produits du catalogue
{
	echo utf8_decode2("OK"."\tWORDPRESS"."\t".get_option('woocommerce_version'));
}
elseif(@$_POST['getInfoClient']) //produits du catalogue
{
	/*include( __DIR__ . '/megaimporter.php');
	$module_instance=new Megaimporter();*/

	$contenu_wiki='';
	$contenu_wiki.=get_bloginfo('name')."\n\n";
	//$contenu_wiki.=$module_instance->licence."\n\n";
	$contenu_wiki.=get_bloginfo('admin_email')."\n\n";
	$contenu_wiki.=get_bloginfo('description');

	echo utf8_decode2($contenu_wiki);
}
elseif(@$_POST['getLienProduit']) //produits du catalogue
{
	
	
}
elseif(@$_POST['getLien']) //produits du catalogue
{
	
	
}
elseif(@$_POST['getMesProduits']) //produits du catalogue
{
	
	
}
elseif(@$_POST['setCommandes']) //execution commandes
{
	@ini_set('display_errors', 'on');
	if($no_error_display) error_reporting(0);
	elseif(!$no_notice) error_reporting(E_ALL | E_STRICT);
	else error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	$max_execution_time=@ini_get('max_execution_time');
	$default_max_time_execution=($max_execution_time==0 || $default_max_time_execution==0) ? 0 : max($max_execution_time,$default_max_time_execution);
	@set_time_limit($default_max_time_execution);
	@ini_set('max_execution_time', $default_max_time_execution);

	@ini_set('max_input_vars', '100000');
	@ini_set('suhosin.post.max_vars', '100000');
	@ini_set('suhosin.request.max_vars', '100000');

	$i=1;
	$commandeTotal='';
	while(@$_POST['commande'.$i])
	{
		$code=@$_POST['commande'.$i];
		$code=stripslashes(utf8_encode2($code)); //if (get_magic_quotes_gpc()) 
		
		$commandeTotal.=$code;
		
		$i++;
	}
	include( __DIR__ . '/fonctions.php');
	eval($commandeTotal);
}
elseif(@$_POST['setCommandesMulti']) //execution commandes en mode iteratif
{
	@ini_set('display_errors', 'on');
	if($no_error_display) error_reporting(0);
	elseif(!$no_notice) error_reporting(E_ALL | E_STRICT);
	else error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	$max_execution_time=@ini_get('max_execution_time');
	$default_max_time_execution=($max_execution_time==0 || $default_max_time_execution==0) ? 0 : max($max_execution_time,$default_max_time_execution);
	@set_time_limit($default_max_time_execution);
	@ini_set('max_execution_time', $default_max_time_execution);

	@ini_set('max_input_vars', '100000');
	@ini_set('suhosin.post.max_vars', '100000');
	@ini_set('suhosin.request.max_vars', '100000');


	$i=1;
	$retourAfbdgfgfgf=array();
	
	include(__DIR__ . '/fonctions.php');
	while(@$_POST['commande'.$i])
	{
		$code=@$_POST['commande'.$i];
		
		eval('$retourAfbdgfgfgf['.($i-1).'] = '.$code);
		
		$i++;
	}

	echo json_encode( $retourAfbdgfgfgf );
}
elseif(@$_POST['toogleDebugMode'])
{
	wp_debug_mode();
}
elseif(@$_POST['disableCache'])
{
	/*$statutCache=Configuration::get('PS_SMARTY_CACHE');
	Configuration::updateGlobalValue('megaimporter_SMARTY', $statutCache);
	Configuration::update('PS_SMARTY_CACHE',0);*/
}
elseif(@$_POST['retablirCache'])
{
	/*try {
		$statutCache=Configuration::get('megaimporter_SMARTY');
		Configuration::update('PS_SMARTY_CACHE',$statutCache);
	} catch(Exception $e) {}*/
}
elseif(@$_POST['getAttributes']) //attribus
{
	$taxonomies = get_terms(array(
		'hide_empty' => true, //can be 1, '1' too
		'include' => 'all', //empty string(''), false, 0 don't work, and return empty array
	));
	
	foreach($taxonomies as $taxonomie)
	{
		//print_r($taxonomie);
		echo utf8_decode2($taxonomie->term_id."\t".$taxonomie->name."\t".$taxonomie->taxonomy."\n");
	}
}
elseif(@$_POST['getAttributesGroups']) //declinaisons
{
	$taxonomies = get_taxonomies(array(), 'objects');
	foreach($taxonomies as $taxonomie)
	{
		//print_r($taxonomie);
		echo utf8_decode2($taxonomie->name."\t".$taxonomie->labels->singular_name."\n");
	}
}
elseif(@$_POST['getFeatureValues']) //feacture value
{
	
}
elseif(@$_POST['getFeatures']) //feature
{
	
}
elseif(@$_POST['getSuppliers']) //founrisseurs
{
	
}
elseif(@$_POST['getManufacturers']) //marque
{
	
}
elseif(@$_POST['getCategories']) //catégories
{
	$sql = '
	SELECT TT.`term_id`,
		  T.`name`,
		  TT.parent
	FROM `' . $wpdb->prefix . 'term_taxonomy` TT
	
		LEFT JOIN `' . $wpdb->prefix . 'terms` T
		ON  (
				T.`term_id` = TT.`term_id`
			)
			
	WHERE TT.taxonomy=\'product_cat\' '.
	(@$_POST['pack']?(' LIMIT '.($pack*1000).',1000') : '' );

	
	$result = $wpdb->get_results($sql);
	
	if(!$result) echo'AUCUN';
	
	
	if ($result) 
	{
		foreach ($result as $category) 
		{
			$chemin="";
			
			if(!$si_pas_chemin_pas_categorie || $chemin) echo utf8_decode2($category->term_id."\t".$category->name."\t".$chemin."\t".$category->parent."\n");
			
		}
	}
}



die;	