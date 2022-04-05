<?php
function GetRolesPermission($check='',$fld='')
{
    global $wpdb;
    $user_id = get_current_user_id();
    $user = new WP_User( $user_id );
    $user_roles=$user->roles[0]; 
    $flds='';
    if($fld)$flds=',`'.$fld.'`';
        
    $query="select config".$flds." from `wp_crm_roleconfig` where role_name='".$user_roles."'";
    $result = $wpdb->get_results($query, OBJECT);
    $config=$result[0]->config; 
    if($check==""):
        $output=json_decode($config)->role;
        if($fld):$output->$fld=$result[0]->$fld;endif;
    else:    
        $permission=json_decode($config)->role;
        return $permission->$check;
    endif;
    return $output;    
}
function GetUserRoleGroup($user_id='')
{
    global $wpdb;
    if($user_id=="")$user_id = get_current_user_id();
    $user = new WP_User( $user_id );
    $user_roles=$user->roles[0]; 
    
    $query="select `group` from `wp_crm_roleconfig` where role_name='".$user_roles."'";
    $result = $wpdb->get_results($query, OBJECT);
    return $result[0]->group; 
}
function GetCompanyLastMember($role='')
{
    global $wpdb;
    $query="select * from `wp_crm_roleconfig` where `group`=1 order by parent DESC Limit 0,1";
    $result = $wpdb->get_results($query, OBJECT);    
    if($role): return $result[0]; endif;
    
    $user_id = get_current_user_id();
    $user = new WP_User( $user_id );
    $user_roles=$user->roles[0];
    
    
}
function GetResellerCompanyByTree($role_data='',$cpid='')
{
    global $wpdb;
    $query="select * from `wp_crm_roleconfig` where `group`=2 order by parent ASC Limit 0,1";
    $result = $wpdb->get_results($query, OBJECT);
    if($role_data): return $result[0];endif;
    
    $user_id = get_current_user_id();
    $user = new WP_User( $user_id );
    $user_roles=$user->roles[0];
    if($cpid):
        $where1=" AND ID NOT IN(select user_id from `wp_crm_cpuser`);";
    endif;
    $query="SELECT u.ID,u.display_name,u.ref_id FROM wp_users as u";
    $where=" Where ref_id=".$user_id.$where1;    
    $role_chk=$result[0]->role_name;
    $childs=GetChildsUsersofParent($query,$where,$role_chk,$where1);
}

