<?php
/* Copyright (C) 2004-2014	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin				<regis.houssin@inodbox.com>
 * Copyright (C) 2008		Raphael Bertrand			<raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2014	Juanjo Menent				<jmenent@2byte.es>
 * Copyright (C) 2012		Christophe Battarel			<christophe.battarel@altairis.fr>
 * Copyright (C) 2012		Cédric Salvador				<csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014	Raphaël Doursenaud			<rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015		Marcos García				<marcosgdf@gmail.com>
 * Copyright (C) 2017		Ferran Marcet				<fmarcet@2byte.es>
 * Copyright (C) 2018-2024	Frédéric France				<frederic.france@free.fr>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024		Alexandre Spangaro			<alexandre@inovea-conseil.com>
 * Copyright (C) 2025		Jonathan Miller || Moko Consulting				<dev@mokoconsulting.tech>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *  \file       htdocs/custom/mokodymolabels/core/modules/product/doc/pdf_asset_dymo30336.modules.php
 *  \ingroup    MokoDymoLables
 *  \brief      File of class to generate document label template
 */

dol_include_once('/mokodymolabels/core/modules/product/modMokoDymoLabels.class.php');

require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/barcode/modules_barcode.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/barcode.lib.php'; // This is to include def like $genbarcode_loc and $font_loc

/**
 *	Class to manage PDF template standard_product
 */
