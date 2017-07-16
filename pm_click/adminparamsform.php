<?php
//защита от прямого доступа
defined('_JEXEC') or die();
?>
<div class="col100">
	<fieldset class="adminform">
		<table class="admintable" width="100%">
			<tr>
				<td class="key" width="300">
					<?php echo _JSHOP_CFG_CLICK_MERCHANT_ID; ?></td>
				<td>
					<input type="text" name="pm_params[click_merchant_id]" class="inputbox" value="<?php echo $params['click_merchant_id']; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key" width="300">
					<?php echo _JSHOP_CFG_CLICK_MERCHANT_USER_ID; ?></td>
				<td>
					<input type="text" name="pm_params[click_merchant_user_id]" class="inputbox" value="<?php echo $params['click_merchant_user_id']; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key" width="300">
					<?php echo _JSHOP_CFG_CLICK_MERCHANT_SERVICE_ID; ?></td>
				<td>
					<input type="text" name="pm_params[click_merchant_servise_id]" class="inputbox" value="<?php echo $params['click_merchant_service_id']; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo _JSHOP_CFG_CLICK_SECRET_KEY; ?>
				</td>
				<td>
					<input type="text" name="pm_params[click_secret_key]" class="inputbox" value="<?php echo $params['click_secret_key'];?>" />
				</td>
			</tr>
      <tr>
				<td class="key">
					<?php echo _JSHOP_TRANSACTION_END; ?>
				</td>
				<td>
				<?php              
					echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class="inputbox" size="1"', 'status_id', 'name', $params['transaction_end_status']);
				?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo _JSHOP_TRANSACTION_PENDING; ?>
				</td>
				<td>
				<?php 
					echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class="inputbox" size="1"', 'status_id', 'name', $params['transaction_pending_status']);
				?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo _JSHOP_TRANSACTION_FAILED; ?>
				</td>
				<td>
				<?php 
					echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class="inputbox" size="1"', 'status_id', 'name', $params['transaction_failed_status']);
				?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>