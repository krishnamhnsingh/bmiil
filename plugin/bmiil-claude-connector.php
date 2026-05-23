<?php
/**
 * Plugin Name: BMIIL Claude Connector
 * Version: 1.4.0
 */
defined('ABSPATH') || exit;
define('BMIIL_CLAUDE_SECRET', 'bmiil_claude_2026_xK9mP3qR7wN2vL8');

add_action('rest_api_init', function() {
    $ns = 'bmiil/v1';
    register_rest_route($ns,'/ping',['methods'=>'GET','callback'=>'bmiil_ping','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/info',['methods'=>'GET','callback'=>'bmiil_site_info','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/pages',['methods'=>'GET','callback'=>'bmiil_list_pages','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/pages/(?P<id>\d+)',['methods'=>'GET','callback'=>'bmiil_get_page','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/pages/(?P<id>\d+)',['methods'=>'POST','callback'=>'bmiil_update_page','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/templates',['methods'=>'GET','callback'=>'bmiil_list_templates','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/templates/(?P<id>\d+)',['methods'=>'GET','callback'=>'bmiil_get_template','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/templates/(?P<id>\d+)',['methods'=>'POST','callback'=>'bmiil_update_template','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/menus',['methods'=>'GET','callback'=>'bmiil_list_menus','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/menus/(?P<id>\d+)',['methods'=>'GET','callback'=>'bmiil_get_menu','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/search-replace',['methods'=>'POST','callback'=>'bmiil_search_replace','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/menus',                 ['methods'=>'GET', 'callback'=>'bmiil_list_menus',     'permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/fix-nav-links',         ['methods'=>'POST','callback'=>'bmiil_fix_nav_links','permission_callback'=>'bmiil_auth']);
        register_rest_route($ns,'/media',['methods'=>'GET','callback'=>'bmiil_list_media','permission_callback'=>'bmiil_auth']);
    register_rest_route($ns,'/set-favicon',['methods'=>'POST','callback'=>'bmiil_set_favicon','permission_callback'=>'bmiil_auth']);
});

function bmiil_auth($r){
    $t=$r->get_header('X-BMIIL-Token');
    if(!$t||!hash_equals(BMIIL_CLAUDE_SECRET,$t))return new WP_Error('unauthorized','Unauthorized',['status'=>401]);
    return true;
}
function bmiil_ok($d){return new WP_REST_Response(['success'=>true,'data'=>$d],200);}
function bmiil_err($m,$c=400){return new WP_REST_Response(['success'=>false,'error'=>$m],$c);}

function bmiil_write_edata($post_id,$data){
    global $wpdb;
    $json=wp_json_encode($data);if(!$json)return false;
    $exists=$wpdb->get_var($wpdb->prepare("SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",$post_id));
    if($exists)$r=$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value=%s WHERE post_id=%d AND meta_key='_elementor_data'",$json,$post_id));
    else $r=$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta}(post_id,meta_key,meta_value) VALUES(%d,'_elementor_data',%s)",$post_id,$json));
    update_post_meta($post_id,'_elementor_edit_mode','builder');
    delete_post_meta($post_id,'_elementor_css');
    delete_transient('elementor_cache');
    if(class_exists('\Elementor\Plugin'))\Elementor\Plugin::$instance->files_manager->clear_cache();
    return $r!==false;
}
function bmiil_read_edata($post_id){
    global $wpdb;
    $raw=$wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",$post_id));
    if(!$raw||$raw==='')return null;
    return json_decode($raw,true)?:null;
}

function bmiil_ping(){return bmiil_ok(['message'=>'BMIIL Connector v1.4 active','site'=>get_bloginfo('url'),'wp_version'=>get_bloginfo('version'),'time'=>current_time('mysql')]);}
function bmiil_site_info(){return bmiil_ok(['site_title'=>get_bloginfo('name'),'site_url'=>get_bloginfo('url'),'wp_version'=>get_bloginfo('version'),'front_page_id'=>(int)get_option('page_on_front'),'elementor_active'=>defined('ELEMENTOR_VERSION'),'elementor_pro'=>defined('ELEMENTOR_PRO_VERSION'),'theme'=>wp_get_theme()->get('Name'),'site_icon'=>(int)get_option('site_icon')]);}
function bmiil_list_pages(){$pages=get_posts(['post_type'=>'page','post_status'=>['publish','draft'],'posts_per_page'=>100,'orderby'=>'title','order'=>'ASC']);return bmiil_ok(array_map(fn($p)=>['id'=>$p->ID,'title'=>$p->post_title,'slug'=>$p->post_name,'status'=>$p->post_status,'url'=>get_permalink($p->ID),'template'=>get_post_meta($p->ID,'_wp_page_template',true)?:'default','has_elementor'=>get_post_meta($p->ID,'_elementor_edit_mode',true)==='builder','modified'=>$p->post_modified],$pages));}
function bmiil_get_page($request){$id=(int)$request->get_param('id');$p=get_post($id);if(!$p)return bmiil_err("Page $id not found.",404);return bmiil_ok(['id'=>$p->ID,'title'=>$p->post_title,'slug'=>$p->post_name,'status'=>$p->post_status,'url'=>get_permalink($id),'template'=>get_post_meta($id,'_wp_page_template',true)?:'default','has_elementor'=>get_post_meta($id,'_elementor_edit_mode',true)==='builder','elementor_data'=>bmiil_read_edata($id)]);}
function bmiil_update_page($request){$id=(int)$request->get_param('id');$p=get_post($id);if(!$p)return bmiil_err("Page $id not found.",404);$body=$request->get_json_params();$upd=[];if(isset($body['title'])){wp_update_post(['ID'=>$id,'post_title'=>sanitize_text_field($body['title'])]);$upd[]='title';}if(isset($body['status'])){wp_update_post(['ID'=>$id,'post_status'=>$body['status']]);$upd[]='status';}if(isset($body['template'])){update_post_meta($id,'_wp_page_template',sanitize_text_field($body['template']));$upd[]='template';}if(isset($body['elementor_data'])){if(bmiil_write_edata($id,$body['elementor_data']))$upd[]='elementor_data';else return bmiil_err('DB write failed.');}return bmiil_ok(['id'=>$id,'updated'=>$upd,'url'=>get_permalink($id)]);}
function bmiil_list_templates(){global $wpdb;$rows=$wpdb->get_results("SELECT p.ID,p.post_title,p.post_name,p.post_status,p.post_modified,pm.meta_value as tt FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='_elementor_template_type' WHERE p.post_type='elementor_library' AND p.post_status IN('publish','draft','private') ORDER BY p.post_title ASC LIMIT 200");return bmiil_ok(array_map(fn($r)=>['id'=>(int)$r->ID,'title'=>$r->post_title,'slug'=>$r->post_name,'status'=>$r->post_status,'template_type'=>$r->tt,'modified'=>$r->post_modified],$rows?:[]));}
function bmiil_get_template($request){$id=(int)$request->get_param('id');global $wpdb;$p=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d AND post_type='elementor_library' LIMIT 1",$id));if(!$p)return bmiil_err("Template $id not found.",404);return bmiil_ok(['id'=>(int)$p->ID,'title'=>$p->post_title,'slug'=>$p->post_name,'status'=>$p->post_status,'template_type'=>get_post_meta($id,'_elementor_template_type',true),'elementor_data'=>bmiil_read_edata($id),'modified'=>$p->post_modified]);}
function bmiil_update_template($request){$id=(int)$request->get_param('id');global $wpdb;$p=$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d AND post_type='elementor_library' LIMIT 1",$id));if(!$p)return bmiil_err("Template $id not found.",404);$body=$request->get_json_params();$upd=[];if(isset($body['title'])){wp_update_post(['ID'=>$id,'post_title'=>sanitize_text_field($body['title'])]);$upd[]='title';}if(isset($body['elementor_data'])){if(bmiil_write_edata($id,$body['elementor_data']))$upd[]='elementor_data';else return bmiil_err('DB write failed.');}return bmiil_ok(['id'=>$id,'updated'=>$upd]);}
function bmiil_list_menus(){return bmiil_ok(array_map(function($m){$locs=get_nav_menu_locations();$assigned=array_keys(array_filter($locs,fn($id)=>$id===$m->term_id));return['id'=>$m->term_id,'name'=>$m->name,'slug'=>$m->slug,'count'=>$m->count,'locations'=>$assigned];},wp_get_nav_menus()));}
function bmiil_get_menu($request){$id=(int)$request->get_param('id');$items=wp_get_nav_menu_items($id);if($items===false)return bmiil_err("Menu $id not found.",404);return bmiil_ok(array_map(fn($i)=>['id'=>$i->ID,'title'=>$i->title,'url'=>$i->url,'parent'=>$i->menu_item_parent,'order'=>$i->menu_order,'classes'=>$i->classes],$items));}
function bmiil_search_replace($request){global $wpdb;$body=$request->get_json_params();$search=$body['search']??'';$replace=$body['replace']??'';$dry=$body['dry_run']??true;if(!$search)return bmiil_err('search required.');$posts=$wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND meta_value LIKE %s",'%'.$wpdb->esc_like($search).'%'));$affected=[];foreach($posts as $row){$pid=$row->post_id;$raw=$wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",$pid));if(!$raw)continue;$new=str_replace($search,$replace,$raw);if($new!==$raw){$affected[]=['post_id'=>$pid,'title'=>get_the_title($pid),'count'=>substr_count($raw,$search)];if(!$dry)$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value=%s WHERE post_id=%d AND meta_key='_elementor_data'",$new,$pid));}}if(!$dry&&!empty($affected)){delete_transient('elementor_cache');if(class_exists('\Elementor\Plugin'))\Elementor\Plugin::$instance->files_manager->clear_cache();}return bmiil_ok(['search'=>$search,'replace'=>$replace,'dry_run'=>$dry,'affected'=>$affected,'count'=>count($affected),'message'=>$dry?'Dry run.':count($affected).' updated.']);}

function bmiil_list_media($request){
    $q=$request->get_param('q');
    $args=['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>100,'post_mime_type'=>'image'];
    if($q)$args['s']=$q;
    $items=get_posts($args);
    return bmiil_ok(array_map(fn($p)=>['id'=>$p->ID,'title'=>$p->post_title,'filename'=>basename(get_attached_file($p->ID)),'url'=>wp_get_attachment_url($p->ID),'mime'=>$p->post_mime_type],$items));
}

function bmiil_set_favicon($request){
    $body=$request->get_json_params();
    $attachment_id=(int)($body['attachment_id']??0);
    $filename=$body['filename']??'';
    if(!$attachment_id&&$filename){
        global $wpdb;
        $attachment_id=(int)$wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
            '%'.basename($filename).'%'
        ));
    }
    if(!$attachment_id)return bmiil_err('Provide attachment_id or filename.');
    update_option('site_icon',$attachment_id);
    $url=wp_get_attachment_url($attachment_id);
    return bmiil_ok(['attachment_id'=>$attachment_id,'favicon_url'=>$url,'message'=>'Favicon set successfully.']);
}

function bmiil_list_menus_full($request){
    $menus = wp_get_nav_menus();
    $out = [];
    foreach($menus as $menu){
        $items = wp_get_nav_menu_items($menu->term_id);
        $out[] = ['id'=>$menu->term_id,'name'=>$menu->name,'slug'=>$menu->slug,'count'=>$menu->count,
                  'items'=>array_map(fn($i)=>['id'=>$i->ID,'title'=>$i->title,'url'=>$i->url,'parent'=>$i->menu_item_parent,'order'=>$i->menu_order],$items?:[])];
    }
    return bmiil_ok($out);
}

function bmiil_fix_nav_links($request){
    global $wpdb;
    $body    = $request->get_json_params();
    $fixes   = $body['fixes'] ?? [];
    $updated = [];
    foreach($fixes as $fix){
        $old = $fix['old'] ?? '';
        $new = $fix['new'] ?? '';
        if(!$old||!$new) continue;
        // Find all nav_menu_item posts with this URL
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_menu_item_url' AND meta_value=%s",
            $old
        ));
        foreach($ids as $pid){
            update_post_meta($pid,'_menu_item_url',$new);
            $updated[] = ['post_id'=>(int)$pid,'old'=>$old,'new'=>$new];
        }
        // Also fix relative URLs
        $ids2 = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_menu_item_url' AND meta_value LIKE %s",
            '%'.$wpdb->esc_like(parse_url($old,PHP_URL_PATH)).'%'
        ));
        foreach($ids2 as $pid){
            $current = get_post_meta($pid,'_menu_item_url',true);
            if(strpos($current, parse_url($old,PHP_URL_PATH)) !== false && !in_array($pid, array_column($updated,'post_id'))){
                update_post_meta($pid,'_menu_item_url',$new);
                $updated[] = ['post_id'=>(int)$pid,'old'=>$current,'new'=>$new];
            }
        }
    }
    // Clear nav menu cache
    wp_cache_flush();
    return bmiil_ok(['updated'=>$updated,'count'=>count($updated)]);
}
