<?php
class ManageCompanyOpportunity
{
    function FilterBy()
    {
        global $wpdb;
        $output.='<form name="fltrops" id="fltrops" method="POST">';
        
      /*  $stagequery = "select * from  wp_crm_opportunitystage";
        $stageresult = $wpdb->get_results($stagequery);
        $stage.='<option value="">All</option>';
        foreach($stageresult as $rs):
            if($_POST['stage']==$rs->stage_name):$schk='selected="selected"';else:$schk='';endif;
            $stage.='<option value="'.$rs->stage_name.'" '.$schk.'>'.$rs->stage_name.'</option>';
        endforeach;*/
        
        $output.=''
                . '<div class="row">
                            <div class="col-md-12 col-sm-12 text-center">
                              <div class="form-group">
                                <input type="text" name="opsname" value="'.$_POST['opsname'].'"   id="usr" placeholder="Search" class="serachinput">
                                <a href="javascript:;" onClick="ResetFlt()">Reset</a>    
                              </div>
                            </div>
                          </div>';
               
        
        $output.='<input type="hidden" name="filterops" id="filterops" value="1"></form>';
        ?>
        <script type="text/javascript">
            function ResetFlt()
            {
                jQuery("#usr").val('');
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
    function GetOpportunityDataLoad($lmt='')
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $chid=GetChildUserIdsinCommas();
        if($chid=="")$chid=$user_id;
        
        if($_POST['opslmt']):$lmt=$_POST['opslmt'];endif;
        if($lmt==""):
            $start=0;$end=5;
        else:
            $start=$lmt;$end=$lmt+5;
        endif;
        $limit=" Limit ".$start.",".$end;
        
        $where=$this->FilterWhereCondit();
        $query="SELECT * from `wp_crm_opportunity` where (user_id=".$user_id." OR user_id IN (".$chid.")) ".$where." Order by id DESC".$limit;        
        $result = $wpdb->get_results($query, OBJECT);   
        
        foreach($result as $rs): 
            $ops=json_decode($rs->opportunity); 
            $quoteicon='';
            if(in_array($ops->StageName,$destage)):
                if($rs->user_id==$user_id):
                    $quoteicon='<a href="javascript:;" onclick="CreateQuoteIT('.$rs->id.')"><img src="'.get_template_directory_uri().'/images/iconadd.png" name="Image21" border="0"></a>';                    
                endif;
                $quoteicon='<a href="javascript:;" onclick="CreateQuoteIT('.$rs->id.')"><img src="'.get_template_directory_uri().'/images/iconcreate.png" name="Image22" border="0"></a>';
                //$quoteicon.='<a href="javascript:;" onclick="QuoteHistory('.$rs->id.')"><img src="'.get_template_directory_uri().'/images/iconcreateview.png" name="Image23"  border="0"></a>';
            endif;
            //$quoteicon.='&nbsp;&nbsp;<a href="javascript:;" onclick="QuoteActivity('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/discussion-icon.png" width="16"></a>&nbsp;&nbsp;';
            
            foreach($ops->product as $ps): 
                if($rs->ops_grp=="2"):
                    $price=$ps->cost_price;
                    $product_total=$ps->qty*$ps->cost_price; 
                    $Amountnew+=$product_total;
                else:
                    $price=$ps->price;
                    $product_total=$ps->product_total;
                endif;               
            endforeach;
            if($rs->ops_grp=="2"):
                $Amount=$Amountnew;
            else:
                $Amount=$ops->Amount;
            endif;
            $c=1;
            $opskey=RFDataEncrypt($rs->id);
            $opsurl=get_bloginfo('url')."/salesforce_reports/?action=opportunity&__t=".$opskey;
            $cdate=date('M d, Y H:i:s',strtotime($rs->created_at));
            echo '<li id="opsli">
                        <h1><a href="'.$opsurl.'">'.$ops->Name.'</a></h1>
                        <p><a href="'.$opsurl.'">Price : '.$currency.$Amount.'</a></p>
                        <p>Created on '.$cdate.'</p>
                        <p><a href="javascript:;" onclick="EditOpportunity('.$rs->id.')"><img src="'.get_template_directory_uri().'/images/iconedit.png" name="Image20" border="0"></a> 
                            '.$quoteicon.'
                            </p>
                      </li>';
            $c++;            
            $n++;                           
        endforeach; 
        if((count($result)=="0")&&($lmt=="")):
            echo '<li id="opsli"><p>No opportunity found yet.</p></li>';
        endif;
    }
    function OpportunityMaps()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $chid=GetChildUserIdsinCommas();
        if($chid=="")$chid=$user_id;
        $where=$this->FilterWhereCondit();
        
        if($_POST['month']):$month=$_POST['month'];else:$month=date('m');endif;
        if($_POST['year']):$year=$_POST['year'];else:$year=date('Y');endif;
        $where1=" AND (YEAR(created_at)='".$year."' AND ( MONTH(created_at)='".$month."'))";
        $query="SELECT count(id) as cnt,ops_grp from `wp_crm_opportunity` where (user_id=".$user_id." OR user_id IN (".$chid.")) ".$where.$where1." Group by ops_grp Order by id DESC";        
        $result = $wpdb->get_results($query, OBJECT); 
        foreach($result as $rs):
            if($rs->ops_grp=="2"): $ch="Channel"; else: $ch="Direct"; endif;
            $dataft.="['".$ch."',".$rs->cnt."],";
        endforeach;
        ?>
        <div class="row">
                            <div class="col-md-12 col-sm-12 opportunityview ">
                                <h1><span class="glyphicon glyphicon-play"></span>Opportunity Details</h1>
                                <?php echo MonthYearDropDown(); ?>
                        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                        <script type="text/javascript">
                            google.charts.load("current", {packages:["corechart"]});
                            google.charts.setOnLoadCallback(drawChart);
                            function drawChart() {
                              var data = google.visualization.arrayToDataTable([
                                ['Type of opportunity', 'Total'],
                                <?php echo $dataft;?>
                              ]);

                              var options = {
                                title: 'Opportunity',
                                is3D: true,
                              };

                              var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
                              chart.draw(data, options);
                            }
                        </script>
                        <div id="piechart_3d" style="width: 900px; height: 500px;"></div>
                        
                        </div>
                        </div>
        <?php
    }
    function ListOpportunity()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $currency=getCurrency();
        ?>
        <div id="tapsection">
            <div class="container-fluid">
              <div class="row">
                <div class="leftsec" id="myContent">
                  <div class="col-md-3 col-sm-3 lefttapsection">
                    <div id="main">
                      <div class="nano">
                        <div class="overthrow nano-content description">
                          <?php
                            echo $this->FilterBy();
                          ?>
                           <div class="row">
                            <div class="col-md-12 col-sm-12 text-center">
                              <div class="form-group">
                                  <a href="<?php echo get_bloginfo('url');?>/salesforce_reports/?action=opportunity"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/homewind.png"></a>                                  
                                  &nbsp;&nbsp;&nbsp;&nbsp;
                                  <a href="javascript:;" onclick="CreateNewOps()"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/plushome.png"></a>
                              </div>
                            </div>
                          </div>
                            <input type="hidden" name="opslmt" id="opslmt">  
                            <input type="hidden" id="cmpusr" class="cmpusr" value="1">
                            <input type="hidden" id="bur" class="bur" value="<?php echo get_bloginfo('url');?>">
                            <input type="hidden" id="tpid" class="tpid" value="<?php echo $_REQUEST['__t'];?>">  
                          <div class="span12">
                            <!-- Adding "responsive" class triggers the magic -->
                            <div class="tabbable responsive">
                              <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">Opportunity</a></li>
                                <?php /*?><li><a href="#tab2" data-toggle="tab">Direct</a></li>
                                <li><a href="#tab3" data-toggle="tab">Sales</a></li><?php */?>
                              </ul>
                              <div class="tab-content">
                                
