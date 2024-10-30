<?php
/******* /!\ COPYRIGHT /!\ - All right reserved in all countries - idIA Tech - contact@idia-tech.com *******/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/****************************
****GLOBAL CONFIGURATION********
****************************/
$force_tax_to_apply=false; //force to use the $tax_to_apply if there id a $id_tax_rules_group indicated in add_product
$alertMail_for_new_product=false;
$warning_if_no_attachment=false;
$email_admin='';
$decimal_percent_reduction=2; //max 4
$proxy_config=array(  //  array('host.com',88888,'login','pass'),
);




/****************************
****DEFAULT FUNCTIONS********
****************************/

/*
Add a post (article)
$categories = array of slugs,ids or names
$status=[publish,draft,pending,inherit,trash,auto-draft,future], if other stauts will be private and the text will be the password
$hierarchical_taxonomy = Array of tax ids
$non_hierarchical_taxonomy = array of ids or string of tax names 
$date format -> 0000-00-00 00:00:00
$type=[post,page,product,revision,attachment,editor,title,excerpt]
$post_parent=for a revision for example
*/
function add_post($name,$content=NULL,$categories=NULL,$date=NULL,$status='publish',$excerpt=NULL,$hierarchical_taxonomy=NULL,$non_hierarchical_taxonomy=NULL,$metas=NULL,$name_code=NULL,$id_author=NULL,$type='post',$post_parent_id=NULL)
{
	global $id_post;
	
	if($categories && !is_array($categories)) $categories=array($categories);
	if($hierarchical_taxonomy && !is_array($hierarchical_taxonomy)) $hierarchical_taxonomy=array($hierarchical_taxonomy);
	if($non_hierarchical_taxonomy && !is_array($non_hierarchical_taxonomy)) $non_hierarchical_taxonomy=array($non_hierarchical_taxonomy);
	
	$post_password=NULL;
	if(!in_array($status,array('publish','draft','pending','inherit','trash','auto-draft','future')))
	{
		$post_password=$status;
		$status='private';
	}
	
	$id = wp_insert_post(array(
		'post_title'   => trim(strip_tags($name)),
		'post_content' => $content,
		'post_excerpt' => $excerpt,
		'post_name' => trim(strip_tags($name_code)),
		'post_status'  => trim($status),
		'post_password'  => $post_password,
		'post_date'  => $date,
		'post_category'  => $categories,
		'post_parent'  => (int)$post_parent_id,
		'post_author'  => $id_author? (int)$id_author:get_current_user_id(),
		'tax_input'    => array(
			'hierarchical_tax'     => $hierarchical_taxonomy,
			'non_hierarchical_tax' => $non_hierarchical_taxonomy,
		),
		'meta_input'   => $metas,
	)
	);
	
	$id_post=$id;
	
	return $id_post;
}


