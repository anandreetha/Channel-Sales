<?php
class ManageRolesInfo
{
    function HierarchyRoledisplay()
    {
        global $wpdb;
        $user_id = get_current_user_id();$output='';
        $user_info = get_userdata($user_id);
        $display_name = $user_info->display_name;
        
        $query="SELECT u.ID,u.display_name,u.ref_id FROM wp_users as u";
        $where=" Where ID=".$user_id;
        
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
                var zNodes=[<?php $this->GetChildsTree($query,$where);?>];

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
                        if(treeNode.role!="reseller"){
                        var aObj = jQuery("#" + treeNode.tId + IDMark_A);
                        var tid=treeNode.tId;
                        var dtname=tid.split('treeDemo_');
                        var dname= dtname[1];
                        var editStr = '<a onclick="EditNameIT('+treeNode.id+','+dname+')">'+"<img src='<?php echo plugins_url()."/salesforce_reports/tree/images/edit.png";?>'></a>" +
					"<a onclick='CreateUserIT("+treeNode.id+");'><img src='<?php echo plugins_url()."/salesforce_reports/tree/images/add.png";?>'></a>"+
                                        "<a onclick='Importusers("+treeNode.id+");'><img src='<?php echo plugins_url()."/salesforce_reports/tree/images/import_csv.png";?>'></a>";
				aObj.after(editStr);
                        }
			/*if (treeNode.parentNode && treeNode.parentNode.id!=2) return;
			
			if (treeNode.id == 21) {
				var editStr = "<span class='demoIcon' id='diyBtn_" +treeNode.id+ "' title='"+treeNode.name+"' onfocus='this.blur();'><span class='button icon01'></span></span>";
				aObj.append(editStr);
				var btn = $("#diyBtn_"+treeNode.id);
				if (btn) btn.bind("click", function(){alert("diy Button for " + treeNode.name);});
			} else if (treeNode.id == 22) {
				var editStr = "<span class='demoIcon' id='diyBtn_" +treeNode.id+ "' title='"+treeNode.name+"' onfocus='this.blur();'><span class='button icon02'></span></span>";
				aObj.after(editStr);
				var btn = $("#diyBtn_"+treeNode.id);
				if (btn) btn.bind("click", function(){alert("diy Button for " + treeNode.name);});
			} else if (treeNode.id == 23) {
				var editStr = "<select class='selDemo' id='diyBtn_" +treeNode.id+ "'><option value=1>1</option><option value=2>2</option><option value=3>3</option></select>";
				aObj.after(editStr);
				var btn = $("#diyBtn_"+treeNode.id);
				if (btn) btn.bind("change", function(){alert("diy Select value="+btn.attr("value")+" for " + treeNode.name);});
			} else if (treeNode.id == 24) {
				var editStr = "<span id='diyBtn_" +treeNode.id+ "'>Text Demo...</span>";
				aObj.after(editStr);
			} else if (treeNode.id == 25) {
				var editStr = "<a id='diyBtn1_" +treeNode.id+ "' onclick='alert(1);return false;'>??1</a>" +
					"<a id='diyBtn2_" +treeNode.id+ "' onclick='alert(2);return false;'>??2</a>";
				aObj.after(editStr);
			}*/
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
        <div class="uconfig"><h3>User configuration</h3>
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
            if($user_roles=="companyadmin"):$dropinner=",open:true,drag:false";
            elseif($user_roles=="reseller"):$dropinner=",dropInner:false,dropRoot:false";
            else:$dropinner="";endif;
            if($us->ref_id==""): $ref_id=0; else: $ref_id=$us->ref_id; endif;
            echo '{ id:'.$us->ID.', pId:'.$ref_id.', name:"'.$us->display_name.'",role:"'.$user_roles.'" '.$dropinner.'},';
            $where1=" Where ref_id=".$us->ID;
            $this->GetChildsTree($query,$where1);
        endforeach;
        return $output;
         
    }
     
