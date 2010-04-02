<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Default start page, displays all relevant informations.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	page
 * @category 	PackageBuilder
 */
class IndexPage extends AbstractPage {
	public $templateName = 'index';
	public $neededPermissions = 'user.source.general.canViewSources';

	// data
	public $sources = array();

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		$sql = "SELECT *
			FROM	pb".PB_N."_sources
			ORDER BY position ASC";
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
			if(WCF::getUser()->getPermission('user.source.dynamic.canUseSource'.$row['sourceID']) == 1) {
				$className = ucfirst(Source::validateSCM($row['scm']));
				require_once(WCF_DIR . 'lib/system/scm/'.$className.'.class.php');
				$row['availableRevision'] = StringUtil::trim(call_user_func(array($className, 'getHeadRevision'), $row['url'], array('username' => $row['username'], 'password' => $row['password'])));
				$this->sources[] = $row;
			}
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'allowSpidersToIndexThisPage' => false,
				'sources' => $this->sources
		));
	}
}
?>