/*
Add a product in WooCommerce
$status = 'publish', 'pending', 'draft' or 'trash
$visibility= 'hidden', 'visible', 'search' or 'catalog
*/
function add_product($name,$price=NULL,$reference=NULL,$short_description=NULL,$long_description=NULL,$price_reduced=NULL,$weight=NULL,$height=NULL,$width=NULL,$length=NULL,$shipping_class_id=NULL,$status=NULL,$visibility=NULL,$tax_status=NULL,$tax_class=NULL,$sale_from=NULL,$sale_to=NULL,$featured=NULL,$virtual=NULL,$if_ref_exists_update=false)
{
	global $id_product,$wpdb;
	
	$product=NULL;
	if($if_ref_exists_update && $reference)
	{ 
		$id_product = $wpdb->get_var( $wpdb->prepare( "SELECT PM.post_id FROM ".$wpdb->prefix."postmeta PM,".$wpdb->prefix."posts P WHERE P.ID=PM.post_id AND P.post_status <> 'trash' AND PM.meta_key='_sku' AND PM.meta_value='%s' LIMIT 1", $reference ) ); 
		if ( $id_product ) $product = new WC_Product_Simple( $id_product );
	}
	
	if(!$product)
	{
		$product = new WC_Product_Simple();
		if( ! $product ) return false;
	}
		
	$product->set_name(sanitize_text_field($name));
	if($reference!==NULL) $product->set_sku( sanitize_text_field($reference ));
    if($short_description!==NULL) $product->set_short_description( sanitize_text_field($long_description ));
    if($long_description!==NULL) $product->set_description( sanitize_text_field($short_description) );
	if($status) $product->set_status( $status );
	if($visibility) $product->set_catalog_visibility( $visibility );
	if($featured) $product->set_featured( $featured );
	if($virtual) $product->set_virtual( $virtual );
	
    // Prices
	if($price!==NULL && $price>0)
	{
		$product->set_regular_price( $price );
		$product->set_sale_price( $price_reduced ? $price_reduced : '' );
		$product->set_price( $price_reduced ? $price_reduced :  $price );
		if( $price_reduced ){
			if($sale_from!==NULL) $product->set_date_on_sale_from( $sale_from );
			if($sale_to!==NULL) $product->set_date_on_sale_to( $sale_to );
		}
	}

    // Taxes
    if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
        if($tax_status) $product->set_tax_status(  $tax_status );
        if($tax_class) $product->set_tax_class(  $tax_class );
    }
	
	//https://stackoverflow.com/questions/52937409/create-programmatically-a-product-using-crud-methods-in-woocommerce-3
	
    // Weight, dimensions and shipping class
    if($weight) $product->set_weight( $weight );
    if($length) $product->set_length($length );
    if($width) $product->set_width( $width );
    if($height) $product->set_height( $height );
    if($shipping_class_id) $product->set_shipping_class_id( $shipping_class_id );

    ## --- SAVE PRODUCT --- ##
	//print_r($product);
    $id_product = $product->save();
	
	$post_data = array(
		'post_content'   => $long_description,
		'post_excerpt'   => $short_description,
	);
	
	//update_post_meta( $id_product, '_sku', $reference );
	
	if ( doing_action( 'save_post' ) ) {
		$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $id_product ) );
		clean_post_cache( $product->get_id() );
	} else {
		wp_update_post( array_merge( array( 'ID' => $id_product ), $post_data ) );
	}
	$product->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
			
			

	return $id_product;
}

function update_or_add_product($name,$price=NULL,$reference=NULL,$short_description=NULL,$long_description=NULL,$price_reduced=NULL,$weight=NULL,$height=NULL,$width=NULL,$length=NULL,$shipping_class_id=NULL,$status=NULL,$visibility=NULL,$tax_status=NULL,$tax_class=NULL,$sale_from=NULL,$sale_to=NULL,$featured=NULL,$virtual=NULL)
{
	return add_product($name,$price,$reference,$short_description,$long_description,$price_reduced,$weight,$height,$width,$length,$shipping_class_id,$status,$visibility,$tax_status,$tax_class,$sale_from,$sale_to,$featured,$virtual,true);
}


function associate_categories($categories_ids,$id_product_for_cat=NULL)
{
	global $id_product;
	
	if(!$id_product_for_cat)
	{
		if($id_product) $id_product_for_cat=$id_product;
		else return false;
	}
	
	 $product = new WC_Product( $id_product_for_cat );
	 $product->set_category_ids( $categories_ids );
	$product->save();
}

/*
$status=outofstock,instock
*/
function update_stock($quantity,$status=NULL,$id_product_for_stock=NULL)
{
	global $id_product;
	
	if(!$id_product_for_stock)
	{
		if($id_product) $id_product_for_stock=$id_product;
		else return false;
	}
	
	 $product = new WC_Product( $id_product_for_stock );

	$product->set_manage_stock( true );
	$product->set_stock_status( $status ? $status : ((int)$quantity>0?'instock':'outofstock') );
	update_post_meta($id_product_for_stock, '_stock', (int)$quantity);
	$product->save();
}

