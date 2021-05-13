<?php
namespace TymFrontiers\HTTP;
use \TymFrontiers\Generic;

class Header{
  const BAD_REQUEST = 400;
  const UNAUTHORIZED = 401;
  const FORBIDDEN = 403;
  const NOT_FOUND = 404;
  const INTERNAL_ERROR = 500;

  protected static $_codes = [
    100 => 'HTTP/1.1 100 Continue',
    101 => 'HTTP/1.1 101 Switching Protocols',
    200 => 'HTTP/1.1 200 OK',
    201 => 'HTTP/1.1 201 Created',
    202 => 'HTTP/1.1 202 Accepted',
    203 => 'HTTP/1.1 203 Non-Authoritative Information',
    204 => 'HTTP/1.1 204 No Content',
    205 => 'HTTP/1.1 205 Reset Content',
    206 => 'HTTP/1.1 206 Partial Content',
    300 => 'HTTP/1.1 300 Multiple Choices',
    301 => 'HTTP/1.1 301 Moved Permanently',
    302 => 'HTTP/1.1 302 Found',
    303 => 'HTTP/1.1 303 See Other',
    304 => 'HTTP/1.1 304 Not Modified',
    305 => 'HTTP/1.1 305 Use Proxy',
    307 => 'HTTP/1.1 307 Temporary Redirect',
    400 => 'HTTP/1.1 400 Bad Request',
    401 => 'HTTP/1.1 401 Unauthorized',
    402 => 'HTTP/1.1 402 Payment Required',
    403 => 'HTTP/1.1 403 Forbidden',
    404 => 'HTTP/1.1 404 Not Found',
    405 => 'HTTP/1.1 405 Method Not Allowed',
    406 => 'HTTP/1.1 406 Not Acceptable',
    407 => 'HTTP/1.1 407 Proxy Authentication Required',
    408 => 'HTTP/1.1 408 Request Time-out',
    409 => 'HTTP/1.1 409 Conflict',
    410 => 'HTTP/1.1 410 Gone',
    411 => 'HTTP/1.1 411 Length Required',
    412 => 'HTTP/1.1 412 Precondition Failed',
    413 => 'HTTP/1.1 413 Request Entity Too Large',
    414 => 'HTTP/1.1 414 Request-URI Too Large',
    415 => 'HTTP/1.1 415 Unsupported Media Type',
    416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
    417 => 'HTTP/1.1 417 Expectation Failed',
    500 => 'HTTP/1.1 500 Internal Server Error',
    501 => 'HTTP/1.1 501 Not Implemented',
    502 => 'HTTP/1.1 502 Bad Gateway',
    503 => 'HTTP/1.1 503 Service Unavailable',
    504 => 'HTTP/1.1 504 Gateway Time-out',
    505 => 'HTTP/1.1 505 HTTP Version Not Supported'
  ];

  function __construct(int $code=0){
    if( $code ) self::send($code);
  }

  public static function send(int $code, array $add_header=[]){
    if( !empty($add_header) ){
      foreach($add_header as $key=>$val){
        \header('X-Tym-'.$key.': '.$val);
      }
    }
    if( array_key_exists($code, self::$_codes) ){
      \header(self::$_codes[$code]);
      exit;
    }
  }
  public static function redirect(string $to,string $message='', array $add_header=[]){
    if( !empty($message) ) $to .= Generic::setGet($to,['message'=>\urlencode($message)]);
    if( !empty($add_header) ){
      foreach($add_header as $key=>$val){
        \header('X-Tym-'.$key.': '.$val);
      }
    }
    \header('Location: '.$to);
    exit;
  }
  public static function notFound(bool $redirect=false,string $message='', array $add_header=[]){
    global $_SERVER;
    self::_do(self::NOT_FOUND,$redirect,$_SERVER['REQUEST_URI'],$message, $add_header);
  }
  public static function badRequest(bool $redirect=false,string $message='', array $add_header=[]){
    global $_SERVER;
    self::_do(self::BAD_REQUEST,$redirect,$_SERVER['REQUEST_URI'],$message, $add_header);
  }
  public static function internalError(bool $redirect=false,string $message='', array $add_header=[]){
    global $_SERVER;
    self::_do(self::INTERNAL_ERROR,$redirect,$_SERVER['REQUEST_URI'],$message, $add_header);
  }
  public static function forbidden(bool $redirect=false,string $message='', array $add_header=[]){
    global $_SERVER;
    self::_do(self::FORBIDDEN,$redirect,$_SERVER['REQUEST_URI'],$message, $add_header);
  }
  public static function unauthorized(bool $redirect=false,string $message='', array $add_header=[]){
    global $_SERVER;
    self::_do(self::UNAUTHORIZED,$redirect,$_SERVER['REQUEST_URI'],$message, $add_header);
  }
  public static function refresh(string $to, int $tym=10, string $message=''){
    $message = !empty($message) ? $message : "You will be redirected in {$tym} seconds";
    \header("Refresh: {$tym}; url={$to}");
    print $message;
  }
  public static function poweredBy(string $by){
    \header('X-Powered-By: '.$by);
  }
  public static function language(string $lang){
    \header('Content-language: '.$lang);
  }
  public static function length(string $len){
    \header('Content-Length: '.$len);
  }
  public static function authDialog(string $message="You are not allowed access."){
    \header('HTTP/1.1 401 Unauthorized');
    \header('WWW-Authenticate: Basic realm="Top Secret"');
    print $message;
  }
  public static function noCache(){
    \header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    \header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    \header('Pragma: no-cache');
  }
  private static function _do(int $code,bool $redirect, string $request, string $message='', array $add_header=[]){
    if( $redirect ){
      $rdt = \defined('REQUEST_SCHEME') ? REQUEST_SCHEME : 'http://';
      $rdt .= $_SERVER['HTTP_HOST'];
      $rdt .= '/' . $code;
      $query = [];
      if( !empty($request) ) $query["request"] = $request;
      if( !empty($message) ) $query["message"] = $message;
      if( !empty($query) ) $rdt = Generic::setGet($rdt,$query);
      self::redirect($rdt,$message, $add_header);
    } else{
      self::send($code, $add_header);
    }

  }
  public static function message(int $code){
    return \array_key_exists($code,self::$_codes) ?
    \str_replace('HTTP/1.1 ','',self::$_codes[$code]) : null;
  }

  public static function getSpecial(string $find_key=''){
    $out = []; $find = 'X-Tym-';
    $h_received = \apache_response_headers();
    foreach($h_received as $key=>$val){
      if( \strpos($key,$find) === false){
        # was not found
      }else{
        $out[\str_replace($find,'',$key)] = $val;
      }
    }
    if( !empty($find_key) ){
      return \in_array($find_key,$out) ? $out[$find_key] : null;
    }else{
      return $out;
    }
  }
}
