$(function() {
    $('.form-account').on('submit', function() {
        var firstname = $('#acme_user_registration_firstname').val(),
            lastname  = $('#acme_user_registration_lastname').val(),
            email     = $('#acme_user_registration_email').val(),
            password  = $('#acme_user_registration_password').val(),
            contact   = $('#acme_user_registration_contactnum').val();

        if (firstname == '') {
            return false;
        } else if (lastname == '') {
            return false;
        } else if (email == '') {
            return false;
        } else if (!IsEmail(email)) {
            return false;
        } else if ($('.email-check').html() == 'Email already exist!') {
            return false;
        }
        return true;
    });

    $('input[type="text"]').each(function() {
        $(this).removeAttr('required');
    });
    $('input[type="email"], input[type="password"], select').removeAttr('required');

    numbersOnly('#acme_user_registration_contactnum');
    numbersOnly('#acme_user_registration_localcontact');
    specialCharAlphaNumeric2('#acme_user_registration_firstname');
    specialCharAlphaNumeric2('#acme_user_registration_lastname');
    jQuery(".form-account, .update-user-form").validationEngine();
    $(".changepass").colorbox({iframe:true, width:"40%", height:"75%"});

    specialCharAlphaNumeric2('.firstname');
    specialCharAlphaNumeric2('.lastname');
    numbersOnly('.contactnum');
    numbersOnly('#acme_user_registration_localcontact');
    jQuery(".update-user-form").validationEngine();
    $('select#acme_user_registration_roles').hide();
    $('select#acme_user_registration_roles').removeAttr( "multiple" );
    $(".txContactnum").text(function(i, text) {
        text = text.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
        return text;
    });

    updateForm();
});

function updateForm() {
    $('.fileImgUpload').hide();
    $(document.body).on('click', '.edit-user', function(e) {
        var getID = $(this).attr('id');

        $('.fields span').hide();
        $('.txImage').show();
        $('.fields .user-textfield, .fields .user-button, .fileImgUpload, .uploadify-button-text').show();
        e.preventDefault();
    });

    $(document.body).on('submit', '.update-user-form', function(e) {
        var url         = $(this).attr('action'),
            firstname   = $('.firstname').val(),
            lastname    = $('.lastname').val(),
            email       = $('.email').val(),
            roles       = $('.roles').val(),
            getID       = $(this).data('id'),
            data        = {
                firstname : firstname,
                lastname : lastname,
                email : email,
                roles : roles
            };

        if ( firstname === '' ) {
            return false;
        } else if ( lastname === '' ) {
            return false;
        } else if ( email === '' || !IsEmail(email) ) {
            return false;
        } else if (!IsEmail(email)) {
            return false;
        } else if ($('.email-check').html() == 'Email already exist!') {
            return false;
        } else {
            $.post(url, data, function() {
                showNotification({
                    message: "The Account has been updated.",
                    type: "success",
                    autoClose: true,
                    duration: 5
                });
            });

            if (roles == 'ROLE_ADMIN' || roles == 'ROLE_SUPER_ADMIN') {
                roles = 'Admin';
            } else if (roles == 'ROLE_MANAGER') {
                roles = 'Manager';
            } else if (roles == 'ROLE_ACCOUNTANT') {
                roles = 'Accountant';
            }

            $('.txFirstname').html(firstname);
            $('.txLastname').html(lastname);
            $('.txEmail').html(email);
            $('.list-users tr#' + getID + ' td:first-child').html(firstname + ' ' + lastname);
            $('.list-users tr#' + getID + ' td:last-child').html(email);

            $('.fields span, .fileImgUpload').show();
            $('.fields .user-textfield, .fields .user-button, .email-check, .fileImgUpload').hide();
            $('.textContacterror').html('');
        }
        e.preventDefault();
    });
}