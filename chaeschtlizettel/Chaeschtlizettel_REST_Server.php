<?php
/*
    "Chaeschtlizettel Plugin" Copyright (C) 2018 Matthias Kunz v/o Funke  (email : funke.uster@cevi.ch)
    "Chaeschtlizettel Plugin" is derived from "WordPress Plugin Template" Copyright (C) 2018 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Chaeschtlizettel for WordPress.

    Chaeschtlizettel is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PUR/E.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/
require_once('Chaeschtlizettel_Plugin.php');

// Avoid the removal of the 'X-WP-Nonce' authentication header (see: https://github.com/WP-API/WP-API/issues/2538)
add_filter( 'rest_pre_dispatch', 'prefix_return_current_user' );
function prefix_return_current_user( $result ) {
  $user_id = get_current_user_id();
  $user = wp_set_current_user($user_id );
}

class Chaeschtlizettel_REST_Server extends WP_REST_Controller {
 
  //The namespace and version for the REST SERVER
  var $my_namespace = 'chaeschtlizettel/v';
  var $my_version   = '1';
 
  public function register_routes() {
    $namespace = $this->my_namespace . $this->my_version;
    $baseStufen      = 'stufen';
    register_rest_route( $namespace, '/' . $baseStufen, array(
      array(
          'methods' => WP_REST_Server::READABLE,
          'callback'  => array( $this, 'get_stufen' ),
          'permission_callback' => array( $this, 'get_stufen_permission' )
      )
    ));

    register_rest_route( $namespace, '/' . $baseStufen."/update/", array(
      array(
          'methods' => WP_REST_Server::EDITABLE,
          'callback'  => array( $this, 'update_stufe' ),
          'permission_callback' => array( $this, 'update_stufe_permission' )
      ),
      'schema' => array( $this,'get_update_stufen_schema')
    ));

    register_rest_route( $namespace, '/' . $baseStufen."/insert/", array(
      array(
          'methods' => WP_REST_Server::EDITABLE,
          'callback'  => array( $this, 'insert_stufe' ),
          'permission_callback' => array( $this, 'insert_stufe_permission' )
      ),
      'schema' => array( $this,'get_insert_stufen_schema')
    ));

    register_rest_route( $namespace, '/' . $baseStufen."/delete/", array(
      array(
          'methods' => WP_REST_Server::EDITABLE,
          'callback'  => array( $this, 'delete_stufe' ),
          'permission_callback' => array( $this, 'delete_stufe_permission' )
      ),
      'schema' => array( $this,'get_delete_stufen_schema')
    ));

    $baseStufenMember = 'stufenmember';
    register_rest_route( $namespace, '/' . $baseStufenMember, array(
      array(
          'methods' => WP_REST_Server::READABLE,
          'callback'  => array( $this, 'get_stufenmember' ),
          'permission_callback' => array( $this, 'get_stufenmember_permission' )
      )
    ));

    register_rest_route( $namespace, '/chaeschtlizettel/(?P<id>\d+)', array(
      array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => array( $this, 'get_chaeschtlizettel' ),
        'permission_callback'  => array( $this, 'get_chaeschtlizettel_permission' ),
        'args' => array(
        'id' => array(
        'validate_callback' => function($param, $request, $key) {
          return is_numeric( $param );
        }
        )
      ) 
    )
    )
  );

  }
 
  // Register our REST Server
  public function hook_rest_server(){
    add_action( 'rest_api_init', array( $this, 'register_routes' ) );
  }
 
  public function get_stufen_permission(){
      // Everyone may read this information!
      return true;
  }
 
  public function get_stufen(WP_REST_Request $request){
    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');
    $sql_stmt = "SELECT stufen_id, name, abteilung, jahrgang FROM $table_name";
    //$sql = $wpdb->prepare($sql_stmt);
    $result = $wpdb->get_results($sql_stmt, OBJECT);
    return $result;
  }

  public function update_or_delete_stufen_permission(){
      //return true;
    $nonce = (string) $request['_nonce'];
    return check_ajax_referer( 'wp_rest', '_nonce', false );
  }
 
  public function update_or_delete_stufen(WP_REST_Request $request){
    //$json_request = json_decode($request->get_params(), true);
    $action = $request->get_params()['action'];
    $stufen_id = $request->get_params()['stufen_id'];
    $name = $request->get_params()['name'];
    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');
    if ($action === 'edit' && isset($stufen_id) && isset($name)){
      $wpdb->show_errors(); 
      return $wpdb->update($table_name, array('name' => $name), array('stufen_id' => $stufen_id), array('%s'), array('%d'));
    } else if ($action === 'delete' && isset($stufen_id)){
      $wpdb->show_errors(); 
      return $wpdb->delete($table_name,  array('stufen_id' => $stufen_id), array('%d'));
    }
    //return 'bad request';
    return $json_request;
  }

  public function get_update_stufen_schema(){
    return file_get_contents(plugin_dir_path(__FILE__).'JSON_Schema_update_stufen.json');
  }

  public function update_stufe_permission(){
    return current_user_can('manage_options');
  }

  public function update_stufe(WP_REST_Request $request){
    $json_request = json_decode($request->get_body(), true);

    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');

    if (isset($json_request['stufen_id']) && isset($json_request['name'])) {
      $wpdb->show_errors(); 
      return $wpdb->update($table_name, array('name' => $json_request['name'], 
          'abteilung' => $json_request['abteilung'], 
          'jahrgang' => $json_request['jahrgang']), 
        array('stufen_id' => $json_request['stufen_id']), array('%s'), array('%d'));
    }
    return 'bad request';
  }

  public function get_insert_stufen_schema(){
    return file_get_contents(plugin_dir_path(__FILE__).'JSON_Schema_insert_stufen.json');
  }

  public function insert_stufe_permission(WP_REST_Request $request){
    return current_user_can('manage_options');
  }

  public function insert_stufe( WP_REST_Request $request ){
    $json_request = json_decode($request->get_body(), true);

    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    
    if (isset($json_request['name'])) {
      $wpdb->show_errors(); 
      $stufen_table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');
      $chaeschtlizettel_table_name = $chaeschtlizettel_plugin->prefixTableName('chaeschtlizettel');
      $status = $wpdb->insert($stufen_table_name, array('name' => $json_request['name'], 
          'erstellt' => current_time( 'mysql' ), 
          'abteilung' => $json_request['abteilung'], 
          'jahrgang' => $json_request['jahrgang']), 
        array('%s', '%s'));
      if ($status == 1){
        $sql_stmt = "SELECT stufen_id FROM $stufen_table_name WHERE name = %s";
        $sql = $wpdb->prepare($sql_stmt, $json_request['name']);
        $stufen_id = intval($wpdb->get_results($sql)[0]->stufen_id);
        
        $status = $wpdb->insert($chaeschtlizettel_table_name, array('stufen_id' => $stufen_id, 
          'wo' => 'undefined'), 
          array('%s', '%s'));
        return $status;
      }
    }
    return 'bad request';
  }


  public function get_delete_stufen_schema(){
    return file_get_contents(plugin_dir_path(__FILE__).'JSON_Schema_delete_stufen.json');
  }

  public function delete_stufe_permission(){
    return current_user_can('manage_options');
  }

  public function delete_stufe( WP_REST_Request $request ){
    $json_request = json_decode($request->get_body(), true);

    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
      $stufen_table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');
      $chaeschtlizettel_table_name = $chaeschtlizettel_plugin->prefixTableName('chaeschtlizettel');

    if (isset($json_request['stufen_id'])) {
      $wpdb->show_errors(); 
      if ($wpdb->delete($stufen_table_name, array('stufen_id' => $json_request['stufen_id']), array('%d'))){
       return $wpdb->delete($chaeschtlizettel_table_name, array('stufen_id' => $json_request['stufen_id']), array('%d'));
      }
    }
    return 'bad request';
  }

  public function get_stufenmember_permission(){
      // return current_user_can('manage_options');
      // Everyone may read this information!
      return true;
  }
 
  public function get_stufenmember(WP_REST_Request $request){
    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('match_user_stufen');
    $sql_stmt = "SELECT id, user_id, stufen_id, erstellt FROM $table_name";
    //$sql = $wpdb->prepare($sql_stmt);
    $result = $wpdb->get_results($sql_stmt, OBJECT);
    return $result;
  }

  public function get_chaeschtlizettel_permission(){
      // Everyone may read this information!
      return true;
  }

  public function get_chaeschtlizettel( WP_REST_Request $request ){
    
    $stufen_id = $request->get_param('id');
    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('chaeschtlizettel');
    $sql_stmt = "SELECT * FROM $table_name WHERE stufen_id = %d";
    $sql = $wpdb->prepare($sql_stmt, $stufen_id);

    $result = $wpdb->get_results($sql);
    $chaeschtli = $result[0];    //return $chaeschtli;
    return $chaeschtli;
  }
}