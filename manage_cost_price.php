<?php
class ManageCostPrice
{
   function ListCPriceflyer()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $select_query="select * from wp_crm_costprice where user_id=".$user_id;
        $result=$wpdb->get_results($select_query, OBJECT);
        
        echo '<table class="list_price" border="1" width="100%">
            <tr>
                <td colspan="6" align="right" class="adtit">
                <a align="center" id="importcsv" href="javascript:;" title="Import CSV file" onclick="ImportCSV()"><img width="16" src="'.plugins_url().'/salesforce_reports/tree/images/import-icon.png"></a>&nbsp;&nbsp;
                <a href="javascript:;" onclick="CreatePriceFiles()"><img src="'.plugins_url().'/salesforce_reports/tree/images/add.png"></a></td>
            </tr>
            <tr>
                <td class="cptit">No</td>
                <td class="cptit">Price List Name</td>
                <td class="cptit">Group Name</td>
                <td class="cptit">Approver</td>
                <td class="cptit">Status</td>
                <td class="cptit">Actions</td>
            </tr>'; 
            $n=1;
            foreach($result as $rs):
                $apid=$rs->apid;
                $apinfo = get_userdata($apid);
                if($rs->approve_status=="0"): $status='Rejected'; elseif($rs->approve_status=="1"): $status='Pending';elseif($rs->approve_status=="2"):$status='Approve';endif;
                echo '<tr class="mlist">
                <td class="podd">'.$n.'</td>
                <td class="podd">'.$rs->pname.'</td>
                <td class="podd">'.$rs->group_name.'</td>    
                <td class="podd">'.$apinfo->display_name.'</td>
                <td class="podd">'.$status.'</td>
                <td class="podd"><a align="center" href="javascript:;" title="Export Prices" onclick="ExportCSV()"><img width="16" src="'.plugins_url().'/salesforce_reports/tree/images/export-icon.png"></a>&nbsp;
                <a align="center" href="javascript:;" title="Download current price flyer config" onclick="ExportPdf()"><img width="16" src="'.plugins_url().'/salesforce_reports/tree/images/pdf-export.png"></a>&nbsp;&nbsp;<a href="javascript:;" onclick="Inivtepeople('.$rs->id.')"><img width="16" src="'.plugins_url().'/salesforce_reports/tree/images/people-icon.png"></a></td>
            </tr>';
            $n++;    
            endforeach;
            if(count($result)=="0"):
                echo '<tr><td colspan="5"> No results Found</td></tr>';
            endif;
        echo '</table>';
        ?>
        <script type="text/javascript">       
            function CreatePriceFiles()
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height:auto; z-index: 950; overflow: hidden; top: 241px; left: 364px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create cost price</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "cpupload=1",
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                    }
                });
            }
            function Inivtepeople(pid)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height:auto; z-index: 950; overflow: hidden; top: 241px; left: 364px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Assign Resellers companies to this cost price</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "cp_invite=1&id="+pid,
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
            function MoveRightIT(cpid)
            {
                var ids=jQuery('.pickusr').find('input[name=pickusers]:checked').map(function() {return this.value;}).get().join(',');var idval=ids.split(",");
                jQuery.ajax({type: "POST",data: "mvrght=1&ids="+ids+"&cpid="+cpid});
                for(var i=0; i< idval.length;i++){
                    jQuery('.pickusr').find('li.usr_'+idval[i]).find('input[name=pickusers]:checked').prop( "checked", false );
                    var slusr=jQuery('.pickusr').find('li.usr_'+idval[i]).clone();
                    jQuery('.pickusr').find('li.usr_'+idval[i]).remove();
                    jQuery('.slusr').append(slusr);                    
                }                 
                 
            }
            function MoveLeftIT()
            {
                var ids=jQuery('.slusr').find('input[name=pickusers]:checked').map(function() {return this.value;}).get().join(',');var idval=ids.split(",");
                for(var i=0; i< idval.length;i++){
                    jQuery('.slusr').find('li.usr_'+idval[i]).find('input[name=pickusers]:checked').prop( "checked", false );
                    var slusr=jQuery('.slusr').find('li.usr_'+idval[i]).clone();
                    jQuery('.slusr').find('li.usr_'+idval[i]).remove();
                    jQuery('.pickusr').append(slusr);                    
                }
                 
            }
            function ImportCSV()
            {
               var dt='<div class="ui-widget-overlay quoteoverlay" style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;"></div>'; 
               dt+='<div class="ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" tabindex="-1" role="dialog" aria-labelledby="edithdjqGridApp" aria-hidden="false" style="width: 300px; height: 180px; z-index: 950; overflow: hidden; top: 357px; left: 425px; display: block;"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGridApp" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Select price file to upload</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGridApp"><div><form name="FormPost" enctype="multipart/form-data" id="FrmGrid_jqGridApp" class="FormGrid" onsubmit="return false;" style="width:auto;overflow:auto;position:relative;height:auto;"><table id="TblGrid_jqGridApp" class="EditTable" cellspacing="0" cellpadding="0" border="0"><tbody><tr id="FormError" style="display:none"><td class="ui-state-error" colspan="2"></td></tr><tr style="display:none" class="tinfo"><td class="topinfo" colspan="2"></td></tr><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Price file</td><td class="DataTD">&nbsp;<input type="file" id="priceups" name="priceups" role="textbox" class="FormElement ui-widget-content ui-corner-all"><a href="<?php echo get_bloginfo('url');?>/salesforce_reports/?action=price_upload&view=sample" target="_blank"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/import_csv.png">sample CSV</a><br></td></tr><tr class="FormData" style="display:none"><td class="CaptionTD"></td><td colspan="1" class="DataTD"><input class="FormElement" id="id_g" type="text" name="jqGridApp_id" value="16"></td></tr></tbody></table><input type="hidden" name="importprice" id="importprice" value="1"></form><table border="0" cellspacing="0" cellpadding="0" class="EditTable" id="TblGrid_jqGridApp_2"><tbody><tr><td colspan="2"><hr class="ui-widget-content" style="margin:1px"></td></tr><tr id="Act_Buttons"><td class="navButton"></td><td class="EditButton"><a href="javascript:;" onclick="SubmitPrice()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a href="javascript:;" onclick="CancelSettings()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr style="display:none" class="binfo"><td class="bottominfo" colspan="2"></td></tr></tbody></table></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>'; 
               jQuery("body").append(dt);
               var pos=jQuery('.prodname').position();jQuery('#editmodjqGridApp').css( "left", pos.left+"px" );
               jQuery('#editmodjqGridApp').css( "top", pos.top+"px" );
            }
            function ViewIT(id)
            {
               jQuery('.hidemychild').hide();
               jQuery('.mychild_'+id).show();
            }
            function SlideClkView()
            {
                jQuery('.curcostprice').slideToggle('slow');
            }
        </script> 
        <?php
    }
    function ViewAllPriceList()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $query="SELECT * from `wp_crm_costprice` where user_id=".$user_id." Order by id DESC";        
        $result = $wpdb->get_results($query, OBJECT);
        $output='<form name="pricefrm" id="pricefrm">';
        $toolbar='<table width="95%"><tr><td align="left" width="10%"></td>'
                . '<td align="right" width="30%">'
                . '<a align="center" href="javascript:;" title="Export Prices" onclick="ExportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/export-icon.png"></a>&nbsp;'
                . '<a align="center" href="javascript:;" title="Download current price flyer config" onclick="ExportPdf()"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-export.png"></a>&nbsp;&nbsp;'
                . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                . '<a align="center" id="importcsv" href="javascript:;" title="Import CSV file" onclick="ImportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/import-icon.png"></a>&nbsp;&nbsp;'
                /*. '<img src="'.plugins_url().'/salesforce_reports/tree/images/save-icon.png">&nbsp;&nbsp;<a href="javascript:;" title="Reset Form" onclick="CleanIT()"><img src="'.plugins_url().'/salesforce_reports/tree/images/cancel-icon.png">'*/
                . '</td></tr></table><br>';
        $output.=$toolbar.'<table width="100%" cellspacing="6" cellpadding="5" border="0">
                                        <tbody>';
        $n=1;
        foreach($result as $rs): 
            if($rs->status=="1"): $rschk='checked="checked"'; else: $rschk=""; endif;
            $price_flyer=json_decode($rs->price_flyer);
            $data='<table border="1" width="95%" class="exttbl" id="exttbl">'
                . '<td class="extitle">No</td>'
                . '<td class="extitle">CRM SKU</td>'
                . '<td class="extitle">Non CRM SKU</td>'
                . '<td class="extitle prodname">Product Name</td>'
                . '<td class="extitle">Cost Price</td>'
                . '<td class="extitle" width="19%">Margin %</td>'
                . '<td class="extitle">Selling Price</td>'
                . '</tr>';
            $c=1;
            
            foreach($price_flyer->product as $ps):
                $data.='<tr>';
                $data.='<td class="extcontent">'.$c.'</td>';
                $data.='<td class="extcontent">'.$ps->crm_sku.'</td>';
                $data.='<td class="extcontent">KB000'.$ps->id.'</td>';
                $data.='<td class="extcontent">'.$ps->pname.'</td>';
                $data.='<td class="extcontent">'.$ps->cprice.'</td>';
                $data.='<td class="extcontent">'.$ps->margin.'</td>';
                $data.='<td class="extcontent">'.$ps->cprice.'</td>';
                $data.='</tr>';
                $c++;
            endforeach;
            $costprice=$price_flyer->totals->cost_price;
            $sellprice=$price_flyer->totals->sell_price;
            
            $data.='<tr><td colspan="5" align="right"><span class="totprice">Total cost price: $ </span><span class="margin_txt">'.$costprice.'</span></span></td><td colspan="2" align="left"><span class="totprice">Total selling price: $ </span><span id="tot_cpc" class="margin_txt">'.$sellprice.'</span></td></tr></table>';
            $output.= '<tr>'
                    . '<td class="ipbox" width="2%" valign="top"><input type="radio" name="price_list" value="'.$rs->id.'" '.$rschk.'></td>'
                    . '<td class="ipbox PriceListTitle"><span class="totprice">Price List '.$n.'</span>'
                    . '<br>'
                    . '&nbsp;Total selling price: $ <span id="tot_cpc" class="margin_txt">'.$sellprice.'</span>'
                    . '<a href="javascript:;" style="float:right;text-decoration:underline;" onclick="ViewPriceDetails('.$rs->id.')">View price</a><br>'
                    . '<div class="hidedetails" id="price-details_'.$rs->id.'">'.$data.'</div>'
                    . '</td>'
                    . '</tr>';
            
            $output.='<tr><td colspan="3"><hr style="margin-bottom:5px;margin-top:5px;"></td></tr>';
            $n++;                            
        endforeach;
         $output.='<input type="hidden" name="updatepriceflyer" id="updatepriceflyer" value="1"></tbody></table><br><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_list1_2"><tbody><tr><td colspan="2"><hr class="ui-widget-content" style="margin:1px"></td></tr><tr id="Act_Buttons"><td class="navButton"><a style="display: none;" id="pData" class="fm-button ui-state-default ui-corner-left"><span class="ui-icon ui-icon-triangle-1-w"></span></a><a style="display: none;" id="nData" class="fm-button ui-state-default ui-corner-right"><span class="ui-icon ui-icon-triangle-1-e"></span></a></td><td class="EditButton"><a onclick="ChangeActivePricFlyer()" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a onclick="CancelSettings()" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr style="display:none" class="binfo"><td class="bottominfo" colspan="2"></td></tr></tbody></table>';
        $output.= '</form>';
        echo $output;
        exit;
    }
    function AddPriceFly()
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
        $price_flyer=$this->Json_Format($args);
        
        $appid=$this->GetApproverId();
        $user_info = get_userdata($appid);
        $wpdb->query("insert into `wp_crm_costprice` (`user_id`,`pname`,`price_flyer`,`approve_status`,`apid`,`group_name`)values('".$user_id."','".$_POST['pname']."','".$price_flyer."','1','".$appid."','".$_POST['group_name']."')");
        $last_id=$wpdb->insert_id;
        //header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=cp_list");
        echo $user_info->display_name."___".$last_id;
        exit;
    }
    function GetApproverId()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        
        $user = new WP_User( $user_id );
        $user_roles=$user->roles[0]; 
        
        $query="select role_name from `wp_crm_roleconfig` where cost_approve=1";
        $result = $wpdb->get_results($query, OBJECT);
        $aprole=$result[0]->role_name;
        
        
        $queryrs="SELECT T2.id 
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
        $res = $wpdb->get_results($queryrs, OBJECT);    
        foreach($res as $rs):
            $user = new WP_User( $rs->id );
            if($aprole==$user->roles[0]):
                return $rs->id;
            endif;
        endforeach;
    }
    function MovetoRight()
    {
        global $wpdb;
        $ids=explode(",",$_POST['ids']);
        foreach($ids as $id):
            $wpdb->query("insert into `wp_crm_cpuser` (`user_id`,`cpid`)values('".$id."','".$_POST['cpid']."')");
            $last_id=$wpdb->insert_id;            
        endforeach;
        exit;
    }
    function Json_Format($args='')
    {
        $jsonp=$args;
	header('Content-type: application/json');
	return json_encode($jsonp);
	exit;
    }
}
class ManageCPCost extends ManageCostPrice
{
    function __construct()
    {
        if($_POST['pricfly']=="1"): $this->AddPriceFly(); endif;
        if($_POST['cpupload']):
            $cp=new CostPriceList();
            echo $cp->DisplayCostprice();exit;
        endif;
        if($_POST['cp_invite']=="1"): 
            $cp=new CostPriceList();
            echo $cp->InviteUsers();exit;
        endif;
        if($_POST['mvrght']=="1"):
            $this->MovetoRight();
        endif;
    }
    function display()
    {
        if($_REQUEST['action']=="cp_list"):
            return $this->ListCPriceflyer();
        endif;
        
    }
}

