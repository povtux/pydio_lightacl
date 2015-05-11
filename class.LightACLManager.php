<?php
/*
 * Copyright 2015 Pierre-Oliiver VERSCHOORE - <po.verschoore@gmail.com>
 *
 * Licence, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <https://github.com/povtux/pydio_lightacl>.
 */

defined('AJXP_EXEC') or die('Access not allowed');

/**
 * Locks a folder manually
 * @package AjaXplorer_Plugins
 * @subpackage Meta
 */
class LightACLManager extends AJXP_AbstractMetaSource
{
//    const METADATA_LOCK_NAMESPACE = "lightacl";
    /**
    * @var MetaStoreProvider
    */
/*    protected $metaStore;

    public function initMeta($accessDriver)
    {
        parent::initMeta($accessDriver);
        $store = AJXP_PluginsService::getInstance()->getUniqueActivePluginForType("metastore");
        if ($store === false) {
            throw new Exception("The 'meta.lightacl' plugin requires at least one active 'metastore' plugin");
        }
        $this->metaStore = $store;
        $this->metaStore->initMeta($accessDriver);
    }

    /**
     * @param string $action
     * @param array $httpVars
     * @param array $fileVars
     */
    public function applyChangePerms($actionName, $httpVars, $fileVars)
    {
$f=fopen('/tmp/lightacl.log', 'a');
fwrite($f, print_r($actionName, true));
fwrite($f, print_r($httpVars, true));
fwrite($f, print_r($fileVars, true));
        if(!isSet($this->actions[$actionName])) return;
        if (is_a($this->accessDriver, "demoAccessDriver")) {
            throw new Exception("Write actions are disabled in demo mode!");
        }
        $repo = $this->accessDriver->repository;
        $user = AuthService::getLoggedUser();
        if (!AuthService::usersEnabled() && $user!=null && !$user->canWrite($repo->getId())) {
            throw new Exception("You have no right on this action.");
        }
        $selection = new UserSelection();
        $selection->initFromHttpVars($httpVars);
        $currentFile = $selection->getUniqueFile();
        $wrapperData = $this->accessDriver->detectStreamWrapper(false);
        $urlBase = $wrapperData["protocol"]."://".$this->accessDriver->repository->getId();


fwrite($f, print_r($currentFile, true));
fclose($f);
	dibi::query('INSERT INTO [light_acl]', Array(
		'unikey' => md5($user . $urlBase . $currentFile),
                'login' => 'admin',
                'path' => $urlBase.$currentFile,
                'accessright' => 0
            ));

        AJXP_XMLWriter::header();
        AJXP_XMLWriter::reloadDataNode();
        AJXP_XMLWriter::close();
    }

    /**
     * @param AJXP_Node $node
     */
    public function beforeChange($node)
    {
	$right = $this->getAccessRights($node);
	if($right < 2)
		throw new Exception("No permissions to write here");
    }

    /**
     * @param AJXP_Node $node
     */
    public function read($node)
    {
	$right = $this->getAccessRights($node);
	if($right < 1)
		throw new Exception("No permissions to access here");
    }

    /**
     * @param AJXP_Node $node
     */
    public function beforeChangePath($node)
    {
	$this->read($node);
    }

	/**
	* @param AJXP_Node $node
	* @return int
	* valeurs possibles:
	* 0: no access
	* 1: read only
	* 2: no alteration
	*/
	private function getAccessRights($node)
	{
		// découpage du path pour les différentes combinaisons possibles
		$search = array($node->getUrl());
		$nd = $node;
		do {
			$nd = $nd->getParent();
			if($nd != null) $search[] = $nd->getUrl();
		} while($nd != null);

		$query = "SELECT accessright FROM light_acl WHERE login = '". AuthService::getLoggedUser()->getId() ."' AND path IN('". implode("','", $search) ."') ORDER BY CHAR_LENGTH(path) DESC";

		$result_rights = dibi::query($query);
		$testRight = $result_rights->fetchSingle();

		if($testRight === 0 || $testRight === 1) return $testRight;
		else return 2;
	}
}
