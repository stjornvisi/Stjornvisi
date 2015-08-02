<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 08/11/14
 * Time: 00:55
 */

namespace Stjornvisi\Validator;

use Stjornvisi\Service\User;

use Zend\Validator\Exception;
use Zend\Validator\AbstractValidator;

class UniqueEmail extends AbstractValidator
{
    const EMAIL_IN_USE = 'emailInUse';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::EMAIL_IN_USE => "Þetta netfang er upptekið",
    );

    /**
     * @var \Stjornvisi\Service\User
     */
    private $userService;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->userService = $user;
        parent::__construct();
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $user = $this->userService->get($value);
        if ($user) {
            $this->error(self::EMAIL_IN_USE);
            return false;
        }
        return true;
    }
}