function add_image_product($url,$title=NULL,$is_cover=2,$id_product_for_attachment=NULL,$check_file_exist=false)
{
	global $id_product,$warning_if_no_attachment;

	if(!$id_product_for_attachment)
	{
		if($id_product) $id_product_for_attachment=$id_product;
		else return false;
	}
	
	$id_product_for_attachment=(int)$id_product_for_attachment;

	if(substr($url,0,2)=='//') $url='http://'.substr($url,2);
	$url=str_replace(' ','%20',$url);
		
	$pathExt=$url;
	$qpos = strpos($pathExt, "?");
	if($qpos!==false) $pathExt = substr($pathExt, 0, $qpos);
	$extension = pathinfo($pathExt, PATHINFO_EXTENSION); 
	
	if($check_file_exist && !file_exists($url)) return false;


	//return add_attachment($url,$title,$id_post_for_attachment,$check_file_exist);
	
	$upload_dir = wp_upload_dir();
	

	$image_data = file_get_contents( $url );
	
	$filename = preg_replace("#[^a-z0-9A-Z.\-]#isU",'_',preg_replace( '/\.[^.]+$/', '', basename( $url ) )).'.'.$extension;
	
	$attach_folder='MI'.mt_rand();
	$path_upload_dir=$upload_dir['path'] . '/' . $attach_folder;
	
	if ( wp_mkdir_p( $path_upload_dir ) ) {
	  $file = $path_upload_dir . '/' . $filename;
	}
	else {
	  $file = $path_upload_dir . '/' . $filename;
	}
	
	$f = $warning_if_no_image ? fopen($url,"r" ) : @fopen($url,"r" );
	if(!$f) return;
	$f2 = fopen($file,"w+" );
	while ($r=fread($f,8192) ) {
		fwrite($f2,$r);
	}
	fclose($f2);
	fclose($f); 
	
	if(!file_exists($file)) return false;

	
	$wp_filetype = wp_check_filetype( $filename, null );
	if(!$wp_filetype['type']) $wp_filetype=array("ext"=>"jpg","type"=>"image/jpeg");
	
	$attachment = array(
	  'post_mime_type' => $wp_filetype['type'],
	  'post_title' => sanitize_file_name( $filename ),
	  'post_content' => '',
	  'post_status' => 'inherit'
	);
	
	$attach_id = wp_insert_attachment( $attachment, $file );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	
	$product = new WC_Product( $id_product_for_attachment );
	
	// set the thumbnail for the product
	$image_id_array=$product->get_gallery_image_ids();
	if(!$image_id_array) $image_id_array=array();
	if($is_cover === 2)
	{
		if (!$image_id_array)
			$is_cover = 1;
		else
			$is_cover = 0;
	}	
    if($is_cover) set_post_thumbnail( $id_product_for_attachment, $attach_id );

    // now "attach" the post to the product setting 'post_parent'
    /*$attachment = get_post( $attach_id );
    $attachment->post_parent = $id_product_for_attachment;
    wp_update_post( $attachment );*/
	array_push($image_id_array, $attach_id);
	update_post_meta($id_product_for_attachment, '_product_image_gallery', implode(',',$image_id_array)); //set the images id's left over after the array shift as the gallery images
}



function add_image_and_get_url($url,$title=NULL,$check_file_exist=false)
{
	global $warning_if_no_attachment;

	if(substr($url,0,2)=='//') $url='http://'.substr($url,2);
	$url=str_replace(' ','%20',$url);
		
	$pathExt=$url;
	$qpos = strpos($pathExt, "?");
	if($qpos!==false) $pathExt = substr($pathExt, 0, $qpos);
	$extension = pathinfo($pathExt, PATHINFO_EXTENSION); 
	
	if($check_file_exist && !file_exists($url)) return false;


	//return add_attachment($url,$title,$id_post_for_attachment,$check_file_exist);
	
	$upload_dir = wp_upload_dir();
	

	$image_data = file_get_contents( $url );
	
	$filename = preg_replace("#[^a-z0-9A-Z.\-]#isU",'_',preg_replace( '/\.[^.]+$/', '', basename( $url ) )).'.'.$extension;
	
	$attach_folder='MI'.mt_rand();
	$path_upload_dir=$upload_dir['path'] . '/' . $attach_folder;
	
	if ( wp_mkdir_p( $path_upload_dir ) ) {
	  $file = $path_upload_dir . '/' . $filename;
	}
	else {
	  $file = $path_upload_dir . '/' . $filename;
	}
	
	$f = $warning_if_no_image ? fopen($url,"r" ) : @fopen($url,"r" );
	if(!$f) return;
	$f2 = fopen($file,"w+" );
	while ($r=fread($f,8192) ) {
		fwrite($f2,$r);
	}
	fclose($f2);
	fclose($f); 
	
	if(!file_exists($file)) return false;

	
	$wp_filetype = wp_check_filetype( $filename, null );
	if(!$wp_filetype['type']) $wp_filetype=array("ext"=>"jpg","type"=>"image/jpeg");
	
	$attachment = array(
	  'post_mime_type' => $wp_filetype['type'],
	  'post_title' => sanitize_file_name( $filename ),
	  'post_content' => '',
	  'post_status' => 'inherit'
	);
	
	$attach_id = wp_insert_attachment( $attachment, $file );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	
	return wp_get_attachment_url($attach_id);
}

