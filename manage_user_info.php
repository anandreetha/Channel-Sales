<?php
class ManageUsers
{
    function FilterBy()
    {
        global $wpdb;
        $output.='<form name="fltrops" id="fltrops" method="POST">';
               
        $output.='<table cellpadding="6" cellspacing="6"><tr><td>'
                . 'Enter email id or name:</td><td> <input type="text" name="opsname" id="opsname" value="'.$_POST['opsname'].'" placeholder="Enter your Name, Email......"></td><td>'
                . '<input type="Submit" name="Search" value="Search">&nbsp;&nbsp;'
                . '<input type="button" value="Reset" onClick="ResetFlt()"></td></tr></table>';
        $output.='<input type="hidden" name="filterusr" id="filterusr" value="1"></form>';
        ?>
        <script type="text/javascript">
            function ResetFlt()
            {
                jQuery("#opsname").val('');
                FilterData();
            }
            function FilterData()
            {
                jQuery('#fltrops').submit();
            }
        </script>
        <?php
        return $output;
    }
    function FilterWhereCondit($post)
    {
        if($post['filterusr']=="1"):
            $opsname=$_POST['opsname'];
            $where="AND ( ";
            $where.="(`user_email` like '%".$opsname."%')";      
            $where.=" OR (`display_name` like '%".$opsname."%')";  
            $where.=")";
        endif;
        return $where;
    }
    function getCompanyAdminList($role)
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $currency=getCurrency();
        $chid=GetChildUserIdsinCommas();
        if($chid=="")$chid=$user_id;
        
        $where=$this->FilterWhereCondit($_POST);
        
        $query="SELECT ID,user_email, display_name,user_registered,user_status,ref_id,created_at from `wp_users` where (ID=".$user_id." OR ID IN (".$chid.")) ".$where." Order by id DESC"; 
        $result = $wpdb->get_results($query, OBJECT);
        
        $output.='<table width="100%" cellspacing="6" cellpadding="5" class="list_price" border="1" width="100%">
                                        <tbody>';
        $output.='<tr><td colspan="5" class="podd" style="padding:10px;">'.$this->FilterBy().'</td><td align="right" colspan="2" class="podd">'
                . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                . '<a href="javascript:;" onclick="CreateNewUser()"><img src="'.plugins_url().'/salesforce_reports/tree/images/create-new.png"></a>'
                . '&nbsp;&nbsp;</td></tr>';
        $output.='<form name="userfrm" id="userfrm">';
        $output.='<tr id="ldprs">'
                . '<td class="cptit">No</td>'
                . '<td class="cptit">Email</td>'
                . '<td class="cptit">First Name</td>'
                . '<td class="cptit">Registered Date</td>'
                . '<td class="cptit">Role</td>'
                . '<td class="cptit">Created by</td>'
                . '<td class="cptit">Status</td>'
                . '</tr>';
        $n=1;
        foreach($result as $rs):
            $reg=date('d-m-Y', strtotime($rs->user_registered));
            if($rs->user_status=="0"):$status="Active";else:$status="InActive";endif;
            $cusr=$rs->created_at;
            $cusrdt = get_userdata($cusr);
            $cat=$cusrdt->display_name;
            if($cat=="")$cat="Unknown";
            $user='';
            $user = new WP_User($rs->ID);// print"<pre>";print_r($user);print"</pre>";
            $user_roles=$user->roles[0];
            
