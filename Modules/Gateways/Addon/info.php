<?php return array (
  'software_id' => '48481246',
  'name' => 'Payment & Sms gateways',
  'is_published' => 1,
  'database_migrated' => 0,
  'purchase_code' => 'stackmart',
  'username' => 'stackmart',
  'class_files_updated' => 1,
  'migrations' => 
  array (
    0 => 
    array (
      'key' => 'update_0001.sql',
      'value' => 1,
      'key_names' => 
      array (
        0 => 'instamojo',
        1 => 'phonepe',
        2 => 'cashfree',
        3 => 'lenco',
      ),
      'settings_type' => 'payment_config',
    ),
    1 => 
    array (
      'key' => 'update_0002.sql',
      'value' => 1,
      'key_names' => 
      array (
        0 => 'engicell',
      ),
      'settings_type' => 'payment_config',
    ),
  ),
);