# api-note-doc
根据注释生成markdown或html形式的API接口文档


* [描述/说明](#describe)
* [环境](#requirements)
* [安装](#install)
* [使用](#demo)
* [配置](#config)
* [展示](#show)
* [TODO](#todo)

### <a id="describe"></a>描述/说明

1/一个根据controller类/方法的注释可以生成markdown或html形式的API接口文档。

2/模版默认为markdown，亦可自定义模版。

3/依赖于composer管理。


### <a id="requirements"></a>环境

1/PHP >= 5.4 or newer

2/composer

### <a id="install"></a>安装

1/添加下面一行到你项目的composer.json中。
```json
{
    "require": {
        ...
        "lxlxw/api-doc": "dev-master"
    }
}
```

2/更新vendor包
```bash
$ php composer.phar update
```

### <a id="demo"></a>使用

1/要生成注释的接口类/方法 #testController.php
```php
<?php 
/**
 * @author lxw
 * @group(name="test", description="test")
 */

class Doc {
    
    function __construct() {
        parent::__construct();
    }

    /**
     * @ApiDescription(这是接口的描述)
     * @ApiMethod(post)
     * @ApiUrl(192.168.0.1:80/doc/test)
     * @ApiNotice(这是接口的说明)
     * @ApiSuccess(value="{'firstname' : 'lxw', 'lastname'  : 'lxlxw', 'lastLogin' : '2016-11-11'}")
     * @ApiExample(value="{'username':'lxw','password':'123456'}")
     * @ApiParams(name="id", type="integer", is_selected=true, description="User id")
     * @ApiParams(name="sort", type="enum[asc,desc]", description="User data")
     * @ApiReturn(name="id", type="integer", description="User id")
     * @ApiReturn(name="sort", type="enum[asc,desc]", description="sort data")
     * @ApiReturn(name="page", type="integer", description="data of page")
     * @ApiReturn(name="count", type="integer", description="data of page")
     */
    function test(){
        echo 'hello';
    }
}
```

2/生成文档处理程序 #apidoc.php

```php
<?php

require 'vendor/autoload.php';

$obj = new ApiDoc\ApiDoc();

//直接生成文档，采用程序默认配置
//$obj->build();

//根据配置的参数生成文档
$config = [
            'build_path' => __DIR__,
            'vender_path' => __DIR__ . "/../vender/",
            'template' => 'default',
            'template_ext' => '.md'
          ];
$obj->set($config)->build();
```

### <a id="config"></a>配置

#TODO:

### <a id="show"></a>展示

#TODO:

### <a id="todo"></a>TODO

* Implement options for JSONP
* Implement "add fields" option


