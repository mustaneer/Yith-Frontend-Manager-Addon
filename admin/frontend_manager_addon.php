<div class="wrap">
<h1><?php _ex( 'Frontend Manager Settings', 'yith-frontend-manager-for-woocommerce' ); ?></h1>

<form method="post" action="">
<table class="form-table" role="presentation">
	<tbody>
		<tr>
			<th scope="row"><label for="source"><?php _ex( 'Frontend Manager Access ', 'yith-frontend-manager-for-woocommerce' ); ?></label></th>
			<td>
				<?php
					global $wp_roles;
					$roles = $wp_roles->get_names();
					$frontend_manager_access = get_option('frontend_manager_access');
					$user_roles_not_allowed 	= get_option('user_roles_not_allowed');
					$override_previous_amount = get_option('override_previous_amount');
					$user_recurring_amount = get_option('user_recurring_amount');
					$recurring_duration = get_option('recurring_duration');
					$recurring_data_time = get_option('recurring_data_time');
				?>
				<select class="user_roles_not_allowed" name="frontend_manager_access[]" multiple="multiple">
					<?php foreach($roles as $role_key => $role_value ){ ?>
						<option value="<?php echo $role_key; ?>" <?php if(in_array($role_key, $frontend_manager_access)){  echo 'selected'; } else { echo '';  } ?>><?php  _ex( $role_value, 'yith-frontend-manager-for-woocommerce' ); ?> </option>
					<?php } ?>
				</select>
				<small> <label for="source"><?php _ex( 'Select user roles which can access frontend manager.', 'yith-frontend-manager-for-woocommerce' ); ?></label> </small></td>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="source"><?php _ex( 'Exclude User Roles', 'yith-frontend-manager-for-woocommerce' ); ?></label></th>
			<td>
				<select class="user_roles_not_allowed" name="user_roles_not_allowed[]" multiple="multiple">
					<?php foreach($roles as $role_key => $role_value ){ ?>
						<option value="<?php echo $role_key; ?>"  <?php if(in_array($role_key, $user_roles_not_allowed)){  echo 'selected'; } else { echo '';  } ?>><?php  _ex( $role_value, 'yith-frontend-manager-for-woocommerce' ); ?></option>
					<?php } ?>
				</select>
				<small> <?php _ex( 'Select user roles which can not be added through Frontend Manager.', 'yith-frontend-manager-for-woocommerce' ); ?></small></td>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="source"><?php _ex( 'Recurring Amount', 'yith-frontend-manager-for-woocommerce' ); ?></label></th>
			<td><input type="checkbox" value="yes" name="override_previous_amount" <?php echo ($override_previous_amount == 'yes')? 'checked': ''; ?> /> <?php _ex( 'Override Amount', 'yith-frontend-manager-for-woocommerce' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="source"></label></th>
			<td>
				<input type="text" name="user_recurring_amount" id="user_recurring_amount" value="<?php echo ($user_recurring_amount)? $user_recurring_amount: ''; ?>" />
			</td>
		</tr>		
		<tr>
			<th scope="row"><label for="source"><?php _ex( 'Select Recurring Duration', 'yith-frontend-manager-for-woocommerce' ); ?></label></th>
			<td>
				<select name="recurring_duration" id="recurring_duration" >
					<option value="monthly" <?php echo ($recurring_duration == 'monthly')? 'selected': ''; ?>><?php _ex( 'Monthly', 'yith-frontend-manager-for-woocommerce' ); ?></option>
					<option value="quarterly" <?php echo ($recurring_duration == 'quarterly')? 'selected': ''; ?> ><?php _ex( 'Quarterly', 'yith-frontend-manager-for-woocommerce' ); ?></option>
					<option value="yearly" <?php echo ($recurring_duration == 'yearly')? 'selected': ''; ?>><?php _ex( 'Yearly', 'yith-frontend-manager-for-woocommerce' ); ?></option>
				</select>
			</td>
		</tr>		
		<tr>
			<th scope="row"><label for="source"><?php _ex( 'Select Date & Time', 'yith-frontend-manager-for-woocommerce' ); ?></label></th>
			<td>
				<input type="text" id="recurring_data_time" name="recurring_data_time" value="<?php echo ($recurring_data_time)? $recurring_data_time: ''; ?>" >
			</td>
		</tr>
	</tbody>
</table>

<p class="submit"><input type="submit" name="fm_submit" id="fm_submit" class="button button-primary" value="Save Changes"></p></form>

</div>
