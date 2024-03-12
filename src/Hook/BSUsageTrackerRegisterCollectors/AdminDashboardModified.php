<?php

namespace BlueSpice\Dashboards\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;
use MediaWiki\MediaWikiServices;

class AdminDashboardModified extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( 'dc_identifier' )
			->from( 'bs_dashboards_configs' )
			->where( [ 'dc_type' => 'admin' ] )
			->caller( __METHOD__ )
			->fetchRowCount();

		$this->collectorConfig['user-dashboard-modified'] = [
			'class' => 'Basic',
			'config' => [
				'identifier' => 'user-dashboard-modified',
				'internalDesc' => 'Is the User Dashboard modified?',
				'count' => $res
			]
		];
	}
}
