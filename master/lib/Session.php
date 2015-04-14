<?php

class Session
{
   function __construct()
   {
      self::initialize();

      if(isset($_COOKIE['SESSIONID']))
         session_start();
   }

   static function initialize()
   {
      // do not expose Cookie value to JavaScript (enforced by browser)
      ini_set('session.cookie_httponly', 1);

      if(Config::get('https_only') === true)
      {
         // only send cookie over https
         ini_set('session.cookie_secure', 1);
      }

      // prevent caching by sending no-cache header
      session_cache_limiter('nocache');

      // rename session
      session_name('SESSIONID');
   }

   static function getSessionId()
   {
      return session_id();
   }

   static function login($machineid, $secret)
   {
      $machines = new Machines();
      if(($machine = $machines->getMachine($machineid)) === false)
         return false;

      if($machine->getToken() !== $secret)
         return false;

      if(session_id() === '')
         session_start();

      /* login successfull */
      $_SESSION['authenticated'] = true;
      $_SESSION['machineid'] = $machine->getName();

      return true;
   }

   static function getMachineId()
   {
      if(isset($_SESSION['machineid']))
         return $_SESSION['machineid'];
      return false;
   }

   static function isAuthenticated()
   {
      return isset($_SESSION['authenticated']);
   }

   static function logout()
   {
      unset($_SESSION['authenticated']);
      unset($_SESSION['machineid']);

      session_destroy();
      return true;
   }

   static function generateRandomToken()
   {
      $cstrong = true;
      $bytes = '';

      for($i = 0; $i <= 32; $i++)
         $bytes .= bin2hex(openssl_random_pseudo_bytes(8, $cstrong));

      return hash('sha256', $bytes);
   }
}

