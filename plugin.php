<?php 
/*
Plugin Name: Tynt Insight For WordPress
Plugin URI: http://mikhailkozlov.com/tynt-insight-for-wordpress/
Description: Make link-backs to your content effortless for readers and gain new insight into user engagement with Tynt Insight.
Author: Mikhail Kozlov
Version: 1.0.0
License: MIT
Author Url: http://mikhailkozlov.com/
*/

class VayamaTyntInsight{
	static $options=array(
				'v'=>'1.0.0',
				'key'=>'vayama-tynt-insight',
				'tynt-where'=>'footer',
				'tynt-id'=>''
				
	);
	static $defs=array('tynt-where'=>array('footer'=>'WP Footer','header'=>'WP Header','custom'=>'I will paste the code'));
	
	function init(){
		$options=get_option(self::$options['key']);
		if($options === false){
			$options = self::activate();
		}
		if(is_admin()) {
			add_action('admin_menu', array('VayamaTyntInsight','admin_menu'));
			if(isset($_POST['action']) && isset($_GET['page']) && $_GET['page'] == 'vayama-tynt-insight'){
				$options=self::$options;
				$options=array_merge($options,unserialize(get_option($options['key'])));
				foreach(self::$options as $k=>$v){
					if(isset($_POST[$k]) && !empty($_POST[$k])){
						$options[$k] = $_POST[$k];
					}
				}
				if(update_option( self::$options['key'], serialize($options) )){
					header('Location: options-general.php?page=vayama-tynt-insight&updated=true');
				}
				
				
			}
			
		}else{
			switch($options['tynt-where']){
				case 'footer':
					add_action('wp_footer', array('VayamaTyntInsight','printTyntInsight'));
				break;
				case 'header':
					add_action('wp_head', array('VayamaTyntInsight','printTyntInsight'));
				break;
				default:
					// this custome case, so user will implement code.
				break;
					
					
			}
		}		
		
	}
	function admin_menu(){
		add_options_page('Tynt Insight Options', 'Tynt Insight', 'manage_options', 'vayama-tynt-insight', array('VayamaTyntInsight','admin_options_page'));
	}
	function admin_options_page(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$options=self::$options;
		$options=array_merge($options,unserialize(get_option($options['key'])));
		echo '<div class="wrap">';
		echo '<div class="icon32" id="icon-options-general"><br /></div><h2>Tynt Insight Options</h2>';
		echo '<p>
				Before you can start you need to have and account @ <a href="http://www.tynt.com/" target="_blank">http://www.tynt.com/</a>.<br />
				Once you have access to your panel, paste your Tynt Insight code here.
				</p>';
		
		echo '<form action="options-general.php?page=vayama-tynt-insight" name="vayama-tynt-insight" method="post">';
		echo '
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Tynt Insight ID</th>
						<td>
							<input type="text" id="tynt-id" class="regular-text" value="'.$options['tynt-id'].'" name="tynt-id">
							<div>On Your Script page find line of code that looks like this<br /><i>var Tynt=Tynt||[];Tynt.push(\'<span style="color:red">findyourcode</span>\');</i><br /> and copy it and paste it above.</div>
						</td>
					</tr>
					<tr>
						<th scope="row">When to Load Tynt Insight</th>
						<td>
							<select name="tynt-where" id="tynt-where">
		';
		foreach(self::$defs['tynt-where'] as $k=>$v){
			echo '<option value="'.$k.'" ';
			echo ($k==$options['tynt-where']) ? ' selected="selected"':'';
			echo '>'.$v.'</option>';
		}		
		echo'							
							</select>
							<div>Default is WordPress footer, but you may want to load script in the header so it works before even page loads. Downside of loading script in the header is overall slower website response. Select last option if you want to paste code into template yourself.</div>
							<div>
								<strong>Selfinstall code:</strong>
								<pre>
if ( function_exists(\'tyntInsight\') ){
	tyntInsight();
}
								</pre>
							</div>
						</td>
					</tr>
					
				</tbody>
			</table>
			<p>For more information check out <a href="http://www.tynt.com/" target="_blank">http://www.tynt.com/</a>.
		';
		echo '
		<p class="submit"><input type="submit" id="action" class="button" value="Save Changes" name="action"></p>';
		echo '</form>';
		echo '</div>';
	}
	
	/**
	 * 
	 * @return Array()
	 */
	function activate(){
		add_option(self::$options['key'], serialize(self::$options),'','yes');
		return self::$options;
	}
	function deactivate(){
		delete_option(self::$options['key']);
	}	
	function getCode(){
		$code = '';
		$options=self::$options;
		$options=array_merge($options,unserialize(get_option($options['key'])));
		if(isset($options['tynt-id']) && !empty($options['tynt-id'])){
			$code='
				<script type="text/javascript">
				if(document.location.protocol==\'http:\'){
				 var Tynt=Tynt||[];Tynt.push(\''.$options['tynt-id'].'\');
				 (function(){var s=document.createElement(\'script\');s.async="async";s.type="text/javascript";s.src=\'http://tcr.tynt.com/ti.js\';var h=document.getElementsByTagName(\'script\')[0];h.parentNode.insertBefore(s,h);})();
				}
				</script>					
			';
		}		
		return $code;
	}
	function printTyntInsight(){
		echo self::getCode();
	}
}
add_action('init', array('VayamaTyntInsight','init'));

/**
 * 
 * @return string();
 */
function tyntInsight(){
	echo VayamaTyntInsight::getCode();	
}