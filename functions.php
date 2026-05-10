<?php

// =============================================================================
// FUNCTIONS.PHP
// -----------------------------------------------------------------------------
// Overwrite or add your own custom functions to Pro in this file.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Enqueue Parent Stylesheet
//   02. Additional Functions
// =============================================================================

// Enqueue Parent Stylesheet
// =============================================================================

add_filter( 'x_enqueue_parent_stylesheet', '__return_true' );



// Additional Functions
// =============================================================================


// Adicionar estilos personalizados à página de login do WordPress
function custom_login_css() {
    echo '<style>

        .login h1 a {
            background-size: contain !important;
  width: 320px !important;
  height: 206px !important;
        }
        .login form {
            border: 0px solid #ccc !important;
            border-radius: 10px !important;
            padding: 20px !important;
            background-color: #fff !important;
            box-shadow: 0px 0px 10px rgba(0,0,0,0) !important;
        }
        .login #wp-submit {
            background-color: #364da0 !important;
            border: none !important;
            color: #fff !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
        }
		.login #backtoblog a, .login #nav a{color:#fff;}
		.login form#language-switcher {
		  border: 0px solid #ccc !important;
		  border-radius: 0px !important;
		  padding: 20px !important;
		  background-color: transparent !important;
		  box-shadow: 0px 0px 10px rgba(0,0,0,0) !important;
}
.login #wp-submit {
  background-color: #364da0 !important;
}
#language-switcher>label{display:none;}

.wp-core-ui .button, .wp-core-ui .button-secondary {
  color: #364da0;
  border-color: #364da0;
  background: #f6f7f7;
}
    </style>';
}
add_action('login_head', 'custom_login_css');



// ADICIONAR CAMPO DE NO RESULTS NA PAGINA DE RESULTADOS DE PESQUISA 
// 
function shortcode_mensagem_erro_pesquisa() {
    return '<div id="mensagem-erro-pesquisa" style="display: none;  font-size: 15px;  line-height:25px; text-align: left;">
        Não encontrámos nenhum resultado para o que procurou.<br> Pesquise por outro termo, ou entre em <a href="/contacto" class="link">contacto</a>.
    </div>';
}
add_shortcode('mensagem_erro_pesquisa', 'shortcode_mensagem_erro_pesquisa');


function nica_translate_search_text( $translated_text, $text, $domain ) {
    if ( is_admin() ) {
        return $translated_text;
    }

    if ( 'Search' === $text || 'Search …' === $text || 'Search...' === $text ) {
        return 'Pesquisar';
    }

    return $translated_text;
}
add_filter( 'gettext', 'nica_translate_search_text', 20, 3 );


// Desativar comentários na página de artigo (single post)
// =============================================================================

function nica_disable_comments_single( $open, $post_id ) {
    if ( is_singular( 'post' ) ) {
        return false;
    }
    return $open;
}
add_filter( 'comments_open', 'nica_disable_comments_single', 10, 2 );
add_filter( 'pings_open', 'nica_disable_comments_single', 10, 2 );

// Esconder o bloco de comentários mesmo que existam comentários antigos
function nica_hide_comments_template( $template ) {
    if ( is_singular( 'post' ) ) {
        return get_stylesheet_directory() . '/comments-blank.php';
    }
    return $template;
}
add_filter( 'comments_template', 'nica_hide_comments_template' );


// Artigos Relacionados no final do single post
// =============================================================================

function nica_related_posts_html() {
    if ( ! is_singular( 'post' ) ) {
        return;
    }

    $post_id   = get_the_ID();
    $categories = wp_get_post_categories( $post_id );

    $args = array(
        'category__in'       => $categories,
        'post__not_in'       => array( $post_id ),
        'posts_per_page'     => 3,
        'orderby'            => 'rand',
        'ignore_sticky_posts' => 1,
    );

    $related = new WP_Query( $args );

    if ( ! $related->have_posts() ) {
        return;
    }

    echo '<div class="nica-related-posts">';
    echo '<h3>Artigos Relacionados</h3>';
    echo '<div class="nica-related-grid">';

    while ( $related->have_posts() ) {
        $related->the_post();
        $thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
        echo '<div class="nica-related-item">';
        echo '<a href="' . esc_url( get_permalink() ) . '">';
        if ( $thumb ) {
            echo '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( get_the_title() ) . '">';
        }
        echo '<div class="nica-related-item-title">' . esc_html( get_the_title() ) . '</div>';
        echo '</a>';
        echo '</div>';
    }

    wp_reset_postdata();

    echo '</div></div>';
}
add_action( 'x_before_the_comments', 'nica_related_posts_html' );


// Marcar ligação de menu ativa com base no hash (#fragmento) via JavaScript
// =============================================================================

function nica_menu_active_hash_js() {
    ?>
    <script>
    (function() {
        function updateActiveHash() {
            var hash = window.location.hash;
            var aulaItems = document.querySelectorAll('.menu-item-368, .menu-item-369, .menu-item-370');
            if (!aulaItems.length) return;

            aulaItems.forEach(function(item) {
                var link = item.querySelector('a');
                if (!link) return;
                if (hash && link.href.indexOf(hash) !== -1) {
                    item.classList.add('current-menu-item');
                } else {
                    item.classList.remove('current-menu-item');
                }
            });
        }

        window.addEventListener('load', updateActiveHash);
        window.addEventListener('hashchange', updateActiveHash);
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'nica_menu_active_hash_js' );


// Ajustar altura do slider de testemunhos ao slide ativo
// =============================================================================

function nica_testimonial_slider_height_js() {
    ?>
    <script>
    (function() {
        function getActiveHeight(container) {
            var active = container.querySelector('.x-slide.is-current-slide');
            return active ? active.scrollHeight : 0;
        }

        function initSliders() {
            var containers = document.querySelectorAll('.x-slide-container.is-stacked');
            containers.forEach(function(container) {
                var observer = new MutationObserver(function(mutations) {
                    // pausar observer para não criar loop infinito
                    observer.disconnect();
                    var h = getActiveHeight(container);
                    if (h > 0) {
                        container.style.setProperty('height', h + 'px', 'important');
                    }
                    // retomar observer
                    observer.observe(container, { attributes: true, childList: true, subtree: true, attributeFilter: ['class', 'style'] });
                });
                observer.observe(container, { attributes: true, childList: true, subtree: true, attributeFilter: ['class', 'style'] });

                // ajuste inicial
                var h = getActiveHeight(container);
                if (h > 0) container.style.setProperty('height', h + 'px', 'important');
            });
        }

        window.addEventListener('load', function() {
            initSliders();
            setTimeout(initSliders, 600);
        });

        window.addEventListener('resize', function() {
            document.querySelectorAll('.x-slide-container.is-stacked').forEach(function(c) {
                var h = getActiveHeight(c);
                if (h > 0) c.style.setProperty('height', h + 'px', 'important');
            });
        });
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'nica_testimonial_slider_height_js' );

