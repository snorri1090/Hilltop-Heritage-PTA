<?php

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}

$nonce = wp_create_nonce( 'cfwpp_update_actions_post' );

if (!defined('CP_CONTACTFORMPP_ID'))
    define ('CP_CONTACTFORMPP_ID',intval($_GET["cal"]));

define('CP_CONTACTFORMPP_DEFAULT_fp_from_email', get_the_author_meta('user_email', get_current_user_id()) );
define('CP_CONTACTFORMPP_DEFAULT_fp_destination_emails', CP_CONTACTFORMPP_DEFAULT_fp_from_email);

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['cp_contactformpp_post_options'] ) )
    echo "<div id='setting-error-settings_updated' class='updated settings-error'> <p><strong>Settings saved.</strong></p></div>";

$scriptmethod = cp_contactformpp_get_option('script_load_method','0');

?>
<script type="text/javascript">
 function displaymorein(id)
 {
    document.getElementById("cpcfppmorein"+id).style.display="";
    document.getElementById("cpcfppmoreinlink"+id).style.display="none";
 }
 function displaylessin(id)
 {
    document.getElementById("cpcfppmorein"+id).style.display="none";
    document.getElementById("cpcfppmoreinlink"+id).style.display="";
 }
</script>
<div class="wrap">
<h1>PayPal Form</h1>

<?php if ($scriptmethod == '1') { ?>
<script type='text/javascript' src='../wp-content/plugins/cp-contact-form-with-paypal/js/jquery-ui-1.8.20.custom.min.js'></script>
<script type='text/javascript' src='../wp-content/plugins/cp-contact-form-with-paypal/js/jQuery.stringify.js'></script>
<script type='text/javascript' src='../wp-content/plugins/cp-contact-form-with-paypal/js/fbuilder.jquery.js'></script>
<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css" type="text/css" rel="stylesheet" />   
<?php } ?>

<input type="button" name="backbtn" value="Back to items list..." onclick="document.location='admin.php?page=cp_contact_form_paypal.php';">
<br /><br />

