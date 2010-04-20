<?php if (!defined('FARI')) die();

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Template for array of data backup.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Backup
 */
abstract class Fari_BackupTemplate {

    /**
     * Returns an array of items to export.
     * @return array
     */
    abstract function items($parameters);



    /********************* database backup *********************/



    /**
     * Template for converting an array of db data into XML.
     * @return XML
     */
	public function dbToXML($parameters) {
        // dom string
 		$DOMDocument = new DOMDocument('1.0', 'UTF-8');

        // fetch the data array
 		$items = $this->items($parameters);

        // check we actually have an array
        try { if (!is_array($items)) {
            throw new Fari_Exception('Fari_Backup expects an array of items.'); }
        } catch (Fari_Exception $exception) { $exception->fire(); }
                
        // <table> root element
 		$table = $DOMDocument->appendChild($DOMDocument->createElement('table'));
 		
		// traverse through all records
 		foreach ($items as $item) {
            // get array keys of the item
            // we could explode $columns as well if they are passed
			$keys = array_keys($item);
                        
			// <table><row> elemenent we will always have
	 		$row = $table->appendChild($DOMDocument->createElement('row'));
                        
            // traverse through keys/columns
	 		foreach ($keys as $column) {
                // <table><row><column> value, escaped
				$row->appendChild($DOMDocument->createElement($column, Fari_Escape::xml($item[$column])));
            }
 		}
                
        // generate XML and return
 		$DOMDocument->formatOutput = TRUE;
 		return $DOMDocument->saveXML();
    }
    
}