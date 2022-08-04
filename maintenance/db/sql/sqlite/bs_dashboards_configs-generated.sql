-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: extensions/BlueSpiceDashboards/maintenance/db/sql/bs_dashboards_configs.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/bs_dashboards_configs (
  dc_type BLOB DEFAULT 'user' NOT NULL,
  dc_identifier BLOB DEFAULT '0' NOT NULL,
  dc_timestamp BLOB NOT NULL,
  dc_config BLOB NOT NULL,
  PRIMARY KEY(
    dc_type, dc_identifier, dc_timestamp
  )
);
