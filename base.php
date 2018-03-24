<?php

use Roots\Sage\Setup;
use Roots\Sage\Wrapper;

?>

<!doctype html>
<html <?php language_attributes(); ?>>
  <?php get_template_part('templates/head'); ?>
  <body <?php body_class(); ?>>
    <noscript>
      <div class="noscript-notice">This website relies on Javascript to display correctly. Please upgrade your browser for an optimal viewing experience.</div>
    </noscript>

    <?php
      do_action('get_header');
      // get_template_part('templates/header');
    ?>

    <?php include Wrapper\template_path(); ?>


    <?php
      do_action('get_footer');
      // get_template_part('templates/footer');
      wp_footer();
    ?>

    <!-- <script src="<?php echo get_template_directory_uri(); ?>/node_modules/react-touch-events/lib/index.js"></script> -->
  </body>
</html>
