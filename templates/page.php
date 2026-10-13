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
 * @package Mino\Option
 */

defined( 'ABSPATH' ) || exit;

use Mino\Option\Admin\FieldRenderer;
use Mino\Option\Admin\SaveHandler;
use Mino\Option\Security\RequestGuard;

$notice = get_transient( SaveHandler::notice_key( $page_id ) );
if ( false !== $notice ) {
	delete_transient( SaveHandler::notice_key( $page_id ) );
}
?>
<div class="wrap mino-settings" id="mino-settings-<?php echo esc_attr( $page_id ); ?>" data-mino-page="<?php echo esc_attr( $page_id ); ?>">
	<div class="mino-loading-overlay" hidden aria-hidden="true">
		<div class="mino-loader" role="status" aria-live="polite">
			<span class="mino-loader-spinner" aria-hidden="true"></span>
			<span class="mino-loader-text"><?php esc_html_e( 'Saving settings…', 'mino-option' ); ?></span>
		</div>
	</div>
	<div class="mino-toast" hidden role="status" aria-live="polite"></div>

	<header class="mino-header">
		<div class="mino-header-brand">
			<?php if ( ! empty( $page['logo'] ) ) : ?>
				<img class="mino-logo" src="<?php echo esc_url( (string) $page['logo'] ); ?>" alt="" />
			<?php else : ?>
				<span class="mino-logo-mark" aria-hidden="true"></span>
			<?php endif; ?>
			<div class="mino-header-text">
				<h1 class="mino-title"><?php echo esc_html( (string) $page['title'] ); ?></h1>
				<?php if ( ! empty( $page['description'] ) ) : ?>
					<p class="mino-subtitle"><?php echo esc_html( (string) $page['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<span class="mino-version"><?php echo esc_html( 'v' . ( defined( 'MINO_VERSION' ) ? MINO_VERSION : '1.0.0' ) ); ?></span>
	</header>

	<?php if ( 'saved' === $status ) : ?>
		<div class="notice notice-success is-dismissible mino-flash"><p><?php esc_html_e( 'Settings saved.', 'mino-option' ); ?></p></div>
	<?php elseif ( 'reset' === $status ) : ?>
		<div class="notice notice-success is-dismissible mino-flash"><p><?php esc_html_e( 'Settings reset to defaults.', 'mino-option' ); ?></p></div>
	<?php elseif ( 'error' === $status || ( is_array( $notice ) && 'error' === ( $notice['type'] ?? '' ) ) ) : ?>
		<div class="notice notice-error mino-flash" role="alert">
			<p><?php esc_html_e( 'Please fix the errors below and try again.', 'mino-option' ); ?></p>
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
		<div class="mino-empty">
			<h2><?php esc_html_e( 'No settings registered', 'mino-option' ); ?></h2>
			<p><?php esc_html_e( 'Register tabs and fields with Settings::register_tab() to populate this page.', 'mino-option' ); ?></p>
		</div>
	<?php else : ?>
		<div class="mino-layout">
			<nav class="mino-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'mino-option' ); ?>">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<?php
					$is_active = ( $active === $tab_id );
					$tab_url   = add_query_arg(
						array(
							'page'    => (string) $page['menu_slug'],
							'mino_tab' => $tab_id,
						),
						admin_url( 'admin.php' )
					);
					?>
					<a
						href="<?php echo esc_url( $tab_url ); ?>"
						class="mino-tab<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						data-mino-tab="<?php echo esc_attr( $tab_id ); ?>"
						id="mino-tab-<?php echo esc_attr( $tab_id ); ?>"
					>
						<?php if ( ! empty( $tab['icon'] ) ) : ?>
							<span class="dashicons <?php echo esc_attr( (string) $tab['icon'] ); ?>" aria-hidden="true"></span>
						<?php endif; ?>
						<span class="mino-tab-label">
							<span class="mino-tab-title"><?php echo esc_html( (string) $tab['title'] ); ?></span>
							<?php if ( ! empty( $tab['description'] ) ) : ?>
								<span class="mino-tab-desc"><?php echo esc_html( (string) $tab['description'] ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="mino-panels">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<?php $is_active = ( $active === $tab_id ); ?>
					<section
						class="mino-panel<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tabpanel"
						id="mino-panel-<?php echo esc_attr( $tab_id ); ?>"
						aria-labelledby="mino-tab-<?php echo esc_attr( $tab_id ); ?>"
						<?php echo $is_active ? '' : 'hidden'; ?>
						data-mino-panel="<?php echo esc_attr( $tab_id ); ?>"
					>
						<?php if ( is_callable( $tab['callback'] ) ) : ?>
							<div class="mino-custom-tab">
								<?php call_user_func( $tab['callback'], $page_id, $tab, $values ); ?>
							</div>
						<?php elseif ( ! empty( $tab['template'] ) && ! empty( $tab['template_base'] ) ) : ?>
							<?php
							$resolved = RequestGuard::resolve_template( (string) $tab['template'], (string) $tab['template_base'] );
							if ( $resolved ) {
								include $resolved;
							} else {
								echo '<div class="notice notice-error"><p>' . esc_html__( 'Custom tab template could not be resolved safely.', 'mino-option' ) . '</p></div>';
							}
							?>
						<?php else : ?>
							<form method="post" action="" class="mino-form" novalidate>
								<?php RequestGuard::nonce_field( $page_id ); ?>
								<input type="hidden" name="mino_page_id" value="<?php echo esc_attr( $page_id ); ?>" />
								<input type="hidden" name="mino_action" value="save" />
								<input type="hidden" name="mino_tab" value="<?php echo esc_attr( $tab_id ); ?>" />

								<div class="mino-fields">
									<?php foreach ( $tab['fields'] as $field ) : ?>
										<?php
										$fid = (string) $field['id'];
										$val = array_key_exists( $fid, $values ) ? $values[ $fid ] : ( $field['default'] ?? null );
										FieldRenderer::render( $page_id, $field, $val );
										?>
									<?php endforeach; ?>
								</div>

								<?php if ( ! empty( $tab['fields'] ) ) : ?>
									<footer class="mino-actions">
										<button type="submit" class="button button-primary mino-save">
											<?php esc_html_e( 'Save changes', 'mino-option' ); ?>
										</button>
										<button type="submit" name="mino_action" value="reset" class="button mino-reset" data-mino-reset>
											<?php esc_html_e( 'Reset to defaults', 'mino-option' ); ?>
										</button>
										<span class="mino-save-status" aria-live="polite"></span>
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
