<?php namespace Hook\Controllers;

use Hook\Application\Context;
use Hook\Application\Config;
use Hook\Mailer\Mail;

use Hook\Model\Auth;
use Hook\Model\Module;

use Hook\Exceptions;

class AuthController extends HookController {
    const TEMPLATE_FORGOT_PASSWORD = 'auth.forgot_password.html';

    public function show() {
        return Auth::current();
    }

    public function register()
    {
        $data = $this->getData();
        $this->validateParams($data);

        if (Auth::where('email', $data['email'])->first()) {
            throw new Exceptions\InternalException('already_registered');
        }

        // let's create a new authentication
        $auth = new Auth;
        $auth->setTrustedAction(true);

        // set email/password directly due mass-assignment prevention
        $auth->email = $data['email'];
        $auth->password = $data['password'];

        // fill with additional auth-data
        $auth->fill($data);
        $auth->save();

        return $auth->dataWithToken();
    }

    public function login()
    {
        $data = $this->getData();
        $this->validateParams($data);

        $auth = Auth::where('email', $data['email'])->first();
        if ($auth) {
            if ($auth->password != Auth::password_hash($data['password'], $auth->password_salt)) {
                throw new Exceptions\ForbiddenException("password_invalid");
            }

            $auth->setTrustedAction(true);

            //
            // Handle login with custom method.
            //
            $observer = Auth::getObserver();
            if ($observer && method_exists($observer, 'login') && !$observer->login($auth)) {
                throw new Exceptions\ForbiddenException();
            }

            return $auth->dataWithToken();

        } else {
            throw new Exceptions\ForbiddenException("invalid_user");
        }
    }

    public function update()
    {
        $auth = Auth::current();

        // not logged in
        if (!$auth) { throw new Exceptions\UnauthorizedException(); }

        // flag action as trusted.
        $auth->setTrustedAction(true);

        if ($auth->fill($this->getData()) && $auth->isModified()) {
            if (!$auth->save()) {
                throw new Exceptions\ForbiddenException();
            }
        }

        return $auth->toArray();
    }

    /**
     * Trigger 'forgot password' email
     */
    public function forgotPassword()
    {
        $data = $this->getData();
        $auth = Auth::where('email', $data['email'])->first();

        if (!$auth) {
            throw new Exceptions\NotFoundException("invalid_user");
        }

        if (!isset($data['subject'])) {
            $data['subject'] = 'Forgot your password?';
        }

        $body_data = Context::unsafe(function() use (&$auth) {
            $array = $auth->generateForgotPasswordToken()->toArray();
            $array['token'] = $auth->getAttribute(Auth::FORGOT_PASSWORD_FIELD);
            return $array;
        });

        $template = isset($data['template']) ? $data['template'] : self::TEMPLATE_FORGOT_PASSWORD;

        return array(
            'success' => (Mail::send(array(
                'subject' => $data['subject'],
                'from' => Config::get('mail.from', 'no-reply@api.2l.cx'),
                'to' => $auth->email,
                'body' => Module::template($template)->compile($body_data)
            )) === 1)
        );
    }

    /**
     * Reset auth password
     */
    public function resetPassword()
    {
        $data = $this->getData();

        if (!isset($data['token']) === 0) {
            throw new Exceptions\BadRequestException("you must provide a 'token'.");
        }
        if (!isset($data['password']) || strlen($data['password']) === 0) {
            throw new Exceptions\BadRequestException("you must provide a valid 'password'.");
        }

        // Set trusted context to update auths row
        Context::setTrusted(true);

        $auth = Auth::where(Auth::FORGOT_PASSWORD_FIELD, $data['token'])->first();
        if ($auth && $auth->resetPassword($data['password'])) {
            return array('success' => true);
        } else {
            throw new Exceptions\UnauthorizedException("invalid_token");
        }
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

    protected function getData() {
        return CollectionController::getData();
    }

}
