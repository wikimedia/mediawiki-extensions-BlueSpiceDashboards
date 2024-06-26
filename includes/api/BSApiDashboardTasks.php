<?php

use BlueSpice\Api\Response\Standard;

class BSApiDashboardTasks extends BSApiTasksBase {

	/**
	 *
	 * @var array
	 */
	protected $aTasks = [
		'saveAdminDashboardConfig' => [
			'examples' => [
				[
					'portletConfig' => [ [
							'someKey' => 'someValue',
							'otherKey' => 'otherValue'
					] ]
				]
			],
			'params' => [
				'portletConfig' => [
					'desc' => 'Array containing valid json encoded portlet configuration '
						. 'in form of { key: "value" }',
					'type' => 'array',
					'required' => true
				]

			]
		],
		'saveUserDashboardConfig' => [
			'examples' => [
				[
					'portletConfig' => [ [
						'someKey' => 'someValue',
						'otherKey' => 'otherValue'
					] ]
				]
			],
			'params' => [
				'portletConfig' => [
					'desc' => 'Array containing valid json encoded portlet configuration '
						. 'in form of { key: "value" }',
					'type' => 'array',
					'required' => true
				]
			]
		]
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'saveAdminDashboardConfig' => [ 'wikiadmin' ],
			'saveUserDashboardConfig' => [ 'read' ]
		];
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_saveUserDashboardConfig( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		if ( $this->getUser()->isAnon() ) {
			$oResponse->message = wfMessage( 'bs-permissionerror' )->plain();
			return $oResponse;
		}

		$aPortletConfig = $oTaskData->portletConfig[0];
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$oResponse->message = wfMessage( 'api-error-missingparam' )->plain();
			return $oResponse;
		}

		$dbw = $this->services->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$iUserId = $this->getUser()->getId();
		$dbw->replace(
				'bs_dashboards_configs',
				[
					'dc_identifier'
				],
				[
					'dc_type' => 'user',
					'dc_identifier' => $iUserId,
					'dc_config' => $aPortletConfig,
					'dc_timestamp' => '',
				],
				__METHOD__
		);

		$oResponse->success = true;
		return $oResponse;
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_saveAdminDashboardConfig( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$aPortletConfig = $oTaskData->portletConfig[0];
		json_decode( $aPortletConfig );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$oResponse->message = wfMessage( 'api-error-missingparam' )->plain();
			return $oResponse;
		}

		$dbw = $this->services->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$dbw->delete(
			'bs_dashboards_configs',
			[ 'dc_type' => 'admin' ]
		);
		$dbw->insert(
			'bs_dashboards_configs',
			[
				'dc_type' => 'admin',
				'dc_identifier' => '',
				'dc_config' => $aPortletConfig,
				'dc_timestamp' => '',
			],
			__METHOD__
		);

		$oResponse->success = true;
		return $oResponse;
	}

}
