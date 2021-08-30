<?php
/**
 * Plugin Name: Math123 Slider Anonsy
 * Description: Плагин WordPress для добавления слайдера с анонсами мероприятий страницу. Слайды берутся из постов категории анонсы мероприятий  () , для вставки плагина на странице использовать шорт-код [Math123_Slider_Anonsy subject="math" old="not"]АНОНСЫ МЕРОПРИЯТИЙ ПО ФИЗИКЕ[/Math123_Slider_Anonsy], для работы требуется SlickSlider
 * Plugin URI: https://github.com/AndreyTSpb/Math123-Slider-Anonsy.git
 * Author: Andrey Tynyany
 * Version: 1.0.1
 * Author URI: http://tynyany.ru
 *
 * Text Domain: Math123 Slider Anonsy
 *
 * @package Math123 Slider Anonsy
 */

defined('ABSPATH') or die('No script kiddies please!');

define( 'WPM123SA_VERSION', '1.0.4' );

define( 'WPM123SA_REQUIRED_WP_VERSION', '5.5' );

define( 'WPM123SA_PLUGIN', __FILE__ );

define( 'WPM123SA_PLUGIN_BASENAME', plugin_basename( WPM123SA_PLUGIN ) );

define( 'WPM123SA_PLUGIN_NAME', trim( dirname( WPM123SA_PLUGIN_BASENAME ), '/' ) );

define( 'WPM123SA_PLUGIN_DIR', untrailingslashit( dirname( WPM123SA_PLUGIN ) ) );

define( 'WPM123SA_PLUGIN_URL',
    untrailingslashit( plugins_url( '', WPM123SA_PLUGIN ) )
);
$math123sa_data = array();
/**
 * Подцепляем шорт код
 */
add_shortcode('Math123_Slider_Anonsy','math123_slider_anonsy');

/**
 * Основная функция, к котоой привязывается шорткод
 *
 * @param $atts - атрибутты для сликслайдера
 * @param $content - Заголовок слайдера
 * @return string - возващает html код на страницу
 */
function math123_slider_anonsy($atts, $content){
    global $math123sa_data;
    $math123sa_data['id_slider'] = rand(1000, 9999);

    /**
     * Подключение стиля
     */
    add_action('wp_head', 'math123_slider_anonsy_add_style');

    /**
     * Подключили скрипт для обработки
     */
    add_action('wp_footer', 'math123_slider_anonsy_add_script');

    /**
     * Определяем из какой категории брать данные
    */
    $subject = (!empty($atts['subject']))?$atts['subject']:'math';

    switch ($subject){
        case 'math':
            $id_category = 14;
            break;
        case 'phyth':
            $id_category = 15;
            break;
        case 'prog':
            $id_category = 16;
            break;
        case 'biznes':
            $id_category = 17;
            break;
        default:
            $id_category = 14;
            break;
    }

    /**
     * Анонсы или Архив
     */
    $old = (!empty($atts['old']) AND $atts['old'] == 'true') ? true : false;

    /**
     * Получаем посты со слайдами , из категории слайдер
     */
    $posts = math123_slider_anonsy_get_posts_for_slider($id_category, $old);

    $data = array(
        'id_slider' => $math123sa_data['id_slider'],
        'title'   => (!empty($content))?$content:'АНОНСЫ МЕРОПРИЯТИЙ',
        'subject' => $subject,
        'posts'   => $posts
    );

    $html = "";
    /**
     * Получим буфиризованый вывод шаблона
     */
    $html = math123_slider_anonsy_get_html($data);
    /**
     * Возвращаем html на страницу
     */
    return $html;
}

/**
 * Подключение стилей
 */
function math123_slider_anonsy_add_style(){
    /**
     * пока не используем, используем базовые стили
     */
    //$src = plugins_url( 'путь к стилю', __FILE__ );
    //exit($src);
    //wp_register_style( 'math123_slider_in_header_style', $src);

    //wp_enqueue_style( 'math123_slider_in_header_style');
}

/**
 * Подключение скриптов
 */
function math123_slider_anonsy_add_script(){
    global $math123sa_data;
    /**
     * регистрация скриптов
     */
    wp_register_script('math123_slider_anonsy_script', plugins_url( 'assets/script.js', __FILE__ ));


    /**
     * подключение скриптов
     */
    wp_enqueue_script('math123_slider_anonsy_script');

    /**
     * Параматры для скрипта
     */
    wp_localize_script( 'math123_slider_anonsy_script', 'Obj', $math123sa_data );
}

/**
 * Получение постов
 */
function math123_slider_anonsy_get_posts_for_slider($id_category, $old){
    /*
     * Для использования тут метода setup_postdata($post),
     * нужно обязательно обьявлять global $post; тогда будет работать
     */
    global $post;
    // параметры по умолчанию
    $posts = get_posts( array(
        'numberposts' => -1,
        'category'    => $id_category,
        'orderby'     => 'date',
        'order'       => 'ASC',
        'post_type'   => 'post',
        'suppress_filters' => true, // подавление работы фильтров изменения SQL запроса
    ) );

    $arr_slider = array();

    foreach ($posts as $id_arr => $post_obj){
        /*
         * $post присваеваем значения, из массива, иначе не работает setup_postdata
         * setup_postdata() работает именно с глобальной переменной $post.
         */
        $post =  $post_obj;
        setup_postdata( $post );

        /**
         * Получаем даныые из костомных полей
         *[data] = Array
         *   (
         *       [subject] =  biznes
         *       [data_ext_post] =  30/11/2021
         *   )
         */
        $fields = get_fields($post->ID);

        /**
         * Если в параметре old = true, значит выводим посты у которых дата публикации ['data_ext_post'] истекла,
         * Если в параметре old = false, то должны быть показаны посты у кого дата публикации еще не истекла
         */
        if (!empty($fields['data_ext_post'])) $date_ext = str_replace('/','.', $fields['data_ext_post']); //конвертируем в нормальную дату (30,11,2011)

        if($old AND !empty($fields['data_ext_post'])){

            if(strtotime($date_ext) > time()) continue;

        }else{

            if(strtotime($date_ext) < time()) continue;

        }
        /**
         * Получаем миниатюру к посту
         */
        if ( has_post_thumbnail() ) {
            $thumb = get_the_post_thumbnail( $post->ID, 'medium' );
        } else {
            $thumb = get_the_post_thumbnail( $post->ID, 'medium' );
        }


        $arr_slider[$id_arr] = array(
            'title'     => $post->post_title,
            'desc'      => $post->post_excerpt,
            'url'       => $post->guid,
            'img'       => $thumb,
            'date'      => $fields['data_ext_post']
        );

    }

    wp_reset_postdata(); //сбрасыва

    return $arr_slider;
}


/**
 * Размещение данных в шаблоне
 */
function math123_slider_anonsy_get_html($data){
    ob_start();
    include WPM123SA_PLUGIN_DIR."/tp/template.php";
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}