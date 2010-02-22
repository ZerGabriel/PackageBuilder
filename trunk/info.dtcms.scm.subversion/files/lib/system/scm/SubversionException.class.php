<?php
/**
 * Removes critical data from subversion exceptions
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	info.dtcms.scm.subversion
 * @subpackage	system.scm
 * @category 	PackageBuilder
 */
class SubversionException extends SystemException {
	/**
	 * Removes critical data from stack trace.
	 *
	 * @see Exception::getTraceAsString()
	 */
	public function __getTraceAsString() {
		return preg_replace('/Subversion\:\:(.*)\((.*)/', 'Subversion::$1(...)', $this->getTraceAsString());
	}
}
?>