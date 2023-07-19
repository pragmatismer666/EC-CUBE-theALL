<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Annotation as Eccube;
use Symfony\Bridge\Doctrine\Validator\Contraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;


/**
 * Shop
 * 
 * @ORM\Table(name="cme_katakana")
 * @ORM\Entity(repositoryClass="Customize\Repository\KatakanaRepository")
 */
class Katakana extends AbstractEntity {
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    

    /**
     * @var string
     * 
     * @ORM\Column(name="chara", type="string", length=10)
     */
    private $character;

    public function getId() {
        return $this->id;
    }
    
    public function getCharacter() {
        return $this->character;
    }
    public function setCharacter( $character ) {
        $this->character = $character;
        return $this;
    }
}