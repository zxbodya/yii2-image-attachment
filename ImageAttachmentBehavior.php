<?php
/**
 * Behavior to handle image associated with model
 *
 * @example
 *      'previewImageAttachmentBehavior' => array(
 *          'class' => 'ext.imageAttachment.ImageAttachmentBehavior',
 *          'previewHeight' => 200,
 *          'previewWidth' => 300,
 *          'extension' => 'jpg',
 *          'directory' => 'images/productTheme/preview',
 *          'url' => Yii::app()->request->baseUrl . '/images/productTheme/preview',
 *          'versions' => array(
 *              'small' => array(
 *                  'resize' => array(200, null),
 *              ),
 *              'medium' => array(
 *                  'resize' => array(800, null),
 *              )
 *          )
 *      )
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 *
 */
class ImageAttachmentBehavior extends CActiveRecordBehavior
{
    /**
     * Widget preview height
     * @var int
     */
    public $previewHeight;
    /**
     * Widget preview width
     * @var int
     */
    public $previewWidth;
    /**
     * Extension for saved images
     * @var string
     */
    public $extension;
    /**
     * Path to directory where to save uploaded images
     * @var string
     */
    public $directory;
    /**
     * Directory Url, without trailing slash
     * @var string
     */
    public $url;
    /**
     * @var array Settings for image auto-generation
     * @note
     * 'preview' & 'original' versions names are reserved for image preview in widget
     * and original image files
     * @example
     *  array(
     *       'small' => array(
     *              'resize' => array(200, null),
     *       ),
     *      'medium' => array(
     *              'resize' => array(800, null),
     *      )
     *  );
     */
    public $versions;

    /**
     * name of query param for modification time hash
     * to avoid using outdated version from cache - set it to false
     * @var string
     */
    public $timeHash = '_';

    private $_imageId;

    /**
     * @param CComponent $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if (!isset($this->versions['original']))
            $this->versions['original'] = array();
        if (!isset($this->versions['preview']))
            $this->versions['preview'] = array('fit' => array($this->previewWidth, $this->previewHeight));
        $this->_imageId = $this->getImageId();
    }

    public function beforeDelete($event)
    {
        $this->removeImages();
        parent::beforeDelete($event);
    }

    public function afterSave($event)
    {
        $imageId = $this->getImageId();
        if ($this->_imageId != $imageId) {
            foreach ($this->versions as $version => $config) {
                $oldPath = $this->getFilePath($version, $this->_imageId);
                $newPath = $this->getFilePath($version, $imageId);
                if (file_exists($oldPath)) {
                    rename($oldPath, $newPath);
                }
            }
        }
        parent::afterSave($event);
    }

    public function hasImage()
    {
        $originalImage = $this->getFilePath('original');
        return file_exists($originalImage);
    }

    private function getFileName($version = '', $id = null)
    {
        if ($id === null) {
            $id = $this->getImageId();
        }
        return $version . '/' . $id . '.' . $this->extension;
    }

    public function getUrl($version)
    {
        if (!$this->hasImage()) return null;
        if (!empty($this->timeHash)) {
            $time = filemtime($this->getFilePath($version));
            $suffix = '?' . $this->timeHash . '=' . crc32($time);
        } else {
            $suffix = '';
        }

        return $this->url . '/' . $this->getFileName($version) . $suffix;
    }

    private function getFilePath($version)
    {
        return $this->directory . '/' . $this->getFileName($version);
    }

    /**
     * Removes all images attached to model using this behavior
     */
    public function removeImages()
    {
        foreach ($this->versions as $version => $actions) {
            $this->removeFile($this->getFilePath($version));
        }
    }

    /**
     * Replace existing image by specified file
     * @param $path
     */
    public function setImage($path)
    {
        $this->checkDirectories();
        //create image preview for gallery manager
        foreach ($this->versions as $version => $actions) {
            /** @var Image $image */
            $image = Yii::app()->image->load($path);

            foreach ($actions as $method => $args) {
                call_user_func_array(array($image, $method), is_array($args) ? $args : array($args));
            }
            $image->save($this->getFilePath($version));
        }
    }


    /**
     * Regenerate image versions
     * Should be called in migration on every model after changes in versions configuration
     */
    public function updateImages()
    {
        $this->checkDirectories();
        foreach ($this->versions as $version => $actions)
            if ($version !== 'original') {
                $this->removeFile($this->getFilePath($version));
                /** @var Image $image */
                $image = Yii::app()->image->load($this->getFilePath('original'));
                foreach ($actions as $method => $args) {
                    call_user_func_array(array($image, $method), is_array($args) ? $args : array($args));
                }
                $image->save($this->getFilePath($version));
            }
    }

    private function removeFile($fileName)
    {
        if (file_exists($fileName))
            @unlink($fileName);
    }


    private function getImageId()
    {
        $pk = $this->owner->getPrimaryKey();
        if (is_array($pk)) {
            return implode('_', $pk);
        } else {
            return $pk;
        }
    }

    private function checkDirectory($path)
    {
        if (!file_exists($path))
            mkdir($path, 0777);
    }

    private function checkDirectories()
    {
        if (!file_exists($this->directory)) {
            $this->checkPath();
        }

        foreach ($this->versions as $version => $actions) {
            $this->checkDirectory($this->directory . '/' . $version);
        }
    }

    private function checkPath()
    {
        $parts = explode('/', $this->directory);
        $i = 1;
        $path = implode('/', array_splice(array_values($parts), 0, $i));
        while (file_exists($path)) {
            $i++;
            $path = implode('/', array_splice(array_values($parts), 0, $i));
        }
        while ($i <= count($parts)) {
            mkdir($path, 0777);
            $i++;
            $path = implode('/', array_splice(array_values($parts), 0, $i));
        }
    }
}