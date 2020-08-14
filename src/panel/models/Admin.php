<?php 
declare (strict_types=1);

namespace models;


use database\Database;
use DateTime;
use models\enum\GenreEnum;
use models\dao\AdminsDAO;

/**
 * Responsible for representing admin-type users.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Admin extends User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_authorization;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a admin-type user.
     * 
     * @param       int $id Administrator id
     * @param       Authorization $authorization Authorization that the
     * administrator has
     * @param       string $name Administrator name
     * @param       GenreEnum $genre Administrator genre
     * @param       DateTime $birthdate Administrator birthdate
     * @param       string $email Administrator email
     */
    public function __construct(int $id, Authorization $authorization, string $name, 
        GenreEnum $genre, DateTime $birthdate, string $email)
    {
        $this->id = $id;
        $this->authorization = $authorization;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether an admin is logged.
     *
     * @return      bool If admin is logged
     */
    public static function isLogged() : bool
    {
        return !empty($_SESSION['a_login']);
    }
    
    /**
     * Checks if login has been successfully or failed.
     *
     * @param       string $email Admin's email
     * @param       string $password Admin's password
     *
     * @return      Admin Information about admin logged in or null if
     * login failed
     */
    public static function login(Database $db, string $email, string $password) : ?Admin
    {
        $adminsDAO = new AdminsDAO($db);
        $admin = $adminsDAO->login($email, $password);
        
        if (!empty($admin))
            $_SESSION['a_login'] = $admin->getId();
            
        return $admin;
    }
    
    /**
     * Gets logged in admin.
     *
     * @param       Database $db Database
     *
     * @return      Admin Admin logged in or null if there is no admin
     * logged in
     */
    public static function getLoggedIn(Database $db) : ?Admin
    {
        if (empty($_SESSION['a_login']))
            return null;
            
        $adminsDAO = new AdminsDAO($db, $_SESSION['a_login']);
        
        return $adminsDAO->get();
    }
    
    /**
     * Logout current admin.
     */
    public static function logout() : void
    {
        unset($_SESSION['a_login']);
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets authorization that the administrator has.
     * 
     * @return      Authorization Authorization that the administrator has
     */
    public function getAuthorization() : Authorization
    {
        return $this->authorization;
    }
}