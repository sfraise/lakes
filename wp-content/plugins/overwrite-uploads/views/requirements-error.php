<div class="error">
	<p>Overwrite Uploads error: Your environment doesn't meet all of the system requirements listed below.</p>

	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo OVUP_REQUIRED_PHP_VERSION; ?>+</strong>
			<em>(You're running version <?php echo PHP_VERSION; ?>)</em>
		</li>

		<li>
			<strong>WordPress <?php echo OVUP_REQUIRED_WP_VERSION; ?>+</strong>
			<em>(You're running version <?php echo esc_html( $wp_version ); ?>)</em>
		</li>

		<li>Overwrite Uploads requires a new filter to be added to Wordpress. If this is a new installation or you recently upgraded Wordpress, please see the installation instructions on <a href="http://wordpress.org/extend/plugins/overwrite-uploads/installation/">the Installation page</a> for information on adding it.</li>
	</ul>

	<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.</p>
</div>