    function InsertRolesConfig()
    {
        global $wpdb;
        $roles=explode(",",$_POST['rid']);
        $c=0;
        foreach($roles as $rs):
            $idval=explode("__",$rs);
            $ids=$idval[0]; $data=array();
            $rolename=$_POST['rolename_'.$ids];
            if($rolename=="companyadmin"):
                $depth=0;$parent=0;
            else:
                $rsnew=$roles[$c-1];
                $idvalnew=explode("__",$rsnew);
                $parent=$idvalnew[0];
            endif;
            if($rolename=="reseller")$depth=count($rs)-1;
            $depth=$idval[1];
            $data['role']['create_user']=$_POST['create_user_'.$ids];
            $data['role']['create_ops']=$_POST['create_ops_'.$ids];
            $data['role']['delete_ops']=$_POST['delete_ops_'.$ids];
            $data['role']['upload_price']=$_POST['upload_price_'.$ids];
            $data['role']['discount_approve']=$_POST['discount_approve_'.$ids];
            
            $roledata=$this->Json_Format($data);
            $quer="update `wp_crm_roleconfig` set config='".$roledata."',parent='".$parent."',depth='".$depth."',status=1 where id=".$ids;
            $wpdb->query($quer);
            $c++;
        endforeach;
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=role_config");
        exit;
    }
    function AddRolesConfig()
    {
        global $wpdb;
        $roles=explode(",",$_POST['roles']);
        foreach($roles as $rs):
            $quer="insert into `wp_crm_roleconfig`(`role_name`,`parent`,`depth`,`status`)values('".$rs."','1','1','1')";
            $wpdb->query($quer);
        endforeach;
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=role_config");
        exit;
        //echo "1";
        //exit;
    }
    function ChangeParent()
    {
        global $wpdb;
        foreach($_POST as $k=>$v):
            $user = new WP_User( $v );
            $user_roles=$user->roles[0];
            $select_query="select role_name from `wp_crm_roleconfig` where parent IN( select id from `wp_crm_roleconfig` where role_name='".$user_roles."')";
            $result=$wpdb->get_results($select_query, OBJECT);
            $newrole=$result[0]->role_name;            
            $query="update `wp_users` set ref_id='".$v."' where id=".$k;
            $wpdb->query($query);
            $user = new WP_User( $k );            
            $user->set_role($newrole);            
        endforeach;
        exit;
    }
    function ChangeRoleParent()
    {
        global $wpdb;
        foreach($_POST as $k=>$v):
            $query="update `wp_crm_roleconfig` set parent='".$v."',depth='".$v."' where id=".$k;
            $wpdb->query($query);
        endforeach;
        exit;
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
    function ChangeRoleSetting()
    {
        global $wpdb,$wp_roles;
        $id=$_POST['id'];
        $queryconfig="SELECT * from `wp_crm_roleconfig` where id=".$id;        
        $resconfig = $wpdb->get_results($queryconfig, OBJECT);
        $d=0;$rid=""; //print"<pre>";print_r($resconfig);print"</pre>";
        $output='<form name="cfrm" id="cfrm">';
        foreach($resconfig as $rs): //$rid.=$rs->id.",";
            $config=json_decode($rs->config); 
            if($config->role->create_user=="1") $cuserchk='checked="checked"';
            if($config->role->create_ops=="1")  $createopschk='checked="checked"';
            if($config->role->delete_ops=="1")  $deleteopschk='checked="checked"';
            if($config->role->escalate=="1") $escchk='checked="checked"';
            if($config->role->smtpups=="1") $smtpchk='checked="checked"';
            
            
            if($config->role->upload_price=="2"):
                $uploadpricechk1='checked="checked"';
            elseif($config->role->upload_price=="1"):
                $uploadpricechk2='checked="checked"';
            else:
                $uploadpricechk3='checked="checked"';
            endif;   
            if($config->role->upload_cost_price=="1"):
                $cpricechk='checked="checked"';
            endif;
            //print"<pre>";print_r($config->role->discount_approve);print"</pre>";
            
        if($rs->group=="1"):    
            $query="SELECT count(cost_approve) as cp,role_name from `wp_crm_roleconfig` where cost_approve=1";        
            $rconfig = $wpdb->get_results($query, OBJECT);    
            if(($rs->cost_approve=="1")||($rconfig[0]->cp=="0")):   
                if($rs->cost_approve=="1"): $cpchk='checked="checked"'; else: $cpchk=""; endif;
                $cpapprove='<input type="checkbox" value="1" name="cost_approve" id="cost_approve" '.$cpchk.'>&nbsp;Cost Price Approval Authority';    
            else:
                $cpapprove='Cost Price Approval Authority Role:<b>'.$rconfig[0]->role_name.'</b>';
            endif;
            $cpapprove='<tr><td class="ipbox">'.$cpapprove.'</td></tr><tr><td><hr></td></tr>';
            $upscp='<input type="checkbox" name="upload_cost_price" value="1" '.$cpricechk.'> Upload Cost price<br>';            
            $discontapp='<tr>
                                            <td class="ipbox"><input type="checkbox" value="1" name="escalate" '.$escchk.'> Escalate discount approval</td>
                                        </tr>
                                        <tr>
                                            <td class="ipbox">Discount approval limit:<input type="textbox" value="'.$config->role->discount_approve.'" name="discount_approve">%</td>
                                        </tr> ';
            
            
        endif;
        $discontapp.='<tr>
                                            <td class="ipbox"><input type="checkbox" value="1" name="smtpups" '.$smtpchk.'> Upload Own email template & SMTP</td>
                                        </tr>';
       if($rs->group=="2"): 
           $upsell='<input type="radio" name="upload_price" value="2" '.$uploadpricechk1.'> Upload Selling price';
       endif;
            
        $output.= '<table width="100%" cellspacing="6" cellpadding="5" border="0">
                                        <tbody>
                                        '.$cpapprove.'                                        
                                        <tr>
                                            <td class="ipbox"><input type="checkbox" '.$cuserchk.' value="1" name="create_user">&nbsp;Create next level user</td>
                                        </tr><tr>        
                                            <td class="ipbox"><input type="checkbox" '.$createopschk.' value="1" name="create_ops">&nbsp;Create opportunity</td>                                            
                                        </tr>
                                        <tr> 
                                            <td class="ipbox"><input type="checkbox" '.$deleteopschk.' value="1" name="delete_ops">&nbsp;Delete opportunity</td>    
                                        </tr>
                                        <tr> 
                                            <td class="ipbox">
                                            '.$upscp.$upsell.'                                            
                                            <input type="radio" name="upload_price" value="1" '.$uploadpricechk2.'> View Pricelist
                                            <input type="radio" name="upload_price" value="0" '.$uploadpricechk3.'> No
                                            </td>    
                                        </tr>
                                         '.$discontapp.'
                                        
                                    </tbody></table><input type="hidden" name="role_id" id="role_id" value="'.$id.'"><input type="hidden" name="parent_id" id="parent_id" value="'.$_POST['parent_id'].'">';
        
        $output.='<br><table cellspacing="0" cellpadding="0" border="0" class="EditTable" id="TblGrid_list1_2"><tbody><tr><td colspan="2"><hr class="ui-widget-content" style="margin:1px"></td></tr><tr id="Act_Buttons"><td class="navButton"><a style="display: none;" id="pData" class="fm-button ui-state-default ui-corner-left"><span class="ui-icon ui-icon-triangle-1-w"></span></a><a style="display: none;" id="nData" class="fm-button ui-state-default ui-corner-right"><span class="ui-icon ui-icon-triangle-1-e"></span></a></td><td class="EditButton"><a onclick="SaveSettings()" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Submit<span class="ui-icon ui-icon-disk"></span></a><a onclick="CancelSettings()" href="javascript:;" class="fm-button ui-state-default ui-corner-all fm-button-icon-left">Cancel<span class="ui-icon ui-icon-close"></span></a></td></tr><tr style="display:none" class="binfo"><td class="bottominfo" colspan="2"></td></tr></tbody></table>';
        endforeach;
        $output.= '</form>';
        echo $output;
        exit;
    }
    function ChangeSetting()
    {
        global $wpdb;
        $id=$_POST['role_id'];
        $data['role']['create_user']=$_POST['create_user'];
        $data['role']['create_ops']=$_POST['create_ops'];
        $data['role']['delete_ops']=$_POST['delete_ops'];
        $data['role']['upload_price']=$_POST['upload_price'];
        $data['role']['upload_cost_price']=$_POST['upload_cost_price'];        
        $data['role']['escalate']=$_POST['escalate'];   
        $data['role']['smtpups']=$_POST['smtpups']; 
        $data['role']['discount_approve']=$_POST['discount_approve'];

        $roledata=$this->Json_Format($data);
        if($_POST['cost_approve']=="1"):$wpdb->query("update `wp_crm_roleconfig` set cost_approve=0");endif;
        $query="update `wp_crm_roleconfig` set config='".$roledata."',parent='".$_POST['parent_id']."',cost_approve='".$_POST['cost_approve']."',status=1 where id=".$id; 
        $wpdb->query($query);
        exit;
    }
    function Rolesinfo()
    {
        $comm=new CommonRoleSetting();
        $comm->AutoRoleScripts();
        $comm->TreeDiagram();
        $this->HierarchyRoledisplay();
        
    }
    function Json_Format($args='')
    {
        $jsonp=$args;
	header('Content-type: application/json');
	return json_encode($jsonp);
	exit;
    }
}
class ManageRolesConfig extends ManageRolesInfo
{
    function Configdisplay()
    {
         $user_id = get_current_user_id();
        $user = new WP_User( $user_id );
        $user_roles=$user->roles[0];
        if($user_roles!="companyadmin"): return 'You dont have a permission to access this';endif;
        if($_POST['addrolestoconfig']=="1"):
            $display=$this->AddRolesConfig();
        elseif($_REQUEST['changerole']=="1"):
            $display=$this->ChangeRoleParent();        
        elseif($_REQUEST['changeuser']=="1"):
            $display=$this->ChangeParent();
        elseif($_REQUEST['rolesetting']=="1"):
            $display=$this->ChangeRoleSetting();        
        elseif($_REQUEST['createuser']=="1"):
            $display=$this->InsertUser();
        elseif($_REQUEST['changename']=="1"):
            $display=$this->UpdateName();
        elseif($_REQUEST['importuser']=="1"):
            $display=$this->ImportUsers();        
        elseif($_POST['rolepost']=="1"):
            $display=$this->InsertRolesConfig();     
        elseif($_REQUEST['changesetting']=="1"):
            $display=$this->ChangeSetting();           
        elseif($_REQUEST['setuprole']=="1"):
            $display=$this->TreeDiagram();              
        else:
            $display=$this->Rolesinfo();
        endif;
        return $display;
    }
}
class CommonRoleSetting
{
    function __construct()
    {
        
    }
    function AutoRoleSets()
    {
        global $wpdb;
        $role=$_POST['create_role'];
        add_role( $role, $role, array( 'read' => true, 'level_0' => true ) );
        $quer="insert into `wp_crm_roleconfig`(`role_name`,`parent`,`depth`,`status`,`group`)values('".$role."','1','1','1','".$_POST['group']."')";
        $wpdb->query($quer);
        header("Location: ".get_bloginfo('url')."/salesforce_reports/?action=role_config");
        exit;
    }
    function TreeDiagram()
    {  
        global $wpdb,$wp_roles;
        $queryconfig="SELECT * from `wp_crm_roleconfig` order by parent ASC";        
        $resconfig = $wpdb->get_results($queryconfig, OBJECT);
        $d=0;$rid="";
        foreach($resconfig as $rs): //$rid.=$rs->id.",";
            $config=json_decode($rs->config); 
            if($config->role->create_user=="1") $cuserchk='checked="checked"';
            if($config->role->create_ops=="1")  $createopschk='checked="checked"';
            if($config->role->delete_ops=="1")  $deleteopschk='checked="checked"';
            if($config->role->upload_price=="1")$uploadpricechk='checked="checked"';
            if($rs->role_name=="companyadmin"):                
                $drag=", drag:false";
            elseif($rs->role_name=="reseller"):                
                $drag=", dropInner:false";          
            else:   
                $drag="";
            endif;
            $tree.='{ id:'.$rs->id.', pId:'.$rs->parent.', name:"'.$rs->role_name.'", open:true '.$drag.'},';
        endforeach;  
        //echo $tree;
        if($_POST['auto_role']=="1"):$this->AutoRoleSets();endif;
        
        ?>
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
        
		var settingRoles = {
			edit: {
				drag: {
					autoExpandTrigger: true,
					prev: dropPrevRoles,
					inner: dropInnerRoles,
					next: dropNextRoles
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
				beforeDrag: beforeDragRoles,
				beforeDrop: beforeDropRoles,
				beforeDragOpen: beforeDragOpenRoles,
				onDrag: onDragRoles,
				onDrop: onDropRoles,
				onExpand: onExpandRoles
			},
                        view: {
				addDiyDom: addDiyDomRoles
			}
		};
                var zNodesRoles=[<?php echo $tree;?>];

		function dropPrevRoles(treeId, nodes, targetNode) {
			var pNode = targetNode.getParentNode();
			if (pNode && pNode.dropInnerRoles === false) {
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
		function dropInnerRoles(treeId, nodes, targetNode) {
			if (targetNode && targetNode.dropInnerRoles === false) {
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
		function dropNextRoles(treeId, nodes, targetNode) {
			var pNode = targetNode.getParentNode();
			if (pNode && pNode.dropInnerRoles === false) {
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
		function beforeDragRoles(treeId, treeNodes) {
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" beforeDragRoles ]&nbsp;&nbsp;&nbsp;&nbsp; drag: " + treeNodes.length + " nodes." );
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
		function beforeDragOpenRoles(treeId, treeNode) {
			autoExpandNode = treeNode;
			return true;
		}
		function beforeDropRoles(treeId, treeNodes, targetNode, moveType, isCopy) {
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" beforeDropRoles ]&nbsp;&nbsp;&nbsp;&nbsp; moveType:" + moveType);
			showLog("target: " + (targetNode ? targetNode.name : "root") + "  -- is "+ (isCopy==null? "cancel" : isCopy ? "copy" : "move"));
			return true;
		}
		function onDragRoles(event, treeId, treeNodes) {
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" onDragRoles ]&nbsp;&nbsp;&nbsp;&nbsp; drag: " + treeNodes.length + " nodes." );
		}
		function onDropRoles(event, treeId, treeNodes, targetNode, moveType, isCopy) {
                        var tarr={};
                        for (var i=0,l=treeNodes.length; i<l; i++) {
                           tarr[treeNodes[i].id]=treeNodes[i].pId;                           
			}
                        jQuery.post( "<?php echo get_bloginfo('url');?>/salesforce_reports/?action=role_config&changerole=1", tarr );
			className = (className === "dark" ? "":"dark");
			showLog("[ "+getTime()+" onDropRoles ]&nbsp;&nbsp;&nbsp;&nbsp; moveType:" + moveType);
			showLog("target: " + (targetNode ? targetNode.name : "root") + "  -- is "+ (isCopy==null? "cancel" : isCopy ? "copy" : "move"))
		}
		function onExpandRoles(event, treeId, treeNode) {
			if (treeNode === autoExpandNode) {
				className = (className === "dark" ? "":"dark");
				showLog("[ "+getTime()+" onExpandRoles ]&nbsp;&nbsp;&nbsp;&nbsp;" + treeNode.name);
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
			var zTree = jQuery.fn.zTree.getZTreeObj("treeDemoRoles");
			zTree.settingRoles.edit.drag.autoExpandTrigger = jQuery("#callbackTrigger").attr("checked");
		}
                function addDiyDomRoles(treeId, treeNode) {
                        if(treeNode.role!="reseller"){
                        var aObj = jQuery("#" + treeNode.tId + IDMark_A);
                        var tid=treeNode.tId;
                        var dtname=tid.split('treeDemoRoles_');
                        var dname= dtname[1];
                        var editStr = '<a onclick="RoleConfSetting('+treeNode.id+','+dname+')">'+"<img src='<?php echo plugins_url()."/salesforce_reports/tree/images/settings.png";?>'></a>";
			aObj.after(editStr);
                        }			
		}
                jQuery(document).ready(function(){
			jQuery.fn.zTree.init(jQuery("#treeDemoRoles"), settingRoles, zNodesRoles);
			jQuery("#callbackTrigger").bind("change", {}, setTrigger);
		});
                function CreateRolesIT()
                {
                    var crole=jQuery('#create_role').val();
                    if(crole=="")
                    {
                        alert("Please enter your role name");
                        jQuery('#create_role').focus();
                    }
                    else
                    {
                        document.cfrm.action="";
                        document.cfrm.method="POST";
                        document.cfrm.submit();
                    }
                }
                function ClearRoles()
                {
                    jQuery('#create_role').val('');
                }
                function RoleConfSetting(id,did)
                {
                    var dt='<div style="height: 100%; width: 100%; position: fixed; left: 0px; top: 0px; z-index: 949; opacity: 0.3;" class="quoteoverlay ui-widget-overlay"></div>';
                    dt+='<div class="createquotebox rolesettings ui-widget ui-widget-content ui-corner-all ui-jqdialog jqmID1" id="editmodjqGrid" dir="ltr" style="width: auto; height:auto; z-index: 950; overflow: hidden; top: 241px; left: 364px; display: block;" tabindex="-1" role="dialog" aria-labelledby="edithdjqGrid" aria-hidden="false"><div class="ui-jqdialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" id="edithdjqGrid" style="cursor: move;"><span class="ui-jqdialog-title" style="float: left;">Opportunity Details</span><a class="ui-jqdialog-titlebar-close ui-corner-all" style="right: 0.3em;" onclick="CancelSettings()"><span class="ui-icon ui-icon-closethick"></span></a></div><div class="ui-jqdialog-content ui-widget-content" id="editcntjqGrid"><div id="dataloader"><img style="margin-top:15%;margin-left:15%;" src="<?php echo get_template_directory_uri();?>/media/images/loader.gif"></div></div><div class="jqResize ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div></div>';
                    jQuery("body").append(dt);
                    jQuery.ajax({type: "POST",
                        data: "rolesetting=1&id="+id+'&parent_id='+did,
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
                function SaveSettings()
                {
                    jQuery.post( "<?php echo get_bloginfo('url');?>/salesforce_reports/?action=role_config&changesetting=1", jQuery('form#cfrm').serialize() );
                    CancelSettings();
                }
		//-->
	</SCRIPT>
        <div id="menu-settings-column" class="metabox-holder">
            <h3>Role configuration</h3>
            <div style="padding: 10px; width: 200px; border: 1px solid rgb(166, 201, 226);">
                <form name="cfrm">    
                <h3 tabindex="0" class="accordion-section-title hndle" style="font-size:12px">Create role to assign</h3>
                <table border="0" width="100%">
                    <tr>
                        <td><span class="rolename">Role name</span></td>
                        <td><input type="text" name="create_role" id="create_role" style="width:105px"><br><br></td>
                    </tr>
                    <tr>
                        <td><span class="rolename">Group</span></td>
                        <td><select name="group" class="group">
                    <option value="1">Company</option>
                    <option value="2">Sellers</option>
                </select></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="button" onclick="CreateRolesIT()" value="Submit" class="button-secondary submit-add-to-menu right">
                            <input type="button" onclick="ClearRoles()" value="Cancel" class="button-secondary submit-add-to-menu right">
                            <input type="hidden" name="auto_role" id="auto_role" value="1"> 
                        </td>
                    </tr>
                </table>    
               
                </form>    
            </div>            
        </div>
        <ul id="treeDemoRoles" class="ztree"></ul>
        <style type="text/css">
            .group{font-size: 12px;}
            #content{width:1009px; margin: 0 auto;}
            #menu-settings-column li{list-style: none;}
            #pickrole{margin-right: 4px}
            div.vertical-line
            {
                width: 1px;
                background-color: silver;
                height: 600px;
                border: 2px ridge silver ;
                border-radius: 2px;
                margin:0px 20px 0px 20px;
                float:left;
            }
            .ipbox{font-size:13px;}
            #menu-to-edit{padding:2em;}
            .page-title{display:none;}
            .uconfig{float:left;}
            .menu-edit{float:left;margin-left:4px;margin-bottom:4em;}
            #treeDemoRoles{float:left;}
            .metabox-holder{float:left;width:auto;}
            .rolename{color:#2e6e9e; font-size:12px; font-weight:bold;width:50px;}
        </style>    
        <script type="text/javascript">
            function AddRolestoConfig()
            {
                var roles=jQuery('input[name=pickrole]:checked').map(function() {return this.value;}).get().join(',');
                if(roles=="")
                {
                    alert("Please select your user roles to config");
                }
                else
                {
                    jQuery('#roles').val(roles);
                    document.pickrole.action="";
                    document.pickrole.method="POST";
                    document.pickrole.submit();                    
                }
            }
            function EditMenus(id)
            {
                jQuery('#menu-item-settings-'+id).toggle('slow');
            }
            function SaveRoleConfig()
            {
                var mydepth="";
                var ids=jQuery('.roleconfig').map(function() {return jQuery(this).attr('class');}).get().join(',');
                var rid=ids.split(",");
                for(var r=0;r<rid.length; r++){
                    var depthval=rid[r].split('menu-item-depth-');
                    var depth=depthval[1].split(" menu-item-custom");
                    var idval=depth[1].split("roletotids-");
                    mydepth+=idval[1]+"__"+depth[0];
                    if(r<rid.length)mydepth+=',';
                }
                jQuery('#rid').val(mydepth);
                document.frm.action="";
                document.frm.method="POST";
                document.frm.submit();
            }
        </script>
        <div class="vertical-line">&nbsp;</div>
        <?php
        
    }
    function AutoRoleScripts()
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
}