<?php
include('settings.php');
register_nav_menus(array( // Регистрация меню
	'top' => 'Верхнее',
	'bottom' => 'Внизу'
));

add_theme_support('post-thumbnails'); // Включение миниатюр
set_post_thumbnail_size(250, 150); // Размер миниатюр 250x150
add_image_size('big-thumb', 400, 400, true); // Ещё один размер миниатюры

register_sidebar(array(
	'name' => 'Колонка слева', // Название сайдбара
	'id' => "left-sidebar", // Идентификатор
	'description' => 'Обычная колонка в сайдбаре',
	'before_widget' => '<div id="%1$s" class="widget %2$s">', // До виджета
	'after_widget' => "</div>\n", // После виджета
	'before_title' => '<span class="widgettitle">', //  До заголовка виджета
	'after_title' => "</span>\n", //  После заголовка виджета
));

class clean_comments_constructor extends Walker_Comment { // класс, который собирает всю структуру комментов
	public function start_lvl( &$output, $depth = 0, $args = array()) { // что выводим перед дочерними комментариями
		$output .= '<ul class="children">' . "\n";
	}
	public function end_lvl( &$output, $depth = 0, $args = array()) { // что выводим после дочерних комментариев
		$output .= "</ul><!-- .children -->\n";
	}
    protected function comment( $comment, $depth, $args ) { // разметка каждого комментария, без закрывающего </li>!
    	$classes = implode(' ', get_comment_class()).($comment->comment_author_email == get_the_author_meta('email') ? ' author-comment' : ''); // берем стандартные классы комментария и если коммент пренадлежит автору поста добавляем класс author-comment
        echo '<li id="li-comment-'.get_comment_ID().'" class="'.$classes.'">'."\n"; // родительский тэг комментария с классами выше и уникальным id
    	echo '<div id="comment-'.get_comment_ID().'">'."\n"; // элемент с таким id нужен для якорных ссылок на коммент
    	echo get_avatar($comment, 64)."\n"; // покажем аватар с размером 64х64
    	echo '<p class="meta">Автор: '.get_comment_author()."\n"; // имя автора коммента
    	echo ' '.get_comment_author_email(); // email автора коммента
    	echo ' '.get_comment_author_url(); // url автора коммента
    	echo ' Добавлено '.get_comment_date('F j, Y').' в '.get_comment_time()."\n"; // дата и время комментирования
    	if ( '0' == $comment->comment_approved ) echo '<em class="comment-awaiting-moderation">Ваш комментарий будет опубликован после проверки модератором.</em>'."\n"; // если комментарий должен пройти проверку
        comment_text()."\n"; // текст коммента
        $reply_link_args = array( // опции ссылки "ответить"
        	'depth' => $depth, // текущая вложенность
        	'reply_text' => 'Ответить', // текст
			'login_text' => 'Вы должны быть залогинены' // текст если юзер должен залогинеться
        );
        echo get_comment_reply_link(array_merge($args, $reply_link_args)); // выводим ссылку ответить
        echo '</div>'."\n"; // закрываем див
    }
    public function end_el( &$output, $comment, $depth = 0, $args = array() ) { // конец каждого коммента
		$output .= "</li><!-- #comment-## -->\n";
	}
}

function pagination() { // функция вывода пагинации
	global $wp_query; // текущая выборка должна быть глобальной
	$big = 999999999; // число для замены
	echo paginate_links(array( // вывод пагинации с опциями ниже
		'base' => str_replace($big,'%#%',esc_url(get_pagenum_link($big))), // что заменяем в формате ниже
		'format' => '?paged=%#%', // формат, %#% будет заменено
		'current' => max(1, get_query_var('paged')), // текущая страница, 1, если $_GET['page'] не определено
		'type' => 'list', // ссылки в ul
		'prev_text'    => 'Назад', // текст назад
    	'next_text'    => 'Вперед', // текст вперед
		'total' => $wp_query->max_num_pages, // общие кол-во страниц в пагинации
		'show_all'     => false, // не показывать ссылки на все страницы, иначе end_size и mid_size будут проигнорированны
		'end_size'     => 15, //  сколько страниц показать в начале и конце списка (12 ... 4 ... 89)
		'mid_size'     => 15, // сколько страниц показать вокруг текущей страницы (... 123 5 678 ...).
		'add_args'     => false, // массив GET параметров для добавления в ссылку страницы
		'add_fragment' => '',	// строка для добавления в конец ссылки на страницу
		'before_page_number' => '', // строка перед цифрой
		'after_page_number' => '' // строка после цифры
	));
}

