<?php

namespace Budkit\Authentication;

use Budkit\Datastore\Database;
use Budkit\Datastore\Model\Entity;
use Budkit\Dependency\Container;
use Budkit\Event\Event;
use Budkit\Session\Store as Session;

class User extends Entity implements Authenticatable
{

    protected $name = "users";

    protected $encryptor;

    protected $config;

    protected $database;

    protected $authenticated = false;

    protected $authority;

    protected $application;


    public function __construct(Container $application, Database $database, Session $session)
    {

        $this->encryptor = $application->encrypt;
        $this->config = $application->config;
        $this->database = $database;
        $this->session = $session;
        $this->application = $application;

        parent::__construct($database, $application);

        //"label"=>"","datatype"=>"","charsize"=>"" , "default"=>"", "index"=>TRUE, "allowempty"=>FALSE
        $this->extendPropertyModel(
            array(
                "user_first_name" => array("First Name", "mediumtext", 50),
                "user_middle_name" => array("Middle Name", "mediumtext", 50),
                "user_last_name" => array("Last Name", "mediumtext", 50),
                "user_name_id" => array("Username", "mediumtext", 50),
                "user_password" => array("Password", "varchar", 1000),
                "user_api_key" => array("API Key", "varchar", 100),
                "user_email" => array("Email", "varchar", 100),
                "user_gender" => array("Gender", "varchar", 10), //Male or Female
                "user_dob_day" => array("Day of Birth", "varchar", 10),
                "user_dob_month" => array("Month of Birth", "varchar", 10),
                "user_dob_year" => array("Year of Birth", "varchar", 10),
                "user_timezone" => array("Timezone", "varchar", 10),
                "user_locale" => array("Locale", "varchar", 10),
                "user_verification" => array("Verification Code", "varchar", 50),
                "user_biography" => array("Biography", "varchar", 2000),
                "user_photo" => array("Profile photo", "mediumtext", 10, 'placeholder'),
                "user_cover_photo" => array("Cover photo", "varchar", 100),
                "user_headline" => array("Headline", "varchar", 100),
                "user_twitter_uid" => array("Twitter Account Id", "mediumtext", 50),
                "user_facebook_uid" => array("Facebook Account Id", "mediumtext", 50),
                "user_twitter_token" => array("Twitter AccessToken", "varchar", 2000),
                "user_facebook_token" => array("Facebook AccessToken", "varchar", 2000),
                "user_google_uid" => array("Google Account Id", "mediumtext", 50),
                "user_google_token" => array("Google AccessToken", "varchar", 2000),
                "user_dropbox_uid" => array("Dropbox Account Id", "mediumtext", 50),
                "user_dropbox_token" => array("Dropbox AccessToken", "varchar", 2000),
                "user_website" => array("Website", "varchar", 100),
                "user_verified" => array("User Verified", "mediumtext", 20)
            ), "user"
        );
        //$this->definePropertyModel( $dataModel ); use this to set a new data models
        $this->defineValueGroup("user"); //Tell the system we are using a proxy table
    }

    public function createUserWithValidatedData(array $data)
    {


        //2. Prevalidate passwords and other stuff;
        $username = $data["user_first_name"];
        $usernameid = $data["user_name_id"];
        $userpass = $data["user_password"];
        $userpass2 = $data["user_password_2"];
        $useremail = $data["user_email"];
        //3. Encrypt validated password if new users!
        //4. If not new user, check user has update permission on this user
        //5. MailOut

        if (empty($userpass) || empty($username) || empty($usernameid) || empty($useremail)) {
            //Display a message telling them what can't be empty
            $this->application->response->addAlert(t('Please provide us with at least your first name, a unique alphanumeric username, e-mail address and password'), "error");
            return false;
        }

        //3. Encrypt validated password if new users!
        //4. If not new user, check user has update permission on this user
        //5. MailOut

        if (empty($userpass) || empty($username) || empty($usernameid) || empty($useremail)) {
            //Display a message telling them what can't be empty
            $this->application->response->addAlert(t('Please provide at least a Name, Username, E-mail and Password'), "error");
            return false;
        }

        //Validate the passwords
        if ($userpass <> $userpass2) {
            $this->application->response->addAlert(t('The user passwords do not match'), "error");
            return false;
        }

        //6. Store the user
        if (!$this->store($data, true)):
            $this->application->response->addAlert("A user account could not be created", "error");
            return false;
        endif;

        //Account successfully created. Redirect to sign in page;
        $this->application->response->addAlert(t('An account was successfully created.'), "info");

        //@TODO attach post user sign up event;


        //@TODO if email verification is required;
        $user = $this->loadObjectByURI($usernameid, [], true);

        //When finished create an event;
        $onSignUp = new Event('Member.onSignUp', $this, $user);
        $this->application->observer->trigger($onSignUp); //Parse the Node;


        return $user;

    }


    /**
     *
     * Generates and stores a new verification code to the user object
     *
     * @return bool
     * @throws \Whoops\Example\Exception
     */
    public function reGenerateVerificationCode()
    {

        if ($this->getObjectId() == null) return false;

        //Create a user verifcation code;
        $this->setPropertyValue("user_verification", getRandomString(30, false, true));

        if (!$this->saveObject($this->getPropertyValue("user_name_id"), "user", $this->getObjectId(), false)) {
            //There is a problem!
            return false;
        }

        return true;
    }


