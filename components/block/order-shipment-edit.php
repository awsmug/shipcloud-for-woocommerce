<input type="hidden" name="shipment_id" value="{{ data.model.get('id') }}">

<fieldset class="label-shipment-sender address">
    <legend>
		<?php esc_html_e( 'Sender', 'shipcloud-for-woocommerce' ) ?>
    </legend>
    <div>
        <label for="shipment[from][company]">
			<?php esc_html_e( _x( 'Company', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][company]" value="{{ data.model.get('from').get('company') }}">
    </div>
    <div>
        <label for="shipment[from][first_name]">
			<?php esc_html_e( _x( 'First name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][first_name]" value="{{ data.model.get('from').get('first_name') }}">
    </div>
    <div>
        <label for="shipment[from][last_name]">
			<?php esc_html_e( _x( 'Last name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][last_name]" value="{{ data.model.get('from').get('last_name') }}">
    </div>
    <div>
        <label for="shipment[from][street]">
			<?php esc_html_e( _x( 'Street', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][street]" value="{{ data.model.get('from').get('street') }}">
    </div>
    <div>
        <label for="shipment[from][street_no]">
			<?php esc_html_e( _x( 'Street number', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][street_no]" value="{{ data.model.get('from').get('street_no') }}">
    </div>
    <div>
        <label for="shipment[from][Care of]">
			<?php esc_html_e( _x( 'Care of', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][care_of]" value="{{ data.model.get('from').get('care_of') }}">
    </div>
    <div>
        <label for="shipment[from][zip_code]">
			<?php esc_html_e( _x( 'ZIP code', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][zip_code]" value="{{ data.model.get('from').get('zip_code') }}">
    </div>
    <div>
        <label for="shipment[from][city]">
			<?php esc_html_e( _x( 'City', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][city]" value="{{ data.model.get('from').get('city') }}">
    </div>
    <div>
        <label for="shipment[from][state]">
			<?php esc_html_e( _x( 'State', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][state]" value="{{ data.model.get('from').get('state') }}">
    </div>
    <div>
        <label for="shipment[from][country]">
			<?php esc_html_e( _x( 'Country', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <select name="shipment[from][country]">
			<?php foreach ( wc()->countries->get_countries() AS $key => $country ): ?>
                <option value="<?php esc_attr_e( $key ); ?>">
					<?php esc_html_e( $country ); ?>
                </option>
			<?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="shipment[from][phone]">
			<?php esc_html_e( _x( 'Phone', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][phone]" value="{{ data.model.get('from').get('phone') }}">
    </div>
</fieldset>

<fieldset class="label-shipment-recipient address">
    <legend>
		<?php esc_html_e( 'Recipient', 'shipcloud-for-woocommerce' ) ?>
    </legend>
    <div>
        <label for="shipment[to][company]">
			<?php esc_html_e( _x( 'Company', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][company]" value="{{ data.model.get('to').get('company') }}">
    </div>
    <div>
        <label for="shipment[to][first_name]">
			<?php esc_html_e( _x( 'First name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][first_name]" value="{{ data.model.get('to').get('first_name') }}">
    </div>
    <div>
        <label for="shipment[to][last_name]">
			<?php esc_html_e( _x( 'Last name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][last_name]" value="{{ data.model.get('to').get('last_name') }}">
    </div>
    <div>
        <label for="shipment[to][street]">
			<?php esc_html_e( _x( 'Street', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][street]" value="{{ data.model.get('to').get('street') }}">
    </div>
    <div>
        <label for="shipment[to][street_no]">
			<?php esc_html_e( _x( 'Street number', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][street_no]" value="{{ data.model.get('to').get('street_no') }}">
    </div>
    <div>
        <label for="shipment[to][Care of]">
			<?php esc_html_e( _x( 'Care of', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][care_of]" value="{{ data.model.get('to').get('care_of') }}">
    </div>
    <div>
        <label for="shipment[to][zip_code]">
			<?php esc_html_e( _x( 'ZIP code', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][zip_code]" value="{{ data.model.get('to').get('zip_code') }}">
    </div>
    <div>
        <label for="shipment[to][city]">
			<?php esc_html_e( _x( 'City', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][city]" value="{{ data.model.get('to').get('city') }}">
    </div>
    <div>
        <label for="shipment[to][state]">
			<?php esc_html_e( _x( 'State', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][state]" value="{{ data.model.get('to').get('state') }}">
    </div>
    <div>
        <label for="shipment[to][country]">
			<?php esc_html_e( _x( 'Country', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <select name="shipment[to][country]">
			<?php foreach ( wc()->countries->get_countries() AS $key => $country ): ?>
                <option value="<?php esc_attr_e( $key ); ?>">
					<?php esc_html_e( $country ); ?>
                </option>
			<?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="shipment[to][phone]">
			<?php esc_html_e( _x( 'Phone', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][phone]" value="{{ data.model.get('to').get('phone') }}">
    </div>
</fieldset>

<input type="hidden" name="shipment[package][weight]" value="{{ data.model.get('package').get('width') }}" />
<input type="hidden" name="shipment[package][length]" value="{{ data.model.get('package').get('length') }}" />
<input type="hidden" name="shipment[package][width]" value="{{ data.model.get('package').get('width') }}" />
<input type="hidden" name="shipment[package][height]" value="{{ data.model.get('package').get('height') }}" />
<input type="hidden" name="shipment[package][type]" value="{{ data.model.get('package').get('type') }}" />

<div class="label-shipment-actions">
    <button class="button wcsc-save-shipment button-primary" type="button">
		<?php _ex( 'Save', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
    </button>
    <button class="button wcsc-edit-abort" type="button">
		<?php _ex( 'Abort', 'Order: Backend button to abort edit', 'wcsc' ) ?>
    </button>
</div>
