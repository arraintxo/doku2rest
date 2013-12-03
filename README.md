doku2rest
=========

This plugin allows exporting Dokuwiki pages to ReStructuredText documents.

Installing
----------

Just copy the full directory to DOCUWIKI_PATH/inc/plugins/doku2rest

Usage
-----

To make a single page exportable you can add the following macro to the page:

    {{DOKU2REST}}
    
To add a export link in the wiki, add the next line in any template document
  
    <?php echo p_render('xhtml', p_get_instructions('{{DOKU2REST}}'), $info) ?>
