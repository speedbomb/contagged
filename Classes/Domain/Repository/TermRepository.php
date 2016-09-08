<?php
namespace Speedbomb\Contagged\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Marco Schrepfer <typo3@speedbomb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
 ***************************************************************/

use Speedbomb\Contagged\Domain\Model\Term;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository class for {@link Speedbomb\Contagged\Domain\Model\Term}
 * @package contagged
 */
class TermRepository extends Repository {

	/**
	 * Default orderings ascending by name
	 *
	 * @var array $defaultOrderings
	 */
	protected $defaultOrderings = array(
		'termMain' => QueryInterface::ORDER_ASCENDING,
	);

    /**
     * Returns terms (and their alternate terms) as string words.
     *
     * @param bool $sortedByLength Should the terms be sorted by length?
     * @return array $termWords
     */
	public function fetchTermWords($sortedByLength = false) {
		$termWords = array();
		$terms = $this->findTerms();

		// get the main term and possibly the alternate terms
		foreach($terms as $term) {
			$termWords[] = $term->getTermMain();
			if(count($termsAlt = $term->getTermAlt(true)) > 0) {
				$termWords = array_merge($termWords, $termsAlt);
			};
			unset($termsAlt);
		}

        // should the terms be sorted by length?
        if($sortedByLength == true) {
            /**
             * Sorting Callback (by string length descending)
             *
             * @param Term $termA
             * @param Term $termB
             * @return int
             */
            $sortingCallback = function($termA, $termB) {
                return strlen($termB) - strlen($termA);
            };

            // Sort terms
            usort($termWords, $sortingCallback);
        }

        return $termWords;
	}

    /**
     * Returns all objects of this repository.
     *
     * @return \Speedbomb\Contagged\Domain\Model\Term[]
     */
    public function findTerms() {
        $records = array();
        $result = $this->findAll();
        if($result->count() > 0) {
            $records = $result->toArray();
        }
        return $records;
    }

	/**
	 * find all terms sorted by name length
	 *
	 * @return array
	 */
	public function findByTermMainLength() {
		$terms = $this->findTerms();

		/**
		 * Sorting Callback (by string length descending)
		 *
		 * @param Term $termA
		 * @param Term $termB
		 * @return int
		 */
		$sortingCallback = function($termA, $termB) {
			return strlen($termB->getTermMain()) - strlen($termA->getTermMain());
		};

		// Sort terms
		usort($terms, $sortingCallback);

		return $terms;
	}

	/**
	 * find all terms ordered by name and grouped by first character
	 *
	 * @return array
	 */
	public function findAllGroupedByFirstCharacter() {
		$terms          = $this->findAll();
		$numbers        = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$normalChars    = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$sortedTerms    = array();

		/** @var Term $term */
		foreach ($terms as $term) {
			$firstCharacter = mb_strtolower(mb_substr($term->getTermMain(), 0, 1, 'UTF-8'), 'UTF-8');

			if (in_array($firstCharacter, $numbers)) {
				$firstCharacter = '0-9';
			} else if (FALSE === in_array($firstCharacter, $normalChars)) {
				switch ($firstCharacter) {
					case 'ä':
						$firstCharacter = 'a';
					break;
					case 'ö':
						$firstCharacter = 'o';
					break;
					case 'ü':
						$firstCharacter = 'u';
					break;
					default:
						$firstCharacter = '_';
					break;
				}

			}

			$firstCharacter = mb_strtoupper($firstCharacter, 'UTF-8');

			if (FALSE === isset($sortedTerms[$firstCharacter])) {
				$sortedTerms[$firstCharacter] = array();
			}

			$sortedTerms[$firstCharacter][] = $term;
		}

		return $sortedTerms;
	}
}
