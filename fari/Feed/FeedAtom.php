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
 * Create an Atom feed.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Feed
 */
class Fari_FeedAtom {

    /** @var XML feed */
    private $feed;
    private $dom;

    /**
     * Construct an atom feed.
     * @param string $title of the feed
     */
    public function __construct($title) {
        $feed = simplexml_load_string('<feed xml:lang="en-US" xmlns="http://www.w3.org/2005/Atom">
            <link rel="self"/><title type="html"/></feed>');

        $feed->title = $title;
        $feed->link['href'] = $feed->id = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $feed->updated = date(DATE_ATOM);

        $this->feed = dom_import_simplexml($feed);

        $this->dom = $this->feed->ownerDocument;
    }

    /**
     * Append items to the feed.
     * @param array $items nees to contain content and title keys
     */
    public function atomise(array $items) {
        foreach ($items as $id => $item) {
            $entry = simplexml_load_string('<entry><link rel="alternate" type="text/html"/><title type="html"/>
                <content type="html"/></entry>');

            $entry->link['href'] = $entry->id = $id;
            assert('array_key_exists("title", $item); // each item needs to have a \'title\' key');
            $entry->title = $item['title'];
            assert('array_key_exists("content", $item); // each item needs to have a \'content\' key');
            $entry->content = $item['content'];

            $this->feed->appendChild($this->dom->importNode(dom_import_simplexml($entry), true));
        }
    }

    /**
     * Echo the feed with appropriate header.
     */
    public function generate() {
        header('Content-Type: application/atom+xml; charset=utf-8');
        echo $this->dom->saveXML();
    }

}