<?php

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Generates application components for us from a command line ala Rails.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Scripts\
 */



// check we are actually using cli
if (PHP_SAPI !== 'cli') die();

// type of generator to use passed
$type = @$argv[1];
// name of component to create, filtered for alphanumeric
$name = ucfirst(preg_replace("/[^a-zA-Z0-9\s]/", "", @$argv[2]));
// optional parameter
$parameter = ucfirst(preg_replace("/[^a-zA-Z0-9\s]/", "", @$argv[3]));

// base path to application
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__) . '/..');

include_once('helpers.php');

// determine type of the generator to use
if (!defined('BACKSTAGE')) {
    switch ($type) {
        case "presenter":
            if (empty($name)) {
                // undefined presenter name
                message("Usage: php script/generate.php presenter presenterName [modelName]", 'red');
            } else {
                newPresenter($name, $parameter);
            }
            break;
        case "model":
            if (empty($name)) {
                // undefined model name
                message("Usage: php script/generate.php model modelName [primaryKey]", 'red');
            } else {
                newModel($name, $parameter);
            }
            break;
        case "auth":
        case "authentication":
        case "security":
        case "login":
        case "users":
        case "acl":
        case "admin":
            if (empty($name)) {
                // undefined auth name
                message("Usage: php script/generate.php authentication presenterName", 'red');
            } else {
                newAuth($name);
            }
            break;
        case 'help':
        case '':
            message("Usage: php script/generate.php [presenter|model|authentication] fileName parameter", 'green');
            break;
        default:
            // fail, undetermined generator type called
            message("Couldn't find '{$type}' generator try 'help'", 'red');
    }
}



/********************* create new presenter *********************/



/**
 * Create a new presenter/default view pair.
 * @param string $name presenter e.g.: "hello"
 * @param string $model make connection to a model
 */
