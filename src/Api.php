<?php

namespace DWPLINEBOT;

use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

class Api {

	private static $channel_access_token = ''; // LINE Messaging API access token.
	private static $channel_secret       = ''; // LINE Messaging API secret.
	private static $chatbase_bot_id      = ''; // Chatbase bot id.
	private static $chatbase_token       = ''; // Chatbase api key.
	private static $chatbase_source      = ''; // Chatbase url of providing source.
	private static $email                = ''; // The email of receiving bot notifications. 

	/**
	 * Instance
	 *
	 * @var Api
	 */
	private static $instance;

	/**
	 * Initialize class and add hooks
	 *
	 * @return void
	 */
	public static function init(): void {
		$class = self::get_instance();
		add_action( 'rest_api_init', array( $class, 'register_api_route' ) );
	}

	public function register_api_route(): void {
		register_rest_route(
			'dwp/v1',
			'/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_webhook' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
	}

	public function get_webhook( $request ) {
		$body = $request->get_body();

		if ( ! isset( $_SERVER['HTTP_X_LINE_SIGNATURE'] ) && ! $this->verify_signature( $body, sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_LINE_SIGNATURE'] ) ) ) ) {
			return new \WP_Error( 'invalid_signature', 'Invalid signature', array( 'status' => 403 ) );
		}

		$events = json_decode( $body, true );
		if ( isset( $events['events'] ) ) {
			foreach ( $events['events'] as $event ) {
				if ( 'message' === $event['type'] && 'text' === $event['message']['type'] ) {
					$this->handle_message( $event );
				}
			}
		}

		return new \WP_REST_Response( 'OK', 200 );
	}

	private function verify_signature( $body, $signature ) {
		return hash_equals( base64_encode( hash_hmac( 'sha256', $body, self::$channel_secret, true ) ), $signature );
	}

	private function handle_message( $event ) {
		$reply_token = $event['replyToken'];
		$text        = $event['message']['text'];
		$this->send_reply_message( $reply_token, $text, $event );
	}

	public function send_reply_message( $reply_token, $message, $event ) {
		$url  = 'https://api.line.me/v2/bot/message/reply';
		$data = array(
			'replyToken' => $reply_token,
			'messages'   => array(
				array(
					'type' => 'text',
					'text' => $this->get_reply_message( $message, $event['destination'] ),
				),
			),
		);

		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . self::$channel_access_token,
		);

		wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => $headers,
			)
		);
	}

	/**
	 * Get answer from Chatbase
	 *
	 * @param string $message
	 * @param string $uid
	 *
	 * @return string
	 */
	public function get_reply_message( $question, $uid ) {
		$url     = 'https://www.chatbase.co/api/v1/chat';
		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . self::$chatbase_token,
		);

		$data = array(
			'messages'       => array(
				array(
					'content' => $question,
					'role'    => 'user',
				),
			),
			'chatbotId'      => self::$chatbase_bot_id,
			'stream'         => false,
			'temperature'    => 0,
			'model'          => 'gpt-4o',
			'conversationId' => $uid,
		);

		$resp = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => $headers,
			)
		);

		$reply = json_decode( $resp['body'] )->text;

		if ( strpos( $reply, '此為 AI 產生答案非本站提供' ) !== false || strpos( $question, 'line' ) !== false || strpos( $question, 'ordernotify' ) !== false ) {
			wp_mail( self::$email, 'LINE 客服機器人修正通知', '問題：' . $question . ' <br><br>答案：' . $reply . '<br><br>前往修正：' . self::$chatbase_source, array( 'Content-Type : text/html; charset=utf-8' ) );
		}

		return '【我是機器人小歐】' . $reply;
	}



	/**
	 * Returns the single instance
	 */
	public static function get_instance(): Api {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Api::init();
