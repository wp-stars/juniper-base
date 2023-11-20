<?php

namespace wps;


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

    public function post_to_mailpoet( $entry, $form ) {
      
        if($form['title'] != "Newsletter") return;

        $gfbody = array(                        
            'name' => rgar( $entry, '1' ),
            'email' => rgar( $entry, '3' ),
            'list_id' => rgar( $entry, '5' ),        
        );
    
        if (class_exists(\MailPoet\API\API::class)) {
            // Get MailPoet API instance
            $mailpoet_api = \MailPoet\API\API::MP('v1');
            // Get available list so that a subscriber can choose in which to subscribe
            $lists = $mailpoet_api->getLists();

            
            $list_ids = array($gfbody["list_id"]);

            $subscriber = [];
            $subscriber['first_name'] = $gfbody["name"];
            $subscriber['last_name'] = "";
            $subscriber['email'] = $gfbody["email"];


            // Check if subscriber exists. If subscriber doesn't exist an exception is thrown
            $get_subscriber = false;
            try {
                $get_subscriber = $mailpoet_api->getSubscriber($subscriber['email']);
                // In case subscriber exists just add them to new lists
                $mailpoet_api->subscribeToLists($subscriber['email'], $list_ids);

            } catch (\Exception $e) {
                // Subscriber doesn't exist let's create one
                $mailpoet_api->addSubscriber($subscriber, $list_ids);
                $mailpoet_api->subscribeToLists($subscriber['email'], $list_ids);
            }

            // try {
            //     if (!$get_subscriber) {
            //         // Subscriber doesn't exist let's create one
            //         $mailpoet_api->addSubscriber($subscriber, $list_ids);
            //         $mailpoet_api->subscribeToLists($subscriber['email'], $list_ids);
            //     } else {
            //         // In case subscriber exists just add them to new lists
            //         $mailpoet_api->subscribeToLists($subscriber['email'], $list_ids);
            //     }
            // } catch (\Exception $e) {
            //     $error_message = $e->getMessage();
            //     die($error_message);
            // }
        }   
    }
}