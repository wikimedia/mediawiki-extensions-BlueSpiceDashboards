( function ( mw ) {
	mw.loader.using( mw.config.get( 'bsPortalDependencies' ) ).done( function() {
		Ext.onReady( function () {
			Ext.create( 'BS.Dashboards.DashboardPanel', {
				renderTo: 'bs-dashboards-admindashboard',
				portalConfig: mw.config.get( 'bsPortalConfig' )
			} );
		} );
	} );
} )( mediaWiki );
