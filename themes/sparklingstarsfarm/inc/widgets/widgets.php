<?php
add_action( 'widgets_init', 'spfarms_widgets_init', 1);

function spfarms_widgets_init() {
    register_widget( "spfarms_news_widget" );
    register_widget('spfarms_events_widget');
    register_widget('spfarms_images_widget');
    unregister_widget('himalayas_portfolio_widget');
    unregister_widget('himalayas_service_widget');
}

/**
 * Portfolio widget section
 */
class spfarms_images_widget extends WP_Widget {

    function __construct() {
        $widget_ops = array( 'classname' => 'widget_portfolio_block', 'description' => __( 'Display some pages as Images', 'himalayas') );
        $control_ops = array( 'width' => 200, 'height' =>250 );
        parent::__construct( false,$name= __( 'TG: Images', 'himalayas' ), $widget_ops);
    }

    function form( $instance ) {
        $defaults[ 'portfolio_menu_id' ] = '';
        $defaults[ 'title' ] = '';
        $defaults[ 'text' ] = '';
        $defaults[ 'number' ] = 8;

        $instance = wp_parse_args( (array) $instance, $defaults );

        $portfolio_menu_id = esc_attr( $instance[ 'portfolio_menu_id' ] );
        $title = esc_attr( $instance[ 'title' ] );
        $text = esc_textarea( $instance[ 'text' ] );
        $number = absint( $instance[ 'number' ] ); ?>

        <p><?php _e( 'Note: Enter the Portfolio Section ID and use same for Menu item.', 'himalayas' ); ?></p>

        <p>
            <label for="<?php echo $this->get_field_id( 'portfolio_menu_id' ); ?>"><?php _e( 'Portfolio Section ID:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'portfolio_menu_id' ); ?>" name="<?php echo $this->get_field_name( 'portfolio_menu_id' ); ?>" type="text" value="<?php echo $portfolio_menu_id; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <?php _e( 'Description:','himalayas' ); ?>
        <textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of pages to display:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>

        <p><?php _e( 'Note: Create the pages and select Images Template to display Images pages.', 'himalayas' ); ?></p>
        <?php
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance[ 'portfolio_menu_id' ] = sanitize_text_field( $new_instance[ 'portfolio_menu_id' ] );
        $instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
        if ( current_user_can('unfiltered_html') )
            $instance[ 'text' ] =  $new_instance[ 'text' ];
        else
            $instance[ 'text' ] = stripslashes( wp_filter_post_kses( addslashes($new_instance[ 'text' ]) ) ); // wp_filter_post_kses() expects slashed
        $instance[ 'number' ] = absint( $new_instance[ 'number' ] );

        return $instance;
    }

    function widget( $args, $instance ) {
        extract( $args );
        extract( $instance );

        global $post;

        $portfolio_menu_id = isset( $instance[ 'portfolio_menu_id' ] ) ? $instance[ 'portfolio_menu_id' ] : '';
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
        $text = isset( $instance[ 'text' ] ) ? $instance[ 'text' ] : '';
        $number = empty( $instance[ 'number' ] ) ? 8 : $instance[ 'number' ];

        $page_array = array();
        $pages = get_pages();
        // get the pages associated with Portfolio Template.
        foreach ( $pages as $page ) {
            $page_id = $page->ID;
            $template_name = get_post_meta( $page_id, '_wp_page_template', true );
            if( $template_name == 'page-templates/template-images.php' ) {
                array_push( $page_array, $page_id );
            }
        }
        $get_featured_posts = new WP_Query( array(
            'posts_per_page'        => $number,
            'post_type'             =>  array( 'page' ),
            'post__in'              => $page_array,
            'orderby'               => array( 'menu_order' => 'ASC', 'date' => 'DESC' )
        ) );

        echo $before_widget;

        $section_id = '';
        if( !empty( $portfolio_menu_id ) )
            $section_id = 'id="' . $portfolio_menu_id . '"'; ?>

        <div <?php echo $section_id; ?> class="" >
            <div class="section-wrapper">
                <div class="tg-container">
                    <div class="section-title-wrapper">
                        <?php
                        if( !empty( $title ) ) { echo $before_title . esc_html( $title ) . $after_title; }
                        if( !empty( $text ) ) { ?> <h4 class="sub-title"> <?php echo esc_textarea( $text ); ?> </h4> <?php } ?>
                    </div>
                </div>

                <?php if( !empty( $page_array ) ) : ?>
                    <div class="Portfolio-content-wrapper clearfix">
                        <?php
                        while( $get_featured_posts->have_posts() ):$get_featured_posts->the_post(); ?>

                            <div class="portfolio-images-wrapper">
                                <?php
                                // Get the full URI of featured image
                                $image_popup_id = get_post_thumbnail_id();
                                $image_popup_url = wp_get_attachment_url( $image_popup_id ); ?>

                                <div class="port-img">
                                    <?php if( has_post_thumbnail() ) {
                                        the_post_thumbnail('himalayas-portfolio-image');

                                    } else { $image_popup_url = get_template_directory_uri() . '/images/placeholder-portfolio.jpg';
                                        echo '<img src="' . $image_popup_url . '">';
                                    } ?>
                                </div>

                                <div class="portfolio-hover">
                                    <div class="port-link">
                                        <a class="image-popup" href="<?php echo $image_popup_url; ?>" ><i class="fa fa-search-plus"></i></a>
                                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute();?>"><i class="fa fa-link"></i></a>
                                    </div>

                                    <div class="port-title-wrapper">
                                        <h4 class="port-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute();?>"><?php the_title(); ?></a></h4>
                                        <div class="port-desc"> <?php echo himalayas_excerpt( 16 ); ?> </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div><!-- .Portfolio-content-wrapper -->
                    <?php
                    // Reset Post Data
                    wp_reset_query();
                endif; ?>
            </div>
        </div><!-- .section-wrapper -->

        <?php echo $after_widget;
    }
}

class spfarms_news_widget extends WP_Widget {
    function __construct() {
        $widget_ops = array( 'classname' => 'widget_service_block', 'description' => __( 'Display some pages as News.', 'himalayas' ) );
        $control_ops = array( 'width' => 200, 'height' =>250 );
        parent::__construct( false, $name = __( 'TG: News Widget', 'himalayas' ), $widget_ops, $control_ops);
    }

