<?php
defined('ABSPATH') || exit;
get_header();
?>
<main id="primary" class="anj-user-single">
  <?php while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('anj-user'); ?>>
      <header class="anj-user-header">
        <h1 class="anj-user-name"><?php the_title(); ?></h1>
        <div class="anj-user-hero">
          <?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } ?>
        </div>
      </header>

      <div class="anj-user-content">
        <?php the_content(); ?>
        <?php
        $nicename = get_post_meta(get_the_ID(), '_anj_users_user_nicename', true);
        if ($nicename) {
          echo '<p class="anj-user-nicename"><strong>' . esc_html__('Username:', 'anj-users') . '</strong> ' . esc_html($nicename) . '</p>';
        }
        ?>
      </div>

      <footer class="anj-user-footer">
        <a class="anj-back-link" href="<?php echo esc_url(get_post_type_archive_link('user_cpt')); ?>">
          <?php esc_html_e('← Back to Users', 'anj-users'); ?>
        </a>
      </footer>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
