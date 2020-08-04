<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;
use models\Student;


/**
 * Main controller. It will be responsible for site's main page behavior.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class HomeController extends Controller 
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if student is logged; otherwise, redirects him to home
     * page.
     */
    public function __construct()
    {
        if (Student::isLogged()) {
            header("Location: ".BASE_URL);
            exit;
        }
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
	public function index ()
	{
	    $students = new Students($_SESSION['s_login']);
	    $courses = new Courses($_SESSION['s_login']);
	    
	    
	    $header = array(
	        'title' => 'Home - Learning platform',
	        'styles' => array('home'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
		$viewArgs = array(
		    'username' => $students->getName(),
		    'courses' => $courses->getMyCourses(),
		    'totalCourses' => $courses->countCourses(),
		    'header' => $header
		);

		$this->loadTemplate("home", $viewArgs, true);
	}
	
	/**
	 * Logout current student and redirects him to login page.
	 */
	public function logout()
	{
	    unset($_SESSION['s_login']);
	    header("Location: ".BASE_URL."login");
	}
	
	/**
	 * Opens student settings.
	 */
	public function settings()
	{
	    $students = new Students($_SESSION['s_login']);
	    $courses = new Courses($_SESSION['s_login']);
	    $student = $students->get($_SESSION['s_login']);
	    
	    
	    $header = array(
	        'title' => 'Home - Learning platform',
	        'styles' => array('settings'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
	    $viewArgs = array(
	        'username' => $student->getName(),
	        'genre' => $student->getGenre(),
	        'birthdate' => explode(" ", $student->getBirthdate())[0],
	        'email' => $student->getEmail(),
	        'courses' => $courses->getMyCourses(),
	        'totalCourses' => $courses->countCourses(),
	        'header' => $header
	    );
	    
	    $this->loadTemplate("settings", $viewArgs, true);
	}
}
