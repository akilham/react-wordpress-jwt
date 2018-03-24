<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');


// add option to hide labels for gravity forms fields
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );


/**
 * Load a template part into a template
 * Same as get_template_part except allows you to pass in parameters
 * Parameters are available in the template file using get_query_var()
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 * @param array $params Any extra params to be passed to the template part.
 */
function get_template_part_extended( $slug, $name = null, $params = array() ) {
  if ( ! empty( $params ) ) {
    foreach ( (array) $params as $key => $param ) {
      set_query_var( $key, $param );
    }
  }
  get_template_part( $slug, $name );
}


// Hook.
add_action( 'rest_api_init', __NAMESPACE__ . '\\wp_rest_allow_all_cors', 15 );
/**
 * Allow all CORS.
 *
 * @since 1.0.0
 */
function wp_rest_allow_all_cors() {
  // Remove the default filter.
  remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
  // Add a Custom filter.
  add_filter( 'rest_pre_serve_request', function( $value ) {
    header( 'Access-Control-Allow-Origin: *' );
    header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
    header( 'Access-Control-Allow-Credentials: true' );
    return $value;
  });
} // End fucntion wp_rest_allow_all_cors().



function my_add_meta_vars ($current_vars) {
  $current_vars = array_merge ($current_vars, array ('orderby'));
  return $current_vars;
}
add_filter ('rest_query_vars', __NAMESPACE__ . '\\my_add_meta_vars');


/* TODO: add permission checks so only authorised users can access this endpoint
    capability name is 'rdm_cap' */
add_action( 'rest_api_init', function () {
  register_rest_route( 'cm/v1', '/presets', array(
    'methods' => 'GET',
    'callback' => __NAMESPACE__ . '\get_presets',
    'permission_callback' => function () { return current_user_can( 'rdm_cap' ); }
  ) );

  register_rest_route( 'cm/v1', '/presets', array(
    'methods' => 'POST',
    'callback' => __NAMESPACE__ . '\add_preset',
    'permission_callback' => function () { return current_user_can( 'rdm_cap' ); }
  ) );

  register_rest_route( 'cm/v1', '/log', array(
    'methods' => 'POST',
    'callback' => __NAMESPACE__ . '\add_log_entry',
    'permission_callback' => function () { return current_user_can( 'rdm_cap' ); }
  ) );

  register_rest_route( 'cm/v1', '/presentation', array(
    'methods' => 'POST',
    'callback' => __NAMESPACE__ . '\add_presentation_data',
    'permission_callback' => function () { return current_user_can( 'rdm_cap' ); }
  ) );

  register_rest_route( 'cm/v1', '/presentation', array(
    'methods' => 'GET',
    'callback' => __NAMESPACE__ . '\get_presentation_data',
    'permission_callback' => function () { return current_user_can( 'rdm_cap' ); }
  ) );
} );



function get_presets( \WP_REST_Request $request ) {
  $data = get_option('cm_presets');

  // Create the response object
  $response = new \WP_REST_Response( $data, 200 );

  return $response;
}




function add_preset( \WP_REST_Request $request ) {
  try {
    $oldPresets = get_option('cm_presets');

    $newPreset = json_decode( $request->get_body() );

    // $newPreset = [ 'id' => 'av4389af', 'name' => 'Preset 3', 'slides' => [10, 14] ];

    unset($newPreset->incomplete);

    $presets = $oldPresets;

    $presets[] = $newPreset;

    update_option('cm_presets', $presets);

    $data = [ 'new' => $newPreset, 'all' => $presets ];

    $response = new \WP_REST_Response( $data, 200);
  }
  catch (\Exception $e) {
    $response = new \WP_REST_Response( $e, 500);
  }

  return $response;
}



