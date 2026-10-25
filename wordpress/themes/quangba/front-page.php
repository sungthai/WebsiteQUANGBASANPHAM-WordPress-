<?php get_header(); ?>
<main>
    <section class="hero">
        <h1><?php bloginfo('name'); ?></h1>
        <p><?php bloginfo('description'); ?></p>
    </section>

    <section class="content">
        <div class="section-heading">
            <h2>Sản phẩm nổi bật</h2>
            <p>Khám phá danh mục sản phẩm và thông tin chi tiết.</p>
        </div>
        <div class="product-grid">
            <?php
            $products = new WP_Query(array(
                'post_type' => 'san_pham',
                'post_status' => 'publish',
                'posts_per_page' => 9,
            ));
            if ($products->have_posts()) :
                while ($products->have_posts()) :
                    $products->the_post();
                    $price = get_post_meta(get_the_ID(), '_quangba_price', true);
                    ?>
                    <article <?php post_class('product-card'); ?>>
                        <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large'); ?>
                            <?php else : ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='640' height='420'%3E%3Crect width='100%25' height='100%25' fill='%23e9eef5'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23576578' font-size='24'%3ESan pham%3C/text%3E%3C/svg%3E" alt="">
                            <?php endif; ?>
                        </a>
                        <div class="product-card-body">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php if ($price !== '') : ?>
                                <p class="product-price"><?php echo esc_html(number_format_i18n((float) $price, 0)); ?> VND</p>
                            <?php endif; ?>
                            <p><?php echo esc_html(get_the_excerpt() ?: wp_trim_words(get_the_content(), 24)); ?></p>
                        </div>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="empty-state">
                    <h3>Chưa có sản phẩm</h3>
                    <p>Đăng nhập trang quản trị, mở mục Sản phẩm và thêm sản phẩm đầu tiên.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
