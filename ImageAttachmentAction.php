<?php
namespace zxbodya\yii2\imageAttachment;

use Yii;
use yii\base\Action;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * Action to handle calls from ImageAttachmentWidget,
 * and apply changes to model with ImageAttachmentBehavior
 *
 * @example
 *
 *    public function actions()
 *    {
 *        return array(
 *            'saveImageAttachment' => 'ext.imageAttachment.ImageAttachmentAction',
 *        );
 *    }
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 *
 */
class ImageAttachmentAction extends Action
{
    /**
     * Glue used to implode composite primary keys
     * @var string
     */
    public $pkGlue = '_';

    /**
     * @var array Mapping between types and model class names
     */
    public $types = [];

    public function run($type, $behavior, $id)
    {
        $remove = Yii::$app->request->post('remove', false);

        if (!isset($this->types[$type])) {
            throw new BadRequestHttpException('Specified model not found');
        }
        /** @var ActiveRecord $targetModel */
        $pkNames = call_user_func([$this->types[$type], 'primaryKey']);
        $pkValues = explode($this->pkGlue, $id);

        $pk = array_combine($pkNames, $pkValues);
        $targetModel = call_user_func([$this->types[$type], 'findOne'], $pk);

        /** @var ImageAttachmentBehavior $behavior */
        $behavior = $targetModel->getBehavior($behavior);

        if ($remove) {
            $behavior->removeImages();

            return Json::encode(array());
        } else {
            /** @var UploadedFile $imageFile */
            $imageFile = UploadedFile::getInstanceByName('image');
            $behavior->setImage($imageFile->tempName);

            return Json::encode(
                array(
                    'previewUrl' => $behavior->getUrl('preview'),
                )
            );
        }
    }
}
