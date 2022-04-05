<?php
	class DisplaySFReports
	{
            	function SFdisplay()
		{
                    $user_id = get_current_user_id();
                    $user = new WP_User( $user_id );
                    $user_roles=$user->roles[0];
                    ob_start();
                    if($_GET['action']=="cp_list"):
                        $cp=new ManageCPCost();
                        echo $cp->display(); 
                    elseif($_GET['action']=="intl_sprice"):
                        $sp=new ManageInternalSellPrice();
                        echo $sp->Displayprice();
                    elseif($_GET['action']=="rs_sprice"):
                        $sp=new ManageResellerSell();
                        echo $sp->Displaysprice();    
                    elseif($_GET['action']=="cp_approve"):
                        $cp=new CostPriceApproval();
                        echo $cp->getDisplay();    
                    elseif($_GET['action']=="cost_price"):
                        $cp=new CostPriceList();
                        echo $cp->DisplayCostprice();     
                    elseif($_GET['action']=="price_upload"):
                        $perm=new ManagePriceList();
                        echo $perm->Displayprice();  
                    elseif($_GET['action']=="users_list"):
                        $perm=new UserslistConfig();
                        echo $perm->usersdisplay();
                    elseif($_GET['action']=="discounts"):
                        $role_group=GetUserRoleGroup();
                        if($role_group=="2"):
                            $perm=new ManageResellerDiscounts();
                            echo $perm->DisplayRSDiscounts(); 
                        elseif($role_group=="1"):  
                            $perm=new ManageCostpriceDiscounts();
                            echo $perm->DisplayCPDiscounts();                             
                        endif;   
                    elseif($_GET['action']=="role_config"):
                        if($user_roles=="companyadmin"):
                            $perm=new ManageRolesConfig();
                            echo $perm->Configdisplay();
                        else:
                            echo 'Sorry you don\'t have a rights to access this page';    
                        endif;
                    elseif($_GET['action']=="crm_user"):
                        if(($user_roles=="companyadmin")||($user_roles=="representative")):
                            $user=new ManageCRMUserData();
                            echo $user->crmuserecredential();
                        else:
                            echo 'Sorry you don\'t have a rights to access this page';    
                        endif;    
                    elseif($_GET['action']=="mail_temp"):
                        $mail=new ManageMailTemp();
                        echo $mail->display();
                    elseif($_GET['action']=="products"):
                        $prod=new ManageProductList();
                        echo $prod->display();    
                    elseif($_GET['action']=="opportunity"):
                        $role_group=GetUserRoleGroup();
                        if($role_group=="2"):
                            $perm=new ResellerOpportunity();
                            echo $perm->display();
                        elseif($role_group=="1"): 
                            $perm=new CompanyOpportunity();
                            echo $perm->display();                            
                        else:    
                            echo 'You dont have a permission';exit;
                        endif; 
                    else:    
                        if(($user_roles=="companyadmin")||($user_roles=="representative")||($user_roles=="administrator")): 
                            $manage_users=new ManageUsersbyRole();    
                            echo $manage_users->getUserInfoData($user_roles); 
                        else:
                            echo 'Sorry you don\'t have a rights to access this page';
                        endif;                                               
                        //$this->SFChartsdisplay();
                    endif;
                    echo '<link rel="stylesheet" href="'.plugins_url() . '/salesforce_reports/css/sales.css"/>'
                            . '<link rel="stylesheet" type="text/css" media="screen" href="'.plugins_url().'/salesforce_reports/lib/js/themes/redmond/jquery-ui.custom.css">
                            <link rel="stylesheet" type="text/css" media="screen" href="'.plugins_url().'/salesforce_reports/lib/js/jqgrid/css/ui.jqgrid.css"></link>';
                    $output.=ob_get_clean();
                    remove_filter ('the_content', 'wpautop'); 
                    
                    return $output;
		}		
	}
	
?>