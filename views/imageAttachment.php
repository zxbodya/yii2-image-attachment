<?php
/**
 * @var ImageAttachmentWidget $this
 */

?>
<?php if ($this->model->isNewRecord): ?>
    <div class="imageAttachment">
        <div class="preview"
             style="width: <?php echo $this->getBehavior()->previewWidth ?>px;
                 height: <?php echo $this->getBehavior()->previewHeight ?>px;">
            <div class="no-image">
                <?php echo Yii::t('ImageAttachmentWidget.main', 'Before image upload<br> save this.'); ?>
            </div>
            <img/>
        </div>
    </div>
<?php else: ?>
    <div class="imageAttachment" id="<?php echo $this->id ?>">

        <div class="preview">
            <div class="no-image"><?php echo Yii::t('ImageAttachmentWidget.main', 'No image'); ?></div>
            <img/>
        </div>
        <div class="btn-toolbar actions-bar">
        <span class="btn btn-success fileinput-button">
            <i class="icon-upload icon-white"></i>
            <span class="file_label"
                  data-upload-text="<?php echo Yii::t('ImageAttachmentWidget.main', 'Upload…'); ?>"
                  data-replace-text="<?php echo Yii::t('ImageAttachmentWidget.main', 'Replace…'); ?>">

                  </span>
            <input type="file" name="image" class="afile" accept="image/*" multiple="multiple"/>
        </span>

        <span class="btn disabled remove_image">
            <i class="icon-trash"></i> <?php echo Yii::t('ImageAttachmentWidget.main', 'Remove'); ?></span>
        </div>
        <div class="overlay">
            <div class="overlay-bg">&nbsp;</div>
            <div class="drop-hint">
                <span
                    class="drop-hint-info"><?php echo Yii::t('ImageAttachmentWidget.main', 'Drop Image Here…'); ?></span>
            </div>
        </div>
        <div class="progress-overlay">
            <div class="overlay-bg">&nbsp;</div>
            <div class="progress-modal">
                <div class="info">
                    <h3><?php echo Yii::t('ImageAttachmentWidget.main', 'Uploading…'); ?></h3>

                    <div class="progress progress-striped active">
                        <div class="bar upload-progress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