    function form( $instance ) {
        $defaults['service_menu_id'] = '';
        $defaults['title'] = '';
        $defaults['text'] = '';
        $defaults['number'] = '6';

        $instance = wp_parse_args( (array) $instance, $defaults );

        $service_menu_id = esc_attr( $instance[ 'service_menu_id' ] );
        $title = esc_attr( $instance['title'] );
        $text = esc_textarea( $instance['text'] );
        $number = absint( $instance[ 'number' ] ); ?>

        <p><?php _e( 'Note: Enter the Service Section ID and use same for Menu item. Only used for One Page Menu.', 'himalayas' ); ?></p>

        <p>
            <label for="<?php echo $this->get_field_id('service_menu_id'); ?>"><?php _e( 'Service Section ID:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id('service_menu_id'); ?>" name="<?php echo $this->get_field_name('service_menu_id'); ?>" type="text" value="<?php echo $service_menu_id; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <?php _e( 'Description:','himalayas' ); ?>
        <textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of pages to display:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>

        <p><?php _e( 'Note: Create the pages and select News Template to display News pages.', 'himalayas' ); ?></p>
    <?php }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'service_menu_id' ] = sanitize_text_field( $new_instance[ 'service_menu_id' ] );
        $instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );

        if ( current_user_can('unfiltered_html') )
            $instance[ 'text' ] =  $new_instance[ 'text' ];
        else
            $instance[ 'text' ] = stripslashes( wp_filter_post_kses( addslashes( $new_instance[ 'text' ] ) ) ); // wp_filter_post_kses() expects slashed

        $instance[ 'number' ] = absint( $new_instance[ 'number' ] );

