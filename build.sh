#!/usr/bin/env sh

uglifyjs -m --unsafe -o ./assets/jquery.imageAttachment.min.js ./assets/jquery.imageAttachment.js
uglifyjs -m --unsafe -o ./assets/jquery.iframe-transport.min.js ./assets/jquery.iframe-transport.js
scss ./assets/imageAttachment.scss > ./assets/imageAttachment.css