function newPresenter($name, $model=NULL) {

// lowercase
$lowercase = strtolower($name);

if (!empty($model)) {
    $model = ucfirst($model);
    $modelLowercase = strtolower($model);
}

$presenterCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * Description of {$name}.
 *
 * @package   Application\Presenters
 */
final class {$name}Presenter extends Fari_ApplicationPresenter {

    /********************* filters *********************/

    /** var filters to apply to these actions before they are called */
    //public \$beforeFilter = array(
    //    array('nameOfFilter' => array('nameOfAction'))
    //);

    /** var filters to apply after all processing has occured */
    //public \$afterFilter = array(
    //    array('nameOfFilter' => array('nameOfAction'))
    //);

    /**
     * Applied automatically before any action is called.
     * @example use it to authenticate users or setup locales
     */
    public function filterStartup() { }

    /********************* actions *********************/

    /** Responsible for presenting a collection back to the user. */
	public function actionIndex(\$p) {
        \$this->renderAction('index');
    }

    /** Responsible for showing a single specific object to the user. */
	public function actionShow() { }

    /** Responsible for providing the user with an empty form to create a new object. */
	public function actionNew() { }

    /** Receives the form submission from the new action and creates the new object. */
	public function actionCreate() { }

    /** Responsible for providing a form populated with a specific object to edit. */
	public function actionEdit() { }

    /** Receives the form submission from the edit action and updates the specific object. */
	public function actionUpdate() { }

    /** Deletes the specified object from the database. */
	public function actionDelete() { }

}
CODE;

$presenterAndModelCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * Description of {$name}.
 *
 * @package   Application\Presenters
 */
final class {$name}Presenter extends Fari_ApplicationPresenter {

    /** var {$model} Table connection */
    private \${$modelLowercase};

    /********************* filters *********************/

    /** var filters to apply to these actions before they are called */
    //public \$beforeFilter = array(
    //    array('nameOfFilter' => array('nameOfAction'))
    //);

    /** var filters to apply after all processing has occured */
    //public \$afterFilter = array(
    //    array('nameOfFilter' => array('nameOfAction'))
    //);

    /**
     * Applied automatically before any action is called.
     * @example use it to authenticate users or setup locales
     */
    public function filterStartup() {
        // setup table connection
        \$this->{$modelLowercase} = new {$model}();
    }

    /********************* actions *********************/

    /** Responsible for presenting a collection back to the user. */
	public function actionIndex(\$p) {
        dump(\$this->{$modelLowercase}->findAll());
    }

    /** Responsible for showing a single specific object to the user. */
	public function actionShow(\$id) {
        dump(\$this->{$modelLowercase}->findFirst()->where(\$id));
    }

    /** Responsible for providing the user with an empty form to create a new object. */
	public function actionNew() { }

    /** Receives the form submission from the new action and creates the new object. */
	public function actionCreate() {
        \$this->{$modelLowercase}->save(\$this->request->getPost());
        \$this->redirectTo('{$lowercase}/index');
    }

    /** Responsible for providing a form populated with a specific object to edit. */
	public function actionEdit() { }

    /** Receives the form submission from the edit action and updates the specific object. */
	public function actionUpdate(\$id) {
        \$this->{$modelLowercase}->update()->set(\$this->request->getPost())->where(\$id);
        \$this->redirectTo("{$lowercase}/show/{\$id}");
    }

    /** Deletes the specified object from the database. */
	public function actionDelete(\$id) {
        \$this->{$modelLowercase}->destroy()->where(\$id);
        \$this->redirectTo('{$lowercase}/index');
    }

}
CODE;

$layoutCode = <<<CODE
<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en" />
    <title>{$name}</title>

    <?php stylesheetLinkTag('style'); ?>
</head>
<body>
    <?php echo \$template; ?>
</body>
</html>
CODE;

$viewCode = <<<CODE
<?php if (!defined('FARI')) die(); ?>
<pre>This is a default view template in 'application/views/{$name}/index.phtml'</pre>
CODE;

    $presenterPath = 'application/presenters';
    $viewsPath = 'application/views';

    // is dir writable?
    if (!is_writable(BASEPATH . "/{$presenterPath}")) {
        echo message("Cannot write into {$presenterPath} directory!", 'red');
    } else {
        // check path to presenters exists, dir-wise
        $path = '';
        foreach (explode('/', $presenterPath) as $dir) {
            $path .= $dir . '/';
            createDirectory($path);
        }

        // does the presenter file exist?
        if (!empty($model)) {
            createFile("{$presenterPath}/{$name}Presenter.php", $presenterAndModelCode);
            newModel($model, 'id');
        } else {
            createFile("{$presenterPath}/{$name}Presenter.php", $presenterCode);
        }

        // check/create views directory
        createDirectory($viewsPath . '/');

        // presenter layout
        createFile("{$viewsPath}/@{$lowercase}.phtml", $layoutCode);

        // create appropriate presenter-named views dir
        createDirectory("{$viewsPath}/{$name}/");

        // default index file
        createFile("{$viewsPath}/{$name}/index.phtml", $viewCode);
    }
}



/********************* create new model *********************/



/**
 * Create a new model based on Table class.
 * @param string $name e.g.: "hello"
 * @param string $primaryKey e.g.: "id"
 */
