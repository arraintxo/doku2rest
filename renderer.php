<?php
/**
 * DokuWiki Plugin doku2rest (Renderer Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Alayn Gortazar <zutoin@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

require_once DOKU_INC.'inc/parser/renderer.php';

class renderer_plugin_doku2rest extends Doku_Renderer {
    var $doc = '';
    var $store = '';
    var $listType = '- ';
    var $links = array();
    var $footnotes = array();
    var $table = array();
    var $tableheaders = array();
    var $row = array();
    var $quoteLevel = 0;

    var $lastHeaderLevel = 0;
    
    public function __construct()
    {
        $this->nocache();
    }

    /**
     * The format this renderer produces
     */
    public function getFormat(){
        return 'rest';
    }

    function document_start() {
        global $ID;
        $headers = array(
            'Content-Type' => 'text/x-rst',
            'Content-Disposition' => 'attachment; filename="'.noNS($ID).'.rst";'
        );
        p_set_metadata($ID, array('format' => array('doku2rest' => $headers) ));
    }

    function document_end() {
        foreach ($this->links as $title => $link) {
            $this->doc .= '.. _`' . $title . '`: ' . $link . DOKU_LF;
        }
    }
    function render_TOC() { return ''; }

    function toc_additem($id, $text, $level) {}

    function header($text, $level, $pos) {
        $levelChars = array('=', '-', '`', ':', '.', '\'', '"', '~', '^', '_', '*', '+', '#');
        if ($level - $this->lastHeaderLevel > 1) {
            $level = $this->lastHeaderLevel + 1;
        }
        $levelCharsSize = sizeof($levelChars);
        if ($level > $levelCharsSize) {
            $level = $levelCharsSize;
        }

        $this->lastHeaderLevel = $level;
        
        $length = mb_strlen($text, 'utf8');
        $headerLine = str_repeat($levelChars[$level - 1], $length);
        $this->doc .= $text . DOKU_LF . $headerLine . DOKU_LF; 
    }

    function section_open($level) {}

    function section_close() {
        $this->doc .= DOKU_LF;
    }

    function cdata($text) {
        $this->doc .= $text;
    }

    function p_open() {
//        $this->doc .= DOKU_LF;
    }

    function p_close() {
        $this->doc .= DOKU_LF;
    }

    function linebreak() {
        $this->doc .= DOKU_LF;
    }

    function hr() {
        $this->doc .= DOKU_LF . "-----" . DOKU_LF;
    }

    function strong_open() {
        $this->doc .= '**';
    }

    function strong_close() {
        $this->doc .= '**';
    }

    function emphasis_open() {
        $this->doc .= '*';
    }

    function emphasis_close() {
        $this->doc .= '*';
    }

    function underline_open() {
        $this->doc .= '*';
    }

    function underline_close() {
        $this->doc .= '*';
    }

    function monospace_open() {
        $this->doc .= '``';
    }

    function monospace_close() {
        $this->doc .= '``';
    }

    function subscript_open() {
        $this->doc .= '\ :sub:`';
    }

    function subscript_close() {
        $this->doc .= '`\ ';
    }

    function superscript_open() {
        $this->doc .= '\ :sub:`';
    }

    function superscript_close() {
        $this->doc .= '`\ ';
    }

    function deleted_open() {}

    function deleted_close() {}

     /**
     * Callback for footnote start syntax
     *
     * All following content will go to the footnote instead of
     * the document. To achieve this the previous rendered content
     * is moved to $store and $doc is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnote_open() {

        // move current content to store and record footnote
        $this->store = $this->doc;
        $this->doc   = '';
    }

    /**
     * Callback for footnote end syntax
     *
     * All rendered content is moved to the $footnotes array and the old
     * content is restored from $store again
     *
     * @author Andreas Gohr
     */
    function footnote_close() {

        // recover footnote into the stack and restore old content
        $footnote = $this->doc;
        $this->doc = $this->store;
        $this->store = '';

        // check to see if this footnote has been seen before
        $i = array_search($footnote, $this->footnotes);

        if ($i === false) {
            // its a new footnote, add it to the $footnotes array
            $id = count($this->footnotes)+1;
            $this->footnotes[count($this->footnotes)] = $footnote;
        } else {
            // seen this one before, translate the index to an id and save a placeholder
            $i++;
            $id = count($this->footnotes)+1;
            $this->footnotes[count($this->footnotes)] = "@@FNT".($i);
        }

        // output the footnote reference and link
        $this->doc .= '[' . $id . ']_';
    }

    function listu_open() {
        $this->doc .= DOKU_LF;
        $this->listType = '-';
    }

    function listu_close() {
        $this->doc .= DOKU_LF;
    }

    function listo_open() {
        $this->doc .= DOKU_LF;
        $this->listType = '-';
    }

    function listo_close() {
        $this->doc .= DOKU_LF;
    }

    function listitem_open($level) {
        $this->doc .= str_repeat(' ', $level - 1) . $this->listType;
    }

    function listitem_close() {}

    function listcontent_open() {
//        $this->doc .= DOKU_LF;
    }

    function listcontent_close() {
        $this->doc .= DOKU_LF;
    }

    function unformatted($text) {
        $this->doc .= DOKU_LF . "::" . DOKU_LF . DOKU_LF;
        $this->doc .= $this->_indent_text($text);
        $this->doc .= DOKU_LF;
    }
    
    function _indent_text($text, $level = 1)
    {
        $indented_text = '';
        $lines = explode(DOKU_LF, $text);
        foreach ($lines as $line) {
            $indented_text .= str_repeat(' ', $level * 3) . $line . DOKU_LF;
        }
        
        return $indented_text;
    }

    function php($text) {}

    function phpblock($text) {}

    function html($text) {}

    function htmlblock($text) {}

    function preformatted($text) {
        $this->unformatted($text);
    }

    function quote_open() {
        $this->quoteLevel++;
        $this->doc .= str_repeat(' ', 3);
    }

    function quote_close() {
        $this->quoteLevel--;
        if (!$this->quoteLevel) {
            $this->doc .= DOKU_LF;
            $this->doc .= DOKU_LF;
        }
    }

    function file($text, $lang = null, $file = null ) {
        $this->code($text, $lang, $file);
    }

    function code($text, $lang = null, $file = null ) {
        if (is_null($lang)) {
            $lang = 'none';
        }
        $this->doc .= DOKU_LF . ".. code-block:: " . $lang . DOKU_LF . DOKU_LF;
        $this->doc .= $this->_indent_text($text);
//        $this->doc .= DOKU_LF;
    }

    function acronym($acronym) {
        $this->doc .= $acronym;
    }

    function smiley($smiley) {
        $this->doc .= $acronym;
    }

    function wordblock($word) {
        $this->doc .= $word;
    }

    function entity($entity) {
        $this->doc .= $entity;        
    }

    // 640x480 ($x=640, $y=480)
    function multiplyentity($x, $y) {}

    function singlequoteopening() {
        $this->doc .= '\\\'';
    }

    function singlequoteclosing() {
        $this->doc .= '\\\'';
    }

    function apostrophe() {
        $this->doc .= '\\\'';
    }

    function doublequoteopening() {
        $this->doc .= '\\"';
    }

    function doublequoteclosing() {
        $this->doc .= '\\"';
    }

    // $link like 'SomePage'
    function camelcaselink($link) {
    }

    function link($type, $link, $name = NULL) {
        if (is_null($name)) {
            $this->doc .= ':' . $type . ':`' . $link . '`';
        } else {
            $this->doc .= ':' . $type . ':`' . $name . ' <' . $link. '>`';
        }
    }
    
    function locallink($hash, $name = NULL) {
        $this->link('ref', $hash, $name);
    }

    // $link like 'wiki:syntax', $title could be an array (media)
    function internallink($link, $title = NULL) {
        $restLink = '/' . str_replace(':', '/', $link);
        $this->link('doc', $restLink, $title);
    }

    // $link is full URL with scheme, $title could be an array (media)
    function externallink($link, $title = NULL) {
        if (is_null($title)) {
            $this->doc .= $link;
        } else if (is_string($title)) {        
            $this->doc .= '`' . $title . '`_';
            $this->links[$title] = $link;
        }
    }

    function rss ($url,$params) {
        $this->externallink($url);
    }

    // $link is the original link - probably not much use
    // $wikiName is an indentifier for the wiki
    // $wikiUri is the URL fragment to append to some known URL
    function interwikilink($link, $title = NULL, $wikiName, $wikiUri) {}

    // Link to file on users OS, $title could be an array (media)
    function filelink($link, $title = NULL) {
        $this->link('download', $link, $title);
    }

    // Link to a Windows share, , $title could be an array (media)
    function windowssharelink($link, $title = NULL) {}

