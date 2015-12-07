<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * model.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Datastore\Model;

use Budkit\Datastore\Database;
use Budkit\Dependency\Container;
use Budkit\Helper\Time;
use Whoops\Example\Exception;


/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Entity extends DataModel
{

    protected $propertyData = array();
    protected $propertyModel = array();
    protected $objectId = NULL;
    protected $objectType = NULL;
    protected $objectURI = NULL;
    protected $valueGroup = NULL; //property value groups can be sub categorised;
    protected $listOrderByStatement = NULL;
    protected $listLookUpConditions = array();
    protected $listLookUpConditionProperties = array();
    protected static $withConditions = false;
    protected $savedObjectURI = NULL;
    protected $registry = array();


    //@TODO we need to find a better way to hide non static variables
    //This is set in in constructor but the intention is to hide it hidden;
    //protected $database;


    public function __construct(Database $database, Container $container)
    {

        parent::__construct($container);

        $this->container = $container;
        $this->database = $database;

    }

    /**
     * Sets the property Value before save
     *
     * @param string $property Proprety ID or Property Name
     * @param type $value
     */
    public function setPropertyValue($property, $value = NULL, $objectId = NULL)
    {

        $property = strtolower($property);
        //1. Check that the property exists in $dataModel;
        if (!array_key_exists($property, $this->propertyModel))
            return false; //@TODO Raise error? specified column not found
        //2. Validate the Value?
        if (empty($value)):
            //Attempt to get the default value;
            if (isset($this->propertyModel[$property][3])) //the third item in the array should be the default value;
                $value = $this->propertyModel[$property][3];
        endif;
        //3. Store the value with the property name in $propertyData;
        if (empty($objectId) || (int)$objectId == $this->objectId):
            $this->propertyData[$property] = $value;
            //elseif(!empty($objectId)):
            //@TODO Go to the database set the property value for this object
        endif;

        return $this;
    }

    /**
     * Set the Entity Type for the current Entity
     *
     * @param string $entityType
     * @return void
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
        return $this;
    }

    /**
     * Get the Entity Type for the current Entity
     *
     * @param string $entityType
     * @return void
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * Set the Entity Type for the current Entity
     *
     * @param string $entityType
     * @return void
     */
    public function setLastSavedObjectURI($objectId)
    {
        $this->savedObjectURI = $objectId;
        return $this;
    }

    /**
     * Get the Entity Type for the current Entity
     *
     * @param string $entityType
     * @return void
     */
    public function getLastSavedObjectURI()
    {
        return $this->savedObjectURI;
    }

    /**
     * Set the Entity Type for the current Entity
     *
     * @param string $entityType
     * @return void
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * Get the Entity Type for the current Entity
     *
     * @param string $entityType
     * @return void
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     *
     * @param type $objectURI
     * @return \Platform\Entity
     */
    public function setObjectURI($objectURI)
    {
        $this->objectURI = $objectURI;
        return $this;
    }

    /**
     *
     * @param type $objectURI
     * @return \Platform\Entity
     */
    public function getObjectURI()
    {
        return $this->objectURI;
    }

    /**
     * Returns the property definition for a given property by name
     * Use {static::getProperNameFromId} to get the propery name from an Id
     *
     * @param string $propertyName
     * @return array
     *
     */
    public function getPropertyDefinition($propertyName)
    {

    }

    /**
     * Returns an entity property value by propery name if exists
     *
     * @todo Allow for default value setting;
     * @param string $propertyName
     * @param interger $objectIdId
     * @return mixed
     */
    public function getPropertyValue($propertyName, $objectId = null)
    {

        //You can return this protected properties as objects too.
        if (in_array($propertyName, array("objectId", "objectType", "objectURI")) && isset($this->$propertyName)) {
            return $this->$propertyName;
        }

        $property = strtolower($propertyName);
        //1. Check that the property exists in $dataModel;
        if (!array_key_exists($property, $this->propertyModel))
            return false; //@TODO Raise error? specified column not found
        //2. if isset objectId and object is this object, check value in propertyData
        if ((!empty($objectId) && (int)$objectId == $this->objectId) || empty($objectId)) {
            //IF we have a property that is not defined go get 
            if (!isset($this->propertyData[$property])) {
                //@TODO Database QUERY with objectId
                //Remember that this will most likely be used for 'un-modeled' data
                return;
            }
            //If we already have the property set and the objectId is empty
            return $this->propertyData[$property];
        }
        //3. If we have an Id and its not the same go back to the DB
        //if we have an id
        return;
    }

    /**
     * Return Object lists with matched properties between two values
     *
     * @param type $property
     * @param type $valueA
     * @param type $valueB
     * @param type $select
     * @param type $objectType
     * @param type $objectURI
     * @param type $objectId
     */
    public function getObjectsByPropertyValueBetween($property, $valueA, $valueB, array $select, $objectType = NULL, $objectURI = NULL, $objectId = NULL)
    {

        if (empty($property) || empty($valueA) || empty($valueB) || empty($select))
            return false; //We must have eactly one property value pair defined 

        $query = static::getObjectQuery($select, "?{$this->valueGroup}property_values", $objectId, $objectType, $objectURI);

        $query .= "\nGROUP BY o.object_id";
        $query .= "\nHAVING {$property} BETWEEN {$valueA} AND {$valueB}"; //@TODO check if we are comparing dates and use CAST() to convert to dates 


        $results = $this->database->prepare($query)->execute();

        return $results;
    }

    /**
     * Return Object lists with properties values similar to defined value (in part or whole)
     *
     * @param type $property the property name or alias used in searching. MUST be included in the select array. @TODO group concat property array for searching in multiple fields
     * @param type $value the value of the propery name being searched for;
     * @param type $select
     * @param type $objectType
     * @param type $objectURI
     * @param type $objectId
     */
    public function getObjectsByPropertyValueLike($property, $value, array $select = array(), $objectType = NULL, $objectURI = NULL, $objectId = NULL)
    {

        if (empty($property) || empty($value))
            return NULL; //We must have eactly one property value pair defined 
        $select = (empty($select)) ? array($property) : array_merge(array($property), $select); //If we have an empty select use the values from property;
        $query = static::getObjectQuery($select, "?{$this->valueGroup}property_values", $objectId, $objectType, $objectURI);

        $query .= "\nGROUP BY o.object_id";
        $query .= "\nHAVING {$property} LIKE '%{$value}%'";


        $results = $this->database->prepare($query)->execute();

        return $results;
    }

    /**
     * Sets a property conditional value for the select query
     *
     * @param type $property
     * @param type $value
     *
     */
    public function setPropertyValueCondition($property, $value)
    {
        return $this->setListLookUpConditions($property, $value);
    }

    /**
     * Return Object lists with properties matching the given value
     *
     * @param type $properties list of properties to match to values, must have exactly a value pair in the values array and must be included in the select array
     * @param type $values
     * @param type $select
     * @param type $objectType
     * @param type $objectURI
     * @param type $objectId
     */
    public function getObjectsByPropertyValueMatch(array $properties, array $values, array $select = array(), $objectType = NULL, $objectURI = NULL, $objectId = NULL)
    {

        if (empty($properties) || empty($values))
            return false; //We must have eactly one property value pair defined 
        $select = array_merge($properties, $select); //If we have an empty select use the values from property;
        $query = static::getObjectQuery($select, "?{$this->valueGroup}property_values", $objectId, $objectType, $objectType);

        $query .= "\nGROUP BY o.object_id";
        $p = count($properties);
        $v = count($values);
        if (!empty($properties) && !empty($values) && $p === $v):
            $query .= "\nHAVING\t";
            $having = false;
            for ($i = 0; $i < $p; $i++):
                $query .= ($having) ? "\tAND\t" : "";
                $query .= "{$properties[$i]} = " . $this->database->quote($values[$i]);
                $having = true;
            endfor;
        endif;

        $results = $this->database->prepare($query)->execute();

        return $results;
    }

    /**
     * Sets the list order direction
     *
     * @param type $fields comma seperated list, or array
     * @param type $direction
     * @return \Platform\Entity
     */
    final public function setListOrderBy($fields, $direction = "ASC")
    {

        $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' ' . $direction : ' ASC';
        $orderby = NULL;
        //Clean up the order by field list
        if (!empty($fields) && !is_array($fields)) {
            $temp = array();
            foreach (explode(',', $fields) as $part) {
                $part = trim($part);
                $temp[] = $part;
            }

            $orderby = implode(', ', $temp);
        } else if (is_array($fields)) {
            $temp = array();
            foreach ($fields as $field) {
                $part = trim($field);
                $temp[] = $part;
            }
            $orderby = implode(', ', $temp);
        }


        if (!empty($orderby)) {
            $this->listOrderByStatement = "\nORDER BY " . $orderby . $direction;
        }
        //Return this object
        return $this;
    }

    /**
     * Returns the list orderby statement if any defined or NULL if none
     *
     * @return string
     */
    final public function getListOrderByStatement()
    {
        return $this->listOrderByStatement;
    }

    /**
     * Sets lookup conditions for entity table search
     *
     * @param type $key
     * @param type $value
     * @param type $type
     * @param type $exact
     * @param type $escape
     * @return \Platform\Entity
     */
    final public function setListLookUpConditions($key, $value = NULL, $type = 'AND', $exact = FALSE, $escape = TRUE, $comparison = "LIKE")
    {

        if (empty($key)) {
            return $this;
        }
        if (!is_array($key)) {
            if (is_null($value)) { //some values could be '' so don't use empty here
                return $this;
            }
            $key = array($key => $value);
        }
        //print_R($key);
        $dataModel = $this->getPropertyModel();
        foreach ($key as $k => $v) {

            //For count queries, we will only add properties if their value is in having or where clause;
            if (array_key_exists($k, $dataModel)):
                $this->listLookUpConditionProperties[] = $k;
            endif;

            //The firs item adds the and prefix;
            $prefix = (count($this->listLookUpConditions) == 0 AND count($this->listLookUpConditions) == 0) ? '' : $type . "\t";
            if ($escape === TRUE) {
                $v = $this->database->escape($v);
                //$v = $this->quote( stripslashes($v) );
            }
            if (empty($v)) {
                // value appears not to have been set, assign the test to IS NULL 
                // IFNULL(xxx, '')
                $v = " IS NULL";
            } else {
                if (is_array($v)):
                    $_values = array_map(array($this->database, "quote"), $v);
                    $values = implode(',', $_values);
                    $v = " IN ($values)";
                else:
                    $v = (strtoupper($comparison) == "LIKE") ? " LIKE '%{$v}%'" : " {$comparison} '{$v}'";
                endif;
            }
            if ($exact && is_array($this->listLookUpConditions) && !empty($this->listLookUpConditions)):
                $conditions = implode("\t", $this->listLookUpConditions);
                $this->listLookUpConditions = array();
                $this->listLookUpConditions[] = "(" . $conditions . ")";
            endif;
            $this->listLookUpConditions[] = $prefix . $k . $v;
        }

        return $this;
    }

    /**
     * Returns the list select clause additional conditions
     *
     * @return string or null if no conditions
     */
    public function getListLookUpConditionsClause()
    {
        $query = null;
        if (is_array($this->listLookUpConditions) && !empty($this->listLookUpConditions)):
            $query .= "\nHAVING\t";
            $query .= implode("\t", $this->listLookUpConditions);
        endif;
        //Reset the listLookUp after the query has been generated, to avoid issues;
        //$this->resetListLookUpConditions();
        return $query;
    }

    /**
     * Resets the list lookUpconditions after each query;
     */
    public function resetListLookUpConditions()
    {
        $this->listLookUpConditions = array();
    }

    /**
     * Returns objects lists table with attributes list and values
     *
     * @param type $objectType
     * @param type $attributes
     * @return type $statement
     *
     */
    final public function getObjectsList($objectType, $properties = array(), $objectURI = NULL, $objectId = NULL)
    {

        if (empty($properties)):
            if (!empty($this->propertyModel))
                $properties = array_keys($this->propertyModel);
        endif;

        $query = static::getObjectQuery($properties, "?{$this->valueGroup}property_values", NULL, $objectType, $objectURI, $objectId);

        //echo($this->withConditions) ;
        $query .= "\nGROUP BY o.object_id";
        $query .= $this->getListLookUpConditionsClause();
        $query .= $this->getListOrderByStatement();
        $query .= $this->getLimitClause();

        $total = $this->getObjectsListCount($objectType, $properties, $objectURI, $objectId); //Count first
        $results = $this->database->prepare($query)->execute();
        //Could use SQL_CALC_FOUND here but just the same as just using a second query really;
        //$queries = $this->database->getQueryLog();
        $this->resetListLookUpConditions();
        $this->setListTotal($total);

        return $results;
    }

    /**
     * Gets the object List Count
     *
     * @param type $objectType
     * @param type $properties
     * @param type $objectURI
     * @param type $objectId
     * @return type
     */
    final public function getObjectsListCount($objectType, $properties = array(), $objectURI = NULL, $objectId = NULL)
    {
        if (empty($properties)):
            if (!empty($this->propertyModel))
                $properties = array_keys($this->propertyModel);
        endif;
        $query = $this->getObjectCountQuery($properties, "?{$this->valueGroup}property_values", $objectId, $objectType, $objectURI);
        //echo($this->withConditions) ;
        $query .= "\nGROUP BY o.object_id";
        $query .= $this->getListLookUpConditionsClause();
        $query .= $this->getListOrderByStatement();
        $cquery = "SELECT COUNT(total_objects) as count FROM ($query) AS total_entities";
        $results = $this->database->prepare($cquery)->execute();
        $count = 0;
        while ($row = $results->fetchAssoc()) {
            $count = $row['count'];
        }
        return $count;
    }

    final public function getObjectById($objectId, $properties = array())
    {
        if (empty($properties)):
            if (!empty($this->propertyModel))
                $properties = array_keys($this->propertyModel);
        endif;

        $query = static::getObjectQuery($properties, "?{$this->valueGroup}property_values");
        $query .= "\nWHERE o.object_id='{$objectId}' GROUP BY o.object_id";

        $results = $this->database->prepare($query)->execute();

        return $results;
    }

    /**
     * Return an Unique Object with or without attributes attributes
     *
     * @param type $objectURI
     * @param type $attributes
     */
    public function loadObjectByURI($objectURI, $properties = array(), $replaceThis = false)
    {

        if (empty($properties)):
            if (!empty($this->propertyModel))
                $properties = array_keys($this->propertyModel);
        endif;

        $query = static::getObjectQuery($properties, "?{$this->valueGroup}property_values");
        $query .= "\nWHERE o.object_uri='{$objectURI}' GROUP BY o.object_id";
        $rows = array();
        $key = sha1($query);
        //Can we limit the number of times we load by URI?
        //$results = $this->database->prepare($query)->execute();
        if (isset($this->registry[$key])):
            $rows = $this->registry[$key];
        else:
            $results = $this->database->prepare($query)->execute();
            $fetched = $results->fetchAll();
            $rows = reset($fetched);

            $this->registry[$key] = $rows;
        endif;

        //$n = 0;


        $object = (!$replaceThis) ? new Entity($this->database, $this->container) : $this;

        $object->definePropertyModel($this->propertyModel);

        foreach ((array)$rows as $property => $value):
            if (strtolower($property) == "object_type") {
                $object->setObjectType($value);
                continue;
            }
            if (strtolower($property) == "object_id") {
                $object->setObjectId($value);
                continue;
            }
            if (strtolower($property) == "object_uri") {
                $object->setObjectURI($value);
                continue;
            }
            $object->setPropertyValue($property, $value);
        endforeach;

       // $object->defineValueGroup($this->valueGroup);

        return $object;
    }

    /**
     * Return an Unique Object with or without attributes attributes
     *
     * @param type $objectId
     * @param type $attributes
     */
    public function loadObjectById($objectId, $properties = array())
    {

        $results = $this->getObjectById($objectId, $properties);
        //If success, store the object id in
        $n = 0;
        $object = new Entity($this->database, $this->container);
        $object->definePropertyModel($this->propertyModel);
        //$object->defineValueGroup($this->valueGroup);

        while ($row = $results->fetchAssoc()) {
            foreach ($row as $property => $value):
                if (strtolower($property) == "object_type") {
                    $object->setObjectType($value);
                    continue;
                }
                if (strtolower($property) == "object_id") {
                    $object->setObjectId($value);
                    continue;
                }
                if (strtolower($property) == "object_uri") {
                    $object->setObjectURI($value);
                    continue;
                }
                $object->setPropertyValue($property, $value);
            endforeach;
            $n++;
        }
        return $object;
    }

    /**
     * Builds the original portion of the Object Query without conditions
     *
     * @param type $properties
     * @param type $vtable
     * @param type $objectId
     * @param type $objectType
     * @param type $objectURI
     * @return string
     */
    final private static function getObjectQuery($properties, $vtable = '?property_values', $objectId = NULL, $objectType = NULL, $objectURI = NULL)
    {
        //Join Query
        $query = "SELECT o.object_id, o.object_uri, o.object_type, o.object_created_on, o.object_updated_on, o.object_status";

        if (!empty($properties)):
            //Loop through the attributes you need
            $i = 0;
            $count = \sizeof($properties);
            //echo $count;
            $query .= ",";
            foreach ($properties as $alias => $attribute):
                $alias = (is_int($alias)) ? $attribute : $alias;
                $query .= "\nMAX(IF(p.property_name = '{$attribute}', v.value_data, null)) AS {$alias}";
                if ($i + 1 < $count):
                    $query .= ",";
                    $i++;
                endif;
            endforeach;

            //The data Joins
            $query .= "\nFROM {$vtable} v"
                . "\nLEFT JOIN ?properties p ON p.property_id = v.property_id"
                . "\nLEFT JOIN ?objects o ON o.object_id=v.object_id";
        else:
            $query .= "\nFROM ?objects o";
        endif;

        static::$withConditions = false;
        if (!empty($objectId) || !empty($objectURI) || !empty($objectType)):
            $query .= "\nWHERE";
            if (!empty($objectType)):
                $query .= "\to.object_type='{$objectType}'";
                static::$withConditions = TRUE;
            endif;
            if (!empty($objectURI)):
                $query .= (static::$withConditions) ? "\t AND" : "";
                $query .= "\to.object_uri='{$objectURI}'";
                static::$withConditions = TRUE;
            endif;
            if (!empty($objectId)):
                $query .= (static::$withConditions) ? "\t AND \t" : "";
                $query .= "\to.object_id='{$objectId}'";
                static::$withConditions = TRUE;
            endif;
        endif;

        return $query;
    }

    /**
     * Get the final count
     *
     * @param type $properties
     * @param type $vtable
     * @param type $objectId
     * @param type $objectType
     * @param type $objectURI
     * @return type
     */
    final private function getObjectCountQuery($properties, $vtable = '?property_values', $objectId = NULL, $objectType = NULL, $objectURI = NULL)
    {

        //Join Query
        $query = "SELECT DISTINCT o.object_id as total_objects";
        $hasProperties = FALSE;
        if (!empty($properties)):
            //Loop through the attributes you need
            $i = 0;
            $count = \sizeof($properties);
            //echo $count;
            foreach ($properties as $alias => $attribute):
                //For count queries, there is no need to have added properties.
                //We will only add does we need in the having clause, which is executed after grouping..
                if (in_array($attribute, $this->listLookUpConditionProperties)):
                    if ($i + 1 < $count):
                        $query .= ",";
                        $i++;
                    endif;
                    $alias = (is_int($alias)) ? $attribute : $alias;
                    $query .= "\nMAX(IF(p.property_name = '{$attribute}', v.value_data, null)) AS {$alias}";
                    $hasProperties = TRUE;
                endif;
            endforeach;

            //The data Joins
            $query .= "\nFROM {$vtable} v";
            $query .= ($hasProperties) ? "\nLEFT JOIN ?properties p ON p.property_id = v.property_id" : NULL;
            $query .= "\nLEFT JOIN ?objects o ON o.object_id=v.object_id";
        else:
            $query .= "\nFROM ?objects o";
        endif;

        static::$withConditions = false;
        if (!empty($objectId) || !empty($objectURI) || !empty($objectType)):
            $query .= "\nWHERE";
            if (!empty($objectType)):
                $query .= "\to.object_type='{$objectType}'";
                static::$withConditions = TRUE;
            endif;
            if (!empty($objectURI)):
                $query .= (static::$withConditions) ? "\t AND" : "";
                $query .= "\to.object_uri='{$objectURI}'";
                static::$withConditions = TRUE;
            endif;
            if (!empty($objectId)):
                $query .= (static::$withConditions) ? "\t AND \t" : "";
                $query .= "\to.object_id='{$objectId}'";
                static::$withConditions = TRUE;
            endif;
        endif;

        return $query;
    }

    final public function bindPropertyData()
    {

    }

    /**
     * Saves an object to the EAV database
     *
     * @param type $objectURI
     * @param type $objectType
     * @return boolean
     */
    final public function removeObject($objectURI)
    {

        if (empty($objectURI)) return false; //@TODO should throw an exception

        $object = $this->database->select()->from("?objects")->where(array("object_uri" => $this->database->quote($objectURI)))->prepare()->execute()->fetchObject();

        if (empty($object->object_id)) return false; //@TODO should throw an exception
        //Get all value table names;
        $tables = $this->database->prepare("SHOW TABLES LIKE '%_property_values'")->execute();
        $from = array();

        while ($row = $tables->fetchAssoc()) {
            $from[] = array_shift(array_values($row));
        }
        //Trigger BeforeObject Removal;
        //Library\Event::trigger('beforeRemoveObject', $object->object_id, $object->object_uri, $object->object_type );

        //Start Deleting transaction
        //$this->database->startTransaction();
        //Delete the object from each value table;
        foreach ($from as $vtable):
            if (!$this->database->delete($vtable, array("object_id" => $this->database->quote($object->object_id)))) {
                static::setError($this->database->getError());
                return false;
            }
        endforeach;
        //then remove the object from the objects table
        if (!$this->database->delete('?objects', array("object_id" => $this->database->quote($object->object_id)))) {
            static::setError($this->database->getError());
            return false;
        }

        // $this->database->commitTransaction();

        //Trigger AfterObjectType Removal;
        // Library\Event::trigger('afterRemoveObject', $object->object_id, $object->object_uri, $object->object_type );

        unset($object);
        return true;

        //die;
    }

    final public function removeObjectProperty($objectURI)
    {
    }

    /**
     * Saves an object to the EAV database
     *
     * @param type $objectURI
     * @param type $objectType
     * @return boolean
     */
    final public function saveObject($objectURI = NULL, $objectType = NULL, $objectId = NULL, $forceNew = false )
    {

        $isNew = (empty($objectId) && empty($objectURI)) ? true : (($forceNew)? true : false );

        //if we are forcing a new object;

        //Get a randomstring for the objectURI
        $objectURI = empty($objectURI) ? getRandomString(10, false, true) : $objectURI;
        $objectType = empty($objectType) ? 'entity' : $objectType;
        //$objectId = empty($objectId) ?  (!empty($this->objectId) ? $this->objectId : null ) : $objectId;
        //Ensure we have all the properties
        if (empty($this->propertyModel) || empty($this->propertyData))
            return false; //We have nothing to save

        //Use a transaction;
        $this->database->startTransaction();
        $pquery = "INSERT IGNORE INTO ?properties (property_name, property_label, property_datatype, property_charsize, property_default, property_indexed) VALUES\n";
        $pqueryV = array();
        foreach ($this->propertyModel as $property => $definition) {
            $values = array($this->database->quote($property), $this->database->quote($definition[0]), $this->database->quote($definition[1])); //Name, Label, DataType
            $values[] = (isset($definition[2])) ? $this->database->quote($definition[2]) : $this->database->quote(""); //Charsize
            $values[] = (isset($definition[3])) ? $this->database->quote($definition[3]) : $this->database->quote(""); //Default
            $values[] = (isset($definition[4]) && $definition[4]) ? $this->database->quote(1) : $this->database->quote(0); //Indexed

            $pqueryV[] = " (" . implode(', ', $values) . " )";
        }
        $pquery .= implode(', ', $pqueryV);


        //update the properties
        $this->database->query($pquery);

        //If objectId is NULL then NEW Create new object
        if ($isNew):

            //Log::message("Object ID is {$objectId}");

            $timestamp = Time::stamp();

            $oquery = $this->database->insert("?objects", array("object_uri" => $this->database->quote($objectURI), "object_type" => $this->database->quote($objectType), "object_created_on" => $this->database->quote($timestamp)), FALSE, NULL, FALSE);
            $this->database->query($oquery);

            $this->setLastSavedObjectURI($objectURI);
        endif;



        //If property exists and value doesnt insert new value row
        //If property exists and value exists update value
        //if property does not exists, insert property and insert value
        $vtable = "?{$this->valueGroup}property_values";

        $iquery = "REPLACE INTO {$vtable} (property_id, object_id, value_data)";
        $iqueryV = array();
        foreach ($this->propertyData as $propertyName => $valueData):
            //@TODO validate the data?
            if (empty($valueData))
                continue; //There is no point in storing empty values;


//@TODO also check that value data has data for fields demarkated as allowempty=false;
            $iqueryV[] = "\nSELECT p.property_id, o.object_id, {$this->database->quote($valueData)}  FROM `?properties` AS p JOIN `?objects` AS o WHERE o.object_uri={$this->database->quote($objectURI)} AND p.property_name={$this->database->quote($propertyName)}";
        endforeach;
        $iquery .= implode("\nUNION ALL", $iqueryV);


        $this->database->query($iquery);

        //Update the object URI so the last update field is auto updated
        //$this->database->exec( "UPDATE ?objects SET objected_updated_on=CURRENT_TIMESTAMP" WHERE object_uri=" );

        if (!$this->database->commitTransaction()) {
            throw new Exception("Could not commit transaction");
            return false;
        }

        return true;
    }

    /**
     * Returns the current data model
     *
     * @return type
     */
    final public function getPropertyModel()
    {
        return $this->propertyModel;
    }

    /**
     * Returns the current data model values
     *
     * @return type
     */
    public function getPropertyData()
    {
        return $this->propertyData;
    }

    /**
     * Extends the parent data model
     * Allows the current object to use parent object properties
     *
     * @param type $dataModel array(property_name=>array("label"=>"","datatype"=>"","charsize"=>"" , "default"=>"", "index"=>FALSE, "allowempty"=>FALSE))
     *
     */
    final public function extendPropertyModel($dataModel = array(), $objectType = "object")
    {

        $this->propertyModel = array_merge($this->propertyModel, $dataModel);
        $this->setObjectType($objectType);

        return $this;
    }

    /**
     * Creates a completely new data model.
     * Any Properties not explicitly described for this object will be ignored
     *
     * @param type $dataModel
     */
    final public function definePropertyModel($dataModel = array(), $objectType = "object")
    {

        $this->propertyModel = $dataModel;
        $this->setObjectType($objectType);

        return $this;
    }

    /**
     * Defines a sub table for value data;
     *
     * @param type $valueGroup
     */
    final public function defineValueGroup($valueGroup = NULL)
    {
        $this->valueGroup = !empty($valueGroup) ? trim($valueGroup) . "_" : NULL;
        //you must have this proxy table created at setup
        //also object type must be the same as valuegroup
        if (!empty($valueGroup) && empty($this->objectType)) {
            return $this->setObjectType(trim($valueGroup));
        }

        return $this;
    }

    /**
     * Returns the value group
     *
     * @return string
     */
    final public function getValueGroup()
    {
        return $this->valueGroup;
    }

    /**
     * Pivot this entity into a sparse matrix
     * @return array associative array
     *
     */
    public function display()
    {
        //@TODO: Renders the display data, as per other models
        return;
    }

}