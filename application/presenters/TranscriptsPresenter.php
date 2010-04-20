<?php if (!defined('FARI')) die();

/**
 * Clubhouse, a 37Signals' Campfire port
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Clubhouse
 */



/**
 * List transcripts and view them.
 * Access: restricted to signed-in users
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class TranscriptsPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;

    private $pagination = 8;
	
	public function startup() {
        // is user authenticated?
        $this->user = new User();
        if (!$this->user->isAuthenticated()) {
            $this->response->redirect('/login/');
        }
	}



    /********************* view display transcripts *********************/



    /**
	 * Display transcripts listing
	 */
	public function actionIndex($page) {
        // set the default page number
        if (!isset($page)) $page = 1;

        // room tabs
        $this->bag->tabs = $this->user->inRooms();

        try {
            // setup new transcripts object
            $transcripts = new Transcripts($this->user->getPermissionsDbString());
        } catch (EmptyException $e) {
            $this->render('empty');
        }

        // are we fetching a page number in a proper range?
        if (!Fari_Filter::isInt($page, array(1, ceil($transcripts->count / $this->pagination)))) {
            $this->render('error404/error404');
        }
        
        // fetch transcript users, files and highlighted messages
        $this->render('listing', array(&$transcripts, $page));
	}

    public function renderListing($transcripts, $page) {
        // cut the whole array of transcripts with the items we need
        $max = min($page + $this->pagination, ceil($transcripts->count / $this->pagination));
        $min = max(1, $page - $this->pagination);
        
        $paginator = array();
		// traverse and build paginator
		for ($min; $min <= $max; $min++) {
			if ($page == $min) $paginator[] = array('number' => $min, 'class' => 'current');
			else $paginator[] = array('number' => $min, 'class' => 'page');
		}
        $this->bag->paginator = $paginator;

        // cut out the subarray we need from the whole transcripts listing and build transcript keys string as well
        $offset = ($page - 1) * $this->pagination;
        $result = array(); $ids = array();
        $max = $offset + $this->pagination - 1;
        for ($offset; $offset <= $max; $offset++) {
            if (!isset($transcripts->all[$offset])) break;
            $result[] = $transcripts->all[$offset];
        }
        
        // build the page transcripts
        $transcripts->buildPage($page, &$result);
        
        // add transcript users
        $result;
        foreach ($result as &$values) {
            // room users
            $values['users'] = implode(', ', $transcripts->users[$values['key']]);
            $values['starred'] = $transcripts->starred[$values['key']];
            $values['files'] = $transcripts->files[$values['key']];
        }
        
        // transcripts
        $this->bag->transcripts = $result;
    }



    /********************* view read transcript *********************/



    /**
     * Read a transcript
     */
    public function actionRead($roomId, $year, $month, $day) {
        // can we actually view these?
        if ($this->user->canEnter($roomId)) {
            $date = $year . '-' . $month . '-' . $day;

            $this->bag->userId = $this->user->getId();

            try {
                // try to instantiate the transcript...
                $transcript = new Transcript($date, $roomId);
            } catch (NotFoundException $e) {
                $this->render('invalid');
            }

            // render it
            $this->render('read', array(&$transcript));
        } else {
            $this->render('error404/error404');
        }
    }

    public function renderRead($transcript) {
        $this->bag->transcript = $transcript->details;
        $this->bag->users = $transcript->users;
        $this->bag->messages = $transcript->messages;
        $this->bag->files = $transcript->files;

        //$this->bag->set($transcript->values);

        $this->bag->previousTranscript = $transcript->previous;
        $this->bag->nextTranscript = $transcript->next;
    }



    /********************* delete a transcript *********************/



    /**
     * Delete a transcript
     */
    public function actionDelete($roomId, $year, $month, $day) {
        // are we admin?
        if ($this->user->isAdmin()) {
            // form date
            $date = $year . '-' . $month . '-' . $day;

            try {
                // try to instantiate the transcript...
                $transcript = new Transcript($date, $roomId);
            } catch (NotFoundException $e) {
                $this->render('invalid');
            }

            // delete it
            $transcript->delete($date, $this->user->getShortName());

            // redirect back
            $this->response->redirect('/transcripts/');
        } else {
            $this->render('error404/error404');
        }
    }

}



class NotFoundException extends Exception {}
class EmptyException extends Exception {}