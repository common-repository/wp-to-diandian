<?php
$base_name = plugin_basename( __FILE__ );
## If Form Is Submitted ##
if($_POST['Submit']&&$_POST['form']=='d-setting') {
	$dian_postblog  = trim($_POST['dian_postblog']);
	$dian_slug  = trim($_POST['dian_slug']);
	$dian_addurl  = trim($_POST['dian_addurl']);
	$update_dian_queries = array();
	$update_dian_text    = array();
	$update_dian_queries[] = update_option('dian_postblog', $dian_postblog);
	$update_dian_queries[] = update_option('dian_slug', $dian_slug);
	$update_dian_queries[] = update_option('dian_addurl', $dian_addurl);
	$update_dian_text[] = '文章同步博客域名';
	$update_dian_text[] = '链接形式';
	$update_dian_text[] = '添加原文链接';
	$i = 0;
	$text = '';
	foreach($update_dian_queries as $update_dian_query) {
		if($update_dian_query) {
			$text .= '<font color="green">'.$update_dian_text[$i].' 更新成功！</font><br />';
		}
		$i++;
	}
	if(empty($text)) {
		$text = '<font color="red">您对设置没有做出任何改动...</font>';
	}

}
if($_POST['Submit']&&$_POST['form']=='d-login'){
	$username = $_POST['username'];
	$pass = $_POST['pass'];
	$url = "https://api.diandian.com/oauth/token?client_id=FWRFjbqiOJ&client_secret=0RXKV1FTMAUhSPAMoUNVevIhJL0C2JOiIrQl&grant_type=password&username=".$username."&password=".$pass."&scope=read,write";
	$formvars ='';
	$json = curl_post($url,$formvars);
	$dian_info = json_decode($json,true);
	if($dian_info['error']){
		$return = '<font color="red">登录失败，请检查用户名或密码是否正确，并重新授权！</font>';
	}else{
		$refresh_token = $dian_info['refresh_token'];
		update_option('refresh_token', $refresh_token);
		$return = '<font color="green">成功授权！可以开始同步文章了！</font>';
	}
}
## Needed Variables ##
$dian_postblog = get_option('dian_postblog');
$dian_slug = get_option('dian_slug');
$dian_addurl = get_option('dian_addurl');
?>
<div class="wrap">
<style>
	.wrap .dian_icon{
		background: transparent url(<?php echo plugins_url( 'logo.png', __FILE__ ); ?>) 0 6px no-repeat;
		float: left;
		height: 33px;
		margin: 15px 0 10px 10px;
		width: 300px;
		padding-left:40px;
	}
	.dian-nav{display:block;margin-left:10px;}
	.dian-nav li{float:left;padding:3px 6px;margin-right:30px;font-size:15px;background: url(<?php echo get_option('home');?>/wp-admin/images/button-grad.png) repeat-x scroll left top #21759B;border-color: #298CBA;color: #FFFFFF;font-weight: bold;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);cursor:pointer;}
	#d-login{display:none;margin-top:30px;}
</style>
<h2 class="dian_icon">点点文章同步插件设置</h2>
<div class="clear"></div>
<div class="dian-nav">
	<ul>
		<li class="d-setting">设置选项</li>
        <li class="d-login">登录授权</li>
	</ul>
</div>
<div class="clear"></div>
<?php
	if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; }
	if(!empty($return)){ echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$return.'</p></div>';};
?>

<form id="d-setting" method="post" action="<?php echo admin_url('options-general.php?page='.plugin_basename(__FILE__)); ?>" style="width:70%;float:left;">
	<table class="form-table">
    	<tr><td valign="top" width="20%"><strong>文章同步博客域名</strong><br /></td>
        	<td><input type="text" id="dian_postblog" name="dian_postblog" value="<?php echo $dian_postblog ; ?>" size="20" />（如：iztwp.diandian.com）</td>
        </tr>
        <tr><td valign="top" width="20%"><strong>链接形式</strong><br /></td>
        	<td><select id="dian_slug" name="dian_slug">
                <option value ="1" <?php if($dian_slug=='1') echo 'selected="selected"';?>>默认</option>
                <option value ="2" <?php if($dian_slug=='2') echo 'selected="selected"';?>>文章ID</option>
                <option value ="3" <?php if($dian_slug=='3') echo 'selected="selected"';?>>文章ID.html</option>
                <option value ="4" <?php if($dian_slug=='4') echo 'selected="selected"';?>>文章别名</option>
              <option value ="5" <?php if($dian_slug=='5') echo 'selected="selected"';?>>文章别名.html</option>
                </select>
            </td>
        </tr> 
    	<tr><td valign="top" width="20%"><strong>添加原文链接</strong><br /></td>
        	<td><input type="checkbox" id="dian_addurl" name="dian_addurl" value="true" <?php if($dian_addurl == 'true') echo 'checked="checked"'; ?> /></td>
        </tr>
	</table>
    <br /> <br />
    <table>
        <tr><input type="hidden" name="form" value="d-setting" />
            <td><p class="submit">
                <input type="submit" name="Submit" value="保存设置" class="button-primary"/>
                </p>
            </td>
        </tr>
    </table>
</form>
<form method="post" id="d-login" action="<?php echo admin_url('options-general.php?page='.plugin_basename(__FILE__)); ?>" />
    <p>用户名：<input type="text" name="username"> （点点网登陆账号）</p>
    <p>密&nbsp;&nbsp;码：<input type="password" name="pass" /> （点点网登陆密码）</p>
    <input type="hidden" name="form" value="d-login" />
    <p><input type="submit" name="Submit" value="登录授权" class="button-primary"/></p>
</form>
</div>
<script>
	jQuery(document).ready(function(){
        jQuery('.dian-nav .d-login').click(function(){
			jQuery('form#d-setting').hide();
			jQuery('form#d-login').show();
		});
        jQuery('.dian-nav .d-setting').click(function(){
			jQuery('form#d-login').hide();
			jQuery('form#d-setting').show();
		});
    });
</script>