function add_attachment($url,$title=NULL,$id_post_for_attachment=NULL,$check_file_exist=false)
{
	global $id_post,$id_product,$id_attachment,$warning_if_no_attachment;

	if(!$id_post_for_attachment)
	{
		if($id_post) $id_post_for_attachment=$id_post;
		if($id_product) $id_post_for_attachment=$id_product;
		else return false;
	}
	
	$id_post_for_attachment=(int)$id_post_for_attachment;

	if(substr($url,0,2)=='//') $url='http://'.substr($url,2);
	$url=str_replace(' ','%20',$url);
		
	$pathExt=$url;
	$qpos = strpos($pathExt, "?");
	if($qpos!==false) $pathExt = substr($pathExt, 0, $qpos);
	$extension = pathinfo($pathExt, PATHINFO_EXTENSION); 
	
	if($check_file_exist && !file_exists($url)) return false;
	
	$path_upload=wp_upload_dir();
	$path_upload_dir=$path_upload['basedir'];
	
	$attach_folder='MI'.mt_rand();
	@mkdir($path_upload_dir.'/'.$attach_folder);
	
	if($tmpName = $path_upload_dir.'/'.$attach_folder.'/'.preg_replace("#[^a-z0-9A-Z.\-]#isU",'_',preg_replace( '/\.[^.]+$/', '', $title?$title:pathinfo($pathExt, PATHINFO_BASENAME) )).'.'.$extension)
	{
		$f = $warning_if_no_image ? fopen($url,"r" ) : @fopen($url,"r" );
		if(!$f) return;
		$f2 = fopen($tmpName,"w+" );
		while ($r=fread($f,8192) ) {
			fwrite($f2,$r);
		}
		fclose($f2);
		fclose($f); 
		
		if(!file_exists($tmpName)) return false;
		
		//mime
		$mimeType='application/pdf';
		if (function_exists('finfo_open'))
		{
			$finfo = @finfo_open(FILEINFO_MIME);
			$mimeType = @finfo_file($finfo, $tmpName,FILEINFO_MIME_TYPE);
			@finfo_close($finfo);
		}
		else if (function_exists('mime_content_type'))
			$mimeType = @mime_content_type($tmpName);
		else if (function_exists('exec'))
		{
			$mimeType = trim(@exec('file -b --mime-type '.escapeshellarg($tmpName)));
			if (!$mimeType)
				$mimeType = trim(@exec('file --mime '.escapeshellarg($tmpName)));
			if (!$mimeType)
				$mimeType = trim(@exec('file -bi '.escapeshellarg($tmpName)));
		}
		$mimeType=str_replace('charsetbinary','',$mimeType);

		$attachment = array(
			'guid'           => $tmpName, 
			'post_mime_type' => $mimeType,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $title?$title:pathinfo($pathExt, PATHINFO_BASENAME) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		
		$filename=$tmpName;
			
		$attach_id = wp_insert_attachment( $attachment, $filename, $id_post_for_attachment );

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		if(strpos($mimeType,'image')!==FALSE)
			set_post_thumbnail( $id_post_for_attachment, $attach_id );
	
		//unlink($tmpName);

		$id_attachment=$attach_id;
		return $id_attachment;
	}

	return false;
}

function add_category_product($name,$id_parent_category=NULL,$active=true,$description='')
{
	global $id_category,$wpdb;
	
	$retour=wp_insert_term( $name, 'product_cat', array(
		'description' => $description, // optional
		'parent' => (int)$id_parent_category, // optional
	) );
	
	if(!is_array($retour) && get_class($retour)=='WP_Error')
	{
		//print_r($retour);
		//return $retour->error_data->term_exists;
		return NULL;
	}
	
	return $retour['term_id'];
}

/*
# ALIAS of add_attribue SEE add_attribute
*/
function add_taxonomy($name,$attribute_id=NULL,$type='select',$public=true,$orderby='')
{
	return add_attribue($name,$attribute_id,$type,$public,$orderby);
}

/*
# Add an attribute, also called taxonomy or combination (an attribute is like a feature in Prestashop, for example Power or Dimension). Atrrribues in WooCommerce can be like Prestashop attributes too (combination). 
< Returns de id of the created attribute, it is the "taxonomy name" with the prefix pa_ generally
@name name of the attribute
@attribute_id[NULL] id/slug of the attribute, generated from the name if null
@type['select'] type of the attribtue
@has_archives[false] Enable or disable attribute archives
@orderby[''] how to order terms of the attriutes. 'menu_order' = personnal order, 'name' = by term name (alphabetical), 'name_num' = by term name if it is numbers, 'id' = by term ID 
*/
function add_attribue($name,$attribute_id=NULL,$type='select',$has_archives=false,$orderby='')
{
	global $id_attribute;
	
	if(!$name) return false;
	
	// Make sure caches are clean. 
	delete_transient( 'wc_attribute_taxonomies' ); 
	WC_Cache_Helper::incr_cache_prefix( 'woocommerce-attributes' ); 

	$attribute = array(
		'name'   => wc_clean( wp_unslash( $name ) ) , // WPCS: input var ok, CSRF ok.
		'slug'    => $attribute_id ? wc_sanitize_taxonomy_name( wp_unslash( $attribute_id ) ) : '', // WPCS: input var ok, CSRF ok, sanitization ok.
		'type'    => isset( $type ) ? wc_clean( wp_unslash( $type ) ) : 'select', // WPCS: input var ok, CSRF ok.
		'order_by' => isset( $orderby ) ? wc_clean( wp_unslash( $orderby ) ) : '', // WPCS: input var ok, CSRF ok.
		'has_archives'  => (bool)$has_archives, // WPCS: input var ok, CSRF ok.
	);

	if ( empty( $attribute['type'] ) ) {
		$attribute['type'] = 'select';
	}
	if ( empty( $attribute['name'] ) ) {
		$attribute['name'] = ucfirst( $attribute['slug'] );
	}
	if ( empty( $attribute['name'] ) ) {
		$attribute['name'] = wc_sanitize_taxonomy_name( $attribute['name'] );
	}
	if ( empty( $attribute['slug'] ) ) {
		$attribute['slug'] = wc_sanitize_taxonomy_name( wp_unslash( $attribute['name'] ));
	}

	$taxonomy_name = wc_attribute_taxonomy_name( $attribute['slug'] ); 
	$id = wc_create_attribute( $attribute );

	if ( !is_wp_error( $id ) ) {
		// Register as taxonomy. 
		register_taxonomy( 
			$taxonomy_name, 
			apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy_name, array( 'product' ) ), 
			apply_filters( 
				'woocommerce_taxonomy_args_' . $taxonomy_name, 
				array( 
					'labels'       => array( 
						'name' => $attribute['name'], 
					), 
					'hierarchical' => false, 
					'show_ui'      => false, 
					'query_var'    => true, 
					'rewrite'      => false, 
				) 
			) 
		); 

		
		
		$id_attribute=$taxonomy_name;
		return $id_attribute;
	}
	else return NULL;
	//echo $id->get_error_message();
}

