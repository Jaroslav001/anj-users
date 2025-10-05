<?php

/**
 * Archive template for Users CPT — Table view
 * Columns: Avatar | Name | Role
 */
defined('ABSPATH') || exit;
get_header();
?>
<main id="primary" class="anj-users-archive anj-users-archive--table">
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
    <div class="anj-users-table-wrap">
      <table class="anj-users-table" role="table">
        <thead>
          <tr>
            <th scope="col" class="anj-col-avatar"><?php esc_html_e('Avatar', 'anj-users'); ?></th>
            <th scope="col" class="anj-col-name"><?php esc_html_e('Name', 'anj-users'); ?></th>
            <th scope="col" class="anj-col-role"><?php esc_html_e('Role', 'anj-users'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php while (have_posts()) : the_post(); ?>
            <?php
            $post_id   = get_the_ID();
            $author_id = (int) get_post_field('post_author', $post_id);
            $user      = $author_id ? get_user_by('id', $author_id) : null;
            $display   = $user ? $user->display_name : get_the_title();
            $nicename  = $user ? $user->user_nicename : '';
            $roles     = $user ? (array) $user->roles : [];
            $primary_role = '';
            if (!empty($roles)) {
              // pick the first role and map to desired labels
              $r = $roles[0];
              $map = [
                'administrator' => 'admin',
                'manager'       => 'manager',
                'lector'        => 'lector',
                'lecturer'      => 'lector',
              ];
              $primary_role = isset($map[$r]) ? $map[$r] : ucfirst(str_replace('_', ' ', $r));
            }
            ?>
            <tr class="anj-user-row">
              <td class="anj-col-avatar">
                <a href="<?php the_permalink(); ?>" class="anj-avatar-link" aria-label="<?php echo esc_attr($display); ?>">
                  <span class="anj-avatar-clip">
                    <?php
                    if (has_post_thumbnail()) {
                      the_post_thumbnail('thumbnail', ['class' => 'anj-avatar-img', 'alt' => esc_attr($display)]);
                    } elseif ($user) {
                      echo get_avatar($author_id, 64, '', esc_attr($display), ['class' => 'anj-avatar-img']);
                    } else {
                      echo '<span class="anj-avatar-ph" aria-hidden="true"></span>';
                    }
                    ?>
                  </span>
                </a>
              </td>
              <td class="anj-col-name">
                <a href="<?php the_permalink(); ?>" class="anj-user-name-link"><?php echo esc_html($display); ?></a>
                <?php if ($nicename): ?>
                  <div class="anj-user-username">@<?php echo esc_html($nicename); ?></div>
                <?php endif; ?>
              </td>
              <td class="anj-col-role">
                <?php if ($primary_role): ?>
                  <span class="anj-role-badge"><?php echo esc_html($primary_role); ?></span>
                <?php else: ?>
                  <span class="anj-role-badge anj-role-badge--unknown"><?php esc_html_e('—', 'anj-users'); ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
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