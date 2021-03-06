<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\enum\OrderDirectionEnum;
use models\Admin;
use models\Bundle;
use models\enum\BundleOrderTypeEnum;
use models\util\IllegalAccessException;
use models\Action;


/**
 * Responsible for managing 'bundles' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class BundlesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'bundles' table manager.
     *
     * @param       Database $db Database
     * @param       Admin $admin [Optional] Admin logged in
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        $this->db = $db->getConnection();
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets a bundle
     *
     * @param       int $id_bundle Bundle id or null if there is no bundle with
     * the given id
     *
     * @return      Bundle Bundle with the given id
     *
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero
     */
    public function get(int $id_bundle) : Bundle
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    bundles
            WHERE   id_bundle = ?
        ");
            
        // Executes query
        $sql->execute(array($id_bundle));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $bundle = $sql->fetch();
            $response = new Bundle(
                (int)$bundle['id_bundle'],
                $bundle['name'],
                (float)$bundle['price'],
                $bundle['logo'],
                $bundle['description']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all registered bundles. If a filter option is provided, it gets 
     * only those bundles that satisfy these filters.
     * 
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return           
     * @param       string $name [Optional] Bundle name
     * @param       BundleOrderTypeEnum $orderBy [Optional] Ordering criteria 
     * @param       OrderDirectionEnum $orderType [Optional] Order that the 
     * elements will be returned. Default is ascending
     * 
     * @return      Bundle[] Bundles with the provided filters or empty array if
     * no bundles are found.
     */
    public function getAll(int $limit = -1, int $offset = -1, string $name = '', 
        BundleOrderTypeEnum $orderBy = null, OrderDirectionEnum $orderType = null) : array
    {
        $response = array();
        $bindParams = array();

        if (empty($orderType))
            $orderType = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);
        
        // Query construction
        $query = "
            SELECT      id_bundle, name, bundles.price, logo, description,
                        COUNT(id_course) AS courses,
                        COUNT(id_student) AS sales
            FROM        bundles 
                        NATURAL LEFT JOIN bundle_courses
                        LEFT JOIN purchases USING (id_bundle)
            GROUP BY    id_bundle, name, bundles.price, description
        ";
        
        // Limits the search to a specified name (if a name was specified)
        if (!empty($name)) {
            $query .= empty($orderBy) ? " HAVING name LIKE ?" : " HAVING name LIKE ?";
            $bindParams[] = $name.'%';
        }
        
        // Sets order by criteria (if any)
        if (!empty($orderBy)) {
            $query .= " ORDER BY ".$orderBy->get()." ".$orderType->get();
        }

        // Limits the results (if a limit was given)
        if ($limit > 0) {
            if ($offset > 0)    
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        // Prepares query
        $sql = $this->db->prepare($query);

        // Executes query
        $sql->execute($bindParams);
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $bundles = $sql->fetchAll();
            $i = 0;
            
            foreach ($bundles as $bundle) {
                $response[$i] = new Bundle(
                    (int)$bundle['id_bundle'],
                    $bundle['name'],
                    (float)$bundle['price'],
                    $bundle['logo'],
                    $bundle['description']
                );
                
                $response[$i]->setTotalStudents((int)$bundle['sales']);
                $i++;
            }
        }

        return $response;
    }
    
    /**
     * Creates a new bundle.
     * 
     * @param       Bundle $bundle Bundle to be created
     * 
     * @return      bool If bundle has been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to create bundles
     * @throws      \InvalidArgumentException If bundle is empty or if admin
     * provided in the constructor is empty
     */
    public function new(Bundle $bundle) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 && 
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
        if (empty($bundle))
            throw new \InvalidArgumentException("Bundle cannot be empty");
        
        $response = false;
        $bindParams = array(
            $bundle->getName(),
            $bundle->getPrice()
        );
            
        // Query construction
        $query = "
            INSERT INTO bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
            $bindParams[] = $bundle->getDescription();
        }
        
        if (!empty($bundle->getLogo())) {
            $query .= ", logo = ?";
            $bindParams[] = $bundle->getLogo();
        }

        // Prepares query
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute($bindParams);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->addBundle((int)$this->db->lastInsertId());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Updates a bundle.
     * 
     * @param       Bundle $bundle Updated bundle
     * 
     * @return      bool If bundle has been successfully updated
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle is empty or if admin  
     * provided in the constructor is empty
     */
    public function update(Bundle $bundle) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($bundle))
            throw new \InvalidArgumentException("Bundle cannot be empty");

        $response = false;
        $bindParams = array(
            $bundle->getName(),
            $bundle->getPrice()
        );
            
        // Query construction
        $query = "
            UPDATE bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
            $bindParams[] = $bundle->getDescription();
        }
        
        if (!empty($bundle->getLogo())) {
            $query .= ", logo = ?";
            $bindParams[] = $bundle->getLogo();
        }

        $query .= " WHERE id_bundle = ".$bundle->getId();
        
        // Prepares query
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute($bindParams);

        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($bundle->getId());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * 
     * @return      bool If bundle has been successfully removed
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to remove bundles
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function remove($id_bundle)
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
                throw new \InvalidArgumentException("Bundle id cannot be empty ".
                    "or less than or equal to zero");
        
        $response = false;
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM bundles
            WHERE id_bundle = ?
        ");
        
        // Executes query
        $sql->execute(array($id_bundle));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->deleteBundle($id_bundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes logo from a bundle.
     *
     * @param       int $id_bundle Bundle id
     *
     * @return      bool If bundle logo has been successfully removed
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to remove bundles
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function removeLogo(int $id_bundle) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
        
        $response = false;
            
        // Query construction
        $sql = $this->db->query("
            UPDATE  bundles
            SET     logo = NULL
            WHERE   id_bundle = ".$id_bundle
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($id_bundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Adds a course to a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * @param       int $id_course Course id
     * 
     * @return      bool If course has been successfully added to the bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id, course id is empty,
     * less than or equal to zero or if admin id provided in the
     * constructor is empty
     */
    public function addCourse(int $id_bundle, int $id_course) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
                
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO bundle_courses
            (id_bundle, id_course)
            VALUES (?, ?)
        ");
        
        // Executes query
        $sql->execute(array($id_bundle, $id_course));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($id_bundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes a course from a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * @param       int $id_course Course id
     * 
     * @return      bool If course has been successfully removed from the bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id or course id  is 
     * empty, less than or equal to zero
     */
    public function deleteCourseFromBundle(int $id_bundle, int $id_course) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM bundle_courses
            WHERE id_bundle = ? AND id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_bundle, $id_course));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($id_bundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes all courses from a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * 
     * @return      bool If all courses have been successfully removed from the 
     * bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id is empty, less than 
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function deleteAllCourses(int $id_bundle) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
                
        $response = false;
            
        $sql = $this->db->query("
            DELETE FROM bundle_courses
            WHERE id_bundle = ".$id_bundle
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($id_bundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Gets total of bundles.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    bundles
        ")->fetch()['total'];
    }
}