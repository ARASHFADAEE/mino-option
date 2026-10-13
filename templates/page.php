<?php
/**
 * Settings page template.
 *
 * Variables available from PageRenderer:
 * @var string               $page_id
 * @var array<string,mixed>  $page
 * @var array<string,array>  $tabs
 * @var string               $active
 * @var array<string,mixed>  $values
 * @var string               $status
 *
 * @package Arash\SettingsFramework
 */

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Admin\FieldRenderer;
use Arash\SettingsFramework\Admin\SaveHandler;
use Arash\SettingsFramework\Security\RequestGuard;

$notice = get_transient( SaveHandler::notice_key( $page_id ) );
if ( false !== $notice ) {
	delete_transient( SaveHandler::notice_key( $page_id ) );
}
?>
<div class="wrap asf-settings" id="asf-settings-<?php echo esc_attr( $page_id ); ?>" data-asf-page="<?php echo esc_attr( $page_id ); ?>">
	<div class="asf-loading-overlay" hidden aria-hidden="true">
		<div class="asf-loader" role="status" aria-live="polite">
			<span class="asf-loader-spinner" aria-hidden="true"></span>
			<span class="asf-loader-text"><?php esc_html_e( 'Saving settings…', 'arash-settings-framework' ); ?></span>
		</div>
	</div>
	<div class="asf-toast" hidden role="status" aria-live="polite"></div>

	<header class="asf-header">
		<div class="asf-header-brand">
			<?php if ( ! empty( $page['logo'] ) ) : ?>
				<img class="asf-logo" src="<?php echo esc_url( (string) $page['logo'] ); ?>" alt="" />
			<?php else : ?>
				<span class="asf-logo-mark" aria-hidden="true"></span>
			<?php endif; ?>
			<div class="asf-header-text">
				<h1 class="asf-title"><?php echo esc_html( (string) $page['title'] ); ?></h1>
				<?php if ( ! empty( $page['description'] ) ) : ?>
					<p class="asf-subtitle"><?php echo esc_html( (string) $page['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<span class="asf-version"><?php echo esc_html( 'v' . ( defined( 'ASF_VERSION' ) ? ASF_VERSION : '1.0.0' ) ); ?></span>
	</header>

	<?php if ( 'saved' === $status ) : ?>
		<div class="notice notice-success is-dismissible asf-flash"><p><?php esc_html_e( 'Settings saved.', 'arash-settings-framework' ); ?></p></div>
	<?php elseif ( 'reset' === $status ) : ?>
		<div class="notice notice-success is-dismissible asf-flash"><p><?php esc_html_e( 'Settings reset to defaults.', 'arash-settings-framework' ); ?></p></div>
	<?php elseif ( 'error' === $status || ( is_array( $notice ) && 'error' === ( $notice['type'] ?? '' ) ) ) : ?>
		<div class="notice notice-error asf-flash" role="alert">
			<p><?php esc_html_e( 'Please fix the errors below and try again.', 'arash-settings-framework' ); ?></p>
			<?php if ( is_array( $notice ) && ! empty( $notice['errors'] ) ) : ?>
				<ul>
					<?php foreach ( $notice['errors'] as $msg ) : ?>
						<li><?php echo esc_html( (string) $msg ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( empty( $tabs ) ) : ?>
		<div class="asf-empty">
			<h2><?php esc_html_e( 'No settings registered', 'arash-settings-framework' ); ?></h2>
			<p><?php esc_html_e( 'Register tabs and fields with Settings::register_tab() to populate this page.', 'arash-settings-framework' ); ?></p>
		</div>
	<?php else : ?>
		<div class="asf-layout">
			<nav class="asf-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'arash-settings-framework' ); ?>">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<?php
					$is_active = ( $active === $tab_id );
					$tab_url   = add_query_arg(
						array(
							'page'    => (string) $page['menu_slug'],
							'asf_tab' => $tab_id,
						),
						admin_url( 'admin.php' )
					);
					?>
					<a
						href="<?php echo esc_url( $tab_url ); ?>"
						class="asf-tab<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						data-asf-tab="<?php echo esc_attr( $tab_id ); ?>"
						id="asf-tab-<?php echo esc_attr( $tab_id ); ?>"
					>
						<?php if ( ! empty( $tab['icon'] ) ) : ?>
							<span class="dashicons <?php echo esc_attr( (string) $tab['icon'] ); ?>" aria-hidden="true"></span>
						<?php endif; ?>
						<span class="asf-tab-label">
							<span class="asf-tab-title"><?php echo esc_html( (string) $tab['title'] ); ?></span>
							<?php if ( ! empty( $tab['description'] ) ) : ?>
								<span class="asf-tab-desc"><?php echo esc_html( (string) $tab['description'] ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="asf-panels">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<?php $is_active = ( $active === $tab_id ); ?>
					<section
						class="asf-panel<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tabpanel"
						id="asf-panel-<?php echo esc_attr( $tab_id ); ?>"
						aria-labelledby="asf-tab-<?php echo esc_attr( $tab_id ); ?>"
						<?php echo $is_active ? '' : 'hidden'; ?>
						data-asf-panel="<?php echo esc_attr( $tab_id ); ?>"
					>
						<?php if ( is_callable( $tab['callback'] ) ) : ?>
							<div class="asf-custom-tab">
								<?php call_user_func( $tab['callback'], $page_id, $tab, $values ); ?>
							</div>
						<?php elseif ( ! empty( $tab['template'] ) && ! empty( $tab['template_base'] ) ) : ?>
							<?php
							$resolved = RequestGuard::resolve_template( (string) $tab['template'], (string) $tab['template_base'] );
							if ( $resolved ) {
								include $resolved;
							} else {
								echo '<div class="notice notice-error"><p>' . esc_html__( 'Custom tab template could not be resolved safely.', 'arash-settings-framework' ) . '</p></div>';
							}
							?>
						<?php else : ?>
							<form method="post" action="" class="asf-form" novalidate>
								<?php RequestGuard::nonce_field( $page_id ); ?>
								<input type="hidden" name="asf_page_id" value="<?php echo esc_attr( $page_id ); ?>" />
								<input type="hidden" name="asf_action" value="save" />
								<input type="hidden" name="asf_tab" value="<?php echo esc_attr( $tab_id ); ?>" />

								<div class="asf-fields">
									<?php foreach ( $tab['fields'] as $field ) : ?>
										<?php
										$fid = (string) $field['id'];
										$val = array_key_exists( $fid, $values ) ? $values[ $fid ] : ( $field['default'] ?? null );
										FieldRenderer::render( $page_id, $field, $val );
										?>
									<?php endforeach; ?>
								</div>

								<?php if ( ! empty( $tab['fields'] ) ) : ?>
									<footer class="asf-actions">
										<button type="submit" class="button button-primary asf-save">
											<?php esc_html_e( 'Save changes', 'arash-settings-framework' ); ?>
										</button>
										<button type="submit" name="asf_action" value="reset" class="button asf-reset" data-asf-reset>
											<?php esc_html_e( 'Reset to defaults', 'arash-settings-framework' ); ?>
										</button>
										<span class="asf-save-status" aria-live="polite"></span>
									</footer>
								<?php endif; ?>
							</form>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
