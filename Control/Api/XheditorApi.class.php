<?php
class XheditorApi extends ApiController {
    function upload($uploadPath,$showPath) {
        import('@.Control.Api.XheditorImpl.Upload');
        $uploadobj = new Upload();
        $uploadobj->uploadfun($uploadPath,$showPath);
    }
}