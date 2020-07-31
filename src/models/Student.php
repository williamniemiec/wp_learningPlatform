<?php
declare (strict_types=1);

namespace models;

use models\enum\GenreEnum;


/**
 * Responsible for representing student-type users.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Student extends User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $photo;
    

    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a student-type user.
     *
     * @param       int $id Student id
     * @param       string $name Student name
     * @param       GenreEnum $genre Student genre
     * @param       string $birthdate Student birthdate
     * @param       string $email Student email
     * @param       string $photo [Optional] Name of the student photo file
     */
    public function __construct(int $id, string $name, GenreEnum $genre, 
        string $birthdate, string $email, string $photo = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
        $this->photo = empty($photo) ? "" : $photo;
    }

    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets the name of the student photo file.
     * 
     * @return      string Name of the student photo file or empty string if
     * the student does not have a registered photo.
     */
    public function getPhoto() : string
    {
        return $this->photo;
    }
}