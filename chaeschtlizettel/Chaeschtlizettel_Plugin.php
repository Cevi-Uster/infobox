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

include_once('Chaeschtlizettel_LifeCycle.php');
include_once('Chaeschtlizettel_REST_Server.php');


class Chaeschtlizettel_Plugin extends Chaeschtlizettel_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            //'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            //'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
            //'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'), 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr) > 1) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'Chäschtlizettel';
    }

    protected function getMainPluginFileName() {
        return 'chaeschtlizettel.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    public function installDatabaseTables() {
        global $wpdb;

             //$wpdb->show_errors();

        $charset_collate = $wpdb->get_charset_collate();

        $tableName = $this->prefixTableName('stufen');
        echo "db ".$tableName." erstellen";
        $wpdb->query("CREATE TABLE IF NOT EXISTS $tableName (
            stufen_id INTEGER(16) NOT NULL AUTO_INCREMENT,
            erstellt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            name VARCHAR(100),
            abteilung VARCHAR(30),
            jahrgang INTEGER,
            PRIMARY KEY (stufen_id)
        )$charset_collate;");

        $tableName = $this->prefixTableName('match_user_stufen');
        echo "db ".$tableName." erstellen";
        $wpdb->query("CREATE TABLE IF NOT EXISTS $tableName (
            id INTEGER(16) NOT NULL AUTO_INCREMENT,
            user_id INTEGER(16) NOT NULL,
            stufen_id INTEGER(16) NOT NULL,
            erstellt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            PRIMARY KEY  (id)
        )$charset_collate;");

        $tableName = $this->prefixTableName('chaeschtlizettel');
        echo "db ".$tableName." erstellen";
        $wpdb->query("CREATE TABLE IF NOT EXISTS $tableName (
            stufen_id INTEGER(16) NOT NULL,
            geaendert datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            von datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            bis datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            wo VARCHAR(100) NOT NULL,
            infos text,
            mitnehmen text,
            PRIMARY KEY (stufen_id)
        )$charset_collate;");

    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    public function unInstallDatabaseTables() {
        global $wpdb;
        $tableName = $this->prefixTableName('stufen');
        $wpdb->query("DROP TABLE IF EXISTS $tableName");

        $tableName = $this->prefixTableName('match_user_stufen');
        $wpdb->query("DROP TABLE IF EXISTS $tableName");

        $tableName = $this->prefixTableName('chaeschtlizettel');
        $wpdb->query("DROP TABLE IF EXISTS $tableName");

    }

    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
        global $wpdb;
        $wpdb->show_errors();
        $upgradeOk = true;
        $savedVersion = $this->getVersionSaved();

        if ($this->isVersionLessThan($savedVersion, '0.3')) {


          $tableName = $this->prefixTableName('stufen');
          if (!$this->tableColumnExists($tableName, "abteilung")){
            $upgradeOk  = $upgradeOk && $wpdb->query("ALTER TABLE $tableName ADD COLUMN abteilung VARCHAR(30)");
          }

          if (!$this->tableColumnExists($tableName, "jahrgang")){
            $upgradeOk  = $upgradeOk && $wpdb->query("ALTER TABLE $tableName ADD COLUMN jahrgang INTEGER");
          }

          // add column to split infos into infos and mitnehmen
          $tableName = $this->prefixTableName('chaeschtlizettel');
          if (!$this->tableColumnExists($tableName, "mitnehmen")){
            $upgradeOk  = $upgradeOk && $wpdb->query("ALTER TABLE $tableName ADD COLUMN mitnehmen text");
          }
        }


        // Post-upgrade, set the current version in the options
        $codeVersion = $this->getVersion();
        if ($upgradeOk && $savedVersion != $codeVersion) {
            $this->saveInstalledVersion();
        }

    }

    /**
     * Returns true if a database table column exists. Otherwise returns false.
     *
     * @link http://stackoverflow.com/a/5943905/2489248
     * @global wpdb $wpdb
     *
     * @param string $table_name Name of table we will check for column existence.
     * @param string $column_name Name of column we are checking for.
     *
     * @return boolean True if column exists. Else returns false.
     */
    function tableColumnExists( $table_name, $column_name ) {
      global $wpdb;
      $column = $wpdb->get_results( $wpdb->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
        DB_NAME, $table_name, $column_name) );
      if ( ! empty( $column ) ) {
        return true;
      }
      return false;
    }

    public function showChaeschtli($atts){
      try {
        global $wpdb;

        $stufenId = intval($atts['stufenid']);

        // stufen name suchen
        $tableName = $this->prefixTableName('stufen');
        $sql_stmt = "SELECT name FROM $tableName WHERE stufen_id = %s";
        $sql = $wpdb->prepare($sql_stmt, $atts['stufenid']);
        $stufenName = $wpdb->get_results($sql)[0]->name;

        // chäschtli infos suchen
        $tableName = $this->prefixTableName('chaeschtlizettel');
        $sql_stmt = "SELECT * FROM $tableName WHERE stufen_id = %d";
        $sql = $wpdb->prepare($sql_stmt, $stufenId);

        $result = $wpdb->get_results($sql);
        $chaeschtli = $result[0];

        // datum formatieren
        $von = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->von);
        $bis = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->bis);

        if($von != NULL || $bis != NULL){
          if($von->format('Y-m-d') == $bis->format('Y-m-d')){
            $zeit = $von->format('j.m.Y   H:i').' - '.$bis->format('H:i');
          }else{
            $zeit = $von->format('j.m.Y   H:i').' - '.$bis->format('j.m.Y H:i');
          }

          $now = date('Y-m-d');
          if($von->format('Y-m-d') < $now){
            $expired = true;
          }else{
            $expired = false;
          }
          ob_start();
            include("forms/chae_public_chaeschtlizettel.php");
          return ob_get_clean();

        }
      } catch (Exception $e) {
        echo 'Fehler entdeckt Hurra: ',  $e->getMessage(), "\n";
      }
    }

    /**
     * Create the function to output the contents of our Dashboard Widget.
     */
    public function example_dashboard_widget_function() {
      try{
        // Display dashboard widget content
        $user = get_current_user_id();

        global $wpdb;

        // stufen id suchen
        $tableName = $this->prefixTableName('match_user_stufen');
        $sql_stmt = "SELECT stufen_id FROM $tableName WHERE user_id = %s";
        $sql = $wpdb->prepare($sql_stmt,
                              $user
                              );

        $stufenId = intval($wpdb->get_results($sql)[0]->stufen_id);

        $tableName = $this->prefixTableName('chaeschtlizettel');
        $sql_stmt = "SELECT * FROM $tableName WHERE stufen_id = %d";
        $sql = $wpdb->prepare($sql_stmt,
                              $stufenId
                              );
        //echo $sql;

        $result = $wpdb->get_results($sql);
        $chaeschtli = $result[0];

        if($chaeschtli != NULL){
          $last_update = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->geaendert)->format('j.m.Y H:i');
        }//var_dump($chaeschtli);

        // datum formatieren
        $von = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->von);
        $bis = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->bis);

        $now = date('Y-m-d');
        if($von != NULL || $bis != NULL){
          if($von->format('Y-m-d') < $now){
            $status = "abgelaufen";
            $chaeschtli->infos = "";
            $chaeschtli->mitnehmen = "";
            $chaeschtli->wo = "";
            $von = DateTime::createFromFormat('H:i', '14:00');
            $bis = DateTime::createFromFormat('H:i', '17:00');
          }else{
            $status = "noch gültig";
          }
        }

      }catch (Exception $e) {
        echo 'Fehler entdeckt Hurra: ',  $e->getMessage(), "\n";
      }

      include("forms/chae_dash_chaeschtlizeddel.php");
    }

    /**
    * Add a widget to the dashboard.
    *
    * This function is hooked into the 'wp_dashboard_setup' action below.
    */
   public function example_add_dashboard_widgets() {
     // stufenname hohlen
     // Display dashboard widget content
     $user = get_current_user_id();

     global $wpdb;

     // stufen id suchen
     $tableName = $this->prefixTableName('match_user_stufen');
     $sql_stmt = "SELECT stufen_id FROM $tableName WHERE user_id = %s";
     $sql = $wpdb->prepare($sql_stmt,
                           $user
                           );

     $stufenId = intval($wpdb->get_results($sql)[0]->stufen_id);

     $tableName = $this->prefixTableName('stufen');
     $sql_stmt = "SELECT * FROM $tableName WHERE stufen_id = %d";
     $sql = $wpdb->prepare($sql_stmt,
                           $stufenId
                           );

     $result = $wpdb->get_results($sql);
     $reqStufe = $result[0];

     wp_add_dashboard_widget(
                    'chae_dash',         // Widget slug.
                    'Chäschtlizäddel '.$reqStufe->name,         // Title.
                    array(&$this, 'example_dashboard_widget_function') // Display function.
           );
   }

   public function ajaxSaveNewChaeschtli() {

     global $wpdb;
     //var_dump($_POST);
     /*echo "\n";
     foreach($_POST as $key => $value) {
       echo "POST parameter '$key' has '$value'\n";
     }*/

     // create Dates
     $timezone = new DateTimeZone(get_option('timezone_string'));
     $str_von = $_POST['von-date']." ".$_POST['von-time'];
     $von = DateTime::createFromFormat('j.m.Y H:i', $str_von, $timezone);

     $str_bis = $_POST['bis-date']." ".$_POST['bis-time'];
     $bis = DateTime::createFromFormat('j.m.Y H:i', $str_bis, $timezone);

     //echo $von->format('Y-m-d H:i:s');
     //echo $bis->format('Y-m-d H:i:s');

     $stufenId = intval($_POST['stufe']);
     //echo "Stufe: ".$stufenId;
     $tableName = $this->prefixTableName('chaeschtlizettel');

     $sql_stmt = "SELECT COUNT(*) FROM $tableName WHERE stufen_id = %d";
     $sql = $wpdb->prepare($sql_stmt,
                           $stufenId
                           );
     //echo $sql;

     $entry_exist = array_map('intval',$wpdb->get_results($sql));
     //echo $entry_exist[0];

    if($entry_exist[0] == 1){
      $sql_stmt = "UPDATE $tableName SET von = %s, geaendert= CURRENT_TIMESTAMP, bis = %s, wo = %s, infos = %s, mitnehmen = %s WHERE stufen_id = %d";
      $sql = $wpdb->prepare($sql_stmt,
                           $von->format('Y-m-d H:i:s'),
                           $bis->format('Y-m-d H:i:s'),
                           $_POST['wo'],
                           $_POST['infos'],
                           $_POST['mitnehmen'],
                           $stufenId
                          );
    }else{
      $sql_stmt = "INSERT INTO $tableName (stufen_id, geaendert, von, bis, wo, infos, mitnehmen) VALUES (%d, CURRENT_TIMESTAMP, %s, %s, %s, %s)";
      $sql = $wpdb->prepare($sql_stmt,
                            $stufenId,
                            $von->format('Y-m-d H:i:s'),
                            $bis->format('Y-m-d H:i:s'),
                            $_POST['wo'],
                            $_POST['infos'],
                            $_POST['mitnehmen']
                          );
    }

    //echo $sql;

    $wpdb->query($sql);

    //return refreshed form
    echo "<p>Der Chäschtlizettel wurde aktualisiert!</p>";

    echo '<a href="index.php" type="button" id="" class="button button-primary"> Ändern</a>';
    wp_die();
    }

    public function addActionsAndFilters() {
        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));




        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }

        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37
        add_action( 'admin_enqueue_scripts', 'my_enqueue' );

        //add_action('admin_enqueue_scripts', array(&$this, 'enqueueAdminPageStylesAndScripts'));

        // Adding scripts & styles to all pages
        // Examples:
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-serializejson-js', plugins_url( '/js/jquery.serializejson.min.js', __FILE__ ), array('jquery'));

        wp_enqueue_style('chaeschtlizettel-style', plugins_url('/css/chaeschtlizettel.css', __FILE__));
        wp_enqueue_style('clockpicker-style', plugins_url('/css/clockpicker.css', __FILE__));
        wp_enqueue_style('standalone-style', plugins_url('/css/standalone.css', __FILE__));
        wp_enqueue_style('datepicker-style', plugins_url('/css/datepicker.css', __FILE__));

        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
          wp_enqueue_style('jquery-ui', plugins_url('/css/jquery-ui.css', __FILE__));
          wp_enqueue_style('table-style', plugins_url('/css/table.css', __FILE__));
          wp_enqueue_style('font-awesome-style', plugins_url('/css/font-awesome.min.css', __FILE__));
          wp_enqueue_style('prettify-bootstrap-style', plugins_url('/css/prettify-bootstrap.min.css', __FILE__));
          wp_enqueue_style('prettify-style', plugins_url('/css/prettify.min.css', __FILE__));
          wp_enqueue_style('bootstrap-yeti-style', plugins_url('/css/bootstrap-yeti.min.css', __FILE__));
          wp_enqueue_script('jquery-ui-core');
          wp_enqueue_script('jquery-ui-tabs');
          wp_enqueue_script( 'table-edit-script', plugins_url( '/js/jquery.tabledit.js', __FILE__ ), array('jquery') );
          wp_enqueue_script( 'chaeschtli-settings-script', plugins_url( '/js/chaeschtli.settings.js', __FILE__ ), array('jquery') );

          // enqueue any othere scripts/styles you need to use
        }



        function my_enqueue($hook) {
          if( 'index.php' != $hook ) {
            // Only applies to dashboard panel
            return;
          }

          wp_enqueue_script( 'clockpicker-script', plugins_url( '/js/clockpicker.js', __FILE__ ), array('jquery') );
          wp_enqueue_script( 'datepicker-script', plugins_url( '/js/datepicker.js', __FILE__ ), array('jquery') );
          wp_enqueue_script( 'chaeschtlizettel-script', plugins_url( '/js/chaeschtlizettel.js', __FILE__ ), array('jquery') );
          // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
          wp_localize_script( 'chaeschtlizettel-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
        }

        add_filter( 'rest_user_query', 'prefix_remove_has_published_posts_from_wp_api_user_query', 10, 2 );
        /**
         * Removes `has_published_posts` from the query args so even users who have not
         * published content are returned by the request.
         *
         * @see https://developer.wordpress.org/reference/classes/wp_user_query/
         *
         * @param array           $prepared_args Array of arguments for WP_User_Query.
         * @param WP_REST_Request $request       The current request.
         *
         * @return array
         */
        function prefix_remove_has_published_posts_from_wp_api_user_query( $prepared_args, $request ) {
          unset( $prepared_args['has_published_posts'] );

          return $prepared_args;
        }

        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39
        add_shortcode('chae', array($this, 'showChaeschtli'));


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41
        //add_action('wp_ajax_SaveNewChaeschtli', array(&$this, 'ajaxSaveNewChaeschtli'));
        add_action('wp_ajax_SaveNewChaeschtli', array(&$this, 'ajaxSaveNewChaeschtli') );

        // add dashboard widget
        add_action( 'wp_dashboard_setup', array(&$this, 'example_add_dashboard_widgets') );


        // Register REST Server
        $rest_server = new Chaeschtlizettel_REST_Server();
        $rest_server->hook_rest_server();

    }

    public function settingsPage() {
        if (!current_user_can('manage_options')) {
          wp_die(__('You do not have sufficient permissions to access this page.', 'TEXT-DOMAIN'));
        }
      ?>
      <div>
        <h2><?php echo $this->getPluginDisplayName(); echo ' '; _e('Settings', 'chaeschtlizettel'); ?></h2>
      </div>

      <script type="text/javascript">
        jQuery(function() {
          jQuery("#plugin_config_tabs").tabs();
        });
      </script>

      <div class="plugin_config">
        <div id="plugin_config_tabs">
          <ul>
            <li><a href="#plugin_config-1">Stufen</a></li>
            <li><a href="#plugin_config-2">Stufenmitglieder</a></li>
            <li><a href="#plugin_config-3">Options</a></li>
          </ul>
          <div id="plugin_config-1">
            <?php $this->outputTabStufenContents(); ?>
          </div>
          <div id="plugin_config-2">
            <?php $this->outputTabStufenMemberContents(); ?>
          </div>
          <div id="plugin_config-3">
            <?php parent::settingsPage(); ?>
          </div>
        </div>
      </div>
      <?php
    }

    public function outputTabStufenContents(){
        $nonce = wp_create_nonce( 'wp_rest' );
      ?>
      <div id="errorMessageContainer"></div>
      <div id="stufeTableContainer"></div>
      <br/>
      Neue Stufe:<br/>
      <form id="newStufeForm">
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" name="name" class="form-control input-sm" value="">
          <label for="abteilung">Abteilung:</label>
            <select name="abteilung" class="form-control input-sm">
              <option value="m">m</option>
              <option value="f">f</option>
            </select>
          <label for="jahrgang">Jahrgang:</label>
          <input type="text" class="form-control input-sm" name="jahrgang" value=""><br/>
          <input type="submit" id="newStufeFormSubmitButton" value="Hinzuf&uuml;gen">
        </div>
      </form>
      <script type="text/javascript">
        jQuery(document).ready(function($) {
            jQuery(document).StufenTableFunction('<?php echo ($nonce);?>', '<?php get_rest_url(null)?>');
        });
      </script>
      <?php
    }

    public function outputTabStufenMemberContents(){
      $nonce = wp_create_nonce( 'wp_rest' );
      ?>
      <div id="errorMessageContainer"></div>
      <div id="stufeMemberTableContainer"></div>
      <br/>
      Neues Stufenmitglied:<br/>
      <form id="newStufenmemberForm">
        <div class="form-group">
          <label for="user_name">User name:</label>
          <select type="text" name="user_name" id="newStufenmemberUserName" class="form-control input-sm">
          </select>
          <label for="stufen_name">Stufenname:</label>
          <select name="stufen_name" id="newStufenmemberStufenName" class="form-control input-sm">
          </select>
          <br/>
          <input type="submit" id="newStufenmemberFormSubmitButton" value="Hinzuf&uuml;gen">
        </div>
      </form>
      <script type="text/javascript">
        jQuery(document).ready(function($) {
          jQuery(document).StufenMemberTableFunction('<?php echo ($nonce);?>', '<?php get_rest_url(null)?>');
        });
      </script>
      <?php
    }
}
?>
