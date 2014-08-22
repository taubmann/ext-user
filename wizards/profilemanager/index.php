<?php
require '../../inc/header.php';
if(!$_SESSION[$projectName]['objects'][$objectName]) exit('access not allowed!');
$objects = $_SESSION[$projectName]['objects'];

/*
$lang = $_SESSION[$projectName]['lang'];

$backend = '../../../../';
*/
$project = $backend.'../projects/'.$projectName.'/';

$relpath = '../../../../';

// array_shift(explode('##',array_pop(explode('||',array_pop(explode('--', $field->lang->$lang))))))

function getLabel($objectName, $fieldName=false)
{
	global $objects, $lang;
	if (!$fieldName)
	{
		return (isset($objects[$objectName]['lang'][$lang])
				? $objects[$objectName]['lang'][$lang] 
				: $objectName
		);
	}
	$field = $objects[$objectName]['col'][$fieldName];
	return	(isset($field['lang'][$lang]['label']) 
			? $field['lang'][$lang]['label'] . ' [' . $fieldName . ']'
			: $fieldName
		);
}

$o = array();
$ol = array();
$fl = array();
foreach ($objects as $k => $v)
{
	$o[$k] = array();//array_keys((array)$v->col);
	$ol[$k] = getLabel($k);
	$fl[$k] = array();
	
	foreach ($v['col'] as $fk => $fv)
	{
		
		// show only the relevant fields
		if (substr($fk,-2)!='id' && 
			substr($fk,-4)!='sort' && 
			!in_array($fk, array('treeleft','treeright')) && 
			!in_array(substr($fv['type'],0,6), array('EXCLUD')) // do we need HIDDEN to be excluded????
		  )
		{
			$o[$k][] = $fk;
			$fl[$k][$fk] = getLabel($k, $fk);
		}
		
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Profile-Management</title>
	<meta charset="utf-8" />
	<?php
	echo '
	<script src="'.$relpath.'../vendor/cmskit/jquery-ui/jquery.min.js"></script>
	<script src="'.$relpath.'../vendor/cmskit/jquery-ui/jquery-ui.js"></script>
	<script src="'.$relpath.'../vendor/cmskit/jquery-ui/jquery.ui.nestedSortable.js"></script>
	<script src="'.$relpath.'../vendor/cmskit/jquery-ui/jquery.ui.touch-punch.js"></script>
	<script src="'.$relpath.'inc/js/json2.js"></script>
	<link  href="'.$relpath.'../vendor/cmskit/jquery-ui/themes/'.end($_SESSION[$projectName]['config']['theme']).'/jquery-ui.css" rel="stylesheet" type="text/css" />
	
	';
	
	$helpButton = file_exists('../../doc/'.$lang.'/.profilemanagement.md')
						? '<button style="float:right" type="button" onclick="window.open(\''.$relpath.'/admin/package_manager/showDoc.php?file=../../extensions/user/doc/'.$lang.'/.profilemanagement.md\',\'help\')">?</button>'
						: '';
	
	?>
	<style>
			body {
				background: #eee; 
				font: .8em "Trebuchet MS", sans-serif;
			}
			
			.delbutton {
				float:right;
				cursor:pointer;
			}
			.lo{
				width: 40px;
				margin-left:20px;
			}
			label{
				margin-left:20px;
			}
			#av button {
				width:250px;
				font-size: 1.2em;
			}
			.obj {
				border: 2px solid #ccc;
				-webkit-border-radius:5px;
				-moz-border-radius: 5px;
				border-radius: 5px;
			}
			.ui-icon{
				display: inline-block;
				margin-left: 30px;
				cursor: pointer;
			}
	</style>
</head>
<body>
<?php echo $helpButton?>
<button type="button" onclick="save()">save</button>

<h3><?php echo L('accessible_objects')?></h3>
<ol id="used">

</ol>
<hr />
<h3><?php echo L('available_objects')?></h3>
<ol id="av">

</ol>

<script>

var all_objects = JSON.parse('<?php echo json_encode($o);?>');

var objectLabels = JSON.parse('<?php echo json_encode($ol);?>');

var fieldLabels = JSON.parse('<?php echo json_encode($fl);?>');

var str = (parent.targetFieldId ? parent.$('#'+parent.targetFieldId).val() : '');
var used_objects = (str.length>3) ? JSON.parse(str) : {};

function L(s){
	return s.replace('_', ' ');
}

//////////////////////////////////////////////////////////////////////

function buildBox(name)
{
	if(!used_objects[name]) return '';
	if(!all_objects[name]) return '';
	
	var al = !!(used_objects[name]==1);
	
	var html = '<li class="obj" id="li_'+name+'" rel="'+name+'">';
		html += '<button style="float:right" type="button" title="<?php echo L('delete')?>" onclick="remObject(\''+name+'\')"><img src="kill.png" /></button> ';
		html += '<strong>'+L('Object')+' <em>'+objectLabels[name]+' ['+name+']</em></strong> ';
		html += '<button type="button" title="<?php echo L('move_up')?>" onclick="liUp(\''+name+'\')">&uArr;</button> <button type="button" title="<?php echo L('move_down')?>" onclick="liDown(\''+name+'\')">&dArr;</button><br />';
		
		//access-type (full/limited)
		html += '<input type="radio" name="at__'+name+'" onclick="$(\'#specs_'+name+'\').hide(\'slow\')" '+(al?'checked="checked" ':'')+'value="1" id="_full_access_'+name+'" /> '+L('full_Access')+'<br />';
		html += '<input type="radio" name="at__'+name+'" onclick="$(\'#specs_'+name+'\').show(\'slow\')" '+(al?'':'checked="checked" ')+'value="0" /> '+L('limited_Access')+'';
		html += '<div id="specs_'+name+'" '+(al?'style="display:none"':'')+'>';
		
		// fields
		html += '<button type="button" onclick="$(\'#flds_'+name+'\').toggle(\'slow\')">'+L('Fields')+'</button><ul id="flds_'+name+'" style="display:none">';
		var uf = (used_objects[name]&&used_objects[name]['show']?used_objects[name]['show']:[]);
		for(var i=0,j=all_objects[name].length; i<j; ++i)
		{
			//  <span class="ui-icon ui-icon-pencil" onclick="createTypeChange(this)"></span>
			html += '<li><input type="checkbox" '+(($.inArray(all_objects[name][i], uf)!=-1)?' checked="checked"':'')+' name="'+all_objects[name][i]+'" /> '+fieldLabels[name][all_objects[name][i]]+'</li>';
		}
		html += '</ul><br />';
		
		// actions
		var af = (used_objects[name]&&used_objects[name]['action']?used_objects[name]['action']:[]);
		
		var c = [];c['c']=L('Create');c['r']=L('Read');c['u']=L('Update');c['d']=L('Delete');c['a']=L('Assocciate');c['s']=L('Sort');
		
		html += '<button type="button" onclick="$(\'#act_'+name+'\').toggle(\'slow\')">'+L('Actions')+'</button><ul id="act_'+name+'" style="display:none">';
		for(e in c)
		{
			html += '<li><input type="checkbox" '+((af[e]&&af[e]==1)?' checked="checked"':'')+' name="'+e+'" /> '+c[e]+'</li>';
		}
		
		html += '</ul>';
		
		
		html += '</div>';
		html += '</li>';
		
	return html;
}

function liUp(name) {
	var c = $('#li_'+name);
	c.prev().before(c);
}

function liDown(name) {
	var c = $('#li_'+name);
	c.next().after(c);
}

function createTypeChange(el)
{
	var html = '<select><option value="">change Input-Type</option>';
		
		html += '</select>';
	el.innerHTML = html;
	el.removeAttribute('class');
}

function save()
{
	var myObj = {};
	$('#used .obj').each(function()
	{
		var name = $(this).attr('rel');
		
		// if 
		if( $('#_full_access_'+name).is(':checked') )
		{
			myObj[name] = 1;
		}
		else
		{
			myObj[name] = {};
			myObj[name]['show'] = [];
			
			$('#flds_'+name+' input:checked').each(function()
			{ 
				myObj[name]['show'].push($(this).attr('name'));
			});
			
			var c = 0;
			myObj[name]['action'] = {};
			$('#act_'+name+' input:checked').each(function()
			{ 
				myObj[name]['action'][$(this).attr('name')] = 1;
				c++;
			});
			
			if(c==0) delete(myObj[name]['action']);
		}
	});
	var j = JSON.stringify(myObj, false, "\t");
	var q = confirm(L('save_this')+"?\n\n"+j);
	if(q)
	{
		parent.$('#'+parent.targetFieldId).val(j);
		parent.saveContent("<?php echo $_GET['objectId'];?>");
	}
}

// fill list with available objects
function fillAv()
{
	var html_av = '';
	for(e in all_objects)
	{
		if(!used_objects[e]) html_av += '<li><button type="button" onclick="addObject(\''+e+'\')">'+objectLabels[e]+' ['+e+']</button></li>';
	}
	$('#av').html(html_av);
}


// transfer "available object" to "used objects"
function addObject(name)
{
	used_objects[name] = 1;
	var b = $(buildBox(name));
	$('#used').append(b);
	fillAv();
	
	$('#li_'+name+' button').button();
	$('#av button').button();
}

function remObject(name)
{
	$('#li_'+name).remove();
	delete(used_objects[name]);
	fillAv();
}

/////////////////////////////////////////////////////////////////////

// generate the List of avalable Objects
fillAv();

// generate the list of used Objects
var html_used = '';
for(e in used_objects)
{
	html_used += buildBox(e);
}
$('#used').html(html_used);

$('button').button();
</script>
</body>
</html>
