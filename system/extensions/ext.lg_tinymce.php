<?php
/**
* Class file for LG TinyMCE
* 
* This file must be placed in the
* /system/extensions/ folder in your ExpressionEngine installation.
*
* @package LgTinyMce
* @version 1.3.3
* @author Leevi Graham <http://leevigraham.com>
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-tinymce/
* @copyright Copyright (c) 2007-2009 Leevi Graham
* @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported
*/
if ( ! defined('EXT')) exit('Invalid file request');

define("LG_TMC_version",			"1.3.3");
define("LG_TMC_docs_url",			"http://leevigraham.com/cms-customisation/expressionengine/addon/lg-tinymce/");
define("LG_TMC_addon_id",			"LG TinyMCE");
define("LG_TMC_extension_class",	"Lg_tinymce");
define("LG_TMC_cache_name",			"lg_cache");

/**
* This extension adds a new custom field type to {@link http://expressionengine.com ExpressionEngine} that integrates {@link http://tinymce.moxiecode.com/ Moxiecode TinyMCE}. 
*
* @package LgTinyMce
* @version 1.3.2
* @author Leevi Graham <http://leevigraham.com>
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-tinymce/
* @copyright Copyright (c) 2007-2009 Leevi Graham
* @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported
*/
class Lg_tinymce {

	/**
	* Extension settings
	* @var array
	*/
	var $settings			= array();

	/**
	* Extension name
	* @var string
	*/
	var $name				= 'LG TinyMCE';

	/**
	* Extension version
	* @var string
	*/
	var $version			= LG_TMC_version;

	/**
	* Extension description
	* @var string
	*/
	var $description		= 'Integrates Moxicodes TinyMCE into ExpressionEngine providing WYSIWYG content editing';

	/**
	* If $settings_exist = 'y' then a settings page will be shown in the ExpressionEngine admin
	* @var string
	*/
	var $settings_exist		= 'y';

	/**
	* Link to extension documentation
	* @var string
	*/
	var $docs_url			= LG_TMC_docs_url;

	/**
	* Debug?
	* @var string
	*/
	var $debug 				= FALSE;

	/**
	* Custom field type id
	* @var string
	*/
	var $type				= "wysiwyg";

	/**
	* PHP4 Constructor
	*
	* @see __construct
	*/
	function Lg_tinymce( $settings='' )
	{
		$this->__construct($settings);
	}

	/**
	* PHP 5 Constructor
	*
	* @param	array|string $settings Extension settings associative array or an empty string
	* @since	Version 1.2.0
	*/
	function __construct( $settings='' )
	{
		global $IN, $SESS;

		// get the settings from our helper class
		// this returns all the sites settings
		$this->settings = $this->_get_settings();

		if(isset($SESS->cache['lg']) === FALSE){
			$SESS->cache['lg'] = array();
		}
		$this->debug = $IN->GBL('debug');
	}

