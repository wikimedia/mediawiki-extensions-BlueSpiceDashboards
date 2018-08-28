<?php
/**
 * Dashboards for BlueSpice
 *
 * Provides dashboards for normal users and admins
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Dashboards
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for Dashboards extension
 * @package BlueSpice_Extensions
 * @subpackage Dashboards
 */
class Dashboards extends BsExtensionMW {

	/**
	 * Adds the table to the database
	 * @param DatabaseUpdater $updater
	 * @return boolean Always true to keep Hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_dashboards_configs',
			__DIR__ .'/db/mysql/bs_dashboards_configs.sql'
		);

		$updater->addPostDatabaseUpdateMaintenance( BSDashBoardsClearConfigMaintenance::class );
		return true;
	}

	protected static $aPageTagIdentifiers = array();

	/**
	 * AjaxDispatcher callback for saving a user portal config
	 * @return BsCAResponse
	 */
	public static function saveUserDashboardConfig() {
		$oResponse = BsCAResponse::newFromPermission( 'read' );
		$aPortalConfig = RequestContext::getMain()->getRequest()->getVal( 'portletConfig', '' );

		$oDbw = wfGetDB( DB_MASTER );
		$iUserId = RequestContext::getMain()->getUser()->getId();
		$oDbw->replace(
				'bs_dashboards_configs',
				array(
					'dc_identifier'
				),
				array(
					'dc_type' => 'user',
					'dc_identifier' => $iUserId,
					'dc_config' => serialize( $aPortalConfig ),
					'dc_timestamp' => '',
				),
				__METHOD__
		);

		return $oResponse;
	}
}