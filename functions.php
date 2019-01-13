<?php
function theme_enqueue_files() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}
add_action('wp_enqueue_scripts', 'theme_enqueue_files');

function widget_custom_script() { ?>
    <script type="text/javascript">
     jQuery(document).ready(function($) {
         var custom_uploader;

         jQuery('.media_btn').click(function(e) {
             e.preventDefault();
             if (custom_uploader) {
                 custom_uploader.open();
                 return;
             }
             custom_uploader = wp.media({
                 title: '画像を選択してください',
                 library: { type: 'image' },
                 button: { text: '画像の選択' },
                 multiple: false,
             });
             custom_uploader.on('select', function() {
                 var images = custom_uploader.state().get('selection');
                 images.each (function (file) {
                     jQuery(".widget-image").val("");
                     jQuery("#img-thumbnail").empty();
                     
                     jQuery(".widget-image").val(file.attributes.sizes.full.url);
                     jQuery("#img-thumbnail").append('<img src="'
                                              + file.attributes.sizes.thumbnail.url
                                              + '">');
                     // $("#img-thumbnail").attr('value', file.toJSON().url);
                 });
             });
             custom_uploader.open();
         });
     });
    </script>
    <?php
}
add_action('admin_footer', 'widget_custom_script');


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function nukayama_widgets_init() {
	register_sidebar( array(
		'name'			=> __( 'Front Page Top Nukayama'),
		'id'			=> 'front-page-top-nukayama',
		'description'	=> '',
		'before_widget'	=> '<div class="nukayama-top-area">',
		'after_widget'	=> '</p></div>',
		'before_title'	=> '<h2><span class="moji">',
		'after_title'	=> '</span></h2><p>',
	) );
}
add_action( 'widgets_init', 'nukayama_widgets_init' );

/**
 * Nukayama's Widget
 */
class NukayamaTopWidget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'nukayama_top_widget',
			'Nukayama Top Widget extend saitama',
			array('description' => '糠山作Saitama拡張ウィジェットです。')
		);	
	}

    // @param: $args -- functions.php で設定した値
    //         $instance -- このformで設定した値
	public function widget ($args, $instance) {
        $cache = array();

        // http://wpdocs.osdn.jp/%E3%82%AF%E3%83%A9%E3%82%B9%E3%83%AA%E3%83%95%E3%82%A1%E3%83%AC%E3%83%B3%E3%82%B9/WP_Object_Cache

        // プレビュー状態でなければ、キャッシュされたデータを取り出す。
        // キャッシュされたデータが無ければ、false がかえる。
        // widget_nukayama_toparea -- キー
        // widget -- グループ
        if (! $this->is_preview()) {
            $cache = wp_cache_get('widget_nukayama_toparea', 'widget');
        }
        // $cacheが配列でなければ、配列に変える。（$cacheがfalseのとき）
        if (! is_array($cache)) {
            $cache = array();
        }

        if (! isset ($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }
        // var_dump($args['widget_id']);            <== 'nukayama_top_widget-2'
        // var_dump($cache[$args['widget_id']]);    <== NULL

        if (isset ($cache[$args['widget_id']])) {
            echo $cache[$args['widget_id']];
            return;
        }
        
        ob_start();

        // =======================================================
        // ここからの内容はトップページに出力される。
        // =======================================================

		// var_dump($instance);    // $instance の内容をチェック

        echo $args['before_widget'];

        if (! empty($instance['link'])) {
            $url_before = '<a href="' . $instance['link'] . '">';
            $url_after = '</a>';
            
        } else {
            $url_before = "";
            $url_after = "";
        }
        
        if (! empty($instance['image'])) {
            $image = '<img src="' . $instance['image'] . '" alt="">';
        }
        echo $url_before, $image, $url_after;
        
        
        echo $args['before_title'];
        $title = (! empty($instance['title'])) ? $instance['title'] : "タイトルやねんけど";
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        echo $url_before, $title, $url_after;

        echo $args['after_title'];
        $body = (! empty($instance['body'])) ? $instance['body'] : "ボディやねんけど";
        $body = apply_filters('widget_body', $body, $instance, $this->id_base);
        echo $body;
		echo $args['after_widget'];

        if (! $this->is_preview()) {
            $cache[$args['widget_id']] = ob_get_flush();
            wp_cache_set('widget_nukayama_toparea', $cache, 'widget');
        } else {
            ob_end_flush();
        }
	}

    // =======================================================
    // ここからの内容は、ウィジェット画面に出力される。
    // =======================================================
	public function form($instance) {
?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>">タイトル</label>
        <input type ="text" class="widefat"
               id="<?php echo $this->get_field_id('title'); ?>"
               name="<?php echo  $this->get_field_name('title'); ?>"
               value="<?php echo esc_attr($instance['title']); ?>">
    </p>
    <p>
        <label for="<?php echo $this->get_field_id('body'); ?>">内容</label>
        <textarea class="widefat"
               id="<?php echo $this->get_field_id('body'); ?>"
               name="<?php echo  $this->get_field_name('body'); ?>"
               ><?php echo esc_attr($instance['body']); ?></textarea>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id('link'); ?>">リンクURL</label>
        <input type="text" class="widefat"
               id="<?php echo $this->get_field_id('link'); ?>"
               name="<?php echo $this->get_field_name('link'); ?>"
               placeholder="http://"
               value="<?php echo esc_attr($instance['link']); ?>">
    </p>
    <p>
        <label for="<?php echo $this->get_field_id('image'); ?>">イメージ</label>
        <input type="text" class="widefat widget-image"
               id="<?php echo $this->get_field_id('image'); ?>"
               name="<?php echo $this->get_field_name('image'); ?>"
               value="<?php echo esc_attr($instance['image']); ?>">
        <div id="img-thumbnail"></div>
        <button type="button" class="media_btn">画像選択</button>
    </p>
<?php

}

function update($new_instance, $old_instance) {
    $instance = $old_instance;
    // $instance = array();
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['body'] = strip_tags($new_instance['body']);
    $instance['link'] = $new_instance['link'];
    $instance['image'] = $new_instance['image'];
	return $instance;
}
} // enc class

add_action('widgets_init', function() {
    register_widget('NukayamaTopWidget');
});

