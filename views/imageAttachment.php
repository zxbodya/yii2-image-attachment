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
            <div class="no-image">Before image upload<br> save your page.</div>
            <img/>
        </div>
    </div>
<?php else: ?>
    <div class="imageAttachment" id="<?php echo $this->id ?>">

        <div class="preview">
            <div class="no-image">No image</div>
            <img/>
        </div>
        <div class="btn-toolbar">
        <span class="btn btn-success fileinput-button">
            <i class="icon-plus icon-white"></i> <span class="file_label">Upload…</span>
            <input type="file" name="image" class="afile" accept="image/*" multiple="multiple"/>
        </span>

        <span class="btn disabled remove_image">
            <i class="icon-remove"></i> Remove</span>
        </div>
        <div class="overlay">
            <div class="overlay-bg">&nbsp;</div>
            <div class="drop-hint">
                <span class="drop-hint-info">Drop Image Here…</span>
            </div>
        </div>
        <div class="progress-overlay">
            <div class="overlay-bg">&nbsp;</div>
            <!-- Upload Progress Modal-->
            <div class="modal progress-modal">
                <div class="modal-header">
                    <h3>Uploading image…</h3>
                </div>
                <div class="modal-body">
                    <div class="progress progress-striped active">
                        <div class="bar upload-progress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
