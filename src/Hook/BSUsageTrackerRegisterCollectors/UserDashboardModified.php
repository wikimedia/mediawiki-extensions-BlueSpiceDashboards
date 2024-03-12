<?php

namespace BlueSpice\Dashboards\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;
use MediaWiki\MediaWikiServices;

class UserDashboardModified extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( 'dc_identifier' )
			->from( 'bs_dashboards_configs' )
			->where( [ 'dc_type' => 'user' ] )
			->caller( __METHOD__ )
			->fetchRowCount();

		$this->collectorConfig['admin-dashboard-modified'] = [
			'class' => 'Basic',
			'config' => [
				'identifier' => 'admin-dashboard-modified',
				'internalDesc' => 'Is the Admin Dashboard modified?',
				'count' => $res
			]
		];
	}
}
