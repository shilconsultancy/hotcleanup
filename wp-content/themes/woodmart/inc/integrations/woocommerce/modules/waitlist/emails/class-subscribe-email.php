<?php
/**
 * Send promotional email.
 *
 * @package XTS
 */

namespace XTS\Modules\Waitlist\Emails;

use XTS\Modules\Waitlist\DB_Storage;
use WP_User;
use WC_Product;

/**
 * Send a letter that the product has been successfully added to Waitlist.
 */
class Subscribe_Email extends Waitlist_Email {
	/**
	 * Email content html.
	 *
	 * @var string
	 */
	protected $content_html = '';

	/**
	 * Email content html.
	 *
	 * @var string
	 */
	protected $content_text = '';

	/**
	 * DB_Storage instance.
	 *
	 * @var DB_Storage
	 */
	protected $db_storage;

	/**
	 * Create an instance of the class.
	 */
	public function __construct() {
		$this->id          = 'woodmart_waitlist_subscribe_email';
		$this->title       = esc_html__( 'Waitlist - Subscription confirmed', 'woodmart' );
		$this->description = esc_html__( 'Configure the email that confirms a customer\'s subscription to the waitlist, assuring them that they will receive updates when the requested item is back in stock.', 'woodmart' );

		$this->customer_email = true;
		$this->heading        = esc_html__( 'You will be notified when product is back in stock', 'woodmart' );
		$this->subject        = esc_html__( 'Waitlist subscription confirmed', 'woodmart' );

		$this->template_html  = 'emails/waitlist-subscribe-email.php';
		$this->template_plain = 'emails/plain/waitlist-subscribe-email.php';

		add_action( 'woodmart_waitlist_send_subscribe_email_notification', array( $this, 'trigger' ), 10, 2 );

		parent::__construct();
	}

	/**
	 * Trigger Function that will send this email to the customer.
	 *
	 * @param string     $user_email User email.
	 * @param WC_Product $product WC_Product instanse.
	 *
	 * @return void
	 */
	public function trigger( $user_email, $product ) {
		$this->object    = $product;
		$this->recipient = $user_email;

		if ( ! $this->is_enabled() || ! $this->get_recipient() || ! $this->object ) {
			return;
		}

		$this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);
	}

	/**
	 * Returns default email content.
	 *
	 * @param string $email_type Email type.
	 *
	 * @return string Default content.
	 */
	public function get_default_content( $email_type ) {
		if ( 'plain' === $email_type ) {
			return __(
				'Hi {user_name},
We confirm that you have been added to the waitlist for the following item:
{product_title} {product_price} {product_link}
Stay tuned because we\'ll notify you when the product is available.

Best regards,
{site_title}',
				'woodmart'
			);
		} else {
			return __(
				'<p>Hi {user_name}</p>
<p>We confirm that you have been added to the waitlist for the following item:</p>
<table class="td xts-prod-table" cellspacing="0" cellpadding="6" border="1">
	<thead>
		<tr>
			<th class="td" scope="col"></th>
			<th class="td xts-align-start" scope="col">Product</th>
			<th class="td xts-align-end" scope="col">Price</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="td xts-tbody-td xts-img-col xts-align-start">
				<a href="{product_link}">
					{product_image}
				</a>
			</td>
			<td class="td xts-tbody-td xts-align-start">
				{product_title_with_link}
			</td>
			<td class="td xts-tbody-td xts-align-end">
				{product_price}
			</td>
		</tr>
	</tbody>
</table>
<p>Best regards,</p>
<p>{site_title}</p>',
				'woodmart'
			);
		}
	}
}

return new Subscribe_Email();
