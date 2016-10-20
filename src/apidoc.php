<?php  
/**
 * API document generation tool
 *
 * Copyright (c) 2016 lxw <http://www.lxlxw.me>
 * 
 * @link https://github.com/lxlxw/api-note-doc
 * @version 1.0
 */
namespace ApiDoc;

define('APP_PATH', rtrim(str_replace('\\', '/', dirname(__DIR__)), '/').'/');
define('DOC_PATH', rtrim(str_replace('\\', '/', dirname(__FILE__)), '/').'/');
define('CONF_PATH', APP_PATH."config/");
define('TEMP_PATH', APP_PATH."template/");

require __DIR__ . '/lib/BaseApiDoc.php';

use lib\base\ApiDocBase;

class ApiDoc extends ApiDocBase {

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
