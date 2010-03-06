<?php

/**
 * Provides functions for handling directories
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	util
 * @category 	PackageBuilder
 */
class DirectoryUtil {
	
	/**
	 * holds the RecursiveDirectoryIterator object
	 *
	 * @var object
	 */
	protected $obj = NULL;
	
	/**
	 * all files with fullpath
	 *
	 * @var array
	 */
	protected $files = array();
	
	/**
	 * all files with filename as key and DirectoryIterator object as value
	 *
	 * @var array
	 */
	protected $filesObj = array();
	
	/**
	 * filesize of the diretory
	 *
	 * @var int
	 */
	protected $size = 0;
	
	/**
	 * path to directory
	 *
	 * @var string
	 */
	protected $directory = '';
	
	/**
	 * all instances
	 *
	 * @var array
	 */
	protected static $instances = array();
	
	protected function __construct($directory) {
		$this->directory = $directory;
		$this->obj = new RecursiveDirectoryIterator($directory);
		// fill the files
		$this->scanFiles();
	}
	/**
	 * returns a (new) instance of DirectoryUtil
	 *
	 * @param 	string		$directory 	directorypath
	 * @return 	object				DirectoryUtil object
	 */
	public function getInstance($directory) {
		$directory = realpath(FileUtil::unifyDirSeperator($directory));
		if($directory === false) throw new SystemException('Invalid directory');
		if(array_key_exists($directory, self::$instances)) return self::$instances[$directory];
		self::$instances[$directory] = new self($directory);
		return self::$instances[$directory];
	}
	
	/**
	 * returns a (sorted) list of files
	 *
	 * @param 	string 	$order	the order the files should be sorted
	 * @return 	array		sorted filelist
	 */
	public function getFiles($order = 'ASC') {
		$tmp = $this->files;
		if($order == 'ASC') asort($tmp);
		elseif($order == 'DESC') arsort($tmp);
		return $tmp;
	}
	
	/**
	 * returns a (sorted) list of files, with DirectoryIterator object as value
	 *
	 * @param 	string 	$order	the order the files should be sorted
	 * @return 	array		sorted filelist
	 */
	public function getFilesObj($order = 'ASC') {
		$this->scanFilesObj();
		$tmp = $this->filesObj;
		if($order == 'ASC') ksort($tmp);
		elseif($order == 'DESC') krsort($tmp);
		return $tmp;
	}
	
	/**
	 * fills the list of availible files
	 *
	 * @return void
	 */
	protected function scanFiles() {
		if(!empty($this->files)) return;
		foreach (new RecursiveIteratorIterator($this->obj) as $filename=>$obj) {
			$this->files[] = $filename;
		}
	}
	
	/**
	 * fills the list of availible files, with DirectoryIterator object as value
	 *
	 * @return void
	 */
	protected function scanFilesObj() {
		if(!empty($this->filesObj)) return;
		foreach (new RecursiveIteratorIterator($this->obj) as $filename=>$obj) {
			$this->filesObj[$filename] = $obj;
		}
	}
	
	/**
	 * recursiv remove of directory
	 *
	 * @return void
	 */
	public function removeComplete() {
		$files = $this->getFilesObj('DESC');
		foreach($files as $filename=>$obj) {
			if(!is_writable($obj->getPath())) throw new SystemException('Could not remove dir: "'.$obj->getPath().'" is not writable');
			if($obj->isDir()) rmdir($filename);
			elseif($obj->isFile()) unlink($filename);
		}
		rmdir($this->directory);
		unset(self::$instances[$this->directory]);
	}
	
	/**
	 * removes all files that match the pattern
	 *
	 * @param  string	$pattern	regex pattern
	 * @return void
	 */
	public function removePattern($pattern) {
		$files = $this->getFiles('DESC');
		foreach($files as $filename=>$obj) {
			if(!preg_match($pattern, $filename)) continue;
			if(!is_writable($obj->getPath())) throw new SystemException('Could not remove dir: "'.$obj->getPath().'" is not writable');
			if($obj->isDir()) rmdir($filename);
			elseif($obj->isFile()) unlink($filename);
		}
	}
	
	/**
	 * calculates the size of the directory
	 *
	 * @return int	directorysize
	 */
	public function getSize() {
		if($this->size != 0) return $this->size;
		$this->scanFilesObj();
		foreach($this->fileObj as $filename=>$obj) {
			$this->size += $obj->getSize();
		}
		return $this->size;
	}
}
?>