<?php
class ManageUserHierarchy
{
    function Hierarchydisplay()
    {
        $output='<link rel="stylesheet" href="'.plugins_url() . '/salesforce_reports/css/bootstrap.min.css"/>
                <link rel="stylesheet" href="'.plugins_url() . '/salesforce_reports/css/jquery.jOrgChart.css"/>
                <link rel="stylesheet" href="'.plugins_url() . '/salesforce_reports/css/custom.css"/>
                <link href="'.plugins_url() . '/salesforce_reports/css/prettify.css" type="text/css" rel="stylesheet" />';
        
        
        $output.='<script type="text/javascript" src="'.plugins_url() . '/salesforce_reports/js/prettify.js"></script>
                  <script src="'.plugins_url() . '/salesforce_reports/js/jquery.jOrgChart.js"></script>';
        
        $data=$this->GenerateORGChartdata();
        $output.='<ul id="org" style="display:none">'.$data.'</ul><div id="chart" class="orgChart"></div>';
        
        $output.='<script>
        jQuery(document).ready(function() {
            
            /* Custom jQuery for the example */
            jQuery("#show-list").click(function(e){
                e.preventDefault();
                
                jQuery("#list-html").toggle("fast", function(){
                    if(jQuery(this).is(":visible")){
                        jQuery("#show-list").text("Hide underlying list.");
                        jQuery(".topbar").fadeTo("fast",0.9);
                    }else{
                        jQuery("#show-list").text("Show underlying list.");
                        jQuery(".topbar").fadeTo("fast",1);                  
                    }
                });
            });
            
            jQuery("#list-html").text(jQuery("#org").html());
            
            jQuery("#org").bind("DOMSubtreeModified", function() {
                jQuery("#list-html").text("");
                
                jQuery("#list-html").text(jQuery("#org").html());
                
                prettyPrint();                
            });
            jQuery("#org").jOrgChart({
            chartElement : "#chart",
            dragAndDrop  : true
        });
        prettyPrint();
        });
    </script>';
        
       return $output;
    }
    function GenerateORGChartdata()
    {
        global $wpdb;
        $output='';
        $uid = get_current_user_id();
        $user = new WP_User( $uid );
        $user_roles=$user->roles[0];
        if($user_roles=="companyadmin"):
            $output=$this->ORGChartCompanyAdmin();
        elseif($user_roles=="representative"):    
            $output=$this->ORGChartRepData();
        endif;
        return $output;
    }
    function ORGChartCompanyAdmin()
    {
        global $wpdb;
        $user_id = get_current_user_id();$output='';
        $user_info = get_userdata($user_id);
        $display_name = $user_info->display_name;
        $where=" AND (m.meta_key = 'wp_capabilities' AND m.meta_value LIKE '%representative%' ) AND u.ref_id='".$user_id."'";
        $query="SELECT u.ID,u.display_name FROM wp_users as u INNER JOIN wp_usermeta as m ON u.ID = m.user_id".$where;        
        $userls = $wpdb->get_results($query, OBJECT);
        $output.='<li><div class="orgchart_name">'.$display_name.'</div><ul>';
        foreach($userls as $us):
            $output.='<li><div class="orgchart_name">'.$us->display_name.'</div>';
            $wheresub=" AND (m.meta_key = 'wp_capabilities' AND m.meta_value LIKE '%reseller%' ) AND u.ref_id='".$us->ID."'";
            $subquery="SELECT u.ID,u.display_name FROM wp_users as u INNER JOIN wp_usermeta as m ON u.ID = m.user_id".$wheresub;  
            $subuser = $wpdb->get_results($subquery, OBJECT);
            if(count($subuser)!="0")$output.='<ul>';
            foreach($subuser as $su):
                $output.='<li><div class="orgchart_name">'.$su->display_name.'</div></li>';
            endforeach;
            if(count($subuser)!="0")$output.='</ul>';
            $output.='</li>';
        endforeach;
         $output.='</ul></li>';
        return $output;
        
    }
    function ORGChartRepData()
    {
        global $wpdb;
        $user_id = get_current_user_id();$output='';
        $queryparents="SELECT T2.ID,T2.display_name
        FROM (
            SELECT
                @r AS _id,
                (SELECT @r := ref_id FROM wp_users WHERE id = _id) AS ref_id,
                @l := @l + 1 AS lvl
            FROM
                (SELECT @r := '".$user_id."', @l := 0) vars,
                wp_users h
            WHERE @r <> 0) T1
        JOIN wp_users T2
        ON T1._id = T2.id
        ORDER BY T1.lvl DESC";
        $rs = $wpdb->get_results($queryparents, OBJECT);
        $output.='<li><div class="orgchart_name">'.$rs[0]->display_name.'</div>';
        $output.='<ul><li><div class="orgchart_name">'.$rs[1]->display_name.'</div>';
        $wheresub=" AND (m.meta_key = 'wp_capabilities' AND m.meta_value LIKE '%reseller%' ) AND u.ref_id='".$rs[1]->ID."'";
        $subquery="SELECT u.ID,u.display_name FROM wp_users as u INNER JOIN wp_usermeta as m ON u.ID = m.user_id".$wheresub;  
        $subuser = $wpdb->get_results($subquery, OBJECT);
        if(count($subuser)!="0")$output.='<ul>';
            foreach($subuser as $su):
                $output.='<li><div class="orgchart_name">'.$su->display_name.'</div></li>';
            endforeach;
        if(count($subuser)!="0")$output.='</ul>';
        $output.='</li></ul></li>';
        return $output;        
    }
}
?>
