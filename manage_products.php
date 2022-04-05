<?php
    class ManageProductsinfo
    {
       function DisplayList()
       {
            global $wpdb;
            $query="SELECT * FROM `wp_crm_products`";
            $result = $wpdb->get_results($query, OBJECT);
            $toolbar='';
            $savebtn='<tr><td colspan="6" align="right">'
                    . '<a href="jsvascript:;" onclick="ImportProd()"><img src="'.plugins_url().'/salesforce_reports/tree/images/import_csv.png"></a>'
                    . '&nbsp;&nbsp;&nbsp;<a href="jsvascript:;" onclick="EnterProd()"><img src="'.plugins_url().'/salesforce_reports/tree/images/create-new.png"></a>'
                    . '&nbsp;&nbsp;&nbsp;<a href="jsvascript:;" onclick="ExportProd()"><img src="'.plugins_url().'/salesforce_reports/tree/images/export-icon.png"></a>'
                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="jsvascript:;" onclick="ApplyChange()"><img src="'.plugins_url().'/salesforce_reports/tree/images/save-icon.png"></a>'
                    . '</td></tr>';

            $output=$toolbar.'<form name="pfrm" id="pfrm" method="post"><table border="1" width="95%" class="exttbl" id="exttbl">';

            $output.=$savebtn;
            $output.='<tr>'
                    . '<td class="extitle"><input type="checkbox" id="checkallproducts" class="checkallproducts" name="checkallproducts" onclick="CheckAllSProd()"></td>'
                    . '<td class="extitle">CRM SKU</td>'
                    . '<td class="extitle">Non CRM SKU</td>'
                    . '<td class="extitle prodname">Product Name</td>'
                    . '<td class="extitle">Price</td>'
                    . '<td class="extitle">Status</td>'
                    . '</tr>';
            //$output.='<tr><td colspan="7" align="right">&nbsp;</td></tr>';
            $c=1;$costprice=0;$idarr=array();
            foreach($result as $rs):
                $amtun=unserialize($rs->pricing);
                $costprice+=$amtun->UnitPrice;
                $idarr[]=$rs->id;
                if($rs->status=="1"):$chkprod='checked="checked"';$status="Active";else:$status="In Active";$chkprod='';endif;
                $output.='<tr>';
                $output.='<td class="extcontent"><input type="checkbox" name="sell_produt" class="sell_produt" value="'.$rs->id.'" '.$chkprod.'></td>';
                $output.='<td class="extcontent">'.$rs->ProductCode.'</td>';
                $output.='<td class="extcontent">KB000'.$rs->id.'</td>';
                $output.='<td class="extcontent">'.$rs->Name.'</td>';
                $output.='<td class="extcontent">'.$amtun->UnitPrice.'</td>';    
                $output.='<td class="extcontent">'.$status.'</td>';
                $output.='</tr>';

                $c++;
            endforeach;
            $allid=implode(',',$idarr);
            $output.='<input type="hidden" name="operate" id="operate"><tr><td colspan="6" align="right"><span class="totprice">Total price: $ </span><span class="margin_txt">'.$costprice.'</span></span></td></tr>';
            $output.='<textarea name="expdf" id="expdf" style="display:none;"></textarea>'.$savebtn;
            $output.='</table></form><br>'.$toolbar.'<br>';
            // $output.='<div><a href="javascript:;" onclick="ExportPdf()">Export Pdf</a> <input type="button" value="Submit">&nbsp;<input type="button" value="Cancel"></div>';
            return  $output;        
       }
       function IncludeScripts()
       {
           ?>
            <script type="text/javascript">
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
                function ApplyChange()
                {
                   jQuery('#operate').val(4); 
                   document.pfrm.action="";
                   document.pfrm.method="POST";
                   document.pfrm.submit();
                }
            </script>    
           <?php
       }
       function UpdateStatus()
       {
           print"<pre>";print_r($_POST);print"</pre>";exit;
           
       }
    }
    class ManageProductList extends ManageProductsinfo
    {
        function display()
        {
            $display=$this->IncludeScripts();
            if($_POST['operate']=="4"):
                $display.=$this->UpdateStatus();   
            else:
                $display.=$this->DisplayList();    
            endif;            
            return $display;
        }
    }
?>