add_action('wp_footer', 'add_scripts'); // приклеем ф-ю на добавление скриптов в футер
if (!function_exists('add_scripts')) { // если ф-я уже есть в дочерней теме - нам не надо её определять
	function add_scripts() { // добавление скриптов
	    if(is_admin()) return false; // если мы в админке - ничего не делаем
	    wp_deregister_script('jquery'); // выключаем стандартный jquery
	    wp_enqueue_script('jquery','//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js','','',true); // добавляем свой
	    wp_enqueue_script('bootstrap', get_template_directory_uri().'/js/bootstrap.min.js','','',true); // бутстрап
	    wp_enqueue_script('pretty-photo', get_template_directory_uri().'/js/jquery.prettyPhoto.min.js','','',true);
	    wp_enqueue_script('video-lightbox', get_template_directory_uri().'/js/video-lightbox.js','','',true); 
	    wp_enqueue_script('slick', get_template_directory_uri().'/js/slick.min.js','','',true); 
	    wp_enqueue_script('informatica-connector', get_template_directory_uri().'/js/informatica.connector.js','','',true); 
	    wp_enqueue_script('countdown', get_template_directory_uri().'/js/jquery.countdown.min.js','','',true); 
	    wp_enqueue_script('cookie', get_template_directory_uri().'/js/js.cookie.js','','',true); 
	    wp_enqueue_script('waypoints', get_template_directory_uri().'/js/jquery.waypoints.min.js','','',true); 
	    wp_enqueue_script('jquery-cycle2', get_template_directory_uri().'/js/jquery.cycle2.min.js','','',true); 
	    wp_enqueue_script('jquery-selectric', get_template_directory_uri().'/js/jquery.selectric.js','','',true); 
	    wp_enqueue_script('jquery-equalizer', get_template_directory_uri().'/js/jquery.equalizer.min.js','','',true); 
	    wp_enqueue_script('jquery-backstretch', get_template_directory_uri().'/js/jquery.backstretch.min.js','','',true); 
	    wp_enqueue_script('velocity', get_template_directory_uri().'/js/velocity.min.js','','',true); 
	    wp_enqueue_script('velocity-ui', get_template_directory_uri().'/js/velocity.ui.js','','',true); 
	    wp_enqueue_script('jquery-magnific-popup', get_template_directory_uri().'/js/jquery.magnific-popup.min.js','','',true); 
	    wp_enqueue_script('main', get_template_directory_uri().'/js/main.js','','',true); // и скрипты шаблона
	}
}

add_action('wp_print_styles', 'add_styles'); // приклеем ф-ю на добавление стилей в хедер
if (!function_exists('add_styles')) { // если ф-я уже есть в дочерней теме - нам не надо её определять
	function add_styles() { // добавление стилей
	    if(is_admin()) return false; // если мы в админке - ничего не делаем
	    wp_enqueue_style( 'bs', get_template_directory_uri().'/css/bootstrap.min.css' ); // бутстрап
	    wp_enqueue_style( 'font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css' );
		wp_enqueue_style( 'main', get_template_directory_uri().'/style.css' ); // основные стили шаблона
		wp_enqueue_style( 'prettyphoto', get_template_directory_uri().'/css/prettyPhoto.css' ); // основные стили шаблона
		wp_enqueue_style( 'videolightbox', get_template_directory_uri().'/css/wp-video-lightbox.css' );
		wp_enqueue_style( 'logstyle', get_template_directory_uri().'/css/log-style.css' );
		wp_enqueue_style( 'frontend', get_template_directory_uri().'/css/frontend.css' );
		wp_enqueue_style( 'royalslider', get_template_directory_uri().'/css/royalslider.css' ); 
		wp_enqueue_style( 'magnificpopup', get_template_directory_uri().'/css/magnific-popup.css' );
		wp_enqueue_style( 'theme', get_template_directory_uri().'/css/theme.css' );
		wp_enqueue_style( 'slick', get_template_directory_uri().'/css/slick.css' );
		wp_enqueue_style( 'mainstyle', get_template_directory_uri().'/css/style.css' ); // основные стили шаблона
	}
}
?>
