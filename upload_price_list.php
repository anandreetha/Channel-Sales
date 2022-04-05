<?php
class ManagePriceList
{
    function getProductList()
    {
        global $wpdb;
        $query="SELECT * FROM `wp_crm_products` where status=1";
        $result = $wpdb->get_results($query, OBJECT);
        $toolbar='<table width="95%"><tr><td align="left" width="10%"><a align="center" href="'.get_bloginfo('url').'/salesforce_reports/?action=users_list" title="Go back to users tree""><img src="'.plugins_url().'/salesforce_reports/tree/images/go-back-icon.png"></a></td>'
                . '<td width="60%" align="center"><a href="javascript:;" class="viewall" onclick="ViewAllPrice()">View all price lists</a></td>'
                . '<td align="right" width="30%">'
                . '<a align="center" href="javascript:;" title="Export Prices" onclick="ExportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/export-icon.png"></a>&nbsp;'
                . '<a align="center" href="javascript:;" title="Download current price flyer config" onclick="ExportPdf()"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-export.png"></a>&nbsp;&nbsp;'
                . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                . '<a align="center" id="importcsv" href="javascript:;" title="Import CSV file" onclick="ImportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/import-icon.png"></a>&nbsp;&nbsp;'
                /*. '<img src="'.plugins_url().'/salesforce_reports/tree/images/save-icon.png">&nbsp;&nbsp;<a href="javascript:;" title="Reset Form" onclick="CleanIT()"><img src="'.plugins_url().'/salesforce_reports/tree/images/cancel-icon.png">'*/
                . '</td></tr></table><br>';
        $savebtn='<tr><td colspan="7" align="right"><input type="button" value="Submit" style="margin:5px" onclick="SavePriceList()"><input type="button" value="Clear" style="margin:5px" onclick="CleanIT()"></td></tr><input type="hidden" name="pricfly" id="pricfly" value="1">';
        
        $output=$toolbar.'<form name="efrm" id="efrm"><table border="1" width="95%" class="exttbl" id="exttbl">';
        
        $output.=$savebtn;
        
        $output.='<tr>'
                . '<td class="extitle">No</td>'
                . '<td class="extitle">CRM SKU</td>'
                . '<td class="extitle">Non CRM SKU</td>'
                . '<td class="extitle prodname">Product Name</td>'
                . '<td class="extitle">Cost Price</td>'
                . '<td class="extitle" width="19%">Margin<br><input type="text" name="cpc" id="cpc" class="applymargin applycpc" value="0"> % <a href="javascript:;" onclick="CpcIT()">Apply</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="CleanIT()">Clear</a></td>'
                . '<td class="extitle">Selling Price</td>'
                . '</tr>';
        //$output.='<tr><td colspan="7" align="right">&nbsp;</td></tr>';
        $c=1;$costprice=0;$idarr=array();
        foreach($result as $rs):
            $amtun=unserialize($rs->pricing);
            $costprice+=$amtun->UnitPrice;
            $idarr[]=$rs->id;
            $output.='<tr>';
            $output.='<td class="extcontent">'.$c.'</td>';
            $output.='<td class="extcontent">'.$rs->ProductCode.'</td>';
            $output.='<td class="extcontent">KB000'.$rs->id.'</td>';
            $output.='<td class="extcontent">'.$rs->Name.'</td>';
            $output.='<td class="extcontent">'.$amtun->UnitPrice.'<input type="hidden" name="margin" id="margin_'.$rs->id.'" class="margin" value="'.$rs->id.'__'.$amtun->UnitPrice.'"></td>';    
            $output.='<td class="extcontent"><input type="text" name="cpc_'.$rs->id.'" id="cpc_'.$rs->id.'" class="cpc applycpc" value="0" onKeyup="ProductCpcIT(this.value,'.$rs->id.')"> %</td>';            
            $output.='<td class="extcontent">$<span id="cprice_'.$rs->id.'" class="cprice">'.$amtun->UnitPrice.'</span></td>';
            $output.='</tr>';
            
            $pdata=$rs->ProductCode.'__'.$rs->id.'__'.$rs->Name.'__'.$amtun->UnitPrice;
            $output.='<input type="hidden" name="product_price_'.$rs->id.'" id="product_price_'.$rs->id.'" value="'.$pdata.'">';
            $c++;
        endforeach;
        $allid=implode(',',$idarr);
        $output.='<input type="hidden" name="allid" id="allid" value="'.$allid.'"><input type="hidden" name="expcsv" id="expcsv">';
        $output.='<tr><td colspan="5" align="right"><span class="totprice">Total cost price: $ </span><span class="margin_txt">'.$costprice.'</span></span></td><td colspan="2" align="left"><span class="totprice">Total selling price: $ </span><span id="tot_cpc" class="margin_txt">'.$costprice.'</span></td></tr>';
        $output.='<textarea name="expdf" id="expdf" style="display:none;"></textarea>'.$savebtn;
        $output.='</table></form><br>'.$toolbar.'<br>';
        $output.='<link rel="stylesheet" type="text/css" media="screen" href="'.plugins_url() . '/salesforce_reports/lib/js/themes/redmond/jquery-ui.custom.css"><link rel="stylesheet" type="text/css" media="screen" href="'.plugins_url() . '/salesforce_reports/lib/js/jqgrid/css/ui.jqgrid.css">';
       // $output.='<div><a href="javascript:;" onclick="ExportPdf()">Export Pdf</a> <input type="button" value="Submit">&nbsp;<input type="button" value="Cancel"></div>';
        
        ?>
        <script type="text/javascript">
            function CpcIT()
            {
                var param=jQuery('.applymargin').val();
                jQuery('.cpc').val(param);var tcpc=0;
                var price=jQuery(".margin").map(function() {return jQuery(this).val();}).get().join(",");var pid=price.split(",");
                for(var p = 0; p < pid.length; p++) {var prod=pid[p]; 
                    var prodinfo=prod.split("__"); var product_id=prodinfo[0]; var price=prodinfo[1];
                    //var discount=price-(price*param/100);
                    var cpc=parseInt(price)+(price*param/100);
                    tcpc+=cpc;
                    jQuery('#cprice_'+product_id).html(cpc);
                }
                jQuery('#tot_cpc').html(tcpc);
            }
            function ProductCpcIT(param,id)
            {
                var prod=jQuery('#margin_'+id).val();
                var prodinfo=prod.split("__");var price=prodinfo[1];
                var cpc=parseInt(price)+(price*param/100);
                jQuery('#cprice_'+id).html(cpc); var totprice=0;
                var price=jQuery(".cprice").map(function() {return jQuery(this).html();}).get().join(",");var pid=price.split(",");
                for(var p = 0; p < pid.length; p++) {totprice+=parseInt(pid[p]);}
                jQuery('#tot_cpc').html(totprice);
            }
            function CleanIT()
            {
                var param=0;jQuery('#cpc').val(param);
                jQuery('.cpc').val(param);var tcpc=0;
                var price=jQuery(".margin").map(function() {return jQuery(this).val();}).get().join(",");var pid=price.split(",");
                for(var p = 0; p < pid.length; p++) {var prod=pid[p]; 
                    var prodinfo=prod.split("__"); var product_id=prodinfo[0]; var price=prodinfo[1];
                    //var discount=price-(price*param/100);
                    var cpc=parseInt(price)+(price*param/100);
                    tcpc+=cpc;
                    jQuery('#cprice_'+product_id).html(cpc);
                }
                jQuery('#tot_cpc').html(tcpc); 
            }
            function ImportCSV()
            {
               var dt='<div class="ui-widget-overlay priceoverlay" style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;"></div>'; 
               dt+='<div class="ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGridApp" dir="ltr" tabindex="-1" role="dialog" aria-labelledby="edithdjqGridApp" aria-hidden="false" style="width: 300px; height: 180px; z-index: 950; overflow: hidden; top: 357px; left: 425px; display: block;"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGridApp" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Select price file to upload</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelPop()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGridApp"><div><form name="FormPost" enctype="multipart/form-data" id="FrmGrid_jqGridApp" class="FormGrid" onsubmit="return false;" style="width:auto;overflow:auto;position:relative;height:auto;"><table id="TblGrid_jqGridApp" class="EditTable" cellspacing="0" cellpadding="0" border="0"><tbody><tr id="FormError" style="display:none"><td class="ui-state-error" colspan="2"></td></tr><tr style="display:none" class="tinfo"><td class="topinfo" colspan="2"></td></tr><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Price file</td><td class="DataTD">&nbsp;<input type="file" id="priceups" name="priceups" role="textbox" class="FormElement ui-widget-content ui-corner-all"><a href="<?php echo get_bloginfo('url');?>/salesforce_reports/?action=price_upload&view=sample" target="_blank"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/import_csv.png">sample CSV</a><br></td></tr><tr class="FormData" style="display:none"><td class="CaptionTD"></td><td colspan="1" class="DataTD"><input class="FormElement" id="id_g" type="text" name="jqGridApp_id" value="16"></td></tr></tbody></table><input type="hidden" name="importprice" id="importprice" value="1"></form><table border="0" cellspacing="0" cellpadding="0" class="EditTable" id="TblGrid_jqGridApp_2"><tbody><tr><td colspan="2"><hr class="ui-widget-content" style="margin:1px"></td></tr><tr id="Act_Buttons"><td class="navButton"></td><td class="EditButton"><a href="javascript:;" onclick="SubmitPrice()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a href="javascript:;" onclick="CancelPop()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr style="display:none" class="binfo"><td class="bottominfo" colspan="2"></td></tr></tbody></table></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>'; 
               jQuery("body").append(dt);
               var pos=jQuery('.prodname').position();jQuery('#editmodjqGridApp').css( "left", pos.left+"px" );
               jQuery('#editmodjqGridApp').css( "top", pos.top+"px" );
            }
            function ExportPdf()
            {
                jQuery('#expdf').val('');
                var htdt=jQuery('#exttbl').html();
                jQuery('#expdf').val(htdt);
                document.efrm.action="<?php echo get_bloginfo('url');?>/salesforce_reports/?action=price_upload";
                document.efrm.method="POST";
                document.efrm.submit();
                //var url="<?php echo get_bloginfo('url');?>/salesforce_reports/?action=price_upload";
                //jQuery.post(url,{ htdata: htdt, actview: "pdf" });
            }
            function CancelPop()
            {
                jQuery(".priceoverlay").remove();
                jQuery("#editmodjqGridApp").remove();
            }
            function SavePriceList()
            {
               var acpc=jQuery(".applycpc").map(function() {return jQuery(this).val();}).get().join(",");
               var pname=jQuery('#pname').val();
               var applycpc=acpc.split(",");var tcpc=0;
               for(var p = 0; p < applycpc.length; p++) {
                   var cpcval=applycpc[p];
                   tcpc+=parseInt(cpcval);
                }  
                if(tcpc=="0")
                {
                    alert("Please change you margin % to submit this");
                }
                else
                {
                    document.efrm.action=""; 
                    document.efrm.method="POST";
                    document.efrm.submit();
                }                
            }
            function ExportCSV()
            {
                jQuery('#expcsv').val(1);
                document.efrm.action=""; 
                document.efrm.method="POST";
                document.efrm.submit();
                jQuery('#expcsv').val('');
            }
            function SubmitPrice()
            {
                document.FormPost.action="";
                document.FormPost.method="POST";
                document.FormPost.submit();
            }
            function ViewAllPrice()
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                    dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 600px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 364px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Opportunity Details</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                    jQuery("body").append(dt);
                    jQuery.ajax({type: "POST",
                        data: "viewallprice=1",
                        success: function(data)
                        {               
                            jQuery("#dataloader").html(data);
                        }
                        });
            }
            function CancelSettings()
            {
                jQuery(".ui-widget-overlay").remove();
                jQuery(".rolesettings").remove(); 
            }
            function ViewPriceDetails(param)
            {
                //jQuery('.hidedetails').slideToggle('slow');
                jQuery('#price-details_'+param).slideToggle('slow');
            }
            function ChangeActivePricFlyer()
            {
                jQuery.post( "<?php echo get_bloginfo('url');?>/salesforce_reports/?action=price_upload", jQuery('form#pricefrm').serialize() );
                CancelSettings();
            }
        </script>   
        <style type="text/css">
            .extitle{font-weight: bold;color: #2e6e9e;font-size:12px;padding:5px;border-color:#a6c9e2;}
            .extcontent{font-size: 12px;padding:5px; border-color:#a6c9e2; }
            .exttbl{border:1px solid #a6c9e2;}
            #cpc,.cpc{border: 1px solid #a6c9e2;font-size:12px;padding:0px 5px;width:70px;}
            .totprice{font-weight: bold; font-size: 12px;margin-left:5px;}
            .margin_txt{font-size: 12px;margin-right:10px; }
            #content{width:1009px; margin: 0 auto;}    
            a.viewall{color:#2e6e9e;font-weight: bold; font-size: 12px; text-decoration:underline;}
            .PriceListTitle{color:#2e6e9e; font-size: 12px;}
            .hidedetails{display:none;}
        </style>    
        <?php
        echo $output;
    }
    function ProductSample()
    {
        global $wpdb; 
        $output.='No, SKU, Sales SKU, Product Name, Cost Price($), Selling Price($)';
        $output.= "\r\n";
        $query="SELECT * FROM `wp_crm_products` where status=1";
        $result = $wpdb->get_results($query, OBJECT);
        $c=1;$costprice=0;
        foreach($result as $rs):
            $amtun=unserialize($rs->pricing);
            $costprice+=$amtun->UnitPrice;
            $output.=$c.','.$rs->ProductCode.',KB000'.$rs->id.','.$rs->Name.','.$amtun->UnitPrice.','.$amtun->UnitPrice;
            $output.= "\r\n";            
            $c++;
        endforeach;  
        $output.= "\r\n";
        $output.= "\r\n";
        $obj=new CreatePDFPrice();
        return $obj->ExportCSV($output,'sampledata');
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
            $cp=$all[3];$margin=$_POST['cpc_'.$aid];        
            $sp=$cp+($cp*$margin/100);
            $total_cost+=$cp;
            $total_sell+=$sp;
            
            $args['product'][$n]['id']=$aid;
            $args['product'][$n]['crm_sku']=$all[0];
            $args['product'][$n]['pname']=$all[2];
            $args['product'][$n]['cprice']=$cp;
            $args['product'][$n]['sprice']=$sp;
            $args['product'][$n]['margin']=$margin;            
            $args['product'][$n]['sfpid']=$sfpid;
            $n++;
        endforeach;
        $args['totals']['cost_price']=$total_cost;
        $args['totals']['sell_price']=$total_sell;        
        $price_flyer=$this->Json_Format($args);
        
        $wpdb->query("update `wp_crm_priceflyer` set status='0' where user_id=".$user_id);
        $wpdb->query("insert into `wp_crm_priceflyer` (`user_id`,`price_flyer`,`status`)values('".$user_id."','".$price_flyer."','1')");
        $last_id=$wpdb->insert_id;
        $this->PriceDataInsert($args,$last_id,$user_id);
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=price_upload");
        exit;
    }
    function PriceDataInsert($args,$last_id,$user_id)
    {
        global $wpdb;
        foreach($args['product'] as $as):            
            $flds='`user_id`,`flyer_id`,`prod_sku`,`sales_sku`,`prodname`,`margin`,`cost_price`,`sell_price`,`product_id`';
            $vals="'".$user_id."','".$last_id."','".$as['id']."','".$as['crm_sku']."','".$as['pname']."','".$as['margin']."','".$as['cprice']."','".$as['sprice']."','".$as['sfpid']."'";
            $wpdb->query("insert into `wp_crm_pricedata` (".$flds.")values(".$vals.")");
        endforeach;
    }
    function ExportCProdCSV()
    {
        global $wpdb;
        $allid=explode(",",$_POST['allid']);
        $csv='No, CRM SKU, Non CRM SKU, Product Name, Cost Price, Margin %, Selling Price';
        $csv.= "\r\n";
        $n=1;$total_cost=0;$total_sell=0;
        foreach($allid as $aid):
            $all=explode("__",$_POST['product_price_'.$aid]);
            $cp=$all[3];$margin=$_POST['cpc_'.$aid];        
            $sp=$cp+($cp*$margin/100);
            $csv.=$n.','.$all[0].',KB000'.$all[1].','.$all[2].','.$cp.','.$margin.'%,'.$sp;
            $csv.= "\r\n";
            $total_cost+=$cp;
            $total_sell+=$sp;
            $n++;
        endforeach;
        $csv.= "\r\n";
        $csv.= "\r\n";
        $csv.= "\r\n";
        $csv.=',,,,Total cost price :'.$total_cost.',,Total Selling Price'.$total_sell;
        echo $csv;
        $filename = "Current_price".time().".csv";
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        exit;
    }
    function ViewAllPriceList()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $query="SELECT * from `wp_crm_priceflyer` where user_id=".$user_id." Order by id DESC";        
        $result = $wpdb->get_results($query, OBJECT);
        $output='<form name="pricefrm" id="pricefrm">';
        $output.='<table width="100%" cellspacing="6" cellpadding="5" border="0">
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
    function ImportPriceFly()
    {
        global $wpdb; 
        $user_id = get_current_user_id();
        $csv_file=plugin_dir_path( __FILE__ ).'import_price/'.time().$_FILES['priceups']['name'];
        move_uploaded_file($_FILES['priceups']['tmp_name'],$csv_file);
        
        $args=array();$total_cost=0;$n=0;
        if (($handle = fopen($csv_file, "r")) !== FALSE):
            fgetcsv($handle);   
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE):
                $num = count($data);
                for ($c=0; $c < $num; $c++):$col[$c] = $data[$c];endfor;                    
                $eid=explode("KB000",$col[2]);
                $id=$eid[1];
                $sprice=$col[5];    
                if(is_numeric($sprice)):
                   $query="SELECT * FROM `wp_crm_products` where id=".$id;
                   $result = $wpdb->get_results($query, OBJECT);
                   $sfpid=$result[0]->PId;
                   $amtun=unserialize($result[0]->pricing);                   

                   $args['product'][$n]['id']=$id;
                   $args['product'][$n]['crm_sku']=$result[0]->ProductCode;
                   $args['product'][$n]['pname']=$result[0]->Name;
                   $args['product'][$n]['cprice']=$amtun->UnitPrice;
                   $args['product'][$n]['sprice']=$sprice;
                   $args['product'][$n]['margin']="";            
                   $args['product'][$n]['sfpid']=$sfpid;
                   $total_cost+=$amtun->UnitPrice;
                   $total_sell+=$sprice;
                endif;
                $n++;
            endwhile;
            
            $args['totals']['cost_price']=$total_cost;
            $args['totals']['sell_price']=$total_sell;    
            $args['csv_path']=$csv_file;
            $price_flyer=$this->Json_Format($args);
            $wpdb->query("update `wp_crm_priceflyer` set status='0' where user_id=".$user_id);
            $wpdb->query("insert into `wp_crm_priceflyer` (`user_id`,`price_flyer`,`status`)values('".$user_id."','".$price_flyer."','1')");
            $last_id=$wpdb->insert_id;
            $this->PriceDataInsert($args,$last_id,$user_id);
            header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=price_upload");
            exit;
        endif;
        
    }
    function UpdatePriceflyer()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $wpdb->query("update `wp_crm_priceflyer` set status='0' where user_id=".$user_id);
        $wpdb->query("update `wp_crm_priceflyer` set status='1' where user_id=".$user_id." AND id=".$_POST['price_list']);
        exit;
    }
    function Json_Format($args='')
    {
        $jsonp=$args;
	header('Content-type: application/json');
	return json_encode($jsonp);
	exit;
    }
    function Displayprice()
    {
        $upsprice=GetRolesPermission('upload_price');
         if($upsprice!="2"):
             return 'You dont have a permsssion to access this page';
             exit;
         endif;
        if($_REQUEST['expdf']!=""):
            $obj=new CreatePDFPrice();
            $display=$obj->PriceGeneratepdf($_POST['expdf']); 
        elseif($_REQUEST['expcsv']=="1"): 
            $this->ExportCProdCSV();
        elseif($_POST['viewallprice']=="1"):
            $this->ViewAllPriceList();
        elseif($_POST['updatepriceflyer']=="1"):
            $this->UpdatePriceflyer();
        elseif($_REQUEST['view']=="sample"):
           $display=$this->ProductSample();     
        else:
            if($_POST['pricfly']=="1"): $this->AddPriceFly(); endif;
            if($_POST['importprice']=="1"): $this->ImportPriceFly(); endif;
            $display=$this->getProductList();
        endif;
        return $display;
    }
}
class CreatePDFPrice
{
    function PriceGeneratepdf($html1)
    {
        $html.='<table width="95%">';
        $html.=$html1;
        $html.='</table>';
        require_once(plugin_dir_path( __FILE__ ).'/pdf/tcpdf_include.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Anand P R');
        $pdf->SetTitle('Kukkuburra Product Price');
        $pdf->SetSubject('Kukkuburra Product Price');
        $pdf->SetKeywords('Kukkuburra, PDF, Product, SF, Price');

        // set default header data
        //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add a page
        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // add a page
        //$pdf->AddPage();

        $pdf->lastPage();
        $pdffile='price_file'.time().'.pdf';
        $pdf->Output($pdffile, 'D');
        exit;
    }
    function ExportCSV($data,$name='')
    {
        echo $data;
        if($name):$fname=$name;else:$fname="myFile";endif;
        $filename = $fname.".csv";
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        exit;
    }
}
