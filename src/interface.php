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
	echo DefEqnGeneric($Latex,'block',$Label);
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

function RefCite($ID)
{
	# ToDo: implement literatur reference
	echo '['.$ID.']';
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




