<?php

namespace wps\frontend;

use Timber\Timber;

enum ModalStatus: string
{
    case open = 'open';
    case closed = 'closed';
}

class Modal
{
    // modal content
    public string $id = 'id-undefined';
    public string $title = 'title undefined';
    public string $content = 'content undefined';
    public string $twigTemplateDir = 'modals/'; // relative to theme root
    public string $view = 'defaultModal.twig';
    public ModalStatus $status = ModalStatus::closed;

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

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function open(): self
    {
        $this->status = ModalStatus::open;
        return $this;
    }

    public function close(): self
    {
        $this->status = ModalStatus::closed;
        return $this;
    }

    public function render(): self
    {
        add_action('wp_footer', function(){
            Timber::render($this->twigTemplateDir . $this->view, [
                'modal' => apply_filters('wps_modal_render', $this),
            ]);
        });

        return $this;
    }
}