                                <div class="tab-pane fade in active" id="tab1" style="height:500px;">
                                  <ul class="oppourtunitytap area" id="ops">
                                    <?php 
                                        $n=1;
                                        $destage=GetDefaultQuoteStage();
                                        $this->GetOpportunityDataLoad();        
                                    ?>  
                                  </ul>
                                </div>
                                <?php /* ?>
                                <div class="tab-pane fade in" id="tab2">
                                  <ul class="oppourtunitytap">
                                    <li>
                                      <h1>Opportunity - Direct1</h1>
                                      <p>Price : $ 2000</p>
                                      <p>Created on April 28, 2016</p>
                                      <p><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image20','','images/iconedit_hover.png',1)"><img src="images/iconedit.png" name="Image20" border="0"></a> <a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image21','','images/iconadd_hover.png',1)"><img src="images/iconadd.png" name="Image21" border="0"></a> <a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image22','','images/iconcreate_hover.png',1)"><img src="images/iconcreate.png" name="Image22" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image23','','images/iconcreateview_hover.png',1)"><img src="images/iconcreateview.png" name="Image23"  border="0"></a></p>
                                    </li>
                                    <li class="active">
                                      <h1>Opportunity - 2</h1>
                                      <p>Price : $ 2000</p>
                                      <p>Created on April 28, 2016</p>
                                      <p><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image8','','images/iconedit_hover.png',1)"><img src="images/iconedit_hover.png" name="Image8" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image9','','images/iconadd_hover.png',1)"><img src="images/iconadd_hover.png" name="Image9" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image10','','images/iconcreate_hover.png',1)"><img src="images/iconcreate_hover.png" name="Image10"border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image11','','images/iconcreateview_hover.png',1)"><img src="images/iconcreateview_hover.png" name="Image11"  border="0"></a></p>
                                    </li>
                                    <li>
                                      <h1>Opportunity - 3</h1>
                                      <p>Price : $ 2000</p>
                                      <p>Created on April 28, 2016</p>
                                      <p><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image12','','images/iconedit_hover.png',1)"><img src="images/iconedit.png" name="Image12" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image13','','images/iconadd_hover.png',1)"><img src="images/iconadd.png" name="Image13"  border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image14','','images/iconcreate_hover.png',1)"><img src="images/iconcreate.png" name="Image14"  border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image15','','images/iconcreateview_hover.png',1)"><img src="images/iconcreateview.png" name="Image15" border="0"></a></p>
                                    </li>
                                  </ul>
                                </div> 
                                <div class="tab-pane fade in" id="tab3">
                                  <ul class="oppourtunitytap">
                                    <li>
                                      <h1>Opportunity -Sales1</h1>
                                      <p>Price : $ 2000</p>
                                      <p>Created on April 28, 2016</p>
                                      <p><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image20','','images/iconedit_hover.png',1)"><img src="images/iconedit.png" name="Image20" border="0"></a> <a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image21','','images/iconadd_hover.png',1)"><img src="images/iconadd.png" name="Image21" border="0"></a> <a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image22','','images/iconcreate_hover.png',1)"><img src="images/iconcreate.png" name="Image22" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image23','','images/iconcreateview_hover.png',1)"><img src="images/iconcreateview.png" name="Image23"  border="0"></a></p>
                                    </li>
                                    <li class="active">
                                      <h1>Opportunity - 2</h1>
                                      <p>Price : $ 2000</p>
                                      <p>Created on April 28, 2016</p>
                                      <p><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image8','','images/iconedit_hover.png',1)"><img src="images/iconedit_hover.png" name="Image8" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image9','','images/iconadd_hover.png',1)"><img src="images/iconadd_hover.png" name="Image9" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image10','','images/iconcreate_hover.png',1)"><img src="images/iconcreate_hover.png" name="Image10"border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image11','','images/iconcreateview_hover.png',1)"><img src="images/iconcreateview_hover.png" name="Image11"  border="0"></a></p>
                                    </li>
                                    <li>
                                      <h1>Opportunity - 3</h1>
                                      <p>Price : $ 2000</p>
                                      <p>Created on April 28, 2016</p>
                                      <p><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image12','','images/iconedit_hover.png',1)"><img src="images/iconedit.png" name="Image12" border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image13','','images/iconadd_hover.png',1)"><img src="images/iconadd.png" name="Image13"  border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image14','','images/iconcreate_hover.png',1)"><img src="images/iconcreate.png" name="Image14"  border="0"></a><a href="#" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image15','','images/iconcreateview_hover.png',1)"><img src="images/iconcreateview.png" name="Image15" border="0"></a></p>
                                    </li>
                                  </ul>
                                </div>
                                <?php */?>
                              </div>
                              <!-- /tab-content -->
                            </div>
                            <!-- /tabbable -->
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--     /********* Left ************/-->
                <?php
                    $td=$_REQUEST['__t'];
                    if($td): $mncls="col-md-6 col-sm-6"; else: $mncls="col-md-9 col-sm-9"; endif;
                ?>
                <div class="<?php echo $mncls;?> viewsec viewmids">
                    <div class="loadcntheight"><div id="loadfullcontent" class="area"></div></div>
                <?php 
                    if($td): ?>  
                        <div class="row OpenOps">
                          <div class="col-md-12 col-sm-12 opportunityview ">
                          <?php                        
                                  $dcp=RFDataDecrypt($td);
                                  $query="SELECT * from `wp_crm_opportunity` where id=".$dcp;        
                                  $result = $wpdb->get_results($query, OBJECT);
                                  $rs=$result[0];
                                  $opps=json_decode($rs->opportunity); 

