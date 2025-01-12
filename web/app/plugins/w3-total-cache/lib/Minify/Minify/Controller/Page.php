<?php
namespace W3TCL\Minify;
/**
 * Class Minify_Controller_Page
 *
 * @package Minify
 */

/**
 * Controller class for serving a single HTML page
 *
 * @link    http://code.google.com/p/minify/source/browse/trunk/web/examples/1/index.php#59
 * @package Minify
 * @author  Stephen Clay <steve@mrclay.org>
 */
class Minify_Controller_Page extends Minify_Controller_Base
{

    /**
     * Set up source of HTML content
     *
     * @param  array $options controller and Minify options
     * @return array Minify options
     *
     * Controller options:
     *
     * 'content': (required) HTML markup
     *
     * 'id': (required) id of page (string for use in server-side caching)
     *
     * 'lastModifiedTime': timestamp of when this content changed. This
     * is recommended to allow both server and client-side caching.
     *
     * 'minifyAll': should all CSS and Javascript blocks be individually
     * minified? (default false)
     *
     * @todo Add 'file' option to read HTML file.
     */
    public function setupSources($options)
    {
        if (isset($options['file'])) {
            $sourceSpec = array(
            'filepath' => $options['file']
            );
            $f = $options['file'];
        } else {
            // strip controller options
            $sourceSpec = array(
            'content' => $options['content']
            ,'id' => $options['id']
            );
            $f = $options['id'];
            unset($options['content'], $options['id']);
        }
        // something like "builder,index.php" or "directory,file.html"
        $this->selectionId = strtr(substr($f, 1 + strlen(dirname(dirname($f)))), '/\\', ',,');

        if (isset($options['minifyAll'])) {
            // this will be the 2nd argument passed to Minify_HTML::minify()
            $sourceSpec['minifyOptions'] = array(
            'cssMinifier' => array('\W3TCL\Minify\Minify_CSS', 'minify')
            ,'jsMinifier' => array('\W3TCL\Minify\JSMin', 'minify')
            );
            unset($options['minifyAll']);
        }
        $this->sources[] = new Minify_Source($sourceSpec);

        $options['contentType'] = Minify::TYPE_HTML;
        return $options;
    }
}
