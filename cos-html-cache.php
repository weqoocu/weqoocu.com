<?php
/*
Plugin Name: cos-html-cache
Plugin URI: http://www.storyday.com/tag/cos-html-cache
Description: cos-html-cache is an extremely efficient WordPress page caching plugin designed to make your WordPress site much faster and more responsive. Based on URL rewriting, the plugin will automatically generate real html files for posts when they are loaded for the first time, and automatically renew the html files if their associated posts are modified.
cos-html-cache. Current version, cos-html-cache2.6, is a huge improvement over previous versions of cos-html-cache.
Version: 2.7.4
Author: jiangdong
date:2007-07-19
Author URI:http://www.storyday.com
*/
/*
Change log:
2007-06-02:  added custom cookie to fix Chinese charactor problems
2007-06-03:  added page cache function
2007-06-24:  fixed js bugs of chinese display
2007-07-25:	 changedd the cache merchanism
2007-08-14:	 changed the comment js
2008-02-21:  fixed database crush error
2008-04-06:  Compatible for wordpress2.5
2008-07-18:  Compatible for wordpress2.6  solved the cookie problems
2008-12-20:  fixed admin cookie httponly problems
2009-03-04:  fixed cookie '+' problems
2009-03-15:	 remove cache for password protected posts & fixed some js problems
2009-03-24:	 remove comment user cache data
2012-09-19:  cache remove bug fixed
				
*/
/* config */
define('IS_INDEX',true);// false = do not create home page cache 

/*end of config*/

define('COSVERSION','2.7.3');

require_once(ABSPATH . 'wp-admin/includes/file.php');
/* end of config */
$sm_locale = get_locale();

