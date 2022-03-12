'use strict';

/* Show loader start */
function showLoader() {
    $('#overlay').fadeIn();
}
/* Show loader end */

/* Hide loader start */
function hideLoader() {
    $('#overlay').fadeOut();
}

function myToastr(msg, type) {
    toastr.remove();
    if (type == 'error') {
        toastr.error(msg);
    } else if (type == 'success') {
        toastr.success(msg);
    }
}

function triggerEmailsField(val){
    if (val == 'specific') {
        $('.emails').removeClass('hide');
    }else{
        $('.emails').addClass('hide');
    }

}

    function resetFilter() {
        var newURL = location.href.split("?")[0];
        console.log(newURL);
          window.history.pushState('object', document.title, newURL);
        //   location.reload();
    }

    function resetFilterfeed() {
        var newURL = location.href.split("?")[0];
        console.log(newURL);
        location.href = newURL;
        //   window.history.pushState('object', document.title, newURL);
        //   location.reload();
    }

    function searchClick() {
        $('#search').click();
    }


$(document).ready(function () {
    $('.image-popup').magnificPopup({
        type: 'image',
        closeOnContentClick: true,
        mainClass: 'mfp-fade',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
        }
    });
});


function resetForm(formID){
    $("#"+formID).closest('form').find("input[type=text], input[type=number], textarea").val("");
    $("#"+formID).closest('form').find("input[type=checkbox]").removeAttr("checked");
    $("#"+formID).closest('form').find("input[type=radio]").removeAttr("checked");
    $("#custom").attr("checked",true);
    $('select').val('');
    $('#createBtn').html('Create');
    $('#image_add').attr('src','');
}



function printArea() {
    document.getElementById('iframeid').contentWindow.print();
}

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};
    var l = 0;
    $.map(unindexed_array, function (n, i) {
        if (n['name'] == 'category_id[]') {
            if (l == 0) {
                indexed_array['category_id'] = [];
            }
            indexed_array['category_id'].push(n['value']);
            l++;
        } else {
            indexed_array[n['name']] = n['value'];
        }
    });
    return indexed_array;
}

function validateEmail(email) {
    var x = email;
    var atpos = x.indexOf("@");
    var dotpos = x.lastIndexOf(".");
    if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= x.length) {
        return true;
    } else {
        return false;
    }
}

function setDataLimit(limit, getData, type, portal) {
    var url;
    if (getData == 'NA') {
        window.location.href = base_url + "/" + portal + "/" + type + "?per_page=" + limit;
    } else {
        var res = getData.split("&");
        var myarray = [];
        $.each(res, function (key, value) {
            var res1 = value.split("=");
            if (res1[0] == 'per_page') {
                res1[1] = limit;
            }
            var newRes = res1.join("=");
            myarray.push(newRes);
        });
        var newUrl = myarray.join("&");
        if (newUrl) {
            window.location.href = base_url + "/" + portal + "/" + type + "?" + newUrl;
        }
    }
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function add_category(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name != '') {
        if (data.color != '') {
            if (blogThumbImage != undefined || data.id) {
                $.ajax({
                    type: 'POST',
                    url: base_url + "/add-update-category",
                    headers: {},
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify(data),
                    success: function (response) {
                        if (response.success) {
                            myToastr(response.message, 'success');
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else {
                            myToastr(response.message, 'error');
                        }
                    }
                });
            } else {
                myToastr('Please category image', 'error');
            }
        } else {
            myToastr('Select category color', 'error');
        }
    } else {
        myToastr('Enter category', 'error');
    }
}

function add_rss_feed_src(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    var re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/;
    if (data.category_id != '') {
        if (data.rss_name != '') {
            if (data.rss_url != '') {
                if (/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(data.rss_url)) {
                    $.ajax({
                        type: 'POST',
                        url: base_url + "/add-update-rss-feed-src",
                        headers: {},
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify(data),
                        success: function (response) {
                            if (response.success) {
                                myToastr(response.message, 'success');
                                setTimeout(function () {
                                    window.location.reload();
                                }, 500);
                            } else {
                                myToastr(response.message, 'error');
                            }
                        }
                    });
                } else {
                    myToastr('Please enter valid URL', 'error');
                }
            } else {
                myToastr('Please enter URL', 'error');
            }
        } else {
            myToastr('Enter name', 'error');
        }
    } else {
        myToastr('Select category', 'error');
    }
}


var blogThumbImage;

function uploadCategoryThumbImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'thumb_image';
    } else {
        var authorimage = 'thumb_image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadCategoryThumbImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function triggerFileInput(className) {
    $('.' + className).click();
}

