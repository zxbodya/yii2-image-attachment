<?php
namespace zxbodya\yii2\imageAttachment;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\imagine\Image;

/**
 * Behavior to handle image associated with model
 *
 * @example
 *      'coverBehavior' => [
 *          'class' => ImageAttachmentBehavior::className(),
 *          'type' => 'post',
 *          'previewHeight' => 200,
 *          'previewWidth' => 300,
 *          'extension' => 'jpg',
 *          'directory' => Yii::getAlias('@webroot') . '/images/post/cover',
 *          'url' => Yii::getAlias('@web') . '/images/post/cover',
 *          'versions' => [
 *              'small' => function ($img) {
 *                  return $img
 *                      ->copy()
 *                      ->resize($img->getSize()->widen(200));
 *              },
 *              'medium' => function ($img) {
 *                  $dstSize = $img->getSize();
 *                  $maxWidth = 800;
 *                  if ($dstSize->getWidth() > $maxWidth) {
 *                      $dstSize = $dstSize->widen($maxWidth);
 *                  }
 *                  return $img
 *                      ->copy()
 *                      ->resize($dstSize);
 *              },
 *          ]
 *      ]
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 *
 *
 */
class ImageAttachmentBehavior extends Behavior
{
    /**
     * @var string Type name assigned to model in image attachment action
     */
    public $type;
    /**
     * @var ActiveRecord the owner of this behavior
     */
    public $owner;

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
     * @var array Functions to generate image versions
     * @note Be sure to not modify image passed to your version function,
     *       because it will be reused in all other versions,
     *       Before modification you should copy images as in examples below
     * @note 'preview' & 'original' versions names are reserved for image preview in widget
     *       and original image files, if it is required - you can override them
     * @example
     * [
     *  'small' => function ($img) {
     *      return $img
     *          ->copy()
     *          ->resize($img->getSize()->widen(200));
     *  },
     *  'medium' => function ($img) {
     *      $dstSize = $img->getSize();
     *      $maxWidth = 800;
     * ]
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
     * @param ActiveRecord $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if (!isset($this->versions['original'])) {
            $this->versions['original'] = function ($image) {
                return $image;
            };
        }
        if (!isset($this->versions['preview'])) {
            $this->versions['preview'] = function ($originalImage) {
                /** @var ImageInterface $originalImage */
                return $originalImage
                    ->thumbnail(new Box($this->previewWidth, $this->previewHeight));
            };
        }
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }


    public function beforeDelete()
    {
        $this->removeImages();
    }

    public function afterFind()
    {
        $this->_imageId = $this->getImageId();
    }

    public function afterUpdate()
    {
        // if primary key changes - move image
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
    }


    public function hasImage($ext = null)
    {
        $originalImage = $this->getFilePath('original', null, $ext);

        return file_exists($originalImage);
    }

    private function getFileName($version = '', $id = null, $ext = null)
    {
        if ($id === null) {
            $id = $this->getImageId();
        }
        if ($ext === null) {
            $ext = $this->extension;
        }

        return $id . '/' . $version . '.' . $ext;
    }

    public function getUrl($version)
    {
        if (!$this->hasImage()) {
            return null;
        }
        if (!empty($this->timeHash)) {
            $time = filemtime($this->getFilePath($version));
            $suffix = '?' . $this->timeHash . '=' . crc32($time);
        } else {
            $suffix = '';
        }

        return $this->url . '/' . $this->getFileName($version) . $suffix;
    }

    public function getFilePath($version, $id = null, $ext = null)
    {
        return $this->directory . '/' . $this->getFileName($version, $id, $ext);
    }

    /**
     * Removes all images attached to model using this behavior
     *
     * @param null $ext
     */
    public function removeImages($ext = null)
    {
        foreach ($this->versions as $version => $fn) {
            $this->removeFile($this->getFilePath($version, null, $ext));
        }
    }

    /**
     * Replace existing image by specified file
     *
     * @param $path
     */
    public function setImage($path)
    {
        $this->checkDirectories();

        $originalImage = Image::getImagine()->open($path);
        //save image in original size

        //create image preview for gallery manager
        foreach ($this->versions as $version => $fn) {
            /** @var Image $image */

            call_user_func($fn, $originalImage)
                ->save($this->getFilePath($version));
        }
    }


    /**
     * Regenerate image versions
     * Should be called in migration on every model after changes in versions configuration
     *
     * @param string|null $oldExt
     */
    public function updateImages($oldExt = null)
    {
        if ($this->hasImage($oldExt)) {
            $this->checkDirectories();
            if ($oldExt !== null) {
                $originalImage = Image::getImagine()->open($this->getFilePath('original', null, $oldExt));
                $originalImage->save($this->getFilePath('original'));
                $this->removeImages($oldExt);
            } else {
                $originalImage = Image::getImagine()->open($this->getFilePath('original'));
            }
            foreach ($this->versions as $version => $fn) {
                if ($version !== 'original') {
                    $this->removeFile($this->getFilePath($version));
                    call_user_func($fn, $originalImage)
                        ->save($this->getFilePath($version));
                }
            }
        }
    }

    private function removeFile($fileName)
    {
        if (file_exists($fileName)) {
            @unlink($fileName);
        }
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
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
    }

    private function checkDirectories()
    {
        if (!file_exists($this->directory)) {
            $this->checkPath();
        }

        $this->checkDirectory($this->directory . '/' . $this->getImageId());
    }

    private function checkPath()
    {
        $parts = explode('/', rtrim($this->directory, '/'));
        $i = 0;

        $path = implode('/', array_slice($parts, 0, count($parts) - $i));
        while (!file_exists($path)) {
            $i++;
            $path = implode('/', array_slice($parts, 0, count($parts) - $i));
        }
        $i--;
        $path = implode('/', array_slice($parts, 0, count($parts) - $i));
        while ($i >= 0) {
            mkdir($path, 0777);
            $i--;
            $path = implode('/', array_slice($parts, 0, count($parts) - $i));
        }
    }
}