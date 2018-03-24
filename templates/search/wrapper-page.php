<?php

$type = get_query_var('postType');
$count = get_query_var('postTypeCount');

$obj = get_post_type_object( $type );
echo '<div class="post-type-container ' . $type . '"><h2>' . $obj->labels->name . '</h2>';

if ($count == 1) {
	while( have_posts() ){
	    the_post();
	    if( $type == get_post_type() ){
			echo '<p class="search-results-pages">We found the <a href="' . get_the_permalink() . '">' . get_the_title() . '</a> page which is relevant to your search.</p>';
		}
	}
}
else {
	echo '<p class="search-results-pages">We found the ';

	$i = 1;

	while( have_posts() ){
	    the_post();
	    if( $type == get_post_type() ){
	    	echo '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>';

		    if (($i + 1) == $count) {
		    	echo ' and ';
		    }
		    elseif ($i < $count) {
		    	echo ', ';
		    }

		    $i++;
	    }
	}
	rewind_posts();

	echo ' pages which are relevant to your search.</p>';
}

echo '</div>';

?>