function GetChildsUsersofParent($query,$where,$role_chk='',$where1='')
{
    global $wpdb;
    $querynew=$query.$where;
    $userls = $wpdb->get_results($querynew, OBJECT);
    foreach($userls as $us):
        $user = new WP_User( $us->ID );
        $user_roles=$user->roles[0];
        if($role_chk):
            if($user_roles==$role_chk):
                $wherenew=" Where ref_id=".$us->ID;
                $viewall=' <a href="javascript:;" onclick="ViewIT('.$us->ID.')" class="view_details">View Details</a>';
                echo '<li class="usr_'.$us->ID.' spuser"><input type="checkbox" name="pickusers" class="pkusr" value="'.$us->ID.'">&nbsp;'.$us->display_name.'&nbsp;'.$viewall.'<br><div class="hidemychild mychild_'.$us->ID.'">';
                GetMyChildsoFUsers($query,$wherenew);
                echo '</div></li>';                
            endif;
        else:
            
        endif;        
        $where2=" Where ref_id=".$us->ID.$where1;        
        $childs=GetChildsUsersofParent($query,$where2,$role_chk,$where1);       
    endforeach;
    
}
function GetMyChildsoFUsers($query,$where)
{
    global $wpdb;
    $querynew=$query.$where;
    $userls = $wpdb->get_results($querynew, OBJECT); 
    echo '<ul>';
    foreach($userls as $us):
        echo '<li class="details_'.$us->ID.' viewdetusr">&nbsp;'.$us->display_name.'&nbsp;'.$viewall;
        $where2=" Where ref_id=".$us->ID;        
        $childs=GetMyChildsoFUsers($query,$where2);  
        echo '</li>';
    endforeach;
    echo '</ul>';
}
function Json_Format($args='')
{
    $jsonp=$args;
    header('Content-type: application/json');
    return json_encode($jsonp);
    exit;
}
function GetmyResellerCompanyId()
{
    global $wpdb;
    $user_id = get_current_user_id();
    $query='SELECT role_name FROM `wp_crm_roleconfig` where `group`=2 order by parent ASC limit 0,1';
    $result1=$wpdb->get_results($query, OBJECT);
    $role_name=$result1[0]->role_name;
    
    $queryparent="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T2.ref_id DESC";
    
    $resultps=$wpdb->get_results($queryparent, OBJECT);
    foreach($resultps as $ps):
        $user = new WP_User( $ps->id );
        $user_roles=$user->roles[0];
        if($user_roles==$role_name):$psid=$ps->id;break;endif;
    endforeach;
    return $psid;
}
function GetResellerSellingList()
{
    global $wpdb;
    $user_id = get_current_user_id();    
    $queryparent="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T2.ref_id DESC";
    
    $resultps=$wpdb->get_results($queryparent, OBJECT);
    foreach($resultps as $ps):
        $query="SELECT * from `wp_crm_reseller_sellprice` where user_id=".$ps->id." AND status=1 Order by id DESC";
        $result=$wpdb->get_results($query, OBJECT);
        if(count($result)>0):$data=$result[0];break;endif;
    endforeach;
    return $data;
}
add_action('wp_head', 'cvf_ps_enqueue_datepicker');
function cvf_ps_enqueue_datepicker() {
    if(!is_super_admin(get_current_user_id())):
        wp_enqueue_script('jquery-ui-datepicker');
    endif;
}
function getCurrency()
{
    return '$';
}
function GetDefaultQuoteStage()
{
    return array('Proposal/Price Quote','Negotiation/Review');
}
function getQuoteTemplate($data)
{
    global $wpdb,$current_user;
    get_currentuserinfo();
    $currency=getCurrency();
    $user_id = get_current_user_id();
    $user = new WP_User( $user_id );
    $user_roles=$user->roles[0];
    $arr=json_decode($data);
    $cinfo=$arr->data->customer_info;
    $product=$arr->data->product;
    
    $html='<div ><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" width="402">&nbsp;</td><td valign="top" width="270"><h1>INVOICE</h1></td></tr><tr><td valign="bottom" width="402">
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
        $n=1;
        foreach($product as $ps):
            if($ps->new_sprice): $sellprice=$ps->new_sprice; else: $sellprice=$ps->price; endif;
            if($ps->new_sprice_total): $sprice_total=$ps->new_sprice_total; else: $sprice_total=$ps->product_total; endif;
            $html.='<tr>
                    <td width="84">
                        <p align="center">'.$n.'</p>
                        <p>&nbsp;</p>
                    </td>
                    <td width="210">
                        <p><em>'.$ps->Name.'</em></p>
                        <p><em>&nbsp;</em></p>
                    </td>

                    <td width="124">
                        <p class="Amount" align="left">'.$currency.$sellprice.'</p>
                        <p class="Amount" align="left">&nbsp;</p>
                    </td>
                    <td width="87">
                        <p class="Amount" align="center">'.$ps->qty.'</p>
                        <p class="Amount" align="left">&nbsp;</p>
                    </td>
                    <td width="107">
                        <p class="Amount" align="center">'.$currency.$sprice_total.'</p>
                        <p class="Amount" align="left">&nbsp;</p>
                    </td>
                </tr>';
            $totprice+=$sprice_total; $n++;   
        endforeach;   
        
        $html.='<tr><td><p>&nbsp;</p></td><td valign="top" width="123"><p class="Labels">&nbsp;</p></td><td width="124"><p class="Labels">&nbsp;</p></td><td valign="top" width="87"><p class="Amount">SUBTOTAL</p></td><td width="107"><p class="Amount" align="center"><strong><span style="text-decoration: underline;">'.$currency.'</span></strong>'.$totprice.'</p></td></tr>';
        
        $html.='<tr><td><p>&nbsp;</p></td><td valign="top" width="123"><p class="Labels">&nbsp;</p></td><td width="124"><p class="Labels">&nbsp;</p></td><td valign="top" width="87"><p class="Amount">VAT @ 17&frac12;%</p></td><td width="107"><p class="Amount">&nbsp;</p></td></tr>';
        
        $html.='<tr><td><p>&nbsp;</p></td><td valign="top" width="123"><p class="Labels">&nbsp;</p></td><td width="124"><p class="Labels">&nbsp;</p></td><td valign="top" width="87"><p class="Amount"><strong>TOTAL</strong></p></td><td width="107"><p class="Amount" align="left"><strong><span style="text-decoration: underline;">'.$currency.'</span></strong><strong><span style="text-decoration: underline;">'.$totprice.'.00</span></strong></p></td></tr></tbody></table></div>';
        $pdf=new ResellerPDFInvoice();   
        $doc_url= $pdf->GenerateInvoice($html); 
        return $doc_url;
}
function GetmyCompanyRepId()
{
    global $wpdb;
    $user_id = get_current_user_id();
    $query='select * from `wp_crm_roleconfig` where `group`=1 order by parent DESC Limit 0,1';
    $result1=$wpdb->get_results($query, OBJECT);
    $role_name=$result1[0]->role_name;
    
    $queryparent="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T2.ref_id DESC";
    
    $resultps=$wpdb->get_results($queryparent, OBJECT);
    foreach($resultps as $ps):
        $user = new WP_User( $ps->id );
        $user_roles=$user->roles[0];
        if($user_roles==$role_name):$psid=$ps->id;break;endif;
    endforeach;
    return $psid;
}
function GetCPApprovalAuthority($maxdiscount)
{
    global $wpdb;
    $user_id = get_current_user_id();
    $conf="SELECT config,cost_approve,role_name FROM `wp_crm_roleconfig` WHERE `group`='1'";
    $confres=$wpdb->get_results($conf, OBJECT);
    foreach($confres as $cf):
        $config=json_decode($cf->config);
        if($config->role->discount_approve>=$maxdiscount):
            $role_name=$cf->role_name;
            break;
        endif;
    endforeach;
    if($role_name==""):
        $confquery="SELECT role_name FROM `wp_crm_roleconfig` WHERE `group`='1' AND cost_approve='1'";
        $cfres=$wpdb->get_results($confquery, OBJECT);
        $role_name=$cfres[0]->role_name;
    endif;
    
    $queryparent="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T2.ref_id DESC";
    
    $resultps=$wpdb->get_results($queryparent, OBJECT);
    foreach($resultps as $ps):
        $user = new WP_User( $ps->id );
        $user_roles=$user->roles[0];
        if($user_roles==$role_name):$psid=$ps->id;break;endif;
    endforeach;
    return $psid;    
}
function CallPageNotFound()
{
    
}

