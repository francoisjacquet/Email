<?php
/**
 * Send Email Attachment function
 *
 * @package Email module
 */

/**
 * Send Email with Attachment
 *
 * @example SendEmailAttachment( $to, $subject, $msg, 'Foo <bar@from.address>', $cc, array( array( $pdf_file, $pdf_name ) ) );
 *
 * @uses PHPMailer class
 *
 * @param string|array $to          Recipients, array or comma separated list of emails.
 * @param string       $subject     Subject.
 * @param string       $message     Message.
 * @param string       $from        if empty, defaults to rosariosis@[yourserverdomain].
 * @param string|array $cc          Carbon Copy, array or comma separated list of emails.
 * @param array        $attachments Array of file paths, or Array of Attachments (file path, file name).
 *
 * @return boolean true if email sent, or false
 */
function SendEmailAttachment( $to, $subject, $message, $from = null, $cc = null, $attachments = array() )
{
	if ( ! is_array( $attachments ) )
	{
		$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
	}

	global $phpmailer;

	// (Re)create it, if it's gone missing.
	if ( ! ( $phpmailer instanceof PHPMailer ) )
	{
		require_once 'classes/PHPMailer/class.phpmailer.php';

		require_once 'classes/PHPMailer/class.smtp.php';

		$phpmailer = new PHPMailer( true );
	}

	// Empty out the values that may be set.
	$phpmailer->ClearAllRecipients();
	$phpmailer->ClearAttachments();
	$phpmailer->ClearCustomHeaders();
	$phpmailer->ClearReplyTos();

	// FJ add email headers.
	if ( empty( $from ) )
	{
		// Get the site domain and get rid of www.
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );

		if ( substr( $sitename, 0, 4 ) == 'www.' )
		{
			$sitename = substr( $sitename, 4 );
		}

		$programname = mb_strtolower( filter_var(
			Config( 'NAME' ),
			FILTER_SANITIZE_EMAIL
		));

		if ( ! $programname )
		{
			$programname = 'rosariosis';
		}

		$from_email = $programname . '@' . $sitename;
	}
	else
	{
		// Break $from into name and address parts if in the format "Foo <bar@baz.com>".
		$bracket_pos = strpos( $from, '<' );

		if ( $bracket_pos !== false )
		{
			// Text before the bracketed email is the "From" name.
			if ( $bracket_pos > 0 )
			{
				$from_name = substr( $from, 0, $bracket_pos - 1 );
				$from_name = str_replace( '"', '', $from_name );
				$from_name = trim( $from_name );
			}

			$from_email = substr( $from, $bracket_pos + 1 );
			$from_email = str_replace( '>', '', $from_email );
			$from_email = trim( $from_email );
		}
		// Avoid setting an empty $from_email.
		elseif ( '' !== trim( $from ) )
		{

			$from_email = trim( $from );
		}
	}

	if ( ! isset( $from_name ) )
	{
		$from_name = Config( 'NAME' );
	}

	// Set Email address to send from.
	$phpmailer->From = $from_email;

	$phpmailer->FromName = $from_name;

	// Set destination addresses.
	if ( ! is_array( $to ) )
	{
		$to = explode( ',', $to );
	}

	foreach ( (array) $to as $recipient )
	{
		try
		{
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
			$recipient_name = '';

			if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) )
			{
				if ( count( $matches ) == 3 )
				{
					$recipient_name = $matches[1];
					$recipient = $matches[2];
				}
			}

			$phpmailer->AddAddress( $recipient, $recipient_name );
		}
		catch ( phpmailerException $e )
		{
			continue;
		}
	}

	// Append Program Name to subject.
	$subject = Config( 'NAME' ) . ' - ' . $subject;

	// Set mail's subject and body.
	$phpmailer->Subject = $subject;

	$phpmailer->Body    = $message;

	// Add any CC and BCC recipients.
	if ( ! is_array( $cc ) )
	{
		$cc = explode( ',', $cc );
	}

	if ( ! empty( $cc ) )
	{
		foreach ( (array) $cc as $recipient )
		{
			try
			{
				// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
				$recipient_name = '';

				if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) )
				{
					if ( count( $matches ) == 3 )
					{
						$recipient_name = $matches[1];
						$recipient = $matches[2];
					}
				}

				$phpmailer->AddCc( $recipient, $recipient_name );
			}
			catch ( phpmailerException $e )
			{
				continue;
			}
		}
	}

	// Set to use PHP's mail().
	$phpmailer->IsMail();

	// Set Content-Type and charset.
	$phpmailer->ContentType = 'text/plain'; // TODO detect if HTML.

	$phpmailer->CharSet = 'UTF-8';

	if ( ! empty( $attachments ) )
	{
		foreach ( (array) $attachments as $attachment )
		{
			try
			{
				if ( is_array( $attachment ) )
				{
					$phpmailer->AddAttachment( $attachment[0], $attachment[1] );
				}
				else
					$phpmailer->AddAttachment( $attachment );
			}
			catch ( phpmailerException $e )
			{
				continue;
			}
		}
	}

	// Send!
	try
	{
		return $phpmailer->Send();
	}
	catch ( phpmailerException $e )
	{
		return false;
	}
}
