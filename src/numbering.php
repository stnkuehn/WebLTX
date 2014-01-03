<?php 

/*
    WebLTX - Very lightweight web framework for publishing scientific 
    stuff as fast and easily as with Latex 
	
    Copyright (C) 2014  Dr. Steffen Kühn / steffen.kuehn@em-sys-dev.de

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// number and link informations about equations, sections, anchors, figures and so on
$NumberedObjects = array();

function CreateLinks($AllHTMFiles)
{
	global $Language;
	global $NumberedObjects;

	// calculate for every file the md5 hash
	$md5 = array();
	foreach ($AllHTMFiles as $file)
	{
		$md5[] = md5_file($file);
	}

	// read back the saved hashs 
	$CacheDir = './cache';

	$DataFile = $CacheDir.'/data.'.$Language;	
	
	if (file_exists($DataFile))
	{
		$data = unserialize(file_get_contents($DataFile));
		if (($data != False) && ($data[0] == $md5))
		{
			// restore data from file
			$NumberedObjects = $data[1];
			return;
		}
	}

	// read every single html-page and execute the php code inside
	CollectGlobalData($AllHTMFiles);

	// create directory when it isn't existing
	if (!file_exists($CacheDir))
	{
		mkdir($CacheDir);
		chmod($CacheDir, 0770);
	}
	
	// save the data
	$data = array($md5,$NumberedObjects);
	file_put_contents($DataFile,serialize($data),LOCK_EX);
	chmod($DataFile, 0660);
}

function CreateLabel($Label)
{
	if ($Label == '') return $Label = uniqid(); else return $Label;
}

function DefineNumberedObject($Label,$Type,$Properties = array())
{
	global $NumberedObjects;
	global $Page;

	if ($Label == '') return;	

	if (!isset($NumberedObjects[$Label]))
	{
		// the unique id
		$id = 0;
		$lastelem = end($NumberedObjects);
		if ($lastelem) $id = $lastelem['id'] + 1;
			
		// create a fitting section number
		$section = array();
		if ($Type == 'headline')
		{			
			$labels = array_reverse(array_keys($NumberedObjects));
			foreach ($labels as $label)
			{
				if ($NumberedObjects[$label]['type'] == 'headline')
				{
					$section = $NumberedObjects[$label]['section'];
					$section[] =  $NumberedObjects[$label]['nbr'];
					break;
				}			
			}
			$section = array_slice($section,0,$Properties['stage']);
		}
		else
		{
			$pre = end($NumberedObjects);
			if ($pre != False) $section = $pre['section'];
			if ($pre['type'] == 'headline') $section[] = $pre['nbr'];
		}	
		
		// count the number of same objects in this section
		$nbr = 1;
		$labels = array_reverse(array_keys($NumberedObjects));
		foreach ($labels as $label)
		{
			// same type and same section?
			if (($NumberedObjects[$label]['type'] == $Type) && 
				($NumberedObjects[$label]['section'] == $section))
			{
				$nbr += $NumberedObjects[$label]['nbr'];
				break;
			}			
		}

		// section numbers
		$NumberedObjects[$Label]['section'] = $section; 
		// the object type (e.g. figure, equation or anchor)
		$NumberedObjects[$Label]['type'] = $Type; 
		// seprate numbering for every section stage 
		$NumberedObjects[$Label]['nbr'] = $nbr;
		// unique id for html-anchors
		$NumberedObjects[$Label]['id'] = $id;
		// the html-page, in which the object was defined
		$NumberedObjects[$Label]['page'] = $Page;
		// array with other type specific properties
		$NumberedObjects[$Label]['prop'] = $Properties;
	}
}

function GetNumberString($Label)
{
	global $NumberedObjects;
	
	if (!isset($NumberedObjects[$Label])) return '';	
	
	if (isset($NumberedObjects[$Label]['prop']['numbering']))
	{
		if (!$NumberedObjects[$Label]['prop']['numbering']) return '';
	}
	
	$sections = $NumberedObjects[$Label]['section'];
	$res = '';
	foreach ($sections as $nbr)	$res .= $nbr.'.';
	$res .= $NumberedObjects[$Label]['nbr'];
	return $res;
}

function DefSectionGeneric($LongName,$ShortName,$Label,$Stage)
{
	global $NumberedObjects;
	
	$Hidden = False;
	$Numbering = True;
	
	if ($Label == '')	
	{
		$Numbering = False;
		$Label = CreateLabel($Label);
	}
	
	if ($ShortName == '')
	{
		$Hidden = True;
	}
	
	$Properties = array('longname'=>$LongName,'shortname'=>$ShortName,
		'stage'=>$Stage,'hidden'=>$Hidden,'numbering'=>$Numbering);
	
	DefineNumberedObject($Label,'headline',$Properties);
	
	$nbr = GetNumberString($Label);
	if ($nbr != '') $nbr .= ' ';
	
	return '<a name="'.$NumberedObjects[$Label]['id'].'"></a><h'.($Stage+1).'>'.
		$nbr.$LongName.'</h'.($Stage+1).'></a>';
}

function DefAnchorGeneric($ShortName,$Order,$Label,$Type)
{
	global $NumberedObjects;
	
	$Properties = array('shortname' => $ShortName, 'order' => $Order, 'type' => $Type);
	
	DefineNumberedObject($Label,'anchor',$Properties);

	return '<a name="'.$NumberedObjects[$Label]['id'].'"></a>';
}

function DefEqnGeneric($latex, $type = '', $label = '')
{
	global $NumberedObjects;

	if ($type == 'block')
	{
		$output = '<table class="eqn"><tr class="eqn"><td class="eqn" width=90%><div class="eqn"><math>';
		DefineNumberedObject($label,'equation');
	}
	else
	{
		$output = '<math>';
	}

	$output .= '<mrow>'.latex2mathml($latex).'</mrow>';

	if ($type == 'block')
	{
		$output .= '</math></div></td><td class="eqn">';
		if ($label != '')
		{
			$output .= '<a name="'.$NumberedObjects[$label]['id'].'"><div class="eqnbr">('.
				GetNumberString($label).')</div></a>';
		}
		$output .= '</td></tr></table>';
	}
	else
	{
		$output .= '</math>';
	}

	return $output;
}

function DefFigureGeneric($File,$CapLabel,$Description='',$Style='',$Label='')
{
	global $Language;
	
	if ($Label == '') $Label = $File;
	DefineNumberedObject($Label,'figure');
	
	$FilewP = './content/'.$Language.'/'.$File;

	$text = '<dl class="figure" style="'.$Style.'">';
	$text .= '<dt class="figure"><img  class="figure" src='.$FilewP.' alt=""/></dt>';
	$text .= '<dd class="figure">'.$CapLabel.' '.GetNumberString($Label);
	if ($Description != '')
	{
		$text .= ': ';
		$text .= $Description;
	}
	$text .= '</dd>';
	$text .= '</dl>';

	return $text;
}

function FormatReference($Page,$Lang,$Ref,$Text,$Ext='')
{
	$refstr = '';
	if ($Ref != '') $refstr = '#'.$Ref;
	return '<a '.$Ext.' href="index?page='.$Page.'&amp;lang='.$Lang.$refstr.'">'.$Text.'</a>';
}

function RefGeneric($Label,$Mode,$Ext='',$Text='')
{
	global $NumberedObjects;
	global $Language;

	$labref = '???';
	$labtext = '???';
	$sec = 0;

	if (isset($NumberedObjects[$Label]))
	{
		if (($Mode == 'title') && (isset($NumberedObjects[$Label]['prop']['longname'])))
		{
			$labtext = $NumberedObjects[$Label]['prop']['longname'];
		}
		elseif (($Mode == 'shortname') && (isset($NumberedObjects[$Label]['prop']['shortname'])))
		{
			$labtext = $NumberedObjects[$Label]['prop']['shortname'];
		}
		elseif ($Mode == 'free')
		{
			$labtext = $Text;
		}
		else
		{
			$labtext = GetNumberString($Label);
		}
		
		$labref = $NumberedObjects[$Label]['id'];
		$sec = $NumberedObjects[$Label]['page'];	
	}

	return FormatReference($sec,$Language,$labref,$labtext,$Ext);
}

function GetFirstSectionFromThisPage()
{
	global $NumberedObjects;
	global $Page;

	$keys = array_keys($NumberedObjects);
	foreach ($keys as $label)
	{
		if ($NumberedObjects[$label]['type'] == 'headline')
		{
			if ($NumberedObjects[$label]['page'] == $Page) return $label;
		}
	}
	return '';
}

function GetTitelGeneric()
{
	global $PROJECT_NAME;
	global $NumberedObjects;

	$label = GetFirstSectionFromThisPage();
	$name = '';
	if ($label != '')
	{
		$shortname = $NumberedObjects[$label]['prop']['shortname'];
		if ($shortname == '') $shortname = $NumberedObjects[$label]['prop']['longname'];
		$name = ': '.$shortname;
	}
	return $PROJECT_NAME.$name;
}

function GetSideLinksGeneric()
{
	// create table of content for side link drawing
	global $NumberedObjects;

	$links = '';
	$keys = array_keys($NumberedObjects);
	foreach ($keys as $label)
	{
		if ($NumberedObjects[$label]['type'] == 'headline')
		{
			if (!$NumberedObjects[$label]['prop']['hidden'])
			{
				$links .= RefGeneric($label,'shortname','class="left'.
					($NumberedObjects[$label]['prop']['stage']+1).'" ');
			}
		}
	}
	return $links;	
}

function GetLanguageLinks()
{
	global $LANGS_AVAILABLE;
	global $Page;
	
	$links = '';
	foreach ($LANGS_AVAILABLE as $lang)
	{
		$links .= '<td class="topnav">'.FormatReference($Page,$lang,'',$lang,'class="topnav" ').'</td>';
	}
	return $links;
}

function GetPrev($class)
{	
	global $Language;
	global $Page;
	
	if ($Page > 0)
	{
		return '<td '.$class.'>'.FormatReference($Page-1,$Language,'','&#8678;',$class).'</td>';
	}
	else
	{
		return '<td '.$class.'>&#8678;</td></div>';
	}
}

function GetNext($class)
{	
	global $Language;
	global $Page;
	global $AllHTMFiles;
	
	if ($Page+1 < sizeof($AllHTMFiles))
	{
		return '<td '.$class.'>'.FormatReference($Page+1,$Language,'','&#8680;',$class).'</td>';
	}
	else
	{
		return '<td '.$class.'>&#8680;</td></div>';
	}
}

function GetTopLinksGeneric()
{
	global $NumberedObjects;
	
	$links = '<div class="topnav" style="float:left"><table class="topnav"><tr class="topnav">';
	$hllabels = array_keys($NumberedObjects);
	$top = array();
	foreach ($hllabels as $hllabel)
	{
		if ($NumberedObjects[$hllabel]['type'] == 'anchor')
		{
			if ($NumberedObjects[$hllabel]['prop']['type'] == 'top')
			{
				$top[$NumberedObjects[$hllabel]['prop']['order']] = $hllabel;
			}
		}
	}
	ksort($top);
	foreach ($top as $anchor)
	{
		$links .= '<td class="topnav">'.RefGeneric($anchor,'shortname','class="topnav" ').'</td>';
	}	
	$links .= '</tr></table></div>';
	$links .= '<div class="topnav" style="float:right"><table class="topnav"><tr class="topnav">';
	$links .= GetPrev('class="topnav"');	
	$links .= '<td class="topnav">|</td>';
	$links .= GetNext('class="topnav"');
	$links .= '<td class="topnav">&nbsp;</td>';
	$links .= GetLanguageLinks();	
	$links .= '</tr></table></div>';
	$links .= "\n";
	return $links;
}


