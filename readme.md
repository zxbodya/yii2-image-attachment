# ImageAttachment

This extension intended to handle images associated with model.

Extensions provides user friendly widget, for image upload and removal.

![Yii2 image attachement screenshot](http://zxbodya.cc.ua/scrup/90/oycab5bcw0gwc.png)

## Features

1. Asynchronous image upload
2. Ability to generate few image versions with different configurations
3. Drag & Drop

## Decencies

1. Yii2
2. Twitter bootstrap assets
3. Imagine library

## Installation:
The preferred way to install this extension is through [composer](https://getcomposer.org/).

Either run

`php composer.phar require --prefer-dist zxbodya/yii2-image-attachment "*@dev"`

or add

`"zxbodya/yii2-image-attachment": "*@dev"`

to the require section of your `composer.json` file.

## Usage

Add ImageAttachmentBehavior to your model, and configure it, create folder for uploaded files.

```php
public function behaviors()
{
    return [
        TimestampBehavior::className(),
        'coverBehavior' => [
            'class' => ImageAttachmentBehavior::className(),
            // type name for model
            'type' => 'post',
            // image dimmentions for preview in widget 
            'previewHeight' => 200,
            'previewWidth' => 300,
            // extension for images saving
            'extension' => 'jpg',
            // path to location where to save images
            'directory' => Yii::getAlias('@webroot') . '/images/post/cover',
            'url' => Yii::getAlias('@web') . '/images/post/cover',
            // additional image versions
            'versions' => [
                'small' => function ($img) {
                    /** @var ImageInterface $img */
                    return $img
                        ->copy()
                        ->resize($img->getSize()->widen(200));
                },
                'medium' => function ($img) {
                    /** @var ImageInterface $img */
                    $dstSize = $img->getSize();
                    $maxWidth = 800;
                    if ($dstSize->getWidth() > $maxWidth) {
                        $dstSize = $dstSize->widen($maxWidth);
                    }
                    return [
                        $img->copy()->resize($dstSize),
                        ['quality' => 80], // options used when saving image (Imagine::save)
                    ];
                },
            ]
        ]
    ];
}
```


Add ImageAttachmentAction in controller somewhere in your application. Also on this step you can add some security checks for this action.

```php
public function actions()
{
    return [
        'imgAttachApi' => [
            'class' => ImageAttachmentAction::className(),
            // mappings between type names and model classes (should be the same as in behaviour)
            'types' => [
                'post' => Post::className()
            ]
        ],
    ];
}
```
        
Add ImageAttachmentWidget somewhere in you application, for example in editing from.

```php
echo ImageAttachmentWidget::widget(
    [
        'model' => $model,
        'behaviorName' => 'coverBehavior',
        'apiRoute' => 'test/imgAttachApi',
    ]
)
```
        
Done! Now, you can use it in other places in app too:

```php
if ($model->getBehavior('coverBehavior')->hasImage()) {
    echo Html::img($model->getBehavior('coverBehavior')->getUrl('medium'));
}
```