//  function email($address, $title = NULL) {}
    function emaillink($address, $name = NULL) {}

    function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {}

    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {}

    function internalmedialink (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {}

    function externalmedialink(
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {}

    function table_open($maxcols = null, $numrows = null, $pos = null){
        $this->doc .= DOKU_LF . ".. csv-table::" . DOKU_LF;
        $this->doc .= "   :escape: \\" . DOKU_LF;
        $this->store = $this->doc;
        $this->doc = '';

        $this->table = array();
        $this->tableheaders = array();
    }

    function table_close($pos = null){
        $this->doc = $this->store;
        $this->store = '';
 
        if (sizeof($this->tableheaders)) {
            $fp = fopen('php://temp/', 'w+');
            fputcsv($fp, $this->tableheaders);
            rewind($fp);
            $this->doc .= $this->_indent_text(':header: ' . stream_get_contents($fp));
            fclose($fp);
            $this->tableheaders = array();
        }
        
        if (sizeof($this->table)) {
            $this->doc .= DOKU_LF;
            $fp = fopen('php://temp/', 'w+');
            foreach($this->table as $row) {
                fputcsv($fp, $row);
            }
            rewind($fp);
            $this->doc .= $this->_indent_text(stream_get_contents($fp)) . DOKU_LF;
            fclose($fp);
            $this->table = array();
        }

        $this->doc .= DOKU_LF;
   }

    function tablerow_open(){
    }

    function tablerow_close(){
        if (sizeof($this->row)) {
            $this->table[] = $this->row;
            $this->row = array();
        }
    }

    function tableheader_open($colspan = 1, $align = NULL, $rowspan = 1){
    }

    function tableheader_close(){
        $this->tableheaders[] = $this->doc;
        $this->doc = '';
    }

    function tablecell_open($colspan = 1, $align = NULL, $rowspan = 1){
    }

    function tablecell_close(){
        $this->row[] = $this->doc;
        $this->doc = '';
    }
}


//Setup VIM: ex: et ts=4 :
