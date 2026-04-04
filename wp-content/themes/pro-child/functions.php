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
