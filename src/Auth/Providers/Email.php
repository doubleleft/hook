<?php
namespace API\Auth\Providers;

use API\Exceptions;

use API\Model\Auth as Auth;
use API\Model\AppConfig as AppConfig;
use API\Model\Module as Module;

use API\Mailer\Mail as Mail;

class Email extends Base
{
    const TEMPLATE_FORGOT_PASSWORD = 'auth.forgot_password.html';

    public function register($data)
    {
        $this->validateParams($data);
        if ($existing = $this->findExistingUser($data)) {
            throw new Exceptions\InternalException('already_registered');
        }
        $user = Auth::create($data);

        return $user->dataWithToken();
    }

    public function login($data)
    {
        $this->validateParams($data);

        $userdata = null;
        if ($user = $this->findExistingUser($data)) {
            if ($user->password != $data['password']) {
                throw new Exceptions\ForbiddenException("password_invalid");
            }
            $userdata = $user->dataWithToken();
        } else {
            if (!$user) {
                throw new Exceptions\ForbiddenException("invalid_user");
            }
        }

        return $userdata;
    }

    /**
     * Trigger 'forgot password' email
     */
    public function forgotPassword($data)
    {
        $user = $this->find('email', $data);

        if (!$user) {
            throw new Exceptions\NotFoundException("invalid_user");
        }

        if (!isset($data['subject'])) {
            $data['subject'] = 'Forgot your password?';
        }

        $body_data = $user->generateForgotPasswordToken()->toArray();
        $body_data['token'] = $user->getAttribute(Auth::FORGOT_PASSWORD_FIELD);

        $template = isset($data['template']) ? $data['template'] : self::TEMPLATE_FORGOT_PASSWORD;

        return array(
            'success' => (Mail::send(array(
                'subject' => $data['subject'],
                'from' => AppConfig::get('mail.from', 'no-reply@api.2l.cx'),
                'to' => $user->email,
                'body' => Module::template($template)->compile($body_data)
            )) === 1)
        );
    }

    /**
     * Reset user password
     */
    public function resetPassword($data)
    {
        if (!isset($data['token']) === 0) {
            throw new Exceptions\BadRequestException("you must provide a 'token'.");
        }
        if (!isset($data['password']) || strlen($data['password']) === 0) {
            throw new Exceptions\BadRequestException("you must provide a valid 'password'.");
        }

        $data[Auth::FORGOT_PASSWORD_FIELD] = $data['token'];
        $user = $this->find(Auth::FORGOT_PASSWORD_FIELD, $data);

        if ($user && $user->resetPassword($data['password'])) {
            return array('success' => true);
        } else {
            throw new Exceptions\UnauthorizedException("invalid_token");
        }
    }

    public function findExistingUser($data)
    {
        $user = null;

        try {
            $user = $this->find('email', $data);
        } catch (\Illuminate\Database\QueryException $e) {}

        return $user;
    }

    protected function validateParams($data)
    {
        // validate email address
        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exceptions\BadRequestException("invalid_email");
        }

        // validate password
        if (!isset($data['password']) || strlen($data['password']) === 0) {
            throw new Exceptions\BadRequestException("invalid_password");
        }

    }

}
