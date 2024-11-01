<?php
/*
Plugin Name: 点点文章同步
Plugin URI: http://www.iztwp.com/wp2diandian.html
Description: 将wordpress发布的文章同步到点点
Version: 1.2
Author: 爱主题
Author URI: http://www.iztwp.com/
*/
/*  Copyright 2012  爱主题  (Homepage:http://www.iztwp.com/  E-Mail:whyun@vip.qq.com)  */

## 设置菜单 ##
add_action('admin_menu', 'dian_menu');
function dian_menu() {
	if(function_exists('add_submenu_page')) {
		add_submenu_page('options-general.php', '点点文章同步','点点文章同步', 'administrator', plugin_dir_path(__FILE__).'/dian-setting.php');
	}
}
## curl ##
function curl_post($url,$data){
    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $tmpInfo = curl_exec($curl);
    curl_close($curl);
    return $tmpInfo;
}
## slug ##
function dian_name_slug($post) {
    $post_data = get_post($post, ARRAY_A);
    $slug = $post_data['post_name'];
    return $slug;
}
## 添加同步 ##
add_action('publish_post', 'publish_post_to_dian',0); 
/* 发布文章 */
function publish_post_to_dian($post_ID){
	if( wp_is_post_revision($post_ID) ) return;
	if(!get_post_meta($post_ID, 'dian_postid',true)&&!get_post_meta($post_ID, 'dian_post',true)){
		query_posts( 'p='.$post_ID );
		while (have_posts()) : the_post();
		$content = get_the_content();
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		endwhile;
		$title = get_the_title($post_ID);
		$body = $content;
		if(get_option('dian_addurl')=='true'){
			$body .='<p>原文链接：<a href="'.get_permalink($post_ID).'" target="_blank" title="'.$title.'">'.get_permalink($post_ID).'</a></p>';
		}
		$posttags = get_the_tags($post_ID);
		$tags = "";
		if ($posttags){
			foreach($posttags as $tag){
				$tags = $tags . $tag->name . ",";
			}
		}
		$dian_slug = get_option('dian_slug');
		if($dian_slug=='1'){
			$slug = '';
		}elseif($dian_slug=='2'){
			$slug = $post_ID;
		}elseif($dian_slug=='3'){
			$slug = $post_ID.'.html';
		}elseif($dian_slug=='4'){
			$slug = dian_name_slug($post_ID);
		}elseif($dian_slug=='5'){
			$slug = dian_name_slug($post_ID).'.html';
		}
		$refresh_token = get_option('refresh_token');
		$refresh_url = "https://api.diandian.com/oauth/token?client_id=FWRFjbqiOJ&client_secret=0RXKV1FTMAUhSPAMoUNVevIhJL0C2JOiIrQl&grant_type=refresh_token&refresh_token=".$refresh_token;
		$formvars = '';
		$json = curl_post($refresh_url,$formvars);
		$refresh_info = json_decode($json,true);
		$refresh_token = $refresh_info['refresh_token'];
		update_option('refresh_token', $refresh_token);
		$access_token = $refresh_info['access_token'];
		$dian_postblog = get_option('dian_postblog');
		$form_url = "https://api.diandian.com/v1/blog/".$dian_postblog."/post";
		$formvars = "blogIdentity=".$dian_postblog."&access_token=".$access_token."&type=text&state=published&tag=".urlencode($tags)."&title=".urlencode($title)."&body=".urlencode($body)."&slug=".$slug;
		$post_json = curl_post($form_url,$formvars);
		$post_info = json_decode($post_json,true);
		$dian_postid = $post_info['response'];
		add_post_meta($post_ID, 'dian_post', 'true', true);
		add_post_meta($post_ID, 'dian_postid', $dian_postid, true);
	}
};
?>