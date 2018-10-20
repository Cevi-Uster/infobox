<?php

require_once('Chaeschtlizettel_Plugin.php');

class Chaeschtlizettel_REST_Server extends WP_REST_Controller {
 
  //The namespace and version for the REST SERVER
  var $my_namespace = 'chaeschtlizettel/v';
  var $my_version   = '1';

 
  public function register_routes() {
    $namespace = $this->my_namespace . $this->my_version;
    $base      = 'stufen';
    register_rest_route( $namespace, '/' . $base, array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_stufen' ),
          'permission_callback'   => array( $this, 'get_stufen_permission' )
        )
    )  );
  }
 
  // Register our REST Server
  public function hook_rest_server(){
    add_action( 'rest_api_init', array( $this, 'register_routes' ) );
  }
 
  public function get_stufen_permission(){
      // Everyone may read this information!
      return true;
  }
 
  public function get_stufen( WP_REST_Request $request ){
    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');
    $sql_stmt = "SELECT stufen_id, name FROM $table_name";
    //$sql = $wpdb->prepare($sql_stmt);
    $result = $wpdb->get_results($sql_stmt, OBJECT);
    return $result;
  }
}