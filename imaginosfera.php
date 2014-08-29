<?php
/**
 * Plugin Name: Imaginosfera
 * Plugin URI: http://imaginosfera.com.br/
 * Description: Todas as funções para personalizacao do WordPress para a Imaginosfera
 * Author: Valerio Souza
 * Author URI: https://valeriosouza.com.br
 * Version: 1.0.0
 * License: GPLv2 or later
 */


//Funções basicas do WordPress, algumas estão comentadas por padrão, pois poderá dar conflito com wp-config.php

//Para Ativar debug abra p wp-config e muda o define('WP_DEBUG', false); para define('WP_DEBUG', true);

//Ativar debug em arquivo log, que ficara em wp-content/debug.log
define(	'WP_DEBUG_LOG', true);
//Ativar display do debug, que mostrará os erros na tela
define(	'WP_DEBUG_DISPLAY', false);
//Ativar debug de scripts
define(	'SCRIPT_DEBUG', false);
//Aumentar memoria PHP, pode não funcionar em alguns servidores
define( 'WP_MEMORY_LIMIT', '256M' );
//Desativa a atualização pelo painel e a instalação de plugins pelo mesmo, também desativa edição de código pelo painel.
//Para ativar, basta comentar a linha com //
//O aviso ainda continuará aparecendo com novas versões, para esconder o aviso, desconte a linha da função imaginosfera_hide_update_notice
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', true );

//Actions and Filters
//Para desativar alguma dessa funções, basta comentar a linha referente a função abaixo, exemplo a linha imaginosfera_remove_dashboard_meta
//Primeira parte identifica se é Action ou Filter. Leia http://codex.wordpress.org/Plugin_API/Action_Reference e http://codex.wordpress.org/Plugin_API/Filter_Reference
//Segunda parte, a ação que o WordPress irá executar, referente a action ou filter
//Terceira parte a função a ser executada, atraves do nome pode descobrir qual quer usar ou não
//Para ler sobre qualquer action ou filter e seus parametros, basta procurar em http://codex.wordpress.org/ o nome do hook, exemplo 'admin_bar_menu' veja em http://codex.wordpress.org/Class_Reference/WP_Admin_Bar

add_action(	'init', 						'imaginosfera_output_buffer');
add_action( 'login_enqueue_scripts', 		'imaginosfera_login_logo' );
add_action( 'login_enqueue_scripts', 		'imaginosfera_login_stylesheet' );
add_action(	'admin_init', 					'imaginosfera_redirect_user_on_role');
add_action( 'admin_menu', 					'imaginosfera_remove_menu_pages' );
//add_action( 'admin_init', 				'imaginosfera_remove_dashboard_meta' );
add_action( 'wp_dashboard_setup', 			'imaginosfera_add_dashboard_widgets' );
add_action(	'wp_logout',					'imaginosfera_logout_redirect');
add_action(	'admin_bar_menu', 				'imaginosfera_add_toolbar_items', 100);
//add_action(	'admin_menu', 				'imaginosfera_hide_update_notice');
add_action(	'admin_footer',					'imaginosfera_posts_status_color');
add_action( 'wp_footer',                    'imaginosfera_add_googleanalytics');
add_filter( 'login_headerurl', 				'imaginosfera_login_logo_url' );
add_filter( 'login_headertitle', 			'imaginosfera_login_logo_url_title' );
add_filter( 'get_user_option_admin_color', 	'imaginosfera_change_admin_color' ); 
//add_filter( 'show_admin_bar' , 			'imaginosfera_hide_admin_bar');
add_filter(	'menu_order', 					'imaginosfera_custom_menu_order');
add_filter(	'admin_footer_text', 			'imaginosfera_change_footer_admin');
add_filter( 'admin_bar_menu', 				'imaginosfera_replace_ola',25 );



//Funcoes

function imaginosfera_login_logo() { ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(<?php echo plugin_dir_url(__FILE__);?>/assets/img/logo-imaginosfera.png);
            padding-bottom: 30px;
        }
    </style>
<?php }

function imaginosfera_login_stylesheet() {
    wp_enqueue_style( 'custom-login', plugin_dir_url(__FILE__) . '/assets/css/style-login.css' );
}

function imaginosfera_login_logo_url() {
    return home_url();
}

function imaginosfera_login_logo_url_title() {
    return 'Imaginosfera';
}

function imaginosfera_output_buffer() {
    ob_start();
}

