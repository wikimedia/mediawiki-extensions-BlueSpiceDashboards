<?php

abstract class SpecialDashboard extends \BlueSpice\SpecialPage {

	/**
	 *
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->checkReadOnly();

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
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$row = $dbr->selectRow(
			'bs_dashboards_configs',
			'*',
			$this->getConds(),
			__METHOD__
		);

		$dbConfig = null;
		if ( $row ) {
			$dbConfig = FormatJson::decode( $row->dc_config, 1 );
		}

		$isDefault = true;
		$portalConfig = [
			[],
			[],
		];

		$hookContainer = $this->services->getHookContainer();
		$hookContainer->run( 'BSDashboardsUserDashboardPortalConfig', [
			$this,
			&$portalConfig,
			$isDefault
		] );

		$outputConfig = $this->balance( $portalConfig );
		if ( $dbConfig !== null ) {
			$outputConfig = $dbConfig;
		}

		$this->getOutput()->addJsConfigVars(
			'bsPortalDependencies', $this->getModulesFromConfig( $portalConfig )
		);
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfig',
			$outputConfig
		);
	}

	/**
	 * Get conditions for the query
	 *
	 * @return array
	 */
	abstract protected function getConds(): array;

	/**
	 * Not used currently - please dont delete
	 *
	 * @param array $config
	 * @return array
	 */
	private function balance( array $config ) {
		if ( count( $config ) !== 2 ) {
			return $config;
		}
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
	 * Get all required RL modules from portal config retrieved by hook
	 *
	 * @param array $portalConfig
	 * @return array
	 */
	protected function getModulesFromConfig( $portalConfig ) {
		$all = array_merge( ...$portalConfig );
		$modules = [];
		foreach ( $all as $portlet ) {
			if ( !isset( $portlet['modules'] ) || !isset( $portlet['type'] ) ) {
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

	/**
	 * @param string $portletType
	 * @param array $defaults
	 * @return array|null
	 */
	protected function getPortletForType( $portletType, array $defaults ) {
		foreach ( $defaults as $col ) {
			foreach ( $col as $default ) {
				if ( $default['type'] === $portletType ) {
					return $default;
				}
			}
		}

		return null;
	}

}
