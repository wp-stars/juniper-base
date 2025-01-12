<?php

namespace wps;


use MailPoet\API\API;
use MailPoet\API\MP\v1\APIException;

class MailPoetGF
{

    private static MailPoetGF $instance;

    public static function get_instance(): MailPoetGF {
        MailPoetGF::$instance = $instance ?? new MailPoetGF();
        return MailPoetGF::$instance;
    }

    public function __construct() {
    }

    public function init(): void
    {
        add_action( 'gform_after_submission', [$this, 'post_to_mailpoet'], 10, 2 );
    }

    /**
     * @throws APIException
     * @throws \Exception
     */
    public function post_to_mailpoet($entry, $form ): bool {
      
        if($form['title'] != "Newsletter") return false;

        $gfbody = array(                        
            'name' => rgar( $entry, '4' ),
            'email' => rgar( $entry, '1' ),
            'list_id' => rgar( $entry, '7' ),
        );

        if (class_exists(API::class)) {
            // Get MailPoet API instance
            $mailpoet_api = API::MP('v1');
            $list_ids = array($gfbody["list_id"]);

            $subscriber = [];
            $subscriber['first_name'] = $gfbody["name"];
            $subscriber['last_name'] = "";
            $subscriber['email'] = $gfbody["email"];

            // Check if subscriber exists. If subscriber doesn't exist an exception is thrown
            $get_subscriber = $mailpoet_api->getSubscriber($subscriber['email']);

            if($get_subscriber) {
                // In case subscriber exists just add them to new lists
                $mailpoet_api->subscribeToLists($subscriber['email'], $list_ids);
            } else {
                // Subscriber doesn't exist let's create one
                $mailpoet_api->addSubscriber($subscriber, $list_ids, []);
            }

            return true;
        }

        return false;
    }
}