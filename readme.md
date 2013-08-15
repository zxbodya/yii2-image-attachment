# ImageAttachment

This extension intended to handle actions with images associated with model.

Extensions provides user friendly widget, to upload and remove image.

## Features

1. Asynchronous image upload
2. Ability to generate few image versions with different configurations
3. Drag & Drop

## Decencies

1. Yii
2. Twitter bootstrap
3. yii-image component (https://bitbucket.org/z_bodya/yii-image)

## How to use:

1. Add ImageAttachmentBehavior to you model, and configure it, create folder for uploaded files.
2. Add ImageAttachmentAction in controller somewhere in your application.
3. Add ImageAttachmentWidget somewhere in you application, for example in editing from.
4. It is done! You can use it now.

Configuration example provided in extension classes.