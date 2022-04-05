<?php
    class ManageResellerCPRequest
    {
        function getAllCPDscRequest()
        {
            global $wpdb;
            $user_id = get_current_user_id();
            $currency=getCurrency();
            $query="SELECT * from `wp_crm_reseller_discountrequest` where (comp_rep_id=".$user_id." OR forward_to=".$user_id.") Order by id DESC";        
            $result = $wpdb->get_results($query, OBJECT);
            $output='<form name="pricefrm" id="pricefrm">';
            $output.='<table width="100%" cellspacing="6" cellpadding="5" class="list_price" border="1" width="100%">
                                            <tbody>';
            $output.=''
                    . '<td class="cptit">No</td>'
                    . '<td class="cptit">Products</td>'
                    . '<td class="cptit">Quote Total Price</td>'                
                    . '<td class="cptit">Discount</td>'
                    . '<td class="cptit">Requested by</td>'
                    . '<td class="cptit">Create date</td>'
                    . '<td class="cptit">Status</td>'
                    . '<td class="cptit" width="80">Action</td>'
                    . '</tr>';
            $n=1;        
            foreach($result as $rs): 
                //print"<pre>";print_r($rs);print"</pre>";            
                $quote=json_decode($rs->quote); 
                $product=$quote->data->product;
                $esculate=$rs->esculate;
                $cp_extra=json_decode($rs->cp_extra); //print"<pre>";print_r($cp_extra);print"</pre>";
                
                $approve_icon='<a href="javascript:;" onclick="OpenQuote('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/quote.png" width="16"></a>';
               
                if($rs->status=="3"):$status="Waiting for cost price discount approval";
                elseif($rs->status=="6"):$status="Waiting for cost price discount approval authority Response";
                elseif($rs->status=="4"):$status="Cost price approved";$approve_icon='';
                elseif($rs->status=="7"):$status="Rejected";$approve_icon='';
                else:$approve_icon='';endif;
                //echo $rs->status;

                $prod='<table border="0">';
                foreach($product as $ps):
                    $prod.='<tr><td><b>Name</b> : '.$ps->Name.'</td></tr>'; 
                    $prod.='<tr><td><b>Price</b> : '.$currency.$ps->price.' per unit</td></tr>'; 
                    $prod.='<tr><td><b>Total qty</b> : '.$ps->qty.'</td></tr>';
                    $prod.='<tr><td><b>Total price</b> : '.$currency.$ps->product_total.'</td></tr>';   

                    $prod.='<tr><td><hr></td></tr>'; 
                    $ntot+=$ps->product_total;
                endforeach;
                $prod.='</table>';
                $c=1;
                
                $disc='';
                if($rs->usr_grp=="2"):
                    $user_info = get_userdata($rs->pm_id);
                    $requested_by=$user_info->display_name;
                    $quote_totalpricels=$cp_extra->totalcp;
                    
                    if($cp_extra->totalcp_approved):$disc.= '<b>Discount Approved</b>: '.$currency.number_format($cp_extra->totalcp_approved).'<br><br>';endif;
                    if($cp_extra->totalcp_requested): $disc.='<b>Discount Requested</b>: '.$currency.number_format($cp_extra->totalcp_requested);endif;
                endif;    
                if($rs->usr_grp=="1"):
                    $user_info = get_userdata($rs->user_id);
                    $requested_by=$user_info->display_name;
                    $sell_extra=json_decode($rs->sell_extra);
                    
                    $quote_totalpricels=$ntot;
                    if($rs->status=="1"):
                        $status="Waiting for discount approval";
                        $approve_icon='<a href="javascript:;" onclick="OpenQuoteForComp('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/quote.png" width="16"></a>';
                    endif;
                    
                    if($sell_extra->discount_approved):$disc.= '<b>Discount Approved</b>: '.$currency.number_format($sell_extra->discount_approved).'<br><br>';endif;
                    if($sell_extra->requested_discount): $disc.='<b>Discount Requested</b>: '.$currency.number_format($sell_extra->requested_discount);endif;
                endif; 
                
                
                
                $output.='<tr>';
                $output.='<td class="podd">'.$n.'</td>';
                $output.='<td class="podd">'.$prod.'</td>';
                $output.='<td class="podd">'.$currency.number_format($quote_totalpricels).'</td>';
                $output.='<td class="podd">'.$disc.'</td>';
                $output.='<td class="podd">'.$requested_by.'</td>';                
                $output.='<td class="podd">'.$rs->created.'</td>';
                $output.='<td class="podd">'.$status.'</td>';
                $output.='<td class="podd">'.$approve_icon.'</td>';
                $output.='</tr>';
                $c++;            
                $n++;                           
            endforeach;
            if(count($result)=="0"):
                $output.='<tr><td colspan="9" align="center" class="podd">No Discount Request found yet.</td></tr>';
            endif;
            $output.='</tbody></table>';
            $output.= '</form>';
            $this->IncludeCPDiscountScripts();
            return $output;
        } 
        function IncludeCPDiscountScripts()
        {
            $currency=getCurrency();
            ?>
            <script type="text/javascript">
                function OpenQuoteForComp(qid)
                {
                    var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                    dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Price Discount Approval</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                    jQuery("body").append(dt);
                    jQuery.ajax({type: "POST",
                        data: "compPricereq=1&id="+qid,
                        success: function(data)
                        {               
                            jQuery("#dataloader").html(data);
                            jQuery('.timeline').datepicker({
                                dateFormat : 'yy-m-dd'
                            });
                        }
                    });
                }
                function OpenQuote(qid)
                {
                    var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                    dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Cost Price Discount Approval</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                    jQuery("body").append(dt);
                    jQuery.ajax({type: "POST",
                        data: "cpdscprocess=1&id="+qid,
                        success: function(data)
                        {               
                            jQuery("#dataloader").html(data);
                            jQuery('.timeline').datepicker({
                                dateFormat : 'yy-m-dd'
                            });
                        }
                    });
                }
                function SendDCApprove()
                {
                    if(jQuery("input[name=discount_approval]:checked").val()==undefined)
                    {
                        alert("Please select your approval option");
                    }
                    else
                    {
                        document.QuoteForm.action="";
                        document.QuoteForm.method="POST";
                        document.QuoteForm.submit();
                    }                    
                }
                function ApprovalVal(e)
                {
                    jQuery('.hidemychild').hide();
                    if(e=="2")
                    {
                        jQuery('.approve_discountnew').hide();                        
                    }
                    else if(e=="1")
                    {
                        jQuery('.approved_cp').show();
                    }
                    else
                    {
                       // jQuery('.approve_discountnew').show();
                    }
                }
                function ApprovalNewVal(e)
                {
                    jQuery('.hidemychild').hide();
                    if(e=="2")
                    {
                        jQuery('.costprice_esculate').show();    
                        jQuery('#totcphr').show();    
                        jQuery('#totcpdata').show();      
                        jQuery('#esctotcp').html(jQuery('#oldtotcp').val());
                        jQuery('.openesctothr').show();
                    }
                    else if(e=="1")
                    {   
                        jQuery('.approve_productdiscount').show();
                        jQuery('.approve_discountnew').show();
                        jQuery('.modifiedsellprice').show();
                    }
                    else
                    {
                        if(e=="2"){jQuery('.cpdiscount').show();}  
                        jQuery('.approve_discountnew').show();
                    }
                }
                function CancelSettings()
                {
                    jQuery(".ui-widget-overlay").remove();
                    jQuery(".rolesettings").remove(); 
                } 
                function CancelQuote()
                {
                    CancelSettings()
                }
                function ApprovedCP()
                {
                    var allid=jQuery('#allid').val(); var aid=allid.split(",");
                    var total_newcp=0;
                    for(var p = 0; p < aid.length; p++) {   
                        var splt=aid[p].split("___");
                        var id=splt[1];var qty=splt[0];
                        
                        var cp=jQuery('#cost_price_old_'+id).val();
                        var newcp=jQuery('#cp_approved_'+id).val();

                        var totcprice = newcp*qty; 
                        var margin=((cp-newcp)/cp)*100;
                        jQuery('#totalapprovedcp_'+id).html('<td><b>Cost price variation:</b> :'+margin.toFixed(2)+'%<br><b>Total cost price approved</b>: <?php echo $currency;?>'+totcprice+'</td>').show();
                        total_newcp+=totcprice;
                    }
                    jQuery('#cpapproved').html('<br><b>Modified Total Cost price</b> :<?php echo $currency;?>'+total_newcp).show();
                }
                
                 function ChangeMargin()
                {
                    var allid=jQuery('#allid').val(); var aid=allid.split(",");
                    var cstatus=jQuery('#current_status').val();
                    var total_sell=0;
                    for(var p = 0; p < aid.length; p++) {   
                        var splt=aid[p].split("___");
                        var cp=jQuery('#cost_price_'+splt[1]).val();

                        var newsell=jQuery('#new_product_sprice_'+splt[1]).val();

                        var gain = newsell-cp; 
                        var gain_percent = (gain / newsell * 100); 
                        var totsprice=parseInt(newsell*splt[0])
                        jQuery('#latest_margin_'+splt[1]).html('<td colspan="2"><b>New Margin</b> :'+gain_percent.toFixed(2)+'%<br><b>New selling price</b>: <?php echo $currency;?>'+totsprice+'</td>');
                        jQuery('#latest_margin_'+splt[1]).show();
                        total_sell+=totsprice;
                    }   
                    jQuery('#modified_sellprice').html(total_sell);
                }
            </script>
            <?php
        }
        function getCPDiscountForm()
        {
            global $wpdb;
            $currency=getCurrency();
            $user_id = get_current_user_id();
            $user = new WP_User( $user_id );
            $user_roles=$user->roles[0];     
            $id=$_POST['id'];
            $dscapprove=GetRolesPermission('discount_approve');
            $opsquery="SELECT * FROM `wp_crm_reseller_discountrequest` where id=".$id." OR forward_to=".$id;
            $response = $wpdb->get_results($opsquery, OBJECT);
            $res=$response[0];
            if(count($response)>=1):
                $ops=json_decode($response[0]->quote); 
                $opportunity=$ops->data;
                $cp_extra=json_decode($res->cp_extra);
                if($dscapprove==""||$dscapprove=="0"):
                    $appico='<input type="radio" name="discount_approval" value="2" onclick="ApprovalVal(2)">&nbsp;Forward to approval authority&nbsp;&nbsp;&nbsp;&nbsp;';
                else:
                    $appico='<input type="radio" name="discount_approval" value="1" onclick="ApprovalVal(1)">&nbsp;Approve discount Request&nbsp;&nbsp;&nbsp;&nbsp;';
                endif;
                
                $display='<form style="width:auto;overflow:auto;position:relative;height:auto;" class="FormGrid" id="QuoteForm" name="QuoteForm"><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_jqGrid"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr>';

                $display.='<tr><td colspan="2"><b>Discount Process:</b></td></tr><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Total Cost price</td><td class="DataTD">&nbsp; 
                                    <input type="text" id="total_price" name="total_price" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$currency.number_format($cp_extra->totalcp).'" disabled="disabled"></td></tr>';

                $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Cost price Requested</td><td class="DataTD">&nbsp; 
                                    <input type="text" id="discount" name="discount" role="textbox" class="FormElement ui-widget-content ui-corner-all timeline" disabled="disabled" value="'.$currency.number_format($cp_extra->totalcp_requested).'"></td></tr>';
                
                
                $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD" colspan="2">'
                       . $appico.'<input type="radio" name="discount_approval" value="3" onclick="ApprovalVal(3)">&nbsp;Reject'
                       . '<br><span id="cpapproved"></span>  </td></tr>'; 
                
               
                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><b>Products :</b></td></tr>';
                $product=$opportunity->product;
                //$product=explode(",",$ops);
                $tprice="";$calcpr="";$i=1;$idarr=array();
                foreach($product as $ps): 
                   // $cp_extra=json_decode($res->cp_extra);
                    $idarr[]=$ps->qty."___".$ps->id;
                    $calcpr.=$ps->id."__".$ps->price."__".$ps->Name;
                    if(count($product)!=$i)$calcpr.=",";
                    $totprice=$ps->qty*$ps->cost_price;
                    $pinfo=$ps;                
                    $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><hr></td></tr><tr><td><table width="100%">';
                    $display.='
                                <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Product Name</td><td class="DataTD">&nbsp; 
                                                <input type="text" disabled="disabled"  name="product_name_'.$pinfo->id.'" value="'.$ps->Name.'" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr>
                                <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Price</td><td class="DataTD">&nbsp; 
                                                <input type="text" disabled="disabled" name="amount'.$pinfo->id.'" value="'.$currency.number_format($ps->cost_price).'" role="textbox" class="FormElement ui-widget-content ui-corner-all"> Per Unit</td></tr>
                                <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Quantity</td><td class="DataTD">&nbsp; 
                                                <input type="text" disabled="disabled" name="qty_'.$pinfo->id.'" value="'.$ps->qty.'" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr>
                                ';
                    $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2">&nbsp;</td></tr>';
                    $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Total $</td><td class="DataTD">&nbsp; 
                                                <input type="text" name="totprice_'.$pinfo->id.'" totprice_'.$pinfo->id.'" value="'.$currency.number_format($totprice).'" disabled="disabled" role="textbox" class="FormElement ui-widget-content ui-corner-all" ><br></td></tr>';
                    
                    $display.='</table></td><td>';
                    foreach($cp_extra->product as $cps): 
                        if($cps->id==$ps->id):
                            $cppercent=round((($cps->cost_price-$cps->requested_cost_price)/$cps->cost_price)*100, 2);
                            $display.='<table width="100%"><tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><br><div style="border:1px dotted #999;padding:15px;">Requested cost price :&nbsp; 
                                               '.$currency.number_format($cps->requested_cost_price).'<br>';
                        
                            $display.='Requested Total cost price : '.$currency.number_format($cps->requested_cost_price*$cps->qty).'<br>';
                            $display.='Changed : '.$cppercent.'%'
                                    . '<span id="approved_cp" class="hidemychild approved_cp"><br>'
                                    . '<b>Cost Price Approved</b>:'.$currency.'<input type="text" name="cp_approved_'.$ps->id.'" id="cp_approved_'.$ps->id.'" value="'.$cps->cost_price.'" class="FormElement ui-widget-content ui-corner-all" onkeyup="ApprovedCP()">'
                                    . '</span><br><span id="totalapprovedcp_'.$ps->id.'" class="hidemychild"><br></span><br></div></td></tr>'
                                    . '<input type="hidden" name="cost_price_old_'.$ps->id.'" id="cost_price_old_'.$ps->id.'" value="'.$cps->cost_price.'">'
                                    . '<input type="hidden" name="cost_price_varaiation_'.$ps->id.'" id="cost_price_varaiation_'.$ps->id.'" value="'.$cppercent.'">';                    
                            $display.='</table>';
                        endif;
                    endforeach;     
                    $display.='</td></tr>';
                    $tprice+=$totprice;
                    $i++;
                endforeach;            
                $allid=implode(',',$idarr);
                $display.='<input type="hidden" name="allid" id="allid" value="'.$allid.'">'
                        . '<input type="hidden" name="sendcpdsc" id="sendcpdsc" value="1"><input type="hidden" name="dscid" id="dscid" value="'.$id.'">
                    <input type="hidden" name="quote_id" id="quote_id" value="'.$res->quote_id.'">
                    <input type="hidden" name="opsid" id="opsid" value="'.$res->opsid.'">
                    <table cellspacing="0" cellpadding="0" border="0" id="TblGrid_jqGrid_2" class="EditTable"><tbody><tr id="Act_Buttons"><td class="EditButton">
                            <a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="SendDCApprove()">Submit 
                            <span class="ui-icon ui-icon-disk"></span></a><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="CancelQuote()">Cancel 
                            <span class="ui-icon ui-icon-close"></span></a></td></tr><tr class="binfo" style="display:none"><td colspan="2" class="bottominfo"></td></tr></tbody></table></form>';
            else:    
                $display='Your opportunity not yet approved. After approval only you can be able to Create Quote';
            endif;
            echo $display;
            exit;  
        }
        function ProcessCPDiscount()
        {
            global $wpdb;
            $id=$_POST['dscid'];
            $allid=$_POST['allid'];
            if($_POST['discount_approval']=="2"):
                $aid=explode(",",$allid);$prod=array();
                foreach($aid as $ad):
                    $pd=explode("___",$ad);
                    $prod[]=$_POST['cost_price_varaiation_'.$pd[1]];
                endforeach;
                $mxdsc=array_sum($prod) / count($prod);
                $forward=GetCPApprovalAuthority($mxdsc);
                $dscquery="update `wp_crm_reseller_discountrequest` set `forward_to`='".$forward."',`status`='6' where id=".$id;
                $wpdb->query($dscquery);                
            endif;    
            if($_POST['discount_approval']=="3"):
                $dscquery="update `wp_crm_reseller_discountrequest` set `status`='7' where id=".$id;
                $wpdb->query($dscquery);
            endif;
            if($_POST['discount_approval']=="1"):
                $id=$_POST['dscid'];
                $allid=explode(",",$_POST['allid']);
                $opsquery="SELECT * FROM `wp_crm_reseller_discountrequest` where id=".$id;
                $response = $wpdb->get_results($opsquery, OBJECT);
                $res=$response[0];
                $cp_extra=json_decode($res->cp_extra);
                $totcp=0;
                foreach($allid as $aid):
                    $pid=explode("___",$aid);
                    $pidnew=$pid[1];
                    foreach($cp_extra->product as $k=>$cps): 
                        if($pid[1]==$cps->id):
                            $cp_extra->product[$k]->approved_cost_price=$_POST['cp_approved_'.$pidnew];
                        endif;
                    endforeach;
                    $totcp+=$_POST['cp_approved_'.$pidnew]*$pid[0];
                endforeach;
                $cp_extra->totalcp_approved=$totcp;   
                
                $dscquery="update `wp_crm_reseller_discountrequest` set `status`='4',`cp_extra`='".Json_Format($cp_extra)."' where id=".$id;
                $wpdb->query($dscquery);
            endif;    
            header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=cp_discounts");  
            exit;
        }
        function ProcessDiscountSteps()
        { 
            //error_reporting(E_ALL);
            global $wpdb;
            $id=$_POST['dscid'];
            if($_POST['discount_approval']=="1"):
                $opsquery="SELECT * FROM `wp_crm_reseller_discountrequest` where id=".$id;
                $response = $wpdb->get_results($opsquery, OBJECT);
                $res=$response[0];

                $sell_extra=json_decode($res->sell_extra);
                $quote=json_decode($res->quote);
                $product=$quote->data->product;
                $args=array();$newtotsprice=0;
                foreach($product as $k=>$qs):
                    $args[$k]=$qs;
                    $sprice=$_POST['new_product_sprice_'.$qs->id];
                    $spricetot=($sprice*$qs->qty);
                    $args[$k]->new_sprice=$sprice;     
                    $args[$k]->new_sprice_total=$spricetot; 
                    $newtotsprice+=$spricetot;
                    $sell_extra->product[$qs->id]['new_sprice']=$sprice;
                    $sell_extra->product[$qs->id]['new_sprice_total']=$spricetot; 
                endforeach;
                $sell_extra->discount_approved=$newtotsprice;
                $quote->data->product=$args;
                $quote->new_total_sellprice=$newtotsprice;    

                $doc_url=getQuoteTemplate(Json_Format($quote));
                $quote->doc_url=$doc_url;
                $quotejson=Json_Format($quote);           


                $dscquery="update `wp_crm_reseller_discountrequest` set `quote`='".$quotejson."',`sell_extra`='".Json_Format($sell_extra)."',`dsc_type`=1,`esculate`='".$_POST['discount_approval']."',`status`=2 where id=".$id;
                $wpdb->query($dscquery);

                $resquery="update `wp_crm_reseller_quote` set `quote`='".$quotejson."',`status`=2 where id=".$_POST['quote_id'];
                $wpdb->query($resquery);           
            else:    

            endif;
            header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=discounts");  
            exit;

        }
        
    }
    class ManageCostpriceDiscounts extends ManageResellerCPRequest
    {
        function DisplayCPDiscounts()
        {
            if($_POST['compPricereq']=="1"):
                $dsc=new CompanyDiscountFrm();
                $display=$dsc->GetNewQuoteForm();
            elseif($_POST['cpdscprocess']=="1"):
                $display=$this->getCPDiscountForm();
            elseif($_POST['senddiscount']=="1"):
                $display=$this->ProcessDiscountSteps();
            elseif($_POST['sendcpdsc']=="1"):
                $display=$this->ProcessCPDiscount();            
            elseif($_GET['action']=="discounts"):
                $display=$this->getAllCPDscRequest();
            endif;
            return $display;
        }
    }
    
    /*-------------- Direct Sales Discount Form start------------------*/
     class CompanyDiscountFrm
    {
        function GetNewQuoteForm()
        {
            global $wpdb;
            $user_id = get_current_user_id();
            $currency=getCurrency();
            $user = new WP_User( $user_id );
            $user_roles=$user->roles[0];     
            $id=$_POST['id'];

            $opsquery="SELECT * FROM `wp_crm_reseller_discountrequest` where id=".$id;
            $response = $wpdb->get_results($opsquery, OBJECT);
            $res=$response[0];
            if(count($response)>=1):
                $ops=json_decode($response[0]->quote); 
                $opportunity=$ops->data;


                //print"<pre>";print_r($ops);print"</pre>";
                $display='<form style="width:auto;overflow:auto;position:relative;height:auto;" class="FormGrid" id="QuoteForm" name="QuoteForm"><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_jqGrid"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr>';

                $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Customer Name</td><td class="DataTD">&nbsp; 
                                    <input type="hidden" name="pid" id="pid" value="'.$id.'"><input type="text" id="customer_name" name="customer_name" readonly role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->customer_info->customer_name.'"></td></tr>';
                $display.='<tr><td colspan="2"></td></tr>';

                $display.='<tr rowpos="1" class="FormData hidemychild" id="tr_OpportunityName"><td class="CaptionTD">Contact Name</td><td class="DataTD">&nbsp; 
                                    <input type="hidden" name="pid" id="pid" value="'.$id.'"><input type="text" id="contact_name" name="contact_name" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->customer_info->contact_name.'"></td></tr>';

                $display.='<tr rowpos="1" class="FormData hidemychild" id="tr_OpportunityName"><td class="CaptionTD">Contact Email</td><td class="DataTD">&nbsp; 
                                    <input type="text" id="email" name="email" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->customer_info->contact_email.'"></td></tr>';

                $display.='<tr rowpos="1" class="FormData hidemychild" id="tr_OpportunityName"><td class="CaptionTD">Contact Address</td><td class="DataTD">&nbsp; 
                                    <textarea type="text" id="contact_address" name="contact_address" role="textbox" class="FormElement ui-widget-content ui-corner-all">'.base64_decode($opportunity->customer_info->contact_address).'</textarea></td></tr>';

                $mclass='';$cpapprovecls='hidemychild';
               

                /*------------Disocunt Process start----------------*/

                $display.='<input type="hidden" name="current_status" id="current_status" value="'.$res->status.'"><tr><td colspan="2"><hr></td></tr><tr><td colspan="2"><b>Discount Process:</b></td></tr><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Total Price</td><td class="DataTD">'.$currency.' 
                                  <input type="text" id="total_price" name="total_price" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.number_format($ops->totalprice).'" disabled="disabled"></td></tr>';

                $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Discount Requested %</td><td class="DataTD">'.$currency.' 
                                    <input type="text" id="discount" name="discount" role="textbox" class="FormElement ui-widget-content ui-corner-all timeline" disabled="disabled" value="'.number_format($opportunity->discount).'"></td></tr>';

                $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD" colspan="2">'
                       . '<input type="radio" name="discount_approval" value="1" onclick="ApprovalNewVal(1)">&nbsp;Approve discount Request&nbsp;&nbsp;&nbsp;&nbsp;'
                       . '<input type="radio" name="discount_approval" value="3" onclick="ApprovalNewVal(3)">&nbsp;Reject'
                       . '<div class="hidemychild cpdiscount"><br>Cost Price discount Request to company <input type="text" name="cp_discount" id="cp_discount" role="textbox" class="FormElement ui-widget-content ui-corner-all">% <br><hr></div>'
                       . '<div class="hidemychild approve_discountnew"><br><b>Modified total selling Price:</b> '.$currency.'<span id="modified_sellprice">'.number_format($ops->totalprice).'</span><br>'
                        . '<i>Please modified selling prices of the products to approve this.</i></div>'
                       . '</td></tr>';   

                /*------------Disocunt Process end----------------*/
                
                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><hr></td></tr>';

                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><b>Products selected:</b></td></tr>';
                $product=$opportunity->product;
                //$product=explode(",",$ops);
                $tprice="";$calcpr="";$i=1;$idarr=array();$totcp=0;
                foreach($product as $ps): 
                    $idarr[]=$ps->qty."___".$ps->id;
                    $calcpr.=$ps->id."__".$ps->price."__".$ps->Name;
                    if(count($product)!=$i)$calcpr.=",";
                    $totprice=$ps->qty*$ps->price;
                    $pinfo=$ps;      
                    $gain = $ps->price - $ps->cost_price; 
                    $gain_percent = ($gain / $ps->price * 100); 

                    if($res->status=="4"):
                        $cpdata='';
                        $cpprod=$cp_extra->product;
                        foreach($cpprod as $cps):
                            if($cps->id==$ps->id):
                                //$gainnew = $ps->price - $cps->approved_cost_price; 
                                //$gain_percentnew = ($gainnew / $ps->price * 100); 

                                $cpdata.='<br><br>Requested cost price: '.$currency.number_format($cps->requested_cost_price);
                                $cpdata.='<br><b>Approved cost price</b>: '.$currency.number_format($cps->approved_cost_price);
                                //$cpdata.='<br><b>New cost price Margin</b>: '.round($gain_percentnew, 2).'%';
                                $display.='<input type="hidden" name="approved_cost_price_'.$ps->id.'" id="approved_cost_price_'.$ps->id.'" value="'.$cps->approved_cost_price.'">  ';
                            endif;
                        endforeach;       

                    else:
                        $cpdata='<br>Default Margin</b>:'.round($gain_percent,2).'%  ';
                    endif;
                    
                    $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><div style="width:90%;margin:10px;"><hr></div></td></tr>';

                    $display.='<tr><td>';

                    $display.='<table width="100%" border="0">
                                <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Product Name</td><td class="DataTD">&nbsp;&nbsp;
                                                <input type="text" disabled="disabled"  name="product_name_'.$pinfo->id.'" value="'.$ps->Name.'" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr>
                                <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Price</td><td class="DataTD"> '.$currency.'
                                                <input type="text" disabled="disabled" name="amount'.$pinfo->id.'" value="'.number_format($ps->price).'" role="textbox" class="FormElement ui-widget-content ui-corner-all"> Per Unit<br>
                                    <input type="hidden" name="cost_price_'.$ps->id.'" id="cost_price_'.$ps->id.'" value="'.$ps->cost_price.'">   
                                </td></tr>
                                <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Quantity</td><td class="DataTD">&nbsp;&nbsp; 
                                                <input type="text" disabled="disabled" name="qty_'.$pinfo->id.'" value="'.$ps->qty.'" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr>
                                ';
                    $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2">&nbsp;</td></tr>';
                    $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Total</td><td class="DataTD">'.$currency.' 
                                                <input type="text" name="totprice_'.$pinfo->id.'" totprice_'.$pinfo->id.'" value="'.number_format($totprice).'" disabled="disabled" role="textbox" class="FormElement ui-widget-content ui-corner-all" ><br></td></tr>';
                    /*$display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Discount</td><td class="DataTD">&nbsp; 
                                                <input type="text" name="discount_'.$pinfo->id.'" value="0" role="textbox" class="FormElement ui-widget-content ui-corner-all">%</td></tr>';*/
                    $display.='</table></td><td valign="top" style="padding-left:25px;">';

                    $display.='<div class="'.$cpapprovecls.' approve_productdiscount" style="border:1px dotted #999;padding:10px;"><br>
                                        <table width="100%" border="0">
                                            <tr>
                                                <td colspan="2">Default Cost Price:'.$currency.number_format($ps->cost_price).' <i>Per Unit</i>
                                                '.$cpdata.'

                                                </td>
                                            </tr>

                                            <tr><td colspan="2" class="modifiedsellprice '.$mclass.'">
                                            Modified selling price :'.$currency.'<input class="FormElement ui-widget-content ui-corner-all" type="text" name="new_product_sprice_'.$pinfo->id.'" id="new_product_sprice_'.$pinfo->id.'" value="'.$ps->price.'" onKeyup="ChangeMargin()"><i>(per unit)</i>
                                            </td></tr>
                                            <tr id="latest_margin_'.$ps->id.'" class="hidemychild"></tr>
                                        </table>
                                    </div>
                                    ';
                    $display.='</td></tr>';

                    $tprice+=$totprice;
                    $totcp+=$ps->cost_price*$ps->qty;
                    $i++;
                endforeach;            
                $allid=implode(',',$idarr);

                $display.='<tr id="totcphr" class="hidemychild"><td colspan="2"><hr></td></tr>';
                $display.='<input type="hidden" name="allid" id="allid" value="'.$allid.'"><input type="hidden" name="senddiscount" id="senddiscount" value="1"><input type="hidden" name="dscid" id="dscid" value="'.$id.'">
                    <input type="hidden" name="quote_id" id="quote_id" value="'.$res->quote_id.'">
                    <input type="hidden" name="oldtotcp" id="oldtotcp" value="'.number_format($totcp).'">    
                    <input type="hidden" name="opsid" id="opsid" value="'.$res->opsid.'">
                    <table cellspacing="0" cellpadding="0" border="0" id="TblGrid_jqGrid_2" class="EditTable"><tbody><tr id="Act_Buttons"><td class="EditButton">
                            <a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="SendDCApprove()">Submit 
                            <span class="ui-icon ui-icon-disk"></span></a><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="CancelQuote()">Cancel 
                            <span class="ui-icon ui-icon-close"></span></a></td></tr><tr class="binfo" style="display:none"><td colspan="2" class="bottominfo"></td></tr></tbody></table></form>';
            else:    
                $display='Your opportunity not yet approved. After approval only you can be able to Create Quote';
            endif;
            echo $display;
            exit;        
        }
    }    
    /*-------------- Direct Sales Discount Form end------------------*/
    
?>