function imaginosfera_redirect_user_on_role()
{
	
    global $current_user;
    get_currentuserinfo();
    
    //Para saber mais sobre os niveis, leia http://codex.wordpress.org/pt-br:Pap%C3%A9is_e_Capacidades
    //Se o usuario de login for Subscriber/Assinante
    if ($current_user->user_level == 0)
    {
        wp_redirect( home_url() ); exit;
    }
    //Se o usuario de login for Contributor/Colaborador
    /*if ($current_user->user_level > 1)
    {
        wp_redirect( home_url() ); exit;
    }

    //Se o usuario de login for Editor
    if ($current_user->user_level > 8)
    {
        wp_redirect( home_url() ); exit;
    }*/
}

function imaginosfera_remove_menu_pages() {
	global $user_ID;
	
	//Para saber mais sobre os niveis, leia http://codex.wordpress.org/pt-br:Pap%C3%A9is_e_Capacidades
	if ( current_user_can( 'editor' ) ) {
	//Inclua o final da url dentro do wp-admin. Exemplo, pra posts a url fica (seudominio.com/wp-admin/edit.php), use somente edit.php. 
	 remove_menu_page('tools.php' );
	 remove_menu_page('edit-comments.php');
	 remove_menu_page('edit.php');
	 } 
}

function imaginosfera_remove_dashboard_meta() {
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');//since 3.8
}

function imaginosfera_add_dashboard_widgets() {

    wp_add_dashboard_widget(
                 'dashboard_estrada',        
                 'Bem vindos',       
                 'imaginosfera_dashboard_widget_function'
        );  
}


function imaginosfera_dashboard_widget_function() {

    echo "Bem vindos à Dashboard";
    echo "Pode incluir vários linhas com echo, usar <br> para nova linha, ou <p>para paragrafo</p>";
    echo "Funciona HTML, CSS e PHP";
}

function imaginosfera_change_admin_color ( $result )  { 
   return  'coffee' ; 
   //Para mais cores, instale o plugin http://wordpress.org/plugins/admin-color-schemes/
}

function imaginosfera_logout_redirect(){
  wp_redirect( home_url() );
  exit();
}

function imaginosfera_hide_admin_bar(){ return false; } 

function imaginosfera_custom_menu_order($menu_ord) {
    if (!$menu_ord) return true;
    
    //Inclua o caminho da URL do wp-admin que quer ordenar.
    //Inclua o final da url dentro do wp-admin. Exemplo, pra posts a url fica (seudominio.com/wp-admin/edit.php), use somente edit.php. 
    return array(
        'index.php', // Dashboard
        'separator1', // First separator
        'edit.php', // Posts
        'edit.php?post_type=page', // Pages
        'separator2', // First separator
        'themes.php', // Appearance
        'link-manager.php', // Links
        'upload.php', // Media
        'users.php', // Users
        'edit-comments.php', // Comments
        'plugins.php', // Plugins
        'tools.php', // Tools
        'options-general.php', // Settings
        'separator-last', // Last separator
    );
}

function imaginosfera_add_toolbar_items($admin_bar){
    $admin_bar->add_menu( array(
        'id'    => 'imaginosfera-url',
        'title' => 'Acessar Imaginosfera',
        'href'  => 'http://imaginosfera.com.br/',
        'meta'  => array(
            'title' => __('Acessar Imaginosfera'),            
        ),
    ));
}

function imaginosfera_change_footer_admin () {
  echo 'Desenvolvido orgulhosamente em Belo Horizonte pela <a target="_blank" href="http://imaginosfera.com.br/">Imaginosfera</a>, usando WordPress <3';
}

function imaginosfera_hide_update_notice() {
	remove_action( 'admin_notices', 'update_nag', 3 );
}

function imaginosfera_replace_ola( $wp_admin_bar ) {
    $my_account=$wp_admin_bar->get_node('my-account');
    $newtitle = str_replace( 'Olá', 'Opa', $my_account->title );            
    $wp_admin_bar->add_node( array(
        'id' => 'my-account',
        'title' => $newtitle,
    ) );
}

function imaginosfera_posts_status_color(){
?>
    <style>
    .status-draft{background: #FCE3F2 !important;}
    .status-pending{background: #87C5D6 !important;}
    .status-publish{/* Nenhum background. Manter as cores alternadas */}
    .status-future{background: #C6EBF5 !important;}
    .status-private{background:#F2D46F;}
    </style>
<?php
}

function imaginosfera_add_googleanalytics() { ?>
<script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-xxxxxxxx-xx', 'auto');
          ga('send', 'pageview');

        </script>
<?php } 