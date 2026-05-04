<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\AlibabaCloud;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class AlibabaCloudTest extends Base
{
    /**
     * @throws \Exception
     */
    public function testSendSMS(): void
    {
        $sender = new AlibabaCloud(
            \getenv('ALIBABA_CLOUD_ACCESS_KEY_ID'),
            \getenv('ALIBABA_CLOUD_ACCESS_KEY_SECRET'),
            \getenv('ALIBABA_CLOUD_SIGN_NAME'),
            \getenv('ALIBABA_CLOUD_TEMPLATE_CODE')
        );

        $message = new SMS(
            to: [\getenv('ALIBABA_CLOUD_TO')],
            content: '123456', // Template code value (adapter wraps it in JSON)
        );

        $result = $sender->send($message);

        $this->assertResponse($result);
    }
}