var authorImage;

function uploadauthorImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'authorimage' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'authorimage';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $("#show_cat_image_add").show();
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                category_image = data.data;
                                if (category_image != undefined) {
                                    $('#' + authorimage).val(category_image);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function addUpdateAuthor(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    if (data.name == '') {
        flag = false;
        myToastr('Enter name', 'error');
    } else if (data.email == '') {
        flag = false;
        myToastr('Enter email', 'error');
    }

    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/addUpdateAuthor",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}


var blogThumbImage;

function uploadblogThumbImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'thumb_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadBlogThumbImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var BannerImage;

function uploadBannerImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadBannerImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                BannerImage = data.data;
                                if (BannerImage != undefined) {
                                    $('#' + authorimage).val(BannerImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var productMyltipleImages;

function uploadMultipleBannerImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    var form_data = new FormData();
    // Read selected files
    var totalfiles = document.getElementById('image').files.length;
    for (var index = 0; index < totalfiles; index++) {
        form_data.append("image[]", document.getElementById('image').files[index]);
    }
    $.ajax({
        url: base_url + '/uploadMultipleBannerImage',
        data: form_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            var productMyltipleimages_url = '';
            setTimeout(function () {
                if (response.status) {
                    if (id == 0) {
                        $("#" + previewid).show();
                    }
                    productMyltipleImages = response.data.images;
                    productMyltipleimages_url = response.data.images_url;
                    $('#' + createBtn).prop('disabled', false);
                    if ($('#productId').val()) {
                        $('#' + createBtn).html('Update');
                    } else {
                        $('#' + createBtn).html('Create');
                    }
                }
                for (var index = 0; index < productMyltipleimages_url.length; index++) {
                    var src = productMyltipleimages_url[index];
                    var cls = 'delete_div_' + index;
                    $('#preview').append('<div class="col-span-12 sm:col-span-12" style="float:left" id="' + cls + '"  ><div><img src="' + src + '" class="multipleUpload"></div></div>');
                }
            }, 10);
        }
    })
}





var audiofile;

function uploaudiofile(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var audio = 'audio_file_upload';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "mp3") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    // $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('audio_file', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadAudioFIle',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                audiofile = data.data.name;
                                var fullpath =  data.data.fullpath;
                                if (audiofile != undefined) {
                                    $("#"+previewid).show();
                                    $('#' + audio).val(audiofile);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                    setTimeout(()=>{
                                        $('#audiopreview').html(`<audio controls controlsList="nodownload"><source src="`+fullpath+`" type="audio/mp3"></audio>`);
                                    },500);
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only mp3 file', 'error');
        }
    }
}

function addUpdateBlog(e, formid,submittype) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    data.submittype = submittype;
    var selected = [];
    // selected = $('#language').val();

    selected = $("#language :selected").map((_, e) => e.value).get();

    data.language_code = selected;
    console.log(data);
    if(submittype=='draft'){
        data.image = productMyltipleImages;
        var desc = CKEDITOR.instances['blogdescription'].getData();
        data.description = desc;
        $.ajax({
            type: 'POST',
            url: base_url + "/addUpdateblog",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.href = base_url + "/blog/side-menu/light";
                    }, 200);
                } else {
                    myToastr("something went wrong.", 'failure');
                    setTimeout(function () {
                        window.location.href = base_url + "/blog/side-menu/light";
                    }, 200);
                }
            }
        });
    }else{
        var flag = true;
        e.preventDefault();
        if (data.category_id == '') {
            flag = false;
            myToastr('Select category', 'error');
        } else if (data.author_id == '') {
            flag = false;
            myToastr('Select author', 'error');
        } else if (data.title == '') {
            flag = false;
            myToastr('Enter title', 'error');
        } else if (data.slug == '') {
            flag = false;
            myToastr('Enter slug', 'error');
        } else {
            data.image = productMyltipleImages;
            if (productMyltipleImages != undefined || data.id) {
                $('#createBtn').attr('disabled');
                $('#createBtn').text('Wait..');
                var desc = CKEDITOR.instances['blogdescription'].getData();
                data.description = desc;
                $.ajax({
                    type: 'POST',
                    url: base_url + "/addUpdateblog",
                    headers: {},
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify(data),
                    success: function (response) {
                        if(data.submittype=='create'){
                            $('#createBtn').text('Create');
                        }else{
                            $('#createBtn').text('Update');
                        } 
                        console.log(response);
                        if (response.status) {
                            myToastr(response.message, 'success');
                            setTimeout(function () {
                                window.location.href = base_url + "/blog/side-menu/light";
                            }, 200);
                        } else {
                            if(data.submittype=='create'){
                                $('#createBtn').text('Create');
                            }else{
                                $('#createBtn').text('Update');
                            }                            
                            myToastr(response.message, 'error');
                        }
                    }
                });
            } else {
                flag = false;
                myToastr('Please select image', 'error');
                if(data.submittype=='create'){
                    $('#createBtn').text('Create');
                }else{
                    $('#createBtn').text('Update');
                } 
            }
        }
    }    
}

