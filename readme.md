# ImageAttachment

This extension intended to handle actions with images associated with model.

Extensions provides user friendly widget, to upload and remove image.

Screenshots:
![Yii image attachement screenshots](http://zxbodya.cc.ua/scrup/4l/wicvwwi7c4cgw.png)

## Features

1. Asynchronous image upload
2. Ability to generate few image versions with different configurations
3. Drag & Drop

## Decencies

1. Yii
2. Twitter bootstrap
3. [yii-image component](https://bitbucket.org/z_bodya/yii-image)

## Installation:

0. Download and extract extension somewhere in your application(in this guide into extensions/imageAttachment). Also available [in composer](https://packagist.org/packages/z_bodya/yii-image-attachment).
1. Add ImageAttachmentBehavior to you model, and configure it, create folder for uploaded files.

        :::php
        public function behaviors()
        {
            return array(
                'previewImageAttachmentBehavior' => array(
                    'class' => 'ext.imageAttachment.ImageAttachmentBehavior',
                    // size for image preview in widget
                    'previewHeight' => 200,
                    'previewWidth' => 300,
                    // extension for image saving, can be also tiff, png or gif
                    'extension' => 'jpg',
                    // folder to store images
                    'directory' => Yii::getPathOfAlias('webroot').'/images/productTheme/preview',
                    // url for images folder
                    'url' => Yii::app()->request->baseUrl . '/images/productTheme/preview',
                    // image versions
                    'versions' => array(
                        'small' => array(
                            'resize' => array(200, null),
                        ),
                        'medium' => array(
                            'resize' => array(800, null),
                        )
                    )
                )
            );
        }

2. Add ImageAttachmentAction in controller somewhere in your application. Also on this step you can add some security checks for this action.

        :::php
        class ApiController extends Controller
        {
            public function actions()
            {
                return array(
                    'saveImageAttachment' => 'ext.imageAttachment.ImageAttachmentAction',
                );
            }
        }
        
3. Add ImageAttachmentWidget somewhere in you application, for example in editing from.

        :::php
        $this->widget('ext.imageAttachment.imageAttachmentWidget', array(
            'model' => $model,
            'behaviorName' => 'previewImageAttachmentBehavior',
            'apiRoute' => 'api/saveImageAttachment',
        ));
        
4. It is done! You can use it now.

        :::php
        if($model->preview->hasImage())
            echo CHtml::image($model->preview->getUrl('medium'),'Medium image version');
        else
            echo 'no image uploaded';

## Contributing

Pull requests are welcome!
Also, if you any ideas or questions about - welcome to [issue tracker](https://bitbucket.org/z_bodya/yii-image-attachment/issues)