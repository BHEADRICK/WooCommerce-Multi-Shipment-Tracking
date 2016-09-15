WooCommerce Multi-Shipment Tracking
==========================


##Requirements

WooCommerce, CMB2, and an account with aftership. 

##Installation

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-multi-shipment-tracking/` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

##Usage

Enter tracking info in order edit screen, then execute "order Complete" Order action.

Example of what to add to order complete order template:

```php
$tracking = get_post_meta($order->id, '_packages', true);

if($tracking):

	$urls = ['ups'=>'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=',
		'fedex'=>'http://www.fedex.com/Tracking?tracknumbers=',
		'usps'=>'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='];
    $carriers = ['ups'=>'UPS', 'fedex'=>'Fedex', 'usps'=>'US Mail'];
echo '<h3>Tracking</h3>
<table class="td" style="width: 100%; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4;">';
if(count($tracking)>0){
echo '
	<th>Package</th><th>Tracking#</th><th>Track</th><th>Photos</th>
';
	foreach($tracking  as $ix=>$package){
		echo '<tr>
		<td>
		Package #'.($ix+1).' of '.count($tracking) .'
</td><td>'. $package['tracking'].' (' . $carriers[$package['carrier']] . ')</td>
<td><a href=""'.$urls[$package['carrier']]. $package['tracking']. '">'.$urls[$package['carrier']]. $package['tracking'].' </a></td>
<td>' ;
			if($package['image'] && count($package['image'])>0){
				foreach($package['image'] as $ix=>$image){
					echo '<a href="' . $image . '"> Photo <br>';
				}
			}
echo '</td></tr>';
	}
}



echo '</table>';

	endif;
```

##Example Order Complete Email Excerpt


<h3>Tracking</h3>
<table class="td" style="width: 100%; font-family: 'Helvetica Neue',
Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px
solid #e4e4e4;">
<tbody>
<tr>
<th style="padding: 12px;">Package</th>
<th style="padding: 12px;">Tracking#</th>
<th style="padding: 12px;">Track</th>
<th style="padding: 12px;">Photos</th>
</tr>
<tr>
<td style="padding: 12px;">
		Package #1 of 2
</td>
<td style="padding: 12px;">asfsdfsadfsda (UPS)</td>
<td style="padding: 12px;"><a href="http://wwwapps.ups.com/WebTracking/track?track=yes&amp;trackNums=asfsdfsadfsda" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">http://wwwapps.ups.com/WebTracking/track?track=yes&amp;trackNums=asfsdfsadfsda
</a></td>
<td style="padding: 12px;">
<a href="http://example.com/wp-content/uploads/2016/09/help-in-a-hurry.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a><a href="http://example.com/wp-content/uploads/2016/09/image1.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a><a href="http://example.com/wp-content/uploads/2016/09/image2.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a><a href="http://example.com/wp-content/uploads/2016/09/image3.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a><a href="http://example.com/wp-content/uploads/2016/09/image4.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a><a href="http://example.com/wp-content/uploads/2016/09/image5.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a>
</td>
</tr>
<tr>
<td style="padding: 12px;">
		Package #2 of 2
</td>
<td style="padding: 12px;">asdfasdfdsa (UPS)</td>
<td style="padding: 12px;"><a href="http://wwwapps.ups.com/WebTracking/track?track=yes&amp;trackNums=asdfasdfdsa" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">http://wwwapps.ups.com/WebTracking/track?track=yes&amp;trackNums=asdfasdfdsa
</a></td>
<td style="padding: 12px;"><a href="http://example.com/wp-content/uploads/2016/09/image6.jpg" style="color: #557da1; font-weight: normal; text-decoration: underline;" target="_blank">
Photo <br></a></td>
</tr>
</tbody>
</table>

##Todo

Still need to add integration with aftership.