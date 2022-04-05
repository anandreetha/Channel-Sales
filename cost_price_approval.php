<?php
class CostPriceApproval
{
    function __construct()
    {
        
    }
    function getDisplay()
    {
        if($_POST['getcostprice']=="1"):
            $this->GetCostPrice();
        else:
            if($_POST['onecpapprove']=="1"):
                $this->ApproveCostPrice();
            endif;
            $this->GetApprovalForm();
        endif;
    }
    function GetCostPrice()
    {
        global $wpdb;
        $iud=$_POST['id'];
        $user_id=get_current_user_id();
        $idarr=array();
        
        $querynew="SELECT * FROM `wp_crm_costprice` where id=".$_POST['id'];
        $qsresult=$wpdb->get_results($querynew, OBJECT); 
        $cost_title=$qsresult[0]->pname;
        $group_name=$qsresult[0]->group_name;
        $price_flyer=$qsresult[0]->price_flyer;
        $pflyer=json_decode($price_flyer);
        
        
        $savebtn='<div><input type="button" value="Submit" style="margin:5px" onclick="SavePriceList()"><input type="button" value="Cancel" style="margin:5px" onclick="CancelSettings()"></div>';
        $dtable='<table width="100%" border="1" id="exttbl" class="exttbl">'
                . '<tbody><tr><td class="extitle">No</td><td class="extitle">CRM SKU</td><td class="extitle">Non CRM SKU</td><td class="extitle prodname">Product Name</td><td class="extitle">Cost Price</td><td class="extitle">New Cost Price</td></tr>';
        $n=1;
        foreach($pflyer->product as $qs):
            $idarr[]=$qs->id;
            $pdata=$qs->crm_sku.'__'.$qs->id.'__'.$qs->pname.'__'.$qs->cprice;
            $dtable.='<input type="hidden" name="product_price_'.$qs->id.'" id="product_price_'.$qs->id.'" value="'.$pdata.'">';
            $dtable.='<tr><td class="extcontent">'.$n.'</td><td class="extcontent">'.$qs->crm_sku.'</td><td class="extcontent">KB000'.$qs->id.'</td><td class="extcontent">'.$qs->pname.'</td><td class="extcontent">'.$qs->cprice.'</td><td class="extcontent"><input type="text" name="newcp_'.$qs->id.'" id="newcp_'.$qs->id.'" value="'.$qs->costnew_price.'"></td></tr>';
            $n++;
        endforeach;
        $allid=implode(',',$idarr);
        $dtable.='</tbody></table>'.$savebtn;

        echo '<form name="efrm"><table width="100%" border="0">'.$savebtn
        . '<tr><td colspan="3" style="margin-top:10px;"><b>Price list Name: </b>'.$cost_title. '</td></tr>'
                . '<tr>';
        echo '<td colspan="3"><b>Group Name: </b> '.$group_name.'</td></tr>';
        echo '<tr><td colspan="3">Status: <select name="approve_status"><option value="1">Pending</option><option value="2">Approve</option><option value="0">Reject</option>'
        . '</select></td></tr>';
        echo '<tr><td colspan="3"><div class="curcostprice">'.$dtable.'</div></td>';
        echo '</tr></table><input type="hidden" name="cpid" id="cpid" value="'.$_POST['id'].'">'
                . '<input type="hidden" name="allid" id="allid" value="'.$allid.'">'
                . '<input type="hidden" name="onecpapprove" id="onecpapprove" value="1"></form>';
        exit;  
    }
    function GetApprovalForm()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $select_query="select * from wp_crm_costprice where apid=".$user_id;
        $result=$wpdb->get_results($select_query, OBJECT);
        $output='<table class="list_price" border="1" width="100%">
            <tr>
                <td class="cptit">No</td>
                <td class="cptit">Price List Name</td>
                <td class="cptit">Group Name</td>
                <td class="cptit">Created by</td>
                <td class="cptit">Status</td>
                <td class="cptit">Actions</td>
            </tr>'; 
        $n=1;
            foreach($result as $rs):
                $apid=$rs->user_id;
                $apinfo = get_userdata($apid);
                if($rs->approve_status=="0"): $status='Rejected'; elseif($rs->approve_status=="1"): $status='Pending';elseif($rs->approve_status=="2"):$status='Approve';endif;
                $output.= '<tr class="mlist">
                <td class="podd">'.$n.'</td>
                <td class="podd">'.$rs->pname.'</td>
                <td class="podd">'.$rs->group_name.'</td>    
                <td class="podd">'.$apinfo->display_name.'</td>
                <td class="podd">'.$status.'</td>
                <td class="podd"><a align="center" href="javascript:;" title="Change Status" onclick="ChangeStatus('.$rs->id.')">Change</a></td>
            </tr>';
            $n++;    
            endforeach;
            if(count($result)=="0"):
                $output.= '<tr><td colspan="5"> No results Found</td></tr>';
            endif;
        $output.='</table>';
        echo $output;
        ?>
        <script type="text/javascript">
            function ChangeStatus(ids)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height:auto; z-index: 950; overflow: hidden; top: 241px; left: 364px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Approve cost price</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "getcostprice=1&id="+ids,
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                    }
                });
            }
            function CancelSettings()
            {
                jQuery(".quoteoverlay").remove();
                jQuery("#editmodjqGrid").remove();
            }
            function SavePriceList()
            {
                document.efrm.action="";
                document.efrm.method="POST";
                document.efrm.submit();
            }
        </script>
        <?php
    }
    function ApproveCostPrice()
    {
        global $wpdb;
        $allid=explode(",",$_POST['allid']);
        $user_id = get_current_user_id();
        $args=array();
        $n=0;$total_cost=0;$total_sell=0;
        
       
        foreach($allid as $aid):
            $query="SELECT PId FROM `wp_crm_products` where id=".$aid;
            $result = $wpdb->get_results($query, OBJECT);
            $sfpid=$result[0]->PId;
            
            $all=explode("__",$_POST['product_price_'.$aid]);
            $cp=$all[3];
         
            $args['product'][$n]['id']=$aid;
            $args['product'][$n]['crm_sku']=$all[0];
            $args['product'][$n]['pname']=$all[2];
            $args['product'][$n]['cprice']=$cp;
            $args['product'][$n]['costnew_price']=$_POST['newcp_'.$aid];       
            $args['product'][$n]['sfpid']=$sfpid;
            $n++;
        endforeach;
        $args['totals']['cost_price']=$total_cost;
        $args['totals']['sell_price']=$total_sell;        
        $price_flyer=Json_Format($args);
        
        $wpdb->query("update `wp_crm_costprice` set `price_flyer`='".$price_flyer."',`approve_status`='".$_POST['approve_status']."' where id=".$_POST['cpid']);
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=cp_approve");
        exit;
    }
}

