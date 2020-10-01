<div id="<?= $slider_attr_id ?>" class="css-netlab-slider carousel slide <?= $slider_attr_class ?>" data-ride="carousel">

    <?php if ($slider_indicator): ?>
    <ol class="carousel-indicators">
    <?php for ($i=0; $i<$slides_total; $i++): ?>
        <li data-target="#<?= $slider_attr_id ?>" data-slide-to="<?php echo $i; ?>" class="<?= $i==0 ? "active" : null ?>"></li>
    <?php endfor; ?>
    </ol>
    <?php endif; ?>


    <div class="carousel-inner">
    <?php while ($slides->have_posts()): $slides->the_post(); ?>
        
    <?php
        $thumbnail = null;
        
        if (has_post_thumbnail()) 
        {
            $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), "slider_image" ); 
            $thumbnail = $thumbnail[0];
        }
    ?>

        <div class="css-netlab-slider-item carousel-item <?= $slide_index==0 ? "active" : null ?>" style="background-image: url(<?= $thumbnail ?>);">
            <h2>
                <span>Welcome to<?= $slide_index ?> <strong><?= get_the_title() ?></strong></span>
            </h2>
            <?= get_post_meta(get_the_ID(),'_slider_link_value_key', true); ?>
            <p><?= get_the_excerpt(); ?></p>
        </div>

    <?php $slide_index++; endwhile; wp_reset_query(); ?>
    </div>

    <?php if ($slider_controls): ?>
    <a class="carousel-control-prev" href="#" data-target="#<?= $slider_attr_id ?>" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#" data-target="#<?= $slider_attr_id ?>" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
    <?php endif; ?>

</div>
