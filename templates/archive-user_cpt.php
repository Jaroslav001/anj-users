<?php

/**
 * Archive template for Users CPT — Avatar strategy:
 * 1) Use CPT featured image if set
 * 2) Else use native WP user avatar
 * 3) Else show placeholder
 */
defined('ABSPATH') || exit;
get_header();
?>
<main id="primary" class="anj-users-archive anj-users-archive--cards">
  <header class="anj-users-header">
    <h1><?php echo esc_html__('Users', 'anj-users'); ?></h1>
    <form class="anj-users-search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
      <input type="hidden" name="post_type" value="user_cpt" />
      <label class="screen-reader-text" for="users-s"><?php esc_html_e('Search users', 'anj-users'); ?></label>
      <input id="users-s" type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr(wp_unslash($_GET['s'])) : ''; ?>" placeholder="<?php esc_attr_e('Search by name…', 'anj-users'); ?>" />
      <button type="submit"><?php esc_html_e('Search', 'anj-users'); ?></button>
    </form>
  </header>

  <?php if (have_posts()) : ?>
    <div class="anj-users-grid">
      <?php while (have_posts()) : the_post(); ?>
        <?php
        $post_id   = get_the_ID();
        $author_id = (int) get_post_field('post_author', $post_id);
        $user      = $author_id ? get_user_by('id', $author_id) : null;
        $display   = $user ? $user->display_name : get_the_title();
        $nicename  = $user ? $user->user_nicename : '';
        $desc      = $user ? get_user_meta($author_id, 'description', true) : '';
        $roles     = $user ? (array) $user->roles : [];
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('anj-user-card'); ?>>
          <a class="anj-user-link" href="<?php the_permalink(); ?>">
            <div class="anj-user-avatar">
              <?php
              if (has_post_thumbnail()) {
                the_post_thumbnail('thumbnail');
              } elseif ($user) {
                echo get_avatar($author_id, 96, '', esc_attr($display));
              } else {
                echo '<span class="anj-avatar-ph" aria-hidden="true"></span>';
              }
              ?>
            </div>
            <div class="anj-user-meta">
              <h2 class="anj-user-title"><?php echo esc_html($display); ?></h2>
              <?php if ($nicename): ?>
                <div class="anj-user-username">@<?php echo esc_html($nicename); ?></div>
              <?php endif; ?>

              <?php if (!empty($roles)): ?>
                <div class="anj-user-roles">
                  <?php foreach ($roles as $r): ?>
                    <span class="anj-role"><?php echo esc_html(ucfirst(str_replace('_', ' ', $r))); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <p class="anj-user-bio">
                <?php
                if ($desc) {
                  echo esc_html(wp_trim_words($desc, 20, '…'));
                } elseif (has_excerpt()) {
                  echo esc_html(get_the_excerpt());
                }
                ?>
              </p>

              <span class="anj-user-view"><?php esc_html_e('View profile →', 'anj-users'); ?></span>
            </div>
          </a>
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