<form method="post" action="" name="cpformconf"> 
<input name="cp_contactformpp_post_options" type="hidden" value="1" />
<input name="rsave" type="hidden" value="<?php echo $nonce; ?>" />
<input name="cp_contactformpp_id" type="hidden" value="<?php echo CP_CONTACTFORMPP_ID; ?>" />

   
<div id="normal-sortables" class="meta-box-sortables">

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Form Processing / Email Settings</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">"From" email</th>
        <td><input required type="email" name="fp_from_email" size="40" value="<?php echo esc_attr(cp_contactformpp_get_option('fp_from_email', CP_CONTACTFORMPP_DEFAULT_fp_from_email)); ?>" /></td>
        </tr>             
        <tr valign="top">
        <th scope="row">Destination emails (comma separated)</th>
        <td><input required type="text" name="fp_destination_emails" size="40" value="<?php echo esc_attr(cp_contactformpp_get_option('fp_destination_emails', CP_CONTACTFORMPP_DEFAULT_fp_destination_emails)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email subject</th>
        <td><input type="text" name="fp_subject" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('fp_subject', CP_CONTACTFORMPP_DEFAULT_fp_subject)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Include additional information?</th>
        <td>
          <?php $option = cp_contactformpp_get_option('fp_inc_additional_info', CP_CONTACTFORMPP_DEFAULT_fp_inc_additional_info); ?>
          <select name="fp_inc_additional_info">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Thank you page (after sending the message)</th>
        <td><input required type="text" name="fp_return_page" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('fp_return_page', CP_CONTACTFORMPP_DEFAULT_fp_return_page)); ?>" /></td>
        </tr>          
        <tr valign="top">
        <th scope="row">Error page (if the payment process isn't compelted)</th>
        <td><input type="text" name="fp_error_page" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('fp_error_page', '')); ?>" />
            <br /><em>Leave empty to send the user back to the page that contains the form.</em>
        </td>
        </tr>          
        <tr valign="top">
        <th scope="row">Email format?</th>
        <td>
          <?php $option = cp_contactformpp_get_option('fp_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format); ?>
          <select name="fp_emailformat">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>>Plain Text (default)</option>
          </select>
        </td>
        </tr>        
        <tr valign="top">
        <th scope="row">Message</th>
        <td><textarea type="text" name="fp_message" rows="6" cols="80"><?php echo esc_attr(cp_contactformpp_get_option('fp_message', CP_CONTACTFORMPP_DEFAULT_fp_message)); ?></textarea></td>
        </tr>                                                               
     </table>  
  </div>    
 </div>   
 
 
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Paypal Payment Configuration</span></h3>
  <div class="inside">

    <table class="form-table">
    
      
        <tr valign="top">
        <th scope="row">PayPal Integration</th>
        <td><select name="enable_paypal" onchange="cfpp_update_pp_payment_selection();">             
             <option value="1" <?php if (cp_contactformpp_get_option('enable_paypal','1') == '1') echo 'selected'; ?>>PayPal Standard</option>              
             <option value="101" <?php if (cp_contactformpp_get_option('enable_paypal','1') == '101') echo 'selected'; ?>>PayPal Express + PayPal Credit</option> 
            </select>
            <em>Note: Leave the default option PayPal Standard if you aren't sure.</em>
            
            <div id="cfpp_paypal_options_express"  style="display:none;margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:450px;">
              <table>
               <tr valign="top">        
               <th scope="row">PayPal Express (NVP) - API UserName</th>
               <td><input type="text" name="paypalexpress_api_username" size="20" value="<?php echo esc_attr(cp_contactformpp_get_option('paypalexpress_api_username','')); ?>" /></td>
               </tr>   
               <tr valign="top">        
               <th scope="row">PayPal Express (NVP) - API Password</th>
               <td><input type="text" name="paypalexpress_api_password" size="40" value="<?php echo esc_attr(cp_contactformpp_get_option('paypalexpress_api_password','')); ?>" /></td>
               </tr>   
               <tr valign="top">        
               <th scope="row">PayPal Express (NVP) - API Signature</th>
               <td><input type="text" name="paypalexpress_api_signature" size="20" value="<?php echo esc_attr(cp_contactformpp_get_option('paypalexpress_api_signature','')); ?>" /></td>
               </tr>      
              </table>    
              <div id="cfpp_paypal_options_label_express" style="display:none;margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                Label for the "<strong>Pay with PayPal Credit (if enabled)</strong>" option:<br />
                <input type="text" name="enable_paypal_expresscredit_yes" size="40" style="width:250px;" value="<?php echo esc_attr(cp_contactformpp_get_option('enable_paypal_expresscredit_yes',CP_CONTACTFORMPP_DEFAULT_PAYPAL_EXPRESSCREDIT_YES)); ?>" />
                <br />
                Label for the "<strong>Pay with PayPal Express (classic)</strong>" option:<br />
                <input type="text" name="enable_paypal_expresscredit_no" size="40" style="width:250px;"  value="<?php echo esc_attr(cp_contactformpp_get_option('enable_paypal_expresscredit_no',CP_CONTACTFORMPP_DEFAULT_PAYPAL_EXPRESSCREDIT_No)); ?>" />                
              </div>        
            </div>
                        
        </td>
        </tr>       
    
        <tr valign="top">        
        <th scope="row"><strong>Paypal email</strong></th>
        <td><input required type="email" name="paypal_email" size="40" value="<?php echo esc_attr(cp_contactformpp_get_option('paypal_email',CP_CONTACTFORMPP_DEFAULT_PAYPAL_EMAIL)); ?>" />
          <br />
          <em>Important! Enter here the email address linked to your PayPal account.</em>
        </td>
        </tr>               
         
        <tr valign="top">
        <th scope="row">Request cost</th>
        <td><input required type="text" name="request_cost" value="<?php echo esc_attr(cp_contactformpp_get_option('request_cost',CP_CONTACTFORMPP_DEFAULT_COST)); ?>" /></td>
        </tr>             
        
        <tr valign="top">
        <th scope="row">Currency</th>
        <td><input required type="text" name="currency" value="<?php echo esc_attr(cp_contactformpp_get_option('currency',CP_CONTACTFORMPP_DEFAULT_CURRENCY)); ?>" /><br />
          <em>Ex: USD, EUR, GBP, AUD, CAD, MXP ... be sure to enter a valid currency code.</em></td>
        </tr>      
        
        <tr valign="top">
        <th scope="row">Taxes (percent)</th>
        <td><input type="text" name="request_taxes" value="<?php echo esc_attr(cp_contactformpp_get_option('request_taxes','0')); ?>" /></td>
        </tr>    
        
        <tr valign="top">
        <th scope="row">Paypal product name</th>
        <td><input required type="text" name="paypal_product_name" size="50" value="<?php echo esc_attr(cp_contactformpp_get_option('paypal_product_name',CP_CONTACTFORMPP_DEFAULT_PRODUCT_NAME)); ?>" /></td>
        </tr>        
        
        <tr valign="top">
        <th scope="row">Paypal language</th>
        <td><input type="text" name="paypal_language" value="<?php echo esc_attr(cp_contactformpp_get_option('paypal_language',CP_CONTACTFORMPP_DEFAULT_PAYPAL_LANGUAGE)); ?>" /></td>
        </tr>         
        
        <tr valign="top">        
        <th scope="row">Payment frequency</th>
        <td><select name="paypal_recurrent" id="paypal_recurrent" onchange="cfwpp_update_recurrent();">
             <option value="0" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '0' || 
                                         cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == ''
                                        ) echo 'selected'; ?>>One time payment (default option, user is billed only once)</option>
             <option value="0.4" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '0.4') echo 'selected'; ?>>Bill the user every 1 week</option>
             <option value="1" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '1') echo 'selected'; ?>>Bill the user every 1 month</option> 
             <option value="2" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '2') echo 'selected'; ?>>Bill the user every 2 months</option> 
             <option value="3" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '3') echo 'selected'; ?>>Bill the user every 3 months</option> 
             <option value="6" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '6') echo 'selected'; ?>>Bill the user every 6 months</option> 
             <option value="12" <?php if (cp_contactformpp_get_option('paypal_recurrent',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '12') echo 'selected'; ?>>Bill the user every 12 months</option>              
            </select>
            <div id="cfwpp_setupfee" style="width:350px;margin-top:5px;padding:5px;background-color:#ddddff;display:none;border:1px dotted black;">
             First period price (ex: include setup fee here if any or 0 for a free initial period):<br />
             <input type="text" name="paypal_recurrent_setup" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('paypal_recurrent_setup','')); ?>" />
             <br />
             First period lenght (if any):<br />
             <select name="paypal_recurrent_fp" id="paypal_recurrent_fp">             
             <option value="0" <?php if (cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '' || cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '0') echo 'selected'; ?>>no first period</option>
             <option value="0.4" <?php if (cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '0.4') echo 'selected'; ?>>1 week</option>
             <option value="1" <?php if (cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '1') echo 'selected'; ?>>1 month</option> 
             <option value="3" <?php if (cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '3') echo 'selected'; ?>>3 months</option> 
             <option value="6" <?php if (cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '6') echo 'selected'; ?>>6 months</option> 
             <option value="12" <?php if (cp_contactformpp_get_option('paypal_recurrent_fp',CP_CONTACTFORMPP_DEFAULT_PAYPAL_RECURRENT) == '12') echo 'selected'; ?>>12 months</option> 
            </select>             
            <br />             
            <hr />
             <?php $selnum = esc_attr(cp_contactformpp_get_option('paypal_recurrent_times','0')); ?>
             Number of times that subscription payments recur (keep "Unlimited" in most cases):<br />
             <select name="paypal_recurrent_times">
                <option value="0" <?php if ($selnum == '0') echo 'selected'; ?>>Unlimited</option>
                <?php for ($kt=2; $kt<=52; $kt++) { ?>
                  <option value="<?php echo $kt; ?>" <?php if ($selnum == $kt."") echo 'selected'; ?>><?php echo $kt; ?> times</option>
                <?php } ?>
             </select>
            </div> 
            <script type="text/javascript">
              function cfwpp_update_recurrent() {
                var f = document.getElementById("paypal_recurrent");
                  if (f.options[f.options.selectedIndex].value != '0')
                      document.getElementById("cfwpp_setupfee").style .display = "";
                  else
                      document.getElementById("cfwpp_setupfee").style .display = "none";    
              } 
              cfwpp_update_recurrent();
            </script>
        </td>        
        </tr>            
        
        <tr valign="top">
        <th scope="row">Request address at PayPal</th>
        <td><select name="request_address">
             <option value="0" <?php if (cp_contactformpp_get_option('request_address','0') != '1') echo 'selected'; ?>>No</option> 
             <option value="1" <?php if (cp_contactformpp_get_option('request_address','0') == '1') echo 'selected'; ?>>Yes</option> 
            </select>
        </td>
        </tr>    
        
        <tr valign="top">        
        <th scope="row">Paypal Mode</th>
        <td><select name="paypal_mode">
             <option value="production" <?php if (cp_contactformpp_get_option('paypal_mode',CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE) != 'sandbox') echo 'selected'; ?>>Production - real payments processed</option> 
             <option value="sandbox" <?php if (cp_contactformpp_get_option('paypal_mode',CP_CONTACTFORMPP_DEFAULT_PAYPAL_MODE) == 'sandbox') echo 'selected'; ?>>SandBox - PayPal testing sandbox area</option> 
            </select>
            <br />
           <em> * Note that if you are testing it in a localhost site the PayPal IPN notification won't reach to your website. Related FAQ entry:
            <a href="https://cfpaypal.dwbooster.com/faq#q734">https://cfpaypal.dwbooster.com/faq#q734</a></em>
        </td>        
        </tr>
        
        <tr valign="top">        
        <th scope="row">Enable donation layout?</th>
        <td><select name="donationlayout">
             <option value="0" <?php if (cp_contactformpp_get_option('donationlayout','') != '1') echo 'selected'; ?>>No</option> 
             <option value="1" <?php if (cp_contactformpp_get_option('donationlayout','') == '1') echo 'selected'; ?>>Yes</option> 
            </select>            
        </tr>                       
        
        <tr valign="top">
        <th scope="row" colspan="2">------- The following set of fields are only partially available in this version ------</th>
        </tr>
        
        <tr valign="top">        
        <th scope="row">Enable Paypal Payments?</th>
        <td>
          <div id="cpcfppmoreinlink1"><input type="checkbox" readonly disabled="disabled" name="enable_paypal2" size="40" value="1" checked /> &nbsp; [<a href="javascript:displaymorein(1);">+ more information</a>]</div>
          <div id="cpcfppmorein1" style="display:none;border:1px solid black;background-color:#ffffcc;padding:10px;">
           <p>Note: The <a href="https://cfpaypal.dwbooster.com/download">pro version</a> works also without PayPal to convert the form in a general purpose form.</p>
           [<a href="javascript:displaylessin(1);">- less information</a>]
          </div>
        </td>
        </tr>          
        
        <tr valign="top">        
        <th scope="row">Automatically identify prices on dropdown and checkboxes?</th>
        <td>            
             
            <div id="cpcfppmoreinlink2">N/A &nbsp; [<a href="javascript:displaymorein(2);">+ more information</a>]</div>
            <div id="cpcfppmorein2" style="display:none;border:1px solid black;background-color:#ffffcc;padding:10px;">       
             <p>Note: This setting applies only for the <a href="https://cfpaypal.dwbooster.com/download">pro version</a> that supports multiplem field types.</p>
             <p>If marked, any price in the selected checkboxes, radiobuttons and dropdown fields will be added to the above request cost. 
                Prices will be identified if are entered in the format $NNNN.NN, example: $30 , $24.99 and also $1,499.99. Also works with the GBP "&pound;" and EUR "&euro;" signs.</p>
             <p>For example, you can create a drop-down/select field with these options:
             <br /><br />
             &nbsp; - 1 hour tutoring for $30<br />
             &nbsp; - 2 hours tutoring for $60<br />
             &nbsp; - 3 hours tutoring for $90<br />
             &nbsp; - 4 hours tutoring for $120
             </p>
             <p>... and put the basic request cost to 0. After submission the price sent to PayPal will be the total sum of the selected options.</p>
             [<a href="javascript:displaylessin(2);">- less information</a>]
            </div>
        </td>
        </tr>        
        
        <tr valign="top">
        <th scope="row">Use a specific field from the form for the payment amount</th>
        <td>
            <div id="cpcfppmoreinlink3"><select id="paypal_price_field" name="paypal_price_field" def="<?php echo esc_attr(cp_contactformpp_get_option('paypal_price_field', '')); ?>"></select> &nbsp; [<a href="javascript:displaymorein(3);">+ more information</a>]</div>
            <div id="cpcfppmorein3" style="display:none;border:1px solid black;background-color:#ffffcc;padding:10px;">
             <p>If selected, any price in the selected field will be added to the above request cost. Use this field for example for having an open donation amount.</p>
             <p>This feature is more useful in the <a href="https://cfpaypal.dwbooster.com/download">pro version</a> since it supports adding new custom fields.</p>
             [<a href="javascript:displaylessin(3);">- less information</a>]
            </div>
        </td>
        </tr>            
        
        <tr valign="top">        
        <th scope="row">When should be sent the notification-confirmation emails?</th>
        <td>
            <div id="cpcfppmoreinlink4"><select name="paypal_notiemails">
             <option value="0" <?php if (cp_contactformpp_get_option('paypal_notiemails','0') != '0') echo 'selected'; ?>>When paid: AFTER receiving the PayPal payment</option>             
            </select> &nbsp; [<a href="javascript:displaymorein(4);">+ more information</a>]</div>
            <div id="cpcfppmorein4" style="display:none;border:1px solid black;background-color:#ffffcc;padding:10px;">             
             <p>The <a href="https://cfpaypal.dwbooster.com/download">pro version</a> includes these options:</p>
             <p>
               &nbsp; &nbsp; - When paid: AFTER receiving the PayPal payment.<br />
               &nbsp; &nbsp; - Always: BEFORE receiving the PayPal payment.
             </p>
             [<a href="javascript:displaylessin(4);">- less information</a>]
            </div>
        </td>
        </tr>          
        
                                    
        
        <tr valign="top">        
        <th scope="row">A $0 amount to pay means:</th>
        <td>
            <div id="cpcfppmoreinlink6"><select name="paypal_zero_payment">
             <option value="0" <?php if (cp_contactformpp_get_option('paypal_zero_payment',CP_CONTACTFORMPP_DEFAULT_PAYPAL_ZERO_PAYMENT) != '1') echo 'selected'; ?>>Let the user enter any amount at PayPal (ex: for a donation)</option>              
            </select> &nbsp; [<a href="javascript:displaymorein(6);">+ more information</a>]</div>
            <div id="cpcfppmorein6" style="display:none;border:1px solid black;background-color:#ffffcc;padding:10px;">             
             <p>The <a href="https://cfpaypal.dwbooster.com/download">pro version</a> includes these options:</p>
             <p>
               &nbsp; &nbsp; - Let the user enter any amount at PayPal (ex: for a donation).<br />
               &nbsp; &nbsp; - Don't require any payment. Form is submitted skiping the PayPal page.
             </p>
             [<a href="javascript:displaylessin(6);">- less information</a>]
            </div>
        </td>
        </tr>                         
               
        
        <tr valign="top">
        <th scope="row">Discount Codes</th>
        <td> 
           <em>N/A - This feature is available in the <a href="https://cfpaypal.dwbooster.com/download">pro version</a>.</em>
        </td>
        </tr>  
                   
     </table>  

  </div>    
 </div>    
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Form Builder</span></h3>
  <div class="inside">   

     <em>Note: This free version includes a classic predefined contact form. The form builder for a total form customization
         is available in the <a href="https://cfpaypal.dwbooster.com/download">pro version</a>.</em>
        <br /><br />
     <input type="hidden" name="form_structure" id="form_structure" size="180" value="<?php echo str_replace('"','&quot;',str_replace("\r","",str_replace("\n","",esc_attr(cp_contactformpp_cleanJSON(cp_contactformpp_get_option('form_structure', CP_CONTACTFORMPP_DEFAULT_form_structure)))))); ?>" />
     
     <link href="<?php echo plugins_url('css/style.css', __FILE__); ?>" type="text/css" rel="stylesheet" />   
     <link href="<?php echo plugins_url('css/cupertino/jquery-ui-1.8.20.custom.css', __FILE__); ?>" type="text/css" rel="stylesheet" />   
        
     <script>
         $contactFormPPQuery = jQuery.noConflict();
         $contactFormPPQuery(document).ready(function() {
            var f = $contactFormPPQuery("#fbuilder").fbuilder();
            f.fBuild.loadData("form_structure");
            
            $contactFormPPQuery("#saveForm").click(function() {       
                f.fBuild.saveData("form_structure");
            });  
                 
            $contactFormPPQuery(".itemForm").click(function() {
     	       f.fBuild.addItem($contactFormPPQuery(this).attr("id"));
     	   });  
          
           $contactFormPPQuery( ".itemForm" ).draggable({revert1: "invalid",helper: "clone",cursor: "move"});
     	   $contactFormPPQuery( "#fbuilder" ).droppable({
     	       accept: ".button",
     	       drop: function( event, ui ) {
     	           f.fBuild.addItem(ui.draggable.attr("id"));				
     	       }
     	   });
     		    
         });
                    
        
        
        function generateCaptcha()
        {            
           var d=new Date();
           var f = document.cpformconf;    
           var qs = "&width="+f.cv_width.value;
		   var cv_background = f.cv_background.value;
		   cv_background = cv_background.replace('#','');
		   var cv_border = f.cv_border.value;
		   cv_border = cv_border.replace('#','');
           qs += "&height="+f.cv_height.value;
           qs += "&letter_count="+f.cv_chars.value;
           qs += "&min_size="+f.cv_min_font_size.value;
           qs += "&max_size="+f.cv_max_font_size.value;
           qs += "&noise="+f.cv_noise.value;
           qs += "&noiselength="+f.cv_noise_length.value;
           qs += "&bcolor="+cv_background;
           qs += "&border="+cv_border;
           qs += "&font="+f.cv_font.options[f.cv_font.selectedIndex].value;
           qs += "&rand="+d;
           
           document.getElementById("captchaimg").src= "<?php echo cp_contactformpp_get_site_url(true); ?>/?cp_contactformpp=captcha"+qs;
        }

        function cfpp_update_pp_payment_selection() 
        {
           var f = document.cpformconf;
           var ppoption = f.enable_paypal.options[f.enable_paypal.selectedIndex].value;     
           
           document.getElementById("cfpp_paypal_options_express").style.display = "none";
           
           if (ppoption == '100' || ppoption == '101')
               document.getElementById("cfpp_paypal_options_express").style.display = "";
        }   
        
        cfpp_update_pp_payment_selection(); 

     </script>
     
     <div style="background:#fafafa;width:780px;" class="form-builder">
     
         <div class="column width50">
             <div id="tabs">
     			<ul>
     				<li><a href="#tabs-1">Add a Field</a></li>
     				<li><a href="#tabs-2">Field Settings</a></li>
     				<li><a href="#tabs-3">Form Settings</a></li>
     			</ul>
     			<div id="tabs-1">
     			    
     			</div>
     			<div id="tabs-2"></div>
     			<div id="tabs-3"></div>
     		</div>	
         </div>
         <div class="columnr width50 padding10" id="fbuilder">
             <div id="formheader"></div>
             <div id="fieldlist"></div>
             <div class="button" id="saveForm">Save Form</div>
         </div>
         <div class="clearer"></div>
         
     </div>        
     
  </div>    
 </div>    
   
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Submit Button</span></h3>
  <div class="inside">   
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Submit button label (text):</th>
        <td><input type="text" name="vs_text_submitbtn" size="40" value="<?php $label = esc_attr(cp_contactformpp_get_option('vs_text_submitbtn', 'Submit')); echo ($label==''?'Submit':$label); ?>" /></td>
        </tr>    
        <tr valign="top">
        <td colspan="2"> - The  <em>class="pbSubmit"</em> can be used to modify the button styles. <br />
        - The styles can be applied into any of the CSS files of your theme or into the CSS file <em>"cp-contact-form-with-paypal\css\stylepublic.css"</em>. <br />
        - For further modifications the submit button is located at the end of the file <em>"cp_contactformpp_public_int.inc.php"</em>.<br />
        - For general CSS styles modifications to the form and samples <a href="https://cfpaypal.dwbooster.com/faq#q82" target="_blank">check this FAQ</a>.
        </tr>
     </table>
  </div>    
 </div> 
 


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Validation Settings</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top" style="display:none;">
        <th scope="row">Use Validation?</th>
        <td>
          <?php $option = cp_contactformpp_get_option('vs_use_validation', CP_CONTACTFORMPP_DEFAULT_vs_use_validation); ?>
          <select name="vs_use_validation">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <!--<option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>-->
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">"is required" text:</th>
        <td><input type="text" name="vs_text_is_required" size="40" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_is_required', CP_CONTACTFORMPP_DEFAULT_vs_text_is_required)); ?>" /></td>
        </tr>             
         <tr valign="top">
        <th scope="row">"is email" text:</th>
        <td><input type="text" name="vs_text_is_email" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_is_email', CP_CONTACTFORMPP_DEFAULT_vs_text_is_email)); ?>" /></td>
        </tr>       
        <tr valign="top">
        <th scope="row">"is valid captcha" text:</th>
        <td><input type="text" name="cv_text_enter_valid_captcha" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_text_enter_valid_captcha', CP_CONTACTFORMPP_DEFAULT_cv_text_enter_valid_captcha)); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">"is valid date (mm/dd/yyyy)" text:</th>
        <td><input type="text" name="vs_text_datemmddyyyy" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_datemmddyyyy', CP_CONTACTFORMPP_DEFAULT_vs_text_datemmddyyyy)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"is valid date (dd/mm/yyyy)" text:</th>
        <td><input type="text" name="vs_text_dateddmmyyyy" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_dateddmmyyyy', CP_CONTACTFORMPP_DEFAULT_vs_text_dateddmmyyyy)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"is number" text:</th>
        <td><input type="text" name="vs_text_number" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_number', CP_CONTACTFORMPP_DEFAULT_vs_text_number)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"only digits" text:</th>
        <td><input type="text" name="vs_text_digits" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_digits', CP_CONTACTFORMPP_DEFAULT_vs_text_digits)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"under maximum" text:</th>
        <td><input type="text" name="vs_text_max" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_max', CP_CONTACTFORMPP_DEFAULT_vs_text_max)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"over minimum" text:</th>
        <td><input type="text" name="vs_text_min" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('vs_text_min', CP_CONTACTFORMPP_DEFAULT_vs_text_min)); ?>" /></td>
        </tr>             
        
     </table>  
  </div>    
 </div>   
 
 
 
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Email Copy to User</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Send confirmation/thank you message to user?</th>
        <td>
          <?php $option = cp_contactformpp_get_option('cu_enable_copy_to_user', CP_CONTACTFORMPP_DEFAULT_cu_enable_copy_to_user); ?>
          <select name="cu_enable_copy_to_user">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Email field on the form</th>
        <td><select id="cu_user_email_field" name="cu_user_email_field" def="<?php echo esc_attr(cp_contactformpp_get_option('cu_user_email_field', CP_CONTACTFORMPP_DEFAULT_cu_user_email_field)); ?>"></select></td>
        </tr>             
        <tr valign="top">
        <th scope="row">Email subject</th>
        <td><input type="text" name="cu_subject" size="70" value="<?php echo esc_attr(cp_contactformpp_get_option('cu_subject', CP_CONTACTFORMPP_DEFAULT_cu_subject)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email format?</th>
        <td>
          <?php $option = cp_contactformpp_get_option('cu_emailformat', CP_CONTACTFORMPP_DEFAULT_email_format); ?>
          <select name="cu_emailformat">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>>Plain Text (default)</option>
          </select>
        </td>
        </tr>  
        <tr valign="top">
        <th scope="row">Message</th>
        <td><textarea type="text" name="cu_message" rows="6" cols="80"><?php echo esc_attr(cp_contactformpp_get_option('cu_message', CP_CONTACTFORMPP_DEFAULT_cu_message)); ?></textarea></td>
        </tr>        
     </table>  
  </div>    
 </div>  
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Captcha Verification</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Use Captcha Verification?</th>
        <td colspan="5">
          <?php $option = cp_contactformpp_get_option('cv_enable_captcha', CP_CONTACTFORMPP_DEFAULT_cv_enable_captcha); ?>
          <select name="cv_enable_captcha">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>
        </td>
        </tr>
        
        <tr valign="top">
         <th scope="row">Width:</th>
         <td><input type="number" name="cv_width" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_width', CP_CONTACTFORMPP_DEFAULT_cv_width)); ?>"  onblur="generateCaptcha();"  /></td>
         <th scope="row">Height:</th>
         <td><input type="number" name="cv_height" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_height', CP_CONTACTFORMPP_DEFAULT_cv_height)); ?>" onblur="generateCaptcha();"  /></td>
         <th scope="row">Chars:</th>
         <td><input type="number" name="cv_chars" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_chars', CP_CONTACTFORMPP_DEFAULT_cv_chars)); ?>" onblur="generateCaptcha();"  /></td>
        </tr>             

        <tr valign="top">
         <th scope="row">Min font size:</th>
         <td><input type="number" name="cv_min_font_size" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_min_font_size', CP_CONTACTFORMPP_DEFAULT_cv_min_font_size)); ?>" onblur="generateCaptcha();"  /></td>
         <th scope="row">Max font size:</th>
         <td><input type="number" name="cv_max_font_size" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_max_font_size', CP_CONTACTFORMPP_DEFAULT_cv_max_font_size)); ?>" onblur="generateCaptcha();"  /></td>        
         <td colspan="2" rowspan="">
           Preview:<br />
             <br />
            <img src="<?php echo cp_contactformpp_get_site_url(true); ?>/?cp_contactformpp=captcha"  id="captchaimg" alt="security code" border="0"  />            
         </td> 
        </tr>             
                

        <tr valign="top">
         <th scope="row">Noise:</th>
         <td><input type="number" name="cv_noise" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_noise', CP_CONTACTFORMPP_DEFAULT_cv_noise)); ?>" onblur="generateCaptcha();" /></td>
         <th scope="row">Noise Length:</th>
         <td><input type="number" name="cv_noise_length" size="10" value="<?php echo esc_attr(cp_contactformpp_get_option('cv_noise_length', CP_CONTACTFORMPP_DEFAULT_cv_noise_length)); ?>" onblur="generateCaptcha();" /></td>        
        </tr>          
        

        <tr valign="top">
         <th scope="row">Background:</th>
         <td><input type="color" name="cv_background" size="10" value="#<?php echo esc_attr(cp_contactformpp_get_option('cv_background', CP_CONTACTFORMPP_DEFAULT_cv_background)); ?>" onchange="generateCaptcha();" /></td>
         <th scope="row">Border:</th>
         <td><input type="color" name="cv_border" size="10" value="#<?php echo esc_attr(cp_contactformpp_get_option('cv_border', CP_CONTACTFORMPP_DEFAULT_cv_border)); ?>" onchange="generateCaptcha();" /></td>        
        </tr>    
        
        <tr valign="top">
         <th scope="row">Font:</th>
         <td>
            <select name="cv_font" onchange="generateCaptcha();" >
              <option value="font-1.ttf"<?php if ("font-1.ttf" == cp_contactformpp_get_option('cv_font', CP_CONTACTFORMPP_DEFAULT_cv_font)) echo " selected"; ?>>Font 1</option>
              <option value="font-2.ttf"<?php if ("font-2.ttf" == cp_contactformpp_get_option('cv_font', CP_CONTACTFORMPP_DEFAULT_cv_font)) echo " selected"; ?>>Font 2</option>
              <option value="font-3.ttf"<?php if ("font-3.ttf" == cp_contactformpp_get_option('cv_font', CP_CONTACTFORMPP_DEFAULT_cv_font)) echo " selected"; ?>>Font 3</option>
              <option value="font-4.ttf"<?php if ("font-4.ttf" == cp_contactformpp_get_option('cv_font', CP_CONTACTFORMPP_DEFAULT_cv_font)) echo " selected"; ?>>Font 4</option>
            </select>            
         </td>              
        </tr>                          
           
        
     </table>  
  </div>    
 </div>    
 
 
<div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Note</span></h3>
  <div class="inside">
   To insert this form in a post/page, use the dedicated icon 
   <?php print '<a href="javascript:cp_contactformpp_insertForm();" title="'.__('Insert PayPal Form').'"><img hspace="5" src="'.plugins_url('/images/cp_form.gif', __FILE__).'" alt="'.__('Insert PayPal Form').'" /></a>';     ?>
   which has been added to your Upload/Insert Menu, just below the title of your Post/Page.
   <br /><br />
  </div>
</div>   
  
</div> 


<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  /></p>


[<a href="https://wordpress.org/support/plugin/cp-contact-form-with-paypal#new-post" target="_blank">Request Custom Modifications</a>] | [<a href="https://cfpaypal.dwbooster.com/" target="_blank">Help</a>]
</form>
</div>
<script type="text/javascript">generateCaptcha();</script>












