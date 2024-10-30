<?php

/**
*
* Plugin Name: Catalog Importer, Scraper & Crawler 
* Plugin URI: https://www.storeinterfacer.com/intelligent-importer.php
* Description: Allows you to import external content interpreting pages of websites, CVS, XML, databases, softwares or others with scripts
* Version: 5.1.3
* Author: idIA Tech
* Author URI: https://www.storeinterfacer.com
* License: GPLv2
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Megaimporter {

	public $licence='diagpowershop';
	/**
	 * WooCommerce version.
	 *
	 * @var string
	 */
	public function __construct() {

		include_once( 'includes/class-install.php' );

		add_filter('wp_title', 'megaimporter_modify_page_title', 20) ;
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		add_action('admin_menu', array($this, 'megaimporter_add_admin_menu'));
		
		add_action( 'init',  array($this, 'megaimporter_communication') );
	}
	
	function megaimporter_communication()
	{
		global $wpdb;

		if(isset($_GET['megaimporter_communication'])){
			$dir = plugin_dir_path( __FILE__ );
			include($dir."communication.php");
		}
	}
	
	public function activation() {

		global $wpdb;
		

		if ( ! defined( 'MI_INSTALLING' ) ) {
			define( 'MI_INSTALLING', true );
		}
		
		if(!get_option('megaimporter_clefacces')) update_option('megaimporter_clefacces',mt_rand(1,999999999));
		
		$collate = $wpdb->get_charset_collate();
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}megaimporter_concurrents` (
					  `id_concurrents` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `nom` VARCHAR( 40 ),
					  `url` TEXT,
					  `ordre` DECIMAL(4,1),
					  `httpAgent` TEXT,
					  `profondeur` INT(3),
					  `delai` INT(7),
					  `maxUrl` INT(10),
					  `regexUrlBloquer` TEXT,
					  `codeGroovy` LONGTEXT,
					  `codeLiens` LONGTEXT,
					  `codeFinal` LONGTEXT,
					  `nb_taches` INT(4),
					  `suivi_cookies` INT(1) DEFAULT '2',
					  `edition_libre` INT(1),
					  `actif` INT(1),
					  
					  PRIMARY KEY (`id_concurrents`)
					) $collate;");
					

	}


	function megaimporter_modify_page_title($title) {
		return $title . ' | Catalog Importer, Scraper & Crawler' ;
	}
	


	public function megaimporter_add_admin_menu()
	{
		add_submenu_page('tools.php', 'Catalog Importer, Scraper & Crawler', 'Catalog Importer, Scraper & Crawler', 'manage_options', 'megaimporter', array($this, 'megaimporter_menu_html'));
	}
	
	public function megaimporter_admin_url()
    {
		return add_query_arg( array( 'page' => 'megaimporter'), admin_url('tools.php')).'&page2=';
	}
	
	
	public function megaimporter_menu_html()
    {
		global $wpdb;
		
		$_POST = stripslashes_deep( $_POST );

		$page2=$_GET['page2'];

		if(!$page2)
		{
        ?>
        
<!-- MegaImporter -->
<form action="#applet" method="post">
 <?php wp_nonce_field('megaimporter'); ?>
  <fieldset>
	<h1>Analysis</h1>
	<p><a href="<?php echo $this->megaimporter_admin_url(); ?>concurrents" class="button">Manage scripts</a><br /><br />

<a href="<?php echo add_query_arg( array( 'file' => 'megaimporter/fonctions.php', 'plugin'=>'megaimporter/megaimporter.php'), admin_url('plugin-editor.php')); ?>" class="button">Manage PHP function for interting or updating</a>
    
    <a style="float:right" href="<?php echo $this->megaimporter_admin_url(); ?>contact" class="button">Request assistance by idIA Tech on this part</a>
    <a style="float:right;margin-left:10px; margin-right:10px" href="<?php echo plugins_url( 'Manuel.pdf', __FILE__ ); ?>" class="button">User Guide</a><br>
OR contact us at contact@idia-tech.com
    
    






    
    </p>
	<h3> Steps of the analysis	:</h3>
	<ol style="list-style:decimal; margin-left:50px"><li>Browse sites and extract information of products</li>
	  <li>Sending queries for inserting or updating</li>
	  <li>Execution PHP functions for inserting or updating</li>
    </ol>
	<p>&nbsp;</p>
	<h3>Run the analysis</h3>


    <p>Note 1: The analysis can be long. It is recommended to execute it during the night.
      <br />
      Note 2: If you are not a professionnal developper, it is more sure to save your shop before the analysis.
      
      <br class="clear" />


<br />

<?php
$nomConcurrent=false;
$testExtraction='';
if(@$_GET['testExtraction'])
{
	$testExtraction=str_replace('---PLUS----','+',@$_GET['testExtraction']);
	preg_match("#^-?([0-9]+)__________#isU",@$_GET['testExtraction'],$subs);
	if($subs[1])
	{
		$id_concurrent=$subs[1];
		$concurrent = $wpdb->get_row('SELECT *
				FROM `'.$wpdb->prefix.'megaimporter_concurrents`
				WHERE `id_concurrents` = '.$id_concurrent
			);
		if($concurrent) $nomConcurrent = $concurrent->nom;
	}
}
$nom_wordpress=get_bloginfo('name');
?>

<strong>Recommanded method</strong> 
<br />
1) <a href="https://www.idia-tech.com/declinaisons-crawler/installer_intelligent_importer.exe">Install Intelligent Importer</a><br />
2) Execute your  <strong><a href="<?php echo plugins_url( 'grl.php', __FILE__ ).'?site_url='.urlencode(get_home_url().'/?megaimporter_communication=1&clef='.get_option('megaimporter_clefacces')).'&nom_concurrent='.urlencode($nomConcurrent).'&nom_wordpress='.urlencode($nom_wordpress).'&testExtraction='.urlencode(str_replace('\\','\\\\',@$_GET['testExtraction'])); ?>">personnal launcher <?php if(@$_GET['testExtraction']) echo 'for the test'; else echo 'for a general crawling'; ?></a></strong> <br />
<br />

<strong>Alternative method</strong> 

<!--OR use this alternative method :--><br />
1) Put this 2 files in the same folder : <a href="https://www.idia-tech.com/grimport-crawler/run.jar">run.jar</a>, <a href="<?php 
echo plugins_url( 'bat.php', __FILE__ ).'?site_url='.urlencode(get_home_url().'/?megaimporter_communication=1&clef='.get_option('megaimporter_clefacces')).'&testExtraction='.urlencode(str_replace('\\','\\\\',@$_GET['testExtraction'])); ?>">LAUNCH.bat</a><br />
2) Double click on LAUNCH.bat
 <div align="center" style="vertical-align:middle"><span style="font-size: 18px; font-weight: bold;">Produced by  <a style="text-decoration:underline" href="https://www.storeinterfacer.com" target="_blank">idIA Tech</a></span> <a href="https://www.storeinterfacer.com" target="_blank"><img src="<?php echo plugins_url( 'assets/images/societe.png', __FILE__ ); ?>" border="0" align="middle" style="vertical-align:middle" /></a>
</div>   


</p><br>
To automatize, use this CRON command :<br>
java -jar <?php echo plugin_dir_path( __FILE__ ); ?>applet/run.jar <?php echo get_home_url().'/?megaimporter_communication=1&clef='.get_option('megaimporter_clefacces').' '.
@$_GET['testExtraction'];
?>
  </fieldset>
</form>
<!-- MegaImporter -->
        <?php
		}
		elseif($page2=='bat')
		{
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
			if(@$_GET['testExtraction'])
			{
				$user_agent = getenv("HTTP_USER_AGENT");
				
				if(strpos($user_agent, "Mac") === FALSE)
				{
					$testExtraction=' '.str_replace('---PLUS----','+',@$_GET['testExtraction']);
					$testExtraction=str_replace('^','^^',$testExtraction);
					$testExtraction=str_replace('&','^&',$testExtraction);
					$testExtraction=str_replace('<','^<',$testExtraction);
					$testExtraction=str_replace('>','^>',$testExtraction);
					$testExtraction=str_replace('|','^|',$testExtraction);
					$testExtraction=str_replace('`','^`',$testExtraction);
					$testExtraction=str_replace('%','%%',$testExtraction);
					$testExtraction=str_replace(',','^,',$testExtraction);
					$testExtraction=str_replace(';','^;',$testExtraction);
					$testExtraction=str_replace('=','^=',$testExtraction);
					$testExtraction=str_replace('(','^(',$testExtraction);
					$testExtraction=str_replace(')','^)',$testExtraction);
					//$testExtraction=str_replace('!','^^!',$testExtraction);
					$testExtraction=str_replace('"','\"',$testExtraction);
					//$testExtraction=str_replace('\\','\\\\',$testExtraction); //les backslashes posent encore problème
					//$testExtraction=str_replace('[','\[',$testExtraction);
					//$testExtraction=str_replace(']','\]',$testExtraction);
					//$testExtraction=str_replace('?','\"',$testExtraction);
					//$testExtraction=str_replace('.','\.',$testExtraction);
					//$testExtraction=str_replace('*','\*',$testExtraction);
					//$testExtraction=str_replace('?','\?',$testExtraction);
				}
			}

echo 'java -jar run.jar '.get_home_url().'/?megaimporter_communication=1&clef='.get_option('megaimporter_clefacces').' '.
$testExtraction.'
pause'; // -Xmx3024k

			die;

		}
		elseif($page2=='effacerBDD')
		{
			$wpdb->query('DROP TABLE `'.$wpdb->prefix.'megaimporter_concurrents`');
		}
		elseif($page2=='fonctions')
		{
			if($_POST['submit'])
			{
				check_admin_referer( 'megaimporter' );
				$code=$_POST['code'];
				$code=($code);
				
				$fp = fopen(plugin_dir_path( __FILE__ ).'fonctions.php', 'w');
				fwrite($fp, "<?php
".str_replace("<?php
",'',$code));
				fclose($fp);
			}
			?>

<!-- megaimporter -->
<?php if(is_writable(plugin_dir_path( __FILE__ ).'fonctions.php'))
{
	?>
<div class="error settings-error notice is-dismissible">
   <p>The file <?php echo plugin_dir_url( __FILE__ ); ?>fonctions.php is not writable, make a CHMOD 777 on this file with FileZilla for example before change the following field</p>
</div>
<?php } ?>


<form action="" method="post">
<?php wp_nonce_field('megaimporter'); ?>
  <fieldset>
	<h1>Manage PHP functions</h1>

    <a href="<?php echo $this->megaimporter_admin_url(); ?>">&lt;&lt; Back to analysis</a><br class="clear"/><br />

<textarea name="code" cols="80" rows="30" style="width:100%" id="code"><?php
$codeFonctions=file_get_contents(plugin_dir_path( __FILE__ ).'fonctions.php');
$codeFonctions=substr($codeFonctions,6);
echo htmlspecialchars($codeFonctions); 
?></textarea>
		  <br />
    <br />
<a target="_blank" href="https://www.storeinterfacer.com/megaimporter_script.php">Ask idIA Tech to program your PHP functions</a>				<br class="clear"/>
	<br class="clear"/>
	    <input type="submit" name="submit" id="submit" value="Submit" class="button" style="margin-left:250px" />
    <br />
	<br /><br />

<h3>Functions aviable (* = facultative) :</h3>

<div id="resume">
</div>
<script>
function resume()
{
	var trouver=/(\/\*([^*]+?)\*\/\s*)?function\s+([a-zA-Z0-9_]+\s*\([^\)]*\))/gi
	var facultatif=/(\$[a-zA-Z0-9_]+)\s*=[^,]+(,|\))/gi
	var champ=jQuery('#code').val()
	
	html=""
	
	while(match = trouver.exec(champ))
	{
		explication=match[2]
		fonction=match[3]
		fonction=fonction.replace(facultatif,"$1*$2");
		fonction=fonction.replace(/,/gi,", ");
		
		html+="<strong>"+fonction+"</strong>"
		if(explication) html+=" : "+explication
		html+="<br><br>"
	}
	
	jQuery('#resume').html(html)
	
}

jQuery( document ).ready(function() {
	resume();
	
	setInterval(resume, 5000)
});
</script>




  </fieldset>
</form>
<!-- megaimporter -->
            <?php
		}
		elseif($page2=='concurrents' || $page2=='supprConcurrent')
		{
			
			if($page2=='supprConcurrent')
			{
				$wpdb->query('DELETE FROM `'.$wpdb->prefix.'megaimporter_concurrents`
				WHERE id_concurrents='.(int)$_GET['id']);
			}
			if(isset($_GET['actif']))
			{
				$wpdb->query('UPDATE `'.$wpdb->prefix.'megaimporter_concurrents`
				SET actif='.((int)$_GET['actif']).'
				WHERE id_concurrents='.(int)$_GET['id']);
			}
			
			
			$concurrents = $wpdb->get_results('
			SELECT *
			FROM `'.$wpdb->prefix.'megaimporter_concurrents`
			WHERE 1=1');
	

		?>

<!-- megaimporter -->
<form action="" method="post">
<?php wp_nonce_field('megaimporter'); ?>
  <fieldset>
	<h1>External scripts</h1>
	<p><a href="<?php echo $this->megaimporter_admin_url(); ?>">&lt;&lt; Back to analysis</a><br /><br>

<a href="<?php echo $this->megaimporter_admin_url(); ?>ajConcurrent" class="button">	Add a script</a></p>
    
    
<?php
	if($concurrents)
	{ ?>
        <table class="wp-list-table widefat fixed striped posts" cellpadding="0" cellspacing="0">
                        <thead><tr>
                            <th style="width: 5.5em;">ID</th>
                            <th>Name</th>
                            <th>URL</th>
                            <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
    <?php foreach($concurrents as $concurrent) { ?>
    
    <tr>
        <td><?php echo esc_html($concurrent->id_concurrents); ?></td>
        <td><?php echo esc_html($concurrent->nom); ?></td>
        <td><?php echo esc_html($concurrent->url); ?></td>
        <td><a href="<?php echo $this->megaimporter_admin_url(); ?>ajConcurrent&id=<?php echo esc_html($concurrent->id_concurrents); ?>">Edit</a> - <a href="<?php echo $this->megaimporter_admin_url(); ?>supprConcurrent&id=<?php echo esc_html($concurrent->id_concurrents); ?>" onclick="return confirm('Are you sure to delete ?')">Delete</a> - 
        <?php if($concurrent->actif) { ?>
            <a href="<?php echo $this->megaimporter_admin_url(); ?>concurrents&actif=0&id=<?php echo esc_html($concurrent->id_concurrents); ?>"><img src="<?php echo plugins_url( 'assets/images/enabled.gif', __FILE__ ); ?>" title="Catalog enabled for analysis" /></a>
        <?php } else { ?>
            <a href="<?php echo $this->megaimporter_admin_url(); ?>concurrents&actif=1&id=<?php echo esc_html($concurrent->id_concurrents); ?>"><img src="<?php echo plugins_url( 'assets/images/disabled.gif', __FILE__ ); ?>" title="Catalog disabled for analysis" /></a>
        <?php } ?>
        </td>
    </tr>
    
 <?php } ?>
    
    </tbody></table>
 <?php } ?>
</fieldset>
</form>
<!-- megaimporter -->
        <?php	
		}
		elseif($page2=='ajConcurrent')
		{
			$id=(int)$_GET['id'];
			
			
			if(!$id)
			{
				$wpdb->insert(
					$wpdb->prefix . 'megaimporter_concurrents',
					array(
						'nom'=>'New script',
						'ordre'=>100,
						'httpAgent'=>'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
						'profondeur'=>7,
						'delai'=>100,
						'maxUrl'=>100000,
						'regexUrlBloquer'=>'',
						'actif'=>1,
						'nb_taches'=>1,
						'suivi_cookies'=>2,
						'edition_libre'=>1,
						'urls_sav_progression'=>1000,
						'id_unique'=>mt_rand(1,999999),
					)
				);
				$id=$wpdb->insert_id;
				
				echo '<SCRIPT LANGUAGE="JavaScript">
document.location.href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&id='.$id.'"
</SCRIPT>';
	die;
			}
			else
			{
				if($_POST['submit'])
				{
					check_admin_referer( 'megaimporter' );
					
					$codeGroovy=$this->megaimporter_escapeCode('codeGroovy');
					$codeLiens=$this->megaimporter_escapeCode('codeLiens');
					$codeFinal=$this->megaimporter_escapeCode('codeFinal');
					
					/*$_POST['codeGroovy'];
					if (get_magic_quotes_gpc()) $codeGroovy=stripslashes($codeGroovy);
					$codeGroovy=str_replace('\\','\\\\',$codeGroovy);
					$codeGroovy=str_replace("'","\'",$codeGroovy);
					
					$codeLiens=$_POST['codeLiens'];
					if (get_magic_quotes_gpc()) $codeLiens=stripslashes($codeLiens);
					$codeLiens=str_replace('\\','\\\\',$codeLiens);
					$codeLiens=str_replace("'","\'",$codeLiens);*/
					
					$wpdb->update(
						$wpdb->prefix . 'megaimporter_concurrents',
						array(
							'nom'=>sanitize_text_field($_POST['nom']),
							'url'=> sanitize_text_field((strpos($_POST['url'],'http://')===FALSE && strpos($_POST['url'],'https://')===FALSE ? 'http://':'')  .$_POST['url']),
							'ordre'=>(int) $_POST['ordre'],
							'httpAgent'=>sanitize_text_field($_POST['httpAgent']),
							'profondeur'=>(int) $_POST['profondeur'],
							'delai'=>(int)$_POST['delai'],
							'maxUrl'=>sanitize_text_field($_POST['maxUrl']),
							'regexUrlBloquer'=>$this->megaimporter_escapeCode('regexUrlBloquer'),
							'codeGroovy'=>$codeGroovy,
							'codeLiens'=>$codeLiens,
							'codeFinal'=>$codeFinal,
							'nb_taches'=>(int) $_POST['nb_taches'],
							'suivi_cookies'=>(int) $_POST['suivi_cookies'],
							'urls_sav_progression'=>(int) $_POST['urls_sav_progression'],
						), array('id_concurrents'=>$id)
					);
				}
			}

			
			$concurrent = $wpdb->get_row('SELECT *
				FROM `'.$wpdb->prefix.'megaimporter_concurrents`
				WHERE `id_concurrents` = '.$id
			);

			?>
            
<!-- megaimporter -->
<form action="" method="post">
<?php wp_nonce_field('megaimporter'); ?>
	<h1>Add a script</h1>

    <a href="<?php echo $this->megaimporter_admin_url(); ?>concurrents">&lt;&lt; Back to scripts</a>
    <h2 style="padding-left:150px">General</h2>

	<table class="form-table">
<tr>
<th scope="row">Name :</th>
<td>
			<input type="text" name="nom" value="<?php echo esc_attr($concurrent->nom); ?>" size="45" id="nom" />
</td></tr>		



<tr>
<th scope="row">URL adress :</th>
<td><input type="text" name="url" value="<?php echo esc_attr($concurrent->url); ?>" size="120" id="url" />
</td></tr>	


<tr>
<th scope="row">Order in Analysis :</th>
<td><input type="text" name="ordre" value="<?php echo esc_attr($concurrent->ordre); ?>" size="3" id="ordre" />
</td></tr>	

</table>


				<br class="clear"/>
		

<h2 style="padding-left:150px">Find information</h2>

<a target="_blank" href="https://www.storeinterfacer.com/megaimporter_script.php" style="margin-left:100px; text-decoration:underline">Do you want a specialist of idIA Tech configure your script?</a>

				<br class="clear"/>
				<br class="clear"/>



	<table class="form-table">
	


<tr style="border-bottom:1px solid #CCC">
<th scope="row">Regular expression for URLs to avoid :</th>
<td><input type="text" name="regexUrlBloquer" value="<?php echo esc_attr($concurrent->regexUrlBloquer); ?>" size="120" id="regexPrix" />
			</td>

<?php if(true) { ?>



</tr>	


<tr style="border-bottom:1px solid #CCC">
<th scope="row">   Write the Groovy script to extract information from each web page :<br />
    <a href="http://www.groovy-lang.org/documentation.html" target="_blank">Groovy Documentation</a>
		  </th>
<td>  <textarea name="codeGroovy" cols="80" rows="6" id="codeGroovy"><?php echo htmlspecialchars($concurrent->codeGroovy); ?></textarea>
			<br />
    Input variables  : code = source code of the page, urlPage = URL of the page<br />
Functions aviables : <br />
display(message[string]) = display a variable in the log<br />
function(function name[string],arguments[array]) = execute a php function referenced on your shop<br />
php(command[string]) = execute a php code (careful with ;)<br />
selectInCode(cssSelector[string],codePortion[string|optionnal],selection[string|optionnal|default:innerHTML]) = select someting in code using a css selector, by default on all the source code but you can indicate a portion of code, the selection correspond to what you want it return : innerHTML, outerHTML, text (the text of the element without tag), object (return the Elements Jsoup object) or else the name of the attribute in the tag. Note:if codePortion=true, the code is utf8-decoded<br />

<a href="#" onclick="jQuery('#autres').toggle(); return false">Other functions</a>

<div id="autres" style="display:none">
functionNow(function name[string],arguments[array]) = execute now a php function referenced on your shop and return its value<br />
phpNow(command[string]) = execute now a php code (careful with ;) and return what is printed<br />

setCommandStackSize(size[int]) = php commands are sent grouped to your server from a certain number, you can define this number with this funtion (default 50)<br />
sendNowCommandsInStack() = Force immediately sending of commands and return which was written on the server side with the functions like echo<br />
blockCommandSending() = Prohibits the sending of commands for the moment<br />
allowCommandSending() = Allows the sending of commands<br />

getAttribute(idCombinaition[int],search[string],byProximity[bool],returnList[bool]) = Get the id of the searched attribute by its name. If byProximity is true, we use a AI algorithm to order by textual proximity. If returnList is true, the list of correspondance are returned, else it is the closer correspondance or the exact correspondance (-1 if nothing found).<br />
getCombination(search[string],byProximity[bool],returnList[bool]) = Idem with getAttribute for combinations .<br />
getCategory(search[string],usePath[bool],byProximity[bool],returnList[bool]) = Idem with getAttribute for categories. If usePath is true, paths of categories are used for comparaisons.<br />
getSupplier(search[string],byProximity[bool],returnList[bool]) = Idem with getAttribute for suppliers .<br />
getManufacturer(search[string],byProximity[bool],returnList[bool]) = Idem with getAttribute for manufacturers .<br />
getFeatureValue(idFeature[int],search[string],byProximity[bool],returnList[bool]) = Idem with getAttribute for feature values .<br />
getFeature(search[string],byProximity[bool],returnList[bool]) = Idem with getAttribute for features .<br />
getOrderAI(search[string],propositions[associative string array toReturn => toCompare],byProximity[bool],returnList[bool]) = Idem with getAttribute for any list of string.<br />
activeFlexibleComparaison(trueOrFalse[bool]) = Set configuration to ignore case, accents and special caracters in functions of recuperation like getAttribute<br />

getAttributesOfCombination(idCombinaition[int]) = Return an associative array (idAttribute => attributeValue) of attributes of a combination<br />
getValuesOfFeature(idFeature[int]) = Return an associative array (idFeatureValue => value) of values of a feature<br />

getAttributes() = Get an associative list of attributes (id => name).<br />
getCombinations() = Get an associative list of combinations (id => name).<br />
getAttributesToCombinations() = Get an associative list between attributes ids and combinations ids (idAttribute => idCombinations).<br />
getCategories() = Get an associative list of categories names (id => name).<br />
getCategoriesPaths() = Get an associative list of categories paths (id => path).<br />
getSuppliers() = Get an associative list of suppliers (id => name).<br />
getManufacturers() = Get an associative list of manufacturers (id => name).<br />
getFeatureValues() = Get an associative list of feature values (id => name).<br />
getFeatures() = Get an associative list of feature (id => name).<br />
getFValueToFeature() = Get an associative list between feacture value ids and feature ids (idFValue => idFeature).<br />

updateCombinations(id_lang[int]) = Update information on combinations and attributes. If id_lang is 0, the default language is used.<br />
updateCategories(id_lang[int]) = Update information on categories. If id_lang is 0, the default language is used.<br />
updateSuppliers(id_lang[int]) = Update information on suppliers. If id_lang is 0, the default language is used.<br />
updateManufacturers(id_lang[int]) = Update information on manufacturers. If id_lang is 0, the default language is used.<br />
updateFeatures(id_lang[int]) = Update information on features. If id_lang is 0, the default language is used.<br />

post(page url[string],arguments[associative array]) = Send a post request<br />
setCookie(page url[string],arguments[associative array|optionnal],frequence[int|optionnal]) = Save the cookie of the page for a use in the post function<br />
standardizeText(text[string]) = Replace all strange and non standard caracters<br />
htmlToPrice(html[string],nbOfDecimal[int|optionnal]) = Return a price (float) in a html code<br />
stripTags(html[string]) = Return a text without html tags<br />

generateCombinations(ids_attributes_of_combination1[array],ids_attributes_of_combination2[array],...) = Return an array of arrays with attributes combinated<br />
translate(text[string],from[string],to[string]) = Return a text translated with Bing Translate (code : nothing in from=Auto-detection, en=English, fr=French, ar=Arabic, sp=Spanish, de=German, it=Italian, pt=Portuguese, ru=Russian, zh=Chinese<br />


</div>

		<br />
	<a target="_blank" href="https://www.storeinterfacer.com/megaimporter_script.php">Ask idIA Tech to program the script</a>
    </td>




</tr>	


<tr style="border-bottom:1px solid #CCC">
<th scope="row">
    Write the initial Groovy script to create the list of links for example :<br />
    <a href="http://www.groovy-lang.org/documentation.html" target="_blank">Groovy Documentation</a></th>
<td>  <textarea name="codeLiens" cols="80" rows="6" id="codeLiens"><?php echo htmlspecialchars($concurrent->codeLiens); ?></textarea>
			<br />
    Input and output variable  : links = array of links<br />
	<a target="_blank" href="https://www.storeinterfacer.com/megaimporter_script.php">Ask idIA Tech to program the script</a></div>
				<br class="clear"/>
		
		
</td>



   
</tr>	


<tr>
<th scope="row">Write the final Groovy script :<br />
    <a href="http://www.groovy-lang.org/documentation.html" target="_blank">Groovy Documentation</a></th>
<td>     <textarea name="codeFinal" cols="80" rows="6" id="codeFinal"><?php echo htmlspecialchars($concurrent->codeFinal); ?></textarea>
			<br />
	<a target="_blank" href="https://www.storeinterfacer.com/megaimporter_script.php">Ask idIA Tech to program the script</a>
</td></tr>	
<?php } ?>
</table>
				<br class="clear"/>
				<br class="clear"/>

<h2 style="padding-left:150px; display:inline">Technical parameters for the crawler</h2>
&nbsp;&nbsp;<span style="color: #666">If you do not know what to put, keep the default settings</span>
				<br class="clear"/>
				<br class="clear"/>
	<table class="form-table">
<tr>
<th scope="row">HTTP-User-Agent :</th>
<td><input type="text" name="httpAgent" value="<?php echo esc_attr($concurrent->httpAgent); ?>" size="120" id="httpAgent" />
 <br />
		  Identification of the browser to avoid Apache filtring
</td></tr>	


<tr>
<th scope="row">Deepness :</th>
<td>	<input type="text" name="profondeur" value="<?php echo esc_attr($concurrent->profondeur); ?>" size="2" id="profondeur" />
		    <br />
For exemple, if deepness is 1, we crawl the page you have indicated and pages where there is a link on this page, but no more. If it is 2, we crawl pages where there is a link of all pages with the deepness 1
</td></tr>	


<tr>
<th scope="row">Delay between requests :</th>
<td>	<input type="text" name="delai" value="<?php echo esc_attr($concurrent->delai); ?>" size="4" />
      <br />
In milliseconds. To not flood target servers
</td></tr>	


<tr>
<th scope="row">Max URL to visit :</th>
<td>	<input type="text" name="maxUrl" value="<?php echo esc_attr($concurrent->maxUrl); ?>" size="9" id="maxUrl" />
</td></tr>
				<br class="clear"/>
		

<tr>
<th scope="row">Number of task in the crawler :</th>
<td>	<input type="text" name="nb_taches" value="<?php echo esc_attr($concurrent->nb_taches); ?>" size="9" id="nb_taches" />
</td></tr>
				<br class="clear"/>
		


<tr>
<th scope="row">Make a progress backup every X urls visited (0=no automatic saving) :</th>
<td>	<input type="text" name="urls_sav_progression" value="<?php echo esc_attr($concurrent->urls_sav_progression); ?>" size="9" id="urls_sav_progression" />
</td></tr>
				<br class="clear"/>
		


<tr>
<th scope="row">Cookie tracking mode :</th>
<td>	
<label>
		        <input type="radio" name="suivi_cookies" value="0" id="suivi_cookies_0" <?php if($concurrent->suivi_cookies==0) echo' checked="checked"'; ?> />
		        No tracking</label>
		      <br />
		      <label>
		        <input type="radio" name="suivi_cookies" value="1" id="suivi_cookies_1" <?php if($concurrent->suivi_cookies==0) echo' checked="checked"'; ?> />
		        Tracking the cookie on the browsing of pages by crawling</label>
		      <br />
		      <label>
		        <input name="suivi_cookies" type="radio" id="suivi_cookies_2" value="2" <?php if($concurrent->suivi_cookies==0) echo' checked="checked"'; ?> />
		        Cookie tracking on crawling and HTTP functions (post, getPage, etc.)</label>
		      <br />
		      <label>
		        <input type="radio" name="suivi_cookies" value="3" id="suivi_cookies_3" <?php if($concurrent->suivi_cookies==0) echo' checked="checked"'; ?> />
		        Cookie tracking on crawling and HTTP functions as well as HTTP errors</label>
		      <br />


</td></tr></table>
				<br class="clear"/>
		

		



    <input type="submit" name="submit" id="submit" value="Submit" class="button" style="margin-left:250px" />
    <br />
    <br />
    
<script>
function replaceShell(txt)
{
	return txt
.replace(/\\/gi,'\\\\')
.replace(/\+/gi,'---PLUS----')

}
</script>
    <a class="button" target="_blank" onclick="page=prompt('Page of the webiste for the test'); if(!page) return false; else { execution=confirm('Do you want execute commands in test ?'); window.location.href = '<?php echo add_query_arg( array( 'page' => 'megaimporter'), megaimporter_admin_url('tools.php')); ?>&testExtraction='+escape((execution?'-':'')+'<?php echo (int)$_GET['id']; ?>__________'+replaceShell(page))+'#applet' ; return false; }" href="<?php echo $this->megaimporter_admin_url(); ?>" style="margin-left:250px">Test with a page of the script
    </a>&nbsp;&nbsp; The script configuration must be saved before
</form>
<!-- megaimporter -->
        <?php	
		}
		elseif($page2=='contact')
		{
			?>

<!-- megaimporter -->
  <fieldset>
	<h1>Contact us </h1>
	<p>
Email : <a href="mailto:contact@idia-tech.com" style="font-size: 18px">contact@idia-tech.com</a></p>
<p><br />
idIA Tech can handle the configuration of the Analysis section</p>
	<p>&nbsp;</p>
  </fieldset>
<!-- megaimporter -->
            <?php
		}
    }
	
	public function megaimporter_escapeCode($variable)
	{
		$codeGroovy=$_POST[$variable];
		//$codeGroovy=stripslashes($codeGroovy);
		//$codeGroovy=str_replace('\\','\\\\',$codeGroovy);
		//$codeGroovy=str_replace("'","\'",$codeGroovy);
		
		return $codeGroovy;
	}
}


new Megaimporter();