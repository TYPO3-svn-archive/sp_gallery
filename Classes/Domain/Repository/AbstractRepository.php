<?php
namespace Speedprogs\SpGallery\Domain\Repository;

/*********************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 *           Maik Hagenbruch <maik@hagenbru.ch>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published
 *  by the Free Software Foundation; either version 3 of the License,
 *  or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ********************************************************************/

/**
 * Abstract repository
 */
abstract class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Returns a query for objects of this repository
	 *
	 * @param string $offset Offset to start with
	 * @param string $limit Limit of the results
	 * @param array $ordering Ordering <-> Direction
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	public function createQuery($offset = NULL, $limit = NULL, array $ordering = NULL) {
		$query = parent::createQuery();
		if (!empty($offset)) {
			$query->setOffset((int) $offset);
		}
		if (!empty($limit)) {
			$query->setLimit((int) $limit);
		}
		if (!empty($ordering)) {
			$query->setOrderings($ordering);
		}
		return $query;
	}

	/**
	 * Returns all objects by offset and limit
	 *
	 * @param array $uids Get objects from this uids
	 * @param array $pids Get objects from this pids
	 * @param string $offset Offset to start with
	 * @param string $count Count of results
	 * @param array $ordering Ordering <-> Direction
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findAll($offset = NULL, $limit = NULL, array $ordering = NULL) {
		$query = $this->createQuery($offset, $limit, $ordering);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		return $query->execute();
	}

	/**
	 * Returns objects by given uids and pids
	 *
	 * @param array $uids Get objects from this uids
	 * @param array $pids Get objects from this pids
	 * @param string $offset Offset to start with
	 * @param string $limit Limit of the results
	 * @param array $ordering Ordering <-> Direction
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findByUidsAndPids(array $uids, array $pids, $offset = NULL, $limit = NULL, array $ordering = NULL) {
		$query = $this->createQuery($offset, $limit, $ordering);
		// Disable default storage page handling
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		// UIDs and PIDs
		if (!empty($uids) && !empty($pids)) {
			$query->matching($query->logicalOr(
				$query->in('uid', $uids),
				$query->in('pid', $pids)
			));
		} else if (!empty($uids)) {
			$query->matching($query->in('uid', $uids));
		} else if (!empty($pids)) {
			$query->matching($query->in('pid', $pids));
		} else {
			throw new Exception('No UIDs and PIDs given');
		}
		return $query->execute();
	}

}
?>