                                  if($rs->status=="2"):$status="Pending"; elseif($rs->status=="1"):$status="Active"; else:$status="Rejected";endif;
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
                                  $ct=1;
                                  foreach($opps->product as $ps): 
                                      if($rs->ops_grp=="2"):
                                          $price=$ps->cost_price;
                                          $product_total=$ps->qty*$ps->cost_price; 
                                          $Amountnew+=$product_total;
                                      else:
                                          $price=$ps->price;
                                          $product_total=$ps->product_total;
                                      endif;
                                      $prod.='<p><label>Name</label><span class="opsdot"> : </span>'.$ps->Name.'</p>'; 
                                      $prod.='<p><label>Price</label><span class="opsdot"> : </span>'.$currency.$price.' per unit</p>';
                                      $prod.='<p><label>Total qty</label><span class="opsdot"> : </span>'.$ps->qty.' per unit</p>';
                                      $prod.='<p><label>Total price</label><span class="opsdot"> : </span>'.$currency.$product_total.' per unit</p>';
                                      if(count($opps->product)!=$ct): $prod.='<hr>'; endif;
                                      $ct++;
                                  endforeach;
                                  
                                  if($rs->ops_grp=="2"):
                                      $Amount=$Amountnew;
                                  else:
                                      $Amount=$opps->Amount;
                                  endif;