        return $instance;
    }

    function widget( $args, $instance ) {
        extract( $args );
        extract( $instance );

        global $post;
        $service_menu_id = isset( $instance[ 'service_menu_id' ] ) ? $instance[ 'service_menu_id' ] : '';
        $title = apply_filters( 'widget_title', isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '');
        $text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
        $number = empty( $instance[ 'number' ] ) ? 6 : $instance[ 'number' ];

        $page_array = array();
        $pages = get_pages();
        // get the pages associated with Services Template.
        foreach ( $pages as $page ) {
            $page_id = $page->ID;
            $template_name = get_post_meta( $page_id, '_wp_page_template', true );
            if( $template_name == 'page-templates/template-news.php' ) {
                array_push( $page_array, $page_id );
            }
        }

        $get_featured_pages = new WP_Query( array(
            'posts_per_page'        => $number,
            'post_type'             =>  array( 'page' ),
            'post__in'              => $page_array,
            'orderby'               => array( 'menu_order' => 'ASC', 'date' => 'DESC' )
        ) );

        $section_id = '';
        if( !empty( $service_menu_id ) )
            $section_id = 'id="' . $service_menu_id . '"';

        echo $before_widget; ?>
        <div <?php echo $section_id; ?> >
            <div  class="section-wrapper">
                <div class="tg-container">

                    <div class="section-title-wrapper">
                        <?php if( !empty( $title ) ) echo $before_title . esc_html( $title ) . $after_title;

                        if( !empty( $text ) ) { ?>
                            <h4 class="sub-title"><?php echo esc_textarea( $text ); ?></h4>
                        <?php } ?>
                    </div>

                    <?php
                    if( !empty( $page_array ) ) {
                        $count = 0; ?>
                        <div class="service-content-wrapper clearfix">
                            <div class="tg-column-wrapper clearfix">

                                <?php while( $get_featured_pages->have_posts() ):$get_featured_pages->the_post();

                                    if ( $count % 3 == 0 && $count > 1 ) { ?> <div class="clearfix"></div> <?php } ?>

                                    <div class="tg-column-3 tg-column-bottom-margin">
                                        <?php
                                        $himalayas_icon = get_post_meta( $post->ID, 'himalayas_font_icon', true );
                                        $himalayas_icon = isset( $himalayas_icon ) ? esc_attr( $himalayas_icon ) : '';

                                        $icon_image_class = '';
                                        if( !empty ( $himalayas_icon ) ) {
                                            $icon_image_class = 'service_icon_class';
                                            $services_top = '<i class="fa ' . esc_html( $himalayas_icon ) . '"></i>';
                                        }
                                        if( has_post_thumbnail() ) {
                                            $icon_image_class = 'service_image_class';
                                            $services_top = get_the_post_thumbnail( $post->ID, 'himalayas-services' );
                                        }

                                        if( has_post_thumbnail() || !empty ( $himalayas_icon ) ) { ?>

                                            <div class="<?php echo $icon_image_class; ?>">
                                                <div class="image-wrap">
                                                    <?php echo $services_top; ?>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="service-desc-wrap">
                                            <h5 class="service-title"><a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" alt="<?php the_title_attribute(); ?>"> <?php echo esc_html( get_the_title() ); ?></a></h5>

                                            <div class="service-content">
                                                <?php the_excerpt(); ?>
                                            </div>

                                            <a class="service-read-more" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"> <?php  _e( 'Read more', 'himalayas' ) ?><i class="fa fa-angle-double-right"> </i></a>
                                        </div>
                                    </div>
                                    <?php $count++;
                                endwhile; ?>
                            </div>
                        </div>
                        <?php
                        // Reset Post Data
                        wp_reset_query();
                    } ?>
                </div>
            </div>
        </div>
        <?php echo $after_widget;
    }
}

/**
 * Events Widget section.
 */
class spfarms_events_widget extends WP_Widget {
    function __construct() {
        $widget_ops = array( 'classname' => 'widget_service_block', 'description' => __( 'Display some pages as events.', 'himalayas' ) );
        $control_ops = array( 'width' => 200, 'height' =>250 );
        parent::__construct( false, $name = __( 'TG: Events Widget', 'himalayas' ), $widget_ops, $control_ops);
    }

