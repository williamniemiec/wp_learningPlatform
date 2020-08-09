<?php 
declare (strict_types=1);

namespace models;

use DateTime;
use models\enum\GenreEnum;


/**
 * Responsible for representing users. An user can be a student or an admin.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    protected $id;
    protected $name;
    protected $genre;
    protected $birthdate;
    protected $email;
    
    
    //-------------------------------------------------------------------------
    //        Getters & Setters
    //-------------------------------------------------------------------------
    /**
     * Gets user id.
     * 
     * @return      int User id
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    /**
     * Gets user name.
     * 
     * @return      string name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Sets user name.
     * 
     * @param       string $name User name
     * 
     * @return      User Itself to allow chained calls
     */
    public function setName(string $name) : User
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Gets user genre
     * 
     * @return      GenreEnum User's genre
     */
    public function getGenre() : GenreEnum
    {
        return $this->genre;
    }
    
    /**
     * Sets user genre.
     *
     * @param       string $name User genre
     *
     * @return      User Itself to allow chained calls
     */
    public function setGenre(GenreEnum $genre) : User
    {
        $this->genre = $genre;
        return $this;
    }
    
    /**
     * Gets user birthdate.
     * 
     * @return      DateTime User birthdate
     */
    public function getBirthdate() : DateTime
    {
        return $this->birthdate;
    }
    
    /**
     * Sets user birthdate.
     *
     * @param       \DateTime $birthdate User birthdate
     *
     * @return      User Itself to allow chained calls
     */
    public function setBirthdate(\DateTime $birthdate) : User
    {
        $this->birthdate = $birthdate;
        return $this;
    }
    
    /**
     * Gets user email.
     *
     * @return      string User email
     */
    public function getEmail() : string
    {
        return $this->email;
    }
}