                          ?>
                            <h1><span class="glyphicon glyphicon-play"></span><?php echo $opps->Name;?></h1>
                            <!--            /*************************** Test Pipe Line ********************/-->
                            <div class="row mainopsload">

                              <div class="col-md-6 col-sm-6 rightview">
                                <p>
                                  <label>Opportunity Name</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo $opps->Name;?></p>
                                  <p>
                                  <label>Total price</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo $currency.number_format($Amount,2);?> </p> 
                                  <p>
                                  <label>Stage</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo $opps->StageName;?> </p> 
                                  <p>
                                  <label>Probability</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo $opps->Probability;?>% </p> 
                                  <p>
                                  <label>Close Date</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo date("d-M-Y",strtotime($opps->CloseDate));?> </p> 
                                  <p>
                                  <label>Status</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo $status;?> </p>                             
                                  <p>
                                  <label>Created at</label>
                                  <span class="opsdot"> : </span>
                                  <?php echo $created;?> </p> 
                                  <p></p>
                                  
                                <?php 
                                if($rs->ops_grp=="1"):   
                                    if($opps->customer_info->customer_name):
                                        echo '<p><label>Customer Name</label><span class="opsdot"> : </span>'.$opps->customer_info->customer_name.'</p>';
                                    endif;
                                    if($opps->customer_info->contact_name):
                                        echo '<p><label>Contact Name</label><span class="opsdot"> : </span>'.$opps->customer_info->contact_name.'</p>';
                                    endif;
                                    if($opps->customer_info->customer_name):
                                        echo '<p><label>Contact Email</label><span class="opsdot"> : </span>'.$opps->customer_info->contact_email.'</p>';
                                    endif;
                                    if($opps->customer_info->contact_address):
                                        //echo '<p><label>Contact Address</label><span class="opsdot"> : </span>'.$opps->customer_info->contact_address.'</p>';
                                    endif;
                                endif;
                                ?>    