/*
# Add an term, also called attribute value
< Returns de id of the created term (it is not the slug, it is a numerical ID)
@name name of the term
@id_attribute_for_term[NULL] if null, last created attribute is used. This filed is a text not a numerica ID. This field is also called the taxonomy. it is the "taxonomy name" with the prefix pa_ generally
@slug[NULL] the slug, a code to identify the term, can be automatically generated
@description[NULL] term description
@id_parent_term[NULL] The id of the parent term
*/
function add_term($name,$id_attribute_for_term=NULL,$slug=NULL,$description='',$id_parent_term=NULL)
{
	global $id_attribute,$id_term;
	
		if(!$id_attribute_for_term)
	{
		if($id_attribute) $id_attribute_for_term=$id_attribute;
		else return false;
	}
	
	if(!$name) return false;
	if(!$slug) wc_sanitize_taxonomy_name( wp_unslash( $name ));
	
	// Make sure caches are clean. 
	delete_transient( 'wc_attribute_taxonomies' ); 
	WC_Cache_Helper::incr_cache_prefix( 'woocommerce-attributes' ); 

	$ret = wp_insert_term( $name, $id_attribute_for_term, array(
		'slug'=>$slug,
		'parent'=>(int)$parent,
		'description'=>$description,
	) );
	
	if ( !is_wp_error( $ret ) )
	{
		$id_term=(int)$ret['term_id'];
		return $id_term;
	}
	return NULL;
	//echo $ret->get_error_message();

}


