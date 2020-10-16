<?php

abstract class SpecialDashboard extends \BlueSpice\SpecialPage {

	/**
	 *
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->checkForReadOnly();

		$this->getPortalConfig();
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfigSavebackend', $this->getSaveBackend()
		);
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfigLocation', $this->getLocation()
		);

		$this->addModules( $this->getOutput() );
		$this->getOutput()->addHTML(
			Html::element( 'div', [ 'id' => $this->getContainerId() ] )
		);
	}

	/**
	 * @throws Exception
	 */
	protected function getPortalConfig() {
		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
			'bs_dashboards_configs',
			'*',
			$this->getConds(),
			__METHOD__
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$row = $oDbr->fetchObject( $res );
			$aPortalConfig = FormatJson::decode( $row->dc_config, 1 );
		} else {
			$bIsDefault = true;
			$aPortalConfig = [
				[],
				[],
			];

			Hooks::run( 'BSDashboardsUserDashboardPortalConfig', [
				$this,
				&$aPortalConfig,
				$bIsDefault
			] );
		}

		$this->getOutput()->addJsConfigVars(
			'bsPortalDependencies', $this->extractModules( $aPortalConfig )
		);
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfig',
			$this->balance( $aPortalConfig )
		);
	}

	/**
	 * Get conditions for the query
	 *
	 * @return array
	 */
	abstract protected function getConds(): array;

	/**
	 * @return bool
	 */
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

	/**
	 * @param array $config
	 * @return array
	 */
	private function balance( array $config ) {
		if ( abs( count( $config[0] ) - count( $config[1] ) ) < 3 ) {
			return $config;
		}

		$greater = count( $config[0] ) > count( $config[1] ) ? 0 : 1;
		$smaller = (int)!$greater;
		$evenDiff = floor( ( count( $config[$greater] ) - count( $config[$smaller] ) ) / 2 );
		$config[$smaller] = array_merge(
			$config[$smaller], array_slice( $config[$greater], -$evenDiff, $evenDiff )
		);
		$config[$greater] = array_diff( $config[$greater], $config[$smaller] );

		return $config;
	}

	/**
	 * Get key for the save backend
	 * @return string
	 */
	abstract protected function getSaveBackend(): string;

	/**
	 * Get location key
	 * @return string
	 */
	abstract protected function getLocation(): string;

	/**
	 * Get HTML container id
	 *
	 * @return string
	 */
	abstract protected function getContainerId(): string;

	/**
	 * @param OutputPage $out
	 */
	protected function addModules( OutputPage $out ) {
		// STUB
	}

	/**
	 * Add modules from portlets
	 *
	 * @param array $portalConfig
	 * @return array
	 */
	protected function extractModules( array $portalConfig ) {
		$all = array_merge( $portalConfig[0], $portalConfig[1] );
		$modules = [];
		foreach ( $all as $portlet ) {
			if ( !isset( $portlet['modules'] ) ) {
				continue;
			}
			$portletModules = $portlet['modules'];
			if ( !is_array( $portletModules ) ) {
				$portletModules = [ $portletModules ];
			}
			$modules = array_merge( $modules, $portletModules );
		}

		return array_unique( $modules );
	}

}
