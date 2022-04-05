<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", "off");
class jqgrid

	{
	var $jqargs;
	var $options = array();
	var $jqparam1 = array();
	var $jqparamsnew = array();
	var $select_command;
	var $table;
	var $actions;
	var $debug;
	var $jqval;
	var $jqitemsnew;
	var $events;
	function jqgrid($gridargs = null)
		{
                
		if (PHPGRID_AUTOCONNECT == 1)
			{
			$gridargs = array();
			$gridargs["type"] = PHPGRID_DBTYPE;
			$gridargs["server"] = PHPGRID_DBHOST;
			$gridargs["user"] = PHPGRID_DBUSER;
			$gridargs["password"] = PHPGRID_DBPASS;
			$gridargs["database"] = PHPGRID_DBNAME;
			}

		session_start();
		$this->jqitemsnew = "mysql";
		$this->debug = 1;
		$this->error_msg = "Some issues occured in this operation, Contact technical support for help";
		@mysql_query("SET NAMES 'utf8'");
		if ($gridargs)
			{
			include_once ("adodb/adodb.inc.php");

			$gridrplce = $gridargs["type"];
			$this->jqval = ADONewConnection($gridrplce);
			$this->jqval->SetFetchMode(ADODB_FETCH_ASSOC);
			$this->jqval->debug = 0;
			$this->jqval->Connect($gridargs["server"], $gridargs["user"], $gridargs["password"], $gridargs["database"]);
			if ($gridargs["type"] == "mysql" || $gridargs["type"] == "mysqli") $this->jqval->Execute("SET NAMES 'utf8'");
			$this->jqitemsnew = $gridargs["type"];
			}

		$griddataform["datatype"] = "json";
		$griddataform["rowNum"] = 20;
		$griddataform["width"] = 900;
		$griddataform["height"] = 350;
		$griddataform["rowList"] = array(
			10,
			20,
			30,
			'All'
		);
		$griddataform["viewrecords"] = true;
		$griddataform["multiSort"] = false;
		$griddataform["scrollrows"] = true;
		$griddataform["toppager"] = false;
		$griddataform["prmNames"] = array(
			"page" => "jqgrid_page"
		);
		$griddataform["sortname"] = "1";
		$griddataform["sortorder"] = "asc";
		$griddataform["form"]["nav"] = false;
		$griddataform["url"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$griddataform["editurl"] = $griddataform["url"];
		$griddataform["cellurl"] = $griddataform["url"];
		$griddataform["scroll"] = 0;
		$griddataform["sortable"] = true;
		$griddataform["cellEdit"] = false;
		$griddataform["add_options"] = array(
			"recreateForm" => true,
			"closeAfterAdd" => true,
			"closeOnEscape" => true,
			"errorTextFormat" => "function(r){ return r.responseText;}",
			"jqModal" => true
		);
		$griddataform["edit_options"] = array(
			"recreateForm" => true,
			"closeAfterEdit" => true,
			"closeOnEscape" => true,
			"errorTextFormat" => "function(r){ return r.responseText;}",
			"jqModal" => true
		);
		$griddataform["delete_options"] = array(
			"closeOnEscape" => true,
			"errorTextFormat" => "function(r){ return r.responseText;}"
		);
		$griddataform["view_options"]["closeOnEscape"] = true;
		$griddataform["form"]["position"] = "center";
		$this->options = $griddataform;
		$this->actions["showhidecolumns"] = false;
		$this->actions["inlineadd"] = false;
		$this->actions["search"] = "";
		$this->actions["export"] = false;
		}

	public

	function strip($stripargs)
		{
			{
			if (is_array($stripargs))
			if (array_is_associative($stripargs))
				{
				foreach($stripargs as $spsdata => $stripdatasplt) $stripdatarplcval[$spsdata] = stripslashes($stripdatasplt);
				$stripargs = $stripdatarplcval;
				}
			  else
			for ($strpforval = 0; $strpforval < sizeof($stripargs); $strpforval++) $stripargs[$strpforval] = stripslashes($stripargs[$strpforval]);
			  else $stripargs = stripslashes($stripargs);
			}

		return $stripargs;
		}

	private function construct_where($conswre)
		{
		$consassn = "";
		$consdefaultargs = array(
			'eq' => " = ",
			'ne' => " <> ",
			'lt' => " < ",
			'le' => " <= ",
			'gt' => " > ",
			'ge' => " >= ",
			'bw' => " LIKE ",
			'bn' => " NOT LIKE ",
			'in' => " IN ",
			'ni' => " NOT IN ",
			'ew' => " LIKE ",
			'en' => " NOT LIKE ",
			'cn' => " LIKE ",
			'nu' => " IS NULL ",
			'nn' => " IS NOT NULL ",
			'nc' => " NOT LIKE "
		);
		if ($conswre)
			{
			$consarrchk = (array)json_decode($conswre, true);
			if (is_array($consarrchk))
				{
				$consgrpchk = $consarrchk['groupOp'];
				$consforech = $consarrchk['rules'];
				$inscalv = 0;
				foreach($consforech as $consforechkay => $consfrchval)
					{
					$consfrchval = (array)$consfrchval;
					$consopsnewval6 = $consfrchval['op'];
					foreach($this->options["colModel"] as $conswherefmdtarr)
						{
						if ($consfrchval['field'] == $conswherefmdtarr["name"] && !empty($conswherefmdtarr["formatoptions"]) && in_array($consopsnewval6, array(
							"cn",
							"ne",
							"eq",
							"gt",
							"ge",
							"lt",
							"le"
						)))
							{
							if ($conswherefmdtarr["formatoptions"]["newformat"] == "d/m/Y")
								{
								$consfmoptnewarrval = explode("/", $consfrchval['data']);
								$consfrchval['data'] = $consfmoptnewarrval[1] . "/" . $consfmoptnewarrval[0] . "/" . $consfmoptnewarrval[2];
								}

							if ($conswherefmdtarr["formatter"] == "date") $consfrchval['data'] = date("Y-m-d", strtotime($consfrchval['data']));
							  else
							if ($conswherefmdtarr["formatter"] == "datetime") $consfrchval['data'] = date("Y-m-d H:i:s", strtotime($consfrchval['data']));
							}

						if ($consfrchval['field'] == $conswherefmdtarr["name"] && !empty($conswherefmdtarr["dbname"]))
							{
							$consfrchval['field'] = $conswherefmdtarr["dbname"];
							}
						}

					$contfldcondnew = $consfrchval['field'];
					$stripdatasplt = $consfrchval['data'];
					if (strpos($this->jqitemsnew, "mssql") !== false) $stripdatasplt = utf8_decode($stripdatasplt);
					$strpdtsptval = 0;
					if (strpos($stripdatasplt, "!=") === 0 || strpos($stripdatasplt, ">=") === 0 || strpos($stripdatasplt, "<=") === 0) $strpdtsptval = 2;
					  else
					if (strpos($stripdatasplt, "=") === 0 || strpos($stripdatasplt, ">") === 0 || strpos($stripdatasplt, "<") === 0) $strpdtsptval = 1;
					if ($strpdtsptval > 0)
						{
						$consdbchk = substr($stripdatasplt, 0, $strpdtsptval);
						$condbvalchknm = substr($stripdatasplt, $strpdtsptval);
						if (!is_numeric($condbvalchknm)) continue;
						$stripdatasplt = " $consdbchk $condbvalchknm";
						$consopsnewval6 = 'inline';
						$consdefaultargs['inline'] = '';
						}

					if (isset($stripdatasplt) && isset($consopsnewval6))
						{
						$inscalv++;
						$stripdatasplt = $this->to_sql($contfldcondnew, $consopsnewval6, $stripdatasplt);
						if ($inscalv == 1) $consassn = " AND ";
						  else $consassn.= " " . $consgrpchk . " ";
						switch ($consopsnewval6)
							{
						case 'in':
						case 'ni':
							$consassn.= $contfldcondnew . $consdefaultargs[$consopsnewval6] . " (" . $stripdatasplt . ")";
							break;

						case 'cn':
							$consassn.= $contfldcondnew . $consdefaultargs[$consopsnewval6] . $stripdatasplt;
							break;

						case 'bw':
							$consassn.= "LOWER($contfldcondnew)" . $consdefaultargs[$consopsnewval6] . " LOWER(" . $stripdatasplt . ")";
							break;

						case 'nn':
						case 'nu':
							$consassn.= $contfldcondnew . $consdefaultargs[$consopsnewval6];
							break;

						default:
							$consassn.= $contfldcondnew . $consdefaultargs[$consopsnewval6] . $stripdatasplt;
							}
						}
					}
				}
			}

		return $consassn;
		}

	private
	function to_sql($contfldcondnew, $oper, $consfrchval)
		{
		if ($oper == 'bw' || $oper == 'bn') return "'" . addslashes($consfrchval) . "%'";
		  else
		if ($oper == 'ew' || $oper == 'en') return "'%" . addcslashes($consfrchval) . "'";
		  else
		if ($oper == 'cn' || $oper == 'nc') return "'%" . addslashes($consfrchval) . "%'";
		  else
		if ($oper == 'inline') return addslashes($consfrchval);
		  else
		if ($oper == 'in' || $oper == 'ni')
			{
			$consfrchval = "'" . implode("','", explode(",", addslashes($consfrchval))) . "'";
			return $consfrchval;
			}
		  else return "'" . addslashes($consfrchval) . "'";
		}

	function set_actions($stargval)
		{
		if (empty($stargval)) $stargval = array();
		if (empty($this->actions)) $this->actions = array();
		foreach($stargval as $spsdata => $stripdatasplt)
		if (is_array($stripdatasplt))
			{
			if (!isset($this->actions[$spsdata])) $this->actions[$spsdata] = array();
			$stargval[$spsdata] = array_merge($stargval[$spsdata], $this->actions[$spsdata]);
			}

		$this->actions = array_merge($this->actions, $stargval);
		}

	function set_options($options)
		{
		if (empty($stargval)) $stargval = array();
		if (empty($this->options)) $this->options = array();
		if (isset($options["rowList"])) unset($this->options["rowList"]);
		foreach($options as $spsdata => $stripdatasplt)
		if (is_array($stripdatasplt))
			{
			if (!isset($this->options[$spsdata])) $this->options[$spsdata] = array();
			$options[$spsdata] = array_merge($this->options[$spsdata], $options[$spsdata]);
			}

		$this->options = array_merge($this->options, $options);
		$this->options["editurl"] = $this->options["url"];
		$this->options["cellurl"] = $this->options["url"];
		$stoptnewdtvalchk = '';
		if ($this->options["form"]["nav"] === true)
			{
			$stoptnewdtvalchk = 'setTimeout(function(){jQuery("#pData").show();jQuery("#nData").show();},100);';
			}
		  else
			{
			$stoptnewdtvalchk = 'setTimeout(function(){jQuery("#pData").hide();jQuery("#nData").hide();},100);';
			}

		$this->jqparamsnew["add_options"]["beforeShowForm"] = $stoptnewdtvalchk;
		$this->jqparamsnew["edit_options"]["beforeShowForm"] = $stoptnewdtvalchk;
		$this->jqparamsnew["delete_options"]["beforeShowForm"] = $stoptnewdtvalchk;
		if (isset($this->options["toolbar"]) && $this->options["toolbar"] != "bottom")
			{
			$this->options["toppager"] = true;
			if ($this->options["hiddengrid"] == true && $this->options["toolbar"] == "top") $this->options["toolbar"] = "both";
			}

		if ($this->options["form"]["position"] == "center")
			{
			$stopshowfrmvalchk = ($this->options["add_options"]["jqModal"] == false) ? "fixed" : "abs";
			$this->jqparamsnew["add_options"]["beforeShowForm"].= ' var gid = formid.attr("id").replace("FrmGrid_","");			jQuery("#editmod" + gid).' . $stopshowfrmvalchk . 'center(); ';
			$stopshowfrmvalchk = ($this->options["edit_options"]["jqModal"] == false) ? "fixed" : "abs";
			$this->jqparamsnew["edit_options"]["beforeShowForm"].= ' var gid = formid.attr("id").replace("FrmGrid_","");			jQuery("#editmod" + gid).' . $stopshowfrmvalchk . 'center(); ';
			$stopshowfrmvalchk = ($this->options["delete_options"]["jqModal"] == false) ? "fixed" : "abs";
			$this->jqparamsnew["delete_options"]["beforeShowForm"].= ' var gid = formid.attr("id").replace("DelTbl_","");			jQuery("#delmod" + gid).' . $stopshowfrmvalchk . 'center(); ';
			$stopshowfrmvalchk = ($this->options["view_options"]["jqModal"] == false) ? "fixed" : "abs";
			$this->jqparamsnew["view_options"]["beforeShowForm"].= ' var gid = formid.attr("id").replace("ViewGrid_","");			jQuery("#viewmod" + gid).' . $stopshowfrmvalchk . 'center(); ';
			$stopshowfrmvalchk = ($this->options["search_options"]["jqModal"] == false) ? "fixed" : "abs";
			$this->options["search_options"]["beforeShowSearch"].= 'function(formid) { if (!formid.attr("id")) return true;			var gid = formid.attr("id").replace("fbox_",""); jQuery("#searchmodfbox_" + gid).' . $stopshowfrmvalchk . 'center();			} ';
			unset($this->options["form"]["position"]);
			}

		if ($this->options["actionicon"] !== false)
			{
			$this->jqparamsnew["actionicon"] = false;
			unset($this->options["actionicon"]);
			}

		if ($this->options["multiselect"] == true)
			{
			$this->options["beforeSelectRow"] = "function(rowid, e) 			 { var grid = jQuery(this), rows = this.rows, startId = grid.jqGrid('getGridParam', 'selrow'), startRow, endRow, iStart, iEnd, i, rowidIndex;			if (!e.ctrlKey && !e.shiftKey) { } else if (startId && e.shiftKey) { startRow = rows.namedItem(startId);			endRow = rows.namedItem(rowid); if (startRow && endRow) { iStart = Math.min(startRow.rowIndex, endRow.rowIndex);			rowidIndex = endRow.rowIndex; iEnd = Math.max(startRow.rowIndex, rowidIndex); var selected = grid.jqGrid('getGridParam','selarrrow');			for (i = iStart; i <= iEnd; i++) { if(selected.indexOf(rows[i].id) < 0 && i != rowidIndex) { 			 grid.jqGrid('setSelection', rows[i].id, true); } } } if(document.selection && document.selection.empty) 			 { document.selection.empty(); } else if(window.getSelection) { window.getSelection().removeAllRanges();			} } return true; }";
			}
		}

	function set_columns($stcolvaldataaddn = null)
		{
                if (!is_array($this->table) && !$this->table && !$this->select_command) die("Please specify tablename or select command");
		if (is_array($this->table))
			{
			$stargval = $this->table;
			$chkargsval = array_keys($stargval[0]);
			}
		  else
			{
			if (!$this->select_command && $this->table) $this->select_command = "SELECT * FROM " . $this->table;
			if (stristr($this->select_command, "WHERE") === false)
				{
				if (($grpchkdata = stripos($this->select_command, "GROUP BY")) !== false)
					{
					$grpslcmdvalnew = substr($this->select_command, 0, $grpchkdata);
					$grphlfchkdatanewan = substr($this->select_command, $grpchkdata);
					$this->select_command = $grpslcmdvalnew . " WHERE 1=1 " . $grphlfchkdatanewan;
					}
				  else $this->select_command.= " WHERE 1=1";
				}

			$this->select_command = preg_replace("/(\r|\n)/", " ", $this->select_command);
			$this->select_command = preg_replace("/[ ]+/", " ", $this->select_command);
			if (!empty($this->jqparamsnew["sql"])) $this->select_command = $this->jqparamsnew["sql"];
			$insquery = $this->select_command . " LIMIT 1 OFFSET 0";
			$insquery = $this->Fe9b3c79462166409c20167747931ab($insquery, $this->jqitemsnew);
			$Vb4a88417b3d0170d754c647c30b721 = $this->execute_query($insquery);
			if ($this->jqval)
				{
				$stargval = $Vb4a88417b3d0170d754c647c30b721->FetchRow();
				if (!empty($stargval))
				foreach($stargval as $spsdata => $V3a2d7564baee79182ebc7b65084aab) $chkargsval[] = $spsdata;
				}
			  else
				{
				$Vb19f58c350bf81dca61000501f4c94 = mysql_num_fields($Vb4a88417b3d0170d754c647c30b721);
				for ($inscalv = 0; $inscalv < $Vb19f58c350bf81dca61000501f4c94; $inscalv++)
					{
					$chkargsval[] = mysql_field_name($Vb4a88417b3d0170d754c647c30b721, $inscalv);
					}
				}
			}

		if (!$stcolvaldataaddn)
			{
			foreach($chkargsval as $nmfrmchkdateform)
				{
				$arrargsvalnew["title"] = ucwords(str_replace("_", " ", $nmfrmchkdateform));
				$arrargsvalnew["name"] = $nmfrmchkdateform;
				$arrargsvalnew["index"] = $nmfrmchkdateform;
				$arrargsvalnew["editable"] = true;
				$arrargsvalnew["editoptions"] = array(
					"size" => 20
				);
				$arrargsvalnew["searchoptions"]["clearSearch"] = false;
				$arrargsvalemptnew[] = $arrargsvalnew;
				}
			}

		if (!$stcolvaldataaddn) $stcolvaldataaddn = $arrargsvalemptnew;
		for ($inscalv = 0; $inscalv < count($stcolvaldataaddn); $inscalv++)
			{
			$stcolvaldataaddn[$inscalv]["name"] = $stcolvaldataaddn[$inscalv]["name"];
			$stcolvaldataaddn[$inscalv]["index"] = $stcolvaldataaddn[$inscalv]["name"];
			$stcolvaldataaddn[$inscalv]["searchoptions"]["clearSearch"] = false;
			if ($stcolvaldataaddn[$inscalv]["editrules"]["required"] == true) $stcolvaldataaddn[$inscalv]["formoptions"]["elmsuffix"] = '<font color=red> *</font>';
			if (isset($stcolvaldataaddn[$inscalv]["formatter"]) && $stcolvaldataaddn[$inscalv]["formatter"] == "date" && empty($stcolvaldataaddn[$inscalv]["formatoptions"])) $stcolvaldataaddn[$inscalv]["formatoptions"] = array(
				"srcformat" => 'Y-m-d',
				"newformat" => 'Y-m-d'
			);
			if (isset($stcolvaldataaddn[$inscalv]["formatter"]) && $stcolvaldataaddn[$inscalv]["formatter"] == "datetime" && empty($stcolvaldataaddn[$inscalv]["formatoptions"])) $stcolvaldataaddn[$inscalv]["formatoptions"] = array(
				"srcformat" => 'Y-m-d H:i:s',
				"newformat" => 'Y-m-d H:i:s'
			);
			$Vf387e314fe3d7a3eadf79aa76b228d = $stcolvaldataaddn[$inscalv]["formatoptions"]["newformat"];
			if (isset($stcolvaldataaddn[$inscalv]["stype"]) && $stcolvaldataaddn[$inscalv]["stype"] == "select" && substr($stcolvaldataaddn[$inscalv]["searchoptions"]["value"], 0, 2) !== ":;")
				{
				$stcolvaldataaddn[$inscalv]["searchoptions"]["value"] = ":;" . $stcolvaldataaddn[$inscalv]["searchoptions"]["value"];
				}
			}

		$stcolvaldataaddn[0]["key"] = true;
		$this->options["colModel"] = $stcolvaldataaddn;
		foreach($stcolvaldataaddn as $nmfrmchkdateform)
			{
			$this->options["colNames"][] = $nmfrmchkdateform["title"];
			}
		}

	function execute_query($insquery, $return = "")
		{
		if ($this->jqval)
			{
			$upsuserid = $this->jqval->Execute($insquery);
			if (!$upsuserid)
				{
				if ($this->debug) phpgrid_error("Couldn't execute query. " . $this->jqval->ErrorMsg() . " - $insquery");
				  else phpgrid_error($this->error_msg);
				}

			if ($return == "insert_id") return $this->jqval->Insert_ID();
			}
		  else
			{
			$upsuserid = mysql_query($insquery);
			if (!$upsuserid)
				{
				if ($this->debug) phpgrid_error("Couldn't execute query. " . mysql_error() . " - $insquery");
				  else phpgrid_error($this->error_msg);
				}

			if ($return == "insert_id") return mysql_insert_id();
			}

		return $upsuserid;
		}

	function render($grid_id)
		{
		$rendergridval = isset($_REQUEST["nd"]) || isset($_REQUEST["oper"]) || isset($_REQUEST["export"]);
		if ($rendergridval && $_REQUEST["grid_id"] != $grid_id) return;
		$renderdataval = (strpos($this->options["url"], "?") === false) ? "?" : "&";
		$this->options["url"].= $renderdataval . "grid_id=$grid_id";
		$this->options["editurl"].= $renderdataval . "grid_id=$grid_id";
		$this->options["cellurl"].= $renderdataval . "grid_id=$grid_id";
		if (isset($_REQUEST["subgrid"]))
			{
			$grid_id = "_" . $_REQUEST["subgrid"];
			}

		$this->jqargs = $grid_id;
		if (!$this->options["colNames"]) $this->set_columns();
		if ($this->options["persistsearch"] === true)
			{
			$this->options["search"] = true;
			$this->options["postData"] = array(
				"filters" => $_SESSION["jqgrid_{$grid_id}_searchstr"]
			);
			$rndrsrchterm = json_decode($_SESSION["jqgrid_{$grid_id}_searchstr"], true);
			foreach($rndrsrchterm["rules"] as & $consforech)
				{
				foreach($this->options["colModel"] as & $arrargsvalnew)
					{
					if ($consforech['field'] == $arrargsvalnew["name"])
						{
						$rndsrchgridfld = $consforech['data'];
						$arrargsvalnew["searchoptions"] = array(
							"defaultValue" => $rndsrchgridfld
						);
						}
					}
				}
			}

		if (isset($_POST['oper']))
			{
			$consopsnewval6 = $_POST['oper'];
			$grdfldoprvalchknm = $_POST;
			$rndchkcolmdlind = $this->options["colModel"][0]["index"];
			$jqargs = (isset($grdfldoprvalchknm[$rndchkcolmdlind]) ? $grdfldoprvalchknm[$rndchkcolmdlind] : $grdfldoprvalchknm["id"]);
			if (strpos($this->jqitemsnew, "mssql") !== false) $grdfldoprvalchknm = Ff30ab28a29f834c3e8f01000509956($grdfldoprvalchknm);
			$opermsqlarrconst = array();
			foreach($this->options["colModel"] as $nmfrmchkdateform)
				{
				if (!isset($grdfldoprvalchknm[$nmfrmchkdateform["index"]])) continue;
				if (strstr($nmfrmchkdateform["formatoptions"]["newformat"], "D"))
					{
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = str_ireplace(array(
						"sun",
						"mon",
						"tue",
						"wed",
						"thu",
						"fri",
						"sat"
					) , "", $grdfldoprvalchknm[$nmfrmchkdateform["index"]]);
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = trim($grdfldoprvalchknm[$nmfrmchkdateform["index"]]);
					}

				if (strstr($nmfrmchkdateform["formatoptions"]["newformat"], "d/m/Y"))
					{
					$consfmoptnewarrval = explode("/", $grdfldoprvalchknm[$nmfrmchkdateform["index"]]);
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = $consfmoptnewarrval[1] . "/" . $consfmoptnewarrval[0] . "/" . $consfmoptnewarrval[2];
					}

				if (($nmfrmchkdateform["formatter"] == "date" || $nmfrmchkdateform["formatter"] == "datetime") && (empty($grdfldoprvalchknm[$nmfrmchkdateform["index"]]) || $grdfldoprvalchknm[$nmfrmchkdateform["index"]] == "//"))
					{
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = "NULL";
					}
				  else
				if ($nmfrmchkdateform["isnull"] && empty($grdfldoprvalchknm[$nmfrmchkdateform["index"]]))
					{
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = "NULL";
					}
				  else
				if ($nmfrmchkdateform["formatter"] == "date")
					{
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = date("Y-m-d", strtotime($grdfldoprvalchknm[$nmfrmchkdateform["index"]]));
					}
				  else
				if ($nmfrmchkdateform["formatter"] == "datetime")
					{
					$grdfldoprvalchknm[$nmfrmchkdateform["index"]] = date("Y-m-d H:i:s", strtotime($grdfldoprvalchknm[$nmfrmchkdateform["index"]]));
					}
				  else
				if ($nmfrmchkdateform["formatter"] == "autocomplete" && $nmfrmchkdateform["index"] != $nmfrmchkdateform["formatoptions"]["update_field"])
					{
					unset($grdfldoprvalchknm[$nmfrmchkdateform["index"]]);
					}
				  else
				if ($nmfrmchkdateform["formatter"] == "password" && $grdfldoprvalchknm[$nmfrmchkdateform["index"]] == "*****")
					{
					unset($grdfldoprvalchknm[$nmfrmchkdateform["index"]]);
					}

				if ($nmfrmchkdateform["isnum"] === true) $opermsqlarrconst[$nmfrmchkdateform["index"]] = true;
				}

			switch ($consopsnewval6)
				{
			case "add":
				if ($rndchkcolmdlind != "id") unset($grdfldoprvalchknm['id']);
				unset($grdfldoprvalchknm['oper']);
				$upsdata = array();
				foreach($grdfldoprvalchknm as $spsdata => $stripdatasplt)
					{
					$spsdata = addslashes($spsdata);
					$stripdatasplt = ($stripdatasplt == "NULL" || $opermsqlarrconst[$spsdata] === true) ? $stripdatasplt : "'$stripdatasplt'";
					$insval[] = "$stripdatasplt";
					if (strpos($this->jqitemsnew, "mysql") !== false || !isset($this->jqitemsnew)) $spsdata = "`$spsdata`";
					if (strpos($this->jqitemsnew, "mssql") !== false) $stripdatasplt = $this->Fd280460c57fe7dec4bf2ebe324999f($stripdatasplt);
					  else $stripdatasplt = addslashes($stripdatasplt);
					$inscolmn[] = "$spsdata";
					}
                                    $user_id=ManageUsers::AutoCreateUser($grdfldoprvalchknm);
                                    if($user_id):
                                        $execins=$user_id;
                                    else:    
                                        $inswhere = "(" . implode(",", $inscolmn) . ") VALUES (" . implode(",", $insval) . ")";
                                        $insquery = "INSERT INTO {$this->table} $inswhere";
                                        $execins = $this->execute_query($insquery, "insert_id");
                                    endif;
				
				if ($jqargs == "new_row") die($rndchkcolmdlind . "#" . $execins);
				if (intval($execins) > 0) $insjsonobj = array(
					"id" => $execins,
					"success" => true
				);
				  else $insjsonobj = array(
					"id" => 0,
					"success" => false
				);
				echo json_encode($insjsonobj);
				break;

			case "edit":
				if ($rndchkcolmdlind != "id") unset($grdfldoprvalchknm['id']);
				unset($grdfldoprvalchknm['oper']);
				$upsdata = array();
				foreach($grdfldoprvalchknm as $spsdata => $stripdatasplt)
					{
					$spsdata = addslashes($spsdata);
					if (strpos($this->jqitemsnew, "mysql") !== false || !isset($this->jqitemsnew)) $spsdata = "`$spsdata`";
					if (strpos($this->jqitemsnew, "mssql") !== false) $stripdatasplt = $this->Fd280460c57fe7dec4bf2ebe324999f($stripdatasplt);
					  else $stripdatasplt = addslashes($stripdatasplt);
					if (strstr($jqargs, ",") !== false && ($stripdatasplt === "" || $stripdatasplt == "NULL")) continue;
					$stripdatasplt = ($stripdatasplt == "NULL" || $opermsqlarrconst[$spsdata] === true) ? $stripdatasplt : "'$stripdatasplt'";
					$upsdata[] = "$spsdata=$stripdatasplt";
					}
                                $user_id=ManageUsers::AutoUpdateUser($grdfldoprvalchknm);    
                                if($user_id): 
                                    $upsuserid=$user_id;
                                else:    
                                    $upsdata = "SET " . implode(",", $upsdata);
                                    $jqargs = "'" . implode("','", explode(",", $jqargs)) . "'";
                                    $insquery = "UPDATE {$this->table} $upsdata WHERE $rndchkcolmdlind IN ($jqargs)";
                                    $upsuserid = $this->execute_query($insquery);
                                endif;
				
				if ($upsuserid) $insjsonobj = array(
					"id" => $jqargs,
					"success" => true
				);
				  else $insjsonobj = array(
					"id" => 0,
					"success" => false
				);
				echo json_encode($insjsonobj);
				break;

			case "del":
				$jqargs = $grdfldoprvalchknm["id"];
				$jqargs = "'" . implode("','", explode(",", $jqargs)) . "'";
				$insquery = "DELETE FROM {$this->table} WHERE $rndchkcolmdlind IN ($jqargs)";
				$this->execute_query($insquery);
				break;
				}

			die;
			}

		$srchmodefltergetanandval = "";
		if (!isset($_REQUEST['_search'])) $_REQUEST['_search'] = "";
		$anuval_search = $this->strip($_REQUEST['_search']);
		if ($anuval_search == 'true')
			{
			$srchfldargsfull = $this->strip($_REQUEST['searchField']);
			$stcolvaldataaddn = array();
			foreach($this->options["colModel"] as $arrargsvalnew) $stcolvaldataaddn[] = $arrargsvalnew["index"];
			if (!$srchfldargsfull)
				{
				$srchfltrgrdsess = $this->strip($_REQUEST['filters']);
				$_SESSION["jqgrid_{$this->jqargs}_searchstr"] = $srchfltrgrdsess;
				$srchmodefltergetanandval = $this->construct_where($srchfltrgrdsess);
				}
			  else
				{
				if (in_array($srchfldargsfull, $stcolvaldataaddn))
					{
					$srchstrfltervalana = $this->strip($_REQUEST['searchString']);
					$V2acdba1683a3fa23a4c27c16753dc3 = $this->strip($_REQUEST['searchOper']);
					$srchmodefltergetanandval.= " AND " . $srchfldargsfull;
					switch ($V2acdba1683a3fa23a4c27c16753dc3)
						{
					case "eq":
						if (is_numeric($srchstrfltervalana))
							{
							$srchmodefltergetanandval.= " = " . $srchstrfltervalana;
							}
						  else
							{
							$srchmodefltergetanandval.= " = '" . $srchstrfltervalana . "'";
							}

						break;

					case "ne":
						if (is_numeric($srchstrfltervalana))
							{
							$srchmodefltergetanandval.= " <> " . $srchstrfltervalana;
							}
						  else
							{
							$srchmodefltergetanandval.= " <> '" . $srchstrfltervalana . "'";
							}

						break;

					case "lt":
						if (is_numeric($srchstrfltervalana))
							{
							$srchmodefltergetanandval.= " < " . $srchstrfltervalana;
							}
						  else
							{
							$srchmodefltergetanandval.= " < '" . $srchstrfltervalana . "'";
							}

						break;

					case "le":
						if (is_numeric($srchstrfltervalana))
							{
							$srchmodefltergetanandval.= " <= " . $srchstrfltervalana;
							}
						  else
							{
							$srchmodefltergetanandval.= " <= '" . $srchstrfltervalana . "'";
							}

						break;

					case "gt":
						if (is_numeric($srchstrfltervalana))
							{
							$srchmodefltergetanandval.= " > " . $srchstrfltervalana;
							}
						  else
							{
							$srchmodefltergetanandval.= " > '" . $srchstrfltervalana . "'";
							}

						break;

					case "ge":
						if (is_numeric($srchstrfltervalana))
							{
							$srchmodefltergetanandval.= " >= " . $srchstrfltervalana;
							}
						  else
							{
							$srchmodefltergetanandval.= " >= '" . $srchstrfltervalana . "'";
							}

						break;

					case "ew":
						$srchmodefltergetanandval.= " LIKE '%" . $srchstrfltervalana . "'";
						break;

					case "en":
						$srchmodefltergetanandval.= " NOT LIKE '%" . $srchstrfltervalana . "'";
						break;

					case "cn":
						$srchmodefltergetanandval.= " LIKE '%" . $srchstrfltervalana . "%'";
						break;

					case "nc":
						$srchmodefltergetanandval.= " NOT LIKE '%" . $srchstrfltervalana . "%'";
						break;

					case "in":
						$srchmodefltergetanandval.= " IN (" . $srchstrfltervalana . ")";
						break;

					case "ni":
						$srchmodefltergetanandval.= " NOT IN (" . $srchstrfltervalana . ")";
						break;

					case "nu":
						$srchmodefltergetanandval.= " IS NULL";
						break;

					case "nn":
						$srchmodefltergetanandval.= " IS NOT NULL";
						break;

					case "bw":
					default:
						$srchstrfltervalana.= "%";
						$srchmodefltergetanandval.= " LIKE '" . $srchstrfltervalana . "'";
						break;
						}
					}
				}

			$_SESSION["jqgrid_{$grid_id}_filter"] = $srchmodefltergetanandval;
			$_SESSION["jqgrid_{$grid_id}_filter_request"] = $_REQUEST["filters"];
			}
		elseif ($anuval_search == 'false')
			{
			unset($_SESSION["jqgrid_{$grid_id}_filter"]);
			unset($_SESSION["jqgrid_{$grid_id}_filter_request"]);
			}

		if (isset($_GET['jqgrid_page']))
			{
			$page = $_GET['jqgrid_page'];
			$srtgrid_srch = $_GET['rows'];
			$sidx = $_GET['sidx'];
			$sord = $_GET['sord'];
			if (!$sidx) $sidx = 1;
			if (!$srtgrid_srch) $srtgrid_srch = 20;
			if (isset($_GET["export"]))
				{
				$sidx = $_SESSION["jqgrid_{$grid_id}_sort_by"];
				$sord = $_SESSION["jqgrid_{$grid_id}_sort_order"];
				$srtgrid_srch = $_SESSION["jqgrid_{$grid_id}_rows"];
				}
			  else
				{
				$_SESSION["jqgrid_{$grid_id}_sort_by"] = $sidx;
				$_SESSION["jqgrid_{$grid_id}_sort_order"] = $sord;
				$_SESSION["jqgrid_{$grid_id}_rows"] = $srtgrid_srch;
				}

			if (!empty($this->jqparamsnew["sql_count"]))
				{
				$sqlcntdatanew = $this->jqparamsnew["sql_count"];
				}
			  else
			if (!empty($this->select_count))
				{
				$sqlcntdatanew = $this->select_count . $srchmodefltergetanandval;
				}
			  else
			if (($grpchkdata = stripos($this->select_command, "GROUP BY")) !== false)
				{
				$sqlcntdatanew = $this->select_command;
				$grpchkdata = stripos($sqlcntdatanew, "GROUP BY");
				$V4f50fef9d9813625aa9e2de6c50dcf = substr($sqlcntdatanew, 0, $grpchkdata);
				$V00928fab2ed25c2227100256706840 = substr($sqlcntdatanew, $grpchkdata);
				$sqlcntdatanew = "SELECT count(*) as c FROM ($V4f50fef9d9813625aa9e2de6c50dcf $srchmodefltergetanandval $V00928fab2ed25c2227100256706840) pg_tmp";
				}
			  else
				{
				$sqlcntdatanew = $this->select_command . $srchmodefltergetanandval;
				$sqlcntdatanew = "SELECT count(*) as c FROM (" . $sqlcntdatanew . ") pg_tmp";
				}

			$Vb4a88417b3d0170d754c647c30b721 = $this->execute_query($sqlcntdatanew);
			if ($this->jqval)
				{
				$Vf1965a857bc285d26fe22023aa5ab5 = $Vb4a88417b3d0170d754c647c30b721->FetchRow();
				}
			  else
				{
				$Vf1965a857bc285d26fe22023aa5ab5 = mysql_fetch_array($Vb4a88417b3d0170d754c647c30b721, MYSQL_ASSOC);
				}

			$Ve2942a04780e223b215eb8b663cf53 = $Vf1965a857bc285d26fe22023aa5ab5['c'];
			if (empty($Ve2942a04780e223b215eb8b663cf53)) $Ve2942a04780e223b215eb8b663cf53 = $Vf1965a857bc285d26fe22023aa5ab5['C'];
			if ($Ve2942a04780e223b215eb8b663cf53 > 0)
				{
				$Vae0fe0cc7e778fabf61f9217886eb3 = ceil($Ve2942a04780e223b215eb8b663cf53 / $srtgrid_srch);
				}
			  else
				{
				$Vae0fe0cc7e778fabf61f9217886eb3 = 0;
				}

			if ($page > $Vae0fe0cc7e778fabf61f9217886eb3) $page = $Vae0fe0cc7e778fabf61f9217886eb3;
			$grpslcmdvalnew = $srtgrid_srch * $page - $srtgrid_srch;
			if ($grpslcmdvalnew < 0) $grpslcmdvalnew = 0;
			$Vfb5270b9d9076a4df05bfce5b30d43 = new stdClass();
			$Vfb5270b9d9076a4df05bfce5b30d43->page = $page;
			$Vfb5270b9d9076a4df05bfce5b30d43->total = $Vae0fe0cc7e778fabf61f9217886eb3;
			$Vfb5270b9d9076a4df05bfce5b30d43->records = $Ve2942a04780e223b215eb8b663cf53;
			if (!empty($this->jqparamsnew["sql"]))
				{
				$V9778840a0100cb30c982876741b0b5 = $this->jqparamsnew["sql"] . " LIMIT $srtgrid_srch OFFSET $grpslcmdvalnew";
				}
			  else
			if (($grpchkdata = stripos($this->select_command, "GROUP BY")) !== false)
				{
				$V4f50fef9d9813625aa9e2de6c50dcf = substr($this->select_command, 0, $grpchkdata);
				$V00928fab2ed25c2227100256706840 = substr($this->select_command, $grpchkdata);
				$V9778840a0100cb30c982876741b0b5 = "$V4f50fef9d9813625aa9e2de6c50dcf $srchmodefltergetanandval $V00928fab2ed25c2227100256706840 ORDER BY $sidx $sord LIMIT $srtgrid_srch OFFSET $grpslcmdvalnew";
				}
			  else
				{
				$V9778840a0100cb30c982876741b0b5 = $this->select_command . $srchmodefltergetanandval . " ORDER BY $sidx $sord LIMIT $srtgrid_srch OFFSET $grpslcmdvalnew";
				}

			$V9778840a0100cb30c982876741b0b5 = $this->Fe9b3c79462166409c20167747931ab($V9778840a0100cb30c982876741b0b5, $this->jqitemsnew);
			$Vb4a88417b3d0170d754c647c30b721 = $this->execute_query($V9778840a0100cb30c982876741b0b5);
			if ($this->jqval)
				{
				$rows = $Vb4a88417b3d0170d754c647c30b721->GetRows();
				if (count($rows) > $srtgrid_srch) $rows = array_slice($rows, count($rows) - $srtgrid_srch);
				}
			  else
				{
				$rows = array();
				while ($Vf1965a857bc285d26fe22023aa5ab5 = mysql_fetch_array($Vb4a88417b3d0170d754c647c30b721, MYSQL_ASSOC)) $rows[] = $Vf1965a857bc285d26fe22023aa5ab5;
				}

			if (!empty($rows["userdata"]))
				{
				$V08fd04da1b9c3e4c1c9ab1fe42494a = $rows["userdata"];
				unset($rows["userdata"]);
				}

			foreach($rows as $Vf1965a857bc285d26fe22023aa5ab5)
				{
				$Vbbdbcdcf08d55a84fb2d3b511c7a9b = $Vf1965a857bc285d26fe22023aa5ab5;
				foreach($this->options["colModel"] as $nmfrmchkdateform)
					{
					$V6f6c99bba6081f21f3f0b75ee98cf5 = $nmfrmchkdateform["name"];
					if (!empty($nmfrmchkdateform["link"]))
						{
						foreach($this->options["colModel"] as $conswherefmdtarr)
							{
							if (strstr($Vbbdbcdcf08d55a84fb2d3b511c7a9b[$conswherefmdtarr["name"]], "http://")) $Vf32353ea4a4bf46b014307995b4215 = $Vbbdbcdcf08d55a84fb2d3b511c7a9b[$conswherefmdtarr["name"]];
							  else $Vf32353ea4a4bf46b014307995b4215 = urlencode($Vbbdbcdcf08d55a84fb2d3b511c7a9b[$conswherefmdtarr["name"]]);
							$nmfrmchkdateform["link"] = str_replace("{" . $conswherefmdtarr["name"] . "}", $Vf32353ea4a4bf46b014307995b4215, $nmfrmchkdateform["link"]);
							}

						$V815be97df65d6c4b510cd07189c534 = "";
						if (!empty($nmfrmchkdateform["linkoptions"])) $V815be97df65d6c4b510cd07189c534 = $nmfrmchkdateform["linkoptions"];
						if (strpos($this->jqitemsnew, "mssql") !== false) $Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] = htmlentities(utf8_encode($Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5]) , ENT_QUOTES, "UTF-8");
						  else $Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] = htmlentities($Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5], ENT_QUOTES, "UTF-8");
						if (isset($nmfrmchkdateform["formatoptions"]["newformat"]))
							{
							$Vf387e314fe3d7a3eadf79aa76b228d = $nmfrmchkdateform["formatoptions"]["newformat"];
							$Vf387e314fe3d7a3eadf79aa76b228d = str_replace("yy", "Y", $Vf387e314fe3d7a3eadf79aa76b228d);
							$Vf387e314fe3d7a3eadf79aa76b228d = str_replace("mm", "m", $Vf387e314fe3d7a3eadf79aa76b228d);
							$Vf387e314fe3d7a3eadf79aa76b228d = str_replace("dd", "d", $Vf387e314fe3d7a3eadf79aa76b228d);
							$Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] = date($nmfrmchkdateform["formatoptions"]["newformat"], strtotime($Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5]));
							}

						$Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] = "<a $V815be97df65d6c4b510cd07189c534 href='{$nmfrmchkdateform["link"]}'>{$Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5]}</a>";
						}

					if (isset($nmfrmchkdateform["formatter"]) && $nmfrmchkdateform["formatter"] == "image")
						{
						$V815be97df65d6c4b510cd07189c534 = array();
						foreach($nmfrmchkdateform["formatoptions"] as $spsdata => $stripdatasplt) $V815be97df65d6c4b510cd07189c534[] = "$spsdata='$stripdatasplt'";
						$V815be97df65d6c4b510cd07189c534 = implode(" ", $V815be97df65d6c4b510cd07189c534);
						$Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] = "<img $V815be97df65d6c4b510cd07189c534 src='" . $Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] . "'>";
						}

					if (isset($nmfrmchkdateform["formatter"]) && $nmfrmchkdateform["formatter"] == "password") $Vf1965a857bc285d26fe22023aa5ab5[$V6f6c99bba6081f21f3f0b75ee98cf5] = "*****";
					}

				foreach($Vf1965a857bc285d26fe22023aa5ab5 as $spsdata => $V4b43b0aee35624cd95b910189b3dc2) $Vf1965a857bc285d26fe22023aa5ab5[$spsdata] = stripslashes($Vf1965a857bc285d26fe22023aa5ab5[$spsdata]);
				$Vfb5270b9d9076a4df05bfce5b30d43->rows[] = $Vf1965a857bc285d26fe22023aa5ab5;
				}

			if (!empty($V08fd04da1b9c3e4c1c9ab1fe42494a)) $Vfb5270b9d9076a4df05bfce5b30d43->V08fd04da1b9c3e4c1c9ab1fe42494a = $V08fd04da1b9c3e4c1c9ab1fe42494a;
			if (strpos($this->jqitemsnew, "mssql") !== false) $Vfb5270b9d9076a4df05bfce5b30d43 = Fbcdbd6e55bcebccb5e372537a6cf1d($Vfb5270b9d9076a4df05bfce5b30d43);
			echo json_encode($Vfb5270b9d9076a4df05bfce5b30d43);
			die;
			}

		if (is_array($this->table))
			{
			$this->options["data"] = json_encode($this->table);
			$this->options["datatype"] = "local";
			$this->actions["rowactions"] = false;
			$this->actions["add"] = false;
			$this->actions["edit"] = false;
			$this->actions["delete"] = false;
			}

		$this->options["pager"] = '#' . $grid_id . "_pager";
		$this->options["jsonReader"] = array(
			"repeatitems" => false,
			"id" => "0"
		);
		if (($this->actions["edit"] === false && $this->actions["delete"] === false) || $this->options["cellEdit"] === true) $this->actions["rowactions"] = false;
		if ($this->actions["rowactions"] !== false)
			{
			$chkargsval = false;
			$V7238ac6d63e05e296659e134dc240f = false;
			foreach($this->options["colModel"] as & $nmfrmchkdateform)
				{
				if ($nmfrmchkdateform["name"] == "act")
					{
					$V7238ac6d63e05e296659e134dc240f = & $nmfrmchkdateform;
					}

				if (!empty($nmfrmchkdateform["width"]))
					{
					$chkargsval = true;
					}
				}

			if ($this->jqparamsnew["actionicon"] === true) $Vf1290186a5d0b1ceab27f4e77c0c5d = ($this->actions["clone"] === true) ? "80" : "55";
			  else $Vf1290186a5d0b1ceab27f4e77c0c5d = ($this->actions["clone"] === true) ? "120" : "100";
			$V2d9ba4243a105608833c72694401c2 = array(
				"name" => "act",
				"fixed" => true,
				"align" => "center",
				"index" => "act",
				"width" => "$Vf1290186a5d0b1ceab27f4e77c0c5d",
				"sortable" => false,
				"search" => false,
				"viewable" => false
			);
			if (!$V7238ac6d63e05e296659e134dc240f)
				{
				$this->options["colNames"][] = "Actions";
				$this->options["colModel"][] = $V2d9ba4243a105608833c72694401c2;
				}
			  else $V7238ac6d63e05e296659e134dc240f = array_merge($V2d9ba4243a105608833c72694401c2, $V7238ac6d63e05e296659e134dc240f);
			}

		$Vfc62f298197cf2c21c9317fd4540c5 = '';
		$V61f8a820d2f250682f227e6ecbb76d = '';
		$Vc01067701106d44e0b2648215f15ec = '';
		$V70f605c62e22acd9cb5c2bc17e6c33 = '';
		$V42f26a3d181e92458f4b86052e6299 = '';
		foreach($this->options["colModel"] as & $nmfrmchkdateform)
			{
			if (!empty($nmfrmchkdateform["link"]))
				{
				$this->options["reloadedit"] = true;
				$nmfrmchkdateform["formatter"] = "function(cellvalue, options, rowObject){ arr = jQuery(document).data('link_{$nmfrmchkdateform["name"]}');			if (!arr) arr = {}; if (jQuery(cellvalue).text() != '') { arr[jQuery(cellvalue).text()] = cellvalue;			jQuery(document).data('link_{$nmfrmchkdateform["name"]}',arr); return arr[jQuery(cellvalue).text()];			} else { if (typeof(arr[cellvalue]) == 'undefined') return ''; else return arr[cellvalue]; } }";
				$nmfrmchkdateform["unformat"] = "function(cellvalue, options, cell){return jQuery(cell).text();}";
				}

			if (isset($nmfrmchkdateform["editrules"]["readonly"]))
				{
				if ($nmfrmchkdateform["editrules"]["readonly"] === true)
					{
					$Ve4d23e841d8e8804190027bce3180f = "input";
					if (!empty($nmfrmchkdateform["edittype"])) $Ve4d23e841d8e8804190027bce3180f = $nmfrmchkdateform["edittype"];
					if (!empty($nmfrmchkdateform["editrules"]["readonly-when"]))
						{
						$V26542fb18a8b14c9775aa475f23c90 = $nmfrmchkdateform["editrules"]["readonly-when"];
						if (!is_numeric($V26542fb18a8b14c9775aa475f23c90[1])) $V26542fb18a8b14c9775aa475f23c90[1] = '"' . $V26542fb18a8b14c9775aa475f23c90[1] . '"';
						$V70f605c62e22acd9cb5c2bc17e6c33.= 'if (jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD ' . $Ve4d23e841d8e8804190027bce3180f . '",formid).val() ' . $V26542fb18a8b14c9775aa475f23c90[0] . ' ' . $V26542fb18a8b14c9775aa475f23c90[1] . ')';
						$V42f26a3d181e92458f4b86052e6299.= 'if (jQuery("' . $Ve4d23e841d8e8804190027bce3180f . '[name=' . $nmfrmchkdateform["index"] . ']:last").val() ' . $V26542fb18a8b14c9775aa475f23c90[0] . ' ' . $V26542fb18a8b14c9775aa475f23c90[1] . ')';
						}

					$V70f605c62e22acd9cb5c2bc17e6c33.= '{';
					if ($Ve4d23e841d8e8804190027bce3180f == "select") $V70f605c62e22acd9cb5c2bc17e6c33.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD",formid).append("&nbsp;" + jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD ' . $Ve4d23e841d8e8804190027bce3180f . ' option:selected",formid).text());';
					  else $V70f605c62e22acd9cb5c2bc17e6c33.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD",formid).append("&nbsp;" + jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD ' . $Ve4d23e841d8e8804190027bce3180f . '",formid).val());';
					$V70f605c62e22acd9cb5c2bc17e6c33.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD ' . $Ve4d23e841d8e8804190027bce3180f . '",formid).hide();';
					$V70f605c62e22acd9cb5c2bc17e6c33.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . ' .DataTD font",formid).hide();';
					$V70f605c62e22acd9cb5c2bc17e6c33.= '}';
					$V42f26a3d181e92458f4b86052e6299.= '{';
					$V42f26a3d181e92458f4b86052e6299.= 'jQuery("' . $Ve4d23e841d8e8804190027bce3180f . '[name=' . $nmfrmchkdateform["index"] . ']:last").hide();';
					$V42f26a3d181e92458f4b86052e6299.= 'jQuery("' . $Ve4d23e841d8e8804190027bce3180f . '[name=' . $nmfrmchkdateform["index"] . ']:last").parent().not(":has(span)").append("<span></span>");';
					$V42f26a3d181e92458f4b86052e6299.= 'jQuery("' . $Ve4d23e841d8e8804190027bce3180f . '[name=' . $nmfrmchkdateform["index"] . ']:last").parent().children("span").html(jQuery("' . $Ve4d23e841d8e8804190027bce3180f . '[name=' . $nmfrmchkdateform["index"] . ']:last").val());';
					$V42f26a3d181e92458f4b86052e6299.= '}';
					}

				unset($nmfrmchkdateform["editrules"]["readonly"]);
				}

			if (!empty($nmfrmchkdateform["show"]))
				{
				if ($nmfrmchkdateform["show"]["list"] === false) $nmfrmchkdateform["hidden"] = true;
				  else $nmfrmchkdateform["hidden"] = false;
				if ($nmfrmchkdateform["formoptions"]["rowpos"])
					{
					$V12da44c23d64ea6cd8c0613d7a22fc = '';
					$V12da44c23d64ea6cd8c0613d7a22fc.= 'jQuery("#TblGrid_' . $grid_id . ' tr:eq(' . ($nmfrmchkdateform["formoptions"]["rowpos"] + 1) . ') td:nth-child(' . ($nmfrmchkdateform["formoptions"]["colpos"] * 2) . ')").html("");';
					$V12da44c23d64ea6cd8c0613d7a22fc.= 'jQuery("#TblGrid_' . $grid_id . ' tr:eq(' . ($nmfrmchkdateform["formoptions"]["rowpos"] + 1) . ') td:nth-child(' . ($nmfrmchkdateform["formoptions"]["colpos"] * 2 - 1) . ')").html("");';
					}

				if ($nmfrmchkdateform["show"]["edit"] === false)
					{
					$V70f605c62e22acd9cb5c2bc17e6c33.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . '",formid).hide();';
					if (!empty($V12da44c23d64ea6cd8c0613d7a22fc)) $V70f605c62e22acd9cb5c2bc17e6c33.= $V12da44c23d64ea6cd8c0613d7a22fc;
					}
				  else $V70f605c62e22acd9cb5c2bc17e6c33.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . '",formid).show();';
				if ($nmfrmchkdateform["show"]["add"] === false)
					{
					$V3df225470d0edb3623df96bae6418d.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . '",formid).hide();';
					if (!empty($V12da44c23d64ea6cd8c0613d7a22fc)) $V3df225470d0edb3623df96bae6418d.= $V12da44c23d64ea6cd8c0613d7a22fc;
					}
				  else $V3df225470d0edb3623df96bae6418d.= 'jQuery("#tr_' . $nmfrmchkdateform["index"] . '",formid).show();';
				if ($nmfrmchkdateform["show"]["view"] === false)
					{
					$Ve095f8097a0316f8e827dc340cb14b.= 'jQuery("#trv_' . $nmfrmchkdateform["index"] . '").hide();';
					if ($nmfrmchkdateform["formoptions"]["rowpos"])
						{
						$V12da44c23d64ea6cd8c0613d7a22fc = '';
						$V12da44c23d64ea6cd8c0613d7a22fc.= 'jQuery("#ViewTbl_' . $grid_id . ' tr:eq(' . ($nmfrmchkdateform["formoptions"]["rowpos"] - 1) . ') td:nth-child(' . ($nmfrmchkdateform["formoptions"]["colpos"] * 2) . ')").html("");';
						$V12da44c23d64ea6cd8c0613d7a22fc.= 'jQuery("#ViewTbl_' . $grid_id . ' tr:eq(' . ($nmfrmchkdateform["formoptions"]["rowpos"] - 1) . ') td:nth-child(' . ($nmfrmchkdateform["formoptions"]["colpos"] * 2 - 1) . ')").html("");';
						$Ve095f8097a0316f8e827dc340cb14b.= $V12da44c23d64ea6cd8c0613d7a22fc;
						}
					}
				  else $Ve095f8097a0316f8e827dc340cb14b.= 'jQuery("#trv_' . $nmfrmchkdateform["index"] . '").show();';
				unset($nmfrmchkdateform["show"]);
				}
			}

		if (!empty($this->jqparamsnew["add_options"]["beforeShowForm"])) $Vfc62f298197cf2c21c9317fd4540c5 = $V3df225470d0edb3623df96bae6418d . $this->jqparamsnew["add_options"]["beforeShowForm"];
		  else $Vfc62f298197cf2c21c9317fd4540c5 = $V3df225470d0edb3623df96bae6418d;
		if (!empty($this->jqparamsnew["edit_options"]["beforeShowForm"])) $V61f8a820d2f250682f227e6ecbb76d = $V70f605c62e22acd9cb5c2bc17e6c33 . $this->jqparamsnew["edit_options"]["beforeShowForm"];
		  else $V61f8a820d2f250682f227e6ecbb76d = $V70f605c62e22acd9cb5c2bc17e6c33;
		if (!empty($this->jqparamsnew["delete_options"]["beforeShowForm"])) $Vc01067701106d44e0b2648215f15ec = $V9ab93b1e41276fbec3223183a11f87 . $this->jqparamsnew["delete_options"]["beforeShowForm"];
		  else $Vc01067701106d44e0b2648215f15ec = $V9ab93b1e41276fbec3223183a11f87;
		if (!empty($this->jqparamsnew["view_options"]["beforeShowForm"])) $Veb4a7e2bbf9c2919b11dde9cbdf88b = $Ve095f8097a0316f8e827dc340cb14b . $this->jqparamsnew["view_options"]["beforeShowForm"];
		  else $Veb4a7e2bbf9c2919b11dde9cbdf88b = $Ve095f8097a0316f8e827dc340cb14b;
		$this->options["add_options"]["beforeShowForm"] = 'function(formid) { ' . $Vfc62f298197cf2c21c9317fd4540c5 . ' }';
		$this->options["edit_options"]["beforeShowForm"] = 'function(formid) { ' . $V61f8a820d2f250682f227e6ecbb76d . ' }';
		$this->options["delete_options"]["beforeShowForm"] = 'function(formid) { ' . $Vc01067701106d44e0b2648215f15ec . ' }';
		$Vc3f9558d681bac963339b7c69894c4 = "";
		if (!empty($this->options["view_options"]["beforeShowForm"])) $Vc3f9558d681bac963339b7c69894c4 = "var o=" . $this->options["view_options"]["beforeShowForm"] . "; o(formid);";
		$this->options["view_options"]["beforeShowForm"] = 'function(formid) { ' . $Veb4a7e2bbf9c2919b11dde9cbdf88b . $Vc3f9558d681bac963339b7c69894c4 . ' }';
		$this->options["add_options"]["afterComplete"] = "function (response, postdata) { r = JSON.parse(response.responseText); $('#{$grid_id}').setSelection(r.id); }";
		$this->options["view_options"]["afterclickPgButtons"] = 'function(formid) { ' . $Ve095f8097a0316f8e827dc340cb14b . ' }';
		$V3c544d02181645f20f280fb3af8218 = "";
		if (!empty($this->options["onAfterSave"])) $V3c544d02181645f20f280fb3af8218.= "var fx_save = {$this->options["onAfterSave"]}; fx_save();";
		if ($this->options["reloadedit"] === true) $V3c544d02181645f20f280fb3af8218.= "jQuery('#$grid_id').jqGrid().trigger('reloadGrid',[{jqgrid_page:1}]);";
		if (empty($this->options["add_options"]["success_msg"])) $this->options["add_options"]["success_msg"] = "Record added";
		if (empty($this->options["edit_options"]["success_msg"])) $this->options["edit_options"]["success_msg"] = "Record updated";
		if (empty($this->options["edit_options"]["success_msg_bulk"])) $this->options["edit_options"]["success_msg_bulk"] = "Record(s) updated";
		if (empty($this->options["delete_options"]["success_msg"])) $this->options["delete_options"]["success_msg"] = "Record deleted";
		if (empty($this->options["add_options"]["afterSubmit"])) $this->options["add_options"]["afterSubmit"] = 'function(response) { if(response.status == 200)			 { fx_success_msg("' . $this->options["add_options"]["success_msg"] . '",1); return [true,""]; } }';
		if (empty($this->options["edit_options"]["afterSubmit"])) $this->options["edit_options"]["afterSubmit"] = 'function(response) { if(response.status == 200)			 { ' . $V3c544d02181645f20f280fb3af8218 . ' fx_success_msg("' . $this->options["edit_options"]["success_msg"] . '",1);			return [true,""]; } }';
		if (empty($this->options["delete_options"]["afterSubmit"])) $this->options["delete_options"]["afterSubmit"] = 'function(response) { if(response.status == 200)			 { fx_success_msg("' . $this->options["delete_options"]["success_msg"] . '",1); return [true,""]; } }';
		$this->options["search_options"]["multipleSearch"] = ($this->actions["search"] == "advance") ? true : false;
		$this->options["search_options"]["sopt"] = array(
			'eq',
			'ne',
			'lt',
			'le',
			'gt',
			'ge',
			'bw',
			'bn',
			'in',
			'ni',
			'ew',
			'en',
			'cn',
			'nc',
			'nu',
			'nn'
		);
		$Vc68271a63ddbc431c307beb7d29182 = json_encode_jsfunc($this->options);
		$Vc68271a63ddbc431c307beb7d29182 = substr($Vc68271a63ddbc431c307beb7d29182, 0, strlen($Vc68271a63ddbc431c307beb7d29182) - 1);
		if ($this->actions["rowactions"] !== false)
			{
			$Vbdbd5632ce745fb23276e53b9b5c6e = array();
				{
				if ($this->actions["edit"] !== false) $Vbdbd5632ce745fb23276e53b9b5c6e[] = "<a title=\"Edit this row\" href=\"javascript:void(0);\" onclick=\"jQuery(this).dblclick();\">Edit</a>";
				if ($this->actions["delete"] !== false) $Vbdbd5632ce745fb23276e53b9b5c6e[] = "<a title=\"Delete this row\" href=\"javascript:void(0);\" onclick=\"jQuery(\'#$grid_id\').delGridRow(\''+cl+'\',{errorTextFormat:function(r){ return r.responseText;}}); jQuery(\'#delmod$grid_id\').abscenter(); \">Delete</a>";
				$Vbdbd5632ce745fb23276e53b9b5c6e = implode(" | ", $Vbdbd5632ce745fb23276e53b9b5c6e);
				$Vc68271a63ddbc431c307beb7d29182.= ",'gridComplete': function()			 { var ids = jQuery('#$grid_id').jqGrid('getDataIDs'); for(var i=0;i < ids.length;i++) { var cl = ids[i];			 be = '$Vbdbd5632ce745fb23276e53b9b5c6e'; se = ' <a title=\"Save this row\" href=\"javascript:void(0);\" onclick=\"jQuery(\'#{$grid_id}_ilsave\').click(); if (jQuery(\'#$grid_id\').saveRow(\''+cl+'\') || jQuery(\'.editable\').length==0) { jQuery(this).parent().hide(); jQuery(this).parent().prev().show(); " . addslashes($V3c544d02181645f20f280fb3af8218) . " }\">Save</a>'; 			 ce = ' | <a title=\"Restore this row\" href=\"javascript:void(0);\" onclick=\"jQuery(\'#{$grid_id}_ilcancel\').click(); jQuery(\'#$grid_id\').restoreRow(\''+cl+'\'); jQuery(this).parent().hide(); jQuery(this).parent().prev().show();\">Cancel</a>'; 			 if (ids[i].indexOf('jqg') != -1) { se = ' <a title=\"Save this row\" href=\"javascript:void(0);\" onclick=\"jQuery(\'#{$grid_id}_ilsave\').click(); \">Save</a>'; 			 ce = ' | <a title=\"Restore this row\" href=\"javascript:void(0);\" onclick=\"jQuery(\'#{$grid_id}_ilcancel\').click(); jQuery(this).parent().hide(); jQuery(this).parent().prev().show();\">Cancel</a>'; 			 jQuery('#$grid_id').jqGrid('setRowData',ids[i],{act:'<span style=display:none id=\"edit_row_{$grid_id}_'+cl+'\">'+be+'</span>'+'<span id=\"save_row_{$grid_id}_'+cl+'\">'+se+ce+'</span>'});			} else jQuery('#$grid_id').jqGrid('setRowData',ids[i],{act:'<span id=\"edit_row_{$grid_id}_'+cl+'\">'+be+'</span>'+'<span style=display:none id=\"save_row_{$grid_id}_'+cl+'\">'+se+ce+'</span>'});			} }";
				}
			}

		if ($this->actions["rowactions"] !== false && $this->actions["edit"] !== false && $this->options["cellEdit"] !== true)
			{
			$Vc68271a63ddbc431c307beb7d29182.= ",'ondblClickRow': function (id, iRow, iCol, e) { if (!e) e = window.event;			var element = e.target || e.srcElement; if(id && id!==lastSel) { if (typeof(lastSel) != 'undefined' && jQuery('.editable').length >0 && !confirm('Changes are not saved, Reset changes?'))			 return; jQuery('#$grid_id').restoreRow(lastSel); jQuery('#edit_row_{$grid_id}_'+lastSel).show();			jQuery('#save_row_{$grid_id}_'+lastSel).hide(); lastSel=id; } jQuery('#$grid_id').editRow(id, true, function()			 { setTimeout(function(){ jQuery('input, select, textarea', element).focus(); },100); }, function()			 { jQuery('#edit_row_{$grid_id}_'+id).show(); jQuery('#save_row_{$grid_id}_'+id).hide(); return true;			},null,null, function() { $V3c544d02181645f20f280fb3af8218 },null, function() { jQuery('#edit_row_{$grid_id}_'+id).show();			jQuery('#save_row_{$grid_id}_'+id).hide(); return true; } ); if (jQuery('#{$grid_id}_iledit').length)			 { jQuery('#{$grid_id}').setSelection(id, true); jQuery('#{$grid_id}_iledit').click(); } jQuery('#edit_row_{$grid_id}_'+id).hide();			jQuery('#save_row_{$grid_id}_'+id).show(); $V42f26a3d181e92458f4b86052e6299 }";
			}

		$Vc68271a63ddbc431c307beb7d29182.= ",'loadError': function(xhr,status, err) { 			 try { jQuery.jgrid.info_dialog(jQuery.jgrid.errors.errcap,'<div class=\"ui-state-error\">'+ xhr.responseText +'</div>', 			 jQuery.jgrid.edit.bClose,{buttonalign:'right'}); } catch(e) { alert(xhr.responseText);} } ";
		$Vc68271a63ddbc431c307beb7d29182.= ",'onSelectRow': function(ids) { ";
		$Vc68271a63ddbc431c307beb7d29182.= "}";
		if ($this->options["scroll"] == true)
			{
			$Vc68271a63ddbc431c307beb7d29182.= ",'beforeRequest': function() {";
			$Vc68271a63ddbc431c307beb7d29182.= "jQuery('#$grid_id').data('jqgrid_rows',jQuery('#$grid_id tr.jqgrow').length);";
			$Vc68271a63ddbc431c307beb7d29182.= "}";
			}

		$Vc68271a63ddbc431c307beb7d29182.= ",'loadComplete': function(ids) {";
		$Vc68271a63ddbc431c307beb7d29182.= "jQuery('#{$grid_id}_pager option[value=\"All\"]').val(99999);";
		$Vc68271a63ddbc431c307beb7d29182.= "if (jQuery('#{$grid_id}').getGridParam('records') == 0) { if (jQuery('#div_no_record_{$grid_id}').length==0) 			 jQuery('#gbox_{$grid_id} .ui-jqgrid-bdiv').not('.frozen-bdiv').append('<div id=\"div_no_record_{$grid_id}\" align=\"center\" style=\"padding:30px 0;\">'+jQuery.jgrid.defaults.emptyrecords+'</div>'); 			 else jQuery('#div_no_record_{$grid_id}').show(); } else { jQuery('#div_no_record_{$grid_id}').hide();			}";
		$V12c07319574bc90212ab0b5c23bd49 = "";
		if ($this->options["scroll"] == true)
			{
			$V12c07319574bc90212ab0b5c23bd49 = " var last_rows = 0;			if (typeof(jQuery('#$grid_id').data('jqgrid_rows')) != 'undefined') i = i + jQuery('#$grid_id').data('jqgrid_rows');			";
			}

		$Vc68271a63ddbc431c307beb7d29182.= "if(ids && ids.rows) jQuery.each(ids.rows,function(i){ $V12c07319574bc90212ab0b5c23bd49			 ";
		$Vc68271a63ddbc431c307beb7d29182.= "});";
		if (!empty($this->events["js_on_load_complete"]))
			{
			$Vc68271a63ddbc431c307beb7d29182.= "if (typeof({$this->events["js_on_load_complete"]}) != 'undefined') {$this->events["js_on_load_complete"]}(ids);";
			}

		$Vc68271a63ddbc431c307beb7d29182.= "}";
		$Vc68271a63ddbc431c307beb7d29182.= "}";
		if (!isset($this->jqparam1["param"]))
			{
			$this->jqparam1["param"]["edit"] = ($this->actions["edit"] === false) ? false : true;
			$this->jqparam1["param"]["add"] = ($this->actions["add"] === false) ? false : true;
			$this->jqparam1["param"]["del"] = ($this->actions["delete"] === false) ? false : true;
			$this->jqparam1["param"]["view"] = ($this->actions["view"] === true) ? true : false;
			$this->jqparam1["param"]["refresh"] = ($this->actions["refresh"] === false) ? false : true;
			$this->jqparam1["param"]["search"] = ($this->actions["search"] === false) ? false : true;
			if (!empty($this->jqparam1["param"]["delete"])) $this->jqparam1["param"]["del"] = $this->jqparam1["param"]["delete"];
			}

		ob_start(); ?> <table id="<?php
		echo $grid_id ?>"></table> <div id="<?php
		echo $grid_id . "_pager" ?>"></div> 			 <script>var phpgrid = jQuery("#<?php
		echo $grid_id ?>"); var phpgrid_pager = jQuery("#<?php
		echo $grid_id . "_pager" ?>");			var fx_ajax_file_upload; var fx_replace_upload; var fx_bulk_update; var fx_get_dropdown; jQuery(document).ready(function(){			 <?php
		echo $this->F300015ed2190df99db5f1bad555700($grid_id, $Vc68271a63ddbc431c307beb7d29182); ?> }); 			 </script> <?php
		return ob_get_clean();
		}

	function F300015ed2190df99db5f1bad555700($grid_id, $Vc68271a63ddbc431c307beb7d29182)
		{ ?> var lastSel; fx_clone_row = function (grid,id) { myData = {}; myData.id = id; myData.grid_id = grid;			myData.oper = 'clone'; jQuery.ajax({ url: "<?php
		echo $this->options["url"] ?>", dataType: "json",			 data: myData, type: "POST", error: function(res, status) { alert(res.status+" : "+res.statusText+". Status: "+status);			}, success: function( data ) { } }); jQuery("#"+grid).jqGrid().trigger('reloadGrid',[{jqgrid_page:1}]);			}; var extra_opts = {}; <?php ?> if (typeof(opts) != 'undefined') extra_opts = opts; if (typeof(opts_<?php
		echo $grid_id ?>) != 'undefined') extra_opts = opts_<?php
		echo $grid_id ?>;			var grid_<?php
		echo $grid_id ?> = jQuery("#<?php
		echo $grid_id ?>").jqGrid( jQuery.extend(<?php
		echo $Vc68271a63ddbc431c307beb7d29182 ?>, extra_opts ) );			 jQuery("#<?php
		echo $grid_id ?>").jqGrid('navGrid','#<?php
		echo $grid_id . "_pager" ?>', <?php
		echo json_encode_jsfunc($this->jqparam1["param"]) ?>,			 <?php
		echo json_encode_jsfunc($this->options["edit_options"]) ?>, <?php
		echo json_encode_jsfunc($this->options["add_options"]) ?>,			 <?php
		echo json_encode_jsfunc($this->options["delete_options"]) ?>, <?php
		echo json_encode_jsfunc($this->options["search_options"]) ?>,			 <?php
		echo json_encode_jsfunc($this->options["view_options"]) ?> ); <?php ?> <?php
		if ($this->actions["inlineadd"] !== false || $this->actions["inline"] === true)
			{ ?>			 jQuery('#<?php
			echo $grid_id ?>').jqGrid('inlineNav','#<?php
			echo $grid_id . "_pager" ?>',{"add":true,"edit":true,"save":true,"cancel":true,			 "addParams":{ "addRowParams": { "oneditfunc": function(id) { jQuery("#edit_row_<?php
			echo $grid_id ?>_"+id+" a:first").click(); 			 }, "afterrestorefunc": function(id) { jQuery("#save_row_<?php
			echo $grid_id ?>_"+id+" a:last").parent().hide().prev().show(); 			 }, "aftersavefunc":function (id, res) { jQuery('#<?php
			echo $grid_id ?>').trigger("reloadGrid",[{jqgrid_page:1}]);			}, "errorfunc": function(id,res) { jQuery.jgrid.info_dialog(jQuery.jgrid.errors.errcap,'<div class=\"ui-state-error\">'+ res.responseText +'</div>', 			 jQuery.jgrid.edit.bClose,{buttonalign:'right'}); jQuery('#<?php
			echo $grid_id ?>').trigger("reloadGrid",[{jqgrid_page:1}]);			} } } ,"editParams":{ "aftersavefunc":function (id, res) { jQuery('#<?php
			echo $grid_id ?>').trigger("reloadGrid",[{jqgrid_page:1}]);			}, "errorfunc": function(id,res) { jQuery.jgrid.info_dialog(jQuery.jgrid.errors.errcap,'<div class=\"ui-state-error\">'+ res.responseText +'</div>', 			 jQuery.jgrid.edit.bClose,{buttonalign:'right'}); jQuery('#<?php
			echo $grid_id ?>').trigger("reloadGrid",[{jqgrid_page:1}]);			}, "oneditfunc": function(id) { jQuery('#<?php
			echo $grid_id ?>').jqGrid('setSelection',id); jQuery("#edit_row_<?php
			echo $grid_id ?>_"+id+" a:first").click(); 			 }, "afterrestorefunc": function(id) { jQuery("#save_row_<?php
			echo $grid_id ?>_"+id+" a:last").parent().hide().prev().show(); 			 } }}); <?php
			} ?> <?php
		if ($this->actions["autofilter"] !== false)
			{ ?> jQuery("#<?php
			echo $grid_id ?>").jqGrid('filterToolbar',{stringResult: true,searchOnEnter : false, defaultSearch:'cn'}); 			 <?php
			} ?> <?php
		if ($this->actions["showhidecolumns"] !== false)
			{ ?> jQuery("#<?php
			echo $grid_id ?>").jqGrid('navButtonAdd',"#<?php
			echo $grid_id . "_pager" ?>",{caption:"Columns",title:"Hide/Show Columns", buttonicon :'ui-icon-note',			 onClickButton:function(){ jQuery("#<?php
			echo $grid_id ?>").jqGrid('columnChooser',{width : 250, height:150, modal:true, done:function(){ c = jQuery('#colchooser_<?php
			echo $grid_id ?> select').val(); var colModel = jQuery("#<?php
			echo $grid_id ?>").jqGrid("getGridParam", "colModel"); str = ''; jQuery(c).each(function(i){ str += colModel[c[i]]['name'] + ","; }); document.cookie = 'jqgrid_colchooser=' + str; }, "dialog_opts" : {"minWidth": 270} });			jQuery("#colchooser_<?php
			echo $grid_id ?>").parent().position({ my: "center", at: "center", of: $("#gbox_<?php
			echo $grid_id ?>")			 }); } }); <?php
			} ?> <?php ?> <?php
		if ($this->actions["bulkedit"] === true)
			{ ?> jQuery("#<?php
			echo $grid_id ?>").jqGrid('navButtonAdd',"#<?php
			echo $grid_id . "_pager" ?>",{			 'caption' : 'Bulk Edit', 'buttonicon' : 'ui-icon-pencil', 'onClickButton': function() { var ids = jQuery('#<?php
			echo $grid_id ?>').jqGrid('getGridParam','selarrrow'); 			 if (ids.length == 0) { jQuery.jgrid.info_dialog(jQuery.jgrid.errors.errcap,'<div class=\"ui-state-error\">'+jQuery.jgrid.nav.alerttext+'</div>', 			 jQuery.jgrid.edit.bClose,{buttonalign:'right'}); return; } jQuery('#<?php
			echo $grid_id ?>').jqGrid('editGridRow', ids, <?php
			echo json_encode_jsfunc($this->options["edit_options"]) ?>);			jQuery('#edithd<?php
			echo $grid_id ?> .ui-jqdialog-title').html("Bulk Edit"); jQuery('#editmod<?php
			echo $grid_id ?> .binfo').show();			jQuery('#editmod<?php
			echo $grid_id ?> .bottominfo').html("NOTE: Blank fields will be skipped"); jQuery('#editmod<?php
			echo $grid_id ?> select').prepend("<option value=''></option>").val('');			return true; }, 'position': 'last' }); <?php
			} ?> <?php ?> <?php
		if (isset($this->actions["clone"]) && $this->actions["clone"] === true)
			{ ?> 			 jQuery("#<?php
			echo $grid_id ?>").jqGrid('navButtonAdd',"#<?php
			echo $grid_id . "_pager" ?>",{caption:"",title:"Clone", buttonicon :'ui-icon-copy',			 onClickButton:function(){ var selr = jQuery("#<?php
			echo $grid_id ?>").jqGrid('getGridParam','selrow');			if (!selr) { var alertIDs = {themodal:'alertmod',modalhead:'alerthd',modalcontent:'alertcnt'}; if (jQuery("#"+alertIDs.themodal).html() === null) {			 jQuery.jgrid.createModal(alertIDs,"<div>"+jQuery.jgrid.nav.alerttext+ "</div><span tabindex='0'><span tabindex='-1' id='jqg_alrt'></span></span>",			 {gbox:"#gbox_"+jQuery.jgrid.jqID(this.p.id),jqModal:true,drag:true,resize:true, caption:jQuery.jgrid.nav.alertcap,			 top:100,left:100,width:200,height: 'auto',closeOnEscape:true, zIndex: null},"","",true); } jQuery.jgrid.viewModal("#"+alertIDs.themodal,			 {gbox:"#gbox_"+jQuery.jgrid.jqID(this.p.id),jqm:true}); jQuery("#jqg_alrt").focus(); return; } fx_clone_row("<?php
			echo $grid_id ?>",selr);			} }); <?php
			} ?> <?php
		if ($this->actions["export"] === true || $this->actions["export_excel"] === true || $this->actions["export_pdf"] === true || $this->actions["export_csv"] === true)
			{
			$Vda3ad3b4322b19b609e4fa9d0a98a9 = "&sidx=" . $this->options["sortname"] . "&sord=" . $this->options["sortorder"] . "&rows=" . $this->options["rowNum"]; ?> function F3113f6c798c1640f3ce1f0ffc75e93(type) { type = type || ""; var detail_grid_params = jQuery("#<?php
			echo $grid_id ?>").data('jqgrid_detail_grid_params');			detail_grid_params = detail_grid_params || ""; if ("<?php
			echo $this->options["url"] ?>".indexOf("?") != -1)			 window.open("<?php
			echo $this->options["url"] ?>" + "&export=1&jqgrid_page=1&export_type="+type+"<?php
			echo $Vda3ad3b4322b19b609e4fa9d0a98a9 ?>"+detail_grid_params);			else window.open("<?php
			echo $this->options["url"] ?>" + "?export=1&jqgrid_page=1&export_type="+type+"<?php
			echo $Vda3ad3b4322b19b609e4fa9d0a98a9 ?>"+detail_grid_params);			} <?php
			} ?> <?php ?> fx_success_msg = function (msg,fade) { var t = Math.max(0, ((jQuery(window).height() - jQuery('#info_dialog').outerHeight()) / 2) + jQuery(window).scrollTop());			var l = Math.max(0, ((jQuery(window).width() - jQuery('#info_dialog').outerWidth()) / 2) + jQuery(window).scrollLeft());			jQuery.jgrid.info_dialog("Info","<div class='ui-state-highlight' style='padding:5px;'>"+msg+"</div>", 			 jQuery.jgrid.edit.bClose,{buttonalign:"right", left:l, top:t }); jQuery("#info_dialog").abscenter();			if (fade == 1) jQuery("#info_dialog").delay(1000).fadeOut(); }; <?php ?> <?php
		if (isset($this->options["toolbar"]) && $this->options["toolbar"] != "bottom")
			{ ?> 			 jQuery(document).ready(function(){ <?php
			if ($this->options["toolbar"] == "top")
				{ ?> jQuery('#<?php
				echo $grid_id ?>_pager').insertBefore('#<?php
				echo $grid_id ?>_toppager');			<?php
				}
			  else
			if ($this->options["toolbar"] == "both")
				{ ?> jQuery('#<?php
				echo $grid_id ?>_pager').clone(true).insertBefore('#<?php
				echo $grid_id ?>_toppager').attr('id','_toppager');			<?php
				} ?> jQuery('#<?php
			echo $grid_id ?>_pager').removeClass("ui-jqgrid-pager"); jQuery('#<?php
			echo $grid_id ?>_pager').addClass("ui-jqgrid-toppager");			jQuery('#<?php
			echo $grid_id ?>_toppager').remove(); jQuery('#_toppager').attr('id','<?php
			echo $grid_id ?>_toppager'); 			 if (jQuery("link[href$='ui.bootstrap.jqgrid.css']").length) { jQuery('div.frozen-div').css('top','+=6px');			jQuery('div.frozen-bdiv').css('top','+=6px'); } }); <?php
			} ?> <?php
		if ($this->options["autoresize"] === true)
			{ ?>			 jQuery(window).bind("resize", function () { var oldWidth = jQuery("#<?php
			echo $grid_id ?>").jqGrid("getGridParam", "width"),			 newWidth = jQuery(window).width() - 30; if (oldWidth !== newWidth) { jQuery("#<?php
			echo $grid_id ?>").jqGrid("setGridWidth", newWidth);			} }).trigger("resize"); <?php
			} ?> <?php
		if ($this->options["resizable"] === true)
			{ ?> jQuery("#<?php
			echo $grid_id ?>").jqGrid('gridResize',{});			<?php
			} ?> <?php ?> jQuery("#<?php
		echo $grid_id ?>").jqGrid('setFrozenColumns'); jQuery("#<?php
		echo $grid_id ?>").triggerHandler("jqGridAfterGridComplete"); 			 jQuery.fn.abscenter = function () { this.css("position","absolute"); this.css("top", Math.max(0, ((jQuery(window).height() - jQuery(this).outerHeight()) / 2) + 			 jQuery(window).scrollTop()) + "px"); this.css("left", Math.max(0, ((jQuery(window).width() - jQuery(this).outerWidth()) / 2) + 			 jQuery(window).scrollLeft()) + "px"); return this; }; jQuery.fn.fixedcenter = function () { this.css("position","fixed");			this.css("top", Math.max(0, ((jQuery(window).height() - jQuery(this).outerHeight()) / 2)) + "px"); this.css("left", Math.max(0, ((jQuery(window).width() - jQuery(this).outerWidth()) / 2)) + "px");			return this; }; <?php
		}

	function Fe9b3c79462166409c20167747931ab($insquery, $Vd77d5e503ad1439f585ac494268b35)
		{
		if (strpos($Vd77d5e503ad1439f585ac494268b35, "mssql") !== false)
			{
			$insquery = preg_replace("/SELECT (.*) LIMIT ([0-9]+) OFFSET ([0-9]+)/i", "select top ($2 + $3) $1", $insquery);
			}
		  else
		if (strpos($Vd77d5e503ad1439f585ac494268b35, "oci8") !== false || strpos($Vd77d5e503ad1439f585ac494268b35, "db2") !== false)
			{
			preg_match("/(.*) LIMIT ([0-9]+) OFFSET ([0-9]+)/i", $insquery, $V9c28d32df234037773be184dbdafc2);
			if (count($V9c28d32df234037773be184dbdafc2))
				{
				$V1b1cc7f086b3f074da452bc3129981 = $V9c28d32df234037773be184dbdafc2[1];
				$srtgrid_srch = $V9c28d32df234037773be184dbdafc2[2];
				$V70be495d9702540befac439bed536f = $V9c28d32df234037773be184dbdafc2[3];
				$Va5ae62869c0a18568c329176f5460a = $V70be495d9702540befac439bed536f;
				$V56389958306b1878a4ef0c4ec340f4 = $V70be495d9702540befac439bed536f + $srtgrid_srch;
				$insquery = " SELECT * FROM ( SELECT a.*,rownum rnum FROM ($V1b1cc7f086b3f074da452bc3129981) a			 ) WHERE rnum > $Va5ae62869c0a18568c329176f5460a AND rnum <= $V56389958306b1878a4ef0c4ec340f4 ";
				}
			}

		return $insquery;
		}

	function F69129ad793d9569df115b389acab44($Vf1965a857bc285d26fe22023aa5ab5, $V341be97d9aff90c9978347f66f945b)
		{
		foreach($this->options["colModel"] as $conswherefmdtarr)
			{
			$Vf32353ea4a4bf46b014307995b4215 = $Vf1965a857bc285d26fe22023aa5ab5[$conswherefmdtarr["name"]];
			$V341be97d9aff90c9978347f66f945b = str_replace("{" . $conswherefmdtarr["name"] . "}", $Vf32353ea4a4bf46b014307995b4215, $V341be97d9aff90c9978347f66f945b);
			}

		return $V341be97d9aff90c9978347f66f945b;
		}

	function Fd280460c57fe7dec4bf2ebe324999f($V341be97d9aff90c9978347f66f945b)
		{
		if (is_array($V341be97d9aff90c9978347f66f945b))
			{
			foreach($V341be97d9aff90c9978347f66f945b AS $jqargs => $stripargs)
				{
				$V341be97d9aff90c9978347f66f945b[$jqargs] = Fd280460c57fe7dec4bf2ebe324999f($stripargs);
				}
			}
		  else
			{
			$V341be97d9aff90c9978347f66f945b = str_replace("'", "''", $V341be97d9aff90c9978347f66f945b);
			}

		return $V341be97d9aff90c9978347f66f945b;
		}
	}

if (!function_exists('json_encode'))
	{
	require_once 'JSON.php';

	function json_encode($V61dd86c2dc75c3f569ec619bd283a3)
		{
		global $Vb3e1e61750d712b03c001c5fe79105;
		if (!isset($Vb3e1e61750d712b03c001c5fe79105))
			{
			$Vb3e1e61750d712b03c001c5fe79105 = new Services_JSON();
			}

		return $Vb3e1e61750d712b03c001c5fe79105->encode($V61dd86c2dc75c3f569ec619bd283a3);
		}

	function json_decode($V61dd86c2dc75c3f569ec619bd283a3)
		{
		global $Vb3e1e61750d712b03c001c5fe79105;
		if (!isset($Vb3e1e61750d712b03c001c5fe79105))
			{
			$Vb3e1e61750d712b03c001c5fe79105 = new Services_JSON();
			}

		return $Vb3e1e61750d712b03c001c5fe79105->decode($V61dd86c2dc75c3f569ec619bd283a3);
		}
	}

if (!function_exists('phpgrid_error'))
	{
	function phpgrid_error($V6e2baaf3b97dbeef01c0043275f9a0)
		{
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
		die($V6e2baaf3b97dbeef01c0043275f9a0);
		}
	}

if (!function_exists('phpgrid_pr'))
	{
	function phpgrid_pr($stargval, $Vf24f62eeb789199b9b2e467df3b187 = 0)
		{
		echo "<pre>";
		print_r($stargval);
		echo "</pre>";
		if ($Vf24f62eeb789199b9b2e467df3b187) die;
		}
	}

function json_encode_jsfunc($Va43c1b0aa53a0c908810c06ab1ff39 = array() , $V4b5bea44af9baf871f58e4ecb54526 = array() , $Vc9e9a848920877e76685b2e4e76de3 = 0)
	{
	foreach($Va43c1b0aa53a0c908810c06ab1ff39 as $consforechkay => $stripargs)
		{
		if (is_array($stripargs))
			{
			$upsuserid = json_encode_jsfunc($stripargs, $V4b5bea44af9baf871f58e4ecb54526, 1);
			$Va43c1b0aa53a0c908810c06ab1ff39[$consforechkay] = $upsuserid[0];
			$V4b5bea44af9baf871f58e4ecb54526 = $upsuserid[1];
			}
		  else
			{
			if (substr($stripargs, 0, 8) == 'function')
				{
				$V19b0bee6b072408fc38b5d76725b76 = "#" . rand() . "#";
				$V4b5bea44af9baf871f58e4ecb54526[$V19b0bee6b072408fc38b5d76725b76] = $stripargs;
				$Va43c1b0aa53a0c908810c06ab1ff39[$consforechkay] = $V19b0bee6b072408fc38b5d76725b76;
				}
			  else
			if (substr($stripargs, 0, 2) == '[{')
				{
				$V19b0bee6b072408fc38b5d76725b76 = "#" . rand() . "#";
				$V4b5bea44af9baf871f58e4ecb54526[$V19b0bee6b072408fc38b5d76725b76] = $stripargs;
				$Va43c1b0aa53a0c908810c06ab1ff39[$consforechkay] = $V19b0bee6b072408fc38b5d76725b76;
				}
			}
		}

	if ($Vc9e9a848920877e76685b2e4e76de3 == 1)
		{
		return array(
			$Va43c1b0aa53a0c908810c06ab1ff39,
			$V4b5bea44af9baf871f58e4ecb54526
		);
		}
	  else
		{
		$V7648c463fc599b54a77f6d6dcbd693 = json_encode($Va43c1b0aa53a0c908810c06ab1ff39);
		foreach($V4b5bea44af9baf871f58e4ecb54526 as $consforechkay => $stripargs)
			{
			$V7648c463fc599b54a77f6d6dcbd693 = str_replace('"' . $consforechkay . '"', $stripargs, $V7648c463fc599b54a77f6d6dcbd693);
			}

		return $V7648c463fc599b54a77f6d6dcbd693;
		}
	}

function Fbcdbd6e55bcebccb5e372537a6cf1d($Ve34d514f7db5c8aac72a7c8191a096)
	{
	if (is_string($Ve34d514f7db5c8aac72a7c8191a096))
		{
		return utf8_encode($Ve34d514f7db5c8aac72a7c8191a096);
		}

	if (is_object($Ve34d514f7db5c8aac72a7c8191a096))
		{
		$V99415f0b9a2ae6d7290f1add23e3e4 = get_object_vars($Ve34d514f7db5c8aac72a7c8191a096);
		$V22af645d1859cb5ca6da0c484f1f37 = $Ve34d514f7db5c8aac72a7c8191a096;
		foreach($V99415f0b9a2ae6d7290f1add23e3e4 as $spsdata => $stripdatasplt)
			{
			$V22af645d1859cb5ca6da0c484f1f37->$spsdata = Fbcdbd6e55bcebccb5e372537a6cf1d($V22af645d1859cb5ca6da0c484f1f37->$spsdata);
			}

		return $V22af645d1859cb5ca6da0c484f1f37;
		}

	if (!is_array($Ve34d514f7db5c8aac72a7c8191a096)) return $Ve34d514f7db5c8aac72a7c8191a096;
	$upsuserid = array();
	foreach($Ve34d514f7db5c8aac72a7c8191a096 as $inscalv => $V8277e0910d750195b448797616e091) $upsuserid[$inscalv] = Fbcdbd6e55bcebccb5e372537a6cf1d($V8277e0910d750195b448797616e091);
	return $upsuserid;
	}

function Ff30ab28a29f834c3e8f01000509956($Ve34d514f7db5c8aac72a7c8191a096)
	{
	if (is_string($Ve34d514f7db5c8aac72a7c8191a096))
		{
		return utf8_decode($Ve34d514f7db5c8aac72a7c8191a096);
		}

	if (is_object($Ve34d514f7db5c8aac72a7c8191a096))
		{
		$V99415f0b9a2ae6d7290f1add23e3e4 = get_object_vars($Ve34d514f7db5c8aac72a7c8191a096);
		$V22af645d1859cb5ca6da0c484f1f37 = $Ve34d514f7db5c8aac72a7c8191a096;
		foreach($V99415f0b9a2ae6d7290f1add23e3e4 as $spsdata => $stripdatasplt)
			{
			$V22af645d1859cb5ca6da0c484f1f37->$spsdata = Ff30ab28a29f834c3e8f01000509956($V22af645d1859cb5ca6da0c484f1f37->$spsdata);
			}

		return $V22af645d1859cb5ca6da0c484f1f37;
		}

	if (!is_array($Ve34d514f7db5c8aac72a7c8191a096)) return $Ve34d514f7db5c8aac72a7c8191a096;
	$upsuserid = array();
	foreach($Ve34d514f7db5c8aac72a7c8191a096 as $inscalv => $V8277e0910d750195b448797616e091) $upsuserid[$inscalv] = Ff30ab28a29f834c3e8f01000509956($V8277e0910d750195b448797616e091);
	return $upsuserid;
	}
