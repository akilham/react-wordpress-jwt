<?php

$type = get_query_var('postType');

$obj = get_post_type_object( $type );
echo '<div class="post-type-container ' . $type . '"><h2>' . $obj->labels->name . '</h2>';

while( have_posts() ){
    the_post();
    if( $type == get_post_type() ){
    	get_template_part('templates/search/content', get_post_type());
    }
}
rewind_posts();
echo '</div>';

?>