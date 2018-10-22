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
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/
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
          'methods' => WP_REST_Server::READABLE,
          'callback'  => array( $this, 'get_stufen' ),
          'permission_callback'   => array( $this, 'get_stufen_permission' )
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
 
  public function get_stufen( WP_REST_Request $request ){
    $chaeschtlizettel_plugin = new Chaeschtlizettel_Plugin();
    global $wpdb;
    $table_name = $chaeschtlizettel_plugin->prefixTableName('stufen');
    $sql_stmt = "SELECT stufen_id, name FROM $table_name";
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
    $tableName = $chaeschtlizettel_plugin->prefixTableName('chaeschtlizettel');
    $sql_stmt = "SELECT * FROM $tableName WHERE stufen_id = %d";
    $sql = $wpdb->prepare($sql_stmt, $stufen_id);

    $result = $wpdb->get_results($sql);
    $chaeschtli = $result[0];    //return $chaeschtli;
    return $chaeschtli;
  }
}