<?php
/*
Plugin Name: Dynamic Latest Post in Nav Menu
Plugin URI: http://hijiriworld.com/web/plugins/dynamic-latest-post-in-nav-menu/
Description: Add Custom Post Type's Dynamic Latest Post and Archive to Nav Menu.
Author: hijiri
Author URI: http://hijiriworld.com/web/
Version: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if( !class_exists( 'Dlpinm' ) )
{
	class Dlpinm
	{
		function __construct()
		{
			load_plugin_textdomain( 'dlpinm', false, basename( dirname( __FILE__ ) ) . '/lang' );
			add_action( 'admin_head-nav-menus.php', array( $this, 'dlpinm_metabox' ) );
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'dlpinm_setup_nav_menu_item' ) );
			add_filter( 'wp_nav_menu_objects', array( $this, 'dlpinm_filter' ), 0 );
			add_filter( 'wp_get_nav_menu_items', array( $this, 'dlpinm_filter' ) );
		}
		
		function dlpinm_filter( $items )
		{
			global $wpdb;
			
			foreach( $items as $key => $item ) {
				
				if ( $item->post_type == 'nav_menu_item' ) {
					
					// latest
					if ( $item->object == 'latest' ) {
						
						$sql = "SELECT ID 
							FROM $wpdb->posts 
							WHERE $wpdb->posts.post_type = '$item->type' AND $wpdb->posts.post_status = 'publish' 
							ORDER BY $wpdb->posts.menu_order, $wpdb->posts.post_date DESC 
							LIMIT 1";
						
						$post_id = $wpdb->get_var( $sql );
						$item->object_id = $post_id;
						$item->url = get_permalink( $post_id );
						
						// archive and all single
						if ( get_query_var( 'post_type' ) == $item->type ) {
							$item->classes[] = 'current-menu-item';
							$item->current = true;
						}
					}
					
					// archive
					if ( $item->object == 'archive' ) {
						
						$item->url = $item->post_content;
						//get_post_type_archive_link( $item->type );
						
						// archive and all single
						if( get_query_var( 'post_type' ) == $item->type ) {
							$item->classes[] = 'current-menu-item';
							$item->current = true;
						}
					}
					
				}
			}
			return $items;
		}
		
		function dlpinm_metabox()
		{
			add_meta_box( 'dlpinm_archive', __( 'Archive', 'dlpinm' ), array( $this, 'dlpinm_metabox_content_archive' ), 'nav-menus', 'side', 'default' );
			add_meta_box( 'dlpinm_latest', __( 'Latest', 'dlpinm' ), array( $this, 'dlpinm_metabox_content_latest' ), 'nav-menus', 'side', 'default' );
		}
		function dlpinm_metabox_content_archive()
		{
			$post_types = get_post_types( array( 'show_in_nav_menus' => true, 'has_archive' => true ), 'object' );
			
			unset( $post_types['page']); // exclude Page object
			
			if( $post_types ) {
				foreach ( $post_types as $post_type ) {
					$post_type->classes = array();
					$post_type->type = $post_type->name;
					$post_type->object_id = $post_type->name;
					$post_type->title = $post_type->labels->name;
					$post_type->object = 'archive';
					
					$post_type->menu_item_parent = null;
					$post_type->url = null;
					$post_type->target = null;
					$post_type->attr_title = null;
					$post_type->xfn = null;
					$post_type->db_id = null;
					$post_type->description = null;
				}
				$walker = new Walker_Nav_Menu_Checklist( array() );
?>
				<div id="dlpinm-archive" class="posttypediv">
					<div id="tabs-panel-dlpinm" class="tabs-panel tabs-panel-active">
						<ul id="dlpinm-checklist" class="categorychecklist form-no-clear">
							<?php //echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $post_types), 0, (object) array( 'walker' => $walker) ); ?>
							<?php echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $post_types ), 0, (object) array( 'walker' => $walker) ); ?>
							<?php //echo walk_nav_menu_tree( $post_types, 0, (object) array( 'walker' => $walker) ); ?>
						</ul>
					</div>
				</div>
				<p class="button-controls">
					<span class="add-to-menu">
						<!-- <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt=""> -->
						<input type="submit"<?php //disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-dlpinm-menu-item" id="submit-dlpinm-archive" />
					</span>
				</p>
<?php
			}
		}
		function dlpinm_metabox_content_latest()
		{
			$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'object' );
			
			unset( $post_types['page']); // exclude Page object
			
			if( $post_types ) {
				foreach ( $post_types as $post_type ) {
					$post_type->classes = array();
					$post_type->type = $post_type->name;
					$post_type->object_id = $post_type->name;
					$post_type->title = $post_type->labels->name;
					$post_type->object = 'latest';
					
					$post_type->menu_item_parent = null;
					$post_type->url = null;
					$post_type->target = null;
					$post_type->attr_title = null;
					$post_type->xfn = null;
					$post_type->db_id = null;
					$post_type->description = null;
				}
				$walker = new Walker_Nav_Menu_Checklist( array() );
?>
				<div id="dlpinm-latest" class="posttypediv">
					<div id="tabs-panel-dlpinm" class="tabs-panel tabs-panel-active">
						<ul id="dlpinm-checklist" class="categorychecklist form-no-clear">
							<?php echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $post_types ), 0, (object) array( 'walker' => $walker) ); ?>
						</ul>
					</div>
				</div>
				<p class="button-controls">
					<span class="add-to-menu">
						<!-- <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" /> -->
						<input type="submit"<?php //disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-dlpinm-menu-item" id="submit-dlpinm-latest" />
					</span>
				</p>
<?php
			}
		}
		
		function dlpinm_setup_nav_menu_item( $item )
		{
			if ( isset( $item->object ) ) {
				if ( $item->object == 'latest' ) {
					$item->type_label = __( 'Latest', 'dlpinm' );
	
				} else if ( $item->object == 'archive' ) {
					$item->type_label = __( 'Archive', 'dlpinm' );
					$item->description = get_post_type_archive_link( $item->type );
				}
			}
			return $item;
		}
	}
	
	$Dlpinm = new Dlpinm();
}

?>