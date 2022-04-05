<?php
class CostPriceList
{
    function getProductList()
    {
        global $wpdb;
        $query="SELECT * FROM `wp_crm_products` where status=1";
        $result = $wpdb->get_results($query, OBJECT);
        $toolbar='';
        $savebtn='<tr><td colspan="7" align="right"><input type="button" value="Submit" style="margin:5px" onclick="SavePriceList()"><input type="button" value="Cancel" style="margin:5px" onclick="CancelSettings()"></td></tr><input type="hidden" name="pricfly" id="pricfly" value="1">';
        
        $output=$toolbar.'<form name="cpfrm" id="cpfrm"><table border="1" width="100%" class="exttbl" id="exttbl">';
        
        $output.=$savebtn;
        
        $output.='<tr><td colspan="7" align="left" class="extitle">Enter Price list name: <input type="text" name="pname" id="pname" class="pname"></td></tr>';
        $output.='<tr><td colspan="7" align="left" class="extitle">Enter Group name: <input type="text" name="group_name" id="group_name" class="group_name"></td></tr>';
        $output.='<tr>'
                . '<td class="extitle">No</td>'
                . '<td class="extitle">CRM SKU</td>'
                . '<td class="extitle">Non CRM SKU</td>'
                . '<td class="extitle prodname">Product Name</td>'
                . '<td class="extitle">Cost Price</td>'                
                . '<td class="extitle">New Cost Price</td>'
                . '</tr>';
        
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
            $output.='<td class="extcontent">'.$amtun->UnitPrice.'</td>';    
            $output.='<td class="extcontent"><input type="text" name="newcp_'.$rs->id.'" value="'.$amtun->UnitPrice.'"></td>';  
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
               var pname=jQuery('#pname').val();
               var group_name=jQuery('#group_name').val();
                
                if(pname=="")
                {
                    alert("Please enter your price flyer title");
                    jQuery('#pname').focus();
                }
                else if(group_name=="")
                {
                    alert("Please enter your group name");
                    jQuery('#group_name').focus();
                }
                else
                {
                    var cplist=jQuery.post( "<?php echo get_bloginfo('url').'/salesforce_reports/?action=cp_list';?>", jQuery('form#cpfrm').serialize() );
                    jQuery("#dataloader").html('<img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif">');
                    
                    cplist.done(function( data ) {
                        var splt=data.split("___");
                        var idsnew=splt[1];
                        //var n = jQuery( "tr.mlist" ).length;
                       // var pit='<tr class="mlist"><td class="podd">'+n+'</td><td class="podd">'+pname+'</td><td class="podd">'+group_name+'</td><td class="podd">'+splt[0]+'</td><td class="podd">Pending</td><td class="podd"><a onclick="ExportCSV()" title="Export Prices" href="javascript:;" align="center"><img width="16" src="<?php echo plugins_url();?>/salesforce_reports/tree/images/export-icon.png"></a>&nbsp;<a onclick="ExportPdf()" title="Download current price flyer config" href="javascript:;" align="center"><img width="16" src="<?php echo plugins_url();?>/salesforce_reports/tree/images/pdf-export.png"></a>&nbsp;&nbsp;<a onclick="Inivtepeople('+idsnew+')" href="javascript:;"><img width="16" src="<?php echo plugins_url();?>/salesforce_reports/tree/images/people-icon.png"></a></td></tr>';
                        //jQuery('tr.mlist:nth-child('+n+'n)').after(pit);
                        jQuery.ajax({type: "POST",
                            data: "cp_invite=1&id="+idsnew,
                            success: function(data1)
                            {               
                                jQuery("#dataloader").html(data1);
                            }
                        });
                   });
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
            $wpdb->query("update `wp_crm_costprice` set status='0' where user_id=".$user_id);
            $wpdb->query("insert into `wp_crm_costprice` (`user_id`,`price_flyer`,`status`)values('".$user_id."','".$price_flyer."','1')");
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
        $wpdb->query("update `wp_crm_costprice` set status='0' where user_id=".$user_id);
        $wpdb->query("update `wp_crm_costprice` set status='1' where user_id=".$user_id." AND id=".$_POST['price_list']);
        exit;
    }
    function InviteUsers()
    {
        global $wpdb;
        $iud=$_POST['id'];
        $user_id=get_current_user_id();
        
        $query1="SELECT * FROM `wp_users` as u INNER JOIN `wp_crm_cpuser` as cpu ON u.ID=cpu.user_id AND cpid=".$_POST['id'];
        $nonres=$wpdb->get_results($query1, OBJECT);   
        
        $querynew="SELECT * FROM `wp_crm_costprice` where id=".$_POST['id'];
        $qsresult=$wpdb->get_results($querynew, OBJECT); 
        $cost_title=$qsresult[0]->pname;
        $group_name=$qsresult[0]->group_name;
        $price_flyer=$qsresult[0]->price_flyer;
        $pflyer=json_decode($price_flyer);
        $dtable='<table width="70%" border="1" id="exttbl" class="exttbl">'
                . '<tbody>'
                . '<tr><td class="extitle">No</td><td class="extitle">CRM SKU</td><td class="extitle">Non CRM SKU</td><td class="extitle prodname">Product Name</td><td class="extitle">Cost Price</td><td class="extitle">New Cost Price</td></tr>';
        $n=1;
        foreach($pflyer->product as $qs):
            $dtable.='<tr><td class="extcontent">'.$n.'</td><td class="extcontent">'.$qs->crm_sku.'</td><td class="extcontent">KB000'.$qs->id.'</td><td class="extcontent">'.$qs->pname.'</td><td class="extcontent">'.$qs->cprice.'</td><td class="extcontent">'.$qs->costnew_price.'</td></tr>';
            $n++;
        endforeach;
        $dtable.='</tbody></table>';
        
        echo '<form name="efrm"><table width="100%" border="0">'
        . '<tr><td colspan="3" style="margin-top:10px;"><b>Price list Name: </b>'.$cost_title.'<br>'
                . '<a href="javascript:;" onclick="SlideClkView()" class="view_details">Click  here to view the Price list</a><br>'
                . '<div class="hidemychild curcostprice">'.$dtable.'</div>'
                . '</td></tr>'
                . '<tr>';
        echo '<td colspan="3"><b>Group Name: </b> '.$group_name.'</td>';
        echo '</tr><tr><td><b>Users</b><div class="Pickusers"><ul class="pickusr">';
        GetResellerCompanyByTree('',$_POST['id']);
        echo '</ul></div></td>';
        echo '<td style="width:100px" valign="middle" align="center"><a href="javascript:;" onclick="MoveRightIT('.$iud.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/right.png"></a><br><br>'
                . '<a href="javascript:;" onclick="MoveLeftIT('.$iud.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/left.png"></a></td>';
        echo '<td><b>Selected Users</b><div class="Pickusers"><ul class="slusr">';
        foreach($nonres as $rs):
            $query="SELECT u.ID,u.display_name,u.ref_id FROM wp_users as u";
            $where=" Where ref_id=".$rs->ID; 
            $viewall=' <a href="javascript:;" onclick="ViewIT('.$rs->ID.')" class="view_details">View Details</a>';
            echo '<li class="usr_'.$rs->ID.'"><input type="checkbox" name="pickusers" class="pkusr" value="'.$rs->ID.'">&nbsp;'.$rs->display_name.$viewall.'<br><div class="hidemychild mychild_'.$rs->ID.'">';
            GetMyChildsoFUsers($query,$where);
            echo '</div></li>';            
        endforeach;
        echo '</ul></div></td>';
        echo '</tr></table></form>';
        exit;
    }
    function Json_Format($args='')
    {
        $jsonp=$args;
	header('Content-type: application/json');
	return json_encode($jsonp);
	exit;
    }
    function DisplayCostprice()
    {
        $upsprice=GetRolesPermission('upload_cost_price'); 
         if($upsprice!="1"):
             return 'You dont have a permsssion to access this page';
             exit;
         endif;
        if($_REQUEST['expdf']!=""):
            $obj=new CreateCPPDFPrice();
            $display=$obj->PriceGeneratepdf($_POST['expdf']); 
        elseif($_REQUEST['expcsv']=="1"): 
            $this->ExportCProdCSV();
        elseif($_POST['updatepriceflyer']=="1"):
            $this->UpdatePriceflyer();
        elseif($_REQUEST['view']=="sample"):
           $display=$this->ProductSample();     
        else:
            if($_POST['importprice']=="1"): $this->ImportPriceFly(); endif;
            $display=$this->getProductList();
        endif;
        return $display;
    }
}
class CreateCPPDFPrice
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
