<?php
namespace zxbodya\yii2\imageAttachment;

use Yii;
use yii\base\Exception;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Widget to provide interface for image upload to models with
 * ImageAttachmentBehavior.
 * @example
 *
 *   $this->widget('ext.imageAttachment.ImageAttachmentWidget', array(
 *       'model' => $model,
 *       'behaviorName' => 'previewImageAttachmentBehavior',
 *       'apiRoute' => 'api/saveImageAttachment',
 *   ));
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class ImageAttachmentWidget extends Widget
{
    /**
     * Route to ImageAttachmentAction
     * @var string
     */
    public $apiRoute;

    public $assets;

    /**
     * Behaviour name in model to use
     * @var string
     */
    public $behaviorName;

    /**
     * Model with behaviour
     * @var ActiveRecord
     */
    public $model;

    /**
     * @return ImageAttachmentBehavior
     */
    public function getAttachmentBehavior()
    {
        return $this->model->getBehavior($this->behaviorName);
    }


    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['imageAttachment/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@zxbodya/yii2/imageAttachment/messages',
            'fileMap' => [
            ],
        ];
    }

    public function run()
    {
        if ($this->apiRoute === null) {
            throw new Exception('$apiRoute must be set.', 500);
        }


        $attachmentBehavior = $this->getAttachmentBehavior();
        $options = [
            'hasImage' => $attachmentBehavior->hasImage(),
            'previewUrl' => $attachmentBehavior->getUrl('preview'),
            'previewWidth' => $attachmentBehavior->previewWidth,
            'previewHeight' => $attachmentBehavior->previewHeight,
            'apiUrl' => Url::to(
                [
                    $this->apiRoute,
                    'type' => $attachmentBehavior->type,
                    'behavior' => $this->behaviorName,
                    'id' => $attachmentBehavior->owner->getPrimaryKey(),
                ]
            ),
        ];

        $optionsJS = Json::encode($options);

        $view = $this->getView();
        ImageAttachmentAsset::register($view);
        $view->registerJs("$('#{$this->id}').imageAttachment({$optionsJS});");

        return $this->render('imageAttachment');
    }

}