    function form( $instance ) {
        $defaults['service_menu_id'] = '';
        $defaults['title'] = '';
        $defaults['text'] = '';
        $defaults['number'] = '6';

        $instance = wp_parse_args( (array) $instance, $defaults );

        $service_menu_id = esc_attr( $instance[ 'service_menu_id' ] );
        $title = esc_attr( $instance['title'] );
        $text = esc_textarea( $instance['text'] );
        $number = absint( $instance[ 'number' ] ); ?>

        <p><?php _e( 'Note: Enter the Service Section ID and use same for Menu item. Only used for One Page Menu.', 'himalayas' ); ?></p>

        <p>
            <label for="<?php echo $this->get_field_id('service_menu_id'); ?>"><?php _e( 'Service Section ID:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id('service_menu_id'); ?>" name="<?php echo $this->get_field_name('service_menu_id'); ?>" type="text" value="<?php echo $service_menu_id; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <?php _e( 'Description:','himalayas' ); ?>
        <textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

        <p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of pages to display:', 'himalayas' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>

        <p><?php _e( 'Note: Create the pages and select Events Template to display Events pages.', 'himalayas' ); ?></p>
    <?php }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'service_menu_id' ] = sanitize_text_field( $new_instance[ 'service_menu_id' ] );
        $instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );

        if ( current_user_can('unfiltered_html') )
            $instance[ 'text' ] =  $new_instance[ 'text' ];
        else
            $instance[ 'text' ] = stripslashes( wp_filter_post_kses( addslashes( $new_instance[ 'text' ] ) ) ); // wp_filter_post_kses() expects slashed

        $instance[ 'number' ] = absint( $new_instance[ 'number' ] );

        return $instance;
    }

    function widget( $args, $instance ) {
        extract( $args );
        extract( $instance );

        global $post;
        $service_menu_id = isset( $instance[ 'service_menu_id' ] ) ? $instance[ 'service_menu_id' ] : '';
        $title = apply_filters( 'widget_title', isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '');
        $text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
        $number = empty( $instance[ 'number' ] ) ? 6 : $instance[ 'number' ];

        $page_array = array();
        $pages = get_pages();
        // get the pages associated with Services Template.
        foreach ( $pages as $page ) {
            $page_id = $page->ID;
            $template_name = get_post_meta( $page_id, '_wp_page_template', true );
            if( $template_name == 'page-templates/template-events.php' ) {
                array_push( $page_array, $page_id );
            }
        }

        $get_featured_pages = new WP_Query( array(
            'posts_per_page'        => $number,
            'post_type'             =>  array( 'page' ),
            'post__in'              => $page_array,
            'orderby'               => array( 'menu_order' => 'ASC', 'date' => 'DESC' )
        ) );

        $section_id = '';
        if( !empty( $service_menu_id ) )
            $section_id = 'id="' . $service_menu_id . '"';

        echo $before_widget; ?>
        <div <?php echo $section_id; ?> >
            <div  class="section-wrapper">
                <div class="tg-container">

                    <div class="section-title-wrapper">
                        <?php if( !empty( $title ) ) echo $before_title . esc_html( $title ) . $after_title;

                        if( !empty( $text ) ) { ?>
                            <h4 class="sub-title"><?php echo esc_textarea( $text ); ?></h4>
                        <?php } ?>
                    </div>

                    <?php
                    if( !empty( $page_array ) ) {
                        $count = 0; ?>
                        <div class="service-content-wrapper clearfix">
                            <div class="tg-column-wrapper clearfix">

                                <?php while( $get_featured_pages->have_posts() ):$get_featured_pages->the_post();

                                    if ( $count % 3 == 0 && $count > 1 ) { ?> <div class="clearfix"></div> <?php } ?>

                                    <div class="tg-column-3 tg-column-bottom-margin">
                                        <?php
                                        $himalayas_icon = get_post_meta( $post->ID, 'himalayas_font_icon', true );
                                        $himalayas_icon = isset( $himalayas_icon ) ? esc_attr( $himalayas_icon ) : '';

                                        $icon_image_class = '';
                                        if( !empty ( $himalayas_icon ) ) {
                                            $icon_image_class = 'service_icon_class';
                                            $services_top = '<i class="fa ' . esc_html( $himalayas_icon ) . '"></i>';
                                        }
                                        if( has_post_thumbnail() ) {
                                            $icon_image_class = 'service_image_class';
                                            $services_top = get_the_post_thumbnail( $post->ID, 'himalayas-services' );
                                        }

                                        if( has_post_thumbnail() || !empty ( $himalayas_icon ) ) { ?>

                                            <div class="<?php echo $icon_image_class; ?>">
                                                <div class="image-wrap">
                                                    <?php echo $services_top; ?>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="service-desc-wrap">
                                            <h5 class="service-title"><a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" alt="<?php the_title_attribute(); ?>"> <?php echo esc_html( get_the_title() ); ?></a></h5>

                                            <div class="service-content">
                                                <?php the_excerpt(); ?>
                                            </div>

                                            <a class="service-read-more" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"> <?php  _e( 'Read more', 'himalayas' ) ?><i class="fa fa-angle-double-right"> </i></a>
                                        </div>
                                    </div>
                                    <?php $count++;
                                endwhile; ?>
                            </div>
                        </div>
                        <?php
                        // Reset Post Data
                        wp_reset_query();
                    } ?>
                </div>
            </div>
        </div>
        <?php echo $after_widget;
    }
}