function validateSlug(slug) {
    var flag = true;
    console.log(slug);
    // e.preventDefault();
    if (slug == '') {
        flag = false;
        myToastr('input slug', 'error');
    } else {
        var data = {};
        data.slug = slug;
        $.ajax({
            type: 'POST',
            url: base_url + "/validateSlug",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                console.log(response);
                if (response.status) {
                    // myToastr(response.message, 'success');
                } else {
                    // $('#createBtn').text('Update');
                    myToastr(response.message, 'error');
                }
            }
        });
    }  
}

var logoUpload;

function uploadLogoImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var logoimage = 'app_logo' + id;
    } else {
        createBtn = 'createBtn';
        logoimage = 'app_logo';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $("#show_cat_image_add").show();
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadLogoImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                logoUpload = data.data;
                                if (logoUpload != undefined) {
                                    $('#' + logoimage).val(logoUpload);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var profileUpload;

function uploadProfileImage(input, previewid, type, id) {

    if (id != 0) {
        var createBtn = 'createBtn' + id;
        var logoimage = 'photo' + id;
    } else {
        createBtn = 'createBtn';
        logoimage = 'photo';
    }

    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $("#show_cat_image_add").show();
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadProfileImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                profileUpload = data.data;
                                if (profileUpload != undefined) {
                                    $('#' + logoimage).val(profileUpload);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Save');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var bgUpload;

function uploadBgImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'bg_image' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'bg_image';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadBGImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                bgUpload = data.data;
                                if (bgUpload != undefined) {
                                    $('#' + authorimage).val(bgUpload);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var siteLogo;

function uploadWebsiteLogo(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'site_logo' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'site_logo';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadLogoImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                siteLogo = data.data;
                                if (siteLogo != undefined) {
                                    $('#' + authorimage).val(siteLogo);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var BannerImage;

function uploadCmsBannerImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');

    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadCMSBannerImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                BannerImage = data.data;
                                if (BannerImage != undefined) {
                                    $('#' + authorimage).val(BannerImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function addUpdateCmsPage(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    if (data.title == '') {
        flag = false;
        myToastr('Enter title', 'error');
    }
    var desc = CKEDITOR.instances['blogdescription'].getData();
    data.description = desc;
    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/addUpdateCMSPage",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}


$(function () {

    $("#tablecontents").sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function () {
            sendOrderToServer();
        }
    });

    function sendOrderToServer() {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        $('tr.row1').each(function (index, element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index + 1
            });
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            url: base_url + "/category-sortable",
            data: {
                order: order,
                _token: token
            },
            success: function (response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
});

$(function () {

    $("#tablecontentsslider").sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function () {
            sendOrderOfSliderPost();
        }
    });

    function sendOrderOfSliderPost() {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        $('tr.row1').each(function (index, element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index + 1
            });
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            url: base_url + "/blog-sortable",
            data: {
                order: order,
                _token: token
            },
            success: function (response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
});

function deleteBlogImage(blog_image_id) {
    var data = {};
    data.blog_image_id = blog_image_id;
    $.ajax({
        type: 'GET',
        url: base_url + "/deleteBlogImage/" + blog_image_id,
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        success: function (response) {
            if (response.status) {
                myToastr(response.message, 'success');
                $('#delete_div_' + blog_image_id).remove();
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}

function add_social(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name != '') {
        if(data.url !='') {
            if(data.icon !=''){
                $.ajax({
                    type: 'POST',
                    url: base_url + "/add-update-social",
                    headers: {},
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify(data),
                    success: function (response) {
                        if (response.success) {
                            myToastr(response.message, 'success');
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else {
                            myToastr(response.message, 'error');
                        }
                    }
                });
            }else{
                myToastr('Enter icon', 'error');
            }
        }else{
            myToastr('Enter url', 'error');
        }   
    } else {
        myToastr('Enter name', 'error');
    }
}

function add_subadmin(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name == '') {
        myToastr('Enter name', 'error');
    }else if (data.email == '') {
        myToastr('Enter email', 'error');
    }else if(data.password == ''){
        myToastr('Enter password', 'error');
    }else{
        $.ajax({
            type: 'POST',
            url: base_url + "/add-update-sub-admin",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.success) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

var subAdminThumbImage;

function uploadSubadminThumbImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'image';
    } else {
        var authorimage = 'image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadSubAdminThumbImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                subAdminThumbImage = data.data;
                                if (subAdminThumbImage != undefined) {
                                    $('#' + authorimage).val(subAdminThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function loadHtml(id, dataObj) {
    var html = "";
    var html = dataObj.html;
    $("#" + id).html(html);
}




var blogThumbImage;

function uploadLogo(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'thumb_image';
    } else {
        var authorimage = 'thumb_image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/upload-logo',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function add_livenews(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.company_name != '') {
        if (blogThumbImage != undefined || data.id) {
            $.ajax({
                type: 'POST',
                url: base_url + "/add-update-live-news",
                headers: {},
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(data),
                success: function (response) {
                    if (response.success) {
                        myToastr(response.message, 'success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        myToastr(response.message, 'error');
                    }
                }
            });
        } else {
            myToastr('Please select image', 'error');
        }
        
    } else {
        myToastr('Enter category', 'error');
    }
}


var blogThumbImage;

function uploadEpaperLogo(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'thumb_image';
    } else {
        var authorimage = 'thumb_image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/upload-logo-e-paper',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}



var pdf;

function uploadPdf(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'upload_file';
    } else {
        var authorimage = 'upload_file_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "pdf") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var fd = new FormData();
                    fd.append('upload_file', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadPdf',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                pdf = data.data;
                                 $('#' + previewid).text(pdf);
                                if (pdf != undefined) {
                                    $('#' + authorimage).val(pdf);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only PDF', 'error');
        }
    }
}


function add_epaper(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.paper_name != '') {
        if (blogThumbImage != undefined || data.id) {
            $.ajax({
                type: 'POST',
                url: base_url + "/add-update-e-paper",
                headers: {},
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(data),
                success: function (response) {
                    if (response.success) {
                        myToastr(response.message, 'success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        myToastr(response.message, 'error');
                    }
                }
            });
        } else {
            myToastr('Please select image', 'error');
        }
        
    } else {
        myToastr('Enter paper name', 'error');
    }
}




function getTranslationValues(id) {
    var data = {};
    data.id = id;

    $('#append').html('');

    $.ajax({
        type: 'POST',
        url: base_url + "/languages/translations/show",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data.data.length > 0) {
                    $('#content-key').text('update key :'+ response.data.keyword);
                    for(var c =0; c<response.data.data.length; c++){
                        $('#append').append(`
                            <input type="hidden" name="id[]" value=`+response.data.data[c].id+`
                            <div class="p-5 grid grid-cols-12 mt-5 gap-4 row-gap-3">
                                <div class="col-span-12 sm:col-span-12">
                                    <label>`+response.data.data[c].language_name+`</label>
                                    <input type="text" class="input w-full border mt-2 flex-1 focus" name="value[]" placeholder="value" value="`+response.data.data[c].value+`">
                                </div>
                            </div>`
                            );
                    }
                    setTimeout(()=>{
                        $('.focus').focus();
                    },500);
                }
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}

function getSources(id,source) {
    var data = {};
    data.category_id = id;
    $('#source').html('<option value="">All Source</option>');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: base_url + "/getFeeds",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status==true) {
                // var firstNew = '<option value="">All Source</option>';
                for(var c =0; c<response.data.length; c++){
                    if(source!=0){
                        var first = '<option value='+response.data[c].id+'';
                        if(source == response.data[c].id){
                            var second = " selected";
                        }else{
                            var second = "";
                        }
                        var third = '>'+response.data[c].rss_name+'</option>';
                        var final = first+''+second+''+third;
                        // $('#source').append(`` if(`+source+` == `+response.data[c].id+`)selected >`+response.data[c].rss_name+`</option>`);
                        $('#source').append(final);
                    }else{
                        $('#source').append(`<option value=`+response.data[c].id+`>`+response.data[c].rss_name+`</option>`);  
                    }
                }              
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}


function getCategoryTranslation(category_id,language_code){
    $('#category_name_'+category_id).val('');
    var data = {};
    data.category_id = category_id;
    data.language_code = language_code;
    $.ajax({
        type:'POST',
        url: base_url +'/get-category-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),

        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#category_name_'+category_id).val(response.data.name);
                }
            }
        }
    })
}

function translateCategory(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.category_id == '') {
        myToastr('something went wrong try to rfresh page !', 'error');
    }else if(data.language_code == ''){
        myToastr('select language', 'error');
    }else if(data.name == ''){
        myToastr('enter name', 'error');
    }else{
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-category",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }  
}



function getLiveNewsTranslation(live_news_id,language_code){
    $('#company_name_'+live_news_id).val('');
    $('#youtube_url_'+live_news_id).val('');
    var data = {};
    data.live_news_id = live_news_id;
    data.language_code = language_code;
    $.ajax({
        type:'POST',
        url: base_url +'/get-live-news-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#company_name_'+live_news_id).val(response.data.company_name);
                    $('#youtube_url_'+live_news_id).val(response.data.url);
                }
            }
        }
    })
}


function translateLiveNews(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.live_news_id == '') {
        myToastr('something went wrong try to rfresh page !', 'error');
    }else if(data.language_code == ''){
        myToastr('select language', 'error');
    }else if(data.company_name == ''){
        myToastr('enter company name', 'error');
    }else if(data.url == ''){
        myToastr('enter youtube url', 'error');
    }else{
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-live-news",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }  
}



function getEpaperTranslation(e_paper_id,language_code){
    $('#paper_name_'+e_paper_id).val('');
    $('#upload_file_'+e_paper_id+'_translate').val('');
    $('#translate_pdf_name_'+e_paper_id).html('No file selected');

    var data = {};
    data.e_paper_id = e_paper_id;
    data.language_code = language_code;
    $.ajax({
        type:'POST',
        url: base_url +'/get-e-paper-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#paper_name_'+e_paper_id).val(response.data.paper_name);
                    if (response.data.pdf_exist) {
                        $('#upload_file_'+e_paper_id+'_translate').val(response.data.pdf);
                        $tag = `<a href="`+response.data.pdf_file+`" target="_blank">view</a>`
                        $('#translate_pdf_name_'+e_paper_id).html($tag);
                    }
                }
            }
        }
    })
}

function translateEpaper(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.e_paper_id == '') {
        myToastr('something went wrong try to rfresh page !', 'error');
    }else if(data.language_code == ''){
        myToastr('select language', 'error');
    }else if(data.paper_name == ''){
        myToastr('enter paper name', 'error');
    }else{
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-e-paper",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }  
}



function getCmsTranslation(cms_id,language_code){
    $('#title').val('');
    CKEDITOR.instances['description'].setData('');
    var data = {};
    data.cms_id = cms_id;
    data.language_code = language_code;
    $.ajax({
        type:'POST',
        url: base_url +'/get-cms-page-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#title').val(response.data.title);
                    CKEDITOR.instances['description'].setData(response.data.description);
                }
            }
        }
    })
}


function translateCmsPage(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    if (data.title == '') {
        flag = false;
        myToastr('Enter title', 'error');
    }
    var desc = CKEDITOR.instances['description'].getData();
    data.description = desc;
    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-cms-page",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}


function getBlogTranslation(blog_id,language_code){
    $('#title').val('');
    $('#blogdescription').val('');
    $('#seo_title').val('');
    $('#seo_keyword').val('');
    $('#seo_description').val('');
    CKEDITOR.instances['blogdescription'].setData('');
    $('#tags').tagsinput('removeAll');
    $('#seo_tag').tagsinput('removeAll');

    var data = {};
    data.blog_id = blog_id;
    data.language_code = language_code;
    $.ajax({
        type:'POST',
        url: base_url +'/get-blog-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),

        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#title').val(response.data.title);
                    CKEDITOR.instances['blogdescription'].setData(response.data.description);
                    $('#seo_title').val(response.data.seo_title);
                    $('#seo_keyword').val(response.data.seo_keyword);
                    $('#seo_description').val(response.data.seo_description);
                    $('#tags').tagsinput('add', response.data.tags);
                    $('#seo_tag').tagsinput('add', response.data.seo_tag);
                }
            }
        }
    })
}


function translateBlog(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    var desc = CKEDITOR.instances['blogdescription'].getData();
    data.description = desc;
    if (data.title == '') {
        flag = false;
        myToastr('Enter title', 'error');
    }else if (data.description == '') {
        flag = false;
        myToastr('Enter description', 'error');
    }

    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-blog",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}



function convertToSlug(Text)
{
    var slug = Text
        .toLowerCase()
        .replace(/ /g,'-')
        .replace(/[^\w-]+/g,'')
        ;
    $('#slug').val(slug);
}


function getQuoteTranslation(quote_id,language_code){
    $('#quote_'+quote_id).val('');
    var data = {};
    data.quote_id = quote_id;
    data.language_code = language_code;
    $.ajax({
        type:'POST',
        url: base_url +'/get-quote-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#quote_'+quote_id).val(response.data.quote);
                   
                }
            }
        }
    })
}