/*
# Associate terms to a product
@id_attribute_for_association[NULL] if null, last created attribute is used. This filed is a text not a numerica ID. This field is also called the taxonomy
@id_term_for_association[NULL] ID of the term to associate (numérical), if null, last term added is used
@id_product_for_association[NULL] product ID, if null the last product updated is used
*/
function associate_terms($id_attribute_for_association=NULL,$id_term_for_association=NULL,$id_product_for_association=NULL)
{
	global $id_term,$id_attribute,$id_product;

	if(!$id_term_for_association)
	{
		if($id_term) $id_term_for_association=$id_term;
		else return false;
	}

	if(!$id_attribute_for_association)
	{
		if($id_attribute) $id_attribute_for_association=$id_attribute;
		else return false;
	}

	if(!$id_product_for_association)
	{
		if($id_product) $id_product_for_association=$id_product;
		else return false;
	}

	// Make sure caches are clean. 
	delete_transient( 'wc_attribute_taxonomies' ); 
	WC_Cache_Helper::incr_cache_prefix( 'woocommerce-attributes' ); 

	$product = new WC_Product($id_product_for_association);	

	$attributes=$product->get_attributes();

	$attribute_id   = 0;
	$attribute_name = wc_clean( esc_html( $id_attribute_for_association ) );

	if ( 'pa_' === substr( $id_attribute_for_association, 0, 3 ) ) {
		$attribute_id = wc_attribute_taxonomy_id_by_name( $id_attribute_for_association );
	}
	
	$options = wp_parse_id_list( array($id_term_for_association) );

	$data=array(
		'attribute_names'=> array($id_attribute_for_association),
		'attribute_values'=> array(array($id_term_for_association)),
		'attribute_position'=>array(1),
		'attribute_visibility'=>array(1),
		//'attribute_variation'=>array(0),
	);
	
	$attribute = new WC_Product_Attribute();
	$attribute->set_id( $attribute_id );
	$attribute->set_name( $attribute_name );
	$attribute->set_options( $options );
	$attribute->set_position( 0 );
	$attribute->set_visible( true );
	$attribute->set_variation( false );
	$attributes[] = apply_filters( 'woocommerce_admin_meta_boxes_prepare_attribute', $attribute, $data, 0 );
	
	
	$product->set_props(
		array(
			'attributes'         => $attributes,
			//'default_attributes' => WC_Meta_Box_Product_Data::prepare_set_attributes( $attributes, 'default_attribute_' )
		)
	);
	
	$product->save();

	sql_execute("REPLACE INTO _DB_PREFIX_term_relationships (object_id, term_taxonomy_id,term_order) VALUES (".(int)$id_product_for_association.",".(int)$id_term_for_association.",0)");
	//	sql_execute("REPLACE INTO _DB_PREFIX_term_relationships (object_id, term_taxonomy_id) VALUES (".(int)$id_product_for_association.",".(int)$id_term_for_association.")");


         
}

/*
Check if a reference exist already
if $reference is an array of references it refurn an associative array [reference=>id_product]
if there is $id_manufacturer_signification, the product must have this manufacturer
*/
function id_product_reference($reference,$use_id_product=false)
{
	global $id_product,$wpdb;
	
	if(!$reference) return false;
	
	$id_product_find = $wpdb->get_var( $wpdb->prepare( "SELECT PM.post_id FROM ".$wpdb->prefix."postmeta PM,".$wpdb->prefix."posts P WHERE P.ID=PM.post_id AND P.post_status <> 'trash' AND PM.meta_key='_sku' AND PM.meta_value='%s' LIMIT 1", $reference ) ); 
	
	if($use_id_product) $id_product=$id_product_find;
	
	return $id_product_find;
}

