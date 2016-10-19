<?php  
/**
 * API document generation tool
 *
 * Copyright (c) 2016 lxw <http://www.lxlxw.me>
 * @version 1.0
 */

define('APP_PATH', rtrim(str_replace('\\', '/', dirname(__DIR__)), '/').'/');
define('DOC_PATH', rtrim(str_replace('\\', '/', dirname(__FILE__)), '/').'/');
define('CONF_PATH', APP_PATH."../config/");
define('TEMP_PATH', APP_PATH."../template/");

require __DIR__ . '/lib/BaseApiDoc.php';

use lib\base\ApiDocBase;

class Apidoc extends ApiDocBase {

    public function __construct()
    {
        parent::__construct();
    }
    
    public function build()
    {
        return $this->buildDoc();
    }
    
    public function set($data = [])
    {
        return $this->setconf($data);
    }
    
}
?>