function newModel($name, $primaryKey) {

// determine the prefix of our model
$prefix = current(preg_split('/(?<=\\w)(?=[A-Z])/', $name));

// lowercase
$lowercase = strtolower($name);
$primaryKey = (empty($primaryKey)) ? 'id' : strtolower($primaryKey);

$modelCode = <<<CODE
<?php

/**
 * Description of {$name}.
 *
 * @package Application\Models\\{$prefix}
 */
class {$name} extends Table {

    /** @var string table name */
    public \$tableName = '{$lowercase}';

    /** @var string primary key */
    public \$primaryKey = '{$primaryKey}';

    /********************* relationships *********************/

    /** @example: \$this->findAddresses()->where(1); // will associate with table 'addresses' */

    /** @var array a "one-to-one association" with another table(s) through primary keys */
    //public \$hasOne;

    /** @var array a "one-to-many association" with another table(s), e.g. a blog post has many comments */
    //public \$hasMany;

    /********************* validation *********************/

    /** @var array validates the presence of column data */
    //public \$validatesPresenceOf = array('id');

    /** @var array validates the length of columns */
    //public \$validatesLengthOf = array(array('password' => 5));

    /** @var array validates uniqueness of columns */
    //public \$validatesUniquenessOf = array('username');

    /** @var array validates regex format of a column */
    //public \$validatesFormatOf = array(array('zip' => '/^([0-9]{5})(-[0-9]{4})?$/i'));

}
CODE;

    $modelsPath = 'application/models';

    // is dir writable?
    if (!is_writable(BASEPATH . "/{$modelsPath}")) {
        echo message("Cannot write into {$modelsPath} directory!", 'red');
    } else {
        // check path to models exists, dir-wise
        $path = '';
        foreach (explode('/', $modelsPath) as $dir) {
            $path .= $dir . '/';
            createDirectory($path);
        }

        // models are in prefix subdirectory
        createDirectory("{$modelsPath}/{$prefix}/");

        // does the model file exist?
        createFile("{$modelsPath}/{$prefix}/{$name}.php", $modelCode);
    }
}



/********************* create new authentication presenter *********************/



/**
 * Create a new authentication presenter-model-view triplet.
 * @param string $name e.g.: "hello"
 */
