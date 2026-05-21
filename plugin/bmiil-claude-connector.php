<?php
/**
 * Plugin Name: BMIIL Claude Connector
 * Plugin URI:  https://bmiil.com
 * Description: Secure REST API bridge connecting Claude AI to WordPress.
 * Version:     1.1.0
 * Author:      BMIIL
 */

defined( 'ABSPATH' ) || exit;

define( 'BMIIL_CLAUDE_SECRET', 'bmiil_claude_2026_xK9mP3qR7wN2vL8' );

add_action( 'rest_api_init', function () {
    $ns = 'bmiil/v1';

    register_rest_route($ns, '/ping',                              ['methods'=>'GET', 'callback'=>'bmiil_ping',             'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/info',                              ['methods'=>'GET', 'callback'=>'bmiil_site_info',        'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/pages',                             ['methods'=>'GET', 'callback'=>'bmiil_list_pages',       'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/pages/(?P<id>\\d+)',               ['methods'=>'GET', 'callback'=>'bmiil_get_page',         'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/pages/(?P<id>\\d+)',               ['methods'=>'POST','callback'=>'bmiil_update_page',      'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/templates',                         ['methods'=>'GET', 'callback'=>'bmiil_list_templates',    'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/templates/(?P<id>\\d+)',           ['methods'=>'GET', 'callback'=>'bmiil_get_template',     'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/templates/(?P<id>\\d+)',           ['methods'=>'POST','callback'=>'bmiil_update_template',  'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/menus',                             ['methods'=>'GET', 'callback'=>'bmiil_list_menus',        'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/menus/(?P<id>\\d+)',              ['methods'=>'GET', 'callback'=>'bmiil_get_menu',          'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns, '/search-replace',                    ['methods'=>'POST','callback'=>'bmiil_search_replace',    'permission_callback'=>'bmiil_auth']);
} );

function bmiil_auth( $request ) {
    $token = $request->get_header('X-BMIIL-Token');
    if ( !$token || !hash_equals( BMIIL_CLAUDE_SECRET, $token ) )
        return new WP_Error('bmiil_unauthorized', 'Unauthorized', ['status'=>401]);
    return true;
}

function bmiil_ok($data)  { return new WP_REST_Response(['success'=>true,  'data'=>$data], 200); }
function bmiil_err($m,$c=400) { return new WP_REST_Response(['success'=>false, 'error'=>$m],   $c);  }

function bmiil_ping() {
    return bmiil_ok([
        'message'    => 'BMIIL Claude Connector is active.',
        'site'       => get_bloginfo('url'),
        'wp_version' => get_bloginfo('version'),
        'time'       => current_time('mysql'),
    ]);
}

function bmiil_site_info() {
    return bmiil_ok([
        'site_title'      => get_bloginfo('name'),
        'site_url'        => get_bloginfo('url'),
        'wp_version'      => get_bloginfo('version'),
        'front_page_id'   => (int)get_option('page_on_front'),
        'blog_page_id'    => (int)get_option('page_for_posts'),
        'elementor_active'=> defined('ELEMENTOR_VERSION'),
        'elementor_pro'   => defined('ELEMENTOR_PRO_VERSION'),
        'elementor_ver'   => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null,
        'theme'           => wp_get_theme()->get('Name'),
    ]);
}

function bmiil_list_pages() {
    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish','draft'],
        'posts_per_page' => 100,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    return bmiil_ok(array_map(function($p) {
        return [
            'id'            => $p->ID,
            'title'         => $p->post_title,
            'slug'          => $p->post_name,
            'status'        => $p->post_status,
            'url'           => get_permalink($p->ID),
            'template'      => get_post_meta($p->ID,'_wp_page_template',true) ?: 'default',
            'has_elementor' => get_post_meta($p->ID,'_elementor_edit_mode',true) === 'builder',
            'modified'      => $p->post_modified,
        ];
    }, $pages));
}

function bmiil_get_page( $request ) {
    $id = (int)$request->get_param('id');
    $p  = get_post($id);
    if (!$p) return bmiil_err("Page $id not found.", 404);
    $raw = get_post_meta($id, '_elementor_data', true);
    return bmiil_ok([
        'id'             => $p->ID,
        'title'          => $p->post_title,
        'slug'           => $p->post_name,
        'status'         => $p->post_status,
        'url'            => get_permalink($id),
        'template'       => get_post_meta($id,'_wp_page_template',true) ?: 'default',
        'has_elementor'  => get_post_meta($id,'_elementor_edit_mode',true) === 'builder',
        'elementor_data' => $raw ? json_decode($raw, true) : null,
    ]);
}

function bmiil_update_page( $request ) {
    $id   = (int)$request->get_param('id');
    $p    = get_post($id);
    if (!$p) return bmiil_err("Page $id not found.", 404);
    $body = $request->get_json_params();
    $upd  = [];
    if (isset($body['title']))    { wp_update_post(['ID'=>$id,'post_title'=>sanitize_text_field($body['title'])]); $upd[]='title'; }
    if (isset($body['status']))   { wp_update_post(['ID'=>$id,'post_status'=>$body['status']]); $upd[]='status'; }
    if (isset($body['template'])) { update_post_meta($id,'_wp_page_template',sanitize_text_field($body['template'])); $upd[]='template'; }
    if (isset($body['elementor_data'])) {
        $json = is_array($body['elementor_data']) ? wp_json_encode($body['elementor_data']) : $body['elementor_data'];
        update_post_meta($id, '_elementor_data', $json);
        delete_post_meta($id, '_elementor_css');
        if (class_exists('\\Elementor\\Plugin'))
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        $upd[]='elementor_data';
    }
    return bmiil_ok(['id'=>$id, 'updated'=>$upd, 'url'=>get_permalink($id)]);
}

function bmiil_list_templates() {
    // Use direct DB query to bypass any post type registration issues
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_name, p.post_status, p.post_modified,
                pm.meta_value as template_type
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_elementor_template_type'
         WHERE p.post_type = 'elementor_library'
         AND p.post_status IN ('publish','draft','private')
         ORDER BY p.post_title ASC
         LIMIT 200"
    );
    $result = array_map(function($r) {
        return [
            'id'            => (int)$r->ID,
            'title'         => $r->post_title,
            'slug'          => $r->post_name,
            'status'        => $r->post_status,
            'template_type' => $r->template_type,
            'modified'      => $r->post_modified,
        ];
    }, $rows ?: []);
    return bmiil_ok($result);
}

function bmiil_get_template( $request ) {
    $id  = (int)$request->get_param('id');
    global $wpdb;
    $p   = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE ID=%d AND post_type='elementor_library' LIMIT 1", $id
    ));
    if (!$p) return bmiil_err("Template $id not found.", 404);
    $raw = get_post_meta($id, '_elementor_data', true);
    return bmiil_ok([
        'id'             => (int)$p->ID,
        'title'          => $p->post_title,
        'slug'           => $p->post_name,
        'status'         => $p->post_status,
        'template_type'  => get_post_meta($id,'_elementor_template_type',true),
        'elementor_data' => $raw ? json_decode($raw, true) : null,
        'modified'       => $p->post_modified,
    ]);
}

function bmiil_update_template( $request ) {
    $id   = (int)$request->get_param('id');
    global $wpdb;
    $p    = $wpdb->get_row($wpdb->prepare(
        "SELECT ID,post_title FROM {$wpdb->posts} WHERE ID=%d AND post_type='elementor_library' LIMIT 1", $id
    ));
    if (!$p) return bmiil_err("Template $id not found.", 404);
    $body = $request->get_json_params();
    $upd  = [];
    if (isset($body['title'])) { wp_update_post(['ID'=>$id,'post_title'=>sanitize_text_field($body['title'])]); $upd[]='title'; }
    if (isset($body['elementor_data'])) {
        $json = is_array($body['elementor_data']) ? wp_json_encode($body['elementor_data']) : $body['elementor_data'];
        update_post_meta($id, '_elementor_data', $json);
        delete_post_meta($id, '_elementor_css');
        if (class_exists('\\Elementor\\Plugin'))
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        $upd[]='elementor_data';
    }
    return bmiil_ok(['id'=>$id, 'updated'=>$upd]);
}

function bmiil_list_menus() {
    $menus = wp_get_nav_menus();
    return bmiil_ok(array_map(function($m) {
        $locs = get_nav_menu_locations();
        $assigned = array_keys(array_filter($locs, fn($id) => $id === $m->term_id));
        return ['id'=>$m->term_id, 'name'=>$m->name, 'slug'=>$m->slug, 'count'=>$m->count, 'locations'=>$assigned];
    }, $menus));
}

function bmiil_get_menu( $request ) {
    $id    = (int)$request->get_param('id');
    $items = wp_get_nav_menu_items($id);
    if ($items === false) return bmiil_err("Menu $id not found.", 404);
    return bmiil_ok(array_map(function($i) {
        return ['id'=>$i->ID, 'title'=>$i->title, 'url'=>$i->url, 'parent'=>$i->menu_item_parent, 'order'=>$i->menu_order, 'classes'=>$i->classes];
    }, $items));
}

function bmiil_search_replace( $request ) {
    global $wpdb;
    $body    = $request->get_json_params();
    $search  = $body['search']  ?? '';
    $replace = $body['replace'] ?? '';
    $dry     = $body['dry_run'] ?? true;
    if (!$search) return bmiil_err('search is required.');
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($search) . '%'
    ));
    $affected = [];
    foreach ($posts as $row) {
        $pid  = $row->post_id;
        $data = get_post_meta($pid, '_elementor_data', true);
        $new  = str_replace($search, $replace, $data);
        if ($new !== $data) {
            $affected[] = ['post_id'=>$pid, 'title'=>get_the_title($pid), 'count'=>substr_count($data,$search)];
            if (!$dry) { update_post_meta($pid,'_elementor_data',$new); delete_post_meta($pid,'_elementor_css'); }
        }
    }
    if (!$dry && !empty($affected) && class_exists('\\Elementor\\Plugin'))
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    return bmiil_ok(['search'=>$search,'replace'=>$replace,'dry_run'=>$dry,'affected'=>$affected,'count'=>count($affected),
        'message'=>$dry ? 'Dry run — set dry_run:false to apply.' : count($affected).' template(s) updated.']);
}
