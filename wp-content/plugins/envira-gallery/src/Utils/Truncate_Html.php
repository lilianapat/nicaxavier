<?php
/**
 * HTML class.
 *
 * @since 1.9.17
 *
 * @package Envira Gallery
 * @author Envira Gallery Team
 */

namespace Envira\Utils;

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- legacy name.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

if ( ! class_exists( 'Truncate_Html' ) ) {

	/**
	 * Envira_Html class.
	 *
	 * @since 1.9.17
	 */
	class Truncate_Html {

		/**
		 * Holds the class object.
		 *
		 * @since 1.9.17
		 *
		 * @var object
		 */
		public static $instance;

		/**
		 * Holds the base class object.
		 *
		 * @since 1.9.17
		 *
		 * @var object
		 */
		public $base;

		/**
		 * Holds the word limit.
		 *
		 * @since 1.9.17
		 *
		 * @var int
		 */
		private $word_limit;

		/**
		 * Holds the word count.
		 *
		 * @since 1.9.17
		 *
		 * @var int
		 */
		private $word_count = 0;

		/**
		 * Primary class constructor.
		 *
		 * @since 1.9.17
		 */
		public function __construct() {
		}

		/**
		 * Truncate HTML content.
		 *
		 * @since 1.9.17
		 *
		 * @param string $html      The HTML content to truncate.
		 * @param int    $word_limit The word limit.
		 * @param bool   $ellipses  Whether to add ellipses.
		 * @return string
		 */
		public function truncate_html( $html, $word_limit, $ellipses = true ) {
			$this->word_limit = $word_limit;

			$dom = new \DOMDocument();
			libxml_use_internal_errors( true );
			$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			libxml_clear_errors();

			$this->trim_node( $dom, $ellipses );

			return $dom->saveHTML();
		}

		/**
		 * Truncate a node and its children to a word limit.
		 *
		 * @since 1.9.17
		 *
		 * @param DOMNode $node     The node to truncate.
		 * @param bool    $ellipses Whether to add ellipses.
		 */
		private function trim_node( $node, $ellipses ) {
			if ( $this->word_count >= $this->word_limit ) {
				// If the word limit has been reached, remove this node and all following siblings.
				while ( $node ) {
					$nextNode = $node->nextSibling; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
					if ( $node->parentNode ) {
						$node->parentNode->removeChild( $node );
					}
					$node = $nextNode; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
				}
			} elseif ( $node instanceof DOMText ) {
				// If this is a text node, split it into words and count them.
				$node_value = $node->nodeValue;
				// Count words in the original node value (including tags and blocks).
				$total_words      = preg_split( '/\s+/', $node_value );
				$total_word_count = count( $total_words );
				// Count words in the text content (excluding tags and blocks).
				$text_words = str_word_count( wp_strip_all_tags( preg_replace( '/<!--(.|\s)*?-->/', '', $node_value ) ) );
				// Subtract the words in the text content from the total words to get the words in the tags and blocks.
				$tag_and_block_words = $total_word_count - $text_words;
				// Subtract the words in the tags and blocks from the word limit.
				$this->word_limit += $tag_and_block_words;
				$words             = preg_split( '/\s+/', $node_value );
				$word_count        = count( $words );

				if ( $this->word_count + $word_count > $this->word_limit ) {
					// If this node contains too many words, trim it.
					$words           = array_slice( $words, 0, $this->word_limit - $this->word_count );
					$node->nodeValue = implode( ' ', $words );
					if ( $ellipses ) {
						$node->nodeValue .= '...';
					}
					$this->word_count = $this->word_limit;
				} else {
					$this->word_count += $word_count;
				}
			} else {
				// If this is an element node, recursively trim its children.
				$child = $node->firstChild;
				while ( $child ) {
					$next_child = $child->nextSibling;
					$this->trim_node( $child, $ellipses );
					$child = $next_child;
				}
			}
		}
	}
	// phpcs:enable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- legacy name.
	// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
}
