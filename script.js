jQuery(document).ready(function($){

	// fade away update messages
	setTimeout(function(){
		$('.fade').fadeOut('slow');
	}, 5000);

    // spectrum color picker
    $("#wpcustomize_admin_bgcolor, #wpcustomize_admin_linkcolor, #wpcustomize_admin_linkhovercolor").spectrum({
        showPalette: true,
        showInput: true,
        preferredFormat: "hex3",
        palette: [
            ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
            ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
            ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
            ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
            ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
            ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
            ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
            ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"],
            ["#b6b6b6","#4c99ef"]
        ]
    });

    // media library upload file input - logo image
    $('#wpcustomize_admin_logo_image_url_button').click(function() {
        formfield = $('#wpcustomize_admin_logo_image_url').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    window.send_to_editor = function(html) {
        imgurl = $('img',html).attr('src');
        $('#wpcustomize_admin_logo_image_url').val(imgurl);
        tb_remove();
    }

    // media library upload file input - page background
    $('#wpcustomize_admin_login_background_url_button').click(function() {
        formfield = $('#wpcustomize_admin_login_background_url').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    window.send_to_editor = function(html) {
        imgurl = $('img',html).attr('src');
        $('#wpcustomize_admin_login_background_url').val(imgurl);
        tb_remove();
    }

});
