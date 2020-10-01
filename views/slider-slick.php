<div id="<?= $slider_attr_id ?>" class="css-netlab-slider js-netlab-slider-slick <?= $slider_attr_class ?>">

<?php while ($slides->have_posts()): $slides->the_post(); ?>
        
<?php
    $thumbnail = null;
    
    if (has_post_thumbnail()) 
    {
        $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), "slider_image" ); 
        $thumbnail = $thumbnail[0];
    }
?>
    
    <div class="css-netlab-slider-item" style="background-image: url(<?= $thumbnail ?>);">
        <h2>
            <span>Welcome to<?= $slide_index ?> <strong><?= get_the_title() ?></strong></span>
        </h2>
        <?= get_post_meta(get_the_ID(),'_slider_link_value_key', true); ?>
        <p><?= get_the_excerpt(); ?></p>
    </div>
    
<?php $slide_index++; endwhile; wp_reset_query(); ?>
</div>
