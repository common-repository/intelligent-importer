<?php
/**
 * @author   idIA Tech
 * @category Admin
 * @package  Megaimporter/Classes
 * @version  2.4.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Megaimporter_Install Class.
 */
class Megaimporter_Install {
	/**
	 * Install MI.
	 */
	public static function install() {
		global $wpdb;

		if ( ! defined( 'MI_INSTALLING' ) ) {
			define( 'MI_INSTALLING', true );
		}
		
		update_option('megaimporter_clefacces',mt_rand(1,999999999));
		
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
					  `id_unique` INT(11),
					  `suivi_cookies` INT(1) DEFAULT \'2\',
					  `urls_sav_progression` INT(10),
					  `edition_libre` INT(1),
					  `actif` INT(1),
					  
					  PRIMARY KEY (`id_concurrents`)
					) $collate;");

	}

}

