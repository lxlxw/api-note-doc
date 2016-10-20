<?php  
/**
 * API document generation tool
 *
 * Copyright (c) 2016 lxw <http://www.lxlxw.me>
 * 
 * @link https://github.com/lxlxw/api-note-doc
 * @version 1.0
 */
namespace lib\base;

class ApiDocBase{
    
    /**
     * Annotation cache
     *
     * @var array
     */
	private $annotationCache = [];
	
	/**
	 * The file dir
	 *
	 * @var array
	 */
	private $dirs = [];
	
	/**
	 * The annotation data
	 *
	 * @var array
	 */
	private $data = [];
	
	/**
	 * Final output data
	 *
	 * @var array
	 */
	private $output = [];
	
	/**
	 * Template rule
	 *
	 * @var array
	 */
	private $rule = [];
	
	/**
	 * Configuration rules
	 *
	 * @var array
	 */
	private $config = [];
	
	/**
	 * Template storage path
	 *
	 * @var string
	 */
	private $template_path = '/template';
	
	/**
	 * Create a new apidoc instance and Initialization parameters.
	 */
	function __construct()
	{
	    require_once CONF_PATH.'common.php';
	    $this->rule = $config['rule'];
	    $this->config = $config['settings'];
	    $this->template_path = TEMP_PATH;
	}
	
	/**
	 * The transformation configuration parameters.
	 *
	 * @param array   $data
	 * @return obj    $this
	 */
	public function setconf($data = [])
	{
	    if(empty($data)){ return $this; }
        foreach ($data as $configName => $configValue){
            if(isset($this->config[$configName])){
                $this->config[$configName] = $configValue;
            }
        }
	    return $this;
	}
	
	/**
	 * Generate API document
	 * 
	 * @return boolean 
	 */
	public function buildDoc()
	{
	    $this->listdirs($this->config['build_path']);
	    $this->getAllAnnotations();
	    return $this->generateTemplate();
	}
	
    /**
	 * Process data and save the final data.
	 *
	 * @return boolean 
	 */
	protected function generateTemplate()
	{
		$this->output = '';
		foreach ($this->data as $group => $class) {
			foreach ($class as $className => $object) {
				$this->class = $className;
				foreach ($object['methods'] as $method => $annotion) {
					$this->method = $method;
					$sub_data = $this->generateItemPage($annotion);
					if(!is_dir($this->config['vender_path'])) mkdir($this->config['vender_path']);
					$sub_file = $this->config['vender_path'] . "{$className}/{$method}{$this->config['template_ext']}";
					if (!is_dir($this->config['vender_path'] . $className)) mkdir($this->config['vender_path'] . $className);
					$this->saveTemplate($sub_file, $sub_data);
				}
			}
		}
		return true;
	}

	/**
	 * Based on template analysis data.
	 *
	 * @param string  $template
	 * @param array   $params
	 * 
	 * @return string $format_data
	 */
	protected function getOutputParams($template, $params)
	{
		$format_data = '';
		foreach($params as $param){
			$data = [
				'{{params}}'      => $param['name'],
				'{{is_selected}}' => isset($param['is_selected']) ? 'true' : 'false',
				'{{field_type}}'  => $param['type'],
				'{{field_desc}}'  => $param['description'],
			];
			$format_data .= strtr($template, $data) . "\n";
		}
		return $format_data;
	}
	
	/**
	 * Based on template analysis data.
	 *
	 * @param array  $annotion
	 *
	 * @return string $subpage
	 */
	protected function generateItemPage($annotion)
	{
		$templates = $this->getSubPageTemplate();
		$comment = $annotion['comment'];
		$params = $comment[$this->rule['params']];
		$return = $comment[$this->rule['return']];
		$siteurl = isset($comment[$this->rule['siteurl']][0]) ? $comment[$this->rule['siteurl']][0] : "{$this->class}/{$this->method}";
		$description = $comment[$this->rule['description']][0];
		$method = $comment[$this->rule['method']][0];
		$notice = isset($comment[$this->rule['notice']]) ? $comment[$this->rule['notice']][0] : '';
		$example_str = isset($comment[$this->rule['example']]) ? $comment[$this->rule['example']][0]['value'] : '';
		$success_str = isset($comment[$this->rule['success']]) ? $comment[$this->rule['success']][0]['value'] : '';
		$subpage = strtr($templates['subpage'], [
			'{{site_url}}' => $siteurl,
			'{{description}}' => $description,
			'{{request_method}}' => $method,
			'{{notice}}' => $notice,
			'{{request_format}}' => $this->getOutputParams($templates['request_format'], $params),
			'{{return_format}}' => $this->getOutputParams($templates['return_format'], $return),
			'{{request_example}}' => $this->jsonFormatItem($example_str),
			'{{return_data}}' => $this->jsonFormatItem($success_str),
		]);
		return $subpage;
	}
	
