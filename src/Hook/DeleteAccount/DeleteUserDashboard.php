<?php

namespace BlueSpice\Dashboards\Hook\DeleteAccount;

use BlueSpice\DistributionConnector\Hook\DeleteAccount;

class DeleteUserDashboard extends DeleteAccount {

	protected function doProcess() {
		$this->getServices()->getDBLoadBalancer()->getConnection( DB_PRIMARY )->delete(
			'bs_dashboards_configs',
			[ 'dc_identifier' => $this->oldUser->getid(), 'dc_type' => 'user' ],
			__METHOD__
		);
	}

}
