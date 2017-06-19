<?php

add_action('widgets_init', 'basket_widget_init', 10);


function basket_widget_init() {
	register_widget('Basket_Widget');
}

class Basket_Widget extends WP_Widget {

	function Basket_Widget()
	{
		$widget_ops = array(
				'classname' => 'basket-widget'
		);

		$this->WP_Widget('Basket_Widget', 'Basket', $widget_ops);
	}

	function widget($args, $instance)
	{
		extract($args);

		echo $before_widget;

		echo $before_title.'Basket'.$after_title;

		$data = getCustomSessionData();
		$current_user = wp_get_current_user();
		$all_meta_for_user = get_user_meta( $current_user->ID );

		if (empty($data["billing-info"]["country"])) {

			$data["billing-info"]["country"] = $all_meta_for_user["user_country"][0];
		}

		if(!empty($data['canine']) || !empty($data['feline']) || !empty($data['equine'])) {
			$items = 0;
			$cost = 0;

			foreach($data as $type => $typeData) {

				if($type != 'vet-info' && $type != 'billing-info' && $type != 'shipping-info' && $type != 'order') {

					foreach($typeData as $breed => $breedData) {
						foreach($breedData as $testId => $testData) {

							$quantity = $testData['quantity'];

							$testPrice = get_post_meta($testId, 'test-price-pm', true);
							$cost += $testPrice * $quantity;
							$items += $quantity;
						}
					}

				}
			}

			if(isset($data['billing-info']['discount-code'])) {

				$discountAmount = $data['billing-info']['discount-code']['amount'];

				if(strpos($discountAmount, '%') !== false) {
					$discountAmount = str_replace('%', '', $discountAmount);
					$discount = (($cost * $discountAmount) / 100);
					$cost -= $discount;
				} else {
					$discount = $discountAmount;
					$cost -= $discount;
				}
			}

			//echo ($data['billing-info']['country']);

			if (in_array($data["billing-info"]["country"], $vatArray)) {
				$vat = $cost / 6;
			} else {
				$vat = 0;
				$minuscost = $cost / 6;
				$cost = $cost - $minuscost;
			}

			$vat = number_format($vat, 1);
			$subTotal = $cost - $vat;


			?>
                        <table class="table table-bordered table-rounded">
                                <tr>
                                        <th>No. of items</th>
                                        <td><?php echo $items; ?></td>
                                </tr>
                                <tr>
                                        <th>Cost</th>
                                        <td><?php echo '&pound;' . number_format($subTotal, 2); ?></td>
                                </tr>

                <?php if (in_array($data["billing-info"]["country"], $vatArray)) { ?>
                                        <tr>
                                        <th>VAT</th>
                                        <td><?php echo '&pound;' . number_format($vat, 2); ?></td>
                                </tr>
                <?php } ?>
                                <tr>
                                        <th>Total  <?php if (in_array($data["billing-info"]["country"], $vatArray)) { ?> (inc VAT)  <?php } ?></th>
                                        <td><?php echo '&pound;' . number_format($cost, 2); ?></td>
                                </tr>
                        </table>

                        <a class="btn btn-success" href="/basket/">View Basket</a>

                        <?php
                } else {
                        echo '<p>Your basket is currently empty.</p>';
                }
                echo $after_widget;
        }

        function update($new_instance, $old_instance) {
                $instance = $old_instance;

                return $instance;
        }

        function form($instance) {

        }
}

?>