<?php
/**

 * @file
 * This is the template file for the pdf object
 *
 * @TODO: Add documentation about this file and the available variables
 */
global $base_url;
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
module_load_include('inc','islandora_solr_search');

// Check for "N" or "O" value for third-party search, and add meta tags if exists
$tps = $dc_array["third_party_search"]["value"];
$meta_tags = array(
    '#tag' => 'META', 
    '#attributes' => array(
        'NAME' => 'ROBOTS',
        'CONTENT' => 'NOINDEX, NOFOLLOW',
    ),
);
        

if ($tps === "N" || $tps === "O") {
    drupal_add_html_head($meta_tags, "meta_tags");
}




$query="*";
$search_array = array("Level"=>"ndltd.level_ss",
                      "Degree"=>"ndltd.name_ss",
                      "Topic"=>"custom.category_ss",
                      "Academic Program"=>"custom.program_ss",
                      "Advisor"=>"dc.contributor_ss",
		      "Committee Members"=>"dc.contributor_ss",
                      "Subject"=>"dc.subject_ss",
                      "Keywords"=>"custom.keyword_ss"
                      );

$facets = array_values($search_array);
$settings = array("facet"=>"true", "facet.limit"=>"-1", "facet.field"=>$facets);
$solr = new Apache_Solr_Service("localhost",8080,"/solr/");
$results = $solr->search($query,0,$limit,$settings);
// var_dump($results);

?>
<?php $search_url = $base_url . "/islandora/search/"; ?>

<div class="islandora-pdf-object">
        <?php if (isset($islandora_content)): ?>
        <div class="islandora-pdf-content">
            <?php print $islandora_download_link; ?>
            <?php print $islandora_content; ?>
        </div>
        <?php endif; ?>
        <?php $display_array = array("Author", "Academic Program", "Level", "Degree", "Advisor", "Committee Members", "Date", "Subject", "Topic", "Keywords", "Access"); 
        ?>
        <div class="islandora-pdf-metadata">
            <fieldset>
               <!-- <h2><legend><span class="fieldset-legend"><?php print ('Dissertation Metadata'); ?></span></legend></h2> --> 
                <div class="fieldset-wrapper">
                    <dl class="islandora-inline-metadata islandora-pdf-fields">
                        <?php $row_field = 0; ?>
                        <?php foreach ($dc_array as $key => $value): ?>
                        <?php if (!empty($value['value']) && in_array($value['label'], $display_array)): ?>
                        <dt class="<?php print $value['class']; ?><?php print $row_field == 0 ? ' first' : ''; ?>">
                        <!--<dt class="<?php print $value['class']; ?>" -->
                            <?php print $value['label']; ?>
                        </dt>
                        <dd class="<?php print $value['class']; ?><?php print $row_field == 0 ? ' first' : ''; ?>">
                        <!-- <dd class="<?php print $value['class']; ?>"> -->
                            <?php foreach ($value['value'] as $entry): ?>
                                <?php if (array_key_exists($value['label'], $search_array)): ?>
					<?php
					$facet_object = $results->facet_counts->facet_fields->$search_array[$value['label']];
					$facet_array = get_object_vars($facet_object);
					if ($facet_array[$entry] > 1): ?>
					<a title="<?php print $facet_array[$entry]?> Results" href="<?php print $search_url ?><?php print $search_array[$value['label']]?>:(&quot;<?php print $entry ?>&quot;)"><?php print $entry ?></a><br>
					<?php elseif ($facet_array[$entry] <= 1): ?>
					<?php print $entry; ?> <br>
					<?php endif; ?>
				<?php elseif (in_array($value['label'], $display_array)): ?>
                                    <?php print $entry; ?> <br>
                        </dd>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php $row_field++; ?>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if($parent_collections): ?>
                            <dt class="collections">Member of</dt>
                            <dd class="collections">
                                <?php foreach ($parent_collections as $collection): ?>
                                <?php print l($collection->label, "islandora/object/{$collection->id}"); ?>
                                <?php endforeach; ?>
                        <?php endif; ?>                      


                    </dl>
                </div>
            </fieldset>
        </div>
        <?php if ($dc_array["Abstract"]["value"][0] !==""): ?>
        <div id="abstract">
	<dl id="abstract">
	<dt id="abstract">Abstract<dt>
	<dd id="abstract"><?php print $dc_array["Abstract"]["value"][0] ?></dd>
        </dl>
                 
        </div>
        <?php endif; ?>
        <!-- <div class="islandora-pdf-description">
            <?php if (!empty($dc_array['dc:description']['value'])): ?>
            <h2><?php print $dc_array['dc:description']['label']; ?></h2>
            <p><?php print $dc_array['dc:description']['value']; ?></p>
            <?php endif; ?>
        </div> -->
        <!-- <fieldset class="collapsible collapsed islandora-pdf-metadata"> -->
</div>