function newAuth($name) {

// lowercase for variables
$lowercase = strtolower($name);

// determine the prefix of our model
$prefix = current(preg_split('/(?<=\\w)(?=[A-Z])/', $name));

$presenterCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * User login and signoff.
 *
 * @package   Application\Presenters
 */
final class {$name}Presenter extends Fari_ApplicationPresenter {

    /**#@+ where to redirect on successful login? */
    const ADMIN = 'admin';
    /**#@-*/

	public function actionIndex(\$p) {
        \$this->actionLogin();
    }

	/**
	 * User sign-in/login
	 */
	public function actionLogin() {
        // authenticate user if form data POSTed
        if (\$this->request->getPost('username')) {
            \$username = Fari_Decode::accents(\$this->request->getPost('username'));
            \$password = Fari_Decode::accents(\$this->request->getPost('password'));

            try {
                \$user = new {$name}Auth(\$username, \$password, \$this->request->getPost('token'));

                \$this->redirectTo('/' . self::ADMIN);
            } catch ({$prefix}UserNotAuthenticatedException \$e) {
                \$this->flashFail = "Sorry, your username or password wasn't recognized";
            }
        }

		// create token & display login form
		\$this->bag->token = Fari_FormToken::create();
		\$this->renderAction('login');
	}

	/**
	 * Destroy user session.
	 */
    public function actionLogout() {
        try {
            \$user = new {$name}User();
            \$user->signOut();
            \$this->flashSuccess = "You have been logged out";
        } catch (AuthUserNotAuthenticatedException \$e) {
            \$this->flashSuccess = 'You are already logged out';
        }

        // create token & display login form
        \$this->bag->token = Fari_FormToken::create();
		\$this->renderAction('login');
	}

}
CODE;

$authModelCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * User authentication.
 *
 * @package   Application\Models\\$prefix
 */
class {$name}Auth {

    /**
     * Authenticate credentials using Fari_AuthenticatorSimple
     * @param string \$username
     * @param string \$password
     * @param string \$token (optional)
     * @return TestUser on success or TestUserNotAuthenticatedException thrown
     */
    function __construct(\$username, \$password, \$token=NULL) {
        \$authenticator = new Fari_AuthenticatorSimple('{$lowercase}');
        // authenticator authenticates...
        if (\$authenticator->authenticate(\$username, \$password, \$token) != TRUE) {
            throw new {$prefix}UserNotAuthenticatedException();
        } else {
            // return the sweet beans
            return new {$prefix}User();
        }
    }

}
CODE;

$userModelCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * Authenticated user.
 *
 * @example   This object will throw an exception if user is not authenticated, use in admin
 * @package   Application\Models\{$prefix}
 */
class {$prefix}User extends Fari_AuthenticatorSimple {

    private \$table;

    /**
     * Check that user is authenticated.
     * @throws {$prefix}UserNotAuthenticatedException
     */
    public function __construct() {
        // construct the db table
        \$this->table = new Table('{$lowercase}');
        // call the authenticator
        parent::__construct(\$this->table);

        // no entry, we are not logged in, fail the constructor
        if (!\$this->isAuthenticated()) throw new {$prefix}UserNotAuthenticatedException();
    }

    /**
     * Fetch row from '{$lowercase}' table.
     * @return array
     */
    public function getUser() {
        return \$this->table->findFirst()->where(array('username' => \$this->getCredentials()));
    }

}

CODE;

$exceptionCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * User has not been authenticated.
 *
 * @package   Application\Models\\$prefix
 */
class {$prefix}UserNotAuthenticatedException extends Exception {}

CODE;

$layoutCode = <<<CODE
<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en" />
    <title>{$name}</title>

    <?php stylesheetLinkTag('style'); ?>
</head>
<body>
    <?php echo \$template; ?>
</body>
</html>
CODE;

$viewCode = <<<CODE
<?php if (!defined('FARI')) die(); ?>
<?php foreach (\flash() as \$message): ?>
    <pre class="<?php echo \$message['key']; ?>"><?php echo \$message['text']; ?></pre>
<?php endforeach; ?>

<form class="form" method="POST" action="<?php url('/{$lowercase}/login/'); ?>">
    <div class="field">
        <label>Username</label>
        <input name="username" type="text" />
    </div>
    <div class="field">
        <label>Password</label>
        <input name="password" type="password" />
    </div>
    <input type="hidden" name="token" value="<?php echo \$token; ?>" />
    <input type="submit" class="button" value="Sign in" />
</form>
CODE;

    $presenterPath = 'application/presenters';
    $modelsPath = 'application/models';
    $viewsPath = 'application/views';

    // is dir writable?
    if (!is_writable(BASEPATH . "/{$presenterPath}")) {
        echo message("Cannot write into {$presenterPath} directory!", 'red');
    } else {
        // check path to presenters exists, dir-wise
        $path = '';
        foreach (explode('/', $presenterPath) as $dir) {
            $path .= $dir . '/';
            createDirectory($path);
        }

        // does the presenter file exist?
        createFile("{$presenterPath}/{$name}Presenter.php", $presenterCode);

        // check/create views directory
        createDirectory($viewsPath . '/');

        // presenter layout
        createFile("{$viewsPath}/@{$lowercase}.phtml", $layoutCode);

        // create appropriate presenter-named views dir
        createDirectory("{$viewsPath}/{$name}/");

        // default index file
        createFile("{$viewsPath}/{$name}/login.phtml", $viewCode);

        // models are in prefix subdirectory
        createDirectory("{$modelsPath}/{$prefix}/");

        // models & exceptions
        createFile("{$modelsPath}/{$prefix}/{$prefix}Auth.php", $authModelCode);
        createFile("{$modelsPath}/{$prefix}/{$prefix}User.php", $userModelCode);
        createFile("{$modelsPath}/{$prefix}/{$prefix}UserNotAuthenticatedException.php", $exceptionCode);
    }
}



/********************* file, directory functions *********************/



/**
 * Create a new directory/check it exists.
 * @param string $path to the directory
 */
function createDirectory($path) {
    // does the dir exist? I can haz file? Dir! File? Cheese!
    if (file_exists(BASEPATH . "/{$path}")) {
        message("      exists  {$path}", 'gray');
    } else {
        // aka STFU, should have been caught above
        @mkdir(BASEPATH . "/{$path}");
        message("      create  {$path}");
    }
}

/**
 * Create a new file/check it exists.
 * @param string $path to the file, basepath will be prepended
 * @param string $content to save
 */
function createFile($path, $content) {
    if (file_exists(BASEPATH . "/{$path}")) {
        message("      exists  {$path}", 'gray');
    } else {
        $file = fopen(BASEPATH . "/{$path}", 'w');
        fwrite($file, $content);
        fclose($file);
        message("      create  {$path}");
    }
}