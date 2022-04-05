<?php
class MailTemplate
{
    function TemplateForm()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $query="select * from `wp_crm_mailtemp` where user_id=".$user_id;
        $response = $wpdb->get_results($query, OBJECT);
        $res=$response[0];
        $smtp=json_decode($res->smtp); 
        $btn='<input type="submit" name="Save" value="Save">';
        
        echo '<form name="mfrm" id="mfrm" method="post"><table border="0" width="100%" cellpadding="5" cellspacing="6" id="mailtemp">';        
        echo '<tr><td colspan="2" align="center"><h1>Email Template</h1></td></tr>';
        echo '<tr><td colspan="2" align="right">'.$btn.'</td></tr>';
        echo '<tr><td width="30%" class="cptit">Header</td><td valign="middle">';
        wp_editor($res->mail_head, 'mail_header', array( 'media_buttons' => true,'tinymce' => true, 'textarea_rows' => 7, 'teeny' => true, 'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,code,spell,close' ) ) );
        echo '</td></tr>';
        echo '<tr><td class="cptit">Footer</td><td valign="middle">';
        wp_editor($res->mail_foot, 'mail_footer', array( 'media_buttons' => true, 'textarea_rows' => 7, 'teeny' => true, 'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,code,spell,close' ) ) );
        echo '</td></tr>';
        echo '<tr><td colspan="2">&nbsp;</td></tr>';
        echo '<tr><td colspan="2"><b>SMTP Config</b></td></tr>';
        echo '<tr><td width="30%" class="cptit">SMTP Port</td><td valign="middle"><input type="text" name="smtp_port" id="smtp_port" value="'.$smtp->smtp_port.'"></td></tr>';
        echo '<tr><td width="30%" class="cptit">SMTP Host</td><td valign="middle"><input type="text" name="smtp_host" id="smtp_host" value="'.$smtp->smtp_host.'"></td></tr>';
        echo '<tr><td width="30%" class="cptit">SMTP Email</td><td valign="middle"><input type="text" name="smtp_email" id="smtp_email" value="'.$smtp->smtp_email.'"></td></tr>';
        echo '<tr><td colspan="2" align="right">'.$btn.'</td></tr>';
        echo '</table><input type="hidden" name="instemp" value="1"><input type="hidden" name="mid" value="'.$res->id.'"></form>';        
    }
    function InsertMailTemplate()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $smp=array();
        if($_POST['smtp_port']): $smp['smtp_port']=$_POST['smtp_port'];endif;
        if($_POST['smtp_host']): $smp['smtp_host']=$_POST['smtp_host'];endif;
        if($_POST['smtp_email']): $smp['smtp_email']=$_POST['smtp_email'];endif;
        $smtp=Json_Format($smp);
        if($_POST['mid']):
            $query="update `wp_crm_mailtemp` set `mail_head`='".stripslashes($_POST['mail_header'])."',`mail_foot`='".stripslashes($_POST['mail_footer'])."',,`smtp`='".$smtp."' where id=".$_POST['mid'];
        else:
            $query="insert into `wp_crm_mailtemp` (`user_id`,`mail_head`,`mail_foot`,`smtp`) values ('".$user_id."','".stripslashes($_POST['mail_header'])."','".stripslashes($_POST['mail_footer'])."','".$smtp."')";
        endif;
        $wpdb->query($query);
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=mail_temp");        
        exit;
    }
    
}
class ManageMailTemp extends MailTemplate
{
    function display()
    {
        if($_POST['instemp']=="1"):
            $display.=$this->InsertMailTemplate();
        endif;
        $display.=$this->TemplateForm();
        return $display;
    }
}
?>