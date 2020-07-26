<?php
namespace models;

use core\Model;
use models\obj\SupportTopic;
use models\obj\Message;
use models\obj\SupportTopicCategory;



/**
 * Responsible for managing 'support_topic' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportTopics extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'support_topic' table manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets information about a support topic.
     *
     * @param      int $id_topic Topic id
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     * 
     * @throws      \InvalidArgumentException If topic id is invalid
     */
    public function get(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
        
        $response = NULL;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));

        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $supportTopic = $sql->fetch(\PDO::FETCH_ASSOC);
            $students = new Students();
            
            $response = new SupportTopics(
                $supportTopic['id_topic'],
                $students->get($supportTopic['id_student']), 
                $supportTopic['title'], 
                $supportTopic['name'], 
                $supportTopic['date'], 
                $supportTopic['message'], 
                $supportTopic['closed']
            );
        }
        
        return $response;
    }
    
    /**
     * Creates a new support topic.
     * 
     * @param       int $id_category Category id that the support topic belongs
     * @param       int $id_student Student id that created the support topic
     * @param       string $title Support topic's title
     * @param       string $message Support topic's content
     * 
     * @return      bool If support topic was successfully created
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function new(int $id_category, int $id_student, string $title, string $message) : bool
    {
        if (empty($id_category) || $id_category <= 0)
            throw new \InvalidArgumentException("Invalid category id");
        
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        if (empty($title))
            throw new \InvalidArgumentException("Title cannot be empty");
        
        if (empty($message))
            throw new \InvalidArgumentException("Message cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic
            (id_category, id_student, title, date, message)
            VALUES (?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_category, $id_student, $title, $message));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Deletes a support topic
     * 
     * @param       int $id_student Student id logged in
     * @param       int $id_topic Support topic id to be deleted
     * 
     * @return      bool If support topic was sucessfully removed
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function delete(int $id_student, int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM support_topic
            WHERE id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Closes a support topic.
     * 
     * @param       int $id_student Student id logged in
     * @param       int $id_topic Support topic id to be closed
     * 
     * @return      bool If support topic was successfully closed
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function close(int $id_student, int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Opens a support topic.
     *
     * @param       int $id_student Student id logged in
     * @param       int $id_topic Support topic id to be opened
     *
     * @return      bool If support topic was successfully closed
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function open(int $id_student, int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Replies a support topic.
     * 
     * @param       int $id_topic Support topic id to be replied
     * @param       int $id_student Student id that will reply the support topic
     * @param       string $text Reply's content
     * 
     * @return      bool If the reply was sucessfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function newReply(int $id_topic, int $id_student, string $text) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        if (empty($text))
            throw new \InvalidArgumentException("Text cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 0, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student, $text));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets all replies from a support topic.
     * 
     * @param       int $id_topic Support topic id
     * 
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     * 
     * @throws      \InvalidArgumentException If topic id is invalid
     */
    public function getReplies(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");

        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $replies = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($replies as $reply) {
                if ($reply['user_type'] == 0) {
                    $students = new Students();
                    $user = $students->get($reply['id_user']);
                }
                else {
                    $admins = new Admins();
                    $user = $admins->get($reply['id_user']);
                }
                
                $response[] = new Message(
                    $user, 
                    $reply['date'], 
                    $reply['text']
                );
            }
        }
            
        return $response;
    }
    
    /**
     * Gets all answered support topics from a user with a specific category.
     * 
     * @param       int $id_student Student id
     * @param       int $id_category Category id
     * 
     * @return      SupportTopic[] Support topics that have already been 
     * answered and that belongs to the category with the given id or empty
     * array if there are no matches
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function getAllAnsweredByCategory(int $id_student, int $id_category) : array
    {
        if (empty($id_category) || $id_category <= 0)
            throw new \InvalidArgumentException("Invalid category id");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_category
            WHERE   id_student = ? AND
                    id_category = ? AND
                    id_topic IN (SELECT id_topic
                                 FROM   support_topic_replies)
        ");
        
        // Executes query
        $sql->execute(array());
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $students = new Students();
            
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $supportTopic) {
                $response = new SupportTopics(
                    $supportTopic['id_topic'],
                    $students->get($supportTopic['id_student']),
                    $supportTopic['title'],
                    $supportTopic['name'],
                    $supportTopic['date'],
                    $supportTopic['message'],
                    $supportTopic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Searches for a topic with a given name.
     * 
     * @param       int $id_student Student id
     * @param       string $name Name to be searched
     * 
     * @return      SupportTopic[] Support topics that match with the provided
     * name or empty array if there are no matches
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function search(int $id_student, string $name) : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
            
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_category
            WHERE   id_student = ? AND title LIKE ?
        ");
        
        // Executes query
        $sql->execute(array($id_student, $name.'%'));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $students = new Students();
            
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $supportTopic) {
                $response = new SupportTopics(
                    $supportTopic['id_topic'],
                    $students->get($supportTopic['id_student']),
                    $supportTopic['title'],
                    $supportTopic['name'],
                    $supportTopic['date'],
                    $supportTopic['message'],
                    $supportTopic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets all support topic categories.
     * 
     * @return      SupportTopicCategory[] Support topic categories or empty 
     * array if there are no registered categories
     */
    public function getCategories() : array
    {
        $response = array();
        
        // Query construction
        $sql = $this->db->query("
            SELECT  *
            FROM    support_category
        ");
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $category) {
                $response[] = new SupportTopicCategory(
                    $category['id_category'],
                    $category['name']
                );
            }
        }
        
        return $response;
    }
}