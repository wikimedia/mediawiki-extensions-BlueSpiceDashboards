<?php

namespace BlueSpice\Dashboards\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;

class AddDashboardUrls implements SkinTemplateNavigation__UniversalHook {
	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

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
		$spUserDashboard = $spFactory->getPage( 'UserDashboard' );

		if ( $this->permissionManager->userHasRight( $user, 'dashboards-viewspecialpage-userdashboard' ) ) {
			if ( $spUserDashboard ) {
				$links['user-menu']['userdashboard'] = [
					'id' => 'pt-userdashboard',
					'href' => \SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
					'text' => $spUserDashboard->getDescription(),
					'position' => 120,
				];
			}
		}

		if ( $this->permissionManager->userHasRight( $user, 'wikiadmin' ) ) {
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