	/**
	 * Get page template.
	 *
	 * @return array $template
	 */
	protected function getSubPageTemplate()
	{
		$ext = $this->config['template_ext'];
		return [
			'subpage' 		 => file_get_contents($this->template_path. $this->config['template']. '/subpage/subpage' . $ext),
			'request_format' => file_get_contents($this->template_path. $this->config['template']. '/subpage/request_format' . $ext),
			'return_format'  => file_get_contents($this->template_path. $this->config['template']. '/subpage/return_format'. $ext),
		];
	}
	
	/**
	 * Save template.
	 *
	 * @param string $file
	 * @param array | string $data
	 * 
	 * @return void
	 */
	protected function saveTemplate($file, $data)
	{
		$handle = fopen($file, "w+");
		if(is_array($data)){
			foreach($data as $item){
				fwrite($handle, $item);
			}
		}else{
			fwrite($handle, $data);
		}
		fclose($handle);
	}
	
	/**
	 * Get target directory.
	 *
	 * @param string   $path
	 * @return array   $this->dirs
	 */
	protected function listdirs($path)
	{
	    $this->dirs[] = "{$path}/*";
	    //TODO:
	    //$dirs = glob($filepath, GLOB_ONLYDIR);
	    // 	if(count($dirs) > 0){
	    // 		foreach ($dirs as $dir) $this->listdirs($dir);
	    // 	}
	    return $this->dirs;
	}
	
	/**
	 * Get all the annotations data.
	 *
	 * @return array $this->data
	 */
	protected function getAllAnnotations()
	{
		foreach($this->dirs as $dir){
			$this->getAnnotations($dir);
		}
		return $this->sortDoc();
	}
	
	/**
	 * Generate doc data
	 *
	 * @return array $this->data
	 */
	protected function sortDoc()
	{
		foreach($this->annotationCache as $class => $annotation){
			if(isset($annotation['class']['comment']['group'])){
				$this->data[$annotation['class']['comment']['group'][0]['name']][$class] = array(
					'comment' => $annotation['class'],
					'methods' => $annotation['methods'],
				);
			}
		}
		return $this->data;
	}

	/**
	 * Get annotations.
	 * 
	 * @param string  $path
	 * 
	 * @return array $this->data
	 */
	protected function getAnnotations($path)
	{
		foreach(glob($path.$this->config['allowed_file'], GLOB_BRACE) as $filename){
			require_once $filename;
			$file = pathinfo($filename);
			$this->getAnnoation($file['filename']);
		}
		return $this->annotationCache;
	}
	
	/**
	 * Get annotation.
	 *
	 * @param string  $className
	 * 
	 * @return array $this->annotationCache
	 */
	protected function getAnnoation($className)
	{
		if (!isset($this->annotationCache[$className])) {
			$class = new \ReflectionClass($className);
			$this->annotationCache[$className] = $this->getClassAnnotation($class);
			$this->getMethodAnnotations($class);
		}
		return $this->annotationCache;
	}

	/**
	 * Get method annotations.
	 *
	 * @param string  $className
	 * 
	 * @return array $this->annotationCache
	 */
	protected function getMethodAnnotations($className)
	{
		foreach ($className->getMethods() as $object) {
			if($object->name == 'get_instance' || $object->name == $className->getConstructor()->name) continue;
			$method = new \ReflectionMethod($object->class, $object->name);
			$this->annotationCache[strtolower($object->class)]['methods'][$object->name] = $this->getMethodAnnotation($method);
		}
		return $this->annotationCache;
	}
	
	/**
	 * Get method annotation.
	 *
	 * @param object  $method
	 * 
	 * @return array $this->annotationCache
	 */
	protected function getMethodAnnotation($method)
	{
	    return [
	               'comment' => self::parseAnnotations($method->getDocComment()),
	               'fileName'	=> $method->getFileName(),
	               'method_attribute' => \Reflection::getModifierNames($method->getModifiers()),
	           ];
	}
	
    /**
	 * Get class annotation.
	 *
	 * @param object  $class
	 * 
	 * @return array $this->annotationCache
	 */
	protected function getClassAnnotation($class)
	{
		return ['class' => 
		          [
			         'comment' => self::parseAnnotations($class->getDocComment()),
			         'parentClass' => $class->getParentClass()->name,
			         'fileName'	=> $class->getFileName(),
		          ]
		      ];
	}

