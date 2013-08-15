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
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
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
    private $_imageId;

    /**
     * @param CComponent $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $this->versions['original'] = array();
        $this->versions['preview'] = array('centeredpreview' => array($this->previewWidth, $this->previewHeight ));
        $this->_imageId = $this->getImageId();
    }

    protected function beforeDelete($event)
    {
        $this->removeImages();
        parent::beforeDelete($event);
    }

    protected function afterSave($event)
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
        return $id . '_' . $version . '.' . $this->extension;
    }

    public function getUrl($version = '')
    {
        return $this->url . '/' . $this->getFileName($version);
    }

    private function getFilePath($version)
    {
        return $this->directory . '/' . $this->getFileName($version);
    }

    /**
     * Removes all attached using this behavior
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
            sort($pk);
            return implode('_', $pk);
        } else {
            return $pk;
        }
    }

}