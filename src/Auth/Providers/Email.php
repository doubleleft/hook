<?php
namespace Hook\Auth\Providers;

use Hook\Model\Auth as Auth;
use Hook\Model\Module as Module;

use Hook\Exceptions;
use Hook\Application\Config as Config;
use Hook\Mailer\Mail as Mail;

class Email extends Base
{
    const TEMPLATE_FORGOT_PASSWORD = 'auth.forgot_password.html';

    public function register($data)
    {
        $this->validateParams($data);
        if ($existing = $this->findExistingUser($data)) {
            throw new Exceptions\InternalException('already_registered');
        }

        // let's create a new authentication
        $user = new Auth;

        // set email/password directly due mass-assignment prevention
        $user->email = $data['email'];
        $user->password = $data['password'];

        // fill with additional user-data
        $user->fill($data);
        $user->save();

        return $user->dataWithToken();
    }

    public function login($data)
    {
        $this->validateParams($data);

        $user = $this->findExistingUser($data);
        if ($user) {
            if ($user->password != Auth::password_hash($data['password'], $user->password_salt)) {
                throw new Exceptions\ForbiddenException("password_invalid");
            }
            return $user->dataWithToken();

        } else {
            throw new Exceptions\ForbiddenException("invalid_user");
        }
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
                'from' => Config::get('mail.from', 'no-reply@api.2l.cx'),
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
