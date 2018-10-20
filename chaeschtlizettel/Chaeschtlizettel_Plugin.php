<?php


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
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

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
            PRIMARY KEY  (stufen_id)
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
            PRIMARY KEY  (stufen_id)
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
    }

    public function showChaeschtli($atts){
      global $wpdb;

      // stufen id suchen
      $tableName = $this->prefixTableName('stufen');
      $sql_stmt = "SELECT stufen_id FROM $tableName WHERE name = %s";
      $sql = $wpdb->prepare($sql_stmt,
                            $atts['stufe']
                            );

      $stufenId = intval($wpdb->get_results($sql)[0]->stufen_id);

      $tableName = $this->prefixTableName('chaeschtlizettel');
      $sql_stmt = "SELECT * FROM $tableName WHERE stufen_id = %d";
      $sql = $wpdb->prepare($sql_stmt,
                            $stufenId
                            );
      //echo $sql;

      $result = $wpdb->get_results($sql);
      //var_dump($result);
      $chaeschtli = $result[0];

      // datum formatieren
      $von = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->von);
      $bis = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->bis);

      if($von->format('Y-m-d') == $bis->format('Y-m-d')){
        $zeit = $von->format('j.m.Y H:i').' - '.$bis->format('H:i');
      }else{
        $zeit = $von->format('j.m.Y H:i').' - '.$bis->format('j.m.Y H:i');
      }

      $now = date('Y-m-d');
      if($von->format('Y-m-d') < $now){
        return '<div class="chae-wrapper"><h3>Chäschtli '.$atts['stufe'].'</h3><p>Keine aktuellen Informationen verfügbar.</p></div>';
      }else{
        return '<div class="chae-wrapper"><h3>Chäschtli '.$atts['stufe'].'</h3><h6>Treffpunkt</h6><p>'.$zeit.'<br>'.$chaeschtli->wo.'</p><h6>Mitnehmen</h6><p>'.$chaeschtli->infos.'</p></div>';
      }
    }

    /**
     * Create the function to output the contents of our Dashboard Widget.
     */
    public function example_dashboard_widget_function() {
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

      $last_update = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->geaendert)->format('j.m.Y H:i');
      //var_dump($chaeschtli);

      // datum formatieren
      $von = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->von);
      $bis = DateTime::createFromFormat('Y-m-j H:i:s', $chaeschtli->bis);

      $now = date('Y-m-d');
      if($von->format('Y-m-d') < $now){
        $status = "abgelaufen";
        $chaeschtli->infos = "";
        $chaeschtli->wo = "";
        $von = DateTime::createFromFormat('H:i', '14:00');
        $bis = DateTime::createFromFormat('H:i', '17:00');
      }else{
        $status = "noch gültig";
      }

      include("forms/chae_dash_chaeschtlizeddel.php");
    }

    /**
    * Add a widget to the dashboard.
    *
    * This function is hooked into the 'wp_dashboard_setup' action below.
    */
   public function example_add_dashboard_widgets() {

     wp_add_dashboard_widget(
                    'chae_dash',         // Widget slug.
                    'Chäschtlizäddel',         // Title.
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
     $str_von = $_POST['von-date']." ".$_POST['von-time'];
     $von = DateTime::createFromFormat('j.m.Y H:i', $str_von);

     $str_bis = $_POST['bis-date']." ".$_POST['bis-time'];
     $bis = DateTime::createFromFormat('j.m.Y H:i', $str_bis);

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
      $sql_stmt = "UPDATE $tableName SET von = %s, geaendert= CURRENT_TIMESTAMP, bis = %s, wo = %s, infos = %s WHERE stufen_id = %d";
      $sql = $wpdb->prepare($sql_stmt,
                           $von->format('Y-m-d H:i:s'),
                           $bis->format('Y-m-d H:i:s'),
                           $_POST['wo'],
                           $_POST['content'],
                           $stufenId
                          );
    }else{
      $sql_stmt = "INSERT INTO $tableName (stufen_id, geaendert, von, bis, wo, infos) VALUES (%d, CURRENT_TIMESTAMP, %s, %s, %s, %s)";
      $sql = $wpdb->prepare($sql_stmt,
                            $stufenId,
                            $von->format('Y-m-d H:i:s'),
                            $bis->format('Y-m-d H:i:s'),
                            $_POST['wo'],
                            $_POST['content']
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

        // Adding scripts & styles to all pages
        // Examples:
                wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
                //wp_enqueue_script('chaeschtlizettel-script', plugins_url('/js/chaeschtlizettel.js', __FILE__));
                wp_enqueue_style('chaeschtlizettel-style', plugins_url('/css/chaeschtlizettel.css', __FILE__));
                wp_enqueue_style('clockpicker-style', plugins_url('/css/clockpicker.css', __FILE__));
                wp_enqueue_style('standalone-style', plugins_url('/css/standalone.css', __FILE__));
                wp_enqueue_style('datepicker-style', plugins_url('/css/datepicker.css', __FILE__));

        function my_enqueue($hook) {
            if( 'index.php' != $hook ) {
          	     // Only applies to dashboard panel
          	     return;
            }

          	wp_enqueue_script( 'clockpicker-script', plugins_url( '/js/clockpicker.js', __FILE__ ), array('jquery') );
            wp_enqueue_script( 'datepicker-script', plugins_url( '/js/datepicker.js', __FILE__ ), array('jquery') );
            wp_enqueue_script( 'chaeschtlizettel-script', plugins_url( '/js/chaeschtlizettel.js', __FILE__ ), array('jquery') );

          	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        	  wp_localize_script( 'chaeschtlizettel-script', 'ajax_object',
                    array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
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


}
