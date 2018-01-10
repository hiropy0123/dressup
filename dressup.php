<?php
/*
Plugin Name: DressUp
Plugin URI:
Description: WordPress 共通ベース設定。このプラグインをベースにテーマファイルを構築しているため、このプラグインは無効化・削除しないでください。管理画面の整形。WordPressのコアファイル及びプラグインを自動更新します。WordPressGeneratorバージョンを非表示にします。
Version: 1.0.2
Author: Hiroki Nakashima
Author URI:
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Add Slug to body_class
add_filter( 'body_class', 'add_page_slug_class_name' );
function add_page_slug_class_name( $classes ) {
  if ( is_page() ) {
    $page = get_post( get_the_ID() );
    $classes[] = $page->post_name;
  }
  return $classes;
}
// ========================================

// 管理画面の編集
// 一覧に「サムネイル」「ID」「スラッグ」を追加
function add_pages_columns($columns) {
  $columns['postid'] = 'ID';
  $columns['slug'] = 'スラッグ';
	$columns['thumbnail'] = 'サムネイル';

  echo '<style type="text/css">
  .fixed .column-postid {width: 3rem;}
  .fixed .column-slug {width: 5%;}
	.fixed .column-thumbnail {width: 50px;}
  </style>';

  return $columns;
}
function add_pages_columns_row($column_name, $post_id) {
    if ( 'postid' == $column_name ) {
    echo $post_id;
  } elseif ( 'slug' == $column_name ) {
    $slug = get_post($post_id) -> post_name;
    echo $slug;
  } elseif ( 'thumbnail' == $column_name ) {
    $thumb = get_the_post_thumbnail($post_id, array(50, 50), 'thumbnail');
    echo ( $thumb ) ? $thumb : '－';
  }
}
add_filter( 'manage_pages_columns', 'add_pages_columns' );
add_action( 'manage_pages_custom_column', 'add_pages_columns_row', 10, 2 );

// ========================================
// 一覧画面から削除するカラム
add_filter( 'manage_pages_columns', 'delete_pages_column');
add_filter( 'manage_posts_columns', 'delete_posts_column');
function delete_posts_column($columns) {
    unset($columns['comments']);
    return $columns;
}
function delete_pages_column($columns) {
    unset($columns['author']);
		unset($columns['date']);
		unset($columns['comments']);
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
  $wp_admin_bar->remove_menu( 'updates' );
}
add_action('admin_bar_menu', 'remove_bar_menus', 201);

// 管理画面フッターを変更
function custom_admin_footer () {
    echo '';
}
add_filter( 'admin_footer_text', 'custom_admin_footer' );

// 管理画面フッターのバージョン番号を削除
function remove_footer_version() {
    remove_filter( 'update_footer', 'core_update_footer' );
}
add_action( 'admin_menu', 'remove_footer_version' );


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
