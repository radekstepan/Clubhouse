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
 * File upload and fetch.
 * Access: restricted to signed-in users
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class FilePresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
    private $file;

    /**
     * Applied automatically before any action is called.
     */
	public function filterStartup() {
        // is user authenticated?
        try {
            $this->user = new User();
        } catch (UserNotAuthenticatedException $e) {
            $this->response->redirect('/login/');
        }
	}

	public function actionIndex($p) { }



    /********************* action upload file *********************/



    /**
	 * File upload
     * FIXME anyone can upload to another room!
	 */
	public function actionUpload() {
        $roomId = $this->request->getPost('roomId');
        if (Fari_Filter::isInt($roomId)) {
            
            $file = &$this->request->getFile();
            // save the file and get its code
            $this->file = new Upload($file, $roomId);

            $this->render('upload', $roomId);
        }
	}

    public function renderUpload($roomId) {
        // message about it
        $time = mktime();
        $message = new MessageSpeak($roomId, $time);

        $text = '<a class="file ' . $this->file->type . '"></a><a class="blue" href="'
                . url('file/get/' . $this->file->code, FALSE, TRUE) . '"/>' . $this->file->name . '</a>';
        // live preview image thumbnail
        if ($this->file->thumbnail === TRUE) {
            $text .= '<div class="image"><img src="'
                    . url('file/thumb/' . $this->file->code, FALSE, TRUE) . '" alt="thumb" /></div>';
        } else switch ($this->file->mime) {
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
            case 'image/gif':
                $text .= '<div class="image"><img src="'
                    . url('file/get/' . $this->file->code, FALSE, TRUE) . '" alt="image" /></div>';
        }

        $message->text($roomId, $time, $this->user->getShortName(), $this->user->getId(), $text);

        die($this->file->name);
    }



    /********************* get a file or a thumbnail *********************/



    public function actionGet($fileCode) {
        $this->render('file', array($fileCode, 'file'));
    }

    public function actionThumb($fileCode) {
        $this->render('file', array($fileCode, 'thumb'));
    }

    public function renderFile($fileCode, $type) {
        $system = new System();

        switch ($type) {
            case 'file':
                $file = $system->getFile(Fari_Escape::text($fileCode));
                break;
            case 'thumb':
                $file = $system->getThumbnail(Fari_Escape::text($fileCode));
                break;
        }

        if (!empty($file)) {
            // respond with a file download
            $this->response($file, 'download');
        } else $this->render('Error404/error404');
    }

}