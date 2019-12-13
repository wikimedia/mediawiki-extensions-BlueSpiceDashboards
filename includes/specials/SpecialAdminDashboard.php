<?php

class SpecialAdminDashboard extends \BlueSpice\SpecialPage {
	/**
	 *
	 * @param string $name
	 * @param string $restriction
	 * @param bool $listed
	 * @param mixed $function
	 * @param string $file
	 * @param bool $includable
	 */
	public function __construct( $name = '', $restriction = '', $listed = true, $function = false,
		$file = 'default', $includable = false ) {
		parent::__construct( 'AdminDashboard', 'wikiadmin' );
	}

	/**
	 *
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->checkForReadOnly();
		$this->getAdminConfig();

		$this->getOutput()->addHTML(
			Html::element( 'div', [ 'id' => 'bs-dashboards-admindashboard' ] )
		);
	}

	private function getAdminConfig() {
		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
				'bs_dashboards_configs',
				'*',
				[ 'dc_type' => 'admin' ],
				__METHOD__
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$row = $oDbr->fetchObject( $res );
			$aPortalConfig = $row->dc_config;
			$aPortalConfig = FormatJson::decode( $aPortalConfig );
		} else {
			$bIsDefault = true;
			$aPortalConfig = [
				[],
				[],
				[]
			];

			Hooks::run( 'BSDashboardsAdminDashboardPortalConfig', [
				$this,
				&$aPortalConfig,
				$bIsDefault
			] );
		}

		$sSaveBackend = 'saveAdminDashboardConfig';
		$sLocation = 'AdminDashboard';
		$this->getOutput()->addJsConfigVars( 'bsPortalConfigSavebackend', $sSaveBackend );
		$this->getOutput()->addJsConfigVars( 'bsPortalConfigLocation', $sLocation );
		$this->getOutput()->addJsConfigVars( 'bsPortalConfig', $aPortalConfig );

		$this->getOutput()->addModuleStyles( 'ext.bluespice.extjs.BS.portal.css' );
		$this->getOutput()->addModules( 'ext.bluespice.dashboards.adminDashboard' );

		return true;
	}

	private function checkForReadOnly() {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$msg = $this->msg( 'bs-readonly', $wgReadOnly );
			$this->getOutput()->addHTML(
				'<script>var wgReadOnly = true; alert("' . $msg->escaped() . '");</script>'
			);

			return true;
		}

		return false;
	}

}
