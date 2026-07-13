<?php
/**
 * Fallback template – koristi se ako front-page.php nije dostupan
 * ili kada WordPress ne pronalazi specifičniji template.
 */
get_header(); ?>

<main class="main-content" style="padding: 120px 0 80px;">
    <div class="container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1><?php the_title(); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>
        <?php endwhile; else : ?>
            <p><?php esc_html_e( 'Nema sadržaja.', 'flyrec' ); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
