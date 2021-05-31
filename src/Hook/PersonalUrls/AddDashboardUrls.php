<?php

namespace BlueSpice\Dashboards\Hook\PersonalUrls;

use BlueSpice\Hook\PersonalUrls;
use MediaWiki\MediaWikiServices;

class AddDashboardUrls extends PersonalUrls {

	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		return !$user->isRegistered();
	}

	protected function doProcess() {
		$user = $this->getContext()->getUser();
		$services = MediaWikiServices::getInstance();
		$spFactory = $services->getSpecialPageFactory();
		$userGroupManager = $services->getUserGroupManager();

		$this->personal_urls['userdashboard'] = [
			'href' => \SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
			'text' => $spFactory->getPage( 'UserDashboard' )->getDescription()
		];

		if ( in_array( 'sysop', $userGroupManager->getUserGroups( $user ) ) ) {
			$this->personal_urls['admindashboard'] = [
				'href' => \SpecialPage::getTitleFor( 'AdminDashboard' )->getLocalURL(),
				'text' => $spFactory->getPage( 'AdminDashboard' )->getDescription()
			];
		}
		return true;
	}

}