/* TODO: add more debug data to the log (headers, body, user-agent, etc) */
function add_log_entry( \WP_REST_Request $request ) {
  global $wpdb;

  try {
    $data = json_decode( $request->get_body() );

    $userEmail = $data->useremail;

    $user = get_user_by('email', $userEmail);

    $userID = (isset($user->ID)) ? $user->ID : '';
    $action = $data->action;
    $logData = json_encode($data->data);
    $time = current_time('mysql', 1);

    $wpdb->insert('wp_cm_log', [ 
      'date' => $time,
      'user' => $userID,
      'action' => $action,
      'data' => $logData,
      'ip' => getUserIP(),
//       'request' => json_encode($request)
    ]);

    $response = new \WP_REST_Response( $data, 200);
  }
  catch (\Exception $e) {
    $response = new \WP_REST_Response( $e, 500);
  }

  return $response;
}



function add_presentation_data( \WP_REST_Request $request ) {
  global $wpdb;

  try {
    $data = json_decode( $request->get_body() );

    $userEmail = $data->useremail;

    $user = get_user_by('email', $userEmail);

    $userID = $user->ID;

    $presID = $data->presID;
    $presetHash = $data->presetHash;
    $clientData = json_encode($data->clientData);
    $slideData = json_encode($data->slideData);
    $time = current_time('mysql', 1);


    // logging / debugging purposes
    $wpdb->insert('wp_cm_presentation_data_log', [
      'presID' => $presID,
      'userID' => $userID,
      'date' => $time,
      'presetHash' => $presetHash,
      'clientData' => $clientData,
      'slideData' => $slideData,
    ]);


    $doesRowExist = $wpdb->get_var( "SELECT COUNT(id) FROM wp_cm_presentation_data WHERE presID = '{$presID}'" );

    if ($doesRowExist > 0) {
      $wpdb->update('wp_cm_presentation_data', [
        'updated' => $time,
        'clientData' => $clientData,
        'slideData' => $slideData,
      ],
      [ 'presID' => $presID ] );
    }
    else {
      $wpdb->insert('wp_cm_presentation_data', [
        'presID' => $presID,
        'userID' => $userID,
        'date' => $time,
        'updated' => $time,
        'presetHash' => $presetHash,
        'clientData' => $clientData,
        'slideData' => $slideData,
      ]);
    }

    $response = new \WP_REST_Response( $data, 200);
  }
  catch (\Exception $e) {
    $response = new \WP_REST_Response( $e, 500);
  }

  return $response;
}

function get_presentation_data( \WP_REST_Request $request ) {
  global $wpdb;

  try {
    $data = json_decode( $request->get_body() );

    $userID = get_current_user_id();

    $data = $wpdb->get_results( "SELECT * FROM wp_cm_presentation_data WHERE userID = '{$userID}' ORDER BY `updated` DESC", ARRAY_A );


    $response = new \WP_REST_Response( $data, 200);
  }
  catch (\Exception $e) {
    $response = new \WP_REST_Response( $e, 500);
  }

  return $response;
}



class AdminPresets {
  private $capability = 'edit_posts';


  function __construct(){
    add_action( 'admin_menu', array( &$this, 'add_top_level_menu' ) );
  }

