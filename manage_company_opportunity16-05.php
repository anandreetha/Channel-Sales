<?php
class ManageCompanyOpportunity
{
    function FilterBy()
    {
        global $wpdb;
        $output.='<form name="fltrops" id="fltrops" method="POST">';
        
        $stagequery = "select * from  wp_crm_opportunitystage";
        $stageresult = $wpdb->get_results($stagequery);
        $stage.='<option value="">All</option>';
        foreach($stageresult as $rs):
            if($_POST['stage']==$rs->stage_name):$schk='selected="selected"';else:$schk='';endif;
            $stage.='<option value="'.$rs->stage_name.'" '.$schk.'>'.$rs->stage_name.'</option>';
        endforeach;
        
        $output.='<table><tr><td>'
                . 'Opportunity name:</td><td> <input type="text" name="opsname" id="opsname" value="'.$_POST['opsname'].'" placeholder="Enter your tite, Productname, etc......"></td><td>Filter by : </td><td>'
                . '<select name="stage" id="stageflt" onchange="FilterData()">'.$stage.'</select></td></tr>'
                . '<tr><td></td><td colspan="3" align="left"><input type="Submit" name="Search" value="Search">'
                . '<input type="button" value="Reset" onClick="ResetFlt()"></td></tr></table>';
        $output.='<input type="hidden" name="filterops" id="filterops" value="1"></form>';
        ?>
        <script type="text/javascript">
            function ResetFlt()
            {
                jQuery("#opsname").val('');
                jQuery("#stageflt").val('');
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
    function FilterWhereCondit()
    {
        if($_POST['filterops']=="1"):
            if($_POST['stage']||$_POST['opsname']):
                $where.=' AND (';
                if($_POST['stage']):
                    $stage='%"StageName":"'.$_POST['stage'].'"%';
                    $stagewhere.="(`opportunity` like '".$stage."')";
                endif;
                if($_POST['opsname']):
                    $opsname='%'.$_POST['opsname'].'%';
                    $opswhere.="(`opportunity` like '".$opsname."')";
                endif;
                if($_POST['stage']&&$_POST['opsname']):
                    $where.=$stagewhere." AND ".$opswhere;
                else:
                    $where.=$stagewhere.$opswhere;
                endif;
                $where.=')';
            endif;
        endif;
        return $where;
    }
    function ListOpportunity()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $currency=getCurrency();
        $chid=GetChildUserIdsinCommas();
        if($chid=="")$chid=$user_id;
        
        $where=$this->FilterWhereCondit();
        
        $query="SELECT * from `wp_crm_opportunity` where (user_id=".$user_id." OR user_id IN (".$chid.")) ".$where." Order by id DESC";        
        $result = $wpdb->get_results($query, OBJECT);
        
        $output.='<table width="100%" cellspacing="6" cellpadding="5" class="list_price" border="1" width="100%">
                                        <tbody>';
        $output.='<tr><td colspan="8" class="podd">'.$this->FilterBy().'</td><td align="right" colspan="2" class="podd">'
               // . '<a align="center" href="javascript:;" title="Export Prices" onclick="ExportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/export-icon.png"></a>&nbsp;'
               // . '<a align="center" href="javascript:;" title="Download current price flyer config" onclick="ExportPdf()"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-export.png"></a>&nbsp;&nbsp;'
                . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                . '<a href="javascript:;" onclick="CreateNewOps()"><img src="'.plugins_url().'/salesforce_reports/tree/images/create-new.png"></a>'
               // . '<a align="center" id="importcsv" href="javascript:;" title="Import CSV file" onclick="ImportCSV()"><img src="'.plugins_url().'/salesforce_reports/tree/images/import-icon.png"></a>&nbsp;&nbsp;'
                /*. '<img src="'.plugins_url().'/salesforce_reports/tree/images/save-icon.png">&nbsp;&nbsp;<a href="javascript:;" title="Reset Form" onclick="CleanIT()"><img src="'.plugins_url().'/salesforce_reports/tree/images/cancel-icon.png">'*/
                . '</td></tr>';
        $output.='<form name="pricefrm" id="pricefrm">';
        $output.=''
                . '<td class="cptit">No</td>'
                . '<td class="cptit">Opportunity Name</td>'
                . '<td class="cptit">Product Name</td>'
                . '<td class="cptit">Price</td>'
                . '<td class="cptit">Stagename</td>'
                . '<td class="cptit">Probability %</td>'
                . '<td class="cptit">Close date</td>'
                . '<td class="cptit">Status</td>'
                . '<td class="cptit">Created by</td>'
                . '<td class="cptit" width="80">Action</td>'
                . '</tr>';
        $n=1;
        $destage=GetDefaultQuoteStage();
        foreach($result as $rs): 
            if($rs->user_id==$user_id):
                $created="Me";
            elseif($rs->ops_grp=="2"):
                $company_id=GetResellerCompanyName($rs->user_id);
                $user_info = get_userdata($company_id);
                $created = $user_info->display_name;  
                $created=$created;
            else:
                $user_info = get_userdata($rs->user_id);
                $created = $user_info->display_name;                
            endif;
            $ops=json_decode($rs->opportunity); 
            if($rs->status=="2"):$status="Pending"; elseif($rs->status=="1"):$status="Active"; else:$status="Rejected";endif;
           // $imgfls='<a href="javascript:;" onclick="'.$clk.'"><img src="'.plugins_url().'/salesforce_reports/tree/images/'.$simg.'" width="16"></a>';
            $quoteicon='';
            if(in_array($ops->StageName,$destage)):
                if($rs->user_id==$user_id):
                    $quoteicon='&nbsp;&nbsp;<a href="javascript:;" onclick="CreateQuoteIT('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/quote.png" width="16"></a>';
                endif;
                $quoteicon.='&nbsp;&nbsp;<a href="javascript:;" onclick="QuoteHistory('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/details-icon.png" width="16"></a>&nbsp;&nbsp;';
            endif;
            $quoteicon.='&nbsp;&nbsp;<a href="javascript:;" onclick="QuoteActivity('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/discussion-icon.png" width="16"></a>&nbsp;&nbsp;';
            
            $prod='<table border="0">';
            foreach($ops->product as $ps): 
                if($rs->ops_grp=="2"):
                    $price=$ps->cost_price;
                    $product_total=$ps->qty*$ps->cost_price; 
                    $Amountnew+=$product_total;
                else:
                    $price=$ps->price;
                    $product_total=$ps->product_total;
                endif;
                $prod.='<tr><td><b>Name</b> : '.$ps->Name.'</td></tr>'; 
                $prod.='<tr><td><b>Price</b> : '.$currency.$price.' per unit</td></tr>'; 
                $prod.='<tr><td><b>Total qty</b> : '.$ps->qty.'</td></tr>';
                $prod.='<tr><td><b>Total price</b> : '.$currency.$product_total.'</td></tr>';   
                
                $prod.='<tr><td><hr></td></tr>'; 
            endforeach;
            if($rs->ops_grp=="2"):
                $Amount=$Amountnew;
            else:
                $Amount=$ops->Amount;
            endif;
            $prod.='</table>';
            $c=1;
            $output.='<tr>';
            $output.='<td class="podd">'.$n.'</td>';
            $output.='<td class="podd">'.$ops->Name.'</td>';
            $output.='<td class="podd">'.$prod.'</td>';
            $output.='<td class="podd">'.$currency.$Amount.'</td>';
            $output.='<td class="podd">'.$ops->StageName.'</td>';
            $output.='<td class="podd">'.$ops->Probability.'</td>';
            $output.='<td class="podd">'.$ops->CloseDate.'</td>';     
            $output.='<td class="podd">'.$status.'</td>';
            $output.='<td class="podd">'.$created.'</td>';
            $output.='<td class="podd"><a href="javascript:;" onclick="EditOpportunity('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/edit.png"></a>'
                    .$quoteicon.'</td>';
            $output.='</tr>';
            $c++;            
            $n++;                           
        endforeach;
        if(count($result)=="0"):
            $output.='<tr><td colspan="10" align="center" class="podd">No opportunity found yet.</td></tr>';
        endif;
        $output.='</tbody></table>';
        $output.= '</form>';
        $this->IncludeScripts();
        return $output;
    }
    function CreateOpsForm()
    {
        global $wpdb;
        $currency=getCurrency();
        $destage=GetDefaultQuoteStage();
        $user_id = get_current_user_id();
        $opshidd='';
        if($_POST['opsid']):
            $opsid=$_POST['opsid'];
            $crmquery="SELECT * FROM `wp_crm_opportunity` where id=".$opsid;
            $opsresult = $wpdb->get_results($crmquery);
            $opsres=$opsresult[0];
            $opportunity=json_decode($opsres->opportunity); 
            $opshidd='<input type="hidden" name="opsid" id="opsid" value="'.$opsid.'">';
        endif;
        
        $txtnonedit='';
        if($opsres->ops_grp=="2"):
            $schk='selected="selected"';
            $txtnonedit='disabled="disabled"';
            $stage.='<option value="'.$opportunity->StageName.'__'.$opportunity->Probability.'" '.$schk.'>'.$opportunity->StageName.'</option>';
            
            if(count($opportunity->product)>0):
                $prod_details='<div style="border: 2px dotted #999;padding:8px;" id="productqtybox"><table border="0" width="100%">'; 
                foreach($opportunity->product as $prod):
                    $product_total=$prod->cost_price*$prod->qty;
                    $Amount1+=$product_total;
                    $prod_details.='<tr><td style="border-bottom:2px dotted #999;"><br><b>Product:</b>&nbsp;'.$prod->Name.'<br><b>Price per unit:</b> $'.$prod->cost_price.'<br><b>Quantity :</b>&nbsp;<input '.$txtnonedit.' type="text" name="pqty_'.$prod->id.'" id="pqty_'.$prod->id.'" class="ui-widget-content ui-corner-all" size="6" value="'.$prod->qty.'" onkeyup="PriceCalcIT('.$prod->qty.','.$prod->cost_price.','.$prod->id.')"><br><span id="prod_price_'.$prod->id.'"><b>Total price : </b>'.$currency.$product_total.'</span><br></td></tr>';
                endforeach;           
                $prod_details.='</table></div>';
            else:
                $prod_details='';
            endif;
            $svrt='selected="selected"';
            $vertical.='<option role="option" value="'.$opportunity->Vertical.'" '.$svrt.'>'.$opportunity->Vertical.'</option>';
            
            $Amount=$Amount1;
            
        else:
            $stagequery = "select * from  wp_crm_opportunitystage";
            $stageresult = $wpdb->get_results($stagequery);
            foreach($stageresult as $rs):
                if($opportunity->StageName==$rs->stage_name):$schk='selected="selected"';else:$schk='';endif;
                $stage.='<option value="'.$rs->stage_name.'__'.$rs->probability.'" '.$schk.'>'.$rs->stage_name.'</option>';
            endforeach;
            
            $selllist=GetInternalSellList();
            $cpid=$selllist->id;
            $price_flyer=json_decode($selllist->price_flyer);
            if(count($price_flyer->product)=="0"): echo 'Your company selling price not yet updated. Once uploaded selling price only you can be able to create opportunity';exit; endif;

            $productname.='<select role="select" multiple="multiple" aria-multiselectable="true" id="product" name="product" size="3" onchange="Selectproduct()" class="FormElement ui-widget-content ui-corner-all">';
            foreach($price_flyer->product as $ps):            
                $sltd='';$tprod=array();
                foreach($opportunity->product as $prod):
                    if($prod->id==$ps->id):$sltd='selected="selected"'; endif;
                    $tprod[]=$prod->id.'__'.$prod->price.'__'.$prod->Name."__".$prod->cost_price;
                endforeach;    
                $product=$ps->id.'__'.$ps->sprice.'__'.$ps->pname.'__'.$ps->cprice;
                $productname.='<option value="'.$product.'" '.$sltd.'>'.$ps->pname.'</option>';
            endforeach;
            $productname.='</select>';
            if(count($opportunity->product)>0):
                $prod_details='<div style="border: 2px dotted #999;padding:8px;" id="productqtybox"><table border="0" width="100%">'; 
                foreach($opportunity->product as $prod):
                    $prod_details.='<tr><td style="border-bottom:2px dotted #999;"><br><b>Product:</b>&nbsp;'.$prod->Name.'<br><b>Price per unit:</b> $'.$prod->price.'<br><b>Quantity :</b>&nbsp;<input type="text" name="pqty_'.$prod->id.'" id="pqty_'.$prod->id.'" class="ui-widget-content ui-corner-all" size="6" value="'.$prod->qty.'" onkeyup="PriceCalcIT('.$prod->qty.','.$prod->price.','.$prod->id.')"><br><span id="prod_price_'.$prod->id.'"><b>Total price : </b>'.$currency.$prod->product_total.'</span><br></td></tr>';
                endforeach;           
                $prod_details.='</table></div>';
            else:
                $prod_details='';
            endif;
            $totproducts=implode(',',$tprod);
            
            $vertdata=array("Financial","Healthcare","Legal","Manufacturing","AEC","Real Estate","Others");
            foreach($vertdata as $vs):
                if($opportunity->Vertical==$vs):$svrt='selected="selected"';else:$svrt='';endif;
                $vertical.='<option role="option" value="'.$vs.'" '.$svrt.'>'.$vs.'</option>';
            endforeach;
            $Amount=$opportunity->Amount;
        endif;
        
        
        if(count($opportunity->extra->StageName)>0): 
            $showbudgetinfo='<tr rowpos="7" class="FormData" id="tr_customerinfo"><td colspan="2" align="right"><a href="javascript:;" onclick="ViewBudgetInfo()"><b>Click here to view Budget info +</b></a></td></tr>';
            $hidedt='hidedetails showbudgetinfo'; 
            $opstage=$opportunity->extra->StageName;
            $budget=$opstage->budget;
            $decision=$opstage->decision;
            $needs=$opstage->needs;
            if($budget=="1"):
                $bd1='checked="checked"';
                $mx='style="display:block;"';
                $minrate=$opstage->minrate;
                $maxrate=$opstage->maxrate;  
            else:    
                $bd0='checked="checked"';
            endif;
            if($decision=="0"):
                $dec0='checked="checked"';
                $whodc='style="display:block;"';
                $decision_maker=$opstage->decision_maker;
            else:
                $dec1='checked="checked"';
            endif;
            if($needs=="1"):
                 $nee1='checked="checked"';
            else:
                $nee0='checked="checked"';
            endif;
            $timeline=$opstage->timeline;            
        else: $hidedt='hidedetails';endif;
        
        
        if(in_array($opportunity->StageName,$destage)):
            $hdcust=' tr_customerinfo hidedetails showcustomplus';
            $qquote=' quickquote';
            $customsign='<tr rowpos="7" class="FormData" id="tr_customerinfo"><td colspan="2" align="right"><a href="javascript:;" onclick="ViewCustomer()"><b>Click here to view customer info +</b></a></td></tr>';
        else:$hdcust=' tr_customerinfo hidedetails';$qquote=' quickquote hidedetails';endif;
        
        $quickquote='<tr rowpos="7" class="FormData'.$qquote.'" id="tr_customerinfo"><td colspan="2" align="center">'
                    . '<input type="checkbox" name="send_invoice" id="sendinvoice" value="1"> Send Quick Quote&nbsp;&nbsp;'
                    . '<input type="checkbox" name="download_invoice" id="download_invoice" value="1"> Download Quote pdf</td></tr>';
        
        $cinfo=$opportunity->customer_info;
        if($cinfo->customer_name):$cdisb='disabled';$cust_name='<input type="hidden" name="cust_name" id="cust_name" value="'.$cinfo->customer_name.'">';endif;
                
        $output='<form name="opsfrm">'.$opshidd.$cust_name.'<table id="TblGrid_jqGrid" class="EditTable" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                        <tr id="FormError" style="display:none">
                            <td class="ui-state-error" colspan="2"></td>
                        </tr>
                        <tr style="display:none" class="tinfo">
                            <td class="topinfo" colspan="2"></td>
                        </tr>
                        <tr rowpos="1" class="FormData" id="tr_OpportunityName">
                            <td class="CaptionTD">Opportunity Name</td>
                            <td class="DataTD">&nbsp;<input type="text" id="OpportunityName" name="OpportunityName" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->Name.'" '.$txtnonedit.'></td>
                        </tr>
                        <tr rowpos="2" class="FormData" id="tr_product">
                            <td class="CaptionTD">Product Name</td>
                            <td class="DataTD">&nbsp;
                            '.$productname.'
                            '.$prod_details.'</td>
                        </tr>
                        <tr rowpos="3" class="FormData" id="tr_Amount">
                            <td class="CaptionTD">Price '.$currency.'</td>
                            <td class="DataTD">&nbsp;<input type="text" readonly="readonly" id="Amount" name="Amount" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$Amount.'" '.$txtnonedit.'></td>
                        </tr>
                        <tr rowpos="4" class="FormData" id="tr_Vertical">
                            <td class="CaptionTD">Vertical</td>
                            <td class="DataTD">&nbsp;<select role="select" id="Vertical" name="Vertical" size="1" class="FormElement ui-widget-content ui-corner-all" '.$txtnonedit.'>
                            '.$vertical.'
                            </select></td>
                        </tr>
                        <tr rowpos="5" class="FormData" id="tr_StageName">
                            <td class="CaptionTD">StageName</td>
                            <td class="DataTD">&nbsp;<select '.$txtnonedit.' role="select" id="StageName" name="StageName" size="1" class="FormElement ui-widget-content ui-corner-all" onchange="ProbAutoIT()" value="'.$opportunity->StageName.'">
                            '.$stage.'
                            </select></td>
                        </tr>
                        <tr rowpos="6" class="FormData" id="tr_Probability">
                            <td class="CaptionTD">Probability %</td>
                            <td class="DataTD">&nbsp;<input type="text" '.$txtnonedit.' id="Probability" name="Probability" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->Probability.'"></td>
                        </tr>
                '.$showbudgetinfo.'        
                <tr rowpos="3" class="FormData '.$hidedt.'" id="tr_extra">
                    <td class="CaptionTD" colspan="2">
                    <b>Please update the information below:</b>
                    <div style="border: 2px dotted #999;padding:8px;" id="budgetbox">
                    <table border="0" width="100%">
                    <tbody>
                    <tr>
                    <td> Budget </td>
                    <td>
                    <input type="radio" value="1" '.$txtnonedit.' name="budget" id="budget" onclick="Maxshow()" '.$bd1.'>Yes&nbsp;&nbsp; 
                        <input type="radio" value="0" '.$txtnonedit.' name="budget" id="budget" onclick="Maxhide()" '.$bd0.'>No&nbsp;&nbsp; 
                            <br>
                                <br>
                    <div id="minmax" '.$mx.'> Min rate 
                        <input type="text" '.$txtnonedit.' name="minrate" id="minrate" class="FormElement ui-widget-content ui-corner-all" value="'.$minrate.'">
                            <br>
                                <br>Max rate 
                                    <input type="text" '.$txtnonedit.' name="maxrate" id="maxrate" class="FormElement ui-widget-content ui-corner-all" value="'.$maxrate.'">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td> Is the prospect decision make? </td>
                                    <td>
                                        <input '.$txtnonedit.' type="radio" value="1" name="decision" id="decision" onclick="Decisionhide()" '.$dec1.'>Yes&nbsp;&nbsp; 
                                            <input '.$txtnonedit.' type="radio" value="0" name="decision" id="decision" onclick="Decisionshow()" '.$dec0.'>No&nbsp;&nbsp; 
                                                <br>
                                                    <br>
                                    <div id="decisionshows" '.$whodc.'> Who is the decision maker? 
                                        <input type="text" '.$txtnonedit.' name="decision_maker" id="decision_maker" class="FormElement ui-widget-content ui-corner-all" value="'.$decision_maker.'">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <hr>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td> Is needs clearly identify? </td>
                                        <td>
                                            <input '.$txtnonedit.' type="radio" value="1" name="needs" id="needs" '.$nee1.'>Yes&nbsp;&nbsp; 
                                                <input '.$txtnonedit.' type="radio" value="0" name="needs" id="needs" '.$nee0.'>No&nbsp;&nbsp; 
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <hr>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> What is the time line of complete this? </td>
                                                    <td>
                                                        <input '.$txtnonedit.' type="text" name="timeline" id="timeline" class="timeline FormElement ui-widget-content ui-corner-all" value="'.$timeline.'">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>    

                        <tr rowpos="7" class="FormData" id="tr_CloseDate">
                            <td class="CaptionTD">Close date</td>
                            <td class="DataTD">&nbsp;<input '.$txtnonedit.' type="text" id="CloseDate" name="CloseDate" role="textbox" class="timeline FormElement ui-widget-content ui-corner-all" value="'.$opportunity->CloseDate.'"></td>
                        </tr>
                        <tr rowpos="7" class="FormData '.$hdcust.'" id="tr_customerinfo"><td colspan="2"><hr></td></tr>
                        '.$customsign.'    
                        <tr rowpos="7" class="FormData '.$hdcust.'" id="tr_customerinfo"><td colspan="2"><b>Please enter your customer info:</b></td></tr>
                        <tr rowpos="1" class="FormData '.$hdcust.'" id="tr_customerinfo"><td class="CaptionTD">Customer Name</td><td class="DataTD">&nbsp; 
                                <input '.$txtnonedit.' type="text" id="customer_name" name="customer_name" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$cinfo->customer_name.'" '.$cdisb.'></td></tr>                        
                        <tr rowpos="7" class="FormData '.$hdcust.'" id="tr_customerinfo"><td colspan="2">&nbsp;</td></tr>
                        <tr rowpos="1" class="FormData '.$hdcust.'" id="tr_customerinfo"><td class="CaptionTD">Contact Name</td><td class="DataTD">&nbsp; 
                                            <input '.$txtnonedit.' type="text" id="contact_name" name="contact_name" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$cinfo->contact_name.'"></td></tr>                    
                        <tr rowpos="1" class="FormData '.$hdcust.'" id="tr_customerinfo"><td class="CaptionTD">Contact Email</td><td class="DataTD">&nbsp; 
                                            <input '.$txtnonedit.' type="text" id="contact_email" name="contact_email" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$cinfo->contact_email.'"></td></tr>            
                        <tr rowpos="1" class="FormData '.$hdcust.'" id="tr_customerinfo"><td class="CaptionTD">Contact Address</td><td class="DataTD">&nbsp; 
                                            <textarea '.$txtnonedit.' type="text" id="contact_address" name="contact_address" role="textbox" class="FormElement ui-widget-content ui-corner-all">'.base64_decode($cinfo->contact_address).'</textarea></td></tr>
                        
                        <tr rowpos="7" class="FormData '.$hdcust.'" id="tr_customerinfo"><td colspan="2">&nbsp;</td></tr>
                        
                        <tr class="FormData" style="display:none">
                            <td class="CaptionTD"></td>
                            <td colspan="1" class="DataTD">&nbsp;<input type="hidden" name="totproducts" id="totproducts" value="'.$totproducts.'">
                            <input type="hidden" name="cpid" id="cpid" value="'.$cpid.'">
                            </td>
                        </tr>';
                if($opsres->ops_grp!="2"):$output.=$quickquote;endif;
                $output.='
                    </tbody>
                </table>';
        if($opsres->ops_grp!="2"):
            $output.='<table border="0" cellspacing="0" cellpadding="0" class="EditTable" id="TblGrid_jqGrid_2">
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <hr class="ui-widget-content" style="margin:1px">
                            </td>
                        </tr>
                        <tr id="Act_Buttons">
                            <td class="EditButton" colspan="2"><a href="javascript:;" onclick="SubmitOps()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a href="javascript:;" onclick="CancelSettings()" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel<span class="ui-icon ui-icon-close"></span></a></td>
                        </tr>
                        <tr style="display:none" class="binfo">
                            <td class="bottominfo" colspan="2"></td>
                        </tr>
                    </tbody>
                </table><input type="hidden" name="opsdata" id="opsdata" value="1">';
        endif;
        $output.='</form>';
       echo $output;
       exit;
    }
    function IncludeScripts()
    {
        $currency=getCurrency();
        $destage=GetDefaultQuoteStage();
        ?>
        <script type="text/javascript">
            function QuoteActivity(opsid)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Opportunity Comments</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "ops_activity=1&cmpusr=1&opsid="+opsid,
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                    }
                });
            }
            function QuoteHistory(opsid)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Quote History</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "quote_history=1&opsid="+opsid,
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                    }
                });
            }
            function ViewBudgetInfo()
            {
                jQuery('.showbudgetinfo').slideToggle('slow');
            }
            function ViewCustomer()
            {
                jQuery('.showcustomplus').slideToggle('slow');
            }
            function CreateQuoteIT(id)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create new opportunity</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "quotefrm=1&id="+id,
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                        jQuery('.timeline').datepicker({
                            dateFormat : 'yy-m-dd'
                        });
                    }
                });

            }
            function SubmitOps()
            {
                
                var OpportunityName=jQuery("#OpportunityName").val();
                var startdate=jQuery("#timeline").val();
                var enddate=jQuery("#CloseDate").val();
                var cname=jQuery('#cname').val();
                var cemail=jQuery('#cemail').val();
                var caddress=jQuery('#caddress').val();   
                var showstage=<?php echo json_encode($destage);?>;
                var stagefull=jQuery("#StageName").val(); var stsplt=stagefull.split('__');
                var resp=jQuery.inArray( stsplt[0], showstage );                
                
                
                
                if(OpportunityName=="")
                {
                    alert("Please enter your opportunity name");
                    jQuery("#OpportunityName").focus();
                }
                else if(jQuery("#product option:selected").val()=="0")
                {
                    alert("Please select your product name");
                }
                else if(jQuery("#StageName option:selected").val()=="0")
                {
                    alert("Please select your stage name");
                }
                else if(jQuery("#CloseDate").val()=="")
                {
                    alert("Please select your CloseDate");
                }
                else
                {
                    if(resp=="-1")
                    {
                        document.opsfrm.action="";
                        document.opsfrm.method="POST";
                        document.opsfrm.submit();                        
                    }  
                    else
                    {
                        if(jQuery("input[name=budget]:checked").val()==undefined)
                        {
                            alert("Please select budget");
                        }
                        else if(jQuery("input[name=decision]:checked").val()==undefined)
                        {
                            alert("Please select decision");
                        }
                        else if(jQuery("input[name=needs]:checked").val()==undefined)
                        {
                            alert("Please select needs");
                        }     
                        else if(jQuery("#timeline").val()=="")
                        {
                            alert("Please select timeline");
                        }
                        else if(startdate > enddate)
                        {
                            alert("CloseDate should be greater than timeline date");
                            jQuery("#CloseDate").focus();
                        }
                        else
                        {
                            document.opsfrm.action="";
                            document.opsfrm.method="POST";
                            document.opsfrm.submit();    
                        }
                    }   
                }
                    
            }
            function ProbAutoIT()
            {
                var stage=jQuery("#StageName :selected").val();
                var sname=stage.split("__");
                if(sname[1]==""){var sd=0;}else{var sd=sname[1];}
                jQuery('#Probability').val(sd);
                AdditionalForm(sname[0]);
            }
            function AdditionalForm(param)
            { 
                var showstage=<?php echo json_encode($destage);?>;
                var resp=jQuery.inArray( param, showstage );                
                if(resp=="-1")
                {
                    jQuery("tr#tr_extra").hide();
                    jQuery('.quickquote').hide();
                }
                else
                {
                    jQuery('#tr_extra').show( "slow" ); 
                    jQuery('.tr_customerinfo').show();
                    jQuery('.quickquote').show();                    
                    
                    jQuery('.timeline').datepicker({
                        dateFormat : 'yy-m-dd'
                    });
                }   

            }
            function Maxshow()
            {
                jQuery("#minmax").show("slow");
            }
            function Maxhide()
            {
                jQuery("#minmax").hide("slow");
            }
            function Decisionhide()
            {
                jQuery("#decisionshows").hide("slow");
            }
            function Decisionshow()
            {
                jQuery("#decisionshows").show("slow");
            }
            function Selectproduct()
            {
                jQuery("#productqtybox").remove();
                var pid=jQuery("#product :selected").map(function() {return jQuery(this).val();}).get();
                jQuery('#totproducts').val(pid);
                var qtydt='<div style="border: 2px dotted #999;padding:8px;" id="productqtybox">';
                qtydt+='<table border="0" width="100%">';
                var ttpr=0;var cnt=0;var totqty="";
                for (p = 0; p < pid.length; p++) {var prod=pid[p]; cnt++;
                    var prodinfo=prod.split("__");ttpr+=Number(prodinfo[1]);
                    qtydt+='<tr><td style="border-bottom:2px dotted #999;"><br><b>Product:</b>&nbsp;'+prodinfo[2]+'<br><b>Price per unit:</b> $'+prodinfo[1]+'<br><b>Quantity :</b>&nbsp;<input type="text" name="pqty_'+prodinfo[0]+'" id="pqty_'+prodinfo[0]+'" class="ui-widget-content ui-corner-all" size="6" value="1" onkeyup="PriceCalcIT(this.value,'+prodinfo[1]+','+prodinfo[0]+')"><br><span id="prod_price_'+prodinfo[0]+'"></span><br></td></tr>';
                }
                qtydt+='</table></div>';
                //qtydt+='<td class="DataTD">&nbsp;<input type="text" id="totqty" name="totqty" value="" role="textbox" class="FormElement ui-widget-content ui-corner-all" style="display:none;"></td>';
                jQuery("#tr_product td.DataTD").append(qtydt);
                jQuery("#Amount").val(ttpr);
            }
            function PriceCalcIT(qty,price,id)
            {
                var qty=jQuery('#pqty_'+id).val();
                var newpr=price*qty;
                jQuery('#prod_price_'+id).html("<b>Total price : </b><?php echo $currency;?>"+newpr); var nqt=0;var totpr=0; 
                var pid=jQuery("#product :selected").map(function() {return jQuery(this).val();}).get();
                for (p = 0; p < pid.length; p++) {var prod=pid[p];var prodinfo=prod.split("__");
                    nqt=jQuery('#pqty_'+prodinfo[0]).val();
                    totpr+=Number(prodinfo[1]*nqt);
                }
                jQuery("#Amount").val(totpr);
            }
            function CreateNewOps()
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create new opportunity</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "getopsform=1",
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                        jQuery('.timeline').datepicker({
                            dateFormat : 'yy-m-dd'
                        });
                    }
                    });
            }
            function EditOpportunity(opsid)
            {
                var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create new opportunity</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "getopsform=1&mode=edit&opsid="+opsid,
                    success: function(data)
                    {               
                        jQuery("#dataloader").html(data);
                        jQuery('.timeline').datepicker({
                            dateFormat : 'yy-m-dd'
                        });
                    }
                    });
            }
            function CancelSettings()
            {
                jQuery(".ui-widget-overlay").remove();
                jQuery(".rolesettings").remove(); 
            }
            function SaveQuote()
                {
                    var checkCount = jQuery("input[name=\'quote[]\']:checked").length;
                    var email=jQuery("#contact_email").val();
                    var caddress=jQuery("#contact_address").val();
                    var expdate=jQuery('#expdate').val();
                    if(email=="")
                    {
                        alert("Please enter your customer email id");
                        jQuery("#email").focus();
                    }
                    else if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)))
                    {
                            alert("Please Enter Your Valid Email Id");
                            document.signup_form.signup_email.focus();
                            return false;	
                    }
                    else if(caddress=="")
                    {
                        alert("Please enter your customer Address");
                        jQuery("#caddress").focus();
                    }
                    else if(checkCount == 0)
                    {
                       alert("Atleast one Product should be checked to create quote.");
                    }
                    else if(expdate=="")
                    {
                        alert("Please select expiry date");
                        jQuery('#expdate').focus();
                    }
                    else{
                        document.QuoteForm.action=""; 
                        document.QuoteForm.method="POST";
                        document.QuoteForm.submit();
                    }        
                }
                function CancelQuote(){
                    jQuery(".quoteoverlay").remove();
                    jQuery(".createquotebox").remove();
                }
                function DiscountRequest()
                {
                    
                }
        </script>
        <?php
    }
    function CreateResellerOpportunity()
    {
        global $wpdb,$current_user;
        get_currentuserinfo();
        $user_id = get_current_user_id();
        $CloseDate=date('Y-m-d',strtotime($_POST['CloseDate']));
        $timeline=date('Y-m-d',strtotime($_POST['timeline']));
        $products=explode(",",$_POST['totproducts']);
        $stage=explode('__',$_POST['StageName']);
                
        $ops=array();
        $ops['Name']              =   $_POST['OpportunityName'];
        $ops['StageName']         =   $stage[0];
        $ops['CloseDate']         =   $CloseDate;
        $ops['Amount']            =   $_POST['Amount'];
        $ops['Probability']       =   $_POST['Probability']; 
        $ops['Vertical']       =   $_POST['Vertical']; 
         
        $c=0;
        $total_costprice=0;
        foreach($products as $ps):
            $prodval=explode("__",$ps);
            $sprice=$prodval[1];
            $qty=$_POST['pqty_'.$prodval[0]];
            $totprice=$qty*$sprice;
            
            $ops['product'][$c]["id"]   =   $prodval[0];
            $ops['product'][$c]["price"]=   $sprice;
            $ops['product'][$c]["Name"] =   $prodval[2];
            $ops['product'][$c]["qty"]  =   $qty;
            $ops['product'][$c]["product_total"]=  $totprice;
            $ops['product'][$c]["cost_price"]=  $prodval[3];            
            $c++;
        endforeach;
        
        if($_POST['StageName']!="Prospecting"):
            if($_POST['budget']!="")$ext['StageName']['budget']=$_POST['budget'];
            if($_POST['budget']=="1"):
                $ext['StageName']['minrate']=$_POST['minrate'];
                $ext['StageName']['maxrate']=$_POST['maxrate'];
            endif;
            if($_POST['decision']!="")$ext['StageName']['decision']=$_POST['decision'];
            if($_POST['decision_maker']!="")$ext['StageName']['decision_maker']=$_POST['decision_maker'];            
            if($_POST['needs']!="")$ext['StageName']['needs']=$_POST['needs'];
            if($_POST['timeline']!="")$ext['StageName']['timeline']=$timeline;
            $ops['extra']=$ext;      
        else:    
            $extra='';    
        endif;
        if($_POST['timeline']!="")$ext['quote']['expiry']=$timeline;
        if($_POST['customer_name']):
            $ops['customer_info']['customer_name']=$_POST['customer_name'];
            $ops['customer_info']['contact_name']=$_POST['contact_name'];
            $ops['customer_info']['contact_email']=$_POST['contact_email'];
            $ops['customer_info']['contact_address']=base64_encode($_POST['contact_address']);            
        endif;
        $opsquery="insert into `wp_crm_opportunity` (`user_id`,`opportunity`,`status`,`cpid`,`ops_grp`) values ('".$user_id."','".Json_Format($ops)."','1','".$_POST['cpid']."','1')";
        $wpdb->query($opsquery);
        $ops_id=$wpdb->insert_id;
        /*        
        $user_info = get_userdata($rep_id); //email part start
        $to = $user_info->user_email;
        $subject = "Created a new opportunity by : ".$current_user->display_name;
        $message = "I created a new opportunity called ".$_POST['OpportunityName'].".\r\n I am waiting for approval for your approval \r\n. Please approve his.";

        wp_mail( $to, $subject, $message );
 * */
        if(($_POST['send_invoice']=="1")||($_POST['download_invoice']=="1")):
            $params=array();
            $params['download_invoice']=$_POST['download_invoice'];
            $params['send_invoice']=$_POST['send_invoice'];
            
            $invoice=new ResellerQuoteAction();
            $invoice->SendQuoteCustomer(Json_Format($ops),$ops_id,$params);
        endif;
        
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=opportunity");        
        exit;
    }
    function EditResellerOpportunity()
    {
        global $wpdb,$current_user;
        get_currentuserinfo();
        $opsid=$_POST['opsid'];
        $user_id = get_current_user_id();
        $CloseDate=date('Y-m-d',strtotime($_POST['CloseDate']));
        $timeline=date('Y-m-d',strtotime($_POST['timeline']));
        $products=explode(",",$_POST['totproducts']);
        $stage=explode('__',$_POST['StageName']);
                
        $ops=array();
        $ops['Name']              =   $_POST['OpportunityName'];
        $ops['StageName']         =   $stage[0];
        $ops['CloseDate']         =   $CloseDate;
        $ops['Amount']            =   $_POST['Amount'];
        $ops['Probability']       =   $_POST['Probability']; 
        $ops['Vertical']       =   $_POST['Vertical']; 
         
        $c=0;
        foreach($products as $ps):
            $prodval=explode("__",$ps);
            $sprice=$prodval[1];
            $qty=$_POST['pqty_'.$prodval[0]];
            $totprice=$qty*$sprice;
            
            $ops['product'][$c]["id"]   =   $prodval[0];
            $ops['product'][$c]["price"]=   $sprice;
            $ops['product'][$c]["Name"] =   $prodval[2];
            $ops['product'][$c]["qty"]  =   $qty;
            $ops['product'][$c]["product_total"]=  $totprice;
            $ops['product'][$c]["cost_price"]=  $prodval[3];  
            $c++;
        endforeach;
        
        if($_POST['StageName']!="Prospecting"):
            if($_POST['budget']!="")$ext['StageName']['budget']=$_POST['budget'];
            if($_POST['budget']=="1"):
                $ext['StageName']['minrate']=$_POST['minrate'];
                $ext['StageName']['maxrate']=$_POST['maxrate'];
            endif;
            if($_POST['decision']!="")$ext['StageName']['decision']=$_POST['decision'];
            if($_POST['decision_maker']!="")$ext['StageName']['decision_maker']=$_POST['decision_maker'];            
            if($_POST['needs']!="")$ext['StageName']['needs']=$_POST['needs'];
            if($_POST['timeline']!="")$ext['StageName']['timeline']=$timeline;
            $ops['extra']=$ext;      
        else:    
            $extra='';    
        endif;
        
        if(($_POST['cust_name'])||($_POST['customer_name'])):
            if($_POST['cust_name']==""):
                $ops['customer_info']['customer_name']=$_POST['customer_name'];
            else:
                $ops['customer_info']['customer_name']=$_POST['cust_name'];
            endif;
            $ops['customer_info']['contact_name']=$_POST['contact_name'];
            $ops['customer_info']['contact_email']=$_POST['contact_email'];
            $ops['customer_info']['contact_address']=base64_encode($_POST['contact_address']);            
        endif;
        
        $opsquery="update `wp_crm_opportunity` set `opportunity`='".Json_Format($ops)."' where id=".$opsid;
        $wpdb->query($opsquery);
        
        if(($_POST['send_invoice']=="1")||($_POST['download_invoice']=="1")):
            $params=array();
            $params['download_invoice']=$_POST['download_invoice'];
            $params['send_invoice']=$_POST['send_invoice'];
            
            $invoice=new ResellerQuoteAction();
            $invoice->SendQuoteCustomer(Json_Format($ops),$opsid,$params);
        endif;
        
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=opportunity");   
        exit;
    }
    function GetQuoteForm()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $currency=getCurrency();
        $user = new WP_User( $user_id );
        $user_roles=$user->roles[0];     
        $id=$_POST['id'];
       
        $opsquery="SELECT id,opportunity FROM `wp_crm_opportunity` where id=".$id." AND status=1";
        $response = $wpdb->get_results($opsquery, OBJECT);
        
        if(count($response)>=1):
            $opportunity=json_decode($response[0]->opportunity); 
            $display='<form style="width:auto;overflow:auto;position:relative;height:auto;" class="FormGrid" id="QuoteForm" name="QuoteForm"><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_jqGrid"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr>';
            
            $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Customer Name</td><td class="DataTD">&nbsp; 
                                <input type="hidden" name="pid" id="pid" value="'.$id.'"><input type="text" id="customer_name" name="customer_name" readonly role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->customer_info->customer_name.'"></td></tr>';
            $display.='<tr><td colspan="2"></td></tr>';
            
            $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Contact Name</td><td class="DataTD">&nbsp; 
                                <input type="hidden" name="pid" id="pid" value="'.$id.'"><input type="text" id="contact_name" name="contact_name" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->customer_info->contact_name.'"></td></tr>';
            
            $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Contact Email</td><td class="DataTD">&nbsp; 
                                <input type="text" id="contact_email" name="contact_email" role="textbox" class="FormElement ui-widget-content ui-corner-all" value="'.$opportunity->customer_info->contact_email.'"></td></tr>';
            
            $display.='<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Contact Address</td><td class="DataTD">&nbsp; 
                                <textarea type="text" id="contact_address" name="contact_address" role="textbox" class="FormElement ui-widget-content ui-corner-all">'.base64_decode($opportunity->customer_info->contact_address).'</textarea></td></tr>';
            
            $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><hr></td></tr>';
            $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2"><b>Select your Products :</b></td></tr>';
            $product=$opportunity->product;
            //$product=explode(",",$ops);
            $tprice="";$calcpr="";$i=1;
            foreach($product as $ps): 
                $calcpr.=$ps->id."__".$ps->price."__".$ps->Name;
                if(count($product)!=$i)$calcpr.=",";
                $totprice=$ps->qty*$ps->price;
                $pinfo=$ps;                
                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2">&nbsp;</td></tr>';
                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Select this product for Quote</td><td class="DataTD">&nbsp; 
                                            <input type="checkbox" name="quote[]" value="'.$pinfo->id.'" checked="checked"></td></tr>
                            <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Product Name</td><td class="DataTD">&nbsp; 
                                            <input type="text" disabled="disabled"  name="product_name_'.$pinfo->id.'" value="'.$ps->Name.'" role="textbox" class="FormElement ui-widget-content ui-corner-all">
                                                <input type="hidden" name="cost_price_'.$pinfo->id.'" value="'.$ps->cost_price.'"></td></tr>
                            <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Price</td><td class="DataTD">'.$currency.' 
                                            <input type="text" disabled="disabled" name="amount'.$pinfo->id.'" value="'.number_format($ps->price).'" role="textbox" class="FormElement ui-widget-content ui-corner-all"> Per Unit</td></tr>
                            <tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Quantity</td><td class="DataTD">&nbsp; 
                                            <input type="text" name="qty_'.$pinfo->id.'" value="'.$ps->qty.'" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr>
                            ';
                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2">&nbsp;</td></tr>';
                $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Total </td><td class="DataTD">'.$currency.' 
                                            <input type="text" name="totprice_'.$pinfo->id.'" totprice_'.$pinfo->id.'" value="'.number_format($totprice).'" disabled="disabled" role="textbox" class="FormElement ui-widget-content ui-corner-all" ><br></td></tr>';
                /*$display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td class="CaptionTD">Discount</td><td class="DataTD">&nbsp; 
                                            <input type="text" name="discount_'.$pinfo->id.'" value="0" role="textbox" class="FormElement ui-widget-content ui-corner-all">%</td></tr>';*/
                $tprice+=$totprice;
                $i++;
            endforeach;            
            $display.='<tr><td colspan="2"><hr></td></tr><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Total Price</td><td class="DataTD">'.$currency.' 
                                <input type="text" id="total_price" name="total_price" role="textbox" class="FormElement ui-widget-content ui-corner-all hasDatepicker" value="'.number_format($tprice).'" disabled="disabled"></td></tr>';
            
            $display.='<tr rowpos="2" class="FormData" id="tr_Amount"><td colspan="2">&nbsp;</td></tr>';
            
            $display.='<input type="hidden" name="usrgrp" id="usrgrp" value="1"><input type="hidden" name="calcpr" id="calcpr" value="'.$calcpr.'"><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD" valign="top">Discounted price</td><td class="DataTD">'.$currency.'
                                 <input type="text" name="discount" id="discount" role="textbox" class="FormElement ui-widget-content ui-corner-all" ><br><i>(If you entered a value. This quote will go for discount approval. It wont send immediately)</i></td></tr>';
            $display.='<tr><td colspan="2"><hr></td></tr><tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Expiry Date</td><td class="DataTD">&nbsp; 
                                <input type="text" id="expdate" name="expdate" role="textbox" class="FormElement ui-widget-content ui-corner-all timeline"></td></tr>';
        
            
            $display.='<input type="hidden" name="sendquote" id="sendquote" value="1"><input type="hidden" name="opsid" id="opsid" value="'.$id.'">
                <table cellspacing="0" cellpadding="0" border="0" id="TblGrid_jqGrid_2" class="EditTable"><tbody><tr id="Act_Buttons"><td class="EditButton">';
                //$display.='<a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="PreviewQuote()" style="float:left;">Preview<span class="ui-icon ui-icon-disk"></span></a>';
                $display.='<a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="SaveQuote()">Submit 
                <span class="ui-icon ui-icon-disk"></span></a><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="CancelQuote()">Cancel 
                <span class="ui-icon ui-icon-close"></span></a></td></tr><tr class="binfo" style="display:none"><td colspan="2" class="bottominfo"></td></tr></tbody></table></form>';
        else:    
            $display='Your opportunity not yet approved. After approval only you can be able to Create Quote';
        endif;
        echo $display;
        exit;        
    }
    function QuoteHistory()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $opsid=$_POST['opsid'];
        $currency=getCurrency();
       
        $opsquery="SELECT * FROM `wp_crm_reseller_quote` where ops_id=".$opsid." Order by id DESC";
        $response = $wpdb->get_results($opsquery, OBJECT);
        
        $output.='<table width="100%" cellspacing="6" cellpadding="5" class="list_price" border="1" width="100%">
                                        <tbody>';
        
        $output.=''
                . '<td class="cptit">No</td>'
                . '<td class="cptit">Created Date</td>'
                . '<td class="cptit">Quote Price</td>'
                . '<td class="cptit">Status</td>'
                . '<td class="cptit">Document</td>'
                . '</tr>';  
        $n=1;
        foreach($response as $rs):
            $quote=json_decode($rs->quote);
            if($quote->new_total_sellprice): $totsellprice=$quote->new_total_sellprice; else: $totsellprice=$quote->totalprice; endif;
            
            $opsquery="SELECT sell_extra FROM `wp_crm_reseller_discountrequest` where quote_id=".$rs->id;
            $response = $wpdb->get_results($opsquery, OBJECT);
            $res=$response[0];
            $sell_extra=json_decode($res->sell_extra); 
            $disc='';
            if($sell_extra->discount_approved):$disc.= '<b>Discount Approved</b>: '.$currency.number_format($sell_extra->discount_approved).'<br><br>';endif;
            if($sell_extra->requested_discount): $disc.='<b>Discount Requested</b>: '.$currency.number_format($sell_extra->requested_discount).'<br><br>';endif;
            
            $qpdf='none';
            if($rs->status=="1"):
                $qstatus="Quote already sent";
                $qpdf='<a href="'.$quote->doc_url.'" target="_blank"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-icon.png"></a>';
            elseif($rs->status=="2"):
                $qstatus="Discount approved.";
                $qpdf='<a href="'.$quote->doc_url.'" target="_blank"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-icon.png"></a>';
            elseif($rs->status=="3"):
                $qstatus="Quote waiting for discount approval.";
            elseif($rs->status=="4"):
                $qstatus="Discount request rejected.";
            endif;
            
            $output.='<td class="podd">'.$n.'</td>';
            $output.= '<td class="podd">'.$rs->created.'</td>';
            $output.= '<td class="podd">'.$disc.'<b>Opportunity Price</b>:'.$currency.$totsellprice.'</td>';
            $output.= '<td class="podd">'.$qstatus.'</td>';
            $output.= '<td class="podd">'.$qpdf.'</td></tr>';
            $n++;
        endforeach;
        echo $output;
        exit;
    }
    function SendQuote()
    {
        global $wpdb;
        $opsid=$_POST['opsid'];
        $created=date('Y-m-d');
        $expdate=date('Y-m-d',strtotime($_POST['expdate']));
        $products=explode(",",$_POST['calcpr']);
        
                
        $ops=array();
        $c=0;
        foreach($products as $ps):
            $prodval=explode("__",$ps);
            if(in_array($prodval[0],$_POST['quote'])):                
                $sprice=$prodval[1];
                $qty=$_POST['qty_'.$prodval[0]];
                $totprice=$qty*$sprice;

                $ops['product'][$c]["id"]   =   $prodval[0];
                $ops['product'][$c]["price"]=   $sprice;
                $ops['product'][$c]["Name"] =   $prodval[2];
                $ops['product'][$c]["qty"]  =   $qty;
                $ops['product'][$c]["product_total"]=  $totprice;
                $ops['product'][$c]["cost_price"]=  $_POST['cost_price_'.$prodval[0]];
                $c++;
            endif;
        endforeach;
        if($_POST['timeline']!="")$ext['quote']['expiry']=$expdate;
        
        if($_POST['customer_name']):
            $ops['customer_info']['customer_name']=$_POST['customer_name'];
            $ops['customer_info']['contact_name']=$_POST['contact_name'];
            $ops['customer_info']['contact_email']=$_POST['contact_email'];
            $ops['customer_info']['contact_address']=base64_encode($_POST['contact_address']);            
        endif;
        
        if($_POST['discount']>0):
            $params=array();
            $status="3";
            $ops['discount']=$_POST['discount'];
        else:
            $params=array();
            $status="1";
            $params['send_invoice']=$_POST['send_invoice'];            
        endif;        
        $invoice=new ResellerQuoteAction();
        $invoice->SendQuoteCustomer(Json_Format($ops),$opsid,$params, $status);
        exit;
    }
}
class CompanyOpportunity extends ManageCompanyOpportunity
{
    function display()
    {
        if($_POST['getopsform']=="1"):
            $output=$this->CreateOpsForm();        
        elseif($_POST['ops_activity']=="1"):
            $ops=new OpportunityActivity();
            $output=$ops->displayOpsAct();
        elseif($_POST['quotefrm']=="1"):
            $output=$this->GetQuoteForm();
        elseif($_POST['sendquote']=="1"):
            $output=$this->SendQuote();
        elseif($_POST['quote_history']=="1"):
            $output=$this->QuoteHistory();
        elseif($_POST['opsdata']=="1"):
            if($_POST['opsid']):
                $output=$this->EditResellerOpportunity();
            else:
                $output=$this->CreateResellerOpportunity();
            endif;            
        else:
            $output=$this->ListOpportunity();
        endif;
        return $output;
    }    
}