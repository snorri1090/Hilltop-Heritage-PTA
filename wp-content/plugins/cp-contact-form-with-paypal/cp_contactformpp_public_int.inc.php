<?php if ( !defined('CP_AUTH_INCLUDE') ) { echo 'Direct access not allowed.';  exit; } ?>
<link href="<?php echo plugins_url('css/stylepublic.css', __FILE__); ?>" type="text/css" rel="stylesheet" /><link href="<?php echo plugins_url('css/cupertino/jquery-ui-1.8.20.custom.css', __FILE__); ?>" type="text/css" rel="stylesheet" />
<script type="text/javascript">
 function doValidate<?php echo $CP_CPP_global_form_count; ?>(form)
 {
    $dexQuery = jQuery.noConflict();
    document.cp_contactformpp_pform<?php echo $CP_CPP_global_form_count; ?>.cp_ref_page.value = document.location;
    <?php if (cp_contactformpp_get_option('cv_enable_captcha', CP_CONTACTFORMPP_DEFAULT_cv_enable_captcha,$id) != 'false') { ?> if ($dexQuery("#hdcaptcha_cp_contact_form_paypal_post<?php echo $CP_CPP_global_form_count; ?>").val() == '')
    {
        alert('<?php echo __('Please enter the captcha verification code.','cpcfwpp'); ?>');
        return false;
    }
    var result = $dexQuery.ajax({
        type: "GET",
        url: "<?php echo cp_contactformpp_get_site_url(); ?>?ps=<?php echo $CP_CPP_global_form_count; ?>"+String.fromCharCode(38)+"hdcaptcha_cp_contact_form_paypal_post="+$dexQuery("#hdcaptcha_cp_contact_form_paypal_post<?php echo $CP_CPP_global_form_count; ?>").val(),
        async: false
    }).responseText;
    if (result.indexOf("captchafailed") != -1)
    {
        $dexQuery("#captchaimg<?php echo $CP_CPP_global_form_count; ?>").attr('src', $dexQuery("#captchaimg<?php echo $CP_CPP_global_form_count; ?>").attr('src')+'&'+Date());
        alert('<?php echo __('Incorrect captcha code. Please try again.','cpcfwpp'); ?>');
        return false;
    }
    else <?php } ?>
    {
        document.getElementById("form_structure<?php echo $CP_CPP_global_form_count; ?>").value = '';    
        return true;
    }
 }
</script>
<form class="cpp_form" name="cp_contactformpp_pform<?php echo $CP_CPP_global_form_count; ?>" id="cp_contactformpp_pform<?php echo $CP_CPP_global_form_count; ?>" action="<?php get_site_url(); ?>" method="post" enctype="multipart/form-data" onsubmit="return doValidate<?php echo $CP_CPP_global_form_count; ?>(this);"><input type="hidden" name="cp_pform_psequence" value="<?php echo $CP_CPP_global_form_count; ?>" /><input type="hidden" name="cp_contactformpp_pform_process" value="1" /><input type="hidden" name="cp_contactformpp_id" value="<?php echo $id; ?>" /><input type="hidden" name="cp_ref_page" value="<?php esc_attr(cp_contactformpp_get_FULL_site_url); ?>" /><input type="hidden" name="form_structure<?php echo $CP_CPP_global_form_count; ?>" id="form_structure<?php echo $CP_CPP_global_form_count; ?>" size="180" value="<?php echo str_replace('"','&quot;',str_replace("\r","",str_replace("\n","",esc_attr(cp_contactformpp_cleanJSON(cp_contactformpp_translate_json(cp_contactformpp_get_option('form_structure', CP_CONTACTFORMPP_DEFAULT_form_structure,$id))))))); ?>" />
<div id="fbuilder">
  <div id="fbuilder<?php echo $CP_CPP_global_form_count; ?>">
      <div id="formheader<?php echo $CP_CPP_global_form_count; ?>"></div>
      <div id="fieldlist<?php echo $CP_CPP_global_form_count; ?>"></div>
  </div>
</div>    
<div id="cpcaptchalayer<?php echo $CP_CPP_global_form_count; ?>">
<?php if (count($codes)) { ?>
     <?php echo __('Coupon code','cpcfwpp'); ?>:<br />
     <input type="text" name="couponcode" value=""><br />
<?php } ?>
  <br />
<?php if (cp_contactformpp_get_option('cv_enable_captcha', CP_CONTACTFORMPP_DEFAULT_cv_enable_captcha,$id) != 'false') { ?>
  <?php echo __('Please enter the security code','cpcfwpp'); ?>:<br />
  <img src="<?php echo cp_contactformpp_get_site_url().'/?cp_contactformpp=captcha&ps='.$CP_CPP_global_form_count.'&width='.cp_contactformpp_get_option('cv_width', CP_CONTACTFORMPP_DEFAULT_cv_width,$id).'&height='.cp_contactformpp_get_option('cv_height', CP_CONTACTFORMPP_DEFAULT_cv_height,$id).'&letter_count='.cp_contactformpp_get_option('cv_chars', CP_CONTACTFORMPP_DEFAULT_cv_chars,$id).'&min_size='.cp_contactformpp_get_option('cv_min_font_size', CP_CONTACTFORMPP_DEFAULT_cv_min_font_size,$id).'&max_size='.cp_contactformpp_get_option('cv_max_font_size', CP_CONTACTFORMPP_DEFAULT_cv_max_font_size,$id).'&noise='.cp_contactformpp_get_option('cv_noise', CP_CONTACTFORMPP_DEFAULT_cv_noise,$id).'&noiselength='.cp_contactformpp_get_option('cv_noise_length', CP_CONTACTFORMPP_DEFAULT_cv_noise_length,$id).'&bcolor='.cp_contactformpp_get_option('cv_background', CP_CONTACTFORMPP_DEFAULT_cv_background,$id).'&border='.cp_contactformpp_get_option('cv_border', CP_CONTACTFORMPP_DEFAULT_cv_border,$id).'&font='.cp_contactformpp_get_option('cv_font', CP_CONTACTFORMPP_DEFAULT_cv_font,$id); ?>"  id="captchaimg<?php echo $CP_CPP_global_form_count; ?>" alt="security code" border="0"  />
  <br />
  <?php echo __('Security Code','cpcfwpp'); ?>:<br />
  <div class="dfield">
  <input type="text" size="20" name="hdcaptcha_cp_contact_form_paypal_post" id="hdcaptcha_cp_contact_form_paypal_post<?php echo $CP_CPP_global_form_count; ?>" value="" />
  <div class="error message" id="hdcaptcha_error<?php echo $CP_CPP_global_form_count; ?>" generated="true" style="display:none;position: absolute; left: 0px; top: 25px;"></div>
  </div>
  <br />
<?php } ?>
</div>
<div id="cp_subbtn<?php echo $CP_CPP_global_form_count; ?>" class="cp_subbtn"><?php echo __($button_label,'cpcfwpp'); ?></div>
</form>