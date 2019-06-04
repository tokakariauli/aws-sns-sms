## Introduction

Library for sending out and verifying SMS codes using AWS SNS

## Installation

To get started simply run:

    composer require contract-zero/aws-sns-sms-verification

and:

    php artisan migrate

Add your AWS credentials to you .env file:

    AWS_ACCESS_KEY_ID=your-aws-access-key-id
    AWS_SECRET_ACCESS_KEY=your-aws-secret-access-key
    AWS_DEFAULT_REGION=your-aws-region

## Basic Usage

Add the VerifiesSMSCode trait to your User model (or any other model on which you might want to enable 2FA);

#Send an SMS by setting the SMS verification number attribute:
    $model->setSMSVerificationNumber($number);

#For simple verification you can use:
    $model->verifySMSCode($request->get('code'));

#Setting SMS verification attempt limits
    protected $sms_verification_attempt_limit = 5;

#Setting SMS verification code type ('number', or 'string')
    protected $sms_verification_code_type = 'number';

#Setting SMS verification code length
    protected $sms_verification_code_length = 4;

#Setting SMS verification sms type
    protected $sms_verification_sms_type = "Transactional";

Changing the SMS message being sent

    ...
    use ContractZero\SMSVerification\Traits\VerifiesSMSCode;

    class User extends Model
    {
        use VerifiesSMSCode;

        protected $sms_verification_attempt_limit = 5;

        ...

        /**
         * Gets the message to be sent with the SMS
         *
         * @param $code
         * @return string
         */
        public function getSMSVerificationMessage($code)
        {
            return "Here is your code for " . env("APP_NAME") . ": " . $code;
        }

        ...
    }

## License

AWS SNS SMS Verification is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