$sm_mofile = dirname(__FILE__) . "/cosbeta-$sm_locale.mo";
load_textdomain('cosbeta', $sm_mofile);
$cossithome = get_option('home');
$script_uri = rtrim( "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] ,"/");



/*
if(is_ssl()){
    $script_uri = rtrim( "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]  ,"/");
}else{
    $script_uri = rtrim( "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]  ,"/");
}
*/

$home_path = get_home_path();

define('SCRIPT_URI',$script_uri);
define('CosSiteHome',$cossithome);
define('CosBlogPath', $home_path);
define("COSMETA","<!--this is a real static html file created at ".date("Y-m-d H:i:s")." by cos-html-cache ".COSVERSION." -->");
function CreateHtmlFile($FilePath,$Content){
	$FilePath = preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', $FilePath);

	// if there is http:// $FilePath will return its bas path
	$dir_array = explode("/",$FilePath);

	//split the FilePath
	$max_index = count($dir_array) ;
	$i = 0;
	$path = $_SERVER['DOCUMENT_ROOT']."/";

	while( $i < $max_index ){
		$path .= "/".$dir_array[$i];
		$path = str_replace("//","/",$path);

		if( $dir_array[$i] == "" ){
			$i ++ ;
			continue;
		}

		if( substr_count($path, '&') ) return true;
		if( substr_count($path, '?') ) return true;
		if( !substr_count($path, '.htm') ){
			//if is a directory
			if( !file_exists( $path ) ){
				@mkdir( $path, 0777);
				@chmod( $path, 0777 );
			}
		}
		$i ++;
	}

    if( is_dir( $path ) ){
		$path = $path."/index.html";
	}
	if ( !strstr( strtolower($Content), '</html>' ) ) return;

	//if sql error ignore...
	$fp = @fopen( $path , "w+" );
	if( $fp ){
		@chmod($path, 0666 ) ;
		@flock($fp ,LOCK_EX );

		// write the file。
		fwrite( $fp , $Content );
		@flock($fp, LOCK_UN);
		fclose($fp);
	 }
}

/* read the content from output buffer */
$is_buffer = false;
if( substr_count($_SERVER['REQUEST_URI'], '.htm') || ( SCRIPT_URI == CosSiteHome) ){
	if( strlen( $_COOKIE['wordpress_logged_in_'.COOKIEHASH] ) < 4 ){
		$is_buffer = true;
	}
	if(  substr_count($_SERVER['REQUEST_URI'], '?'))  $is_buffer = false;
	if(  substr_count($_SERVER['REQUEST_URI'], '../'))  $is_buffer = false;
}

if( $is_buffer ){
	ob_start('cos_cache_ob_callback');
	register_shutdown_function('cos_cache_shutdown_callback');
}

function cos_cache_ob_callback($buffer){

	$buffer = preg_replace('/(<\s*input[^>]+?(name=["\']author[\'"])[^>]+?value=(["\']))([^"\']+?)\3/i', '\1\3', $buffer);

	$buffer = preg_replace('/(<\s*input[^>]+?value=)([\'"])[^\'"]+\2([^>]+?name=[\'"]author[\'"])/i', '\1""\3', $buffer);
	
	$buffer = preg_replace('/(<\s*input[^>]+?(name=["\']url[\'"])[^>]+?value=(["\']))([^"\']+?)\3/i', '\1\3', $buffer);

	$buffer = preg_replace('/(<\s*input[^>]+?value=)([\'"])[^\'"]+\2([^>]+?name=[\'"]url[\'"])/i', '\1""\3', $buffer);
	
	$buffer = preg_replace('/(<\s*input[^>]+?(name=["\']email[\'"])[^>]+?value=(["\']))([^"\']+?)\3/i', '\1\3', $buffer);

	$buffer = preg_replace('/(<\s*input[^>]+?value=)([\'"])[^\'"]+\2([^>]+?name=[\'"]email[\'"])/i', '\1""\3', $buffer);

	if( !substr_count($buffer, '<!--cos-html-cache-safe-tag-->') ) return  $buffer;
	if( substr_count($buffer, 'post_password') > 0 ) return  $buffer;//to check if post password protected 
	$wppasscookie = "wp-postpass_".COOKIEHASH;
	if( strlen( $_COOKIE[$wppasscookie] ) > 0 ) return  $buffer;//to check if post password protected 
	/*
	$comment_author_url='';
$comment_author_email='';
$comment_author='';*/
	
	
	elseif( SCRIPT_URI == CosSiteHome) {// creat homepage
		$fp = @fopen( CosBlogPath."index.bak" , "w+" );
		if( $fp ){
			@flock($fp ,LOCK_EX );
			// write the file。
			fwrite( $fp , $buffer.COSMETA );
			@flock($fp, LOCK_UN);
			fclose($fp);
		 }
		if(IS_INDEX) {
	//@rename(CosBlogPath."index.bak",CosBlogPath."index.html");
	

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

	}

	}
	else
		CreateHtmlFile($_SERVER['REQUEST_URI'],$buffer.COSMETA );
	return $buffer;
}

function cos_cache_shutdown_callback(){
	ob_end_flush();
	flush();
}

if( !function_exists('DelCacheByUrl') ){
	function DelCacheByUrl($url) {
		$url = CosBlogPath.str_replace( CosSiteHome,"",$url );
		$url = str_replace("//","/", $url );
		 if( file_exists( $url )){
			 if( is_dir( $url )) {
				
				//@unlink( $url."/index.html" );@rmdir($url);
			
			}
			 else @unlink( $url );
		 }
	}
}

if( !function_exists('htmlCacheDel') ){
	// create single html
	function htmlCacheDel($post_ID) {
		if( $post_ID == "" ) return true;
		$uri = get_permalink($post_ID);
		DelCacheByUrl($uri );
	}
}

if( !function_exists('htmlCacheDelNb') ){
	// delete nabour posts
	function htmlCacheDelNb($post_ID) {
		if( $post_ID == "" ) return true;

		$uri = get_permalink($post_ID);
		DelCacheByUrl($uri );
		global $wpdb;
		$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_status = 'publish'   AND   post_type='post'   AND  ID < ".$post_ID." ORDER BY ID DESC LIMIT 0,1;");
		$uri1 = get_permalink($postRes[0]->ID);
		DelCacheByUrl($uri1 );
		$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_status = 'publish'  AND   post_type='post'    AND ID > ".$post_ID."  ORDER BY ID ASC  LIMIT 0,1;");
		if( $postRes[0]->ID != '' ){
			  $uri2  = get_permalink($postRes[0]->ID);
			  DelCacheByUrl($uri2 );
		}
	}
}

//create index.html
if( !function_exists('createIndexHTML') ){
	function createIndexHTML($post_ID){
		if( $post_ID == "" ) return true;
		//[menghao]@rename(ABSPATH."index.html",ABSPATH."index.bak");
		@rename(CosBlogPath."index.html",CosBlogPath."index.bak");//[menghao]
	}
}

if(!function_exists("htmlCacheDel_reg_admin")) {
	/**
	* Add the options page in the admin menu
	*/
	function htmlCacheDel_reg_admin() {
		if (function_exists('add_options_page')) {
			add_options_page('html-cache-creator', 'CosHtmlCache',8, basename(__FILE__), 'cosHtmlOption');
			//add_options_page($page_title, $menu_title, $access_level, $file).
		}
	}
}

add_action('admin_menu', 'htmlCacheDel_reg_admin');

if(!function_exists("cosHtmlOption")) {
function cosHtmlOption(){
	do_cos_html_cache_action();
?>
	<div class="wrap" style="padding:10px 0 0 10px;text-align:left">
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<p>
	<?php _e("Click the button bellow to delete all the html cache files","cosbeta");?></p>
	<p><?php _e("Note:this will Not  delete data from your databases","cosbeta");?></p>
	<p><?php _e("If you want to rebuild all cache files, you should delete them first,and then the cache files will be built when post or page first visited","cosbeta");?></p>

	<p><b><?php _e("specify a post ID or Title to to delete the related cache file","cosbeta");?></b> <input type="text" id="cache_id" name="cache_id" value="" /> <?php _e("Leave blank if you want to delete all caches","cosbeta");?></p>
	<p><input type="submit" value="<?php _e("Delete Html Cache files","cosbeta");?>" id="htmlCacheDelbt" name="htmlCacheDelbt" onClick="return checkcacheinput(); " />
	</form>
	</div>

	<SCRIPT LANGUAGE="JavaScript">
	<!--
		function checkcacheinput(){
		document.getElementById('htmlCacheDelbt').value = 'Please Wait...';
		return true;
	}
	//-->
	</SCRIPT>
<?php
	}
}
/*
end of get url
*/
// deal with rebuild or delete
function do_cos_html_cache_action(){
	if( !empty($_POST['htmlCacheDelbt']) ){
		@rename(CosBlogPath."index.html",CosBlogPath."index.bak");
		@chmod( CosBlogPath."index.bak", 0666 );
		global $wpdb;
		if( $_POST['cache_id'] * 1 > 0 ){
			//delete cache by id
			 DelCacheByUrl(get_permalink($_POST['cache_id']));
			 $msg = __('the post cache was deleted successfully: ID=','cosbeta').$_POST['cache_id'];
		}
		else if( strlen($_POST['cache_id']) > 2  ){
			$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_title like '%".$_POST['cache_id']."%' LIMIT 0,1 ");
			DelCacheByUrl( get_permalink( $postRes[0]->ID ) );
			$msg = __('the post cache was deleted successfully: Title=','cosbeta').$_POST['cache_id'];
		}
		else{
		$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_status = 'publish' AND ( post_type='post' OR  post_type='page' )  ORDER BY post_modified DESC ");
		foreach($postRes as $post) {
			DelCacheByUrl(get_permalink($post->ID));
			}
			$msg = __('HTML Caches were deleted successfully','cosbeta');
		}
	}
	if($msg)
	echo '<div class="updated"><strong><p>'.$msg.'</p></strong></div>';
}
$is_add_comment_is = true;
/*
 * with ajax comments
 */
if ( !function_exists("cos_comments_js") ){
	function cos_comments_js($postID){
		global $is_add_comment_is;
		if( $is_add_comment_is ){
			$is_add_comment_is = false;
		?>
		<script language="JavaScript" type="text/javascript" src="<?php echo CosSiteHome;?>/wp-content/plugins/cos-html-cache/common.js.php?hash=<?php echo COOKIEHASH;?>"></script>
		<script language="JavaScript" type="text/javascript">
		//<![CDATA[
		var hash = "<?php echo COOKIEHASH;?>";
		var author_cookie = "comment_author_" + hash;
		var email_cookie = "comment_author_email_" + hash;
		var url_cookie = "comment_author_url_" + hash; 
		var adminmail = "<?php  echo str_replace('@','{_}',get_option('admin_email'));?>";
		var adminurl = "<?php  echo  get_option('siteurl') ;?>";
		setCommForm();
		//]]>
		</script>
	<?php
		}
	}
}

function CosSafeTag(){
	if   ( is_single() || (is_home() && IS_INDEX) )  {
		echo "<!--cos-html-cache-safe-tag-->";
	}
}
function clearCommentHistory(){
global $comment_author_url,$comment_author_email,$comment_author;
$comment_author_url='';
$comment_author_email='';
$comment_author='';
}
//add_action('comments_array','clearCommentHistory');
add_action('get_footer', 'CosSafeTag');
add_action('comment_form', 'cos_comments_js');

/* end of ajaxcomments*/
if(IS_INDEX)	add_action('publish_post', 'createIndexHTML');
add_action('publish_post', 'htmlCacheDelNb');

if(IS_INDEX)	add_action('delete_post', 'createIndexHTML');
add_action('delete_post', 'htmlCacheDelNb');

//if comments add
add_action('edit_post', 'htmlCacheDel');
if(IS_INDEX) add_action('edit_post', 'createIndexHTML');
?>
