<?php defined('SYSPATH') OR die('No direct script access.');

return array(
  // Leave this alone
  'modules' => array(

    // This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
    'kohana-huia-api' => array(

      // Whether this modules userguide pages should be shown
      'enabled' => TRUE,
      
      // The name that should show up on the userguide index page
      'name' => 'Huia API',

      // A short description of this module, shown on the index page
      'description' => 'API module for Huia Framework.',
      
      // Copyright message, shown in the footer for this module
      'copyright' => '&copy; 2015 Huia Team',
    ) 
  )
);