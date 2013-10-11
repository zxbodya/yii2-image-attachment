<?php
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
class ImageAttachmentAction extends CAction
{
    public function run($model, $behavior)
    {
        $id = $_GET['id'];
		$remove = isset($_POST['remove'])?$_POST['remove']:false;
        $model = CActiveRecord::model($model)->findByPk($id);
        if ($remove) {
            $model->{$behavior}->removeImages();
            echo CJSON::encode(array());
        } else {
            $imageFile = CUploadedFile::getInstanceByName('image');

            $model->{$behavior}->setImage($imageFile->getTempName());
            echo CJSON::encode(array(
                'previewUrl' => $model->{$behavior}->getUrl('preview'),
            ));
        }
    }
}