	/**
     * Parse annotations
     *
     * @param  string $docblock
     * 
     * @return array  parsed annotations params
     */
	private static function parseAnnotations($docblock)
	{
		$annotations = [];
		// Strip away the docblock header and footer to ease parsing of one line annotations
		$docblock = substr($docblock, 3, -2);
		if (preg_match_all('/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/m', $docblock, $matches)) {
			$numMatches = count($matches[0]);
			for ($i = 0; $i < $numMatches; ++$i) {
				if (isset($matches['args'][$i])) {
					$argsParts = trim($matches['args'][$i]);
					$name      = $matches['name'][$i];
					$value     = self::parseArgs($argsParts);
				} else {
					$value = [];
				}
				$annotations[$name][] = $value;
			}
		}
		return $annotations;
	}

	/**
	 * Parse individual annotation arguments
	 *
	 * @param  string $content arguments string
	 * 
	 * @return array  annotated arguments
	 */
	private static function parseArgs($content)
	{
		$data  = array();
		$len   = strlen($content);
		$i     = 0;
		$var   = '';
		$val   = '';
		$level = 1;
		$prevDelimiter = '';
		$nextDelimiter = '';
		$nextToken     = '';
		$composing     = false;
		$type          = 'plain';
		$delimiter     = null;
		$quoted        = false;
		$tokens        = array('"', '"', '{', '}', ',', '=');

		while ($i <= $len) {
			$c = substr($content, $i++, 1);

			//if ($c === '\'' || $c === '"') {
		    if ($c === '"') {
				$delimiter = $c;
				//open delimiter
				if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) {
					$prevDelimiter = $nextDelimiter = $delimiter;
					$val           = '';
					$composing     = true;
					$quoted        = true;
				} else {
					// close delimiter
					if ($c !== $nextDelimiter) {
						throw new Exception(sprintf(
							"Parse Error: enclosing error -> expected: [%s], given: [%s]",
							$nextDelimiter, $c
						));
					}

					// validating sintax
					if ($i < $len) {
						if (',' !== substr($content, $i, 1)) {
							throw new Exception(sprintf(
								"Parse Error: missing comma separator near: ...%s<--",
								substr($content, ($i-10), $i)
							));
						}
					}

					$prevDelimiter = $nextDelimiter = '';
					$composing     = false;
					$delimiter     = null;
				}
			} elseif (!$composing && in_array($c, $tokens)) {
				switch ($c) {
				    case '=':
						$prevDelimiter = $nextDelimiter = '';
						$level     = 2;
						$composing = false;
						$type      = 'assoc';
						$quoted = false;
						break;
					case ',':
						$level = 3;

						// If composing flag is true yet,
						// it means that the string was not enclosed, so it is parsing error.
						if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
							throw new Exception(sprintf(
								"Parse Error: enclosing error -> expected: [%s], given: [%s]",
								$nextDelimiter, $c
							));
						}

						$prevDelimiter = $nextDelimiter = '';
						break;
				    case '{':
						$subc = '';
						$subComposing = true;

						while ($i <= $len) {
							$c = substr($content, $i++, 1);

							if (isset($delimiter) && $c === $delimiter) {
								throw new Exception(sprintf(
									"Parse Error: Composite variable is not enclosed correctly."
								));
							}

							if ($c === '}') {
								$subComposing = false;
								break;
							}
							$subc .= $c;
						}

						// if the string is composing yet means that the structure of var. never was enclosed with '}'
						if ($subComposing) {
						    throw new Exception(sprintf(
						        "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
						        $subc
						    ));
						}

						$val = self::parseArgs($subc);
						break;
				}
			} else {
				if ($level == 1) {
					$var .= $c;
				} elseif ($level == 2) {
					$val .= $c;
				}
			}

		    if ($level === 3 || $i === $len) {
				if ($type == 'plain' && $i === $len) {
					$data = self::castValue($var);
				} else {
					$data[trim($var)] = self::castValue($val, !$quoted);
				}

				$level = 1;
				$var   = $val = '';
				$composing = false;
				$quoted = false;
			}
		}

		return $data;
	}

	private static function castValue($val, $trim = false)
	{
		if (is_array($val)) {
			foreach ($val as $key => $value) {
				$val[$key] = self::castValue($value);
			}
		} elseif (is_string($val)) {
			if ($trim) {
				$val = trim($val);
			}

			$tmp = strtolower($val);

			if ($tmp === 'false' || $tmp === 'true') {
				$val = $tmp === 'true';
			} elseif (is_numeric($val)) {
				return $val + 0;
			}

			unset($tmp);
		}

		return $val;
	}
	
	private function jsonFormatItem($str)
	{
	    if(empty($str)) return false;
	    $success = '';
	    $success_obj = json_decode(str_replace("'", '"', $str), true);
	    $i = 0;
	    foreach($success_obj as $key => $item){
	        if($i >= count($success_obj) -1){
	            $success .= "{$key} : {$item},";
	        }else{
	            $success .= "{$key} : {$item}," . "\n\t";
	        }
	        $i++;
	    }
	    return $success;
	}


}
?>