/*
Update the supplier link in the product backoffice
*/
function update_supplier_page($url,$id_product_for_page=NULL)
{
	global $id_product;
	
	if(!$id_product_for_page)
	{
		if($id_product) $id_product_for_page=$id_product;
		else return false;
	}
	update_post_meta($id_product_for_page, 'fiche_fournisseur', array (
	  'title' => 'Product page of supplier',
	  'url' => $url,
	  'target' => '_blank',
	));

}

/***************
GESTION DE FICHIERS
***************/

function unzip($file, $path='', $effacer_zip=false,$extensions_ok=array() )
{
	set_time_limit(0);

	/*Méthode qui permet de décompresser un fichier zip $file dans un répertoire de destination $path
  et qui retourne un tableau contenant la liste des fichiers extraits
  Si $effacer_zip est égal à true, on efface le fichier zip d'origine $file*/ 
	$tab_liste_fichiers = array(); //Initialisation
	$zip = zip_open($file);
	if ($zip)
	{
		for ($i=1;$zip_entry = zip_read($zip);$i++) //Pour chaque fichier contenu dans le fichier zip
		{
			if (zip_entry_filesize($zip_entry) > 0)
			{
				$complete_path = $path.preg_replace("#[^a-zA-Z0-9_.,\(\)/]#isU",'_',dirname(zip_entry_name($zip_entry)));
				$nom_fichier=preg_replace("#[^a-zA-Z0-9_.,\(\)/]#isU",'_',zip_entry_name($zip_entry));
				
				//extension
				preg_match("#^(.+)\.([a-z]{3,4})$#isU",$nom_fichier,$extension); // Récupération de l'extension
				$extension=$extension[2];
				if($extensions_ok && !empty($extensions_ok) && !in_array(strtolower($extension),$extensions_ok)) continue;

				/*On ajoute le nom du fichier dans le tableau*/
				array_push($tab_liste_fichiers,$nom_fichier);
				$complete_name = $path.$nom_fichier; //Nom et chemin de destination
				if(!file_exists($complete_path))
				{
						$tmp = '';
						foreach(explode('/',$complete_path) AS $k)
						{
								$tmp .= $k.'/';
								if(!file_exists($tmp))
								{ mkdir($tmp, 0777); }
						}
				}
				/*On extrait le fichier*/

				if (zip_entry_open($zip, $zip_entry, "r"))
				{

						$fd = fopen($complete_name, 'w');
						fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
						fclose($fd);
						zip_entry_close($zip_entry);
				}
			}
		}
		zip_close($zip);
		/*On efface éventuellement le fichier zip d'origine*/
		if ($effacer_zip === true)
		unlink($file);
	}
	return $tab_liste_fichiers;
}


function sql_value($sql)
{
	global $wpdb;
	
	$sql=str_replace('_DB_PREFIX_',$wpdb->prefix,$sql);
	 $resultat=$wpdb->get_var( $sql );
	 
	 return $resultat;
}



function sql_set($sql)
{
	global $wpdb;
	
	$sql=str_replace('_DB_PREFIX_',$wpdb->prefix,$sql);
	 $results=$wpdb->get_results( $sql );
	 
	 return $results;
}

function sql_row($sql)
{
	global $wpdb;
	
	$sql=str_replace('_DB_PREFIX_',$wpdb->prefix,$sql);
	 $results=$wpdb->get_row( $sql );
	 
	 return $results;
}


function sql_execute($sql)
{
	global $wpdb;
	
	$sql=str_replace('_DB_PREFIX_',$wpdb->prefix,$sql);
	$wpdb->query( $sql );
}


function sql_insert($sql)
{
	global $wpdb;
	
	$sql=str_replace('_DB_PREFIX_',$wpdb->prefix,$sql);
	 $results=$wpdb->query( $sql );
	 
	return $wpdb->insert_id;
}


/* Unzip with ssh (faster), to install unzipper : apt-get install zip unzip */
function unzip_ssh($file, $path='')
{	
	return system('unzip '.$file.' -d '.$path);
}

function extract_gz($file, $path)
{
    $sfp = gzopen($file, "rb");
    $fp = fopen($path, "w");

    while (!gzeof($sfp)) {
        $string = gzread($sfp, 4096);
        fwrite($fp, $string, strlen($string));
    }
    gzclose($sfp);
    fclose($fp);
}


