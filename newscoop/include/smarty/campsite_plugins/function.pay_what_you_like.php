<?php
/**
 * Generates a piece of markup that provides payment functionality
 * something like doantions
 * @param array $p_params
 * @param Smarty_Internal_Template $p_smarty
 */
function smarty_function_pay_what_you_like($p_params, &$p_smarty)
{
    // get request params for checking the return from payment site
    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $acceptMsg = '';
    if ( array_key_exists('payment', $params)) {
    	if ($params['payment'] == 'yes') {
    		$js = "
    			$('#pay-what-you-want').trigger('click');
    		";
    		$acceptMsg = getGS('Vielen Dank für Ihre Zahlung!');
    	}  
    }
    /*
     *
     */

    $campsite = $p_smarty->getTemplateVars('gimme');

    // allowing logged in user or not
    $denyAnon = false;
    $userId = $campsite->user->identifier ? $campsite->user->identifier : 'null'; // for js
    if (isset($p_params['nologin'])) {
        $denyAnon = false;
    }

    $amount = '5';
    if (isset($p_params['amount'])) {
        $amount = $p_params['amount'];
    }

    $mustLogin = getGS('You must login to use this feature!');
    $markup = '';
    if ($p_smarty->getVariable('pay-what-you-like-js-set') instanceof Undefined_Smarty_Variable)
    {
    	 
        $markup = <<<JS
<script type='text/javascript'>


$(function()
{	
	
});
</script>
JS;
        $p_smarty->assign('pay-what-you-like-js-set', true, true);
    }
    
	$linktext = getGS('tageswoche.ch honorieren');
    if (isset($p_params['linktext'])) {
        $linktext = $p_params['linktext'];
    }
    
    $title = getGS('tageswoche.ch honorieren');
	if (isset($p_params['title'])) {
        $linktext = $p_params['title'];
    }

    $pre = getGS('Alle Artikel auf tageswoche.ch sind frei verfügbar. Wenn Ihnen unsere Arbeit etwas wert ist, können Sie uns freiwillig unterstützen. <br />
        	Sie entscheiden, wieviel Sie bezahlen. Danke, dass Sie uns helfen, tageswoche.ch in Zukunft noch besser zu machen.');
    if (isset($p_params['descr'])) {
        $pre = $p_params['descr'];
    }

    $descYes = '';
    if (isset($p_params['yestext'])) {
        $descYes = $p_params['yestext'];
    }

    $descNo = '';
    if (isset($p_params['notext'])) {
        $descNo = $p_params['notext'];
    }
    $x .='<div class="pay-what-you-like">'
    		.    '<a href="javascript:void(0)">'.getGS('Pay what you like').'!</a>'
    		.    '<div>'
    		.        "<div class='pay-what-you-like-pre'>{$pre}</div>"
    		.        "<form action=''>"
    		.    	 	'<ul>'
    		.                '<li>'
    		.               	'<button class="pay-what-you-like-yes">'.getGS("Yes, I'd like to pay").'</button> '
    		.            		"<input value='{$amount}' />&thinsp;".getGS('Francs')
    		.            	'</li>'
    		.            	'<li><button class="pay-what-you-like-no">'.getGS("No I don't want to pay").'</button></li>'
    		.        		"<li class='pay-what-you-like-desc-yes'>{$descYes}</li>"
    		.        		"<li class='pay-what-you-like-desc-no'>{$descNo}</li>"
    		.    		'</ul>'
    		.       "</form>"
    		.    '</div>'
    		. '</div>';
    		
    $orderId = uniqid('', true);
    
    $p_smarty->smarty->loadPlugin('smarty_function_uri');
	$pwylUri = smarty_function_uri( array('static_file' => "_css/tw2011/img/thumb_tw-pwyl.png"), $p_smarty);
	
	$acceptUrl = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'].'?payment=yes';
	
	
    
	$markup .= <<<HTML
<div style="display: block; margin-bottom: 25px">
	<img style="float: left; margin-right: 10px" alt="pwyl" src="{$pwylUri}" />
	<p><a href="#pay-what-you-want-popup" id="pay-what-you-want">{$linktext}</a></p>

	<div style="display:none">
		<div class="pay-what-you-want-popup" id="pay-what-you-want-popup">
	    	<article>
	        	<header><p>{$title}</p></header>
	        	<p>{$pre}</p>
	        	
	        	<div style="width:90%; text-align: center; height:35px;">
	        	{$acceptMsg}
	        	</div>
				<div style="width:100%">
	        		<div style="width:32%; float:left; text-align: right;">
	        			
	        			<label for='postfinance_amount' style='float: left; margin-left:60px; margin-top:3px; margin-right:2px;'>CHF</label>
        				<input type="text" value="3" id='postfinance_amount' name='postfinance_amount' style='width:35px; float: left;'/>
						<form method="post" action="https://e-payment.postfinance.ch/ncol/test/orderstandard.asp" onsubmit=" $('#postfinance_final_amount').val( $('#postfinance_amount').val() * 100 ); return true;">
							<!-- general parameters -->
							<input type="hidden" name="PSPID" value="medienbaselTEST">
							<input type="hidden" name="orderID" value='{$orderId}'>
							<input type="hidden" name="amount" id='postfinance_final_amount' value="300">
							<input type="hidden" name="currency" value="CHF">
							<input type="hidden" name="language" value="sde_DE">
							<input type="hidden" name="C N" value="">
							<input type="hidden" name="EMAIL" value="">
							<input type="hidden" name="ownerZIP" value="">
							<input type="hidden" name="owneraddress" value="">
							<input type="hidden" name="ownercty" value="">
							<input type="hidden" name="ownertown" value="">
							<input type="hidden" name="ownertelno" value="">
							
							<!-- check before the payment: see Security: Check before the Payment -->
							<input type="hidden" name="SHASign" value="">
							
							<!-- post payment redirection: see Transaction Feedback to the Customer -->
							<input type="hidden" name="accepturl" value="{$acceptUrl}">
							<input type="hidden" name="declineurl" value="">
							<input type="hidden" name="exceptionurl" value="">
							<input type="hidden" name="cancelurl" value="">
							<input type="submit" value="Postfinance" id=submit2 name=submit2 style="background-color: #FFCC00; font-weight: bold; color: black;">
					</form>
	        	</div>
	        	<div style="width:32%; float:left; text-align:center;">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post"> 
					<input type="hidden" name="cmd" value="_s-xclick"> 
					<input type="hidden" name="hosted_button_id" value="GJVQTYU3LMQJ6"> 
					<input type="image" src="https://www.paypalobjects.com/de_DE/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal."> 
					<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1"> 
					</form>  
				</div>
	        	<div style="width:32%; float:left; text-align:left;">
						<a href="http://flattr.com/thing/421328/tageswoche-ch" target="_blank"> 
							<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" />
						</a>
	        	</div>
	        </div>
	    </article>
	</div>
</div>


</div>

<script>
$(function() {
	$('head').append(
	"<script type='text/javascript'>" +  
	"/* <![CDATA[ */ " +  
	    "(function() { " +  
	        "var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];" + 
	        "s.type = 'text/javascript';" +  
	        "s.async = true;" +  
	        "s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';" +  
	        "t.parentNode.insertBefore(s, t);" +  
	    "})();" +  
	"/* ]]> */ " + 
	'<' + '/' + 'script>');
		
	$('#pay-what-you-want').fancybox();
	
	$("#postfinance_amount").keydown(function(event) {
        // Allow only backspace and delete
        if ( event.keyCode == 46 || event.keyCode == 8 ) {
            // let it happen, don't do anything
        }
        else {
            // Ensure that it is a number and stop the keypress
            if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
                event.preventDefault(); 
            }
        }
    });
    {$js}
});

</script>
HTML;

    return $markup;
}