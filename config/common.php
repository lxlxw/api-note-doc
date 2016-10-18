<?php
/**
 * @param allowed_file [允许的文件后缀 如:.php,.js]
 * @param build_path [生成文档的目录]
 * @param vender_path [生成文档后存放的目录]
 * @param template [所用模版]
 * @param template_ext[模板后缀名]
 */
$config['settings'] = [
 	'allowed_file'    => '.php',
 	'build_path'      => '/',
    'vender_path'     => '/vender/',
 	'template_ext'    => '.md',
 	'template'	      => 'default',
];

/**
 * @param description 
 * @param params 
 * @param return 
 * @param format
 * @param method
 * @param notice
 * @param example
 * @param success
 */
$config['rule'] = [
    'description'   => 'ApiDescription',
    'params'        => 'ApiParams',
    'return'        => 'ApiReturn',
    'format'        => 'ApiFormat',
    'method'        => 'ApiMethod',
    'notice'        => 'ApiNotice',
    'example'       => 'ApiExample',
    'success'       => 'ApiSuccess',
];
?>
