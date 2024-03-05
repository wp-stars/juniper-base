<?php

namespace wps\frontend;

use Timber\Timber;

class Modal
{
    // modal content
    public string $id = 'id-undefined';
    public string $title = 'title undefined';
    public string $content = 'content undefined';

    //submitButton
    public bool $showSubmitButton = false;
    public string $submitButtonLabel = '';
    public string $submitButtonCallbackFn = '';

    // close button
    public bool $showCloseButton = false;
    public string $closeButtonLabel = '';
    public string $closeButtonCallbackFn = '';

    public function __construct()
    {
        $this->submitButtonLabel = __('Speichern', 'wps');
        $this->closeButtonLabel = __('Schließen', 'wps');
    }

    public function render(): void
    {
        add_action('wp_footer', function(){
            Timber::render('modals/modal.twig', [
                'modal' => apply_filters('wps_modal_render', $this),
            ]);
        });
    }
}