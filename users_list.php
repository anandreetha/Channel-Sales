<?php
error_reporting('E_ALL');
class Manageuserslist
{
    function UsersTree()
    {
        global $wpdb;
        $user_id = get_current_user_id();$output='';
        $user_info = get_userdata($user_id);
        $display_name = $user_info->display_name;
        
        $query="SELECT u.ID,u.display_name,u.ref_id FROM wp_users as u";
        $where=" Where ID=".$user_id;
        $chk=GetRolesPermission();
        $create_user=$chk->create_user;
        $upload_price=$chk->upload_price;
        $znode=$this->getParentsTree($user_id);
        /*
        ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url();?>/salesforce_reports/lib/js/themes/redmond/jquery-ui.custom.css">
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url();?>/salesforce_reports/lib/js/jqgrid/css/ui.jqgrid.css"></link>
        <link rel="stylesheet" href="<?php echo plugins_url();?>/salesforce_reports/tree/css/demo.css" type="text/css">
	<link rel="stylesheet" href="<?php echo plugins_url();?>/salesforce_reports/tree/css/zTreeStyle/zTreeStyle.css" type="text/css">
	
	<script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.ztree.core-3.5.js"></script>
	<script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.ztree.excheck-3.5.js"></script>
	<script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.ztree.exedit-3.5.js"></script>
        <?php */?>
	<SCRIPT type="text/javascript">
		<!--
                var IDMark_Switch = "_switch",
		IDMark_Icon = "_ico",
		IDMark_Span = "_span",
		IDMark_Input = "_input",
		IDMark_Check = "_check",
		IDMark_Edit = "_edit",
		IDMark_Remove = "_remove",
		IDMark_Ul = "_ul",
		IDMark_A = "_a";
        
		var setting = {
			edit: {
				drag: {
					autoExpandTrigger: true,
					prev: dropPrev,
					inner: dropInner,
					next: dropNext
				},
				enable: true,
				showRemoveBtn: false,
				showRenameBtn: false
			},
			data: {
				simpleData: {
					enable: true
				}
			},
			callback: {
				beforeDrag: beforeDrag,
				beforeDrop: beforeDrop,
				beforeDragOpen: beforeDragOpen,
				onDrag: onDrag,
				onDrop: onDrop,
				onExpand: onExpand
			},
                        view: {
				addDiyDom: addDiyDom
			}
		};

		/*var zNodes =[
			{ id:1, pId:0, name:"can drag 1", open:true},
			{ id:11, pId:1, name:"can drag 1-1"},
			{ id:12, pId:1, name:"can drag 1-2"},
			{ id:121, pId:12, name:"can drag 1-2-1"},
			{ id:122, pId:12, name:"can drag 1-2-2"},
			{ id:123, pId:12, name:"can drag 1-2-3"},
			{ id:13, pId:1, name:"can't drag 1-3", open:true, drag:false},
			{ id:131, pId:13, name:"can't drag 1-3-1", drag:false},
			{ id:132, pId:13, name:"can't drag 1-3-2", drag:false},
			{ id:132, pId:13, name:"can't drag 1-3-3", drag:false},
			{ id:2, pId:0, name:"can't drag my child out 2", open:true, childOuter:false},
			{ id:21, pId:2, name:"can't be parent  2-1", dropInner:false},
			{ id:22, pId:2, name:"can't be root 2-2", dropRoot:false},
			{ id:23, pId:2, name:"try to drag me 2-3"},
			{ id:3, pId:0, name:"can't add/sort my child 3", open:true, childOrder:false, dropInner:false},
			{ id:31, pId:3, name:"can drag 3-1"},
			{ id:32, pId:3, name:"can drag 3-2"},
			{ id:33, pId:3, name:"can drag 3-3"}
		];*/
                var zNodes=[<?php echo $znode.$this->GetChildsTree($query,$where);?>];

		function dropPrev(treeId, nodes, targetNode) {
			var pNode = targetNode.getParentNode();
			if (pNode && pNode.dropInner === false) {
				return false;
			} else {
				for (var i=0,l=curDragNodes.length; i<l; i++) {
					var curPNode = curDragNodes[i].getParentNode();
					if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
						return false;
					}
				}
			}
			return true;
		}
		function dropInner(treeId, nodes, targetNode) {
			if (targetNode && targetNode.dropInner === false) {
				return false;
			} else {
				for (var i=0,l=curDragNodes.length; i<l; i++) {
					if (!targetNode && curDragNodes[i].dropRoot === false) {
						return false;
					} else if (curDragNodes[i].parentTId && curDragNodes[i].getParentNode() !== targetNode && curDragNodes[i].getParentNode().childOuter === false) {
						return false;
					}
				}
			}
			return true;
		}
		function dropNext(treeId, nodes, targetNode) {
			var pNode = targetNode.getParentNode();
			if (pNode && pNode.dropInner === false) {
				return false;
			} else {
				for (var i=0,l=curDragNodes.length; i<l; i++) {
					var curPNode = curDragNodes[i].getParentNode();
					if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
						return false;
					}
				}
			}
			return true;
		}

		var log, className = "dark", curDragNodes, autoExpandNode;
		function beforeDrag(treeId, treeNodes) {
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" beforeDrag ]&nbsp;&nbsp;&nbsp;&nbsp; drag: " + treeNodes.length + " nodes." );
			for (var i=0,l=treeNodes.length; i<l; i++) {
				if (treeNodes[i].drag === false) {
					curDragNodes = null;
					return false;
				} else if (treeNodes[i].parentTId && treeNodes[i].getParentNode().childDrag === false) {
					curDragNodes = null;
					return false;
				}
			}
			curDragNodes = treeNodes;
			return true;
		}
		function beforeDragOpen(treeId, treeNode) {
			autoExpandNode = treeNode;
			return true;
		}
		function beforeDrop(treeId, treeNodes, targetNode, moveType, isCopy) {
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" beforeDrop ]&nbsp;&nbsp;&nbsp;&nbsp; moveType:" + moveType);
			showLog("target: " + (targetNode ? targetNode.name : "root") + "  -- is "+ (isCopy==null? "cancel" : isCopy ? "copy" : "move"));
			return true;
		}
		function onDrag(event, treeId, treeNodes) {
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" onDrag ]&nbsp;&nbsp;&nbsp;&nbsp; drag: " + treeNodes.length + " nodes." );
		}
		function onDrop(event, treeId, treeNodes, targetNode, moveType, isCopy) {
                        var tarr={};
                        for (var i=0,l=treeNodes.length; i<l; i++) {
                           tarr[treeNodes[i].id]=treeNodes[i].pId;                           
			}
                        jQuery.post( "<?php echo get_bloginfo('url');?>/salesforce_reports/?action=role_config&changeuser=1", tarr );
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" onDrop ]&nbsp;&nbsp;&nbsp;&nbsp; moveType:" + moveType);
			showLog("target: " + (targetNode ? targetNode.name : "root") + "  -- is "+ (isCopy==null? "cancel" : isCopy ? "copy" : "move"))
		}
		function onExpand(event, treeId, treeNode) {
			if (treeNode === autoExpandNode) {
				className = (className === "dark" ? "":"dark");
				showLog("[ "+getTime()+" onExpand ]&nbsp;&nbsp;&nbsp;&nbsp;" + treeNode.name);
			}
		}

		function showLog(str) {
			if (!log) log = jQuery("#log");
			log.append("<li class='"+className+"'>"+str+"</li>");
			if(log.children("li").length > 8) {
				log.get(0).removeChild(log.children("li")[0]);
			}
		}
		function getTime() {
			var now= new Date(),
			h=now.getHours(),
			m=now.getMinutes(),
			s=now.getSeconds(),
			ms=now.getMilliseconds();
			return (h+":"+m+":"+s+ " " +ms);
		}

		function setTrigger() {
			var zTree = jQuery.fn.zTree.getZTreeObj("treeDemo");
			zTree.setting.edit.drag.autoExpandTrigger = jQuery("#callbackTrigger").attr("checked");
		}
                function addDiyDom(treeId, treeNode) {
                        var csid=jQuery("#csid").val();
                        if(csid==treeNode.id){
                        var aObj = jQuery("#" + treeNode.tId + IDMark_A);
                        var tid=treeNode.tId;
                        var dtname=tid.split('treeDemo_');
                        var dname= dtname[1];
                        var editStr = '<a onclick="EditNameIT('+treeNode.id+','+dname+')">'+"<img src='<?php echo plugins_url()."/salesforce_reports/tree/images/edit.png";?>'></a>";
                                    <?php
                                        if($create_user=="1"):?>
                                            editStr+="<a onclick='CreateUserIT("+treeNode.id+");'><img src='<?php echo plugins_url()."/salesforce_reports/tree/images/add.png";?>'></a>"+
                                            "<a onclick='Importusers("+treeNode.id+");'><img src='<?php echo plugins_url()."/salesforce_reports/tree/images/import_csv.png";?>'></a>";
                                    <?php 
                                        endif;
                                        if($upload_price=="2"):?>
                                              editStr+="<a href='<?php echo get_bloginfo('url')."/salesforce_reports/?action=price_upload";?>'><img src='<?php echo plugins_url()."/salesforce_reports/tree/images/currency_dollar.png";?>'></a>";
                                          
                                    <?php endif;?>
				aObj.after(editStr);
                        }
			
		}
                function CreateUserIT(id)
                {
                  var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="ui-widget-overlay"></div>';
                  dt+='<div class="ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodlist1" dir="ltr" style="width: 300px; height: auto; z-index: 950; overflow: hidden; top: 564px; left: 524.5px; position: absolute; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdlist1" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdlist1" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Create user</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" href="javascript:;" onclick="CancelUser()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntlist1"><div><form style="width:auto;overflow:auto;position:relative;height:auto;" onsubmit="return false;" class="FormGrid" id="FrmGrid_list1" name="FormPost"><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_list1"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr><tr rowpos="1" class="FormData" id="tr_user_email"><td class="CaptionTD">Email</td><td class="DataTD">&nbsp;<input type="text" id="user_email" name="user_email" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr><tr rowpos="2" class="FormData" id="tr_display_name"><td class="CaptionTD">Firstname</td><td class="DataTD">&nbsp;<input type="text" id="display_name" name="display_name" role="textbox" class="FormElement ui-widget-content ui-corner-all"></td></tr><tr style="display:none" class="FormData"><td class="CaptionTD"></td><td class="DataTD" colspan="1"><input type="text" value="_empty" name="list1_id" id="id_g" class="FormElement"></td></tr></tbody></table></form><table cellspacing="0" cellpadding="0" border="0" id="TblGrid_list1_2" class="EditTable"><tbody><tr><td colspan="2"><hr style="margin:1px" class="ui-widget-content"></td></tr><tr id="Act_Buttons"><td class="navButton"><a class="fm-button ui-state-default ui-corner-left" id="pData" style="display: none;"><span class="ui-icon ui-icon-triangle-1-w"></span></a><a class="fm-button ui-state-default ui-corner-right" id="nData" style="display: none;"><span class="ui-icon ui-icon-triangle-1-e"></span></a></td><td class="EditButton"><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" href="javascript:;" onclick="SaveUser('+id+')">Submit<span class="ui-icon ui-icon-disk"></span></a><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" href="javascript:;" onclick="CancelUser()">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr class="binfo" style="display:none"><td colspan="2" class="bottominfo"></td></tr></tbody></table></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                  jQuery("body").append(dt);
                  var pos=jQuery("#treeDemo").position();
                  jQuery("#editmodlist1").css('left', pos.left+'px');jQuery("#editmodlist1").css('top', pos.top+'px');
                }
                function CancelUser(){
                  jQuery(".ui-widget-overlay").remove();
                  jQuery("#editmodlist1").remove();
                }
                function SaveUser(id)
                {
                    var user_email=jQuery('#user_email').val();
                    var display_name=jQuery('#display_name').val();
                    if(user_email=="")
                    {
                       alert("Please enter your email id"); 
                       jQuery('#user_email').focus();
                    }
                    else if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(user_email)))
                    {
                        alert("Please Enter Your Valid Email Id");
                        jQuery('#user_email').focus()	
                    }
                    else if(display_name=="")
                    {
                       alert("Please enter your displayname"); 
                       jQuery('#display_name').focus();
                    }
                    else
                    {
                        jQuery.ajax({type: "POST",
				data: "createuser=1&user_email="+user_email+"&display_name="+display_name+'&ref_id='+id,
				success: function(data)
				{     
                                    document.location.href="";
				}
				});
                    }
                }
                function EditNameIT(id,did)
                {
                    var dname=jQuery('#treeDemo_'+did+'_span').html();
                    var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="ui-widget-overlay"></div>';
                    dt+='<div aria-hidden="false" aria-labelledby="edithdlist1" role="dialog" tabindex="-1" style="width: 300px; height: auto; z-index: 950; overflow: hidden; top: 282.5px; left: 170px; position: absolute; display: block;" dir="ltr" id="editmodlist1" class="ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1"><div style="cursor: move;" id="edithdlist1" class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span style="float: left;" class="ui-jqdialog-title">Edit user</span><a onclick="CancelUser()" href="javascript:;" style="right: 0.3em;" class="ui-jqdialog-titlebar-close ui-corner-all"><span class="ui-icon ui-icon-closethick"></span></a></div><div id="editcntlist1" class="ui-jqdialog-content ui-widget-content"><div><form name="FormPost" id="FrmGrid_list1" class="FormGrid" onsubmit="return false;" style="width:auto;overflow:auto;position:relative;height:auto;"><table cellspacing="0" cellpadding="0" border="0" id="TblGrid_list1" class="EditTable"><tbody><tr id="FormError" style="display:none"><td class="ui-state-error" colspan="2"></td></tr><tr style="display:none" class="tinfo"><td class="topinfo" colspan="2"></td></tr><tr id="tr_display_name" class="FormData" rowpos="2"><td class="CaptionTD">Firstname</td><td class="DataTD">&nbsp;<input type="text" class="FormElement ui-widget-content ui-corner-all" role="textbox" name="display_name" id="display_name" value="'+dname+'"></td></tr><tr class="FormData" style="display:none"><td class="CaptionTD"></td><td colspan="1" class="DataTD"><input type="text" class="FormElement" id="id_g" name="list1_id" value="_empty"></td></tr></tbody></table></form><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_list1_2"><tbody><tr><td colspan="2"><hr class="ui-widget-content" style="margin:1px"></td></tr><tr id="Act_Buttons"><td class="navButton"><a style="display: none;" id="pData" class="fm-button ui-state-default ui-corner-left"><span class="ui-icon ui-icon-triangle-1-w"></span></a><a style="display: none;" id="nData" class="fm-button ui-state-default ui-corner-right"><span class="ui-icon ui-icon-triangle-1-e"></span></a></td><td class="EditButton"><a onclick="UpdateDisplayName('+id+','+did+')" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a onclick="CancelUser()" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr style="display:none" class="binfo"><td class="bottominfo" colspan="2"></td></tr></tbody></table></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                    jQuery("body").append(dt);
                    var pos=jQuery("#treeDemo").position();
                    jQuery("#editmodlist1").css('left', pos.left+'px');jQuery("#editmodlist1").css('top', pos.top+'px');
                }
                function UpdateDisplayName(id,did)
                {
                    var display_name=jQuery('#display_name').val();var data={};
                    if(display_name=="")
                    {
                        alert("Please enter your display name");
                        jQuery('#display_name').focus();
                    }
                    else
                    {
                        data['id']=id;
                        data['display_name']=display_name;                    
                        jQuery('#treeDemo_'+did+'_span').html(display_name);
                        jQuery.post( "<?php echo get_bloginfo('url');?>/salesforce_reports/?action=role_config&changename=1", data );
                        CancelUser();
                    }                    
                }
                function Importusers(id)
                {
                    var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="ui-widget-overlay"></div>';
                    dt+='<div class="ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodlist1" dir="ltr" style="width: 300px; height: auto; z-index: 950; overflow: hidden; top: 310px; left: 170px; position: absolute; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdlist1" aria-hidden="false"><div style="cursor: move;" id="edithdlist1" class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span style="float: left;" class="ui-jqdialog-title">Create user</span><a onclick="CancelUser()" href="javascript:;" style="right: 0.3em;" class="ui-jqdialog-titlebar-close ui-corner-all"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntlist1"><div><form style="width:auto;overflow:auto;position:relative;height:auto;" enctype="multipart/form-data" class="FormGrid" id="FrmGrid_list1" name="FormPost"><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_list1"><tbody><tr style="display:none" id="FormError"><td colspan="2" class="ui-state-error"></td></tr><tr class="tinfo" style="display:none"><td colspan="2" class="topinfo"></td></tr><tr rowpos="2" class="FormData" id="tr_display_name"><td class="CaptionTD">Select your file</td><td class="DataTD">&nbsp;<input type="file" id="import_user" name="import_user" class="FormElement ui-widget-content ui-corner-all"></td></tr><tr style="display:none" class="FormData"><td class="CaptionTD"></td><td class="DataTD" colspan="1"><input type="text" value="_empty" name="list1_id" id="id_g" class="FormElement"></td></tr></tbody></table><input type="hidden" name="importuser" id="importuser" value="1"><input type="hidden" name="ref_id" id="ref_id" value="'+id+'"></form><table cellspacing="0" cellpadding="0" border="0" id="TblGrid_list1_2" class="EditTable"><tbody><tr><td colspan="2"><hr style="margin:1px" class="ui-widget-content"></td></tr><tr id="Act_Buttons"><td class="navButton"><a class="fm-button ui-state-default ui-corner-left" id="pData" style="display: none;"><span class="ui-icon ui-icon-triangle-1-w"></span></a><a class="fm-button ui-state-default ui-corner-right" id="nData" style="display: none;"><span class="ui-icon ui-icon-triangle-1-e"></span></a></td><td class="EditButton"><a onclick="ImportfrmUser()" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a class="fm-button ui-state-default ui-corner-all fm-button-icon-left" href="javascript:;" onclick="CancelUser()">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr class="binfo" style="display:none"><td colspan="2" class="bottominfo"></td></tr></tbody></table></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                    jQuery("body").append(dt);
                    var pos=jQuery("#treeDemo").position();
                    jQuery("#editmodlist1").css('left', pos.left+'px');jQuery("#editmodlist1").css('top', pos.top+'px');
                }
                function ImportfrmUser()
                {
                    document.FormPost.action="";
                    document.FormPost.method="POST";
                    document.FormPost.submit(); 
                }
		jQuery(document).ready(function(){
			jQuery.fn.zTree.init(jQuery("#treeDemo"), setting, zNodes);
			jQuery("#callbackTrigger").bind("change", {}, setTrigger);
		});
		//-->
	</SCRIPT>
        <div class="uconfig"><h3>Users List</h3>
        <input type="hidden" name="csid" id="csid" value="<?php echo $user_id;?>">
        <ul id="treeDemo" class="ztree"></ul>
        </div>
        <style type="text/css">
            #content{width:1009px; margin: 0 auto;}
        </style>
        <br style="clear:both;">
        <br style="clear:both;">
	
     <?php   
    }
    function GetChildsTree($query,$where)
    {
        global $wpdb;
        $querynew=$query.$where;
        $userls = $wpdb->get_results($querynew, OBJECT);//print_r($userls);
        foreach($userls as $us):
            $user = new WP_User( $us->ID );
            $user_roles=$user->roles[0];
            if($user_roles=="reseller"):$dropinner=",dropInner:false,dropRoot:false";
            else:$dropinner="";endif;
            if($us->ref_id==""): $ref_id=0; else: $ref_id=$us->ref_id; endif;
            echo '{ id:'.$us->ID.', pId:'.$ref_id.', name:"'.$us->display_name.'",role:"'.$user_roles.'" '.$dropinner.'},';
            $where1=" Where ref_id=".$us->ID;
            $this->GetChildsTree($query,$where1);
        endforeach;
        //return $display;
         
    }
    function getParentsTree($user_id)
    {
        global $wpdb;
        $query="SELECT T2.id,T2.ref_id,T2.display_name
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
                ORDER BY T1.lvl DESC";
        $result = $wpdb->get_results($query, OBJECT);    
        foreach($result as $rs):
            if($user_id==$rs->id):
            else:
                $user = new WP_User( $rs->id );
                $user_roles=$user->roles[0];
                $dropinner=",open:true, drag:false";
                if($rs->ref_id==""): $ref_id=0; else: $ref_id=$rs->ref_id; endif;
                $display.= '{ id:'.$rs->id.', pId:'.$ref_id.', name:"'.$rs->display_name.'",role:"'.$user_roles.'" '.$dropinner.'},';               
            endif;            
        endforeach;  
        return $display;
    }
    function InsertUser($uid='',$email='',$display_name='',$extra='')
    {
        global $wpdb;
        if($uid=="")$uid = $_POST['ref_id'];
        $user = new WP_User( $uid );
        $user_roles=$user->roles[0];
        if($email=="")$email=$_POST['user_email'];
        if($display_name=="")$display_name=$_POST['display_name'];
        
        $select_query="select role_name from `wp_crm_roleconfig` where parent IN( select id from `wp_crm_roleconfig` where role_name='".$user_roles."')";
        $result=$wpdb->get_results($select_query, OBJECT);
        $newrole=$result[0]->role_name; 
        
        if( null == username_exists( $email ) ) {
            $password = wp_generate_password( 12, false );
            $user_id = wp_create_user( $email, $password, $email );
            wp_update_user(
              array(
                'ID'          =>    $user_id,
                'nickname'    =>    $display_name,
                'role'        =>    $newrole
              )
            );
            $wpdb->query("update wp_users set ref_id='".$uid."',display_name='".$display_name."' where ID=".$user_id);
            //wp_mail( $email, 'Welcome!', 'Your Password: ' . $password );
            if($extra==""): echo $user_id;exit;endif;
        }
        
    }
    function ImportUsers()
    {
        global $wpdb; 
        $ref_id=$_POST['ref_id'];
        $handle = fopen($_FILES['import_user']['tmp_name'], "r");

	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $this->InsertUser($ref_id,$data[0],$data[1],'1');
	}
	fclose($handle);        
    }
    function UpdateName()
    {
       global $wpdb;
       $id=$_POST['id'];
       $display_name=$_POST['display_name'];
       $wpdb->query("update wp_users set display_name='".$display_name."' where ID=".$id);
       exit;
    }    
    function UsersShow()
    {
        $this->AutoUsersRoleScripts();
        $this->UsersTree();
        
    }
    function AutoUsersRoleScripts()
    {
        ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url();?>/salesforce_reports/lib/js/themes/redmond/jquery-ui.custom.css">
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url();?>/salesforce_reports/lib/js/jqgrid/css/ui.jqgrid.css"></link>
        <link rel="stylesheet" href="<?php echo plugins_url();?>/salesforce_reports/tree/css/demo.css" type="text/css">
	<link rel="stylesheet" href="<?php echo plugins_url();?>/salesforce_reports/tree/css/zTreeStyle/zTreeStyle.css" type="text/css">
	
	<script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.ztree.core-3.5.js"></script>
	<script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.ztree.excheck-3.5.js"></script>
	<script type="text/javascript" src="<?php echo plugins_url();?>/salesforce_reports/tree/js/jquery.ztree.exedit-3.5.js"></script>
        <?php
    }
    function Json_Format($args='')
    {
        $jsonp=$args;
	header('Content-type: application/json');
	return json_encode($jsonp);
	exit;
    }
}
class UserslistConfig extends Manageuserslist
{
    function usersdisplay()
    {
        $user_id = get_current_user_id();
        $user = new WP_User( $user_id );
        $user_roles=$user->roles[0];
        if($_REQUEST['createuser']=="1"):
            $display=$this->InsertUser();
        elseif($_REQUEST['changename']=="1"):
            $display=$this->UpdateName();
        elseif($_REQUEST['importuser']=="1"):
            $display=$this->ImportUsers();                          
        else:
            $display=$this->UsersShow();
        endif;
        return $display;
    }
}
