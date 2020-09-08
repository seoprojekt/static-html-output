<?php

namespace StaticHTMLOutput;

class Webhook {

    /**
     * @var Archive
     */
    private $archive;
    private $advancedPostSettings;

    public function __construct(Archive $archive) {
        $this->archive = $archive;
        $this->advancedPostSettings = PostSettings::get(['advanced']);
    }

    private function canDoWebhook(): bool {
        return is_string($this->getCompletionWebhookUrl()) && count($this->getCompletionWebhookUrl()) > 0;
    }

    private function getCompletionWebhookUrl(): string {
        return $this->advancedPostSettings['completionWebhook'];
    }

    private function getPostRequestBody(): array {
        $postRequestBody = [
            'message' => 'WP2Static deployment complete!',
            'deploy_host_url' => $this->archive->settings['baseUrl'],
            'deploy_token' => $this->advancedPostSettings['firebaseToken'],
            'deploy_project_name' => $this->advancedPostSettings['firebaseProjectName']
        ];
        if ($this->archive->settings['selected_deployment_option'] === 'zip') {
            $postRequestBody['zip_url'] = $this->archive->settings['wp_uploads_url'] . '/' . $this->archive->name . '.zip';
        }
        return $postRequestBody;
    }


    public function send() {
        if ($this->canDoWebhook()) {
            wp_remote_post($this->getCompletionWebhookUrl(), [
                'headers'     => [
                    'Content-Type' => 'application/json; charset=utf-8'
                ],
                'body'        => json_encode($this->getPostRequestBody()),
                'method'      => 'POST',
                'data_format' => 'body',
            ]);
        }
    }
}