<?php

namespace BlueSpice\Dashboards\Hook\PersonalUrls;

use BlueSpice\Hook\PersonalUrls;

class AddDashboardUrls extends PersonalUrls {

	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		return !$user->isLoggedIn();
	}

	protected function doProcess() {
		$user = $this->getContext()->getUser();
		$services = $this->getServices();
		$spFactory = $services->getSpecialPageFactory();

		$this->personal_urls['userdashboard'] = [
			'href' => \SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
			'text' => $spFactory->getPage( 'UserDashboard' )->getDescription(),
			'position' => 120,
		];

		$userGroupManager = $services->getUserGroupManager();
		if ( in_array( 'sysop', $userGroupManager->getUserGroups( $user ) ) ) {
			$this->personal_urls['admindashboard'] = [
				'href' => \SpecialPage::getTitleFor( 'AdminDashboard' )->getLocalURL(),
				'text' => $spFactory->getPage( 'AdminDashboard' )->getDescription(),
				'position' => 130,
			];
		}
		return true;
	}

}
