<?php
/*
Plugin Name:Salesforce Reports
Description: ~
Version: 1.0
Author: Anand
Author URI: mailto:anandpr2008@gmail.com
*/
if (!class_exists('Salesforce_reports_Plugin'))
{
  class Salesforce_reports_Plugin
  {
    public $_name;
    public $page_title;
    public $page_name;
    public $page_id;

    public function __construct()
    {
      $this->_name      = 'Salesforce Report';
      $this->page_title = 'salesforce_reports';
      $this->page_name  = $this->_name;
      $this->page_id    = '0';

      register_activation_hook(__FILE__, array($this, 'activate'));
      register_deactivation_hook(__FILE__, array($this, 'deactivate'));
      register_uninstall_hook(__FILE__, array($this, 'uninstall'));

      add_filter('parse_query', array($this, 'query_parser'));
      add_filter('the_posts', array($this, 'page_filter'));
        require( dirname( __FILE__ ) . '/functions.php' );
        require( dirname( __FILE__ ) . '/salesforce_reports_steps.php' );
        require( dirname( __FILE__ ) . '/manage_user_info.php' );
        require( dirname( __FILE__ ) . '/manage_user_hierarchy.php' );
        require( dirname( __FILE__ ) . '/manage_crm_users.php' );
        require( dirname( __FILE__ ) . '/common_role_config.php' );
        require( dirname( __FILE__ ) . '/upload_price_list.php' );
        require( dirname( __FILE__ ) . '/users_list.php' );        
        require( dirname( __FILE__ ) . '/cost_price_list.php' );
        require( dirname( __FILE__ ) . '/manage_cost_price.php' );
        require( dirname( __FILE__ ) . '/cost_price_approval.php' );
        require( dirname( __FILE__ ) . '/manage_internal_sellprice.php');  
        require( dirname( __FILE__ ) . '/manage_reseller_sellprice.php');  
        require( dirname( __FILE__ ) . '/manage_reseller_opportunity.php');  
        require( dirname( __FILE__ ) . '/reseller_invoice.php');
        require( dirname( __FILE__ ) . '/manage_reseller_discounts.php');  
        require( dirname( __FILE__ ) . '/manage_cost_price_discounts.php');
        require( dirname( __FILE__ ) . '/opportunity_activity.php');
        require( dirname( __FILE__ ) . '/manage_company_opportunity.php');
        
        require( dirname( __FILE__ ) . '/mail_template.php');
        require( dirname( __FILE__ ) . '/manage_products.php');  
        
    }

    public function activate()
    {
      global $wpdb;      

      delete_option($this->_name.'_page_title');
      add_option($this->_name.'_page_title', $this->page_title, '', 'yes');

      delete_option($this->_name.'_page_name');
      add_option($this->_name.'_page_name', $this->page_name, '', 'yes');

      delete_option($this->_name.'_page_id');
      add_option($this->_name.'_page_id', $this->page_id, '', 'yes');

      $the_page = get_page_by_title($this->page_title);

      if (!$the_page)
      {
        // Create post object
        $_p = array();
        $_p['post_title']     = $this->page_title;
        $_p['post_content']   = "This text may be overridden by the plugin. You shouldn't edit it.";
        $_p['post_status']    = 'publish';
        $_p['post_type']      = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status']    = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $this->page_id = wp_insert_post($_p);
      }
      else
      {
        // the plugin may have been previously active and the page may just be trashed...
        $this->page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $this->page_id = wp_update_post($the_page);
      }

      delete_option($this->_name.'_page_id');
      add_option($this->_name.'_page_id', $this->page_id);
    }

    public function deactivate()
    {
      $this->deletePage();
      $this->deleteOptions();
    }

    public function uninstall()
    {
      $this->deletePage(true);
      $this->deleteOptions();
    }

    public function query_parser($q)
    {
	  if(isset($q->query_vars['page_id']) AND (intval($q->query_vars['page_id']) == $this->page_id ))
      {
        $q->set($this->_name.'_page_is_called', true);
      }
      elseif(isset($q->query_vars['pagename']) AND (($q->query_vars['pagename'] == $this->page_name) OR ($_pos_found = strpos($q->query_vars['pagename'],$this->page_name.'/') === 0)))
      {
        $q->set($this->_name.'_page_is_called', true);
      }
      else
      {
        $q->set($this->_name.'_page_is_called', false);
      }
    }

    function page_filter($posts)
    {
     	global $wp_query;  
	if ( !is_admin() && ($wp_query->query_vars['pagename']=="salesforce_reports")) :
            if ( is_user_logged_in() ):
                if($_GET['action']=="opportunity"): $title='';else:$title='Manage Users';endif;
                $posts[0]->post_title = $title;
                $sfreports=new DisplaySFReports();
                $posts[0]->post_content =$sfreports->SFdisplay();
            else:
                $posts[0]->post_title = "<h1>404 Not Found</h1>";
                $posts[0]->post_content= "The page that you have requested could not be found.";                
            endif;    
      	endif;
      	return $posts;
    }

    private function deletePage($hard = false)
    {
      global $wpdb;

      $id = get_option($this->_name.'_page_id');
      if($id && $hard == true)
        wp_delete_post($id, true);
      elseif($id && $hard == false)
        wp_delete_post($id);
    }

    private function deleteOptions()
    {
      delete_option($this->_name.'_page_title');
      delete_option($this->_name.'_page_name');
      delete_option($this->_name.'_page_id');
    }
  }
}
$salesforce_reports = new Salesforce_reports_Plugin();
?>