                              </div>
                              <div class="col-md-6 col-sm-6 listview">
                                <?php echo $prod; ?>
                              </div>

                            </div>
                            <!--            /*************************** Test Pipe Line end ********************/-->
                          </div>
                        </div><input type="hidden" id="discusslmt" class="discusslmt" value="0">
                        <div class="row OpenOps">
                          <div class="col-md-12 col-sm-12 quote ">
                            <h1><span class="glyphicon glyphicon-play"></span> Quote History</h1>
                            <!--            /*************************** Test Pipe Line ********************/-->
                            <div style="height:300px;" >
                                <div class="area">
                                    <table class="table table-striped" id="quotehistoy">
                                        <tbody>
                                        <?php $this->QuoteHistory();?>  
                                      </tbody>
                                    </table>  
                                </div>    
                            </div>    
                          </div>
                        </div>
                   <?php
                    else:
                        $this->OpportunityMaps();                     
                    endif;
                    if($td):
                      ?> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="50%" align="left"><?php /*?><a href="javascript:toggleDiv('myContent');" ><img src="<?php echo get_template_directory_uri();?>/images/toggle.png" alt="toggle"></a> <?php  */?></td>
                      <td align="right"><a href="javascript:;" onclick="SwapDisc()"><img src="<?php echo get_template_directory_uri();?>/images/toggleskype.png" alt="toggle"></a></td>
                    </tr>
                  </table> <?php endif;?>
                </div>
                <?php if($td): ?>
                    <div id="myContent1">
                  <div class="col-md-3 col-sm-3 message" > 
                    <div class="messagesec" style="height:450px;">
                        <ul id="msgbrd" class="areamsg">
                            <span id="opshistnewe"></span>
                            <?php
                              $ops=new OpportunityActivity();
                              echo $ops->OpsActHistory();
                            ?>
                        </ul>                      
                    </div>
                    <div class="messagesec">  
                      <ul id="msgtp">  
                          <?php
                            echo $ops->OpsActForm();
                          ?>
                      </ul>
                    </div>    
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php
        $this->IncludeScripts();
       
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
        if($opsres->ops_grp!="2"):
            $btnnew='<tr><td></td><td><a href="javascript:;" class="btn btn-warning actbtn" onclick="SubmitOps()">Submit</a><a href="javascript:;" class="btn btn-warning actbtn" onclick="CancelSettings()">Cancel</a></td></tr>';
        endif;
        
        $output='<form name="opsfrm"><table id="TblGrid_jqGrid" class="EditTable" cellspacing="0" cellpadding="0" border="0">'.$btnnew.$opshidd.$cust_name.'
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
            $output.=$btnnew.'<input type="hidden" name="opsdata" id="opsdata" value="1">';
        endif;
        
