<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	/* ######## Start security check ########## */
	$d1 = strtolower(substr(@$GLOBALS['phpgw_info']['server']['api_inc'],0,3));
	$d2 = strtolower(substr(@$GLOBALS['phpgw_info']['server']['server_root'],0,3));
	$d3 = strtolower(substr(@$GLOBALS['phpgw_info']['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' || $d2 == 'htt' || $d2 == 'ftp' || $d3 == 'htt' || $d3 == 'ftp')
	{
		echo 'Failed attempt to break in via an old Security Hole!<br>';
		exit;
	}
	unset($d1);unset($d2);unset($d3);
	/* ######## End security check ########## */
	if(file_exists('../header.inc.php'))
	{
		include('../header.inc.php');
	}
	else
	{
		define('PHPGW_SERVER_ROOT','..');
		define('PHPGW_INCLUDE_ROOT','..');
	}

	function CreateObject($class,
		$p1='_UNDEF_',$p2='_UNDEF_',$p3='_UNDEF_',$p4='_UNDEF_',
		$p5='_UNDEF_',$p6='_UNDEF_',$p7='_UNDEF_',$p8='_UNDEF_',
		$p9='_UNDEF_',$p10='_UNDEF_',$p11='_UNDEF_',$p12='_UNDEF_',
		$p13='_UNDEF_',$p14='_UNDEF_',$p15='_UNDEF_',$p16='_UNDEF_')
	{
		list($appname,$classname) = explode('.', $class);

		if (!isset($GLOBALS['phpgw_info']['flags']['included_classes'][$classname]) ||
			!$GLOBALS['phpgw_info']['flags']['included_classes'][$classname])
		{
			$GLOBALS['phpgw_info']['flags']['included_classes'][$classname] = True;   
			include(PHPGW_INCLUDE_ROOT.'/'.$appname.'/inc/class.'.$classname.'.inc.php');
		}
		if ($p1 == '_UNDEF_' && $p1 != 1)
		{
			eval('$obj = new ' . $classname . ';');
		}
		else
		{
			$input = array($p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12,$p13,$p14,$p15,$p16);
			$i = 1;
			$code = '$obj = new ' . $classname . '(';
			while (list($x,$test) = each($input))
			{
				if (($test == '_UNDEF_' && $test != 1 ) || $i == 17)
				{
					break;
				}
				else
				{
					$code .= '$p' . $i . ',';
				}
				$i++;
			}
			$code = substr($code,0,-1) . ');';
			eval($code);
		}
		return $obj;
	}

	/* This is needed is some parts of setup, until we include the API directly */
	function filesystem_separator()
	{
		if (PHP_OS == 'Windows' || PHP_OS == 'OS/2')
		{
			return '\\';
		}
		else
		{
			return '/';
		}
	}

	define('SEP',filesystem_separator());

	/*!
	 @function get_account_id
	 @abstract Return a properly formatted account_id.
	 @author skeeter
	 @discussion This function will return a properly formatted account_id. This can take either a name or an account_id as paramters. If a name is provided it will return the associated id.
	 @syntax get_account_id($accountid);
	 @example $account_id = get_account_id($accountid);
	 @param $account_id either a name or an id
	 @param $default_id either a name or an id
	*/
	function get_account_id($account_id = '',$default_id = '')
	{
		if (is_int($account_id))
		{
			return $account_id;
		}
		elseif ($account_id == '')
		{
			if ($default_id == '')
			{
				return (isset($GLOBALS['phpgw_info']['user']['account_id'])?$GLOBALS['phpgw_info']['user']['account_id']:0);
			}
			elseif (is_string($default_id))
			{
				return $GLOBALS['phpgw']->accounts->name2id($default_id);
			}
			return intval($default_id);
		}
		elseif (is_string($account_id))
		{
			if($GLOBALS['phpgw']->accounts->exists(intval($account_id)) == True)
			{
				return intval($account_id);
			}
			else
			{
				return $GLOBALS['phpgw']->accounts->name2id($account_id);
			}
		}
	}

	/*!
	 @function lang
	 @abstract function to handle multilanguage support
	*/
	function lang($key,$m1='',$m2='',$m3='',$m4='',$m5='',$m6='',$m7='',$m8='',$m9='',$m10='')
	{
		if(is_array($m1))
		{
			$vars = $m1;
		}
		else
		{
			$vars = array($m1,$m2,$m3,$m4,$m5,$m6,$m7,$m8,$m9,$m10);
		}
		$value = $GLOBALS['phpgw_setup']->translate("$key", $vars );
		return $value;
	}

	/*!
	@function get_langs
	@abstract	returns array of languages we support, with enabled set
				to True if the lang file exists
	*/
	function get_langs()
	{
		$f = fopen('./lang/languages','r');
		while ($line = fgets($f,200))
		{
			list($x,$y) = split("\t",$line);
			$languages[$x]['lang']  = trim($x);
			$languages[$x]['descr'] = trim($y);
			$languages[$x]['available'] = False;
		}
		fclose($f);

		$d = dir('./lang');
		while($entry=$d->read())
		{
			if (ereg('phpgw_',$entry))
			{
				$z = substr($entry,6,2);
				$languages[$z]['available'] = True;
			}
		}
		$d->close();

		//print_r($languages);
		return $languages;
	}

	function lang_select()
	{
		$ConfigLang = $GLOBALS['HTTP_COOKIE_VARS']['ConfigLang'] ? $GLOBALS['HTTP_COOKIE_VARS']['ConfigLang'] : $GLOBALS['HTTP_POST_VARS']['ConfigLang']; 

		$select = '<select name="ConfigLang">' . "\n";
		$languages = get_langs();
		while (list($null,$data) = each($languages))
		{
			if ($data['available'])
			{
				$selected = '';
				$short = substr($data['lang'],0,2);
				if ($short == $ConfigLang)
				{
					$selected = ' selected';
				}
				$select .= '<option value="' . $data['lang'] . '"' . $selected . '>' . $data['descr'] . '</option>' . "\n";
			}
		}
		$select .= '</select>' . "\n";

		return $select;
	}

	/*!
	@function isinarray
	@abstract php3/4 compliant in_array()
	@param	$needle		String to search for
	@param	$haystack	Array to search
	*/
	function isinarray($needle,$haystack='') 
	{
		if($haystack == '')
		{
			settype($haystack,'array');
			$haystack = Array();
		}
		for($i=0;$i<count($haystack) && $haystack[$i] !=$needle;$i++);
		return ($i!=count($haystack));
	}

	/* Include to check user authorization against the 
	   password in ../header.inc.php to protect all of the setup
	   pages from unauthorized use.
	*/

	function _debug_array($array)
	{
		if(floor(phpversion()) == 4)
		{
			ob_start(); 
			echo '<pre>'; print_r($array); echo '</pre>';
			$contents = ob_get_contents(); 
			ob_end_clean();
			echo $contents;
		}
		else
		{
			echo '<pre>'; var_dump($array); echo '</pre>';
		}
	}

	if(file_exists(PHPGW_SERVER_ROOT.'/phpgwapi/setup/setup.inc.php'))
	{
		include(PHPGW_SERVER_ROOT.'/phpgwapi/setup/setup.inc.php'); /* To set the current core version */
		/* This will change to just use setup_info */
		$GLOBALS['phpgw_info']['server']['versions']['current_header'] = $setup_info['phpgwapi']['versions']['current_header'];
	}
	else
	{
		$GLOBALS['phpgw_info']['server']['versions']['phpgwapi'] = 'Undetected';
	}

	$GLOBALS['phpgw_info']['server']['app_images'] = 'templates/default/images';

	include('./inc/class.setup.inc.php');
	$phpgw_setup = new phpgw_setup;
?>
