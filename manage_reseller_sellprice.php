<?php
class ManageResellerSell
{
    function Displaysprice()
    {
        if($_POST['getsproduct']=="1"): 
            $this->getSellerProductList();
        elseif($_POST['resell_sprice']=="1"):
            $this->InsertResellerSPrice();
        elseif($_POST['viewresprice']=="1"):
            $this->ViewResellerSprice();
        else:
            $this->GetResellerSellPrice();
        endif;
    }
    function GetResellerSellPrice()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $query="SELECT * from `wp_crm_reseller_sellprice` where user_id=".$user_id." Order by id DESC";        
        $result = $wpdb->get_results($query, OBJECT);
        $output='<form name="pricefrm" id="pricefrm">';
        $output.='<table width="100%" cellspacing="6" cellpadding="5" class="list_price" border="1" width="100%">
                                        <tbody>';
        $output.='<tr><td align="right" colspan="6" class="podd">'
               // . '<a align="center" href="javascript:;" title="Export Prices" onclick="ExportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/export-icon.png"></a>&nbsp;'
               // . '<a align="center" href="javascript:;" title="Download current price flyer config" onclick="ExportPdf()"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-export.png"></a>&nbsp;&nbsp;'
                . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                . '<a href="javascript:;" onclick="CreateNewSprice()"><img src="'.plugins_url().'/salesforce_reports/tree/images/create-new.png"></a>'
               // . '<a align="center" id="importcsv" href="javascript:;" title="Import CSV file" onclick="ImportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/import-icon.png"></a>&nbsp;&nbsp;'
                /*. '<img src="'.plugins_url().'/salesforce_reports/tree/images/save-icon.png">&nbsp;&nbsp;<a href="javascript:;" title="Reset Form" onclick="CleanIT()"><img src="'.plugins_url().'/salesforce_reports/tree/images/cancel-icon.png">'*/
                . '</td></tr>';
        $output.=''
                . '<td class="cptit">No</td>'
                . '<td class="cptit">Price List Name</td>'
                . '<td class="cptit">Total Cost Price</td>'
                . '<td class="cptit">Total Selling Price</td>'
                . '<td class="cptit">Status</td>'
                . '<td class="cptit">Action</td>'
                . '</tr>';
        $n=1;
        foreach($result as $rs): 
            if($rs->status=="0"):$status="In Active"; $simg="cancel-icon.png";$clk="ChangeActive(".$rs->id.",'1')";else:$status="Active"; $simg="accept-icon.png";$clk="ChangeActive(".$rs->id.",'0')";endif;
            $imgfls='<a href="javascript:;" onclick="'.$clk.'"><img src="'.plugins_url().'/salesforce_reports/tree/images/'.$simg.'" width="16"></a>';
            $price_flyer=json_decode($rs->price_flyer);             
            $c=1;
            $output.='<tr>';
            $output.='<td class="podd">'.$c.'</td>';
            $output.='<td class="podd">'.$rs->pname.'</td>';
            $output.='<td class="podd">$'.$price_flyer->totals->cost_price.'</td>';
            $output.='<td class="podd">$'.$price_flyer->totals->sell_price.'</td>';
            $output.='<td class="podd">'.$status.'</td>';
            $output.='<td class="podd">'.$imgfls.' | <a href="javascript:;" onclick="ViewResSellPrice('.$rs->id.')">View</a></td>';
            $output.='</tr>';
            $c++;            
            $n++;                            
        endforeach;
        if(count($result)=="0"):
            $output.='<tr><td colspan="6" align="center" class="podd">No selling price file found yet.</td></tr>';
        endif;
        $output.='</tbody></table>';
        $output.= '</form>';
        ?>
        <script type="text/javascript">
            function CreateNewSprice()
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 264px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create Selling price</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "getsproduct=1",
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
            function CheckAllSProd()
            {
                if(jQuery("#checkallproducts").is(':checked'))
                {
                    jQuery(".sell_produt").attr("checked", "checked");
                }
                else
                {
                    jQuery(".sell_produt").removeAttr("checked");
                }
            }
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
            function SavePriceList()
            {
               var acpc=jQuery(".applycpc").map(function() {return jQuery(this).val();}).get().join(",");
               var chkval = jQuery('.sell_produt:checkbox:checked').map(function() {return this.value;}).get();
               jQuery('#allid').val(chkval.join(","));
               var pname=jQuery('#pname').val();
               var applycpc=acpc.split(",");var tcpc=0;
               for(var p = 0; p < applycpc.length; p++) {
                   var cpcval=applycpc[p];
                   tcpc+=parseInt(cpcval);
                }
                if(pname=="")
                {
                    alert("Please enter your pricelist name");
                    jQuery('#pname').focus();
                }
                else if(chkval.length<=0)
                {
                    alert("Please select your products to submit");
                }
                else
                {
                    document.efrm.action=""; 
                    document.efrm.method="POST";
                    document.efrm.submit();
                }                
            }
            function ViewResSellPrice(id)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 364px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">View Selling price</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);  
                jQuery.ajax({type: "POST",
                    data: "viewresprice=1&ids="+id,
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                    }
                    });
            }
        </script>    
        <?php
        echo $output;
    }
    function ViewResellerSprice()
    {
        
        global $wpdb; 
        $query="SELECT * from `wp_crm_reseller_sellprice` where id=".$_POST['ids'];        
        $result = $wpdb->get_results($query, OBJECT);
        $rdata=$result[0];
        $output='<form name="efrm" id="efrm"><table border="1" width="95%" class="exttbl" id="exttbl">';
        $output.='<tr><td colspan="7" class="extitle"><b>Price List Name : </b> '.$rdata->pname.'</td></tr>';
        $output.='<tr>'
                . '<td class="extitle">CRM SKU</td>'
                . '<td class="extitle">Non CRM SKU</td>'
                . '<td class="extitle prodname">Product Name</td>'
                . '<td class="extitle">Cost Price</td>'
                . '<td class="extitle">Selling Price</td>'
                . '</tr>';
        $price_flyer=json_decode($rdata->price_flyer);
        $c=1;$costprice=0;$idarr=array();
        foreach($price_flyer->product as $rs):
            $output.='<tr>';
            $output.='<td class="extcontent">'.$rs->crm_sku.'</td>';
            $output.='<td class="extcontent">KB000'.$rs->id.'</td>';
            $output.='<td class="extcontent">'.$rs->pname.'</td>';
            $output.='<td class="extcontent">'.$rs->cprice.'</td>';    
            $output.='<td class="extcontent">$<span id="cprice_'.$rs->id.'" class="cprice">'.$rs->sprice.'</span></td>';
            $output.='</tr>';
            $totcprice+=$rs->cprice;
            $totsprice+=$rs->sprice;
            $c++;
        endforeach;
        $output.='<tr><td colspan="3" align="right"><span class="totprice">Total cost price: $ </span><span class="margin_txt">'.$totcprice.'</span></span></td><td colspan="2" align="left"><span class="totprice">Total selling price: $ </span><span id="tot_cpc" class="margin_txt">'.$totsprice.'</span></td></tr>';
        $output.='</table></form><br><br>';
        echo $output;
        exit;
    }
    function getSellerProductList()
    {
        
        global $wpdb;
        $rsid=GetmyResellerCompanyId();
        
        $cpquery="SELECT cpid FROM `wp_crm_cpuser` where user_id=".$rsid." limit 0,1";
        $cpres = $wpdb->get_results($cpquery, OBJECT);
        
        $query="SELECT * FROM `wp_crm_costprice` where approve_status=2 AND id=".$cpres[0]->cpid;
        $result = $wpdb->get_results($query, OBJECT); 
        $toolbar='';
        $savebtn='<tr><td colspan="7" align="right"><input type="button" value="Submit" style="margin:5px" onclick="SavePriceList()"><input type="button" value="Clear" style="margin:5px" onclick="CleanIT()"></td></tr><input type="hidden" name="pricfly" id="pricfly" value="1">';
        
        $output=$toolbar.'<form name="efrm" id="efrm"><table border="1" width="95%" class="exttbl" id="exttbl"><input type="hidden" name="cpid" id="cpid" value="'.$cpres[0]->cpid.'"><input type="hidden" name="res_comp_id" id="res_comp_id" value="'.$rsid.'">';
        
        $output.=$savebtn;
        $output.='<tr><td colspan="7" class="extitle">Price List Name <input type="text" name="pname" id="pname" ></td></tr>';
        $output.='<tr>'
                . '<td class="extitle"><input type="checkbox" id="checkallproducts" class="checkallproducts" name="checkallproducts" onclick="CheckAllSProd()"></td>'
                . '<td class="extitle">CRM SKU</td>'
                . '<td class="extitle">Non CRM SKU</td>'
                . '<td class="extitle prodname">Product Name</td>'
                . '<td class="extitle">Cost Price</td>'
                . '<td class="extitle" width="19%">Margin<br><input type="text" name="cpc" id="cpc" class="applymargin applycpc" value="0"> % <a href="javascript:;" onclick="CpcIT()">Apply</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="CleanIT()">Clear</a></td>'
                . '<td class="extitle">Selling Price</td>'
                . '</tr>';
        //$output.='<tr><td colspan="7" align="right">&nbsp;</td></tr>';
        $resdata=$result[0];
        $pflyer=json_decode($resdata->price_flyer);
        //print"<pre>";print_r($pflyer);print"</pre>";
        
        $c=1;$costprice=0;$idarr=array();
        foreach($pflyer->product as $rs):
            
            $idarr[]=$rs->id;
            $output.='<tr>';
            $output.='<td class="extcontent"><input type="checkbox" name="sell_produt" class="sell_produt" value="'.$rs->id.'"></td>';
            $output.='<td class="extcontent">'.$rs->crm_sku.'</td>';
            $output.='<td class="extcontent">KB000'.$rs->id.'</td>';
            $output.='<td class="extcontent">'.$rs->pname.'</td>';
            $output.='<td class="extcontent">$'.$rs->costnew_price.'<input type="hidden" name="margin_'.$rs->id.'" id="margin_'.$rs->id.'" class="margin" value="'.$rs->id.'__'.$rs->costnew_price.'"></td>';    
            $output.='<td class="extcontent"><input type="text" name="cpc_'.$rs->id.'" id="cpc_'.$rs->id.'" class="cpc applycpc" value="0" onKeyup="ProductCpcIT(this.value,'.$rs->id.')"> %</td>';            
            $output.='<td class="extcontent">$<span id="cprice_'.$rs->id.'" class="cprice">'.$rs->costnew_price.'</span></td>';
            $output.='</tr>';
            
            $pdata=$rs->crm_sku.'__'.$rs->id.'__'.$rs->pname.'__'.$rs->costnew_price;
            $output.='<input type="hidden" name="product_price_'.$rs->id.'" id="product_price_'.$rs->id.'" value="'.$pdata.'">';
            $c++;
        endforeach;
        $allid=implode(',',$idarr);
        $output.='<input type="hidden" name="allid" id="allid"><input type="hidden" name="resell_sprice" id="resell_sprice" value="1">';
        $output.='<tr><td colspan="5" align="right"><span class="totprice">Total cost price: $ </span><span class="margin_txt">'.$costprice.'</span></span></td><td colspan="2" align="left"><span class="totprice">Total selling price: $ </span><span id="tot_cpc" class="margin_txt">'.$costprice.'</span></td></tr>';
        $output.='<textarea name="expdf" id="expdf" style="display:none;"></textarea>'.$savebtn;
        $output.='</table></form><br>'.$toolbar.'<br>';
        // $output.='<div><a href="javascript:;" onclick="ExportPdf()">Export Pdf</a> <input type="button" value="Submit">&nbsp;<input type="button" value="Cancel"></div>';
        echo $output;
        exit;
    }
    function InsertResellerSPrice()
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
        $price_flyer=Json_Format($args);
        
        $wpdb->query("update `wp_crm_reseller_sellprice` set status='0' where user_id=".$user_id);
        $wpdb->query("insert into `wp_crm_reseller_sellprice` (`pname`,`user_id`,`price_flyer`,`cpid`,`res_comp_id`,`status`)values('".$_POST['pname']."','".$user_id."','".$price_flyer."','".$_POST['cpid']."','".$_POST['res_comp_id']."','1')");
        $last_id=$wpdb->insert_id;        
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=rs_sprice");
        exit;
    }
}
?>