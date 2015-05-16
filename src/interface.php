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

// === define objects with numbering ===

function DefHiddenAnchor($ShortName,$Label)
{
	echo DefAnchorGeneric($ShortName,0,$Label,$Type='hidden');
}

// defines a link target with an link entry in the top line.
function DefTopAnchor($ShortName,$Label,$Order)
{
	echo DefAnchorGeneric($ShortName,$Order,$Label,$Type='top');
}

// equation in the floating text
function DefEqn($Latex)
{
	echo DefEqnGeneric($Latex);
}

// equation in block form. Can be referenced by a label.
function DefEqnB($Latex,$Label='')
{
	if ($Label=='') $Label=$Latex;
	echo DefEqnGeneric($Latex,'block',$Label);
}

// very important equation. 
function DefEqnBImp($Latex,$Label='')
{
	if ($Label=='') $Label=$Latex;
	echo DefEqnGeneric($Latex,'block',$Label,'important');
}

// defines a chapter.
function DefChapter($Title,$ShortName='',$Label='')
{
	echo DefSectionGeneric($Title,$ShortName,$Label,0);
}

// defines a section.
function DefSection($Title,$ShortName='',$Label='')
{
	echo DefSectionGeneric($Title,$ShortName,$Label,1);
}

// defines a subsection.
function DefSubSection($Title,$ShortName='',$Label='')
{
	echo DefSectionGeneric($Title,$ShortName,$Label,2);
}

// defines a subsection.
function DefSubSubSection($Title,$ShortName='',$Label='')
{
	echo DefSectionGeneric($Title,$ShortName,$Label,3);
}

// defines a figure.
function DefFigure($File,$Description='',$Style='',$Label='')
{
	global $Language;
	
	if ($Language == 'de')
	{
		$CapLabel = 'Abbildung';
	}
	else
	{
		$CapLabel = 'Figure';
	}	
	
	echo DefFigureGeneric($File,$CapLabel,$Description,$Style,$Label);
}

// === for quotation ===

function Cite($Label)
{
	NoCite($Label);
	echo '['.RefGeneric($Label,'free','',$Label).']';
}

function NoCite($Label)
{
	global $BibKeys;
	$BibKeys[] = $Label;
}

function Bibliography()
{
	echo print_bibliography();
}

// === refer to a numbered object ===

function RefByTitle($Label,$Ext='')
{
	echo RefGeneric($Label,'title',$Ext);
}

function RefByShortname($Label,$Ext='')
{
	echo RefGeneric($Label,'shortname',$Ext);
}

function Ref($Label,$Ext='')
{
	echo RefGeneric($Label,'nbr',$Ext);
}

function RefFree($Label,$Text,$Ext='')
{
	echo RefGeneric($Label,'free',$Ext,$Text);
}

// === for template ===

function GetTitel()
{
	echo GetTitelGeneric();
}

function GetSideLinks()
{
	echo GetSideLinksGeneric();
}

function GetTopLinks()
{
	echo GetTopLinksGeneric();
}

function GetAuthor()
{
	global $AUTHOR;
	echo '<meta name="author" content="'.$AUTHOR.'">';
}

function GetCopyright()
{
	global $COPYRIGHT;
	echo $COPYRIGHT;
}

function GetFavIcon()
{
	global $ROOTDIR;
	global $Version;
	echo '<link rel="shortcut icon" href="'.$ROOTDIR.'/'.$Version.'/template/favicon.ico">';
}

function GetScreenCSS()
{
	global $ROOTDIR;
	global $Version;
	echo '<link rel="stylesheet" href="'.$ROOTDIR.'/'.$Version.'/template/screen.css" media="screen">';
}

function GetPrintCSS()
{
	global $ROOTDIR;
	global $Version;
	echo '<link rel="stylesheet" href="'.$ROOTDIR.'/'.$Version.'/template/print.css" media="print">';
}

function GetHeaderImage()
{
	global $ROOTDIR;
	global $Version;
	echo '<img style="height:100%" src="'.$ROOTDIR.'/'.$Version.'/template/header.gif" alt="" />';
}
		
function LastUpdate()
{
	global $Language;
	
	if ($Language == 'de')
	{
		echo 'Zuletzt geändert am '.date("d.m.Y",LastUpdateGeneric());
	}
	else
	{
		echo 'Last updated at '.date("Y-m-d",LastUpdateGeneric());
	}
}

function GetContent()
{
	global $AllHTMFiles;
	global $Page;
	
	if (empty($AllHTMFiles)) return;
	if (array_key_exists($Page, $AllHTMFiles))
	{
		include($AllHTMFiles[$Page]);
	}
}

function GetKeywords()
{
	echo GetKeywordsGeneric();
}

function GetNavLinks()
{
	$res = '<table class="botnav"><tr>';
	$res .= GetPrev('class="botnav"');
	$res .= '<td class="botnav">|</td>';
	$res .= GetNext('class="botnav"');
	$res .= '</tr></table>';
	echo $res;
}

// === for drawing source code ===

function format_codeline($line)
{
	$line = str_replace("\r",'',$line);
	$line = str_replace("\n",'',$line);
	$line = str_replace("<",'&lt;',$line);
	$line = str_replace(">",'&gt;',$line);
	$line = str_replace("&",'&amp;',$line);
	$line = str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$line);

	return $line;
}

function HighlightSourcecode($file)
{
	global $Language;
	global $ROOTDIR;
	global $Version;
	
	$code = file($ROOTDIR.'/'.$Version.'/content/'.$Language.'/'.$file);
	$str = '<table class="code">'."\n";
	for ($i=0;$i<count($code);$i++)
	{
		$str .= '<tr>';
		$str .= '<td class="ln">'.$i.'</td>';
		$str .= '<td class="cl">'.format_codeline($code[$i]).'</td>'."\n";
		$str .= '</tr>';
	}
	$str .= '</table>'."\n";
	echo $str;
}

function GetDownloadLink($file,$descr)
{
	global $Language;
	global $ROOTDIR;
	global $Version;
	
	$path = $ROOTDIR.'/'.$Version.'/content/'.$Language.'/'.$file;	
	echo '<a href="'.$path.'">'.$descr.'</a>';
}









