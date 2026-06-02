<?php
defined( 'ABSPATH' ) || exit;

class WM_Deactivator {

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
