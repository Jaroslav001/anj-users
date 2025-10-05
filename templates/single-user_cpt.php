<?php

/**
 * Single template for a User CPT — Avatar strategy:
 * 1) Use CPT featured image if set
 * 2) Else use native WP user avatar
 */
defined('ABSPATH') || exit;
get_header();
?>
<main id="primary" class="anj-user-single">
  <?php while (have_posts()) : the_post(); ?>
    <?php
    $post_id   = get_the_ID();
    $author_id = (int) get_post_field('post_author', $post_id);
    $user      = $author_id ? get_user_by('id', $author_id) : null;
    $display   = $user ? $user->display_name : get_the_title();
    $nicename  = $user ? $user->user_nicename : '';
    $roles     = $user ? (array) $user->roles : [];
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('anj-user'); ?>>
      <header class="anj-user-header">
        <div class="anj-user-hero">
          <?php
          if (has_post_thumbnail()) {
            the_post_thumbnail('large', ['class' => 'anj-user-hero-img']);
          } elseif ($user) {
            echo get_avatar($author_id, 192, '', esc_attr($display), ['class' => 'anj-user-avatar--lg']);
          }
          ?>
        </div>
        <h1 class="anj-user-name"><?php echo esc_html($display); ?></h1>
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
      </header>

      <div class="anj-user-content">
        <?php the_content(); ?>
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