        $output.='</tbody></table></form>';
       echo $output;
       exit;
    }
    function IncludeScripts()
    {
        $currency=getCurrency();
        $destage=GetDefaultQuoteStage();
        ?>
        <script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.slimscroll.js"></script>
        <script type="text/javascript">
            function AreaScroll()//Scroll ajax call
            {
                jQuery('.area').slimscroll({
                  width: '200px',
                  height: 'auto'
                }).parent().css({
                  'float': 'left',
                  'margin-right': '30px'
                });
            }
            jQuery(function(){
                jQuery('.area').slimscroll({
                  width: '200px',
                  height: 'auto'
                }).parent().css({
                  'float': 'left',
                  'margin-right': '30px'
                });
                 jQuery('.areamsg').slimscroll({
                  width: '200px',
                  height: 'auto',
                  start:'bottom'
                }).parent().css({
                  'float': 'left',
                  'margin-right': '30px'
                });
             });
             
            function SwapDisc()
            {
                if(jQuery('#myContent1').css('display') == 'none') {
                    jQuery('#myContent1').show('slow');
                    jQuery('.viewmids').removeClass('col-md-9 col-sm-9').addClass('col-md-6 col-sm-6');
                }
                else
                {
                    jQuery('#myContent1').hide('slow');
                    jQuery('.viewmids').removeClass('col-md-6 col-sm-6').addClass('col-md-9 col-sm-9');
                }
            }
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
                //var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                //dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: 700px; height: auto; z-index: 950; overflow: hidden; top: 241px; left: 164px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create new opportunity</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                //jQuery("body").append(dt);
                jQuery.ajax({type: "POST",
                    data: "quotefrm=1&id="+id,
                    success: function(data)
                    {     
                        var data1='<div class="row"><div class="col-md-12 col-sm-12 opportunityview "><h1><span class="glyphicon glyphicon-play"></span>Create Quote</h1>';
                        var data2='<a href="javascript:;" onclick="GobacktoOps()"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/back-32.png">Go Back</a>';
                        //var data1+=data;
                        var data3='</div></div>';
                        jQuery("#loadfullcontent").html(data1+data2+data+data3);
                        jQuery('.OpenOps').hide();
                        jQuery('.timeline').datepicker({
                            dateFormat : 'yy-m-dd'
                        });
                        jQuery('.loadcntheight').show();
                        AreaScroll();
                    }
                });

            }
            function GobacktoOps()
            {
                jQuery("#loadfullcontent").html('');
                jQuery('.OpenOps').show();
                jQuery('.loadcntheight').hide();                
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
                
                jQuery.ajax({type: "POST",
                    data: "getopsform=1",
                    success: function(data)
                    {               
                        var data1='<div class="row"><div class="col-md-12 col-sm-12 opportunityview "><h1><span class="glyphicon glyphicon-play"></span>Create new opportunity</h1>';
                        var data2='<a href="javascript:;" onclick="GobacktoOps()"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/back-32.png">Go Back</a>';
                        //var data1+=data;
                        var data3='</div></div>';
                        jQuery("#loadfullcontent").html(data1+data2+data+data3);
                        jQuery('.OpenOps').hide();
                        jQuery('.timeline').datepicker({
                            dateFormat : 'yy-m-dd'
                        });
                        jQuery('.loadcntheight').show();
                        AreaScroll();
                        
                    }
                    });
            }
            function EditOpportunity(opsid)
            {
               
                jQuery.ajax({type: "POST",
                    data: "getopsform=1&mode=edit&opsid="+opsid,
                    success: function(data)
                    {               
                        var data1='<div class="row"><div class="col-md-12 col-sm-12 opportunityview "><h1><span class="glyphicon glyphicon-play"></span>Create new opportunity</h1>';
                        var data2='<a href="javascript:;" onclick="GobacktoOps()"><img src="<?php echo plugins_url();?>/salesforce_reports/tree/images/back-32.png">Go Back</a>';
                        //var data1+=data;
                        var data3='</div></div>';
                        jQuery("#loadfullcontent").html(data1+data2+data+data3);
                        jQuery('.OpenOps').hide();
                        jQuery('.timeline').datepicker({
                            dateFormat : 'yy-m-dd'
                        });
                        jQuery('.loadcntheight').show();
                        AreaScroll();
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
                function ResendQuoteIt(qid)
                {
                    var result = confirm("Are you sure you want to Resend the quote?");
                    if (result) {
                        var data={};
                        data['quote_id']=qid;
                        data['resend_quote']="1";
                        alert(data);
                        jQuery.post( "<?php echo get_bloginfo('url');?>/salesforce_reports/?action=opportunity&__t=<?php echo $_REQUEST['__t'];?>", data );
                    }
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
        $opsquery="insert into `wp_crm_opportunity` (`user_id`,`opportunity`,`status`,`cpid`,`ops_grp`,`created_at`) values ('".$user_id."','".Json_Format($ops)."','1','".$_POST['cpid']."','1','".date('Y-m-d H:i:s')."')";
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
        if($ops_id):
            $opskey=RFDataEncrypt($ops_id);
            $ta="&__t=".$opskey;
        endif;
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=opportunity".$ta);        
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
        
        if($_REQUEST['__t']):$ta="&__t=".$_REQUEST['__t'];endif;
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=opportunity".$ta);   
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
            $display='<form class="FormGrid MiddlePushcontent" id="QuoteForm" name="QuoteForm"><table cellspacing="5" cellpadding="5" border="0" class="EditTable" id="TblGrid_jqGrid"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr>';
            
            $btnnew='<tr><td></td><td><a class="btn btn-warning actbtn" href="javascript:;" onclick="SaveQuote()">Submit</a><a class="btn btn-warning actbtn" href="javascript:;" onclick="CancelQuote()">Cancel</a></td></tr>';
            
            $display.=$btnnew.'<tr rowpos="1" class="FormData" id="tr_OpportunityName"><td class="CaptionTD">Customer Name</td><td class="DataTD">&nbsp; 
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
                ';
                //$display.='<a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" onclick="PreviewQuote()" style="float:left;">Preview<span class="ui-icon ui-icon-disk"></span></a>';
                $display.=$btnnew.'</table></form>';
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
        $opsid=RFDataDecrypt($_REQUEST['__t']);
        $currency=getCurrency();
       
        $opsquery="SELECT * FROM `wp_crm_reseller_quote` where ops_id=".$opsid." Order by id DESC";
        $response = $wpdb->get_results($opsquery, OBJECT);
        
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
                $qpdf.='&nbsp;&nbsp;<a href="javascript:;" onclick="ResendQuoteIt('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/Mail-icon.png"></a>';                
            elseif($rs->status=="2"):
                $qstatus="Discount approved.";
                $qpdf='<a href="'.$quote->doc_url.'" target="_blank"><img src="'.plugins_url().'/salesforce_reports/tree/images/pdf-icon.png"></a>';
                $qpdf.='&nbsp;&nbsp;<a href="javascript:;" onclick="ResendQuoteIt('.$rs->id.')"><img src="'.plugins_url().'/salesforce_reports/tree/images/Mail-icon.png"></a>';                
            elseif($rs->status=="3"):
                $qstatus="Quote waiting for discount approval.";
            elseif($rs->status=="4"):
                $qstatus="Discount request rejected.";
            endif;
            
            $output.='<tr>';
            $output.= '<td>'.$rs->created.'</td>';
            $output.= '<td>'.$disc.'<b>Opportunity Price</b>:'.$currency.$totsellprice.'</td>';
            $output.= '<td>'.$qstatus.'</td>';
            $output.= '<td>'.$qpdf.'</td></tr>';
            $n++;
        endforeach;
        
        if($n==1):
            $output.='<tr><td colspan="4" align="center"> No quote found</td></tr>';
        endif;
        echo $output;        
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
    function ReSendQuoteCustomer()
    {
        $opsid=RFDataDecrypt($_REQUEST['__t']);
        $in=new ResellerQuoteAction();
        echo $in->ReSendQuoteCustomer($opsid);
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
        elseif($_POST['getopsdisc']=="1"):
            $cmpusr=$_POST['cmpusr'];
            $ops=new OpportunityActivity();
            echo $ops->OpsActHistory($cmpusr);
            exit;     
        elseif($_POST['getopslmt']=="1"):  
            echo $this->GetOpportunityDataLoad();exit;   
        elseif($_POST['resend_quote']=="1"):  
           echo $this->ReSendQuoteCustomer();exit;
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