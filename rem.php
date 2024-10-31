<?php

/*
OAI-ORE Resource Map
Copyright (C) 2007, 2008, 2009  Michael J. Giarlo (leftwing@alumni.rutgers.edu)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once ('../../../wp-config.php');

header('Content-Type: application/atom+xml; charset=' . get_option('blog_charset'), true);

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
  define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
  define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
  define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

if (!function_exists('plugins_url')):
function plugins_url($path = '') {
        $url = WP_PLUGIN_URL;
        if ( !empty($path) && is_string($path) && strpos($path, '..') === false ) {
                $url .= '/' . ltrim($path, '/');
        }
        return $url;
}
endif;
// End pre-2.6 compat

$remUrl = plugins_url('oai-ore/rem.php');
$resources = array();

echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . ">\n"; 
?>
<entry xmlns="http://www.w3.org/2005/Atom"
  xmlns:oreatom="http://www.openarchives.org/ore/atom/"
  xmlns:ore="http://www.openarchives.org/ore/terms/"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/"
  xmlns:grddl="http://www.w3.org/2003/g/data-view#"
  grddl:transformation="http://www.openarchives.org/ore/atom/atom-grddl.xsl">

  <!-- Atom-specific -->
  <id><?php bloginfo('url'); ?></id>
  <link rel="alternate" type="text/html" href="<?php bloginfo('url'); ?>" />

  <!-- Resource map metadata -->
  <link rel="self" type="application/atom+xml" href="<?php echo $remUrl; ?>" />
  <link rel="http://www.openarchives.org/ore/terms/describes" href="<?php echo $remUrl; ?>#aggregation"/>
  <source>
    <author>
      <name><?php bloginfo('name'); ?></name>
      <uri><?php bloginfo('url'); ?></uri>
      <email><?php bloginfo('admin_email'); ?></email>
    </author>
    <title type="text">Resource Map for <?php bloginfo('url'); ?></title>
  </source>
  <updated><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT')); ?></updated>
  <link rel="license" type="application/rdf+xml" href="http://creativecommons.org/licenses/by-nc/2.5/rdf" />
  <rights>This Resource Map is available under the Creative Commons Attribution-Noncommercial 2.5 Generic license</rights>

  <!-- Aggregation metadata -->
  <title>Aggregation of entries from <?php bloginfo('url'); ?> </title>
  <author>
    <name><?php bloginfo('name'); ?></name>
    <email><?php bloginfo('admin_email'); ?></email>
  </author>
  <link rel="http://www.openarchives.org/ore/terms/isDescribedBy" href="<?php echo $remUrl; ?>" />
  <category term="http://www.openarchives.org/ore/terms/Aggregation" label="Aggregation" scheme="http://www.openarchives.org/ore/terms/" />
  <category term="<?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT')); ?>" scheme="http://www.openarchives.org/ore/atom/modified"/>

  <!-- Aggregated resources -->
<?php foreach(array_merge(get_pages(), get_posts('numberposts=-1')) as $post) : setup_postdata($post); ?>
  <link rel="http://www.openarchives.org/ore/terms/aggregates" href="<?php the_permalink() ?>" title="<?php the_title(); ?>" type="text/html" />
<?php
  $resources[$post->guid] = array("id" => $post->guid,
                                  "modified" => $post->post_modified,
                                  "author" => get_the_author(),
                                  "url" => get_the_author_url(),
                                  "description" => htmlspecialchars(strip_tags($post->post_content)),
                                  "subjects" => (array) get_the_category());
?>
<?php endforeach ; ?>

  <!-- Additional properties pertaining to Aggregated Resources -->
  <oreatom:triples>
<?php foreach ($resources as $resource) { ?>
    <rdf:Description rdf:about="<?php echo $resource['id']; ?>">
      <dcterms:modified><?php echo $resource['modified']; ?></dcterms:modified>
      <dcterms:description><?php echo $resource['description']; ?></dcterms:description>
      <dcterms:creator>
        <foaf:name><?php echo $resource['author']; ?></foaf:name>
        <foaf:homepage><?php echo $resource['url']; ?></foaf:homepage>
      </dcterms:creator>
<?php   foreach ( (array) $resource['subjects'] as $cat) { ?>
      <dcterms:subject><?php echo $cat->cat_name; ?></dcterms:subject>
<?php   } ?>
    </rdf:Description>
<?php } ?>
  </oreatom:triples>
</entry>
