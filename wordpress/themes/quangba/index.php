<?php get_header(); ?>
<main class="content">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('entry'); ?>>
                <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="empty-state"><p>Chưa có nội dung.</p></div>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
