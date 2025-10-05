<?php
defined('ABSPATH') || exit;
get_header();
?>
<main id="primary" class="anj-users-archive">
  <header class="anj-users-header">
    <h1><?php echo esc_html__('Users', 'anj-users'); ?></h1>
    <form class="anj-users-search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
      <input type="hidden" name="post_type" value="user_cpt" />
      <label class="screen-reader-text" for="users-s"><?php esc_html_e('Search users', 'anj-users'); ?></label>
      <input id="users-s" type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr(wp_unslash($_GET['s'])) : ''; ?>" />
      <button type="submit"><?php esc_html_e('Search', 'anj-users'); ?></button>
    </form>
  </header>

  <?php if (have_posts()) : ?>
    <div class="anj-users-grid">
      <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('anj-user-card'); ?>>
          <a class="anj-user-link" href="<?php the_permalink(); ?>">
            <div class="anj-user-thumb">
              <?php if (has_post_thumbnail()) {
                the_post_thumbnail('thumbnail');
              } else {
                echo '<div class="anj-user-thumb--ph" aria-hidden="true"></div>';
              } ?>
            </div>
            <h2 class="anj-user-title"><?php the_title(); ?></h2>
          </a>
          <?php if (has_excerpt()) : ?>
            <p class="anj-user-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
          <?php endif; ?>
        </article>
      <?php endwhile; ?>
    </div>

    <nav class="anj-users-pagination" aria-label="<?php esc_attr_e('Users pagination', 'anj-users'); ?>">
      <?php the_posts_pagination([
        'mid_size'  => 2,
        'prev_text' => __('« Prev', 'anj-users'),
        'next_text' => __('Next »', 'anj-users'),
      ]); ?>
    </nav>
  <?php else : ?>
    <p class="anj-users-empty"><?php esc_html_e('No users found.', 'anj-users'); ?></p>
  <?php endif; ?>
</main>
<?php get_footer(); ?>