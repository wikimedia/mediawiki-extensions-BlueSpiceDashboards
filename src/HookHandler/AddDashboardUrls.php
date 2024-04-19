<?php

namespace BlueSpice\Dashboards\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;

class AddDashboardUrls implements SkinTemplateNavigation__UniversalHook {

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$user = $sktemplate->getUser();
		if ( !$user->isRegistered() ) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		$spFactory = $services->getSpecialPageFactory();
		$userGroupManager = $services->getUserGroupManager();

		$spUserDashboard = $spFactory->getPage( 'UserDashboard' );
		if ( $spUserDashboard ) {
			$links['user-menu']['userdashboard'] = [
				'id' => 'pt-userdashboard',
				'href' => \SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
				'text' => $spUserDashboard->getDescription(),
				'position' => 120,
			];
		}

		if ( in_array( 'sysop', $userGroupManager->getUserGroups( $user ) ) ) {
			$spAdminDashboard = $spFactory->getPage( 'AdminDashboard' );
			if ( $spUserDashboard ) {
				$links['user-menu']['admindashboard'] = [
					'id' => 'pt-admindashboard',
					'href' => \SpecialPage::getTitleFor( 'AdminDashboard' )->getLocalURL(),
					'text' => $spAdminDashboard->getDescription(),
					'position' => 130,
				];
			}
		}
	}
}
