<?php
/**
 * TGM plugin module.
 *
 * @package the7
 * @since 3.0.0
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Presscore_Modules_TGMPAModule', false ) ) :

	class Presscore_Modules_TGMPAModule {

		/**
		 * Execute module.
		 */
		public static function execute() {
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				return;
			}

			require_once plugin_dir_path( __FILE__ ) . 'class-tgm-plugin-activation.php';

			// Register plugins.
			add_action( 'tgmpa_register', array( __CLASS__, 'register_plugins_action' ) );
			add_action( 'admin_menu', array( __CLASS__, 'on_page_load' ), 9999 );
		}

		public static function register_plugins_action() {
			$plugins = include trailingslashit( PRESSCORE_PLUGINS_DIR ) . 'plugins.php';
			$plugins = apply_filters( 'presscore_tgmpa_module_plugins_list', $plugins );

			tgmpa( $plugins, array(
				'id'               => 'the7_tgmpa',
				'menu'             => 'install-required-plugins',
				'parent_slug'      => 'plugins.php',
				'dismissable'      => true,
				'has_notices'      => true,
				'is_automatic'     => false,
				'strings'          => array(
					'page_title'                      => __( 'The7 Плагины', 'the7mk2' ),
					'menu_title'                      => __( 'The7 Плагины', 'the7mk2' ),
					'installing'                      => __( 'Установка плагина: %s', 'the7mk2' ),
					'oops'                            => __( 'Что-то пошло не так с плагином  API.', 'the7mk2' ),
					'notice_can_install_required'     => _n_noop( 'Эта тема требует следующий плагин: %1$s.', 'Эта тема требует следующие плагины: %1$s.', 'the7mk2' ),
					'notice_can_install_recommended'  => _n_noop( 'Эта тема рекомендует следующие плагин: %1$s.', 'Эта тема рекомендует следующие плагины: %1$s.', 'the7mk2' ),
					'notice_cannot_install'           => false,
					'notice_can_activate_required'    => _n_noop( 'Следующие необходимые плагин в настоящее время неактивным: %1$s.', 'Следующие необходимые плагины в настоящее время неактивны: %1$s.', 'the7mk2' ),
					'notice_can_activate_recommended' => _n_noop( 'Следующие рекомендованные плагин в настоящее время неактивным: %1$s.', 'Следующие Рекомендуемые плагины в настоящее время неактивны: %1$s.', 'the7mk2' ),
					'notice_cannot_activate'          => false,
					'notice_ask_to_update'            => _n_noop( 'Следующие плагин должен быть обновлен до последней версии для обеспечения максимальной совместимости с этой темой: %1$s.', 'Следующие плагины должны быть обновлены до последней версии их для обеспечения максимальной совместимости с этой темой: %1$s.', 'the7mk2' ),
					'notice_cannot_update'            => false,
					'install_link'                    => _n_noop( 'Начать установку плагина', 'Начать установку плагинов', 'the7mk2' ),
					'activate_link'                   => _n_noop( 'Активировать установленный плагин', 'Активация установленных плагинов', 'the7mk2' ),
					'return'                          => __( 'Вернуться к установителю необходимые плагины', 'the7mk2' ),
					'plugin_activated'                => __( 'Плагин активирован успешно.', 'the7mk2' ),
					'complete'                        => __( 'Все плагины установлены и успешно активирована. %s', 'the7mk2' ),
					'nag_type'                        => 'updated',
				),
			) );

			global $tgmpa;
			if ( $tgmpa && ! $tgmpa->is_tgmpa_complete() ) {
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'print_inline_js_action' ) );
			}
		}

		public static function print_inline_js_action() {
			?>
			<script type="text/javascript">
				jQuery(function($) {
					$('#setting-error-tgmpa .notice-dismiss').unbind().on('click.the7.tgmpa.dismiss', function(event) {
						location.href = $('#setting-error-tgmpa a.dismiss-notice').attr('href');
					});
				});
			</script>
			<?php
		}

		/**
		 * Fires on the page load.
		 */
		public static function on_page_load() {
			global $tgmpa;
			if ( $tgmpa ) {
				add_action( 'load-' . $tgmpa->page_hook, array( __CLASS__, 'remove_update_filters' ) );
			}
		}

		/**
		 * This function prevents plugin update api modification, so tgmpa can its job.
		 */
		public static function remove_update_filters() {
			$tgmpa_update = ( isset( $_GET['tgmpa-update'] ) ? $_GET['tgmpa-update'] : '' );

			if ( 'update-plugin' !== $tgmpa_update ) {
				return;
			}

			$tags_to_wipe = array(
				'pre_set_site_transient_update_plugins',
				'update_api',
				'upgrader_pre_download',
			);

			// Wipe out filters.
			foreach ( $tags_to_wipe as $tag ) {
				$GLOBALS['wp_filter'][ $tag ] = array();
				unset( $GLOBALS['merged_filters'][ $tag ] );
			}
		}
	}

	Presscore_Modules_TGMPAModule::execute();

endif;
