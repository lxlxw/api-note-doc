<?php
/**
 * @param allowed_file   [Allowed file suffix,for example:.php,.js]
 * @param build_path     [Generate the target directory for the document.]
 * @param vender_path    [Directory to generate documents.]
 * @param template       [template,for example:default,html,wiki..]
 * @param template_ext   [Template suffix,for example:.md,.html]
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
    'siteurl'       => 'ApiUrl',
    'params'        => 'ApiParams',
    'return'        => 'ApiReturn',
    'format'        => 'ApiFormat',
    'method'        => 'ApiMethod',
    'notice'        => 'ApiNotice',
    'example'       => 'ApiExample',
    'success'       => 'ApiSuccess',
];
?>
