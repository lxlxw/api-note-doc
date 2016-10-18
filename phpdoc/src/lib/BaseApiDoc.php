<?php  
namespace lib\apibase;

class ApiMdBase{

	private $annotationCache;//注释缓存
	private $dirs = [];//文件目录地址
	private $data = [];//最终的注释数据
	private $output = [];
	private $rule = [];
	private $config = [];
	private $template_path = '/template';

	function __construct() {
	    require_once CONF_PATH.'common.php';
	    $this->rule = $config['rule'];
	    $this->config = $config['settings'];
	    $this->template_path = TEMP_PATH;
	}

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
	
	function buildDoc(){
	    //获取要操作的目录
	    $this->listdirs($this->config['build_path']);
	    $this->getAllAnnotations();
	    $this->generateTemplate();
	}
	
	function listdirs($path) {
	    $this->dirs[] = "{$path}/*";
	    //暂不循环目录
        //$dirs = glob($filepath, GLOB_ONLYDIR);
        // 	if(count($dirs) > 0){
        // 		foreach ($dirs as $dir) $this->listdirs($dir);
        // 	}
	    return $this->dirs;
	}
	
	//处理数据
	function generateTemplate(){
		
		$this->output = '';
		
		foreach ($this->data as $group => $class) {
			foreach ($class as $className => $object) {
// 				$content[$group]['group'] = strtr($templates['content'], array(
// 					'{{group}}'		 => $object['comment']['comment']['group'][0]['name'],
// 					'{{group_desc}}' => $object['comment']['comment']['group'][0]['description'],
// 				)) . "\n";
				$this->class = $className;
				foreach ($object['methods'] as $method => $annotion) {
					$this->method = $method;
					//$content[$group]['item'][] = strtr($templates['item'], array('{{ext}}' => $this->config['template_ext'], '{{class}}' => $className, '{{method}}' => $method, '{{description}}' => $annotion['comment'][$this->config['rule']['description']][0]['description'])) . "\n";
					
					//获取解析之后的数据 经过处理
					$sub_data = $this->generateItemPage($annotion);
					//判断是否有可以保存的目录
					if(!is_dir($this->config['vender_path'])) mkdir($this->config['vender_path']);
					$sub_file = $this->config['vender_path'] . "{$className}/{$method}{$this->config['template_ext']}";
					if (!is_dir($this->config['vender_path'] . $className)) mkdir($this->config['vender_path'] . $className);
					//保存数据
					$this->saveTemplate($sub_file, $sub_data);
				}
			}
		}
		return $this->output;
	}

	function generateContent($data){
		$this->output = '';
		foreach($data as $group => $items){
			$this->output .= $items['group'];
			foreach($items['item'] as $item){
				$this->output .= $item;
			}
		}
		return $this->output;
	}

	function getOutputParams($template, $params){
		$format_data = '';
		foreach($params as $param){
			$data = array(
				'{{params}}'      => $param['name'],
				'{{is_selected}}' => isset($param['is_selected']) ? 'true' : 'false',
				'{{field_type}}'  => $param['type'],
				'{{field_desc}}'  => $param['description'],
			);
			$format_data .= strtr($template, $data) . "\n";
		}
		return $format_data;
	}

	function generateItemPage($annotion){
	    
		$templates = $this->getSubPageTemplate();
		
		$comment = $annotion['comment'];
		$params = $comment[$this->rule['params']];
		$return = $comment[$this->rule['return']];
		$description = $comment[$this->rule['description']][0];
		$method = $comment[$this->rule['method']][0];
		$notice = isset($comment[$this->rule['notice']]) ? $comment[$this->rule['notice']][0] : '';
		$example_str = isset($comment[$this->rule['example']]) ? $comment[$this->rule['example']][0]['value'] : '';
		$success_str = isset($comment[$this->rule['success']]) ? $comment[$this->rule['success']][0]['value'] : '';
		$subpage = strtr($templates['subpage'], [
			'{{site_url}}' => "{$this->class}/{$this->method}",
			'{{description}}' => $description,
			'{{request_method}}' => $method,
			'{{notice}}' => $notice,
			'{{request_format}}' => $this->getOutputParams($templates['request_format'], $params),
			'{{return_format}}' => $this->getOutputParams($templates['return_format'], $return),
			'{{request_example}}' => $this->json_format_item($example_str),
			'{{return_data}}' => $this->json_format_item($success_str),
		]);
		return $subpage;
	}
	
