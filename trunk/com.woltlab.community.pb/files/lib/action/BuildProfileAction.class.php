<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');
require_once(PB_DIR.'lib/system/package/PackageBuilder.class.php');
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');
require_once(PB_DIR.'lib/system/package/PackageReader.class.php');
require_once(PB_DIR.'lib/system/package/StandalonePackageBuilder.class.php');

class BuildProfileAction extends AbstractSecureAction {
	/**
	 * list of packages
	 * 
	 * @var	array
	 */
	public $packages = array();
	
	/**
	 * current package hash
	 * 
	 * @var	string
	 */
	public $packageHash = '';
	
	/**
	 * current package name
	 * 
	 * @var	string
	 */
	public $packageName = '';
	
	/**
	 * Current build profile hash
	 * 
	 * @var	string
	 */
	public $profile = '';
	
	/**
	 * WCFSetup resource
	 * 
	 * @var	string
	 */
	public $resource = '';
	
	/**
	 * @see	Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['profile'])) $this->profile = StringUtil::trim($_POST['profile']);
		
		// read build profile
		$sql = "SELECT	packages, packageHash, packageName, resource
			FROM	pb".PB_N."_build_profile
			WHERE	profileHash = '".escapeString($this->profile)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		
		// validate profile
		if (!$row) {
			throw new SystemException("Build profile '".$this->profile."' is not valid.");
		}
		
		$this->packages = unserialize($row['packages']);
		$this->packageHash = $row['packageHash'];
		$this->packageName = $row['packageName'];
		$this->resource = $row['resource'];
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// get source id by package hash and name
		$sql = "SELECT	sourceID, directory
			FROM	pb".PB_N."_source_package
			WHERE	hash = '".escapeString($this->packageHash)."'
				AND packageName = '".escapeString($this->packageName)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (!$row) throw new SystemException("Unable to find source for package '".$this->packageName."' identified by '".$this->packageHash."'");
		
		$source = new Source($row['sourceID']);
		if (!$source->hasAccess()) throw new PermissionDeniedException();
		
		// set package resources
		PackageHelper::registerPackageSelection($this->packages);
		
		// read package
		$pr = new PackageReader($source, $row['directory']);
		
		try {
			// build package
			$pb = new PackageBuilder($source, $pr, $row['directory'], 'pn');
			
			// build wcf setup
			$spb = new StandalonePackageBuilder($source, $this->resource);
			$spb->createWcfSetup(array($pb->getArchiveLocation()));
		}
		// do cleanup
		catch (SystemException $e) {
			PackageHelper::clearTemporaryFiles();
			throw $e;
		}

		// clear previously created archives
		PackageHelper::clearTemporaryFiles();
		
		// call executed event
		$this->executed();
		
		HeaderUtil::redirect('index.php?page=SourceView&sourceID=' . $source->sourceID . SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>