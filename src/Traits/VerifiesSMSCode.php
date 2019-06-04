<?php

namespace ContractZero\SMSVerification\Traits;

use ContractZero\SMSVerification\ContractZeroSNSClientFacade;
use ContractZero\SMSVerification\Exceptions\TooManySMSVerificationAttempts;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * Trait VerifiesSMSCode
 *
 * Sends an SMS message containing a verificaiton code to a given mobile number (using AWS SNS)
 * Contains methods for verifying the user submitted code (authorising action) and monitoring attempts.
 *
 * @package ContractZero\SMSVerification\Traits
 */
trait VerifiesSMSCode
{
    protected $sms_verification_attempt_limit = 5;
    protected $sms_verification_code_type = 'number';
    protected $sms_verification_code_length = 4;
    protected $sms_verification_sms_type = "Transactional";

    /**
     * Sends the SMS confirmation code
     *
     * @param $mobile
     * @return $this
     */
    public function setSMSVerificationNumber($mobile)
    {
        $code = $this->getCode();

        ContractZeroSNSClientFacade::publish([
            "SenderId"    => $this->getSMSVerificationSender(),
            "SMSType"     => $this->sms_verification_sms_type,
            "Message"     => $this->getSMSVerificationMessage($code),
            "PhoneNumber" => $mobile,
        ]);

        $this->contact_number = $mobile;
        $this->code           = $code;
        $this->uuid           = (string) Str::uuid();

        if ($this->SMSVerificationAttemptLimitEnabled()) {
            $this->attempts = 0;
        }

        $this->save();

        return $this;
    }

    /**
     * Verifies the submitted SMS code
     *
     * @param $code
     * @return bool
     * @throws \Exception
     */
    public function verifySMSCode($code)
    {
        return $this
            ->validateSMSVerificationAttempts()
            ->setSMSVerificationStatus($this->code === $code);
    }

    /**
     * Validates SMS Verification attempts
     *
     * @return $this
     * @throws TooManySMSVerificationAttempts
     */
    private function validateSMSVerificationAttempts()
    {
        if ($this->SMSVerificationAttemptLimitEnabled() && $this->SMSVerificationAttemptLimitExceeded()) {
            throw new TooManySMSVerificationAttempts("Too many SMS verification attempts. Please re-send the SMS code");
        }

        return $this;
    }

    /**
     * Checks it SMS Verification attempt limit is enabled
     *
     * @return bool
     */
    private function SMSVerificationAttemptLimitEnabled()
    {
        return !empty($this->sms_verification_attempt_limit);
    }

    /**
     * Checks if SMS Verification attempt limit exceeded
     *
     * @return bool
     */
    private function SMSVerificationAttemptLimitExceeded()
    {
        return $this->attempts > $this->sms_verification_attempt_limit;
    }

    /**
     * Sets the SMS verification status
     *
     * @param bool $status
     * @return bool
     */
    private function setSMSVerificationStatus(bool $status)
    {
        $this->status = $status;

        $this
            ->updateSMSVerificationAttempts($status)
            ->save();

        return $status;
    }

    /**
     * Updates the SMS Verification attempts
     *
     * @param bool $status
     * @return $this
     */
    private function updateSMSVerificationAttempts(bool $status)
    {
        if ($this->SMSVerificationAttemptLimitEnabled() && !$status) {
            $this->attempts++;
        }

        return $this;
    }

    /**
     * Gets the message to be sent with the SMS
     *
     * @param $code
     * @return string
     */
    protected function getSMSVerificationMessage($code)
    {
        return "Your SMS verification code is: $code";
    }

    /**
     * Gets the sender of the verification SMS
     *
     * @return string
     */
    protected function getSMSVerificationSender()
    {
        return env('APP_NAME');
    }

    /**
     * Generate the code
     *
     * @return string
     */
    protected function getCode()
    {
        if ($this->sms_verification_code_type === 'number') {
            return rand(pow(10, $this->sms_verification_code_length-1), pow(10, $this->sms_verification_code_length)-1);
        }

        return str_random($this->sms_verification_code_length);
    }
}
