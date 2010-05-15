#!/usr/bin/env php
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

// base path to application
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__));

// determine type of the generator to use
switch ($type) {
    case "presenter":
        if (empty($name)) {
            // undefined presenter name
            message("Usage: php script/generate.php presenter presenterName", 'red');
        } else {
            newPresenter($name);
        }
        break;
    case "model":
        if (empty($name)) {
            // undefined model name
            message("Usage: php script/generate.php model modelName", 'red');
        } else {
            newModel($name);
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
    case '-help':
    case 'help':
    case '':
        message("Usage: php script/generate.php [presenter|model|authentication] fileName", 'green');
        break;
    default:
        // fail, undetermined generator type called
        message("Couldn't find '{$type}' generator try 'help'", 'red');
}



/********************* create new presenter *********************/



/**
 * Create a new presenter/default view pair.
 * @param string $name e.g.: "hello"
 */
function newPresenter($name) {
$presenterCode = <<<CODE
<?php if (!defined('FARI')) die();

/**
 * Description of {$name}.
 *
 * @package   Application\Presenters
 */
class {$name}Presenter extends Fari_ApplicationPresenter {

    /**
     * Applied automatically before any action is called.
     * @example use it to authenticate users or setup locales
     */
    public function filterStartup() { }

    /**
     * Default action.
     */
	public function actionIndex(\$p) {
        \$this->render('index');
    }

}
CODE;

$viewCode = <<<CODE
<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en" />
    <title>{$name}</title>

    <link rel="stylesheet" type="text/css" media="screen" href="<?php url('/public/css/style.css'); ?>" />
</head>
<body>
    <pre>This is a default view template</pre>
</body>
</html>
CODE;

    $presenterPath = 'application/presenters';
    $viewsPath = 'application/views';

    // is dir writable?
    if (!is_writable(BASEPATH . "/../{$presenterPath}")) {
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

        // create appropriate presenter-named views dir
        createDirectory("{$viewsPath}/{$name}/");

        // default index file
        createFile("{$viewsPath}/{$name}/index.tpl.php", $viewCode);
    }
}



/********************* create new model *********************/



/**
 * Create a new model based on Table class.
 * @param string $name e.g.: "hello"
 */
function newModel($name) {

// determine the prefix of our model
$prefix = current(preg_split('/(?<=\\w)(?=[A-Z])/', $name));

// lowercase
$lowercase = strtolower($name);

$modelCode = <<<CODE
<?php

/**
 * Description of {$name}.
 *
 * @package Application\Models\\{$prefix}
 */
class {$name} extends Table {

    /** @var string table name */
    public \$table = '{$lowercase}';

    /** @var array validates the presence of column data */
    public \$validatesPresenceOf = array('id');

    /** @var array validates the length of columns */
    public \$validatesLengthOf = array(array('password' => 5));

    /** @var array validates uniqueness of columns */
    public \$validatesUniquenessOf = array('username');

    /** @var array validates regex format of a column */
    public \$validatesFormatOf = array(array('zip' => '/^([0-9]{5})(-[0-9]{4})?$/i'));

}
CODE;

    $modelsPath = 'application/models';

    // is dir writable?
    if (!is_writable(BASEPATH . "/../{$modelsPath}")) {
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
class {$name}Presenter extends Fari_ApplicationPresenter {

    /**#@+ where to redirect on successful login? */
    const ADMIN = 'admin';
    /**#@-*/

    /** @var authenticated user */
    private \$user;

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
                \$this->user = new {$name}Auth(\$username, \$password, \$this->request->getPost('token'));

                \$this->response->redirect('/' . self::ADMIN);
            } catch ({$prefix}UserNotAuthenticatedException \$e) {
                Fari_Message::fail("Sorry, your username or password wasn't recognized");
            }
        }

        \$this->bag->messages = Fari_Message::get();

		// create token & display login form
		\$this->bag->token = Fari_FormToken::create();
		\$this->render('login');
	}

	/**
	 * Destroy user session.
	 */
    public function actionLogout() {
        // do we have an instance?
        if (\$this->user instanceof {$prefix}User) {
            Fari_Message::success('You have been logged out');
            \$this->user->signOut();
        } else {
            Fari_Message::success('You are already logged out');
        }

        \$this->bag->messages = Fari_Message::get();

        // create token & display login form
        \$this->bag->token = Fari_FormToken::create();
		\$this->render('login');
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
        \$authenticator = new Fari_AuthenticatorSimple();
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
 * @package   Application\Models\\$prefix
 */
class {$prefix}User extends Fari_AuthenticatorSimple {

        public function __construct() {
            parent::__construct();

            // no entry, we are not logged in, fail the constructor
            if (!\$this->isAuthenticated()) throw new {$prefix}UserNotAuthenticatedException();
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

$viewCode = <<<CODE
<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en" />
    <title>{$name} Login</title>

    <link rel="stylesheet" type="text/css" media="screen" href="<?php url('/public/css/style.css'); ?>" />
</head>
<body>
    <?php if (isset(\$messages)) foreach(\$messages as \$message): ?>
        <pre class="<?php echo \$message['status']; ?>"><?php echo \$message['message']; ?></pre>
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
</body>
</html>
CODE;

    $presenterPath = 'application/presenters';
    $modelsPath = 'application/models';
    $viewsPath = 'application/views';

    // is dir writable?
    if (!is_writable(BASEPATH . "/../{$presenterPath}")) {
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

        // create appropriate presenter-named views dir
        createDirectory("{$viewsPath}/{$name}/");

        // default index file
        createFile("{$viewsPath}/{$name}/login.tpl.php", $viewCode);

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
    if (file_exists(BASEPATH . "/../{$path}")) {
        message("      exists  {$path}", 'gray');
    } else {
        // aka STFU, should have been caught above
        @mkdir(BASEPATH . "/../{$path}");
        message("      create  {$path}");
    }
}

/**
 * Create a new file/check it exists.
 * @param string $path to the file, basepath will be prepended
 * @param string $content to save
 */
function createFile($path, $content) {
    if (file_exists(BASEPATH . "/../{$path}")) {
        message("      exists  {$path}", 'gray');
    } else {
        $file = fopen(BASEPATH . "/../{$path}", 'w');
        fwrite($file, $content);
        fclose($file);
        message("      create  {$path}");
    }
}



/********************* helpers *********************/



/**
 * Display a message in the terminal.
 * @param string $string to display
 * @param string $color to use
 */
function message($string, $color='black') {
    // color switcher
    switch ($color) {
        case "magenta":
            echo "[1;36;1m{$string}[0m\n";
            break;
        case "violet":
            echo "[1;35;1m{$string}[0m\n";
            break;
        case "blue":
            echo "[1;34;1m{$string}[0m\n";
            break;
        case "yellow":
            echo "[1;33;1m{$string}[0m\n";
            break;
        case "green":
            echo "[1;32;1m{$string}[0m\n";
            break;
        case "red":
            echo "[1;31;1m{$string}[0m\n";
            break;
        case "gray":
            echo "[1;30;1m{$string}[0m\n";
            break;
        case "black":
        default:
            echo "[1;29;1m{$string}[0m\n";
    }
}