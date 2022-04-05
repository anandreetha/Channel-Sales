<?php
    class OpportunityActivity
    {
        function OpsActForm()
        {
            $cmpusr=$_POST['cmpusr'];
            $display='<form class="FormGrid" id="opsactivity" name="opsactivity" enctype="multipart/form-data"><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_jqGrid"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr>';
            
            $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Comments</td><td class="DataTD">&nbsp; 
                                <textarea name="comments" id="comments" class="FormElement ui-widget-content ui-corner-all" style="width:330px !important;min-height:125px;"></textarea></td></tr>';
            $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Upload document</td><td class="DataTD" colspan="2">&nbsp; 
                                
            <div class="input_fields_wrap">
                <input type="file" name="doc[]" id="doc" class="FormElement ui-widget-content ui-corner-all" multiple="multiple">
            </div>    
            <br style="clear:both;">select ur option<select name="postfeed"><option value="1">My Group</option><option value="2">Public</option></select>                
            </td></tr>';
            
            $display.='</table>';
            if($cmpusr=="1"):
                $display.='<input type="hidden" name="cmpdcpost" id="cmpdcpost" value="1">';
            endif;
            $display.='<input type="hidden" name="opscmt" id="opscmt" value="1"><input type="hidden" name="opsid" id="opsid" value="'.$_POST['opsid'].'"><input type="hidden" name="ops_activity" id="ops_activity" value="1"><table cellspacing="0" cellpadding="0" border="0" id="TblGrid_jqGrid_2" class="EditTable"><tbody><tr id="Act_Buttons"><td class="EditButton">';
                //$display.='<a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="PreviewQuote()" style="float:left;">Preview<span class="ui-icon ui-icon-disk"></span></a>';
                $display.='<a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="SaveOpsActivity()">Submit 
                <span class="ui-icon ui-icon-disk"></span></a><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="CancelQuote()">Cancel 
                <span class="ui-icon ui-icon-close"></span></a></td></tr><tr class="binfo" style="display:none"><td colspan="2" class="bottominfo"></td></tr></tbody></table></form>';
            
            echo $display;
        }
        function OpsActHistory()
        {
            global $wpdb;
            $opsid=$_POST['opsid'];
            $cmpusr=$_POST['cmpusr'];
            $display='<table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="OpsHostTbl" style="margin:20px;">';
            $display.='<tr><td colspan="2"><b>Previous History</b></td></tr>';
            $display.='<tr class="containerinside"><td colspan="2"><hr></td></tr>';
            $display.='<tr id="docpush"><td colspan="2"><span id="opshistnewe"></span></td></tr>';
            if($cmpusr=="1"):
                $extra=" AND (only_group=1 OR is_public=1) ";
            else:  
                $extra=" AND (only_group=2 OR is_public=1) ";
            endif;            
            $query="SELECT * FROM `wp_crm_discussion` where ops_id=".$opsid.$extra." Order by id DESC";
            $result = $wpdb->get_results($query);
            foreach($result as $res):
                $cdate=date('d-m-Y H:i:s', strtotime($res->created_at)); //print_r($res->document);
                $document=json_decode($res->document);
                $docdisplay='';
                foreach($document->files as $dc):
                    $docdisplay.='<a href="'.$dc.'" target="_blank"><img src="'.plugins_url().'/salesforce_reports/tree/images/attach-icon.png" width="16" target="_blank"></a>&nbsp;&nbsp;';
                endforeach;
                $display.='<tr><td>'.$res->comments.'&nbsp;&nbsp;&nbsp;'.$docdisplay.'<br><i><span style="color:#666;">'.$cdate.'</span></i></td><td></td></tr>';
                $display.='<tr><td colspan="2"><hr></td></tr>';
            endforeach;
            $display.='</table>';
            echo $display;
        }
        function SaveOpsActivity()
        {
            global $wpdb,$current_user;
            get_currentuserinfo();
            $user_id = get_current_user_id();
            $cdate= date('Y-m-d H:i:s');
            $cmpdcpost=$_POST['cmpdcpost'];
                    
            $doc=array();
            $docdisplay='';
            if(isset($_FILES['doc'])) {
                $file = $_FILES['doc'];
                for($i = 0; $i < count($file['name']); $i++){
                    $image = array(
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'size' => $file['size'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i]
                    );
                    if($image['name']):
                        $imgname='discussion_attach/'.time().$image['name'];
                        $att_file=plugin_dir_path( __FILE__ ).$imgname;
                        $fullattfile=plugins_url()."/salesforce_reports/".$imgname;
                        move_uploaded_file($image['tmp_name'],$att_file);
                        $doc['files'][]=$fullattfile;     
                        $docdisplay.='<a href="'.$fullattfile.'" target="_blank"><img src="'.plugins_url().'/salesforce_reports/tree/images/attach-icon.png" width="16"></a>&nbsp;&nbsp;';
                    endif;    
                }
            }
            if($cmpdcpost=="1"): $cgrp="1"; else: $cgrp="2"; endif;
            if($_POST['postfeed']=="1"):
               $mygrp=$cgrp;$inuser="";
            elseif($_POST['postfeed']=="2"):
               $mygrp="";$inuser="1";   
            endif;
            
            $opsquery="insert into `wp_crm_discussion` (`user_id`,`ops_id`,`comments`,`document`,`created_at`,`only_group`,`is_public`) values ('".$user_id."','".$_POST['opsid']."','".$_POST['comments']."','".Json_Format($doc)."','".$cdate."','".$mygrp."','".$inuser."')";
            $wpdb->query($opsquery);
            $ops_id=$wpdb->insert_id;
            //echo $ops_id;
            $output=array();
            $cdatenew=date('d-m-Y H:i:s', strtotime($cdate));
            $display.='<tr><td>'.$_POST['comments'].'&nbsp;&nbsp;&nbsp;'.$docdisplay.'<br><i><span style="color:#666;">'.$cdatenew.'</span></i></td><td></td></tr>';
            $display.='<tr><td colspan="2"><hr></td></tr>';
            $output['first_data']=$display;
            echo Json_Format($output);
            exit;
            
        }
        
        function IncludeScriptsVal()
        {
            ?>
            <script type="text/javascript">
                function SaveOpsActivity()
                {
                    var comments=jQuery('#comments').val();
                    if (!jQuery.trim(comments).length > 0)
                    {
                        alert("Please enter your comments");
                        jQuery('#comments').focus();
                    }
                    else
                    {
                                                
                        var formData = new FormData(jQuery('form#opsactivity')[0]);
                        jQuery.ajax({
                            url: '',
                            type: 'POST',
                            data: formData,
                            async: false,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                               jQuery(data.first_data).insertAfter( jQuery( ".containerinside" ) );
                               jQuery('#opsactivity').trigger("reset");                                
                              }
                        });
                        return false;
                    }
                }
                jQuery(document).ready(function() {
                    var max_fields      = 10; //maximum input boxes allowed
                    var wrapper         = jQuery(".input_fields_wrap"); //Fields wrapper
                    var add_button      = jQuery(".add_field_button"); //Add button ID

                    var x = 1; //initlal text box count
                    jQuery(add_button).click(function(e){ //on add input button click
                        e.preventDefault();
                        if(x < max_fields){ 
                            x++;
                            jQuery(wrapper).append('<div class="docattach" style="clear:both;"><input style="float:left; margin-right:10px;margin-top:10px;" type="file" name="doc[]" id="doc" class="FormElement ui-widget-content ui-corner-all"><a href="#" class="remove_field"><img style="margin-top:15px;" src="<?php echo plugins_url();?>/salesforce_reports/tree/images/cancel-icon.png" width="15"></a></div>'); //add input box
                        }
                    });

                    jQuery(wrapper).on("click",".remove_field", function(e){ //user click on remove text
                        e.preventDefault(); jQuery(this).parent('div').remove(); x--;
                    })
                });
            </script>
            <?php
        }
        function displayOpsAct()
        {
           if($_POST['opscmt']=="1"):
               $this->SaveOpsActivity();               
           else:
                $this->OpsActForm(); 
                $this->OpsActHistory();
                $this->IncludeScriptsVal();
           endif;            
           exit;
        }
        
    }
?>