    //获取模版
	function getSubPageTemplate(){
		$ext = $this->config['template_ext'];
		return [
			'subpage' 		 => file_get_contents($this->template_path. $this->config['template']. '/subpage/subpage' . $ext),
			'request_format' => file_get_contents($this->template_path. $this->config['template']. '/subpage/request_format' . $ext),
			'return_format'  => file_get_contents($this->template_path. $this->config['template']. '/subpage/return_format'. $ext),
		];
	}

	function saveTemplate($file, $data){
		$handle=fopen($file, "w+");
		if(is_array($data)){
			foreach($data as $item){
				fwrite($handle, $item);
			}
		}else{
			fwrite($handle, $data);
		}
		fclose($handle);
	}

	//获取所有的注释数据
	function getAllAnnotations(){
		foreach($this->dirs as $dir){
			$this->getAnnotations($dir);
		}
		$this->sortDoc();
		//获取最后要的注释数据
		return $this->data;
	}
	function sortDoc(){
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

	function getAnnotations($path){
		foreach(glob($path.$this->config['allowed_file'], GLOB_BRACE) as $filename){
			require_once $filename;//加载php文件
			//pathinfo()函数返回的是一个包含了文件信息的数组，数组中有四个元素，分别是dirname、basename、extension、filename。
			$file = pathinfo($filename);
			$this->getAnnoation($file['filename']);
		}
		return $this->annotationCache;
	}
    //获取总的注释
	function getAnnoation($className){
	    //如果不存在这个类
		if (!isset($this->annotationCache[$className])) {
			$class = new \ReflectionClass($className);
			$this->annotationCache[$className] = $this->getClassAnnotation($class);
			$this->getMethodAnnotations($class);
		}
		return $this->annotationCache;
	}

	function getMethodAnnotations($className)
	{
	    //获取该类的所有方法 然后foreach
		foreach ($className->getMethods() as $object) {
		    //如果遇到方法名为get_instance的且为构造函数的则不记录
			if($object->name == 'get_instance' || $object->name == $className->getConstructor()->name) continue;
			//获取方法
			$method = new \ReflectionMethod($object->class, $object->name);
			//获取方法里的注释
			$this->annotationCache[strtolower($object->class)]['methods'][$object->name] = $this->getMethodAnnotation($method);
		}
		return $this->annotationCache;
	}
    //获取类的注释
	function getClassAnnotation($class){
		return array('class' => array(
			'comment' => self::parseAnnotations($class->getDocComment()),//这边就是真正的注释的值
			'parentClass' => $class->getParentClass()->name,
			'fileName'	=> $class->getFileName(),
		));
	}
    //获取类里的方法的注释
	function getMethodAnnotation($method){
		return array(
			'comment' => self::parseAnnotations($method->getDocComment()),//这边就是真正的注释的值
			'fileName'	=> $method->getFileName(),
			'method_attribute' => \Reflection::getModifierNames($method->getModifiers()),
		);
	}

	/**
     * Parse annotations
     *
     * @param  string $docblock
     * @return array  parsed annotations params
     */
	private static function parseAnnotations($docblock)
	{
		$annotations = array();
		// Strip away the docblock header and footer to ease parsing of one line annotations
		$docblock = substr($docblock, 3, -2);
        //http://php.net/manual/en/reflectionclass.getdoccomment.php
		if (preg_match_all('/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/m', $docblock, $matches)) {
			$numMatches = count($matches[0]);
			for ($i = 0; $i < $numMatches; ++$i) {
				if (isset($matches['args'][$i])) {
					$argsParts = trim($matches['args'][$i]);
					$name      = $matches['name'][$i];
					$value     = self::parseArgs($argsParts);
				} else {
					$value = array();
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
	function json_format_item($str){
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