<?php

namespace ManaPHP\Http\Request;

use ManaPHP\Utility\Text;

/**
 * ManaPHP\Http\Request\File
 *
 * Provides OO wrappers to the $_FILES
 *
 *<code>
 *    class PostsController extends \ManaPHP\Mvc\Controller
 *    {
 *
 *        public function uploadAction()
 *        {
 *            //Check if the user has uploaded files
 *            if ($this->request->hasFiles() == true) {
 *                //Print the real file names and their sizes
 *                foreach ($this->request->getFiles() as $file){
 *                    echo $file->getName(), " ", $file->getSize(), "\n";
 *                }
 *            }
 *        }
 *
 *    }
 *</code>
 */
class File implements FileInterface
{
    /**
     * @var string
     */
    protected $_key;

    /**
     * @var array
     */
    protected $_file;

    /**
     * @var string
     */
    protected static $_alwaysRejectedExtensions = 'php,pl,py,cgi,asp,jsp,sh,cgi';

    /**
     * \ManaPHP\Http\Request\File constructor
     *
     * @param string $key
     * @param array  $file
     */
    public function __construct($key, $file)
    {
        $this->_key = $key;
        $this->_file = $file;
    }

    /**
     * Returns the file size of the uploaded file
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_file['size'];
    }

    /**
     * Returns the real name of the uploaded file
     *
     * @return string
     */
    public function getName()
    {
        return $this->_file['name'];
    }

    /**
     * Returns the temporary name of the uploaded file
     *
     * @return string
     */
    public function getTempName()
    {
        return $this->_file['tmp_name'];
    }

    /**
     * Returns the mime type reported by the browser
     * This mime type is not completely secure, use getRealType() instead
     *
     * @return string
     */
    public function getType()
    {
        return $this->_file['type'];
    }

    /**
     * Returns the error code
     *
     * @return string
     */
    public function getError()
    {
        return $this->_file['error'];
    }

    /**
     * Returns the file key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Checks whether the file has been uploaded via Post.
     *
     * @return boolean
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->_file['tmp_name']);
    }

    /**
     * Moves the temporary file to a destination within the application
     *
     * @param string       $destination
     * @param string|false $allowedExtensions
     *
     * @throws \ManaPHP\Http\Request\Exception
     */
    public function moveTo($destination, $allowedExtensions = 'jpg,jpeg,png,gif,doc,xls,pdf,zip')
    {
        $extension = pathinfo($destination, PATHINFO_EXTENSION);
        if ($extension) {
            $extension = ',' . $extension . ',';

            if (is_string($allowedExtensions)) {
                $allowedExtensions = ',' . str_replace(' ', '', $allowedExtensions) . ',';
                $allowedExtensions = str_replace(',.', ',', $allowedExtensions);

                if (!Text::contains($allowedExtensions, $extension, true)) {
                    throw new Exception('`:extension` file type is not allowed upload'/**m0fc09a879406a3940*/, ['extension' => $extension]);
                }
            }

            if (is_string(self::$_alwaysRejectedExtensions)) {
                $alwaysRejectedExtensions = ',' . str_replace(' ', '', self::$_alwaysRejectedExtensions) . ',';
                $alwaysRejectedExtensions = str_replace(',.', ',', $alwaysRejectedExtensions);
                if (Text::contains($alwaysRejectedExtensions, $extension, true)) {
                    throw new Exception('`:extension` file types is not allowed upload always'/**m0331d91c39adb3af6*/, ['extensions' => self::$_alwaysRejectedExtensions]);
                }
            }
        }

        if ($this->_file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('error code of upload file is not UPLOAD_ERR_OK: :error'/**m0454e71638e03eee6*/, ['error' => $this->_file['error']]);
        }

        if (is_file($destination)) {
            throw new Exception('`:file` file already exists'/**m0402f85613fe0f167*/, ['file' => $destination]);
        }

        $dir = dirname($destination);
        /** @noinspection NotOptimalIfConditionsInspection */
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new Exception('unable to create `:dir` uploaded directory: :message'/**m01de5a9e19a205e54*/, ['dir' => $dir, 'message' => Exception::getLastErrorMessage()]);
        }

        if (!move_uploaded_file($this->_file['tmp_name'], $destination)) {
            throw new Exception('move_uploaded_file to `:destination` failed: :message'/**m01d834f396d846d2b*/,
                ['destination' => $destination, 'message' => Exception::getLastErrorMessage()]);
        }

        if (!chmod($destination, 0644)) {
            throw new Exception('chmod `:destination` destination failed: :message'/**m0a0e7dc6898fb4abe*/,
                ['destination' => $destination, 'message' => Exception::getLastErrorMessage()]);
        }
    }

    /**
     * Returns the file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->_file['name'], PATHINFO_EXTENSION);
    }
}