	/**
	* Configuration for the extension settings page
	*
	* @since	Version 1.3.0	
	**/
	function settings_form( $current )
	{
		global $DB, $DSP, $LANG, $IN, $PREFS, $SESS;
		
		// create a local variable for the site settings
		$settings = $this->_get_settings();

		$DSP->crumbline = TRUE;

		$DSP->title  = $LANG->line('extension_settings');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
		$DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));

		$DSP->crumb .= $DSP->crumb_item($LANG->line('lg_tinymce_title') . " {$this->version}");

		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));

		$DSP->body = '';

		if(isset($settings['show_promos']) === FALSE) {$settings['show_promos'] = 'y';}
		if($settings['show_promos'] == 'y')
		{
			$DSP->body .= "<script src='http://leevigraham.com/promos/ee.php?id=" . rawurlencode(LG_TMC_addon_id) ."&v=".$this->version."' type='text/javascript' charset='utf-8'></script>";
		}

		if(isset($settings['show_donate']) === FALSE) {$settings['show_donate'] = 'y';}
		if($settings['show_donate'] == 'y')
		{
			$DSP->body .= "<style type='text/css' media='screen'>
				#donate{float:right; margin-top:0; padding-left:190px; position:relative; top:-2px}
				#donate .button{background:transparent url(http://leevigraham.com/themes/site_themes/default/img/btn_paypal-donation.png) no-repeat scroll left bottom; display:block; height:0; overflow:hidden; position:absolute; top:0; left:0; padding-top:27px; text-decoration:none; width:175px}
				#donate .button:hover{background-position:top right;}
			</style>";
			$DSP->body .= "<p id='donate'>
							" . $LANG->line('donation') ."
							<a rel='external' href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=sales%40newism%2ecom.au&amp;item_name=LG%20Expression%20Engine%20Development&amp;amount=%2e00&amp;no_shipping=1&amp;return=http%3a%2f%2fleevigraham%2ecom%2fdonate%2fthanks&amp;cancel_return=http%3a%2f%2fleevigraham%2ecom%2fdonate%2fno%2dthanks&amp;no_note=1&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8' class='button' target='_blank'>Donate</a>
						</p>";
		}

		$DSP->body .= $DSP->heading($LANG->line('lg_tinymce_title') . " <small>{$this->version}</small>");
		
		$DSP->body .= $DSP->form_open(
			array(
				'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
				'name'   => 'settings_example',
				'id'     => 'settings_example'
			),
			// WHAT A M*THERF!@KING B!TCH THIS WAS
			// REMBER THE NAME ATTRIBUTE MUST ALWAYS MATCH THE FILENAME AND ITS CASE SENSITIVE
			// BUG??
			array('name' => strtolower(get_class($this)))
		);

		// EXTENSION SETTINGS
		$DSP->body .=   $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'margin-top:18px; width:100%'));

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableHeading', '', '2');
		$DSP->body .=   $LANG->line("extension_settings_title");
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('', '', '2');
		$DSP->body .=   "<div class='box' style='border-width:0 0 1px 0; margin:0; padding:10px 5px'><p>" . $LANG->line('extension_settings_info'). "</p></div>";
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();


		// STANDARD CONFIG
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellTwo', '30%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line("script_path_label"));
		$DSP->body .=   $DSP->td_c();

		$DSP->body .=   $DSP->td('tableCellTwo');
		$DSP->body .=   $DSP->input_text('script_path', $settings['script_path']);
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellOne', '30%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('script_config_label'));
		$DSP->body .=   $DSP->td_c();

		$DSP->body .=   $DSP->td('tableCellOne');
		$DSP->body .=   $DSP->input_textarea('script_config', $settings['script_config'], 20, 'textarea', '99%');
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();
		$DSP->body .=   $DSP->table_c();

		// GZIP
		$DSP->body .=   $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'margin-top:18px; width:100%'));

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableHeading', '', '2');
		$DSP->body .=   $LANG->line("gzip_settings_title");
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('', '', '2');
		$DSP->body .=   "<div class='box' style='border-width:0 0 1px 0; margin:0; padding:10px 5px'><p>" . $LANG->line('gzip_settings_info'). "</p></div>";
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellTwo', '30%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line("enable_gzip_label"));
		$DSP->body .=   $DSP->td_c();

		$DSP->body .=   $DSP->td('tableCellTwo');
		$DSP->body .=   "<select name='enable_gzip'>"
						. $DSP->input_select_option('y', "Yes", (($settings['enable_gzip'] == 'y') ? 'y' : '' ))
						. $DSP->input_select_option('n', "No", (($settings['enable_gzip'] == 'n') ? 'y' : '' ))
						. $DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellOne', '30%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line("gzip_script_path_label"));
		$DSP->body .=   $DSP->td_c();

		$DSP->body .=   $DSP->td('tableCellOne');
		$DSP->body .=   $DSP->input_text('gzip_script_path', $settings['gzip_script_path']);
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellTwo', '30%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('gzip_script_config_label'));
		$DSP->body .=   $DSP->td_c();

		$DSP->body .=   $DSP->td('tableCellTwo');
		$DSP->body .=   $DSP->input_textarea('gzip_script_config', $settings['gzip_script_config'], 10, 'textarea', '99%');
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();
		$DSP->body .=   $DSP->table_c();

		// UPDATE SETTINGS
		$DSP->body .=   $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'margin-top:18px; width:100%'));

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableHeading', '', '2');
		$DSP->body .=   $LANG->line("check_for_updates_title");
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('', '', '2');
		$DSP->body .=   "<div class='box' style='border-width:0 0 1px 0; margin:0; padding:10px 5px'><p>" . $LANG->line('check_for_updates_info'). "</p></div>";
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();

		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellOne', '30%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line("check_for_updates_label"));
		$DSP->body .=   $DSP->td_c();

		$DSP->body .=   $DSP->td('tableCellOne');
		$DSP->body .=   "<select name='check_for_updates'>"
						. $DSP->input_select_option('y', "Yes", (($settings['check_for_updates'] == 'y') ? 'y' : '' ))
						. $DSP->input_select_option('n', "No", (($settings['check_for_updates'] == 'n') ? 'y' : '' ))
						. $DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();


		$DSP->body .=   $DSP->table_c();


		if($IN->GBL('lg_admin') != 'y')
		{
			$DSP->body .= $DSP->table_c();
			$DSP->body .= "<input type='hidden' value='".$settings['show_donate']."' name='show_donate' />";
			$DSP->body .= "<input type='hidden' value='".$settings['show_promos']."' name='show_promos' />";
		}
		else
		{
			$DSP->body .= $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'margin-top:18px; width:100%'));
			$DSP->body .= $DSP->tr()
				. $DSP->td('tableHeading', '', '2')
				. $LANG->line("lg_admin_title")
				. $DSP->td_c()
				. $DSP->tr_c();

			$DSP->body .= $DSP->tr()
				. $DSP->td('tableCellOne', '30%')
				. $DSP->qdiv('defaultBold', $LANG->line("show_donate_label"))
				. $DSP->td_c();

			$DSP->body .= $DSP->td('tableCellOne')
				. "<select name='show_donate'>"
						. $DSP->input_select_option('y', "Yes", (($settings['show_donate'] == 'y') ? 'y' : '' ))
						. $DSP->input_select_option('n', "No", (($settings['show_donate'] == 'n') ? 'y' : '' ))
						. $DSP->input_select_footer()
				. $DSP->td_c()
				. $DSP->tr_c();

			$DSP->body .= $DSP->tr()
				. $DSP->td('tableCellTwo', '30%')
				. $DSP->qdiv('defaultBold', $LANG->line("show_promos_label"))
				. $DSP->td_c();

			$DSP->body .= $DSP->td('tableCellTwo')
				. "<select name='show_promos'>"
						. $DSP->input_select_option('y', "Yes", (($settings['show_promos'] == 'y') ? 'y' : '' ))
						. $DSP->input_select_option('n', "No", (($settings['show_promos'] == 'n') ? 'y' : '' ))
						. $DSP->input_select_footer()
				. $DSP->td_c()
				. $DSP->tr_c();

			$DSP->body .= $DSP->table_c();
		}

		$DSP->body .=   $DSP->qdiv('itemWrapperTop', $DSP->input_submit());
		$DSP->body .=   $DSP->form_c();
	}

	/**
	* Saves the settings from the config form
	*
	* @since	Version 1.3.0
	**/
	function save_settings()
	{
		// make somethings global
		global $DB, $IN, $PREFS, $REGX, $SESS;
		
		// load the settings from cache or DB
		$this->settings = $this->_get_settings(TRUE, TRUE);

		// unset the name
		unset($_POST['name']);
		
		// add the posted values to the settings
		$this->settings[$PREFS->ini('site_id')] = $_POST;

		if(isset($_POST['weblogs']) === FALSE)
		{
			$this->settings[$PREFS->ini('site_id')]['weblogs'] = array();
		}

		// update the settings
		$DB->query($sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($this->settings)) . "' WHERE class = '" . get_class($this) . "'");

	}

	/**
	* Returns the default settings for this extension
	* This is used when the extension is activated or when a new site is installed
	*/
	function _build_default_settings()
	{
		$default_settings = array(
			'check_for_updates' 	=> 'y',
			'script_path'			=> '/scripts/tinymce/jscripts/tiny_mce/tiny_mce.js',
			'show_donate'			=> 'y',
			'show_promos'			=> 'y',
			'enable_gzip'			=> 'n',
			'gzip_script_path' 		=> '/scripts/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js',
			'gzip_script_config'	=> "/* 
  Basic Configuration
  My Favs - Simple but effective
*/
plugins: 'safari,pagebreak,style,inlinepopups,media,contextmenu,paste,fullscreen,nonbreaking,xhtmlxtras',
/*
  Advanced Configuration 
  All Plugins
*/
/*
plugins : 'safari,pagebreak,style,layer,table,save,advhr,advimage,'
    + 'advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,'
    + 'searchreplace,print,contextmenu,paste,directionality,fullscreen,'
    + 'noneditable,visualchars,nonbreaking,xhtmlxtras,template',
*/

themes : 'advanced',
languages : 'en',
disk_cache : true,
debug : false",
	'script_config'					=> "// General
button_tile_map : true,
editor_selector : 'lg_mceEditor',
mode:'textareas',
theme : 'advanced',

// Cleanup/Output
apply_source_formatting : true,
convert_fonts_to_spans : true,
convert_newlines_to_brs : false,
fix_list_elements : true,
fix_table_elements : true,
fix_nesting : true,
forced_root_block : 'p',

// URL
relative_urls : false,
remove_script_host : true,

// Layout
// Uncomment and add your own stylesheet to style editor content
// content_css : '/themes/site_themes/default/styles/editor.css?' + new Date().getTime(),

// Advanced Theme
theme_advanced_blockformats : 'p,h1,h2,h3,h4,h5,h6,code',
theme_advanced_toolbar_location : 'top',
theme_advanced_toolbar_align : 'left',
theme_advanced_statusbar_location : 'bottom',
theme_advanced_resize_horizontal : false,
theme_advanced_resizing : true,

/* 
  Basic configuration
  My Favs - Simple but effective
*/
plugins : 'safari,pagebreak,style,inlinepopups,media,contextmenu,paste,'
    + 'fullscreen,nonbreaking,xhtmlxtras',
theme_advanced_buttons1 : 'cut,copy,pastetext,|,formatselect,|,bold,italic,'
    + 'strikethrough,acronym,abbr,ins,del,nonbreaking,|,bullist,numlist,outdent,'
    + 'indent,|,link,unlink,|,image,|,visualaid,fullscreen,|,cleanup,removeformat,code',
theme_advanced_buttons2 : '',
theme_advanced_buttons3 : '',

/* 
  Advanced Configuration
  Every button and plugin in the world. With great power come shitty code...
*/
/*
plugins : ''
    + 'safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,'
    + 'inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,'
    + 'directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
theme_advanced_buttons1 : 'save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,'
    + 'justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect',
theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,'
    + '|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime'
    + 'preview,|,forecolor,backcolor',
theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,'
    + 'iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
theme_advanced_buttons4 : 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,'
    + 'acronym,del,ins,|,visualchars,nonbreaking',
*/

// Really Long Settings
entities : ''
    + '160,nbsp,38,amp,162,cent,8364,euro,163,pound,165,yen,169,copy,174,reg,8482,trade,'
    + '8240,permil,181,micro,183,middot,8226,bull,8230,hellip,8242,prime,8243,Prime,167,sect,'
    + '182,para,223,szlig,8249,lsaquo,8250,rsaquo,171,laquo,187,raquo,8216,lsquo,8217,rsquo,'
    + '8220,ldquo,8221,rdquo,8218,sbquo,8222,bdquo,60,lt,62,gt,8804,le,8805,ge,8211,ndash,'
    + '8212,mdash,175,macr,8254,oline,164,curren,166,brvbar,168,uml,161,iexcl,191,iquest,'
    + '710,circ,732,tilde,176,deg,8722,minus,177,plusmn,247,divide,8260,frasl,215,times,185,sup1,'
    + '178,sup2,179,sup3,188,frac14,189,frac12,190,frac34,402,fnof,8747,int,8721,sum,8734,infin,'
    + '8730,radic,8764,sim,8773,cong,8776,asymp,8800,ne,8801,equiv,8712,isin,8713,notin,8715,ni,'
    + '8719,prod,8743,and,8744,or,172,not,8745,cap,8746,cup,8706,part,8704,forall,8707,exist,'
    + '8709,empty,8711,nabla,8727,lowast,8733,prop,8736,ang,180,acute,184,cedil,170,ordf,186,ordm,'
    + '8224,dagger,8225,Dagger,192,Agrave,194,Acirc,195,Atilde,196,Auml,197,Aring,198,AElig,'
    + '199,Ccedil,200,Egrave,202,Ecirc,203,Euml,204,Igrave,206,Icirc,207,Iuml,208,ETH,209,Ntilde,'
    + '210,Ograve,212,Ocirc,213,Otilde,214,Ouml,216,Oslash,338,OElig,217,Ugrave,219,Ucirc,220,Uuml,'
    + '376,Yuml,222,THORN,224,agrave,226,acirc,227,atilde,228,auml,229,aring,230,aelig,231,ccedil,'
    + '232,egrave,234,ecirc,235,euml,236,igrave,238,icirc,239,iuml,240,eth,241,ntilde,242,ograve,'
    + '244,ocirc,245,otilde,246,ouml,248,oslash,339,oelig,249,ugrave,251,ucirc,252,uuml,254,thorn,'
    + '255,yuml,914,Beta,915,Gamma,916,Delta,917,Epsilon,918,Zeta,919,Eta,920,Theta,921,Iota,922,Kappa,'
    + '923,Lambda,924,Mu,925,Nu,926,Xi,927,Omicron,928,Pi,929,Rho,931,Sigma,932,Tau,933,Upsilon,'
    + '934,Phi,935,Chi,936,Psi,937,Omega,945,alpha,946,beta,947,gamma,948,delta,949,epsilon,950,zeta,'
    + '951,eta,952,theta,953,iota,954,kappa,955,lambda,956,mu,957,nu,958,xi,959,omicron,960,pi,'
    + '961,rho,962,sigmaf,963,sigma,964,tau,965,upsilon,966,phi,967,chi,968,psi,969,omega,8501,alefsym,'
    + '982,piv,8476,real,977,thetasym,978,upsih,8472,weierp,8465,image,8592,larr,8593,uarr,8594,rarr,'
    + '8595,darr,8596,harr,8629,crarr,8656,lArr,8657,uArr,8658,rArr,8659,dArr,8660,hArr,8756,there4,'
    + '8834,sub,8835,sup,8836,nsub,8838,sube,8839,supe,8853,oplus,8855,otimes,8869,perp,8901,sdot,'
    + '8968,lceil,8969,rceil,8970,lfloor,8971,rfloor,9001,lang,9002,rang,9674,loz,9824,spades,'
    + '9827,clubs,9829,hearts,9830,diams,8194,ensp,8195,emsp,8201,thinsp,8204,zwnj,8205,zwj,8206,lrm,'
    + '8207,rlm,173,shy,233,eacute,237,iacute,243,oacute,250,uacute,193,Aacute,225,aacute,201,Eacute,'
    + '205,Iacute,211,Oacute,218,Uacute,221,Yacute,253,yacute',

valid_elements : ''
+'a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name'
    +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev'
    +'|shape<circle?default?poly?rect|style|tabindex|title|target|type],'
+'abbr[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'acronym[class|dir<ltr?rtl|id|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'address[class|align|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'applet[align<bottom?left?middle?right?top|alt|archive|class|code|codebase'
    +'|height|hspace|id|name|object|style|title|vspace|width],'
+'area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref'
    +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup'
    +'|shape<circle?default?poly?rect|style|tabindex|title|target],'
+'base[href|target],'
+'basefont[color|face|id|size],'
+'bdo[class|dir<ltr?rtl|id|lang|style|title],'
+'big[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'blockquote[dir|style|cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick'
    +'|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
    +'|onmouseover|onmouseup|style|title],'
+'body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink],'
+'br[class|clear<all?left?none?right|id|style|title],'
+'button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur'
    +'|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown'
    +'|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|title|type'
    +'|value],'
+'caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'center[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
+'|title],'
+'cite[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'code[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id'
    +'|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
    +'|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title'
    +'|valign<baseline?bottom?middle?top|width],'
+'colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl'
    +'|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
    +'|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title'
    +'|valign<baseline?bottom?middle?top|width],'
+'dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],'
+'del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'dfn[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'dir[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'dl[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'dt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],'
+'em/i[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'fieldset[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'font[class|color|dir<ltr?rtl|face|id|lang|size|style|title],'
+'form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang'
    +'|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit'
    +'|style|title|target],'
+'frame[class|frameborder|id|longdesc|marginheight|marginwidth|name'
    +'|noresize<noresize|scrolling<auto?no?yes|src|style|title],'
+'frameset[class|cols|id|onload|onunload|rows|style|title],'
+'h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'head[dir<ltr?rtl|lang|profile],'
+'hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|size|style|title|width],'
+'html[dir<ltr?rtl|lang|version],'
+'iframe[align<bottom?left?middle?right?top|class|frameborder|height|id'
    +'|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style'
    +'|title|width],'
+'img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height'
    +'|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|src|style|title|usemap|vspace|width],'
+'input[accept|accesskey|align<bottom?left?middle?right?top|alt'
    +'|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang'
    +'|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
    +'|readonly<readonly|size|src|style|tabindex|title'
    +'|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text'
    +'|usemap|value],'
+'ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],'
+'kbd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|onclick|ondblclick'
    +'|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
    +'|onmouseover|onmouseup|style|title],'
+'legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang'
    +'|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type'
    +'|value],'
+'link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type],'
+'map[class|dir<ltr?rtl|id|lang|name|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'menu[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme],'
+'noframes[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'noscript[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'object[align<bottom?left?middle?right?top|archive|border|class|classid'
    +'|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name'
    +'|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap'
    +'|vspace|width],'
+'ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|start|style|title|type],'
+'optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick'
    +'|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
    +'|onmouseover|onmouseup|selected<selected|style|title|value],'
+'p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|style|title],'
+'param[id|name|type|value|valuetype<DATA?OBJECT?REF],'
+'pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick'
    +'|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
    +'|onmouseover|onmouseup|style|title|width],'
+'q[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'s[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],'
+'samp[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'script[charset|defer|language|src|type],'
+'select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name'
    +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style'
    +'|tabindex|title],'
+'small[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'strike[class|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title],'
+'strong/b[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'style[dir<ltr?rtl|lang|media|title|type],'
+'sub[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'sup[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title],'
+'table[align<center?left?right|bgcolor|border|cellpadding|cellspacing|class'
    +'|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rules'
    +'|style|summary|title|width],'
+'tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id'
    +'|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
    +'|onmousemove|onmouseout|onmouseover|onmouseup|style|title'
    +'|valign<baseline?bottom?middle?top],'
+'td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class'
    +'|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup'
    +'|style|title|valign<baseline?bottom?middle?top|width],'
+'textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name'
    +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
    +'|readonly<readonly|rows|style|tabindex|title],'
+'tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id'
    +'|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
    +'|onmousemove|onmouseout|onmouseover|onmouseup|style|title'
    +'|valign<baseline?bottom?middle?top],'
+'th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class'
    +'|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick'
    +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
    +'|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup'
    +'|style|title|valign<baseline?bottom?middle?top|width],'
+'thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id'
    +'|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown'
    +'|onmousemove|onmouseout|onmouseover|onmouseup|style|title'
    +'|valign<baseline?bottom?middle?top],'
+'title[dir<ltr?rtl|lang],'
+'tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class'
    +'|rowspan|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'
    +'|title|valign<baseline?bottom?middle?top],'
+'tt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],'
+'u[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
    +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],'
+'ul[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown'
    +'|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover'
    +'|onmouseup|style|title|type],'
+'var[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress'
    +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style'"
);
		return $default_settings;
	
	}

	/**
	* Activates the extension
	*
	* <p>The extension activation script adds a very inclusive TinyMCE by default.
	* Removing plugins you don't need will increase load speed of the editor.<p>
	* <p>The configuration also allows you to style the editor content by 
	* creating a CSS file at: <code>/themes/site_themes/default/styles/editor.css</code>.</p>
	*
	* @return	bool Always TRUE
	*/
	function activate_extension()
	{
		global $DB, $PREFS;

		$default_settings = $this->_build_default_settings();

		$query = $DB->query("SELECT * FROM exp_sites");

		if ($query->num_rows > 0)
		{
			foreach($query->result as $row)
			{
				$settings[$row['site_id']] = $default_settings;
			}
		}

		$hooks = array(
			'publish_admin_edit_field_extra_row'	=> 'publish_admin_edit_field_extra_row',
			'publish_form_field_unique'				=> 'publish_form_field_unique',
			'show_full_control_panel_end' 			=> 'show_full_control_panel_end',
			'lg_addon_update_register_source'		=> 'lg_addon_update_register_source',
			'lg_addon_update_register_addon'		=> 'lg_addon_update_register_addon'
		);

		foreach ($hooks as $hook => $method)
		{
			$sql[] = $DB->insert_string( 'exp_extensions', 
											array('extension_id' 	=> '',
												'class'				=> LG_TMC_extension_class,
												'method'			=> $method,
												'hook'				=> $hook,
												'settings'			=> addslashes(serialize($settings)),
												'priority'			=> 10,
												'version'			=> $this->version,
												'enabled'			=> "y"
											)
										);
		}

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
	}

	/**
	* Updates the extension
	*
	* @param	string $current If installed the current version of the extension otherwise an empty string
	* @return	bool FALSE if the extension is not installed or is the current version
	*/
	function update_extension( $current = '' )
	{
		global $DB, $LANG, $OUT;

		if ($current == '' OR $current == $this->version)
			return FALSE;

		if ($current < '1.3.0')
	    {
			return $OUT->show_user_error( 'general', $LANG->line('130_previous_version_error'));
		}

		// get all settings
		$settings = $this->_get_settings(TRUE, TRUE);

		if ($current < '1.3.2')
	    {
			// delete the control_panel_home_page hook
			$sql[] = "DELETE FROM `exp_extensions` WHERE `class` = '".get_class($this)."' AND `hook` = 'control_panel_home_page'";

			// create two new hooks
			$hooks = array(
				'lg_addon_update_register_source'	=> 'lg_addon_update_register_source',
				'lg_addon_update_register_addon'	=> 'lg_addon_update_register_addon'
			);
			// for each of the new hooks
			foreach ($hooks as $hook => $method)
			{
				// build the sql
				$sql[] = $DB->insert_string( 'exp_extensions', 
												array('extension_id' 	=> '',
													'class'			=> get_class($this),
													'method'		=> $method,
													'hook'			=> $hook,
													'settings'		=> addslashes(serialize($settings)),
													'priority'		=> 10,
													'version'		=> $this->version,
													'enabled'		=> "y"
												)
											);
			}

		}

		$sql[] = "UPDATE exp_extensions SET version = '" . $DB->escape_str($this->version) . "' WHERE class = '" . get_class($this) . "'";

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
		
		return TRUE;
	}

	/**
	* Disables the extension the extension and deletes settings from DB
	*/
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM `exp_extensions` WHERE class = '" . get_class($this) . "'");
	}

	/**
	* Adds the custom field option to the {@link http://expressionengine.com/docs/cp/admin/weblog_administration/custom_fields_edit.html Custom Weblog Fields - Add/Edit page}.
	*
	* @param	array $data The data about this field from the database
	* @return	string $r The page content
	* @since 	Version 1.2.0
	*/
	function publish_admin_edit_field_extra_row( $data, $r )
	{
		global $EXT, $LANG, $REGX;

		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
		{
			$r = $EXT->last_call;
		}

		// -- Add the <option />
		$selected =  ($data["field_type"] == $this->type) ? " selected='selected'" : "";

		$r = preg_replace("/(<select.*?name=.field_type.*?value=.select.*?[\r\n])/is", "$1<option value='" . $REGX->form_prep($this->type) . "'" . $selected . ">" . $REGX->form_prep(strtoupper($this->type)) . "</option>\n", $r);

		// -- Set which blocks are displayed
		$items = array(
			"date_block" => "block",
			"select_block" => "none",
			"pre_populate" => "none",
			"text_block" => "none",
			"textarea_block" => "block",
			"rel_block" => "none",
			"relationship_type" => "none",
			"formatting_block" => "none",
			"formatting_unavailable" => "block",
			"direction_available" => "none",
			"direction_unavailable" => "block"
		);

		$js = "$1\n\t\telse if (id == '".$this->type."'){";
	
		foreach ($items as $key => $value)
		{
			$js .= "\n\t\t\tdocument.getElementById('" . $key . "').style.display = '" . $value . "';";
		}
		$js.= "\ndocument.field_form.field_fmt.selectedIndex = 0;\n";
		$js .= "\t\t}";

		 // -- Add the JS
		$r = preg_replace("/(id\s*==\s*.rel.*?})/is", $js, $r);

		// -- If existing field, select the proper blocks
		if(isset($data["field_type"]) && $data["field_type"] == $this->type)
		{
			foreach ($items as $key => $value)
			{
				preg_match('/(id=.' . $key . '.*?display:\s*)block/', $r, $match);

				// look for a block
				if(count($match) > 0 && $value == "none")
				{
					$r = str_replace($match[0], $match[1] . $value, $r);
				}
				elseif($value == "block")
				{ // no block matches

					preg_match('/(id=.' . $key . '.*?display:\s*)none/', $r, $match);

					if(count($match) > 0)
					{
						$r = str_replace($match[0], $match[1] . $value, $r);
					}
				}
			}
		}
		return $r;
	}

	/**
	* Renders the custom field in the publish / edit form and sets a $SESS->cache array element so we know the field has been rendered
	*
	* @param	array $row Parameters for the field from the database
	* @param	string $field_data If entry is not new, this will have field's current value
	* @return	string The custom field html
	* @since 	Version 1.2.0
	*/
	function publish_form_field_unique( $row, $field_data )
	{
		global $DSP, $EXT, $SESS;

		// -- Check if we're not the only one using this hook
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';

		if($row["field_type"] == $this->type)
		{
			$r .= $DSP->input_textarea("field_id_" . $row['field_id'], $field_data, $row['field_ta_rows'], 'lg_mceEditor', '99%');
			$r .= $DSP->input_hidden("field_ft_" . $row['field_id'], "none");
			$SESS->cache['lg'][LG_TMC_addon_id]['require_scripts'] = TRUE;
		}

		return $r;
	}

	/**
	* Takes the control panel html and adds the Moxiecode Image Manager initialisation script
	*
	* @param	string $out The control panel html
	* @return	string The modified control panel html
	* @since 	Version 1.2.0
	*/
	function show_full_control_panel_end( $out )
	{
		global $DB, $EXT, $IN, $REGX, $SESS;

		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$out = $EXT->last_call;
			
		// if we are displaying the custom field list
		if(
			$IN->GBL('M', 'GET') == 'blog_admin' && (
				$IN->GBL('P', 'GET') == 'field_editor' ||
				$IN->GBL('P', 'GET') == 'update_weblog_fields' ||
				$IN->GBL('P', 'GET') == 'update_field_order' 
			) ||
			$IN->GBL('P', 'GET') == 'delete_field'
		)
		{
			// get the table rows
			if(preg_match_all("/C=admin&amp;M=blog_admin&amp;P=edit_field&amp;field_id=(\d*).*?<\/td>.*?<td.*?>.*?<\/td>.*?<\/td>/is", $out, $matches))
			{
				// for each field id
				foreach($matches[1] as $key=>$field_id)
				{
					// get the field type
					$query = $DB->query("SELECT field_type FROM exp_weblog_fields WHERE field_id='" . $DB->escape_str($field_id) . "' LIMIT 1");

					// if the field type is wysiwyg
					if($query->row["field_type"] == $this->type)
					{
						$out = preg_replace("/(C=admin&amp;M=blog_admin&amp;P=edit_field&amp;field_id=" . $field_id . ".*?<\/td>.*?<td.*?>.*?<\/td>.*?)<\/td>/is", "$1" . $REGX->form_prep(strtoupper($this->type)) . "</td>", $out);
					}
				}
			}
		}
		if(
			// we haven't already included the script
			isset($SESS->cache['lg'][LG_TMC_addon_id]['scripts_included']) === FALSE &&
			// AND a LG Image Manager field has been rendered
			isset($SESS->cache['lg'][LG_TMC_addon_id]['require_scripts']) === TRUE &&
			// AND its a publish or an edit page
			($IN->GBL('C', 'GET') == 'publish' || $IN->GBL('C', 'GET') == 'edit')
		)
		{
			$r = "";
			// if we have gzip enabled
			if($this->settings['enable_gzip'] == 'y')
			{
				// render the gzip init
				$settings_parts = implode("\n\t\t", preg_split("/(\r\n|\n|\r)/", trim($this->settings['gzip_script_config'])));
				$r .= "\n" . '<script type="text/javascript" src="' . trim($this->settings['gzip_script_path']) . '"></script>';
				$r .= "\n" . '
<script type="text/javascript">
	//<![CDATA[
	tinyMCE_GZ.init({'.$settings_parts.'});
	//]]>
</script>';
			}
			// else add the normal tinymce script
			else
			{
				$r .= "\n" . '<script type="text/javascript" src="' . trim($this->settings['script_path']) . '"></script>';
			}
			$settings_parts = implode("\n\t\t", preg_split("/(\r\n|\n|\r)/", trim($this->settings['script_config'])));
			
			// render the tinymce init
			$r .= "\n" . '
<script type="text/javascript">
//<![CDATA[
	tinyMCE.init({'.$settings_parts.'});
//]]>
</script>';

			// add the script string before the closing head tag
			$out = str_replace("</head>", $r . "</head>", $out);
			// make sure we don't add it again
			$SESS->cache['lg'][LG_TMC_addon_id]['scripts_included'] = TRUE;
		}

		return $out;
	}

	/**
	* Returns the extension settings from the DB
	*
	* @access	private
	* @param	bool	$force_refresh	Force a refresh
	* @param	bool	$return_all		Set the full array of settings rather than just the current site
	* @return	array					The settings array
	* @since 	Version 1.3.0
	*/
	function _get_settings( $force_refresh = FALSE, $return_all = FALSE )
	{
		global $SESS, $DB, $REGX, $LANG, $PREFS;

		// assume there are no settings
		$settings = FALSE;

		// Get the settings for the extension
		if(isset($SESS->cache['lg'][LG_TMC_addon_id]['settings']) === FALSE || $force_refresh === TRUE)
		{
			// check the db for extension settings
			$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = '" . LG_TMC_extension_class . "' LIMIT 1");

			// if there is a row and the row has settings
			if ($query->num_rows > 0 && $query->row['settings'] != '')
			{
				// save them to the cache
				$SESS->cache['lg'][LG_TMC_addon_id]['settings'] = $REGX->array_stripslashes(unserialize($query->row['settings']));
			}
		}

		// check to see if the session has been set
		// if it has return the session
		// if not return false
		if(empty($SESS->cache['lg'][LG_TMC_addon_id]['settings']) !== TRUE)
		{
			if($return_all === TRUE)
			{
				$settings = $SESS->cache['lg'][LG_TMC_addon_id]['settings'];
			}
			else
			{
				if(isset($SESS->cache['lg'][LG_TMC_addon_id]['settings'][$PREFS->ini('site_id')]) === TRUE)
				{
					$settings = $SESS->cache['lg'][LG_TMC_addon_id]['settings'][$PREFS->ini('site_id')];
				}
				else
				{
					$settings = $this->_build_default_settings();
				}
			}
		}

		return $settings;

	}

	/**
	* Register a new Addon Source
	*
	* @param	array $sources The existing sources
	* @return	array The new source list
	* @since 	Version 2.0.0
	*/
	function lg_addon_update_register_source( $sources )
	{
		global $EXT;
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$sources = $EXT->last_call;

		// add a new source
		// must be in the following format:
		/*
		<versions>
			<addon id='LG Social Bookmarks' version='2.0.0' last_updated="1218852797" docs_url="http://leevigraham.com/" />
		</versions>
		*/
		if($this->settings['check_for_updates'] == 'y')
		{
			$sources[] = 'http://leevigraham.com/version-check/versions.xml';
		}

		return $sources;

	}

	/**
	* Register a new Addon
	*
	* @param	array $addons The existing sources
	* @return	array The new addon list
	* @since 	Version 2.0.0
	*/
	function lg_addon_update_register_addon( $addons )
	{
		global $EXT;
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$addons = $EXT->last_call;

		// add a new addon
		// the key must match the id attribute in the source xml
		// the value must be the addons current version
		if($this->settings['check_for_updates'] == 'y')
		{
			$addons[LG_TMC_addon_id] = $this->version;
		}

		return $addons;
	}

	/**
	* Debug
	*
	* @access	private 
	* @param	mixed 	$obj 	The data
	* @param	bool	$exit	Exit after the method has been called
	* @param	bool	$ret	Return the text from the method rather than print it
	* @param	string	$msg	A message outputted added to the output
	* @return	void
	* @since 	Version 1.3.0
	*/
	function _debug( $obj, $exit = TRUE, $ret = FALSE, $msg = '' )
	{
		$r = "<h2>" . $msg . "</h2>\n<pre>" . ((is_string($obj) === FALSE) ? htmlentities(print_r($obj, TRUE)) : htmlentities($obj)) . "</pre>\n";
		if($ret !== FALSE)
		{
			return $r;
		}
		else
		{
			print $r;
		}
		if($exit === TRUE) exit;
	}
}

?>