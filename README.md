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

{
    "require": {
        ...
        "lxlxw/api-doc": "dev-master"
    }
}

2/更新vendor包

$ php composer.phar update


### <a id="demo"></a>使用

```php
<?php

namespace Some\Namespace;

class User
{
    /**
     * @ApiDescription(section="User", description="Get information about user")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     * @ApiParams(name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *  'transaction_id':'int',
     *  'transaction_status':'string'
     * }")
     */
    public function get()
    {

    }

    /**
     * @ApiDescription(section="User", description="Create's a new user")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/user/create")
     * @ApiParams(name="username", type="string", nullable=false, description="Username")
     * @ApiParams(name="email", type="string", nullable=false, description="Email")
     * @ApiParams(name="password", type="string", nullable=false, description="Password")
     * @ApiParams(name="age", type="integer", nullable=true, description="Age")
     */
    public function create()
    {

    }
}
```

Create an apidoc.php file in your project root folder as follow:


```php
# apidoc.php
<?php

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

$classes = array(
    'Some\Namespace\User',
    'Some\Namespace\OtherClass',
);

$output_dir  = __DIR__.'/apidocs';
$output_file = 'api.html'; // defaults to index.html

try {
    $builder = new Builder($classes, $output_dir, 'Api Title', $output_file);
    $builder->generate();
} catch (Exception $e) {
    echo 'There was an error generating the documentation: ', $e->getMessage();
}

```

Then, execute it via CLI

```php
$ php apidoc.php
```

### <a id="methods"></a>Available Methods

Here is the list of methods available so far :

* @ApiDescription(section="...", description="...")
* @ApiMethod(type="(get|post|put|delete|patch")
* @ApiRoute(name="...")
* @ApiParams(name="...", type="...", nullable=..., description="...", [sample=".."])
* @ApiHeaders(name="...", type="...", nullable=..., description="...")
* @ApiReturnHeaders(sample="...")
* @ApiReturn(type="...", sample="...")
* @ApiBody(sample="...")

### <a id="preview"></a>Preview

You can see a dummy generated documentation on http://calinrada.github.io/php-apidoc/

### <a id="tips"></a>Tips

To generate complex object sample input, use the ApiParam "type=(object|array(object)|array)":

```php
* @ApiParams(name="data", type="object", sample="{'user_id':'int','profile':{'email':'string','age':'integer'}}")
```

### <a id="knownissues"></a>Known issues

I don't know any, but please tell me if you find something. PS: I have tested it only in Chrome !

### <a id="todo"></a>TODO

* Implement options for JSONP
* Implement "add fields" option