    /**
     * Returns the current Session user
     *
     * @param array $withVars
     * @param bool $overwrite overwrites loaded params into current object
     * @return $this|Entity
     */
    public function getCurrentUser(array $withVars = [], $overwrite = true)
    {

//      //@TODO Rework the userid, use case, if user id is not provided or is null
        //Get the authenticated user
        //Also load some user data from the user database table, some basic info
        $this->authenticated = false;

        //Authenticate
        $authenticate = $this->session->get("handler", "auth");

        if (is_a($authenticate, Authenticate::class)) {

            if ($authenticate->authenticated) {

                $this->authenticated = true;

                //Does this actually do anything?
                $this->authority = $this->session->getAuthority();

                return $this->loadObjectByURI($authenticate->get("user_name_id"), $withVars , $overwrite);

            }
        }

        //Gets an instance of the session user;
        return $this;
    }

    /**
     * Returns the full name of the loaded Profile
     *
     * @param type $first The default First Name
     * @param type $middle The default Middle Name
     * @param type $last The default Last Name
     * @return type
     */
    public function getFullName($first = NULL, $middle = NULL, $last = NULL)
    {

        $user_first_name = $this->getPropertyValue("user_first_name");
        $user_middle_name = $this->getPropertyValue("user_middle_name");
        $user_last_name = $this->getPropertyValue("user_last_name");
        $user_full_name = implode(' ', array(empty($user_first_name) ? $first : $user_first_name, empty($user_middle_name) ? $middle : $user_middle_name, empty($user_last_name) ? $last : $user_last_name));

        if (!empty($user_full_name)) {
            return $user_full_name;
        }
    }

    /**
     * Updates the user profile data
     *
     * @param type $username
     * @param type $data
     */
    public function update($usernameId, $data = array())
    {

        //print_R($data);

        if (empty($usernameId))
            return false;

        //These properties have specific ways in which they are updated
        $protected = ['user_password','user_verification','user_verified','user_api_key','user_name_id'];

        //Load the username;
        $profile = $this->loadObjectByURI($usernameId, array_keys($this->getPropertyModel()));
        $this->setObjectId($profile->getObjectId());
        $this->setObjectURI($profile->getObjectURI());

        $profileData = $profile->getPropertyData();

        //update the user profile data;
        $updated = array_merge($profileData, $data);

        foreach ($updated as $property => $value):
            if(!in_array($property, $protected)) {
                $this->setPropertyValue($property, $value);
            }
        endforeach;
        //$data = $this->getPropertyData();
        $this->defineValueGroup("user");
        //die;
        if (!$this->saveObject($this->getPropertyValue("user_name_id"), "user", $this->getObjectId())) {
            //Null because the system can autogenerate an ID for this attachment
            throw new \Exception( t("Could not save the profile data") );
            return false;
        }

        return true;
    }

    /**
     * Searches the database for users
     *
     * @param type $query
     * @param type $results
     */
    public function search($query, &$results = array())
    {

        if (!empty($query)):
            $words = explode(' ', $query);
            foreach ($words as $word) {
                $_results =
                    $this->setListLookUpConditions("user_first_name", $word, 'OR')
                        ->setListLookUpConditions("user_last_name", $word, 'OR')
                        ->setListLookUpConditions("user_middle_name", $word, 'OR')
                        ->setListLookUpConditions("user_name_id", $word, 'OR');
            }

            $_results = $this->getObjectsList("user");
            $rows = $_results->fetchAll();

            //Include the members section
            $members = array(
                "filterid" => "users",
                "title" => "People",
                "results" => array()
            );
            //Loop through fetched attachments;
            //@TODO might be a better way of doing this, but just trying
            foreach ($rows as $member) {
                $photo = empty($member['user_photo']) ? "" : "/system/object/{$member['user_photo']}/resize/170/170";
                $members["results"][] = array(
                    "icon" => $photo, //optional
                    "link" => "/member:{$member['user_name_id']}/profile/timeline",
                    "title" => $this->getFullName($member['user_first_name'], $member['user_middle_name'], $member['user_last_name']), //required
                    "description" => "", //required
                    "type" => $member['object_type'],
                    "user_name_id" => $member['user_name_id']
                );
            }

            //Add the members section to the result array, only if they have items;
            if (!empty($members["results"]))
                $results[] = $members;

        endif;

        return true;
    }

    /**
     * Store the user data in the database
     *
     * @param array $data
     * @return boolean
     * @throws \Platform\Exception
     */
    public function store($data, $isNew = false)
    {

        $encrypt = $this->encryptor;

        $authority = $this->config->get("setup.site.default-authority", NULL);

        $data['user_password'] = $encrypt->hash($data['user_password']);

        if(!isset($data['user_photo'])|| empty($data['user_photo'])){
            $data['user_photo'] = 'placeholder';
        }

        foreach ($data as $property => $value):
            $this->setPropertyValue($property, $value);
        endforeach;

        if ($isNew) {
            $this->setPropertyValue("user_verification", getRandomString(30, false, true));
        }

        if (!$this->saveObject($this->getPropertyValue("user_name_id"), "user", null, $isNew)) {
            //There is a problem!
            return false;
        }
   
        //Default Permission Group?
        if (!empty($authority)) {
            $query = "INSERT INTO ?objects_authority( authority_id, object_id ) SELECT {$this->database->quote((int) $authority)}, object_id FROM ?objects WHERE object_uri={$this->database->quote($this->getPropertyValue("user_name_id"))}";
            $this->database->exec($query);
        }


        return true;
    }

    /**
     * Method to determine if the user is authenticated;
     *
     * @return type
     */
    public function isAuthenticated()
    {
        return (bool)$this->authenticated;
    }


    /**
     * Returns a user datastore row
     * @todo User EAV model load
     * @return void
     */
    protected function load($objectId)
    {

    }

    /**
     * Deletes a user record from the datastore
     * @todo User delete
     * @return void
     */
    public function delete()
    {

    }

    /**
     * Validates user data before store
     * @todo User data validate
     * @return void
     */
    public function validate()
    {

    }


    public function getSession()
    {
        return $this->session;
    }

}