  function add_admin_scripts(){
    //adds javavascript files for this plugin.
    wp_enqueue_script('my-script-name', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) . '/js/javascript.js', array('jquery'), '1.0');
    wp_localize_script('my-script-name', 'MyScriptAjax', array('ajaxUrl' => admin_url('admin-ajax.php')));
  }

  function add_top_level_menu()
  {
    // Settings for the function call below
    $page_title = 'Presets';
    $menu_title = 'Presets';
    $menu_slug = 'presets';
    $function = array( &$this, 'display_page' );
    $icon_url = NULL;
    $position = '';

    // Creates a top level admin menu - this kicks off the 'display_page()' function to build the page
    $page = add_menu_page($page_title, $menu_title, $this->capability, $menu_slug, $function, $icon_url, 10);

    // Adds an additional sub menu page to the above menu - if we add this, we end up with 2 sub menu pages (the main pages is then in sub menu. But if we omit this, we have no sub menu
    // This has been left in incase we want to add an additional page here soon
    //add_submenu_page( $menu_slug, $page_title, $page_title, $capability, $menu_slug . '_sub_menu_page', $function );


  }

  function display_page()
  {
    if (!current_user_can($this->capability ))
      wp_die(__('You do not have sufficient permissions to access this page.'));
    //here comes the HTML to build the page in the admin.


    $presets = get_option('cm_presets');

    ?>

    <div class="wrap">
      <h1>Presentation Presets</h1>

      <form id="preset-list" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

        <?php

        $presetsListTable = new \Roots\Sage\Extras\PresetsListTable();

        $presetsListTable->prepare_items();

        $presetsListTable->display();

        ?>

      </form>
    </div>

    <?php
  }
}

$adminPresets = new \Roots\Sage\Extras\AdminPresets();

if(!class_exists('WP_List_Table')){
 require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class PresetsListTable extends \WP_List_Table {

   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
   function __construct() {
     parent::__construct( array(
      'singular'=> 'preset', //Singular label
      'plural' => 'presets', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
    ) );
   }

  function get_columns() {
     return $columns = array(
      'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
      'id'=>'ID',
      'name'=>'Name',
      'slides'=>'Slides'
    );
   }


  function column_default($item, $column_name){
    switch($column_name){
      case 'id':
      case 'name':
        return $item[$column_name];
      case 'slides':
        return count($item[$column_name]);
      default:
          return print_r($item,true); //Show the whole array for troubleshooting purposes
    }
  }

  function column_cb($item){
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
          );
  }



  function column_title($item){

        //Build row actions
    $actions = array(
      'edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
      'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
    );

        //Return the title contents
    return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
      /*$1%s*/ $item['title'],
      /*$2%s*/ $item['id'],
      /*$3%s*/ $this->row_actions($actions)
    );
  }


  function process_bulk_action() {

    //Detect when a bulk action is being triggered...
    if( 'delete'===$this->current_action() ) {
      
      $presetsToDelete = $_GET['preset'];

      if (is_array($presetsToDelete)) {
        $presets = get_option('cm_presets');

        foreach ($presetsToDelete as $pD) {
          foreach ($presets as $key => $p) {
            if ($p->id === $pD) {
              unset($presets[$key]);
            }
          }
        }

        update_option('cm_presets', $presets);

          ?>
          <div class="notice notice-success">
          <p>The selected presets have been deleted.</p>
            </div>
            <?php

      }
    }

  }


  function get_bulk_actions() {
        $actions = array(
          'delete'    => 'Delete'
        );
        return $actions;
      }


  /* TODO: make pagination work, check downloaded WP_LIST_TABLE example */
  function prepare_items() {
    // global $_wp_column_headers;
    $screen = get_current_screen();


    /* -- Register the Columns -- */
    $columns = $this->get_columns();

    // $_wp_column_headers[$screen->id]=$columns;

    $this->_column_headers = [ $columns, [], $this->get_sortable_columns() ];



    $this->process_bulk_action();



    $presets = get_option('cm_presets');


    // $this->items = $presets;

    foreach ($presets as $preset) {
      $this->items[] = [
        'id' => $preset->id,
        'name' => $preset->name,
        'slides' => $preset->slides
      ];
    }


    $total_items = count($this->items);
    $per_page = 5;

    /**
     * REQUIRED. We also have to register our pagination options & calculations.
     */
        $this->set_pagination_args( array(
        'total_items' => $total_items,                  //WE have to calculate the total number of items
        'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
        'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
      ) );
  }
 }




 function getUserIP() {
   foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
     if (array_key_exists($key, $_SERVER) === true) {
       foreach (explode(',', $_SERVER[$key]) as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
          return $ip;
        }
      }
    }
  }
}