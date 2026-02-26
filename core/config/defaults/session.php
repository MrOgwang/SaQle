<?php

/**
 * Session configurations
 * */

use SaQle\Session\SessionHandler;

return [
      /**
      * Session domain
      * 
      * Defines which domain the session cookie is valid for
      * Example: 
      * - If set to .example.com, the cookie works for www.example.com, api.example.com, app.example.com.
      * - If left blank, it defaults to the current host only
      * 
      * This is important if you want a single signon across multiple sub domains
      * */
      'cookie_domain' => '',

      /**
      * Session handler
      * 
      * This is the mechanism by which session data is stored and retrieved
      * 
      * By default PHP saves sessions in files on disk (/tmp/sess_xxx)
      * But you can override this with your own storage (e.g., database, Redis, Memcached).
      * */
      'handler' => SessionHandler::class,

      /**
      * Session gc maxlifetime
      * 
      * Lifetime (in seconds) of session data stored on the server before garbage collection may delete it.
      * 
      * Controls how long session data exists on the server. If too low, users get logged out often
      * 
      * Defaults to 1440 seconds(24 minutes)
      * 
      * */
     'gc_maxlifetime' => 1440,

      /**
       * Session cookie lifetime
       * 
       * Lifetime (in seconds) of the cookie on the client's browser.
       * 
       * Controls how long the browser remembers the session ID.
       * 
       * Defaults to 0 - which means until browser is closed
       * */
     'cookie_lifetime' => 0,

     /**
      * Session gc probability and Session gc divisor
      * 
      * Controls how often the garbage collection is run
      * Example:
      * probability / divisor = chance that this request will trigger GC.
      * gc_probability = 1, gc_divisor = 100 → 1% chance per request.
      * gc_probability = 1, gc_divisor = 1 → always run GC.
      * 
      * Garbage collection is expensive, so PHP doesn't run it on every request. 
      * Tuning this helps balance between stale session files vs. performance.
      * */
     'gc_probability' => 1,
     'gc_divisor' => 100,
 ]
?>