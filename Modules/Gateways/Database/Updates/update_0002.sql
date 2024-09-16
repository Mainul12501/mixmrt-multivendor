UPDATE addon_settings SET test_values = JSON_SET( COALESCE(test_values, '{}'), '$.merchant_secret', ''), live_values = JSON_SET( COALESCE(live_values, '{}'), '$.merchant_secret', '') WHERE key_name='esewa';
UPDATE addon_settings SET test_values = JSON_SET( COALESCE(test_values, '{}'), '$.am_merchant_code', ''), live_values = JSON_SET( COALESCE(live_values, '{}'), '$.am_merchant_code', '' ) WHERE key_name='pvit';

INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`) VALUES
('cb0081ce-d1ac-11ed-96f4-0c7a158e4469', 'engicell', '{\"gateway\":\"engicell\",\"mode\":\"test\",\"status\":0,\"api_key\":\"data\",\"sender_id\":\"data\",\"otp_template\":\"data\"}', '{\"gateway\":\"engicell\",\"mode\":\"live\",\"status\":0,\"api_key\":\"data\",\"sender_id\":\"data\",\"otp_template\":\"data\"}', 'sms_config', 'live', 0, NULL, '2023-04-10 02:14:44', NULL);