function GetAllTreeChildIds($query,$where)
{
    global $wpdb;
    $user_id = get_current_user_id();
    $currency=getCurrency();
    $query="SELECT u.ID,u.display_name,u.ref_id FROM wp_users as u";
    $where=" Where ref_id=".$user_id; 
    $querynew=$query.$where;
    $userls = $wpdb->get_results($querynew, OBJECT); 
    foreach($userls as $us):
        $pid = $us->ID;
        $cat_list[$pid] = recursiveCategory($pid,array(),$query);
    endforeach;
    return $cat_list;

}
function recursiveCategory($ref_id, $array, $query)
{
    global $wpdb;
    $querynew=$query." Where ref_id=".$ref_id;
    $userls = $wpdb->get_results($querynew, OBJECT); 
    foreach($userls as $us):
        $sub_cat = $us->ID;
        $array[] = $sub_cat;
        $array = recursiveCategory($sub_cat, $array,$query);
    endforeach;
    return $array;
}
function GetChildUserIdsinCommas()
{
    $child_users=GetAllTreeChildIds();
    $ids=array();
    foreach($child_users as $k=>$cs):
       $ids[]=$k;
       if(count($cs)>0)$ids[]=implode(",",$cs);
    endforeach;
    return implode(",",$ids);                  
}
function GetResellerCompanyName($user_id)
{
    global $wpdb;
    if($user_id=="")$user_id = get_current_user_id();
    $query='select * from `wp_crm_roleconfig` where `group`=2 order by parent ASC Limit 0,1';
    $result1=$wpdb->get_results($query, OBJECT);
    $role_name=$result1[0]->role_name;
    
   $queryparent="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T2.ref_id DESC";
    
    $resultps=$wpdb->get_results($queryparent, OBJECT);
    foreach($resultps as $ps):
        $user = new WP_User( $ps->id );
        $user_roles=$user->roles[0];
        if($user_roles==$role_name):$psid=$ps->id;break;endif;
    endforeach;
    return $psid;
}
function GetInternalSellList()
{
    global $wpdb;
    $user_id = get_current_user_id();    
    $queryparent="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T2.ref_id DESC";
    
    $resultps=$wpdb->get_results($queryparent, OBJECT);
    foreach($resultps as $ps):
        $query="SELECT * from `wp_crm_internal_sellprice` where user_id=".$ps->id." AND status=1 Order by id DESC";
        $result=$wpdb->get_results($query, OBJECT);
        if(count($result)>0):$data=$result[0];break;endif;
    endforeach;
    return $data;
}
?>