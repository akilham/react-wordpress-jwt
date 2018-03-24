<?php use Roots\Sage\Titles; ?>

<div class="wrap container" role="document">
  <div class="content row">
    <main class="main">
    	<div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
		    <?php if(function_exists('bcn_display')):
		        bcn_display();
		    endif; ?>
		 </div>

		<h1><?= Titles\title(); ?></h1>


		<?php if (!have_posts()) : ?>
		  <div class="alert alert-warning">
		    <?php _e('Sorry, no results were found.', 'sage'); ?>
		  </div>
		  <?php get_search_form(); ?>
		<?php endif; ?>

		<?php

		if ( have_posts() ) {
		    $postTypes = array('product' => 0, 'page' => 0);
	        while( have_posts() ) {
	            the_post();
	            if (array_key_exists(get_post_type(), $postTypes)) {
	            	$postTypes[get_post_type()]++;
	            }
	        }
	        rewind_posts();

		    foreach( $postTypes as $pType => $pCount ){
		    	if ($pCount > 0) {
		    		\Roots\Sage\Extras\get_template_part_extended('templates/search/wrapper', $pType, [ 'postType' => $pType, 'postTypeCount' => $pCount ]);
			    }
		    }
		}

		the_posts_navigation(); 

		?>


    </main><!-- /.main -->
    <?php if (\Roots\Sage\Setup\display_sidebar()) : ?>
      <aside class="sidebar">
        <?php include \Roots\Sage\Wrapper\sidebar_path(); ?>
      </aside><!-- /.sidebar -->
    <?php endif; ?>
  </div><!-- /.content -->
</div><!-- /.wrap -->