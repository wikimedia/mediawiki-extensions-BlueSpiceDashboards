<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class BSDashboardsConvertToDynamicModules extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceFoundation' );
		$this->requireExtension( 'BlueSpiceDashboards' );
	}

	/**
	 *
	 * @return bool
	 */
	public function doDBUpdates() {
		$oDbr = $this->getDB( DB_REPLICA );
		$res = $oDbr->select( 'bs_dashboards_configs', [
			'dc_type', 'dc_identifier', 'dc_config'
		] );

		foreach ( $res as $row ) {
			$user = $row->dc_identifier;
			$type = $row->dc_type;

			if ( $type !== 'admin' && ( !$user || !$type ) ) {
				continue;
			}

			$isDefault = true;
			$defaults = [
				[],
				[],
			];

			Hooks::run( 'BSDashboardsUserDashboardPortalConfig', [
				$this,
				&$defaults,
				$isDefault
			] );

			$config = FormatJson::decode( $row->dc_config, 1 );
			foreach ( $config as &$col ) {
				foreach ( $col as &$portlet ) {
					$default = $this->getDefault( $portlet, $defaults );
					if ( !$default ) {
						continue;
					}
					if ( isset( $default['modules'] ) ) {
						if ( !is_array( $default['modules'] ) ) {
							$default['modules'] = [ $default['modules'] ];
						}
						$portlet['modules'] = $default['modules'];
					}
				}
			}

			$oDbw = $this->getDB( DB_MASTER );
			$oDbw->update(
				'bs_dashboards_configs',
				[
					// save json string into db
					'dc_config' => FormatJson::encode( $config )
				],
				[
					'dc_type' => $type,
					'dc_identifier' => $user
				]
			);
		}

		return true;
	}

	/**
	 * @param array $portlet
	 * @param array $defaults
	 * @return array|null
	 */
	protected function getDefault( $portlet, $defaults ) {
		foreach ( $defaults as $col ) {
			foreach ( $col as $default ) {
				if ( $default['type'] === $portlet['type'] ) {
					return $default;
				}
			}
		}

		return null;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_dashboards-convert-to-dynamic-modules';
	}
}

$maintClass = 'BSDashboardsConvertToDynamicModules';
require_once RUN_MAINTENANCE_IF_MAIN;
