<?php
namespace Speedbomb\Contagged\Domain\Model;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This class represents a term.
 *
 * @author Marco Schrepfer <typo3@speedbomb.de>
 * @package contagged
 */
class Term extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

    /**
     * Long description.
     *
     * @var string
     */
    protected $description;

    /**
     * Image references
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $image;

    /**
     * Link
     *
     * @var string
     */
    protected $link;

    /**
     * Pronunciation
     *
     * @var string
     */
    protected $pronunciation;

    /**
     * Reference
     *
     * @var string
     */
    protected $reference;

    /**
     * Related terms
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Speedbomb\Contagged\Domain\Model\Term>
     * @lazy
     */
    protected $related;

    /**
     * Short description, i.e. for tool tips.
     *
     * @var string
     */
    protected $short;

    /**
     * Alternative terms (separted by line break)
     *
     * @var string
     */
    protected $termAlt;

    /**
     * Language of the term.
     * 2-char language code.
     *
     * @var string
     */
    protected $termLang;

    /**
     * (main) name of the term
     *
     * @var string
     * @validate NotEmpty
     */
    protected $termMain;

    /**
     * Replacement for the term.
     *
     * @var string
     */
    protected $termReplace;

    /**
     * Type of the term.
     * Configured by TypoScript.
     *
     * @var string
     */
    protected $termType;

    /**
     * Constructor
     */
    public function __construct() {
        $this->initializeObjectStorages();
    }

    /**
     * Adds a term
     *
     * @param \Speedbomb\Contagged\Domain\Model\Term $term Term that will be added
     */
    public function addRelated(Term $term) {
        $this->related->attach($term);
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getLink() {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getPronunciation() {
        return $this->pronunciation;
    }

    /**
     * @param bool $as_list Return field value as single items (separated by line break)
     * @return string|array
     */
    public function getReference($as_list = false) {
        if($as_list == true) {
            $reference = str_replace(array("\r\n", "\r"), "\n", $this->reference);
            $references = GeneralUtility::trimExplode("\n", $reference, true);
            foreach($references as $rk => $rv) {
                if(preg_match('/^(http|https):\/\/(.*)/', $rv, $matches)) {
                    $references[$rk] = '<a href="' . $matches[0] . '" target="_blank">' . $matches[2] . '</a>';
                }
            }

            return $references;
        }

        return $this->reference;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Speedbomb\Contagged\Domain\Model\Term>
     */
    public function getRelated() {
        return $this->related;
    }

    /**
     * @return string
     */
    public function getShort() {
        return $this->short;
    }

    /**
     * @return string
     */
    public function getTermAlt() {
        return $this->termAlt;
    }

    /**
     * @return string
     */
    public function getTermLang() {
        return $this->termLang;
    }

    /**
     * @return string
     */
    public function getTermMain() {
        return $this->termMain;
    }

    /**
     * @return string
     */
    public function getTermReplace() {
        return $this->termReplace;
    }

    /**
     * @return string
     */
    public function getTermType() {
        return $this->termType;
    }

    /**
     * Initialize object storages
     */
    public function initializeObjectStorages() {
        $this->related = new ObjectStorage();
    }

    /**
     * Removes a term
     *
     * @param \Speedbomb\Contagged\Domain\Model\Term $term Term that will be removed
     */
    public function removeRelated(Term $term) {
        $this->related->detach($term);
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image) {
        $this->image = $image;
    }

    /**
     * @param string $link
     */
    public function setLink($link) {
        $this->link = $link;
    }

    /**
     * @param string $pronunciation
     */
    public function setPronunciation($pronunciation) {
        $this->pronunciation = $pronunciation;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference) {
        $this->reference = $reference;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $related
     */
    public function setRelated($related) {
        $this->related = $related;
    }

    /**
     * @param string $short
     */
    public function setShort($short) {
        $this->short = $short;
    }

    /**
     * @param string $termAlt
     */
    public function setTermAlt($termAlt) {
        $this->termAlt = $termAlt;
    }

    /**
     * @param string $termLang
     */
    public function setTermLang($termLang) {
        $this->termLang = $termLang;
    }

    /**
     * @param string $termMain
     */
    public function setTermMain($termMain) {
        $this->termMain = $termMain;
    }

    /**
     * @param string $termReplace
     */
    public function setTermReplace($termReplace) {
        $this->termReplace = $termReplace;
    }

    /**
     * @param string $termType
     */
    public function setTermType($termType) {
        $this->termType = $termType;
    }

}