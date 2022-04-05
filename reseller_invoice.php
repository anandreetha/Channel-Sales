<?php
error_reporting(0);
class ResellerQuoteAction
{
    function SendQuoteCustomer($data,$opsid,$params,$status='1')
    {
        global $wpdb,$current_user;
        get_currentuserinfo();
        $currency=getCurrency();
        $user_id = get_current_user_id();
        $user = new WP_User( $user_id );
        $user_roles=$user->roles[0];
        $arr=json_decode($data);
        $cinfo=$arr->customer_info;
        $product=$arr->product;
        
        $html='<div ><table border="1" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" width="402">&nbsp;</td><td valign="top" width="270"><h1>INVOICE</h1></td></tr><tr><td valign="bottom" width="402">
                    &nbsp;
                </td><td valign="bottom" width="270"><p class="DateandNumber">INVOICE No: '.mt_rand(5, 15).'</p><p class="DateandNumber">&nbsp;</p><p class="DateandNumber">Date: <strong>'.date('d M,Y').'</strong></p></td></tr><tr><td valign="top" width="402">
                    '.$current_user->display_name.'
                </td><td width="270"><p>&nbsp;</p></td></tr></tbody></table></div><p>&nbsp;</p>';
        
        $html.='<div><table  border="0" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" width="30"><h2>To</h2></td><td valign="top" width="371">
                    '.$cinfo->customer_name.'<br>'.$cinfo->contact_name.'<br>'.base64_decode($cinfo->contact_address).'
                </td><td valign="top" width="271"><p>&nbsp;</p></td></tr></tbody></table></div><p>&nbsp;</p>';
        
        $html.='<div><table border="1" cellspacing="0" cellpadding="0"><tbody>';
        $html.='<tr><td width="84"><p class="ColumnHeadings">No</p></td><td width="210"><p class="ColumnHeadings">Product Name</p></td><td width="124"><p class="ColumnHeadings">Price Per Quantity</p></td><td valign="top" width="87"><p class="ColumnHeadings">Total Quantity</p></td><td width="107"><p class="ColumnHeadings">Total Price</p></td></tr><tr><td width="84"><p>&nbsp;</p></td><td width="210"><p><em>&nbsp;</em></p></td><td width="124"><p class="Amount" align="left">&nbsp;</p></td><td width="87"><p class="Amount" align="center">&nbsp;</p></td><td width="107"><p class="Amount" align="center">&nbsp;</p></td></tr>';
        //$html.='<tr><td width="84"><p class="ColumnHeadings">No</p></td><td width="210"><p class="ColumnHeadings">Product Name</p></td><td valign="top" width="123"><p class="ColumnHeadings">&nbsp;</p><p class="ColumnHeadings">DIscount %</p></td><td width="124"><p class="ColumnHeadings">Price Per Quantity</p></td><td valign="top" width="87"><p class="ColumnHeadings">ToTal Quantity</p></td><td width="107"><p class="ColumnHeadings">TOTAL</p></td></tr><tr><td width="84"><p>&nbsp;</p></td><td width="210"><p><em>&nbsp;</em></p></td><td valign="top" width="123"><p class="Amount" align="left">&nbsp;</p></td><td width="124"><p class="Amount" align="left">&nbsp;</p></td><td width="87"><p class="Amount" align="center">&nbsp;</p></td><td width="107"><p class="Amount" align="center">&nbsp;</p></td></tr>';
        $totprice='';
        foreach($product as $ps):
            $html.='<tr>
                    <td width="84">
                        <p align="center">1</p>
                        <p>&nbsp;</p>
                    </td>
                    <td width="210">
                        <p><em>'.$ps->Name.'</em></p>
                        <p><em>&nbsp;</em></p>
                    </td>

                    <td width="124">
                        <p class="Amount" align="left">'.$currency.$ps->price.'</p>
                        <p class="Amount" align="left">&nbsp;</p>
                    </td>
                    <td width="87">
                        <p class="Amount" align="center">'.$ps->qty.'</p>
                        <p class="Amount" align="left">&nbsp;</p>
                    </td>
                    <td width="107">
                        <p class="Amount" align="center">'.$currency.$ps->product_total.'</p>
                        <p class="Amount" align="left">&nbsp;</p>
                    </td>
                </tr>';
            $totprice+=$ps->product_total;    
        endforeach;   
        
        $html.='<tr><td><p>&nbsp;</p></td><td valign="top" width="123"><p class="Labels">&nbsp;</p></td><td width="124"><p class="Labels">&nbsp;</p></td><td valign="top" width="87"><p class="Amount">SUBTOTAL</p></td><td width="107"><p class="Amount" align="center"><strong><span style="text-decoration: underline;">'.$currency.'</span></strong>'.$totprice.'</p></td></tr>';
        
        $html.='<tr><td><p>&nbsp;</p></td><td valign="top" width="123"><p class="Labels">&nbsp;</p></td><td width="124"><p class="Labels">&nbsp;</p></td><td valign="top" width="87"><p class="Amount">VAT @ 17&frac12;%</p></td><td width="107"><p class="Amount">&nbsp;</p></td></tr>';
        
        $html.='<tr><td><p>&nbsp;</p></td><td valign="top" width="123"><p class="Labels">&nbsp;</p></td><td width="124"><p class="Labels">&nbsp;</p></td><td valign="top" width="87"><p class="Amount"><strong>TOTAL</strong></p></td><td width="107"><p class="Amount" align="left"><strong><span style="text-decoration: underline;">'.$currency.'</span></strong><strong><span style="text-decoration: underline;">'.$totprice.'.00</span></strong></p></td></tr></tbody></table></div>';
        
        if($arr->discount>0):
            $doc_url='';
        else:
            $pdf=new ResellerPDFInvoice();   
            $doc_url= $pdf->GenerateInvoice($html); 
        endif;
        
        
        $quote=array();
        $quote['data']=$arr;
        $quote['doc_url']=$doc_url;
        $quote['totalprice']=$totprice;
        
        $opsquery="insert into `wp_crm_reseller_quote` (`user_id`,`ops_id`,`quote`,`created`,`status`) values ('".$user_id."','".$opsid."','".Json_Format($quote)."','".date('Y-m-d H:i:s')."','".$status."')";
        $wpdb->query($opsquery);
        $quote_id=$wpdb->insert_id;
        if($arr->discount>0):
            if($_POST['usrgrp']=="1"):
                $pm_id='';
                $usr_grp='1';                
                $mxdsc=round(($arr->discount/$totprice)*100,2);
                $forward_to=GetCPApprovalAuthority($mxdsc);                
            else:
                $rsid=GetmyResellerCompanyId();        
                $query="SELECT ID FROM `wp_users` where `ref_id`='".$rsid."' limit 0,1";
                $result1=$wpdb->get_results($query, OBJECT);
                $pm_id=$result1[0]->ID; 
                $forward_to='';   
                $usr_grp='2';
            endif;
            
            
            $dis=array();
            $dis['requested_discount']=$arr->discount;
            
            
            
            $opsquery="insert into `wp_crm_reseller_discountrequest` (`user_id`,`quote_id`,`opsid`,`quote`,`pm_id`,`esculate`,`status`,`created`,`sell_extra`,`forward_to`,`usr_grp`) values ('".$user_id."','".$quote_id."','".$opsid."','".Json_Format($quote)."','".$pm_id."','0','1','".date('Y-m-d H:i:s')."','".Json_Format($dis)."','".$forward_to."','".$usr_grp."')";
            $wpdb->query($opsquery);
            
        else:
            if($params['download_invoice']=="1"):
           //echo '<iframe frameborder="0" width="0" height="0" src="'.$doc_url.'"></iframe>';
            endif;
            if($params['send_invoice']=="1"):
                $user_info = get_userdata($rep_id); //email part start
                $to = $_POST['email'];
                $subject="Received a new Quote";
                $message = $user_info->display_name." has been sent a new quote for you.";
                $message.='<a href="'.$doc_url.'">Click here to view your invoice</a>';
                $headers.= 'Cc:'.$user_info->user_email;
                $headers.= 'MIME-Version: 1.0' . "\r\n";
                $headers.= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                wp_mail( $to, $subject, $message,$headers );            
            endif;  
        endif;
        
        if($_REQUEST['__t']):$ta="&__t=".$_REQUEST['__t'];endif;
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=opportunity".$ta);  
        exit;
    }
    function ReSendQuoteCustomer($opsid)
    {
        global $wpdb,$current_user;
        get_currentuserinfo();
        $currency=getCurrency();
        $user_id = get_current_user_id();
        $opsquery="SELECT * FROM `wp_crm_reseller_quote` where ops_id=".$opsid." Order by id DESC";
        $response = $wpdb->get_results($opsquery, OBJECT);
        $data=$response[0]->quote;
        
        $arr=json_decode($data);
        $cinfo=$arr->customer_info;
        
        $user_info = get_userdata($user_id); //email part start
        $to = $_POST['email'];
        $subject="Received a new Quote";
        $message = $user_info->display_name." has been sent a new quote for you.";
        $message.='<a href="'.$arr->doc_url.'">Click here to view your invoice</a>';
        $headers.= 'Cc:'.$user_info->user_email;
        $headers.= 'MIME-Version: 1.0' . "\r\n";
        $headers.= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        wp_mail( $to, $subject, $message,$headers );            
        exit;
    }   
    
}
class ResellerPDFInvoice
{
    function GenerateInvoice($html)
    {
        require_once(plugin_dir_path( __FILE__ ).'/pdf/tcpdf_include.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Anand P R');
        $pdf->SetTitle('Kukkuburra Invoice');
        $pdf->SetSubject('Kukkuburra Invoice');
        $pdf->SetKeywords('Kukkuburra, PDF, Invoice, SF, Sales');

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

        //$pdf->lastPage();
        $pdffile='invoice'.time().'.pdf';
        $pdf->Output(plugin_dir_path( __FILE__ ).'/invoice_doc/'.$pdffile, 'F');
        return get_bloginfo('url').'/wp-content/plugins/salesforce_reports/invoice_doc/'.$pdffile;
    }
}
?>