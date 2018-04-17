<?php
return array (
  'backend' => 
  array (
    'frontName' => 'sec-2017-moma',
  ),
  'crypt' => 
  array (
    'key' => '5a4542476eb1631d7aad9bf0a8a832b4',
  ),
  'session' => 
  array (
    'save' => 'redis',
    'redis' => 
    array (
      'host' => 'momaredis.e0wsdj.0001.apse1.cache.amazonaws.com',
      'port' => '6379',
      'password' => '',
      'timeout' => '2.5',
      'persistent_identifier' => '',
      'database' => '3',
      'compression_threshold' => '2048',
      'compression_library' => 'gzip',
      'log_level' => '1',
      'max_concurrency' => '6',
      'break_after_frontend' => '5',
      'break_after_adminhtml' => '30',
      'first_lifetime' => '600',
      'bot_first_lifetime' => '60',
      'bot_lifetime' => '7200',
      'disable_locking' => '0',
      'min_lifetime' => '60',
      'max_lifetime' => '2592000',
    ),
  ),
  'db' => 
  array (
    'table_prefix' => '',
    'connection' => 
    array (
      'default' => 
      array (
        'host' => 'localhost',
        'dbname' => 'as_moma',
        'username' => 'root',
        'password' => 'Eason/0417',
        'active' => '1',
      ),
    ),
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'production',
  0 => 
  array (
    'frontend' => 
    array (
      'default' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => 'momaredis.e0wsdj.0001.apse1.cache.amazonaws.com',
          'database' => '0',
          'port' => '6379',
        ),
      ),
      'page_cache' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => 'momaredis.e0wsdj.0001.apse1.cache.amazonaws.com',
          'port' => '6379',
          'database' => '1',
          'compress_data' => '0',
        ),
      ),
    ),
  ),
  'cache_types' => 
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'customer_notification' => 1,
    'full_page' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'translate' => 1,
    'config_webservice' => 1,
    'amasty_shopby' => 1,
    'compiled_config' => 1,
  ),
  'install' => 
  array (
    'date' => 'Tue, 28 Mar 2017 14:45:20 +0000',
  ),
);
