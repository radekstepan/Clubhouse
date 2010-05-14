<?php

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Load appropriate language file.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationI18n extends Fari_File {

    /**#@+ directory with language locale */
	const LOCALE_DIR = 'config/locales';
    /**#@-*/

    /**
     * Sanity check.
     */
    public function __construct() {
        assert('defined(\'BASEPATH\'); // base path not defined');
    }

    /**
     * Return array with translations.
     * @param string $language code
     * @return array or FileNotFoundException
     */
    public function getTranslations($language) {
        assert('!empty($language); // provide language name');
        $yml = new Fari_FileYaml();
        $result = $yml->toArray(self::LOCALE_DIR . "/{$language}.yml");

        // array(['en']=>array('hello' => 'Hello!');
        assert('is_array($result[$language]); // malformed locale file, root element needs to be language key');
        return $result[$language];
    }

    /**
     * Returns 'installed' locales
     * @return array of installed locales
     */
    public function getLocales() {
        sort($result = $this->listDirectory(self::LOCALE_DIR));
        return $result;
    }

    /**
     * Check if locale is installed.
     * @param string $language code
     * @return true if so is, but parse check not performed!
     */
    public function isLocale($language) {
        assert('!empty($language); // provide language name');
        return in_array($language, $this->getLocales());
    }

    /*
     * Overwriting parent to do some magic.
     * @param array $listing
     * @param DirectoryIterator $item
     */
    public function pushItem(&$listing, DirectoryIterator $item) {
        // dot would be for morse code locale?
        if (!$item->isDot() && $item->isFile()) {
            // explode and extract name & type
            list ($name, $type) = explode('.', $item->getFilename());
            assert('!empty($name) && !empty($type); // invalid locale files');
            // push name to stack
            if ($type == 'yml') array_push($listing, $name);
        }
    }

}