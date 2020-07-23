<?php
namespace models;

use core\Model;
use models\obj\Student;
use models\obj\_Class;


/**
 * Responsible for managing students.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Students extends Model
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_student;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates students manager.
     *
     * @param       int $id_user [Optional] Student id
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($id_user = -1)
    {
        parent::__construct();
        $this->id_student = $id_user;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether a student is logged.
     *
     * @return      boolean If student is logged
     */
    public static function isLogged()
    {
        return !empty($_SESSION['s_login']);
    }
    
    /**
     * Checks whether student credentials are correct.
     *
     * @param       string $email Student's email
     * @param       string $pass Student's password
     *
     * @return      boolean If student credentials are correct
     */
    public function login($email, $pass)
    {
        if (empty($email) || empty($pass)) { return false; }
        
        $sql = $this->db->prepare("
            SELECT id 
            FROM students 
            WHERE email = ? AND password = ?
        ");
        $sql->execute(array($email, md5($pass)));
        
        if ($sql->rowCount() == 0) { return false; }
        
        $_SESSION['s_login'] = $sql->fetch()['id'];
        $this->id_student = $sql->fetch()['id'];
        
        return true;
    }
    
    /**
     * Adds a new student.
     *
     * @param       Student $student Informations about the student
     * @param       boolean $autologin [Optional] If true, after registration is completed
     * the student will automatically login to the system
     *
     * @return      int Student id or -1 if the student has not been added
     */
    public function register($student, $autologin = true)
    {
        if ($this->existUser($student)) { return false; }
        
        $sql = $this->db->prepare("
            INSERT INTO students 
            (name,genre,birthdate,email,password) 
            VALUES (?,?,?,?,?)
        ");
        $sql->execute(array(
            $student->getName(), 
            $student->getGenre(),
            $student->getBirthdate(),
            $student->getEmail(),
            md5($student->getPassword())
        ));

        if ($sql->rowCount() == 0) { return false; }
        
        if ($autologin)
            $_SESSION['s_login'] = $this->db->lastInsertId();
        
        return true;
    }
    
    /**
     * Gets student name.
     *
     * @return      string Student's name
     */
    public function getName()
    {
        if ($this->id_student == -1) { return ""; }
        
        $response = "";
        
        $sql = $this->db->query("SELECT name FROM students WHERE id = $this->id_student");
        
        if ($sql && $sql->rowCount() > 0) {
            $response = $sql->fetch()['name'];
        }
        
        return $response;
    }
    
    /**
     * Gets information about a student.
     *
     * @param       int $id_user [Optional] Student id
     *
     * @return      array Informations about the student
     */
    public function get($id_student)
    {
        $response = null;
         
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    students
            WHERE   id_student = ?
        ");
        $sql->execute(array($id_student));
        
        if ($sql->rowCount() > 0) {
            $student = $sql->fetch();
            
            $response = new Student(
                $student['name'], 
                $student['genre'], 
                $student['birthdate'], 
                $student['email'],
                $student['photo'] 
            );
        }
        
        return $response;
    }
    
    /**
     * Gets last class watched by the student.
     *
     * @param       int $id_course Course id
     *
     * @return      _Class ...
     */
    public function getLastClassWatched($id_course)
    {
        $response = NULL;
        
        $sql = $this->db->prepare("
            SELECT      id_module, class_order,
                        CASE
                            WHEN class_type = 0 THEN 'video'
                            ELSE 'questionnaire'
                        END AS class_type
            FROM        student_historic
            WHERE       id_student = ? AND
                        id_module IN (SELECT    id_module
                                      FROM      course_modules
                                      WHERE     id_course = ?)
            ORDER BY    date DESC
            LIMIT 1
        ");
        
        $sql->execute(array($this->id_student, $id_course));
        
        if ($sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            if ($class['class_type'] == 'video') {
                $videos = new Videos();
                
                $response = $videos->get(
                    $class['id_module'], 
                    $class['class_order']
                ); 
            }
            else {
                $questionnaries = new Questionnaires();
                
                $response = $questionnaries->get(
                    $class['id_module'],
                    $class['class_order']
                ); 
            }
        }
        
        return $response;
    }
    
    /**
     * Checks whether a student exists by its id.
     *
     * @param       int $id_student Student id
     *
     * @return      boolean If the student with the specified id exists
     */
//     public function exist($id_student)
//     {
//         if (empty($id_student) || $id_student <= 0) { return false; }
        
//         $sql = $this->db->prepare("SELECT COUNT(*) AS count FROM students WHERE id = ?");
//         $sql->execute(array($id_student));
        
//         return $sql->fetch()['count'] > 0;
//     }
    
    /**
     * Updates current student information.
     * 
     * @param       string $name
     * @param       int $genre New genre (0 => Man; 1 => Woman)
     * @param       string $birthdate New birthdate
     * 
     * @return      boolean If student information was sucessfully updated
     */
    public function update($name, $genre, $birthdate)
    {
        if (empty($name)) { return false; }
        
        $sql = $this->db->prepare("
            UPDATE students 
            SET name = ?, genre = ?, birthdate = ? 
            WHERE id = ".$this->id_student
        );
        $sql->execute(array($name, $genre, $birthdate));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Deletes current student.
     * 
     * @return      boolean If student was sucessfully deleted
     */
    public function delete()
    {
        $response = false;
        
        
        $sql = $this->db->query("DELETE FROM students WHERE id = ".$this->id_student);
        
        if ($sql->rowCount() > 0) {
            $this->db->query("DELETE FROM historic WHERE id_student = ".$this->id_student);
            $this->db->query("DELETE FROM student_course WHERE id_student = ".$this->id_student);
            
            $response = true;
        }
        
        return $response;
    }
    
    /**
     * Updates photo of the current student.
     * 
     * @param       array $photo New photo (from $_FILES)
     * 
     * @return      boolean If photo was sucessfully updated
     */
    public function updatePhoto($photo)
    {
        if (empty($photo)) {
            $imageName = $this->getPhoto();
            
            if (!empty($imageName)) {
                unlink("assets/images/profile_photos/".$imageName);
            }
        }
        
        else if (!empty($photo['tmp_name']) && $this->isPhoto($photo)) {
            $extension = explode("/", $photo['type'])[1];
            
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {
                
                $filename = md5(rand(1,9999).time().rand(1,9999));
                $filename = $filename."."."jpg";
                
                move_uploaded_file($photo['tmp_name'], "assets/images/profile_photos/".$filename);

                // Deletes old image (if there is one)
                $imageName = $this->getPhoto();
                
                if (!empty($imageName)) {
                    unlink("assets/images/profile_photos/".$imageName);
                }
            }
        }
        
        $filename = empty($filename) ? "'".$filename."'" : NULL;
        
        $sql = $this->db->query("
            UPDATE students 
            SET photo = ".$filename." 
            WHERE id = ".$this->id_student
        );
        return $sql->rowCount() > 0;
    }
    
    /**
     * Updates password from current student.
     * 
     * @param       string $currentPassword Current student password
     * @param       string $newPassword New password
     * 
     * @return      boolean If password was sucessfully updated
     */
    public function updatePassword($currentPassword, $newPassword)
    {
        if (empty($currentPassword) || empty($newPassword)) { return false; }
        
        $response = false;
        
        
        $sql = $this->db->query("
            SELECT COUNT(*) AS correctPassword 
            FROM students 
            WHERE id = ".$this->id_student." AND password = '".md5($currentPassword)."'
        ");
        
        if ($sql->fetch()['correctPassword'] > 0) {
            $sql = $this->db->query("
                UPDATE students 
                SET password = '".md5($newPassword)."' 
                WHERE id = ".$this->id_student
            );
            $response = $sql->rowCount() > 0;
        }
        
        return $response;
    }
    
    /**
     * Gets total watched classes in platform.
     * @param unknown $id_student
     * @return mixed
     */
    public function getTotalWatchedClasses($id_student)
    {
        $sql = $this->db->prepare("
            SELECT SUM(length) AS total_length
            FROM student_historic_watched_length
            WHERE id_student = ?
            GROUP BY id_module
            HAVING id_module IN (SELECT    id_module
            					 FROM      course_modules NATURAL JOIN bundle_courses 
                                           NATURAL JOIN purchases
            					 WHERE     id_student = ?)
        ");
        
        $sql->execute(array($id_student, $id_student));
        
        return $sql->fetch()['total_length'];
    }
    
    /**
     * Gets photo from current student.
     * 
     * @return      string Photo filename
     */
    private function getPhoto()
    {
        $response = null;
        
        $sql = $this->db->query("
            SELECT photo 
            FROM students 
            WHERE id = ".$this->id_student
        );
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['photo'];
        }
        
        return $response;
    }
    
    /**
     * Checks whether a student exists by its email.
     *
     * @param       Student $Student Informations about the student
     *
     * @return      boolean If there is already a student with the email used.
     */
    private function existUser($student) 
    {
        $email = $student->getEmail();
        
        if (empty($email)) { return false; }
        
        $sql = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM students 
            WHERE email = ?
        ");
        $sql->execute(array($email));

        return $sql->fetch()['count'] > 0;
    }
    
    /**
     * Checks if a submitted photo is really a photo.
     *
     * @param       array $photo Submitted photo (from $_FILES)
     * 
     * @return      boolean If the photo is really a photo
     */
    private function isPhoto($photo)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $photo['tmp_name']);
        finfo_close($finfo);
        
        return explode("/", $mime)[0] == "image";
    }
}