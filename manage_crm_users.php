<?php
class ManageCRMUserData
{
    function crmuserecredential()
    {
        $result=$this->crmuserdatapush();
        $output='<form method="post" id="crm_users" name="crm_users">
				
					<p><label for="log">Salesforce Username</label>
					<input type="text" name="uname" id="uname" value="'.$result[0]->uname.'" size="22" placeholder="Salesforce Username"></p>
					
					<p><label for="pwd">Salesforce Password</label>
					<input type="password" name="pass" id="pass" size="22" placeholder="Salesforce Password" value="'.trim(base64_decode($result[0]->pass)).'"></p>
					
					<p><input type="submit" name="submit" value="Save" class="button">&nbsp;&nbsp;
                                        <input type="button" name="cancel" value="Cancel" class="button" onClick="window.location.reload()">
                                        </p>
					
				</form>';
        
       return $output;
    }
    function crmuserdatapush()
    {
        global $wpdb;
        $uname=$_POST['uname'];
        $user_id = get_current_user_id();
        $query="select id,uname,pass from `wp_crm_users` where user_id='".$user_id."'";
        $userls = $wpdb->get_results($query, OBJECT);
        if($uname):
            if(count($userls)>0):
                $id=$userls[0]->id;
                $query="update `wp_crm_users` set uname='".$_POST['uname']."',pass='".trim(base64_encode($_POST['pass']))."', date_modified='".date('Y-m-d H:i:s')."' where id=".$id;
            else:
                $query="insert into `wp_crm_users`(`uname`,`pass`,`user_id`,`crm_name`,`date_modified`)values('".$_POST['uname']."','".trim(base64_encode($_POST['pass']))."','".$user_id."','salesforce','".date('Y-m-d H:i:s')."')";
            endif;
            $wpdb->query($query);
        endif;        
        return $userls;
    }
   
}
?>
