<?php
/*
Plugin Name: DressUp
Plugin URI:
Description: 管理画面の整形。WordPressのコアファイル及びプラグインを自動更新します。WordPressGeneratorバージョンを非表示にします。
Version: 1.0
Author: Hiroki Nakashima
Author URI:
License: GPL2
*/


// 管理画面の編集
add_filter('manage_posts_columns', 'posts_columns_id', 5);
add_action('manage_posts_custom_column', 'posts_custom_id_columns', 5, 2);
add_filter('manage_pages_columns', 'posts_columns_id', 5);
add_action('manage_pages_custom_column', 'posts_custom_id_columns', 5, 2);

function posts_columns_id($defaults){
    $defaults['wps_post_id'] = __('ID');
    $defaults['slug'] = "スラッグ";
    return $defaults;
}
function posts_custom_id_columns($column_name, $id){
    if($column_name === 'wps_post_id'){
      echo $id;
    }
    if( $column_name == 'slug' ) {
        $post = get_post($post_id);
        $slug = $post->post_name;
        echo esc_attr($slug);
    }
}

// ========================================

// 一覧画面から削除するカラム
add_filter( 'manage_pages_columns', 'delete_column');
add_filter( 'manage_posts_columns', 'delete_column');
function delete_column($columns) {
    unset($columns['author'],$columns['comments']);
    return $columns;
}

// ダッシュボードの概要にカスタム投稿も表示
add_action( 'dashboard_glance_items', 'add_custom_post_dashboard_widget' );
function add_custom_post_dashboard_widget() {
	$args = array(
		'public' => true,
		'_builtin' => false
	);
	$output = 'object';
	$operator = 'and';

	$post_types = get_post_types( $args, $output, $operator );
	foreach ( $post_types as $post_type ) {
		$num_posts = wp_count_posts( $post_type->name );
		$num = number_format_i18n( $num_posts->publish );
		$text = _n( $post_type->labels->singular_name, $post_type->labels->name, intval( $num_posts->publish ) );
		if ( current_user_can( 'edit_posts' ) ) {
			$output = '<a href="edit.php?post_type=' . $post_type->name . '">' . $num . '&nbsp;' . $text . '</a>';
		}
		echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
	}
}

// ========================================

// remove admin menus
function remove_admin_menus() {
    global $menu;
    unset($menu[25]);       // コメント
}
add_action('admin_menu', 'remove_admin_menus');

// ========================================

//管理バーの項目削除
function remove_bar_menus( $wp_admin_bar ) {
  $wp_admin_bar->remove_menu( 'wp-logo' );
  $wp_admin_bar->remove_menu( 'comments' );
  $wp_admin_bar->remove_menu( 'customize' );
  // $wp_admin_bar->remove_menu( 'updates' );
}
add_action('admin_bar_menu', 'remove_bar_menus', 201);


// ========================================

//スマホ表示分岐
function is_mobile(){
    $useragents = array(
        'iPhone', // iPhone
        'iPod', // iPod touch
        'Android.*Mobile', // 1.5+ Android *** Only mobile
        'Windows.*Phone', // *** Windows Phone
        'dream', // Pre 1.5 Android
        'CUPCAKE', // 1.5+ Android
        'blackberry9500', // Storm
        'blackberry9530', // Storm
        'blackberry9520', // Storm v2
        'blackberry9550', // Storm v2
        'blackberry9800', // Torch
        'webOS', // Palm Pre Experimental
        'incognito', // Other iPhone browser
        'webmate' // Other iPhone browser

    );
    $pattern = '/'.implode('|', $useragents).'/i';
    return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
}

//iPad条件分岐
function is_ipad() {
	$is_ipad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
	if ($is_ipad) {
		return true;
	} else {
		return false;
	}
}

// remove generator
remove_action('wp_head','wp_generator');
// remove EditURI
remove_action('wp_head','rsd_link');
// remove wlwmanifest
remove_action('wp_head', 'wlwmanifest_link');
// remove wp version param from any enqueued scripts
function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );

// auto update
add_filter( 'allow_major_auto_core_updates', '__return_true' );
add_filter( 'auto_update_plugin', '__return_true' );