class pdf_asset_dymo30336 extends ModelePDFProduct
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var int The environment ID when using a multicompany module
	 */
	public $entity;

	/**
	 * @var string model name
	 */
	public $name;

	/**
	 * @var string model description (short text)
	 */
	public $description;

	/**
	 * @var int     Save the name of generated file as the main doc when generating a doc with this template
	 */
	public $update_main_doc_field;

	/**
	 * @var string document type
	 */
	public $type;

	/**
	 * @var array{0:int,1:int} Minimum version of PHP required by module.
	 * e.g.: PHP ≥ 7.0 = array(7, 0)
	 */
	public $phpmin = array(7, 0);

	/**
	 * Dolibarr version of the loaded document
	 * @var string Version, possible values are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'''|'development'|'dolibarr'|'experimental'
	 */
	public $version = 'dolibarr';

	/**
	 * Issuer
	 * @var Societe Object that emits
	 */
	public $emetteur;

	/**
	 * @var array<string,array{rank:int,width:float|int,status:bool,title:array{textkey:string,label:string,align:string,padding:array{0:float,1:float,2:float,3:float}},content:array{align:string,padding:array{0:float,1:float,2:float,3:float}}}>	Array of document table columns
	 */
	public $cols;


	/**
	 *	Constructor
	 *
	 *	@param	DoliDB	$db      Database handler
	 */
	public function __construct($db)
	{
		global $langs, $mysoc;

		// Translations
		$langs->loadLangs(array("main", "product","printlabels@printlabels"));

		$this->db = $db;
		$this->name = "asset_dymo30336";
		$this->description = "Asset label for Dymo 30336-Small Multi-Purpose";
		$this->update_main_doc_field = 1; // Save the name of generated file as the main doc when generating a doc with this template

		// Dimension page
		$this->type = 'pdf';
		//$formatarray = pdf_getFormat();
		$this->page_width = 25;
		$this->page_height = 54;
		$this->format = array($this->page_width, $this->page_height);
		$this->metric = 'mm';
		$this->orientation = 'L';
		$this->margin_left = 2;
		$this->margin_right = 2;
		$this->margin_top = 2;
		$this->margin_bottom = 2;
		$this->padding = 2;

		$this->tabTitleHeight = 2; // default height

		$this->content_width = $this->page_width-$this->margin_left-$this->margin_right;

		$this->default_font_size=$default_font_size-5;
		$this->title_height = 4;
		$this->description_height = 6;
		$this->ref_height = 3;
		$this->barcode_width = $this->page_width-$this->margin_right-$this->margin_left-10;

		if ($mysoc === null) {
			dol_syslog(get_class($this).'::__construct() Global $mysoc should not be null.'. getCallerInfoString(), LOG_ERR);
			return;
		}

		// Get source company
		$this->emetteur = $mysoc;
		if (empty($this->emetteur->country_code)) {
			$this->emetteur->country_code = substr($langs->defaultlang, -2); // By default, if was not defined
		}
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Function to build and write pdf to disk
	 *
	 *  @param	Product	$object				Source object to generate document from
	 *  @param	Translate	$outputlangs		Lang output object
	 *  @param	string		$srctemplatepath	Full path of source filename for generator using a template file
	 *  @param	int<0,1>	$hidedetails		Do not show line details
	 *  @param	int<0,1>	$hidedesc			Do not show desc
	 *  @param	int<0,1>	$hideref			Do not show ref
	 *  @return	int<-1,1>						1 if OK, <=0 if KO
	 */
	public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		// phpcs:enable
		global $user, $langs, $conf, $mysoc, $hookmanager, $nblines;
if (!is_object($outputlangs)) {
			$outputlangs = $langs;
		}
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!empty($conf->global->MAIN_USE_FPDF)) {
			$outputlangs->charset_output = 'ISO-8859-1';
		}

		// Load traductions files required by page
		$outputlangs->loadLangs(array("main", "dict", "companies", "bills", "products", "orders", "deliveries"));

		if (is_array($object->lines)) {
			$nblines = count($object->lines);
		} else {
			$nblines = 0;
		}

		if ($conf->product->dir_output) {
			// Definition of $dir and $file
			if ($object->specimen) {
				$dir = $conf->product->dir_output;
				$file = $dir."/".$this->name."_".$objectref.".pdf";
			} else {
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->product->dir_output."/".$objectref;
				$file = $dir."/".$this->name."_".$objectref.".pdf";
			}

			if (!file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
					return -1;
				}
			}

			if (file_exists($dir)) {
				// Add pdfgeneration hook
				if (!is_object($hookmanager)) {
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager = new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

				// Create pdf instance
				$pdf = pdf_getInstance($this->format, $this->metric, $this->orientation);
				$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
				$pdf->SetAutoPageBreak(1, 0);

				if (class_exists('TCPDF')) {
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (!empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				$pdf->Open();

				$pagenb = 0;
			$pdf->SetDrawColor(0);
			$pdf->SetTextColor(0);
			$pdf->SetFillColor(0);
				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Asset"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Asset")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
				if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) {
					$pdf->SetCompression(false);
				}

				$pdf->SetMargins($this->margin_left, $this->margin_top, $this->margin_right); // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (!empty($tplidx)) {
					$pdf->useTemplate($tplidx);
				}
				$pagenb++;

	if($object->barcode){
		$pdf->write2DBarcode(
	    $object->barcode, // The data to encode
	    'DATAMATRIX', // Barcode type
	    $this->margin_left, // X position (left margin)
	    $this->margin_top, // Y position (top margin)
	    $this->barcode_width, // Width of the barcode
	    $this->barcode_width+$this->padding // Height of the barcode (same as width for square)
		);
		$pdf->SetFont('', 'I', $default_font_size - ($this->padding*2));
		$pdf->MultiCell(
			$this->barcode_width, // Width (0 = full available width)
			$this->ref_height, // Cell height for REF
			$outputlangs->transnoentities($object->barcode), // Translated object reference (SKU or product ref)
			0, // No border
			'C', // Center alignment
			null, // No background fill
			0, // Move to next line after
			$this->margin_left, // X position (null = current)
			$this->margin_top+$this->barcode_width+$this->padding, // Y position (null = current)
			true, // Reseth (reset the last cell height)
			1, // Stretch (adjust font size if needed)
			false, // Ignore minimum cell height
			true, // Calign (align text considering RTL/language flow)
			0, // No vertical padding
			'M', // Vertical alignment: Middle
			true // Auto padding
		);
	}
	//title
		$pdf->SetTextColor(255);

		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->MultiCell(
			0, // Width (0 = full available width)
			$this->title_height, // Cell height
			$outputlangs->transnoentities("PROPERTYOF"), // Translated string without entity encoding
			true, // Border
			'C', // Center alignment
			true, // Fill background
			1, // Move to next line after
			$this->margin_left+$this->barcode_width+$this->padding, // X position (null = current)
			$this->margin_top, // Y position (null = current)
			true, // Reseth (reset the last cell height)
			1, // Stretch (adjust font size if needed)
			false, // Ignore minimum cell height
			true, // Calign (align text considering RTL/language flow)
			0, // No vertical padding
			'M', // Vertical alignment: Middle
			true // Auto padding
		);

//text reset
			$pdf->SetTextColor(0);

//Company Information
		$pdf->SetFont('', 'B', $default_font_size-$this->padding);
		$pdf->MultiCell(
	    0, // Width (0 = extend to right margin)
	    $this->description_height, // Height
	    strtoupper($this->emetteur->name) . "\n" . strtoupper($this->emetteur->phone), // Content with line break
	    true, // Border
	    'C', // Center alignment
	    null, // No background fill
	    1, // Move to next line after
	    $this->margin_left+$this->barcode_width+$this->padding, // X
	    $this->margin_top+$this->title_height, // Y
	    false, // Reseth (reset the last cell height)
	    1, // Stretch (1 = adjust font size to fit)
	    false, // Ignore minimum height
	    true, // Calign (vertical alignment)
	    0, // Valign padding
	    'M', // Vertical alignment: Middle
	    true // Auto padding
		);

	//Ref
		$pdf->SetFont(
		    '', // Font family (empty = default)
		    'B', // Font style (empty = regular)
		    $default_font_size - $this->padding // Font size reduced by padding value
		);
		$pdf->SetTextColor(255);
		$pdf->MultiCell(
	    $this->padding*5, // Width (0 = extend to right margin)
	    $this->description_height, // Height
	    strtoupper($outputlangs->transnoentities('REF')),
	    true, // Border
	    'C', // Center alignment
	    true, // Background fill
	    0, // Move to next line after
	    $this->margin_left+$this->barcode_width+$this->padding, // X
	    $this->margin_top+$this->title_height+$this->description_height, // Y
	    true, // Reseth (reset the last cell height)
	    1, // Stretch (1 = adjust font size to fit)
	    false, // Ignore minimum height
	    true, // Calign (vertical alignment)
	    0, // Valign padding
	    'M', // Vertical alignment: Middle
	    true // Auto padding
		);
			$pdf->SetFont(
			    '', // Font family (empty = default)
			    '', // Font style (empty = regular)
			    $default_font_size - $this->padding // Font size reduced by padding value
			);
			$pdf->SetTextColor(0);

			$pdf->MultiCell(
	    0, // Width (0 = extend to right margin)
	    $this->description_height, // Height
	    strtoupper($outputlangs->transnoentities($object->ref)),
	    true, // Border
	    'C', // Center alignment
	    false, // Background fill
	    0, // Move to next line after
	    $this->margin_left+$this->barcode_width+($this->padding*6), // X
	    $this->margin_top+$this->title_height+$this->description_height, // Y
	    true, // Reseth (reset the last cell height)
	    1, // Stretch (1 = adjust font size to fit)
	    false, // Ignore minimum height
	    true, // Calign (vertical alignment)
	    1, // Valign padding
	    'M', // Vertical alignment: Middle
	    true // Auto padding
		);
//LAel
		$pdf->SetFont(
		    '', // Font family (empty = default)
		    'B', // Font style (empty = regular)
		    $default_font_size - $this->padding // Font size reduced by padding value
		);
		$pdf->SetTextColor(255);
		$pdf->MultiCell(
	    $this->barcode_width+$this->padding, // Width (0 = extend to right margin)
	    $this->description_height, // Height
	    strtoupper($outputlangs->transnoentities('LABEL')),
	    true, // Border
	    'C', // Center alignment
	    true, // Background fill
	    0, // Move to next line after
	    $this->margin_left, // X
	    $this->margin_top+$this->title_height+$this->description_height+$this->description_height, // Y
	    true, // Reseth (reset the last cell height)
	    1, // Stretch (1 = adjust font size to fit)
	    false, // Ignore minimum height
	    true, // Calign (vertical alignment)
	    0, // Valign padding
	    'M', // Vertical alignment: Middle
	    true // Auto padding
		);
			$pdf->SetFont(
			    '', // Font family (empty = default)
			    '', // Font style (empty = regular)
			    $default_font_size - $this->padding // Font size reduced by padding value
			);
			$pdf->SetTextColor(0);

			$pdf->MultiCell(
	    0, // Width (0 = extend to right margin)
	    $this->description_height, // Height
	    dol_htmlentitiesbr($object->label),
	    true, // Border
	    'C', // Center alignment
	    false, // Background fill
	    0, // Move to next line after
	    $this->margin_left+$this->barcode_width+$this->padding, // X
	    $this->margin_top+$this->title_height+$this->description_height+$this->description_height, // Y
	    true, // Reseth (reset the last cell height)
	    1, // Stretch (1 = adjust font size to fit)
	    false, // Ignore minimum height
	    true, // Calign (vertical alignment)
	    1, // Valign padding
	    'M', // Vertical alignment: Middle
	    true // Auto padding
		);
		//

				$pdf->Close();

				$pdf->Output($file, 'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file' => $file, 'object' => $object, 'outputlangs' => $outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0) {
					$this->error = $hookmanager->error;
					$this->errors = $hookmanager->errors;
				}

				dolChmod($file);

				$this->result = array('fullpath' => $file);

				return 1; // No error
			} else {
				$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		} else {
			$this->error = $langs->transnoentities("ErrorConstantNotDefined", "FAC_OUTPUTDIR");
			return 0;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return list of active generation modules
	 *
	 *  @param  DoliDB  	$db					Database handler
	 *  @param  int<0,max>	$maxfilenamelength	Max length of value to show
	 *  @return string[]|int<-1,0>				List of templates
	 */
	public static function liste_modeles($db, $maxfilenamelength = 0)
	{
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *	Show table for lines
	 *
	 *	@param	TCPDF|TCPDI	$pdf     		Object PDF
	 *	@param	float		$tab_top		Top position of table
	 *	@param	float		$tab_height		Height of table (rectangle)
	 *	@param	int			$nexY			Y (not used)
	 *	@param	Translate	$outputlangs	Langs object
	 *	@param	int<-1,1>	$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *	@param	int<0,1>	$hidebottom		Hide bottom bar of array
	 *	@param	string		$currency		Currency code
	 *	@param	?Translate	$outputlangsbis	Langs object bis
	 *	@return	void
	 */
	protected function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0, $currency = '', $outputlangsbis = null)
	{
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *	@param	TCPDF|TCPDI	$pdf     		Object PDF
	 *	@param	Product	$object     	Object to show
	 *	@param	int<0,1>	$showaddress    0=no, 1=yes
	 *	@param	Translate	$outputlangs	Object lang for output
	 *	@param	?Translate	$outputlangsbis	Object lang for output bis
	 *	@return	float|int                   Return topshift value
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $outputlangsbis = null)
	{
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *	Show footer of page. Need this->emetteur object
	 *
	 *	@param	TCPDI|TCPDF		$pdf     		PDF
	 *	@param	CommonObject	$object			Object to show
	 *	@param	Translate		$outputlangs	Object lang for output
	 *	@param	int<0,1>		$hidefreetext	1=Hide free text
	 *	@return	int<0,1>						Return height of bottom margin including footer text
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		global $conf;
		$showdetails = !getDolGlobalInt('MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS') ? 0 : getDolGlobalInt('MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS');
		return pdf_pagefoot($pdf, $outputlangs, 'INVOICE_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext);
	}

	/**
	 *	Define Array Column Field
	 *
	 *	@param	CommonObject	$object    		common object
	 *	@param	Translate		$outputlangs    langs
	 *	@param	int<0,1>		$hidedetails	Do not show line details
	 *	@param	int<0,1>		$hidedesc		Do not show desc
	 *	@param	int<0,1>		$hideref		Do not show ref
	 *	@return	void
	 */
	public function defineColumnField($object, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{}
}
