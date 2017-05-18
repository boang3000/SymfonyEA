$(function() {
    var container = document.getElementById('pl-container'),
        fileList = document.getElementById('filelist');

    var uploader = new plupload.Uploader({
        runtimes: 'html5',
        browse_button: 'pickfiles',
        drop_element : 'drop-target',
        container: container,
        url: plupParams.url.upload,
        filters: {
            max_file_size: '10mb',
            mime_types: [
                {title: 'Image files', extensions: 'jpg,gif,png,pdf'}
            ]
        },
        init: {
            PostInit: function () {
                fileList.innerHTML = '';
            },
            BeforeUpload: function (up, file) {
                up.settings.multipart_params = {
                    token : $('input[name="token"]').val(),
                    formtoken : $('.formtoken').val(),
                    pagenumber : $('input.pagenumber').val()
                };
            },
            FilesAdded: function (up, files) {
                fileList.innerHTML = '';

                plupload.each(files, function (file) {
                    fileList.innerHTML += '<div id="' + file.id + '" class="file-info">' + file.name + ' <span></span></div>';
                });

                uploader.start();
            },
            UploadProgress: function (up, file) {
                if (document.getElementById(file.id)) {
                    document.getElementById(file.id).getElementsByTagName('span')[0].innerHTML = '<progress max="100" value="' + file.percent + '">' + "</progress>";
                }
            },
            FileUploaded: function(up, file, info) {
                var response = $.parseJSON(info.response);
                if (!response.success) {
                    fileList.innerHTML = response.msg;
                    return;
                }

                var data = response.data,
                    owl = $(".owl-carousel").data('owlCarousel'),
                    content = "<div class=\"item imglist-" + data.id + "\">";

                if (plupParams.data) {
                    content += "<select data-id=\"" + data.id + "\" name=\"imageVendor\" class=\"cs-select cs-skin-border vendor-select2 validate[required]\">"
                    content += "<option></option>" + plupParams.data.vendorOptions + "</select>"
                }

                content += "<span class=\"close\" data-id=\""
                content += data.id +"\">&times;</span><img class=\"lazyOwl\" data-src=\"" + plupParams.url.imgs + data.fileName
                content += "\" /></div>";

                owl.addItem(content, 0);

                if (plupParams.data) {
                    $("select.vendor-select2").select2({placeholder: 'Vendor'});
                }

                $("#" + file.id).remove();
            },
            Error: function (up, err) {
                fileList.innerHTML = "\nError #" + err.code + ": " + err.message;
            }
        }
    });
    uploader.init();

    $(document).on('click', '.close', function(e) {
        var id = $(this).data('id'),
            index = 0,
            owl = $(".owl-carousel").data('owlCarousel'),
            images = $('.lazyOwl');

        for (var i = 0; i < images.length; i++) {
            if ($(images[i]).data('id') == id) {
                index = i;
                break;
            }
        }

        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                type: 'POST',
                url: plupParams.url.delete,
                data: {
                    id : id
                },
                success: function(data, textStatus, jQxhr) {
                    if (data.success) {
                        owl.removeItem(index);
                    }
                },
                error: function(jqXhr, textStatus, errorThrown) {
                }
            });
        }
        e.preventDefault();
    });

    $(document).on('change', '.vendor-select2', function(e) {
        var me = $(this);

        var id = me.data('id'),
            val = me.val();

        $.ajax({
            type: 'POST',
            url: plupParams.url.uploadVendor,
            data: {
                id : id,
                idVendor: val
            },
            success: function(data, textStatus, jQxhr) {
                if (data.success) {
                    alert('Vendor successfully saved.');
                }
            },
            error: function(jqXhr, textStatus, errorThrown) {
            }
        });
    });
});