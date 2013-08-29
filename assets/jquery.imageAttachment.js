(function ($) {
    $.fn.imageAttachment = function (opts) {
        if (this.length) {
            this.each(function () {
                imageAttachment($(this), opts);
            });
        }
    };

    function imageAttachment(el, options) {

        var $progressOverlay = $('.progress-overlay', el);
        var $uploadProgress = $('.upload-progress', el);
        var $removeImage = $('.remove_image', el);
        var $fileLabel = $('.file_label', el);

        var hasImage = options.hasImage || false;
        var apiUrl = options.apiUrl || '';
        var previewUrl = options.previewUrl || '';
        var previewWidth = options.previewWidth || 300;
        var previewHeight = options.previewHeight || 200;

        var previewWrap = $('.preview', el);
        var previewImage = $('img', previewWrap);
        previewWrap.css({
            'min-width': previewWidth + 'px',
            'min-height': previewHeight + 'px'
        });
        if (hasImage) {
            updatePreview(previewUrl);
        } else {
            $fileLabel.text($fileLabel.data('upload-text'));
        }

        function updatePreview(url) {
            if (url) {
                console.log(url);
                previewUrl = url;
                hasImage = true;
                el.addClass('with-image');
                previewImage.attr('src', previewUrl);
                $removeImage.removeClass('disabled');
                $fileLabel.text($fileLabel.data('replace-text'));
            } else {
                el.removeClass('with-image');
                previewImage.removeAttr('src');
                $removeImage.addClass('disabled');
                $fileLabel.text($fileLabel.data('upload-text'));
            }
        }

        $removeImage.click(function () {
            if (hasImage) {
                $.ajax({
                    type: 'POST',
                    url: apiUrl + '&remove=true',
                    data: (options.csrfToken ? '&' + options.csrfTokenName + '=' + options.csrfToken : ''),
                    dataType: "json"
                }).done(function (data) {
                        hasImage = false;
                        updatePreview();
                    });
            }
        });

        //// image upload

        if (window.FormData !== undefined) { // if XHR2 available
            var uploadFileName = $('.afile', el).attr('name');

            function multiUpload(files) {
                if (files.length == 0) return;
                $progressOverlay.show();
                $uploadProgress.css('width', '5%');
                var filesCount = files.length;
                for (var i = 0; i < filesCount; i++) {
                    var fd = new FormData();

                    fd.append(uploadFileName, files[i]);
                    if (options.csrfToken) {
                        fd.append(options.csrfTokenName, options.csrfToken);
                    }
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', apiUrl, true);
                    xhr.onload = function () {
                        if (this.status == 200) {
                            var resp = JSON.parse(this.response);
                            updatePreview(resp.previewUrl)
                        } else {
                            // exception !!!
                            console.log(this.response);
                        }
                        $uploadProgress.css('width', '100%');
                        $progressOverlay.hide();

                    };
                    xhr.send(fd);
                }

            }

            (function () { // add drag and drop
                var area = el[0];
                var isOver = false;
                var lastIsOver = false;

                setInterval(function () {
                    if (isOver != lastIsOver) {
                        if (isOver) area.classList.add('over');
                        else area.classList.remove('over');
                        lastIsOver = isOver
                    }
                }, 30);

                function handleDragOver(e) {
                    e.preventDefault();
                    isOver = true;
                    return false;
                }

                function handleDragLeave() {
                    isOver = false;
                    return false;
                }

                function handleDrop(e) {
                    e.preventDefault();
                    e.stopPropagation();


                    var files = e.dataTransfer.files;
                    multiUpload(files);

                    isOver = false;
                    return false;
                }

                function handleDragEnd() {
                    isOver = false;
                }


                area.addEventListener('dragover', handleDragOver, false);
                area.addEventListener('dragleave', handleDragLeave, false);
                area.addEventListener('drop', handleDrop, false);
                area.addEventListener('dragend', handleDragEnd, false);
            })();

            $('.afile', el).on('change', function (e) {
                e.preventDefault();
                multiUpload(this.files);
            });
        } else {
            $('.afile', el).on('change', function (e) {
                e.preventDefault();
                $progressOverlay.show();
                $uploadProgress.css('width', '5%');

                var data = {};
                if (options.csrfToken)
                    data[options.csrfTokenName] = options.csrfToken;
                $.ajax({
                    type: 'POST',
                    url: apiUrl,
                    data: data,
                    files: $(this),
                    iframe: true,
                    processData: false,
                    dataType: "json"
                }).done(function (resp) {
                        updatePreview(resp.previewUrl);
                        $uploadProgress.css('width', '100%');
                        $progressOverlay.hide();
                    });
            });
        }
    }
}(jQuery));