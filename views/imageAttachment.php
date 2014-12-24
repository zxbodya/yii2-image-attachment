<?php
/**
 * @var View $this
 */
use yii\web\View;
use zxbodya\yii2\imageAttachment\ImageAttachmentWidget;

/** @var ImageAttachmentWidget $widget */
$widget = $this->context;
?>
<?php
if ($widget->model->isNewRecord): ?>
    <div class="imageAttachment">
        <div class="preview"
             style="width: <?php echo $widget->getAttachmentBehavior()->previewWidth ?>px;
                 height: <?php echo $widget->getAttachmentBehavior()->previewHeight ?>px;">
            <div class="no-image">
                <?php echo Yii::t('imageAttachment/main', 'Before image upload<br> save this.'); ?>
            </div>
            <img/>
        </div>
    </div>
<?php else: ?>
    <div class="imageAttachment" id="<?php echo $widget->id ?>">

        <div class="preview">
            <div class="no-image"><?php echo Yii::t('imageAttachment/main', 'No image'); ?></div>
            <img/>
        </div>
        <div class="btn-toolbar actions-bar">
            <span class="btn btn-success btn-file">
                <i class="glyphicon glyphicon-upload glyphicon-white"></i>
                <span class="file_label"
                      data-upload-text="<?php echo Yii::t('imageAttachment/main', 'Upload…'); ?>"
                      data-replace-text="<?php echo Yii::t('imageAttachment/main', 'Replace…'); ?>">
                      </span>
                <input type="file" name="image" class="afile" accept="image/*" multiple="multiple"/>
            </span>

            <span class="btn btn-default disabled remove_image">
                <i class="glyphicon glyphicon-trash"></i> <?php echo Yii::t('imageAttachment/main', 'Remove'); ?></span>
        </div>
        <div class="overlay">
            <div class="overlay-bg">&nbsp;</div>
            <div class="drop-hint">
                <span
                    class="drop-hint-info"><?php echo Yii::t('imageAttachment/main', 'Drop Image Here…'); ?></span>
            </div>
        </div>
        <div class="progress-overlay">
            <div class="overlay-bg">&nbsp;</div>
            <div class="progress-modal">
                <div class="info">
                    <h3><?php echo Yii::t('imageAttachment/main', 'Uploading…'); ?></h3>

                    <div class="progress ">
                        <div class="progress-bar progress-bar-info progress-bar-striped active upload-progress"
                             role="progressbar">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