            $output.='<tr>';
            $output.='<td class="podd">'.$n.'</td>';
            $output.='<td class="podd">'.$rs->user_email.'</td>';
            $output.='<td class="podd">'.$rs->display_name.'</td>';
            $output.='<td class="podd">'.$reg.'</td>';
            $output.='<td class="podd">'.$user_roles.'</td>';            
            $output.='<td class="podd">'.$cat.'</td>';
            $output.='<td class="podd">'.$status.'</td>';
            $output.='</tr>';
            $n++;
        endforeach;
        if(count($result)=="0"):
            $output.='<tr><td class="podd" colspan="7" align="center"> No users found yet</td></tr>';        
        endif;
        $output.='</tbody></table>';
        $output.= '</form>';
        $output.=$this->IncludeScript();
        return $output;        
         
    }
    function IncludeScript()
    {
        ?>
        <script type="text/javascript">
            function CreateNewUser()
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create new user</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "getuserform=1",
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                    }
                });
                
            }
            function SaveUserInfo()
            {
                var email=jQuery('#user_email').val();
                var display_name=jQuery('#display_name').val();
                if(email=="")
                {
                    alert("Please enter your customer email id");
                    jQuery("#user_email").focus();
                }
                else if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)))
                {
                        alert("Please Enter Your Valid Email Id");
                        jQuery("#user_email").focus();
                }
                else if(display_name=="")
                {
                    alert("Please enter your name");
                    jQuery("#display_name").focus();
                }
                else if(jQuery('#setrole').length==0)
                {
                    alert("Please select the user role");
                }
                else
                {
                    document.frmpost.action='';
                    document.frmpost.method="POST";
                    document.frmpost.submit();                    
                }
            }
            function CancelSettings()
            {
                jQuery(".ui-widget-overlay").remove();
                jQuery(".rolesettings").remove(); 
            }
        </script>    
        <?php
        
    }
    function AutoCreateUser()
    {
        global $wpdb;
        $param=$_POST;
        $uid = get_current_user_id();
        $user = new WP_User( $uid );
        $user_roles=$user->roles[0];
        $email=$param['user_email'];
        $display_name=$param['display_name'];
        
        $setrole=$param['setrole'];
        if( null == username_exists( $email ) ) {
            $password = wp_generate_password( 12, false );
            $user_id = wp_create_user( $email, $password, $email );
            
            $user = new WP_User( $user_id );
            $user->set_role($setrole);
            $wpdb->query("update wp_users set ref_id='".$uid."',created_at='".$uid."',display_name='".$display_name."' where ID=".$user_id);
            wp_mail( $email, 'Welcome!', 'Your Password: ' . $password );
            header("Location: ".get_bloginfo('url')."/salesforce_reports");        
            exit;
        }
    } 
    function AutoUpdateUser($param)
    {
        global $wpdb;
        $email=$param['user_email'];
        $display_name=$param['display_name'];
        $user_id=$param['id'];
        wp_update_user(
            array(
                'ID'          =>    $user_id,
                'nickname'    =>    $display_name,
                )
            );
        $wpdb->query("update wp_users set user_email='".$email."',display_name='".$display_name."' where ID=".$user_id);
        return $user_id;
    }
    function GetUsrForm()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $user = new WP_User($user_id);
        $user_roles=$user->roles[0];
        $query="SELECT u.id,u.role_name,u.parent FROM wp_crm_roleconfig as u where `parent` IN (SELECT id FROM wp_crm_roleconfig where `role_name`='".$user_roles."')";
        $result = $wpdb->get_results($query, OBJECT);
        $slt='<select name="setrole" id="setrole">';
        foreach($result as $rs):
            $slt.='<option value="'.$rs->role_name.'">'.$rs->role_name.'</option>';
        endforeach;
        $slt.='</select>';
        
        $output='<form name="frmpost" id="FrmGrid_list1" class="FormGrid">
        <table id="TblGrid_list1" class="EditTable" cellspacing="0" cellpadding="0" border="0">
        <tbody>
            <tr id="FormError" style="display:none">
                <td class="ui-state-error" colspan="2"></td>
            </tr>
            <tr style="display:none" class="tinfo">
                <td class="topinfo" colspan="2"></td>
            </tr>
            <tr rowpos="1" class="FormData" id="tr_user_email">
                <td class="CaptionTD">Email</td>
                <td class="DataTD">&nbsp;
                    <input type="text" id="user_email" name="user_email" role="textbox" class="FormElement ui-widget-content ui-corner-all">
                    </td>
                </tr>
                <tr rowpos="2" class="FormData" id="tr_display_name">
                    <td class="CaptionTD">Firstname</td>
                    <td class="DataTD">&nbsp;
                        <input type="text" id="display_name" name="display_name" role="textbox" class="FormElement ui-widget-content ui-corner-all">
                        </td>
                    </tr>
                <tr rowpos="2" class="FormData" id="tr_display_name">
                    <td class="CaptionTD">Role</td>
                    <td class="DataTD">&nbsp;
                        '.$slt.'
                        </td>
                    </tr>    
                    <tr class="FormData" style="display:none">
                        <td class="CaptionTD"></td>
                        <td colspan="1" class="DataTD">
                            <input class="FormElement" id="id_g" type="text" name="list1_id" value="_empty">
                            </td>
                        </tr>
                    </tbody>
                </table><input type="hidden" name="cusr" value="1">
            </form>';
            $output.='<table border="0" cellspacing="0" cellpadding="0" class="EditTable" id="TblGrid_list1_2">
                <tbody>
                    <tr>
                        <td colspan="2">
                            <hr class="ui-widget-content" style="margin:1px">
                            </td>
                        </tr>
                        <tr id="Act_Buttons">
                            <td class="navButton">
                                <a id="pData" class="fm-button ui-state-default ui-corner-left" style="display: none;">
                                    <span class="ui-icon ui-icon-triangle-1-w"></span>
                                </a>
                                <a id="nData" class="fm-button ui-state-default ui-corner-right" style="display: none;">
                                    <span class="ui-icon ui-icon-triangle-1-e"></span>
                                </a>
                            </td>
                            <td class="EditButton">
                                <a id="sData" href="javascript:;" onclick="SaveUserInfo()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit
                                    <span class="ui-icon ui-icon-disk"></span>
                                </a>
                                <a id="cData" href="javascript:;" onclick="CancelSettings()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel
                                    <span class="ui-icon ui-icon-close"></span>
                                </a>
                            </td>
                        </tr>
                        <tr style="display:none" class="binfo">
                            <td class="bottominfo" colspan="2"></td>
                        </tr>
                    </tbody>
                </table>';
        echo $output;
        exit;
    }
}
class ManageUsersbyRole extends ManageUsers
{
    function getUserInfoData($role)
    {
        if($_POST['getuserform']=="1"):
            $out=$this->GetUsrForm();
        elseif($_POST['cusr']=="1"):
            $out=$this->AutoCreateUser();
        else:
            $out=$this->getCompanyAdminList($role);
        endif;
        return $out;
    }
}
?>