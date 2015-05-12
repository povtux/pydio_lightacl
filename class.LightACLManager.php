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
    /**
     * @param string $action
     * @param array $httpVars
     * @param array $fileVars
     */
    public function applyChangePerms($actionName, $httpVars, $fileVars)
    {
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

	$unikey = hash('sha256', $httpVars['usr'] . $urlBase . $currentFile);
	try {
		dibi::query('INSERT INTO [light_acl]', Array(
			'unikey' => $unikey,
                	'login' => $httpVars['usr'],
	                'path' => $urlBase.$currentFile,
        	        'accessright' => $httpVars['perm']
	            ));
	} catch(Exception $e) {
		dibi::query("UPDATE light_acl SET accessright=" . $httpVars['perm'] . " WHERE unikey='" . $unikey . "'");
	}

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
		$user = AuthService::getLoggedUser()->getId();
		// découpage du path pour les différentes combinaisons possibles
		$search = array(hash('sha256', $user . $node->getUrl()));
		$nd = $node;
		do {
			$nd = $nd->getParent();
			if($nd != null) $search[] = hash('sha256', $user . $nd->getUrl());
		} while($nd != null);

		$query = "SELECT accessright FROM light_acl WHERE unikey IN('". implode("','", $search) ."') ORDER BY CHAR_LENGTH(path) DESC";

		$result_rights = dibi::query($query);
		$testRight = $result_rights->fetchSingle();

		if($testRight === 0 || $testRight === 1) return $testRight;
		else return 2;
	}
}