function create_folder($folder_name,$chmod=0777)
{
	return mkdir($folder_name, $chmod);
}

function remove($path_all)
{
	if(!is_array($path_all)) $path_all=array($path_all);
	
	foreach($path_all as $path)
	{
		if(is_dir($path))
		{
			$files = array_diff(scandir($path), array('.','..'));
			foreach ($files as $file) {
			  (is_dir("$path/$file")) ? remove("$path/$file") : unlink("$path/$file");
			}
			rmdir($path); 
			continue;
		}
		unlink($path);
	}
}

function move($original,$destination)
{
	return rename($original,$destination);
}

function create_file($path,$txt)
{
	return file_put_contents($path,$txt);
}

function read_file($path)
{
	return file_get_contents($path);
}

/** DO NOT CHANGE THE NAME OF THIS FUNCTION **/
function upload($path_to_move=NULL,$return_disk_path=false)
{
	if(!$path_to_move) 
	{
		$uniqid=$_FILES['file']['name'];
		$ext=NULL;
		$path_default=wp_upload_dir(); //dirname(__FILE__).'/tmp/'
		$path_default=$path_default['basedir'];
		//@mkdir($path_default);
		while (!$uniqid || file_exists($path_default.$uniqid))
		{
			$uniqid = sha1(microtime());
			$ext=mimeToExtension($_FILES['file']['type']);
			$uniqid=$uniqid.($ext?'.'.$ext:'');
		}
		
		$path_to_move=$path_default.$uniqid;
	}
	
	if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name']))
	{
		move_uploaded_file($_FILES['file']['tmp_name'], $path_to_move );
		@unlink($_FILES['file']['tmp_name']);
		return $return_disk_path  ?  $path_to_move  :  str_replace('\\','/',str_replace(rtrim(get_home_path(),'\/'),rtrim(_PS_BASE_URL_.get_site_url(),'\/'),$path_to_move)  );
	}
	return false;
}

/* get path with a / at the end */
function get_path($of_this_module=false)
{
	if($of_this_module) return dirname(__FILE__) . '/' ;

	else return get_home_path();
}

function get_url()
{
	return get_site_url();
}

function time_unlimited()
{
	set_time_limit(0);
}

function list_files_in($path,$filter=false)
{
	$return=array();
	
	chdir($path);
	
	if ($handle = opendir($path)) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				if(is_file($entry))
				{
					$realpath=realpath($entry);
					if(!$filter || preg_match("#".$filter."#isU",$realpath))
						$return[]=$realpath;
				}
			}
		}
		closedir($handle);
	}	
	
	return $return;
}

function last_modified_in($path,$filter=false)
{
	$files=list_files_in($path,$filter);
	
	$files = array_combine($files, array_map("filemtime", $files));
	arsort($files);
	
	return array_keys($files);
}

function lastest_modified_in($path,$filter=false)
{
	$files=last_modified_in($path,$filter);
	if(!$files) return false;
	
	return $files[0];
}

function first_modified_in($path,$filter=false)
{
	$files=list_files_in($path,$filter);
	
	$files = array_combine($files, array_map("filemtime", $files));
	asort($files);
	
	return array_keys($files);
}

function firstest_modified_in($path,$filter=false)
{
	$files=first_modified_in($path,$filter);
	if(!$files) return false;
	
	return $files[0];
}


function recursive_chmod($path, $filePerm=0644, $dirPerm=0755) {
	// Check if the path exists
	if (!file_exists($path)) {
		return(false);
	}
	
	// See whether this is a file
	if (is_file($path)) {
		// Chmod the file with our given filepermissions
		chmod($path, $filePerm);
	
	// If this is a directory...
	} elseif (is_dir($path)) {
		// Then get an array of the contents
		$foldersAndFiles = scandir($path);
		
		// Remove "." and ".." from the list
		$entries = array_slice($foldersAndFiles, 2);
		
		// Parse every result...
		foreach ($entries as $entry) {
			// And call this function again recursively, with the same permissions
			recursive_chmod($path."/".$entry, $filePerm, $dirPerm);
		}
		
		// When we are done with the contents of the directory, we chmod the directory itself
		chmod($path, $dirPerm);
	}
	
	// Everything seemed to work out well, return true
	return(true);
}

